<?php 
    namespace Amin\Event\Classes;
    use Amin\Event\Helpers\JsonResponse;
    class Request{
        public $inputs=[];public $errors=[];
        public $current_url=null;
        public function __construct($url=null, $method=null){
            $postdata =null;
            if($method == 'POST'){
                $postdata = file_get_contents("php://input");
                $this->inputs = $postdata ? json_decode($postdata) : null;
            }
            else if($method == 'GET')
                {
                    $request_info=[];
                    foreach($_GET as $key => $value){
                        $request_info[$key]=$value;
                    }
                    $request_info['params'] = $_GET;
                    $this->inputs = (object)($request_info);
                    
            }
            if($this->inputs){
                $this->inputs->_method = $method;
                $this->inputs->url = $url;
            }
            $this->current_url=$url;
            SessionManagement::setUserRequestSession($this);
        }
        public function __get($name){
            return is_string($this->inputs->$name ?? null) ? strip_tags($this->inputs->$name) : ($this->inputs->$name ?? '');
        }
        public function validate($rules){
            foreach($rules as $field => $rule_params){
                $rules_exp = explode('|', $rule_params);
                foreach($rules_exp as $rule){
                    if($rule == 'required' && empty($this->inputs->$field)){
                        $this->errors[] = $field . ' is required';
                    }
                    else if($rule == 'number'){
                        if(!is_numeric($this->inputs->$field)){
                            $this->errors[] = $field . ' must be numeric';
                        }
                    }
                    else if($rule == 'email' && !empty($this->inputs->$field) && !filter_var($this->inputs->$field, FILTER_VALIDATE_EMAIL)){
                        $this->errors[] = $this->$field . ' is not valid email';
                    }
                    else if( !empty($this->inputs->$field) &&  str_starts_with($rule, 'unique:')){
                        [$table, $column] = explode(',', $rule);
                        $table = str_replace('unique:', '', $table);
                        if (!$this->validateUnique($table, $column, $this->inputs->$field)) {
                            $this->errors[] = "{$field} must be unique.";
                        }
                    }
                }
            }
            if (count($this->errors) > 0) {
                JsonResponse::error('Validation failed', 400, $this->errors);
            }
            return true;
        }
        private function validateUnique($table, $column, $value) {
            $db=new DBQuery();
            $isExist =$db->setTable($table)->where($column, $value)->first();
            if(!empty($isExist->id)){
                return false;
            }
            return true;
        }
    }
?>