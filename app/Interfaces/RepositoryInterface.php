<?php

namespace App\Interfaces;

interface RepositoryInterface 
{
	public function findOne(string $id);
	public function findWhere(array $conditions);
	public function paginate();
	public function findAll();
}