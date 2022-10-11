<?php

namespace App\Repository;

use Log;
use App\Models\User;
use App\Repository\Repository;
use Illuminate\Support\Facades\DB;
use App\Exceptions\TransactionFaultException;
use Illuminate\Support\Collection;

class UserRepository extends Repository
{
	public function __construct(User $model)
	{
		$this->model=$model;
	}

	public function findOne(string $id) : User
	{
		return $this->model
			->select('id', 'name', 'email', 'username')
			->where([
				'id'=>$id
			])->first();
	}

	public function findOneWhere(array $conditions) : User
	{
		return $this->model
			->select('id', 'name', 'email', 'username')
			->where($conditions)
			->first();
	}

	public function findWhere(array $conditions) : Collection
	{
		return $this->model
			->select('id', 'name', 'email', 'username')
			->where($conditions)
			->get();
	}

	public function findAll() : Collection
	{ 
		return $this->model
			->select('id', 'name', 'email', 'username')
			->orderByDesc('created_at')
			->get();
	}

	public function paginate() 
	{ 
		return $this->model
			->select('id', 'name', 'email', 'username')
			->orderByDesc('created_at')
			->paginate();
	}

	public function create(array $data) : User
	{
		try
        {
			DB::beginTransaction();
			
			$user=$this->model->create($data); 
			
			DB::commit(); 
        }
        catch(\Illuminate\Database\QueryException $e)
        {
        	DB::rollback();
        	Log::debug($e->getMessage());

        	throw new TransactionFaultException($e->getMessage());
        }

        return $user;
	}

	public function update(string $id, array $data) : User
	{
		try
        {
			DB::beginTransaction();
			
			$user=$this->model->where([
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

        return $user;
	}

	public function delete(string $id)
	{
		try
        {
			DB::beginTransaction();
			
			$user=$this->model->where([
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

        return $user;
	}
}
