<?php

namespace App\Http\Controllers\Api;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\UserService;
use App\Exceptions\ServiceFaultException;

use Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthenticationController extends Controller
{
    /**
     * @see https://jwt-auth.readthedocs.io/en/develop/
     */

    protected $service;

    public function __construct(UserService $service) {
        $this->service=$service;
    }

    public function profile(Request $request) : JsonResponse
    { 
        $user=auth()->guard('api')->user();

        $token=JWTAuth::fromUser($user);
        JWTAuth::setToken($token);

        return $this->respondWithToken($token, 200);
    }

    public function refresh() : JsonResponse
    {   
        try
        {
            return $this->respondWithToken(auth()->guard('api')->refresh(true, true), 200);
        } 
        catch(JWTException $e)
        {
            return response()->json([
                'status'=>false
              , 'message'=>'Unauthorized'
            ], 401)
            ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
        }
    }
    
    public function logout(Request $request) : JsonResponse
    {   
        $token=JWTAuth::fromUser($request->user());
        JWTAuth::setToken($token);

        auth()->guard('api')->logout(true);
        
        return response()->json([
            'status'=>true
          , 'message'=>'Successfully logged out'
        ], 200)
        ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
    }

    public function login(Request $request) : JsonResponse
    {   
        try 
        {
            $result=$this->service->login($request->only([
                    'email'
                  , 'password'
                ])
            );
        }
        catch(ServiceFaultException $e)
        {
            return response()->json([
                'status'=>false
              , 'message'=>$e->getMessage()
            ], 500)
            ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
        }

        if(is_array($result))
        {
            return response()->json([
                'status'=>false
              , 'message'=>'Please correct form validation errors'
              , 'errors'=>$result
            ], 409)
            ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
        }

        if(!$token=auth()->guard('api')->attempt(['email'=>$request->email, 'password'=>$request->password])) {
            return response()->json([
                'status'=>false
              , 'message'=>'Unauthorized'
            ], 401)
            ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
        }

        return $this->respondWithToken($token, 200);
    }

    public function register(Request $request) : JsonResponse
    {
        try 
        {
            $result=$this->service->register($request->only([
                    'name'
                  , 'email'
                  , 'username'
                  , 'password'
                ])
            );
        }
        catch(ServiceFaultException $e)
        {
            return response()->json([
                'status'=>false
              , 'message'=>$e->getMessage()
            ], 500)
            ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
        }

        if(is_array($result))
        {
            return response()->json([
                'status'=>false
              , 'message'=>'Please correct form validation errors'
              , 'errors'=>$result
            ], 409)
            ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
        }

        $token = JWTAuth::fromUser($result);
        return response()->json([
            'status'=>true
          , 'message'=>'User successfully registered'
          , 'token'=>$token
          , 'token_type'=>'bearer'
          , 'user'=>$result
        ], 201)
        ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
    }

    protected function respondWithToken(string $token, int $code) 
    {
    	/**
    	 * @note	this expiry, use with a cookie on the client side
    	 */

        $expires_in=auth()->guard('api')->factory()->getTTL() * 60;

        return response()->json([
            'status'=>true
          , 'token'=>$token
          , 'token_type'=>'bearer'
        ], $code)
        ->withHeaders(['Content-Type'=>'application/json; charset=utf-8']);
    }

}

