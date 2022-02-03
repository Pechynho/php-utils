<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use Pechynho\Test\Model\Person;
use Pechynho\Test\Traits\AssertExceptionTrait;
use Pechynho\Utility\Arrays;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ArraysTest extends TestCase
{
	use AssertExceptionTrait;

	/** @var Person[] */
	private $persons;

	private $numbersOrdered;

	private $alphabetOrdered;

	private $numbers;

	private $alphabet;

	private $forenames = ["Joe", "John", "Amy", "Melisa", "Lucy", "Brat", "Justin", "Oto", "Andrew", "Paul", "Laura", "David"];

	private $surnames = ["Doe", "Parker", "Smith", "Williams", "Jones", "Brown", "Davis", "Miller", "Wilson", "Reed", "Coleman", "Norman"];

	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		foreach ($this->forenames as $key => $forename)
		{
			$this->persons[] = new Person($this->forenames[$key], $this->surnames[$key], 15 + $key, 190 - $key);
		}
		for ($i = 0; $i < 26; $i++)
		{
			$this->numbersOrdered[] = $i + 1;
			$this->alphabetOrdered[] = chr(65 + $i);
		}
		$this->numbers = $this->numbersOrdered;
		$this->alphabet = $this->alphabetOrdered;
		shuffle($this->numbers);
		shuffle($this->alphabet);
	}

	public function testSum()
	{
		self::assertEquals(351, Arrays::sum($this->numbers));
		self::assertEquals(351, Arrays::sum($this->numbersOrdered));
		self::assertException(function () { Arrays::sum([]); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::sum($this->numbers, 5); }, InvalidArgumentException::class);
		self::assertEquals(2214, Arrays::sum($this->persons, "height"));
	}

	public function testLastOrDefault()
	{
		self::assertEquals(26, Arrays::lastOrDefault($this->numbersOrdered, function (int $number) { return $number > 15; }));
		self::assertEquals(null, Arrays::lastOrDefault($this->numbersOrdered, function (int $number) { return $number > 26; }));
	}

	public function testLast()
	{
		self::assertEquals(26, Arrays::last($this->numbersOrdered, function (int $number) { return $number > 15; }));
		self::assertException(function () { Arrays::last([], function () { }); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::last($this->numbersOrdered, function (int $number) { return $number > 100; }); }, RuntimeException::class);
	}

	public function testKeyOf()
	{
		self::assertEquals(0, Arrays::keyOf($this->alphabetOrdered, "A"));
		self::assertEquals(null, Arrays::keyOf($this->alphabetOrdered, 5));
	}

	public function testBinarySearch()
	{
		self::assertEquals(4, Arrays::binarySearch($this->alphabetOrdered, "E"));
		self::assertEquals(null, Arrays::binarySearch($this->alphabetOrdered, 5));
	}

	public function testItemsWithMin()
	{
		self::assertEquals([1], Arrays::itemsWithMin($this->numbers));
		self::assertEquals([$this->persons[Arrays::count($this->persons) - 1]], Arrays::itemsWithMin($this->persons, "height"));
		self::assertEquals([], Arrays::itemsWithMin([]));
		self::assertException(function () { Arrays::itemsWithMin($this->numbers, 5); }, InvalidArgumentException::class);
	}

	public function testGroupBy()
	{
		self::assertEquals([$this->persons[0]], Arrays::groupBy($this->persons, "height")[190]);
		self::assertException(function () { Arrays::groupBy($this->persons, null); }, InvalidArgumentException::class);
	}

	public function testFirstOrDefault()
	{
		self::assertEquals(16, Arrays::firstOrDefault($this->numbersOrdered, function (int $number) { return $number > 15; }));
		self::assertEquals(null, Arrays::firstOrDefault($this->numbersOrdered, function (int $number) { return $number > 26; }));
	}

	public function testItemsWithMax()
	{
		self::assertEquals([26], Arrays::itemsWithMax($this->numbers));
		self::assertEquals([$this->persons[0]], Arrays::itemsWithMax($this->persons, "height"));
		self::assertEquals([], Arrays::itemsWithMax([]));
		self::assertException(function () { Arrays::itemsWithMax($this->numbers, 5); }, InvalidArgumentException::class);
	}

	public function testFirstKey()
	{
		self::assertEquals(0, Arrays::firstKey($this->alphabetOrdered));
		self::assertException(function () { Arrays::firstKey([]); }, InvalidArgumentException::class);
	}

	public function testToArray()
	{
		self::assertEquals($this->numbers, Arrays::toArray($this->numbers));
	}

	public function testFirst()
	{
		self::assertEquals(16, Arrays::first($this->numbersOrdered, function (int $number) { return $number > 15; }));
		self::assertException(function () { Arrays::first([], function () { }); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::first($this->numbersOrdered, function (int $number) { return $number > 100; }); }, RuntimeException::class);
	}

	public function testLastValue()
	{
		self::assertEquals($this->persons[Arrays::count($this->persons) - 1], Arrays::lastValue($this->persons));
		self::assertException(function () { Arrays::lastValue([]); }, InvalidArgumentException::class);
	}

	public function testFirstValue()
	{
		self::assertEquals($this->persons[0], Arrays::firstValue($this->persons));
		self::assertException(function () { Arrays::firstValue([]); }, InvalidArgumentException::class);
	}

	public function testOrderBy()
	{
		self::assertEquals($this->numbersOrdered, Arrays::orderBy($this->numbers));
		self::assertEquals($this->alphabetOrdered, Arrays::orderBy($this->alphabet));
		self::assertEquals(Arrays::reverse($this->alphabetOrdered), Arrays::orderBy($this->alphabet, Arrays::ORDER_DIRECTION_DESCENDING));
		$persons = $this->persons;
		usort($persons, function (Person $personA, Person $personB) { return strcmp($personA->getForename(), $personB->getForename()); });
		self::assertEquals($persons, Arrays::orderBy($this->persons, Arrays::ORDER_DIRECTION_ASCENDING, "forename"));
		self::assertEquals($persons, Arrays::orderBy($this->persons, Arrays::ORDER_DIRECTION_ASCENDING, "forename", function (string $forenameA, string $forenameB) { return strcmp($forenameA, $forenameB); }));
		self::assertException(function () { Arrays::orderBy($this->numbers, "something"); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::orderBy($this->numbers, Arrays::ORDER_DIRECTION_ASCENDING, 5); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::orderBy($this->numbers, Arrays::ORDER_DIRECTION_ASCENDING, null, 5); }, InvalidArgumentException::class);
	}

	public function testIsIterable()
	{
		self::assertEquals(true, Arrays::isIterable($this->numbers));
		self::assertEquals(false, Arrays::isIterable(null));
	}

	public function testLastKey()
	{
		self::assertEquals(count($this->alphabetOrdered) - 1, Arrays::lastKey($this->alphabetOrdered));
		self::assertException(function () { Arrays::lastKey([]); }, InvalidArgumentException::class);
	}

	public function testIsEmpty()
	{
		self::assertEquals(false, Arrays::isEmpty($this->numbers));
		self::assertEquals(true, Arrays::isEmpty([]));
	}

	public function testReverse()
	{
		self::assertEquals(array_reverse($this->numbers), Arrays::reverse($this->numbers));
		self::assertEquals([], Arrays::reverse([]));
	}

	public function testWhere()
	{
		self::assertEquals([25, 26], Arrays::where($this->numbersOrdered, function (int $number) { return $number > 24; }));
		self::assertEquals([], Arrays::where([], function (int $number) { return $number > 24; }));
	}

	public function testIsCountable()
	{
		self::assertEquals(true, Arrays::isCountable($this->persons));
		self::assertEquals(false, Arrays::isCountable(null));
	}

	public function testMin()
	{
		self::assertEquals(1, Arrays::min($this->numbers));
		self::assertEquals(179, Arrays::min($this->persons, "height"));
		self::assertException(function () { Arrays::min([]); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::min($this->numbers, 5); }, InvalidArgumentException::class);
	}

	public function testContains()
	{
		self::assertEquals(true, Arrays::contains($this->persons, $this->persons[Arrays::count($this->persons) - 1]));
		self::assertEquals(false, Arrays::contains($this->persons, 5));
	}

	public function testAverage()
	{
		self::assertEquals(13.5, Arrays::average($this->numbers));
		self::assertEquals(13.5, Arrays::average($this->numbersOrdered));
		self::assertException(function () { Arrays::average([]); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::average($this->numbers, 5); }, InvalidArgumentException::class);
		self::assertEquals(184.5, Arrays::average($this->persons, "height"));
	}

	public function testMax()
	{
		self::assertEquals(26, Arrays::max($this->numbers));
		self::assertEquals(190, Arrays::max($this->persons, "height"));
		self::assertException(function () { Arrays::max([]); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::max($this->numbers, 5); }, InvalidArgumentException::class);
	}

	public function testCount()
	{
		self::assertEquals(count($this->persons), Arrays::count($this->persons));
		self::assertEquals(count($this->numbersOrdered), Arrays::count($this->numbersOrdered));
	}

	public function testSelect()
	{
		self::assertEquals($this->surnames, Arrays::select($this->persons, "surname"));
		self::assertEquals($this->surnames, Arrays::select($this->persons, "surname", true));
		self::assertException(function () { Arrays::select($this->persons, 5); }, InvalidArgumentException::class);
	}

	public function testFlip()
	{
		self::assertEquals(array_flip($this->forenames), Arrays::flip($this->forenames));
	}

	public function testMapToPairs()
	{
		$testCase = [];
		foreach ($this->persons as $person)
		{
			$testCase[$person->getHeight()] = $person->getSurname();
		}
		self::assertEquals($testCase, Arrays::mapToPairs($this->persons, "height", "surname"));
		self::assertException(function () { Arrays::mapToPairs($this->persons, null, "surname"); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::mapToPairs($this->persons, "height", null); }, InvalidArgumentException::class);
	}

	public function testMapByProperty()
	{
		$testCase = [];
		foreach ($this->persons as $person)
		{
			$testCase[$person->getHeight()] = $person;
		}
		self::assertEquals($testCase, Arrays::mapByProperty($this->persons, "height"));
		self::assertException(function () { Arrays::mapByProperty($this->persons, null); }, InvalidArgumentException::class);
	}

	public function testMergeArrayConfig()
	{
		$defaultConfig = [
			"value1" => ["value1_1" => 5, "value1_2" => ["value1_2_1" => 5, "value1_2_2" => [5, 6, 7]]],
			"value2" => [4, 3, 5],
			"value3" => null
		];
		$config = [
			"value1" => ["value1_2" => ["value1_2_2" => [8, 7]]],
			"value2" => 5
		];
		$result = [
			"value1" => ["value1_1" => 5, "value1_2" => ["value1_2_1" => 5, "value1_2_2" => [8, 7]]],
			"value2" => 5,
			"value3" => null
		];
		self::assertEquals($result, Arrays::mergeArrayConfig($config, $defaultConfig, true));
		self::assertEquals(["value1" => ["value1_2" => ["value1_2_2" => [8, 7]]], "value2" => 5, "value3" => null], Arrays::mergeArrayConfig($config, $defaultConfig, false));
	}

	public function testRecursiveGet()
	{
		$testCase = ["foo" => ["bar" => ["john" => ["doe" => [4 => 15]]]]];
		self::assertTrue(Arrays::recursiveGet($testCase, "[foo][bar][john][doe][4]", $value));
		self::assertEquals(15, $value);
		self::assertFalse(Arrays::recursiveGet($testCase, "[foo][bar][lewis]"));
		self::assertException(function () use ($testCase) { Arrays::recursiveGet($testCase, "   "); }, InvalidArgumentException::class);
		self::assertFalse(Arrays::recursiveGet([], "[foo][bar]"));
	}

	public function testRecursiveSet()
	{
		self::assertEquals(["name" => ["forename" => "Joe"]], Arrays::recursiveSet([], "[name][forename]", "Joe"));
		self::assertEquals(["name" => ["forename" => "Doe", "surname" => "Admin"]], Arrays::recursiveSet(["name" => ["forename" => "Joe", "surname" => "Admin"]], "[name][forename]", "Doe"));
		self::assertEquals(["name" => ["forename" => "Joe", "surname" => "Admin", "job" => ["place" => "Prague"]]], Arrays::recursiveSet(["name" => ["forename" => "Joe", "surname" => "Admin"]], "[name][job][place]", "Prague"));
		self::assertException(function () { Arrays::recursiveSet([], "   ", 5); }, InvalidArgumentException::class);
	}

	public static function testInsertAfter()
	{
		$target = ["one" => 1, "three" => 3];
		self::assertEquals(["one" => 1, "two" => 2, "three" => 3], Arrays::insertAfter($target, "two", 2, "one"));
		self::assertEquals(["one" => 1, "three" => 3, "key" => "key"], Arrays::insertAfter($target, "key", "key", "two"));
		$target = [1 => 1, 3 => 3];
		self::assertEquals([1 => 1, 2 => 2, 3 => 3], Arrays::insertAfter($target, 2, 2, 1));
		self::assertEquals([1 => 1], Arrays::insertAfter([], 1, 1, "one"));
		self::assertException(function () use ($target) { Arrays::insertAfter($target, null, 2, "one"); }, InvalidArgumentException::class);
		self::assertException(function () use ($target) { Arrays::insertAfter($target, 2, 2, null); }, InvalidArgumentException::class);
	}

	public static function testInsertBefore()
	{
		$target = ["one" => 1, "three" => 3];
		self::assertEquals(["one" => 1, "two" => 2, "three" => 3], Arrays::insertBefore($target, "two", 2, "three"));
		self::assertEquals(["one" => 1, "three" => 3, "key" => "key"], Arrays::insertBefore($target, "key", "key", "two"));
		$target = [1 => 1, 3 => 3];
		self::assertEquals([1 => 1, 2 => 2, 3 => 3], Arrays::insertBefore($target, 2, 2, 3));
		self::assertEquals([1 => 1], Arrays::insertBefore([], 1, 1, "one"));
		self::assertException(function () use ($target) { Arrays::insertBefore($target, null, 2, "one"); }, InvalidArgumentException::class);
		self::assertException(function () use ($target) { Arrays::insertBefore($target, 2, 2, null); }, InvalidArgumentException::class);
	}
}
