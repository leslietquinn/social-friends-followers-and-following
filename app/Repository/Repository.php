<?php

namespace App\Repository;

use App\Interfaces\RepositoryInterface;
use Illuminate\Support\Collection;

abstract class Repository implements RepositoryInterface
{
	protected $model;

	public function __construct() {}

	abstract public function create(array $data);
	abstract public function update(string $id, array $data);
	abstract public function delete(string $id);

}