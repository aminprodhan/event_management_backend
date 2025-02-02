<?php 
    namespace Amin\Event\Classes;
    use Amin\Event\Helpers\JsonResponse;
    use Amin\Event\Classes\Request;
    class Router{
        private $routes = [];private $current_route='';
        public function post($route, $callback){
            $this->routes['POST'][$route] = $callback;
            $this->current_route=$route;
            return $this;
        }
        public function get($route, $callback){
            $this->routes['GET'][$route] = $callback;
            $this->current_route=$route;
            return $this;
        }
        public function delete($route, $callback){
            $this->routes['POST'][$route] = $callback;
            $this->current_route=$route;
            return $this;
        }
        public function handleRequest(){
            $method = $_SERVER['REQUEST_METHOD'];
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
            $clearedBasePath = '/\\'.str_replace('/public', '',$basePath);
            //$final_uri = str_replace($clearedBasePath, '/', $uri);
            $final_uri = preg_replace($clearedBasePath, '', $uri, 1);
            if(empty($final_uri))
                $final_uri=$uri;
            if(isset($this->routes[$method])){
                $route_url=false;
                if(array_key_exists($final_uri, $this->routes[$method])){
                    $route_info=$this->routes[$method][$final_uri];
                    $route_url=$final_uri;
                }
                $matches = [];
                if(!$route_url){
                    foreach($this->routes[$method] as $route => $route_info){
                        $pattern=$this->routeRegex($route);
                        if (preg_match($pattern, $final_uri, $matches)) {
                            $route_info=$this->routes[$method][$route];
                            $route_url=$route;
                            break;
                        }
                    }
                }
                if(!$route_url){
                    JsonResponse::error('Route not found', 404, [$route_url,$uri,$final_uri,$this->routes[$method]]);
                }
                if(isset($this->routes['MIDDLEWARE'][$route_url])){
                    $className=new $this->routes['MIDDLEWARE'][$route_url];
                    $className();
                }
                $params=[new Request($final_uri, $method)];
                foreach($matches as $key => $value){
                    if($key < 1)
                        continue;
                    $params[]=$value;
                }
                $className=new $route_info[0];
                call_user_func_array([$className, $route_info[1]], $params);
            }
            
        }
        private function routeRegex($routeTemplate){
            $result = preg_replace_callback(
                '/\{([^}]+)\}/', // Match placeholders like {id}, {section}
                function ($matches) {
                    // Return regex pattern for each placeholder
                    return '([^/]+)';
                },
                $routeTemplate
            );
            $regexPattern = '/^' . str_replace('/', '\/', $result) . '$/';
            return $regexPattern;
        }
        public function middleware($callback){
            $this->routes['MIDDLEWARE'][$this->current_route]=$callback;
            return $this;
        }
    }
?>