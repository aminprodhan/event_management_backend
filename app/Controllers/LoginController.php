<?php
namespace Amin\Event\Controllers;
use Amin\Event\Classes\Request;
use Amin\Event\Helpers\JsonResponse;
use Amin\Event\Helpers\PasswordHasher;
use Amin\Event\Models\User;
use Amin\Event\Helpers\Utils;
use Amin\Event\Models\PersonalAccessToken;
use Amin\Event\Classes\DB;
class LoginController{
    private $user;private $personalAccessToken;
    public function __construct(){
        $this->user=new User();
        $this->personalAccessToken=new PersonalAccessToken();
    }
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        DB::beginTransaction();
        try{
            $is_exist=$this->user->where('email', $request->email)->first();
            if($is_exist && PasswordHasher::verify($request->password, $is_exist->password)){
                $token=Utils::generateJWTToken(['user_id' => $is_exist->id,'datetime' => date('Y-m-d H:i:s')]);
                $res['user']=[
                    'name' => $is_exist->name,
                    'email' => $is_exist->email,
                    'role' => $is_exist->role,
                    'status' => $is_exist->status,
                    'token' => $token,
                ];
                $this->personalAccessToken->insert([
                    'tokenable_type' => '\Amin\Event\Models\User',
                    'tokenable_id' => $is_exist->id,
                    'token' => $token,
                    'name' => $is_exist->name,
                ]);
                DB::commit();
                $res['ctoken']=Utils::getBearerToken();
                $res['status']=200;
                $res['message']='User logged in successfully';
                JsonResponse::send($res['status'], $res);    
            }
            else{
                $res['status']=400;
                $res['errors'][]='Invalid credentials';
                JsonResponse::send($res['status'], $res);
            }
        }
        catch(\Exception $e){
            DB::rollback();
            $res['status']=500;
            $res['errors'][]= $e->getMessage();
            JsonResponse::send($res['status'], $res);
        }
        
    }
    public function logout(Request $request){
        $token=Utils::getBearerToken();
        $isExist=$this->personalAccessToken->where('token', $token)->first();
        if($isExist){
            $isExist->delete();
        }
        $res['status']=200;
        $res['message']='User logged out successfully';
        JsonResponse::send($res['status'], $res);
    }
    public function register(Request $request){
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required',
            'password' => 'required'
        ]);
        $register_data = [
            'email' => $request->email,
            'name' => $request->name,
            'password' => PasswordHasher::hash($request->password)
        ];
        $res['status']=500;$res['message']='Something went wrong';
        try {
            $this->user->insert($register_data);
            $res['status']=200;
            $res['message']='User registered successfully';
        }
        catch(\Exception $e){
            $res['status']=500;
            $res['errors'][]= $e->getMessage();
        }
        JsonResponse::send($res['status'], $res);
    }
}