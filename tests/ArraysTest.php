<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use Pechynho\Test\Model\Person;
use Pechynho\Utility\Arrays;
use Pechynho\Utility\Exception\ItemNotFoundException;
use Pechynho\Utility\Exception\PropertyAccessException;
use PHPUnit\Framework\TestCase;
use VladaHejda\AssertException;

class ArraysTest extends TestCase
{
	use AssertException;

	private $persons;

	private $numbersOrdered;

	private $alphabetOrdered;

	private $numbers;

	private $alphabet;

	private $forenames = ["Joe", "John", "Amy", "Melisa", "Lucy", "Brat", "Justin", "Oto", "Andrew", "Paul", "Laura", "David"];

	private $surnames = ["Doe", "Parker", "Smith", "Williams", "Jones", "Brown", "Davis", "Miller", "Wilson", "Reed", "Coleman", "Norman"];

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

	/**
	 * @throws PropertyAccessException
	 */
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

	/**
	 * @throws ItemNotFoundException
	 */
	public function testLast()
	{
		self::assertEquals(26, Arrays::last($this->numbersOrdered, function (int $number) { return $number > 15; }));
		self::assertException(function () { Arrays::last([], function () { }); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::last($this->numbersOrdered, function (int $number) { return $number > 100; }); }, ItemNotFoundException::class);
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

	/**
	 * @throws PropertyAccessException
	 */
	public function testItemsWithMin()
	{
		self::assertEquals([1], Arrays::itemsWithMin($this->numbers));
		self::assertEquals([$this->persons[Arrays::count($this->persons) - 1]], Arrays::itemsWithMin($this->persons, "height"));
		self::assertException(function () { Arrays::itemsWithMin([]); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::itemsWithMin($this->numbers, 5); }, InvalidArgumentException::class);
	}

	/**
	 * @throws PropertyAccessException
	 */
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

	/**
	 * @throws PropertyAccessException
	 */
	public function testItemsWithMax()
	{
		self::assertEquals([26], Arrays::itemsWithMax($this->numbers));
		self::assertEquals([$this->persons[0]], Arrays::itemsWithMax($this->persons, "height"));
		self::assertException(function () { Arrays::itemsWithMax([]); }, InvalidArgumentException::class);
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

	/**
	 * @throws ItemNotFoundException
	 */
	public function testFirst()
	{
		self::assertEquals(16, Arrays::first($this->numbersOrdered, function (int $number) { return $number > 15; }));
		self::assertException(function () { Arrays::first([], function () { }); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::first($this->numbersOrdered, function (int $number) { return $number > 100; }); }, ItemNotFoundException::class);
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

	public function testLastKeyOf()
	{
		self::assertEquals(0, Arrays::lastKeyOf($this->alphabetOrdered, "A"));
		self::assertEquals(null, Arrays::lastKeyOf($this->alphabetOrdered, 5));
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

	/**
	 * @throws PropertyAccessException
	 */
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

	/**
	 * @throws PropertyAccessException
	 */
	public function testAverage()
	{
		self::assertEquals(13.5, Arrays::average($this->numbers));
		self::assertEquals(13.5, Arrays::average($this->numbersOrdered));
		self::assertException(function () { Arrays::average([]); }, InvalidArgumentException::class);
		self::assertException(function () { Arrays::average($this->numbers, 5); }, InvalidArgumentException::class);
		self::assertEquals(184.5, Arrays::average($this->persons, "height"));
	}

	/**
	 * @throws PropertyAccessException
	 */
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

	/**
	 * @throws PropertyAccessException
	 */
	public function testSelect()
	{
		self::assertEquals($this->surnames, Arrays::select($this->persons, "surname"));
		self::assertEquals($this->surnames, Arrays::select($this->persons, "surname", true));
		self::assertException(function () {  Arrays::select($this->persons, 5); }, InvalidArgumentException::class);
	}

	public function testFlip()
	{
		self::assertEquals(array_flip($this->forenames), Arrays::flip($this->forenames));
	}
}