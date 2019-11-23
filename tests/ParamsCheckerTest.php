<?php

namespace Pechynho\Test;

use DateTime;
use InvalidArgumentException;
use Pechynho\Test\Model\Person;
use Pechynho\Test\Model\Worker;
use Pechynho\Utility\ParamsChecker;
use PHPUnit\Framework\TestCase;
use VladaHejda\AssertException;

class ParamsCheckerTest extends TestCase
{
	use AssertException;

	public function test__callStatic()
	{
		ParamsChecker::isNullOrIntOrBool('value', "true");
		self::assertException(function () { ParamsChecker::isNullOrIntOrBool('value', "Hello"); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::isNotEmptyArray('value', []); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::isNotEmptyArrayOrString('value', new Person("Joe", "Doe", 18, 180)); }, InvalidArgumentException::class);
	}

	public function testNotEmpty()
	{
		ParamsChecker::notEmpty('value', ["a"]);
		self::assertException(function () { ParamsChecker::notEmpty('value', []); }, InvalidArgumentException::class);
	}

	public function testRange()
	{
		ParamsChecker::range('value', 5, 1, 10);
		ParamsChecker::range('value', 5, 1, null);
		ParamsChecker::range('value', 5, null, 10);
		self::assertException(function () { ParamsChecker::range('value', 15, 1, 10); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::range('value', -1, 1, null); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::range('value', 15, null, 10); }, InvalidArgumentException::class);
	}

	public function testInArray()
	{
		ParamsChecker::inArray('value', 5, [4, 5]);
		self::assertException(function () { ParamsChecker::inArray('value', 3, [4, 5]); }, InvalidArgumentException::class);
	}

	public function testCount()
	{
		ParamsChecker::count('value', [4, 3, 2], 1, 10);
		ParamsChecker::count('value', [4, 3, 2], 1, null);
		ParamsChecker::count('value', [4, 3, 2], null, 10);
		self::assertException(function () { ParamsChecker::count('value', [4, 3, 2], 4, 10); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::count('value', [4, 3, 2], null, 2); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::count('value', [4, 3, 2], 4, null); }, InvalidArgumentException::class);
	}

	public function testType()
	{
		$value = 5;
		ParamsChecker::type('value', $value, "string");
		$value = new DateTime();
		ParamsChecker::type('value', $value, "DateTime");
		$value = function () { };
		ParamsChecker::type('value', $value, "callable");
		self::assertException(function () {
			ParamsChecker::type('value', $value, "string");;
		}, InvalidArgumentException::class);
	}

	public function testLength()
	{
		ParamsChecker::length('value', "abc", 1, 10);
		ParamsChecker::length('value', "abc", 1, null);
		ParamsChecker::length('value', "abc", null, 10);
		self::assertException(function () { ParamsChecker::length('value', "abc", 4, 10); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::length('value', "abc", null, 2); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::length('value', "abc", 4, null); }, InvalidArgumentException::class);
	}

	public function testNotWhiteSpaceOrNullString()
	{
		ParamsChecker::notWhiteSpaceOrNullString('value', "abc");
		self::assertException(function () { ParamsChecker::notWhiteSpaceOrNullString("value", ""); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::notWhiteSpaceOrNullString('value', null); }, InvalidArgumentException::class);
	}

	public function testTypes()
	{
		$value = 5;
		ParamsChecker::types('value', $value, ['DateTime', "string"]);
		$value = new DateTime();
		ParamsChecker::types('value', $value, ["int", "DateTime"]);
		$value = function () { };
		ParamsChecker::types('value', $value, ["int", "callable"]);
		self::assertException(function () {
			ParamsChecker::types('value', $value, ["int", "string"]);;
		}, InvalidArgumentException::class);
	}

	public function testClassExists()
	{
		ParamsChecker::classExists('value', DateTime::class);
		self::assertException(function () { ParamsChecker::classExists('value', 'RandomValue'); }, InvalidArgumentException::class);
	}

	public function testIsInstanceOf()
	{
		ParamsChecker::isInstanceOf('value', new DateTime(), DateTime::class);
		self::assertException(function () { ParamsChecker::isInstanceOf('value', new DateTime(), 'string'); }, InvalidArgumentException::class);
		self::assertException(function () { ParamsChecker::isInstanceOf('value', new DateTime(), Worker::class); }, InvalidArgumentException::class);
	}
}
