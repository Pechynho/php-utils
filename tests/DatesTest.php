<?php

namespace Pechynho\Test;

use DateTime;
use InvalidArgumentException;
use Pechynho\Utility\Dates;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use VladaHejda\AssertException;

class DatesTest extends TestCase
{
	use AssertException;

	public function testFromTimestamp()
	{
		self::assertEquals(new DateTime("@0"), Dates::fromTimestamp("0"));
		self::assertException(function () {  Dates::fromTimestamp("-6"); }, InvalidArgumentException::class);
	}

	public function testNow()
	{
		self::assertEquals((new DateTime())->setTime(0, 0), Dates::now()->setTime(0, 0));
	}

	public function testToday()
	{
		self::assertEquals((new DateTime())->setTime(0, 0), Dates::today());
		self::assertEquals((new DateTime())->setTime(23, 59, 59), Dates::today(true));
	}
}
