<?php

namespace Pechynho\Test;

use Pechynho\Test\Model\Person;
use Pechynho\Utility\PropertyAccess;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use VladaHejda\AssertException;

class PropertyAccessTest extends TestCase
{
	use AssertException;

	/** @var Person */
	private $person;

	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		$this->person = new Person("Joe", "Doe", 30, 180);
	}

	public function testGetValue()
	{
		self::assertEquals("Joe", PropertyAccess::getValue($this->person, "forename"));
		self::assertEquals("Doe", PropertyAccess::getValue($this->person, function (Person $person) { return $person->getSurname(); }));
		self::assertEquals(30, PropertyAccess::getValue($this->person, "age", true, null, true));
		self::assertEquals(null, PropertyAccess::getValue($this->person, "notExistingProperty", false, null, true));
		self::assertException(function () { PropertyAccess::getValue($this->person, "notExistingProperty", true, null, true); }, RuntimeException::class);
	}

	public function testSetValue()
	{
		PropertyAccess::setValue($this->person, "forename", "Joshua");
		self::assertEquals("Joshua", $this->person->getForename());
		PropertyAccess::setValue($this->person, function (Person $person, $value) { $person->setSurname($value);}, "Green");
		self::assertEquals("Green", $this->person->getSurname());
		PropertyAccess::setValue($this->person, "age", 40, false, true);
		self::assertEquals(40, PropertyAccess::getValue($this->person, "age", true, null, true));
		self::assertException(function () { PropertyAccess::setValue($this->person, "notExistingProperty", [], true, true); }, RuntimeException::class);
	}
}
