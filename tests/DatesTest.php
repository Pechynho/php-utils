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
		self::assertException(function () { Dates::fromTimestamp("-6"); }, InvalidArgumentException::class);
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

	public function testDatabaseNow()
	{
		self::assertEquals((new DateTime())->format(Dates::DATABASE_DATETIME), Dates::databaseNow());
	}

	public function testDatabaseToday()
	{
		self::assertEquals((new DateTime())->format(Dates::DATABASE_DATE), Dates::databaseToday());
	}

	public function testIsYearLeap()
	{
		self::assertEquals(true, Dates::isYearLeap(2016));
		self::assertEquals(true, Dates::isYearLeap(1600));
		self::assertEquals(false, Dates::isYearLeap(1700));
	}

	public function testParse()
	{
		$tests = [
			[Dates::DATABASE_DATE, (new DateTime())->setDate(2000, 10, 1), "2000-10-01"],
			[Dates::DATABASE_DATE, (new DateTime())->setDate(2000, 10, 1), "2000-10-1"],
			[Dates::DATABASE_DATE, (new DateTime())->setDate(2000, 10, 1), "1.10.2000"],
			[Dates::DATABASE_DATE, (new DateTime())->setDate(2000, 10, 1), "10/1/2000"],
			[Dates::DATABASE_DATE, (new DateTime())->setDate(2000, 10, 1), "10/01/2000"],
			[Dates::DATABASE_DATETIME, (new DateTime())->setDate(2000, 10, 1)->setTime(12, 1, 0), "2000-10-01 12:01"],
			[Dates::DATABASE_DATETIME, (new DateTime())->setDate(2000, 10, 1)->setTime(12, 1, 0), "2000-10-01 12:01:00"],
			[Dates::DATABASE_DATETIME, (new DateTime())->setDate(2000, 10, 1)->setTime(12, 1, 5), "2000-10-01 12:01:5"],
			[Dates::DATABASE_DATETIME, (new DateTime())->setDate(2000, 10, 1)->setTime(12, 1, 5), "2000-10-01 12:01:05"],
			[Dates::DATABASE_DATETIME, (new DateTime())->setDate(2000, 10, 1)->setTime(12, 1, 50), "2000-10-01 12:01:50"],
		];
		foreach ($tests as $test)
		{
			$expected = $test[1]->format($test[0]);
			$given = Dates::parse($test[2])->format($test[0]);
			self::assertEquals($expected, $given);
		}
		self::assertEquals(970401710, Dates::parse("2000-10-01 12:01:50")->getTimestamp());
		self::assertException(function () { Dates::parse("test"); }, RuntimeException::class);
		self::assertException(function () { Dates::parse("      \t"); }, InvalidArgumentException::class);
	}

	public function testGetLastDayOfMonth()
	{
		self::assertEquals(31, Dates::getLastDayOfMonth(10, 2020));
		self::assertException(function () { Dates::getLastDayOfMonth(null, 2020); }, InvalidArgumentException::class);
		self::assertException(function () { Dates::getLastDayOfMonth(0, 2020); }, InvalidArgumentException::class);
	}

	public function testGetLastDateOfMonth()
	{
		self::assertSame("31.10.2020", Dates::getLastDateOfMonth(10, 2020)->format("d.m.Y"));
		self::assertException(function () { Dates::getLastDateOfMonth(null, 2020); }, InvalidArgumentException::class);
		self::assertException(function () { Dates::getLastDateOfMonth(0, 2020); }, InvalidArgumentException::class);
	}

	public function testTryParse()
	{
		self::assertSame(false, Dates::tryParse("adfafdadf", $result));
		self::assertSame(true, Dates::tryParse("2000-01-01", $result));
	}
}
