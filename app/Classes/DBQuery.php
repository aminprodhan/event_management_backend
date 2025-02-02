<?php
    namespace Amin\Event\Classes;
    use Amin\Event\Classes\Database;
    use Amin\Event\Helpers\JsonResponse;
    class DBQuery extends Database {
        protected $table = "";
        private $select_columns = "*";
        private $condi = [];
        private $condi_or = [];
        private $condi_values = [];
        private $condi_between = [];
        private $orderBy = [];
        private $with_relations = [];
        private $current_query_data=[];
        private $db_operations = 1; //[1=select,delete,2=insert,update]
        private $class_attributes=[
            'with_relations' => [],
            'current_query_data' => null,
            'class_attributes' => [],
            'db_operations' => [],
            'table' => '',
            'select_columns' => '*',
            'condi' => [],
            'condi_or' => [],
            'condi_values' => [],
            'condi_between' => [],
            'orderBy' => [],
        ];
        public function __construct() {
            Database::getInstance();
            $this->db_operations = 1;
        }
        public function insert($data) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $columnString = implode(',', array_keys($data));
            $valueString = implode(',', array_fill(0, count($data), '?'));
            
            // Use the singleton connection
            $statm = self::getConnection()->prepare("INSERT INTO {$this->table} ({$columnString}) VALUES ({$valueString})");
            $statm->execute(array_values($data));
            $lastId= self::getConnection()->lastInsertId();
            return $this->find($lastId);
        }
        public function update($data){
            if(isset($data['deleted_at'])) 
                $data['deleted_at'] = date('Y-m-d H:i:s');
            else
                $data['updated_at'] = date('Y-m-d H:i:s');
            $columnString = implode('=?,', array_keys($data)).'=?';
            $valueString = implode(',', array_fill(0, count($data), '?'));
            $condiString = implode(' and ', $this->condi);
            $condiValues = $this->condi_values;
            $sql="UPDATE {$this->table} SET {$columnString} WHERE {$condiString}";
            $statm = self::getConnection()->prepare($sql);
            $statm->execute(array_merge(array_values($data), $condiValues));
            return $statm->rowCount();
        }
        public function save(){  
            $this->where('id', $this->id);  
            $updateData=[];
            $properties = get_object_vars($this);
            foreach ($properties as $key => $value) {
                if(!in_array($key,array_keys($this->class_attributes))) {
                    $class= $this->getRelationalClassName();
                    $call=new $class();
                    $method_set = 'set' . ucfirst($key).'Attribute';
                    if(method_exists($this, $method_set))
                        $updateData[$key]=$this->$method_set($value);
                    else
                        $updateData[$key]=$value;
                }
            }
            return $this->update($updateData);   
        }
        public function delete() {
            // $condiString = implode(' and ', $this->condi);
            // $condiValues = $this->condi_values;
            // $sql="DELETE FROM {$this->table} WHERE {$condiString}";
            // $statm = self::getConnection()->prepare($sql);
            // $statm->execute($condiValues);
            // return $statm->rowCount();
            if((isset($this->condi) && count($this->condi) ==0) && !empty($this->id)) {
                $this->where('id', $this->id);
            }
            return $this->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }

        public function find($id) {
            $stmt = self::getConnection()->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $row=$stmt->fetch(\PDO::FETCH_OBJ);
            if(empty($row)) 
                {
                    //print_r($id);
                    JsonResponse::error("Record not foundtt",500,['Record not found']);
                }
            return $this->manangeRelationDataRow($row);
        }
        
    
        public function select($cols) {
            $this->select_columns = $cols;
            return $this;
        }
    
        public function orderBy($col, $val="asc") {
            if(is_array($col)) {
                foreach ($col as $key => $value) {
                    $this->orderBy[] = " {$key} {$value}";
                }
                return $this;
            }
            $this->orderBy[] = " {$col} {$val}";
            return $this;
        }
    
    public function whereBetween($col, $values = []) {
        $this->condi_between[] = " {$col} BETWEEN {$values[0]} and {$values[1]}";
    }
    
    public function where($condi, $param = null, $param2 = null) {
            if(is_callable($condi)){
                 $condi($this);
            }
            else{
                $this->handleWhere('condi', $condi, $param, $param2);
            }
            return $this;
    }
    public function whereOr($condi, $param = null, $param2 = null) {
        if(is_callable($condi))
            $condi($this);
        else
            $this->handleWhere('condi_or', $condi, $param, $param2);
        return $this;
    }
    private function handleWhere($property, $condi, $param = null, $param2 = null) {
        if (is_array($condi)) {
            foreach ($condi as $key => $val) {
                if(is_array($val)){
                    $symbol = "=";$count=count($val);
                    if(is_array($val)){
                        $symbol = $val[1];
                        $key = $val[0];
                        $val = $val[2];
                    }
                    if($count == 4)
                       $this->$property[] = "{$key} {$symbol} {$val} ";
                    else{
                        $this->$property[] = "{$key} {$symbol}? ";
                        $this->condi_values[] = $val;
                    }
                }else{
                    $this->$property[] = "{$key}=? ";
                    $this->condi_values[] = $val;
                }
            }
        } 
        else if (!empty($param)) {
            $symbol = "=";
            if (!empty($param2)) {
                $symbol = $param;
                $param = $param2;
            }
            $this->$property[] = "{$condi} {$symbol}? ";
            $this->condi_values[] = $param;
        }
    }
    public function count($column='*'){
        $sql="SELECT count({$column}) as total FROM {$this->table} {$this->getCond()}";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($this->condi_values);
        return $stmt->fetch(\PDO::FETCH_OBJ)->total;
    }
    public function paginate($limit){
        $request=SessionManagement::request();
        $total=$this->count();
        $page_no=isset($request->params['page'])?$request->params['page']:1;
        $params=$request->params ?? [];
        $offset=($page_no-1)*$limit;
        $route='';
        if(!empty($request->params['route'])){
            $route = $params['route'];
            unset($params['route']);
        }
        
        $sql="SELECT {$this->select_columns} FROM {$this->table} {$this->getCond()} LIMIT ?,?";
        $stmt = self::getConnection()->prepare($sql);
        $this->condi_values[] = $offset;
        $this->condi_values[] = $limit;
        $stmt->execute($this->condi_values);
        $data=$stmt->fetchAll(\PDO::FETCH_OBJ);
        $hasNextPage=null;$hasPreviousPage=null;
        if($page_no*$limit<$total)
            {
                $params['page'] = $page_no+1;
                $queryString = http_build_query($params);
                $hasNextPage = $route . '?' . $queryString;
            }
        else if($page_no>1)
            {
                $params['page'] = $page_no-1;
                $queryString = http_build_query($params);
                $hasPreviousPage = $route . '?' . $queryString;
            }
        $info=[
          'data' => $this->setRespondData($data),
          'total' => $total,
          'perPage' => $limit,
          'hasNextPage' => $hasNextPage,
          'hasPreviousPage' => $hasPreviousPage  
        ];
        return $info;  
    }
    public function get() {
        $sql = "SELECT {$this->select_columns} FROM {$this->table} {$this->getCond()}";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($this->condi_values);
        $data=$stmt->fetchAll(\PDO::FETCH_OBJ);
        //$this->current_query_data=$data;
        return $this->setRespondData($data);
    }
        
    public function first() {
        $sql = "SELECT {$this->select_columns} FROM {$this->table} {$this->getCond()} LIMIT 1";
        //echo $sql;
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($this->condi_values);
        $data=$stmt->fetch(\PDO::FETCH_OBJ);
        $this->current_query_data=$data;
        return $this->manangeRelationDataRow($data);
        //return $this->setRespondData($data);    
    }
    private function setRespondData($data){
        
        if(is_array($data)){
            foreach ($data as $key => $value) 
                {
                    $this->current_query_data=$data[$key];
                    $data[$key]= $this->unsetProperties($this->manangeRelationDataRow($value));
                }
            $this->current_query_data=$data;
            return $data; 
        }
        else          
            return $this->unsetProperties($this->manangeRelationDataRow($data));       
    }
    private function manangeRelationDataRow($row){
        if(!is_array($row) && !is_object($row))
            return null;
        $class= $this->getRelationalClassName();
        $call=new $class();
        //unset call property
        //$call=$this->unsetProperties($call);
        foreach ($row as $key => $value) {
            $attribute_status=0;
            $method_get = 'get' . ucfirst($key).'Attribute';
            $method_set = 'set' . ucfirst($key).'Attribute';
            if (method_exists($this, $method_get)) {
                $call->$key = $this->$method_get($value);
                $attribute_status=1;
            }
            // if(method_exists($this, $method_set))
            //     {
            //         $call->$key=$this->$method_set($value);
            //         $attribute_status=1;
            //     }

            if(!$attribute_status)       
                $call->$key=$value;
        }
        if(isset($this->with_relations)){
            foreach ($this->with_relations as $key => $value) {
                $className=new $value['class']();
                $_SESSION["orm_data"] = $this->current_query_data; 
                $res= call_user_func_array([$className, $value['callback']], []);
                $call->$key=$res;
            }
        } 
        return $call;
    }
    
    public function setTable($table) {
        $this->table = $table;
        return $this;
    }
    private function getRelationalClassName(){
        return $classname_obj=static::class;
    }
    public function with($callback) {
        $classname_obj=$this->getRelationalClassName();
        if(is_array($callback)){
            foreach($callback as $key => $value){
                $this->with_relations[$value] = ['callback' => $value, 'class' => $classname_obj];
            }
        }
        else 
            $this->with_relations[$callback] = ['callback' => $callback, 'class' => $classname_obj]; 
        return $this;
    }
    public function belongsTo($className, $column) {
        $class_blueprint=new $className();
    //    if($this->table == 'attendees'){
    //         print_r($_SESSION["orm_data"]);
    //         exit;
    //    }
        return $info= $class_blueprint->find($_SESSION["orm_data"]->$column);
    }
    public function __get($name){  
        if(isset($this->with_relations[$name])){
            $info=$this->with_relations[$name];
            $_SESSION["orm_data"] = $this->current_query_data;  
            $className=new $info['class']();
            return call_user_func_array([$className, $info['callback']], []);
        }
        else if(isset($this->current_query_data->$name)){
            return $this->current_query_data->$name;
        }
        return null;
    }
    private function getCond() {
        $where = "";$where_or = "";
        $whereBetween = "";$orderBy="";
        if (count($this->condi) > 0) {
            $where = " And " . implode("And ", $this->condi);
        }
        if (count($this->condi_or) > 0) {
            $where_or = " OR " . implode("OR ", $this->condi_or);
        }
        if (count($this->condi_between) > 0) {
            $whereBetween = " And " . implode("And ", $this->condi_between);
        }
        if(count($this->orderBy)>0)
            $orderBy=" ORDER BY ".implode(",", $this->orderBy);
        return " WHERE 1 {$where} {$whereBetween} {$where_or} and deleted_at is null {$orderBy}";
    }
    private function unsetProperties($data) {
        if(!$data)
            return null;
        foreach ($data as $key => $value) {
            if (in_array($key, array_keys($this->class_attributes)))
                unset($data->$key);
        }
        return $data;
    }
    //set property
    public function setClassProperties(){
        foreach ($this->class_attributes as $key => $value) {
            $this->$key=$value;
        }
    }
}
    

?>