<?php

namespace Pechynho\Test\Model;

class Worker extends Person
{
	/**
	 * @MyAnnotation
	 * @var string|null
	 */
	private $job;

	/**
	 * Worker constructor.
	 * @param string      $forename
	 * @param string      $surname
	 * @param int         $age
	 * @param int         $height
	 * @param string|null $job
	 */
	public function __construct(string $forename, string $surname, int $age, int $height, ?string $job)
	{
		parent::__construct($forename, $surname, $age, $height);
		$this->job = $job;
	}

	/**
	 * @return mixed
	 */
	public function getJob(): string
	{
		return $this->job;
	}

	/**
	 * @param string|null $job
	 * @return self
	 */
	public function setJob(?string $job): Worker
	{
		$this->job = $job;
		return $this;
	}
}
