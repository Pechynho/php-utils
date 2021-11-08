<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use Pechynho\Utility\Formatting;
use PHPUnit\Framework\TestCase;
use Pechynho\Test\Traits\AssertExceptionTrait;

class FormattingTest extends TestCase
{
	use AssertExceptionTrait;

	public function testFormatFileSize()
	{
		self::assertEquals("10.21 MB", Formatting::formatFileSize(10207519));
		self::assertEquals("9.73 MiB", Formatting::formatFileSize(10207519, null, null, false));
		self::assertEquals("10207.52 kB", Formatting::formatFileSize(10207519, "kB"));
		self::assertEquals("9968.28 KiB", Formatting::formatFileSize(10207519, "KiB"));
		self::assertEquals("0.00 B", Formatting::formatFileSize(0));
		self::assertException(function () { Formatting::formatFileSize(-5); }, InvalidArgumentException::class);
	}

	public function testFormatNumber()
	{
		self::assertEquals("10 000.00", Formatting::formatNumber(10000, 2, ".", " ", false));
		self::assertEquals("10 000", Formatting::formatNumber(10000, 2, ".", " ", true));
		self::assertEquals("10,000.56", Formatting::formatNumber(10000.56, 2, ".", ",", true));
		self::assertEquals("10,000.5", Formatting::formatNumber(10000.50, 2, ".", ",", true));
		self::assertEquals("10,000.50", Formatting::formatNumber(10000.50, 2, ".", ",", false));self::assertException(function () { Formatting::formatNumber(50, 2, ",,"); }, InvalidArgumentException::class);
		self::assertException(function () { Formatting::formatNumber(50, 2, ",", "  "); }, InvalidArgumentException::class);
	}
}
