<?php

namespace Tests\Feature;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Log;
use App\Models\User;
use App\Models\Follower;
use Illuminate\Support\Testing\Fakes\EventFake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FollowerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $usera;
    protected $userb;
    protected $userc;
    protected $userd;

    public function setUp() : void
    {
        parent::setUp();

        foreach([
            0=>$this->getUserA()
          , 1=>$this->getUserB()
          , 2=>$this->getUserC()
          , 3=>$this->getUserD()
        ] as $user)
        {
            $this->json('POST', '/api/register', [
                'name'=>$user->name
              , 'email'=>$user->email
              , 'password'=>'password'
            ], ['Accept'=>'application/json']);
        }
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    /** @test */
    public function a_different_user_can_follow_multiple_others() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUserD()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        // User D follows User A, User B and User C

        $user=auth()->guard('api')->user(); 
        $this->json('POST', '/api/follow/'.$this->getUserA()->username, [], 
            $this->getHeaders($user)
        );

        $this->json('POST', '/api/follow/'.$this->getUserB()->username, [], 
            $this->getHeaders($user)
        );

        $this->json('POST', '/api/follow/'.$this->getUserC()->username, [], 
            $this->getHeaders($user)
        );

        $this->assertDatabaseHas('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserA()->id
        ]);

        $this->assertDatabaseHas('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserB()->id
        ]);

        $this->assertDatabaseHas('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserC()->id
        ]);

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUserB()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        // User B follows User A

        $user=auth()->guard('api')->user(); 
        $this->json('POST', '/api/follow/'.$this->getUserA()->username, [], 
            $this->getHeaders($user)
        );

        $this->assertDatabaseHas('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserA()->id
        ]);
    }

    /** @test */
    public function a_user_cannot_follow_themself() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUserA()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        $user=auth()->guard('api')->user(); 
        $response=$this->json('POST', '/api/follow/'.$this->getUserA()->username, [], 
            $this->getHeaders($user)
        );

        $response->assertStatus(500);
        $this->assertDatabaseMissing('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserA()->id
        ]);
    }

    /** @test */
    public function can_a_user_unfollow_another_they_follow() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUserA()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        $user=auth()->guard('api')->user(); 
        $response=$this->json('POST', '/api/follow/'.$this->getUserB()->username, [], 
            $this->getHeaders($user)
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserB()->id
        ]);

        $response=$this->json('POST', '/api/unfollow/'.$this->getUserB()->username, [],
            $this->getHeaders($user)
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserB()->id
        ]);
    }

    /** @test */
    public function can_one_user_follow_another() : void
    {
        $this->withExceptionHandling();

        $response=$this->json('POST', '/api/login', [
            'email'=>$this->getUserA()->email
          , 'password'=>'password'
        ], ['Accept'=>'application/json'])
        ->assertStatus(200)
        ->assertJsonStructure([
            'status'
          , 'token'
        ]);
        
        $this->assertAuthenticated('api');

        $user=auth()->guard('api')->user(); 
        $response=$this->json('POST', '/api/follow/'.$this->getUserB()->username, [], 
            $this->getHeaders($user)
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('followers', [
            'user_id'=>$user->id
          , 'follower'=>$this->getUserB()->id
        ]);
    }

    /** @test */
    public function all_users_have_been_registered() : void
    {
        $this->assertDatabaseHas('users', [
            'email'=>$this->getUserA()->email
        ]);

        $this->assertDatabaseHas('users', [
            'email'=>$this->getUserB()->email
        ]);
        
        $this->assertDatabaseHas('users', [
            'email'=>$this->getUserC()->email
        ]);
        
        $this->assertDatabaseHas('users', [
            'email'=>$this->getUserD()->email
        ]);
    }

    protected function getUserA() : User
    {
        if(is_null($this->usera))
        {
            $this->usera=User::factory()->create([
                'name'=>'Susan'
              , 'email'=>'susan@bt.com'
              , 'username'=>'susan'
              , 'password'=>bcrypt('password')
            ]);
        }

        return $this->usera;
    }

    protected function getUserB() : User
    {
        if(is_null($this->userb))
        {
            $this->userb=User::factory()->create([
                'name'=>'Donald'
              , 'email'=>'donald@bt.com'
              , 'username'=>'donald'
              , 'password'=>bcrypt('password')
            ]);
        }

        return $this->userb;
    }

    protected function getUserC() : User
    {
        if(is_null($this->userc))
        {
            $this->userc=User::factory()->create([
                'name'=>'John'
              , 'email'=>'john@bt.com'
              , 'username'=>'john'
              , 'password'=>bcrypt('password')
            ]);
        }

        return $this->userc;
    }

    protected function getUserD() : User
    {
        if(is_null($this->userd))
        {
            $this->userd=User::factory()->create([
                'name'=>'Beth'
              , 'email'=>'beth@bt.com'
              , 'username'=>'beth'
              , 'password'=>bcrypt('password')
            ]);
        }

        return $this->userd;
    }

    protected function getHeaders($user=null) : array 
    {
        $headers=['Accept'=>'application/json']; 
            
        if(!is_null($user)) 
        {
            $token=JWTAuth::fromUser($user); 
            JWTAuth::setToken($token);
            
            /**
             * @note    use "HTTP:Authorization" because of the Apache server, but on
             *          the client side, use "Authorization" instead
             */

            $headers['HTTP:Authorization']='Bearer '.$token;
        } 

        return $headers;
    }
}
