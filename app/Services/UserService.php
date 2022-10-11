<?php

namespace App\Services;

use Log;
use App\Models\User;
use Illuminate\Http\Request;
use App\Interfaces\ServiceInterface;
use App\Repository\UserRepository;
use App\Exceptions\ServiceFaultException;
use App\Exceptions\TransactionFaultException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserService implements ServiceInterface
{
	protected $repository;

	public function __construct(UserRepository $repository)
	{
		$this->repository=$repository;
	}
	
	public function getModelUsingUsername(string $username) : User
	{
		return $this->repository->findOneWhere([
			'username'=>$username
		]);
	}

	public function login(array $data)
	{
		$validator=Validator::make($data, [
                'email'=>['required', 'string']
              , 'password'=>['required', 'string', 'min:6', 'max:32']
        ]); 

        if($validator->fails()) 
        {
            return $validator->messages()->get('*');
        }

        return null;
	}

	public function register(array $data)
	{
		$validator=Validator::make($data, [
                'name'=>['required', 'string']
              , 'email'=>['required', 'string', 'unique:users']
              , 'username'=>['required', 'string', 'max:32']
              , 'password'=>['required', 'string', 'min:6', 'max:32']
        ]); 

        if($validator->fails()) 
        {
            return $validator->messages()->get('*');
        }

        try
        {
	        return $this->repository->create([
	        	'name'=>$data['name']
	          , 'email'=>$data['email']
	          , 'username'=>$data['username']
	          , 'password'=>bcrypt($data['password'])
	        ]);
	    }
	    catch(TransactionFaultException $e)
	    {
	    	throw new ServiceFaultException($e->getMessage());
	    }
	}


}
