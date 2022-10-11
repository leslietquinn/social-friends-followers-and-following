<?php

namespace App\Services;

use Log;
use App\Models\User;
use Illuminate\Http\Request;
use App\Interfaces\ServiceInterface;
use App\Repository\UserRepository;
use App\Repository\FollowerRepository;
use App\Exceptions\ServiceFaultException;
use App\Exceptions\TransactionFaultException;
use Illuminate\Support\Facades\Validator;

class FollowerService implements ServiceInterface
{
	protected $repository;

	public function __construct(FollowerRepository $repository)
	{
		$this->repository=$repository;
	}

	public function follow(User $user, User $follower)
	{
		if($user->id === $follower->id)
		{
			throw new ServiceFaultException('User cannot follow themself');
		}
		
		try
		{
			return $this->repository->create([
				'user_id'=>$user->id
			  , 'follower'=>$follower->id
			]);
		}
		catch(TransactionFaultException $e)
		{
			Log::info('TransactionFaultException [FollowerService::follow()]');

			throw new ServiceFaultException($e->getMessage());
		}
	}

	public function unfollow(User $user, User $follower)
	{
		try
		{ 
			return $this->repository->deleteWhere([
				'user_id'=>$user->id
			  , 'follower'=>$follower->id
			]);
		}
		catch(TransactionFaultException $e)
		{
			Log::info('TransactionFaultException [FollowerService::unfollow()]');

			throw new ServiceFaultException($e->getMessage());
		}
	}
}
