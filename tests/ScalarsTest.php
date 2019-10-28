<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use Pechynho\Test\Model\Person;
use Pechynho\Utility\Scalars;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use VladaHejda\AssertException;

class ScalarsTest extends TestCase
{
	use AssertException;

	public function testIsScalarTypeValid()
	{
		self::assertTrue(Scalars::isScalarTypeValid("BOOLEAN"));
		self::assertFalse(Scalars::isScalarTypeValid("bool"));
	}

	public function testParseToScalarType()
	{
		self::assertSame("5", Scalars::parse(5, Scalars::STRING));
		self::assertSame("5.5", Scalars::parse(5.5, Scalars::STRING));
		self::assertSame("true", Scalars::parse(true, Scalars::STRING));
		self::assertSame("false", Scalars::parse(false, Scalars::STRING));
		self::assertSame(5, Scalars::parse("5", Scalars::INTEGER));
		self::assertSame(5.5, Scalars::parse("5.5", Scalars::FLOAT));
		self::assertSame(true, Scalars::parse("1", Scalars::BOOLEAN));
		self::assertSame(false, Scalars::parse(0, Scalars::BOOLEAN));
		self::assertSame(false, Scalars::parse(0.0, Scalars::BOOLEAN));
		self::assertSame(false, Scalars::parse(0.000000, Scalars::BOOLEAN));
		self::assertSame(true, Scalars::parse("true", Scalars::BOOLEAN));
		self::assertSame(false, Scalars::parse("false", Scalars::BOOLEAN));
		self::assertException(function () {  Scalars::parse(new Person("John", "Doe", 18, 180), Scalars::INTEGER); }, InvalidArgumentException::class);
		self::assertException(function () {  Scalars::parse("true", "bool"); }, InvalidArgumentException::class);
		self::assertException(function () {  Scalars::parse("true", Scalars::INTEGER); }, RuntimeException::class);
	}

	public function testTryParseToScalarType()
	{
		self::assertSame(true, Scalars::tryParse("false", $result, Scalars::BOOLEAN));
		self::assertSame(false, $result);
		self::assertSame(true, Scalars::tryParse("54.7898", $result, Scalars::FLOAT));
		self::assertSame(54.7898, $result);
		self::assertSame(false, Scalars::tryParse("true", $output, Scalars::FLOAT));
	}
}
