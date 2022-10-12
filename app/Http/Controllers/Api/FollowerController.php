<?php

namespace App\Http\Controllers\Api;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\FollowerService;
use App\Services\UserService;
use App\Exceptions\ServiceFaultException;

use Log;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FollowerController extends Controller
{
    protected $userService;
    protected $followerService;

    public function __construct(FollowerService $followerService, UserService $userService)
    {
        $this->followerService=$followerService;
        $this->userService=$userService;
    }

    public function follow(Request $request, string $username) : JsonResponse
    {
        $user=auth()->guard('api')->user();
        
        try
        {
            $this->followerService->follow(
                $user
              , $this->userService->getModelUsingUsername($username)
            );
        }
        catch(ServiceFaultException $e)
        {
            $this->respondWithFailure($this->generateToken($user), 500);
        }

        return $this->respondWithSuccess($this->generateToken($user), 201);
    }

    public function unfollow(Request $request, string $username) : JsonResponse
    {
        $user=auth()->guard('api')->user(); 
        
        try
        {
            $this->followerService->unfollow(
                $user
              , $this->userService->getModelUsingUsername($username)
            );
        }
        catch(ServiceFaultException $e)
        {
            $this->respondWithFailure($this->generateToken($user), 500);
        }

        return $this->respondWithSuccess($this->generateToken($user), 200);
    }

    protected function generateToken(User $user) : string
    {
        $token=JWTAuth::fromUser($user);
        JWTAuth::setToken($token);

        return $token;
    }

    protected function respondWithSuccess(string $token, int $code) 
    {
        /**
         * @note    this expiry, in minutes, use with a cookie on the client side
         */

        $expiry=auth()->guard('api')->factory()->getTTL() * 60;

        return response()->json([
            'status'=>true
          , 'token'=>$token
          , 'expiry'=>$expiry
          , 'token_type'=>'bearer'
        ], $code)
        ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
    }

    protected function respondWithFailure(string $token, int $code) 
    {
        return response()->json([
            'status'=>false
          , 'token'=>$token
          , 'token_type'=>'bearer'
        ], $code)
        ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
    }

}
