<?php

namespace Pechynho\Test;

use Pechynho\Test\Model\Worker;
use Pechynho\Utility\Reflections;
use PHPUnit\Framework\TestCase;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
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
		self::assertInstanceOf(ReflectionClass::class, Reflections::createReflectionClass(Worker::class));
		self::assertException(function () { Reflections::createReflectionClass("string"); }, RuntimeException::class);
	}

	public function testGetProperty()
	{
		self::assertInstanceOf(ReflectionProperty::class, Reflections::getProperty(Worker::class, "job"));
		self::assertInstanceOf(ReflectionProperty::class, Reflections::getProperty(Worker::class, "surname"));
		self::assertException(function () { Reflections::getProperty(Worker::class, "nonExistingProperty"); }, RuntimeException::class);
		self::assertEquals(null, Reflections::getProperty(Worker::class, "nonExistingProperty", true));
	}
}
