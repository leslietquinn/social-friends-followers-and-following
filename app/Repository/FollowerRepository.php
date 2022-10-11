<?php

namespace App\Repository;

use Log;
use App\Models\User;
use App\Models\Follower;
use App\Repository\Repository;
use Illuminate\Support\Facades\DB;
use App\Exceptions\TransactionFaultException;
use Illuminate\Support\Collection;

class FollowerRepository extends Repository
{
	public function __construct(Follower $model)
	{
		$this->model=$model;
	}

	public function findOne(string $id) : Follower
	{
		return $this->model
			->select('id', 'user_id', 'follower')
			->where([
				'id'=>$id
			])->first();
	}

	public function findWhere(array $conditions) : Collection
	{
		return $this->model
			->select('id', 'user_id', 'follower')
			->where($conditions)
			->get();
	}

	public function findAll() : Collection
	{ 
		return $this->model
			->select('id', 'user_id', 'follower')
			->orderByDesc('created_at')
			->get();
	}

	public function paginate() 
	{ 
		return $this->model
			->select('id', 'user_id', 'follower')
			->orderByDesc('created_at')
			->paginate();
	}

	public function create(array $data) : Follower
	{
		try
        {
			DB::beginTransaction();
			
			$follower=$this->model->create($data); 
			
			DB::commit(); 
        }
        catch(\Illuminate\Database\QueryException $e)
        {
        	DB::rollback();
        	Log::debug($e->getMessage());

        	throw new TransactionFaultException($e->getMessage());
        }

        return $follower;
	}

	public function update(string $id, array $data) : Follower
	{
		try
        {
			DB::beginTransaction();
			
			$follower=$this->model->where([
				'id'=>$id
			])->update($data); 

			DB::commit();
        }
        catch(\Illuminate\Database\QueryException $e)
        {
        	DB::rollback();
        	Log::debug($e->getMessage());

        	throw new TransactionFaultException($e->getMessage());
        }

        return $follower;
	}

	public function delete(string $id)
	{
		try
        {
			DB::beginTransaction();
			
			$follower=$this->model->where([
				'id'=>$id
			])->delete($id); 

			DB::commit();
        }
        catch(\Illuminate\Database\QueryException $e)
        {
        	DB::rollback();
        	Log::debug($e->getMessage());

        	throw new TransactionFaultException($e->getMessage());
        }

        return $follower;
	}

	public function deleteWhere(array $conditions)
	{	
		try
        {
			DB::beginTransaction();
			
			$follower=$this->model->where([
				'user_id'=>$conditions['user_id']
			  , 'follower'=>$conditions['follower']
			])->delete(); 

			DB::commit();
        }
        catch(\Illuminate\Database\QueryException $e)
        {
        	DB::rollback();
        	Log::debug($e->getMessage());

        	throw new TransactionFaultException($e->getMessage());
        }

        return $follower;
	}
}
