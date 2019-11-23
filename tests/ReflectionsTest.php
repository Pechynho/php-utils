<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use Pechynho\Test\Model\Worker;
use Pechynho\Utility\Reflections;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use VladaHejda\AssertException;

class ReflectionsTest extends TestCase
{
	use AssertException;

	public function testGetMethod()
	{
		self::assertInstanceOf(ReflectionMethod::class, Reflections::getMethod(Worker::class, "setJob"));
		self::assertInstanceOf(ReflectionMethod::class, Reflections::getMethod(Worker::class, "setSurname"));
		self::assertException(function () { Reflections::getMethod(Worker::class, "nonExistingMethod"); }, RuntimeException::class);
		self::assertEquals(null, Reflections::getMethod(Worker::class, "nonExistingMethod", true));
	}

	public function testCreateReflectionClass()
	{
		$worker = new Worker("Joe", "Doe", 30, 180, "Cook");
		self::assertInstanceOf(ReflectionClass::class, Reflections::createReflectionClass(Worker::class));
		self::assertInstanceOf(ReflectionClass::class, Reflections::createReflectionClass($worker));
		self::assertException(function () { Reflections::createReflectionClass("string"); }, RuntimeException::class);
		self::assertException(function () { Reflections::createReflectionClass(""); }, InvalidArgumentException::class);
	}

	public function testCreateReflectionObject()
	{
		$worker = new Worker("Joe", "Doe", 30, 180, "Cook");
		self::assertInstanceOf(ReflectionObject::class, Reflections::createReflectionObject($worker));
		self::assertException(function () { Reflections::createReflectionObject(Worker::class); }, InvalidArgumentException::class);
		self::assertException(function () { Reflections::createReflectionObject(null); }, InvalidArgumentException::class);
	}

	public function testGetProperty()
	{
		self::assertInstanceOf(ReflectionProperty::class, Reflections::getProperty(Worker::class, "job"));
		self::assertInstanceOf(ReflectionProperty::class, Reflections::getProperty(Worker::class, "surname"));
		self::assertException(function () { Reflections::getProperty(Worker::class, "nonExistingProperty"); }, RuntimeException::class);
		self::assertEquals(null, Reflections::getProperty(Worker::class, "nonExistingProperty", true));
	}
}
