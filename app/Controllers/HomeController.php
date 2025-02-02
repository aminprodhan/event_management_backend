<?php
    namespace Amin\Event\Controllers;
    use Amin\Event\Classes\Request;
    use Amin\Event\Models\User;
    class HomeController{
        public function index(Request $request){
            //print_r($request->email);
            //$user=new User();
            //print_r($user->get());
            $res=$request->validate([
                'email' => 'required|email|unique:users,email',
                'name' => 'required',
                'password' => 'required'
            ]);
            //print_r($res);
        }
    }
?>