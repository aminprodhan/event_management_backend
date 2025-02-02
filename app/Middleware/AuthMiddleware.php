<?php
namespace Amin\Event\Middleware;
use Amin\Event\Helpers\Utils;
use Amin\Event\Helpers\JsonResponse;
use Amin\Event\Models\PersonalAccessToken;
class AuthMiddleware{
    //invoke function
    public function __invoke(){ //$request, $response, $next
        $token=Utils::getBearerToken();
        $errors[]='Unauthorized,Please login first!!';
        if(empty($token)){
            JsonResponse::send(401, ['errors' => $errors]);
        }else{
            $info=Utils::decripteJWTToken($token);
            $pat=new PersonalAccessToken();
            $isExist=$pat->with('user')->where('token', $token)->first();
            //$value=$isExist && $isExist->id ?? null;
            if(empty($isExist->id) || empty($isExist->user->id) || empty($info->user_id)){
                    JsonResponse::send(401, ['errors' => $errors]);
            }
            Utils::setUserSession($isExist->user);
        }
    }
}