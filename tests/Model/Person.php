<?php

namespace Pechynho\Test\Model;

/**
 * @MyAnnotation
 */
class Person
{
	/** @var string */
	private $forename;

	/** @var string */
	private $surname;

	/** @var int */
	private $age;

	/** @var int */
	private $height;

	/**
	 * Person constructor.
	 * @param string $forename
	 * @param string $surname
	 * @param int    $age
	 * @param int    $height
	 */
	public function __construct(string $forename, string $surname, int $age, int $height)
	{
		$this->forename = $forename;
		$this->surname = $surname;
		$this->age = $age;
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getForename(): string
	{
		return $this->forename;
	}

	/**
	 * @param string $forename
	 * @return Person
	 */
	public function setForename(string $forename): Person
	{
		$this->forename = $forename;
		return $this;
	}

	/**
	 * @MyAnnotation
	 * @return string
	 */
	public function getSurname(): string
	{
		return $this->surname;
	}

	/**
	 * @param string $surname
	 * @return Person
	 */
	public function setSurname(string $surname): Person
	{
		$this->surname = $surname;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getHeight(): int
	{
		return $this->height;
	}

	/**
	 * @param int $height
	 * @return Person
	 */
	public function setHeight(int $height): Person
	{
		$this->height = $height;
		return $this;
	}
}
