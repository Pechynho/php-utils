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
	public function __construct($forename, $surname, $age, $height)
	{
		$this->forename = $forename;
		$this->surname = $surname;
		$this->age = $age;
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getForename()
	{
		return $this->forename;
	}

	/**
	 * @param string $forename
	 * @return self
	 */
	public function setForename($forename)
	{
		$this->forename = $forename;
		return $this;
	}

	/**
	 * @MyAnnotation
	 * @return string
	 */
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @param string $surname
	 * @return self
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param int $height
	 * @return self
	 */
	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}
}
