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
	public function __construct($forename, $surname, $age, $height, $job)
	{
		parent::__construct($forename, $surname, $age, $height);
		$this->job = $job;
	}

	/**
	 * @return mixed
	 */
	public function getJob()
	{
		return $this->job;
	}

	/**
	 * @param string|null $job
	 * @return self
	 */
	public function setJob($job)
	{
		$this->job = $job;
		return $this;
	}
}
