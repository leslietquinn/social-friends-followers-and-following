<?php

namespace Tests\Feature;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Log;
use App\Models\User;
use Illuminate\Support\Testing\Fakes\EventFake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;

    public function setUp() : void
    {
        parent::setUp();
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    /** @test */
    public function can_a_registered_user_access_their_profile() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUser()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        $user=auth()->guard('api')->user(); 
        $response=$this->json('POST', '/api/profile', [], 
            $this->getHeaders($user)
        );
        $response->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);

        $content=json_decode($response->getContent(), true);

        $this->assertTrue($content['status']);
    }
    
    /** @test */
    public function can_a_registered_user_refresh_their_token() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUser()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        $user=auth()->guard('api')->user(); 
        $response=$this->json('POST', '/api/refresh', [], 
            $this->getHeaders($user)
        );
        $response->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
    }

    /** @test */
    public function can_a_registered_user_logout() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUser()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        $user=auth()->guard('api')->user(); 
        $response=$this->json('POST', '/api/logout', [], 
            $this->getHeaders($user)
        );
        $response->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'message'
        ]);

        $this->assertGuest('api');
    }

    /** @test */
    public function can_an_unregistered_user_login() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>'fake@bt.com'
          , 'password'=>'password'
        ], ['Accept'=>'application/json']);
        
        $response->assertStatus(401);
        
        $this->assertGuest('api');
    }

    /** @test */
    public function can_a_registered_user_login() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUser()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');
    }

    /** @test */
    public function can_a_new_user_register() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/register', [
            'name'=>'Susan'
          , 'email'=>'susan@bt.com'
          , 'username'=>'susan'
          , 'password'=>'password'
        ], ['Accept'=>'application/json']);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status'
          , 'user'=>[
                'id'
              , 'name'
              , 'email'
              , 'username'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'name'=>'Susan'
          , 'email'=>'susan@bt.com'
          , 'username'=>'susan'
        ]);
    }

    protected function getUser() : User
    {
        if(is_null($this->user))
        {
            $this->user=User::factory()->create([
                'name'=>'Susan'
              , 'email'=>'susan@bt.com'
              , 'username'=>'susan'
              , 'password'=>bcrypt('password')
            ]);
        }

        return $this->user;
    }

    protected function getHeaders($user=null) : array 
    {
        $headers=['Accept'=>'application/json']; 
            
        if(!is_null($user)) 
        {
            $token=JWTAuth::fromUser($user); 
            JWTAuth::setToken($token);
            
            /**
             * @note    we use "HTTP:Authorization" because of the Apache server, but on
             *          the client side, use "Authorization" instead
             */

            $headers['HTTP:Authorization']='Bearer '.$token;
        } 

        return $headers;
    }
}
