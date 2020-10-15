<?php


namespace Pechynho\Utility;


use DateTime;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Dates
{
	/** @var string */
	public const DATABASE_DATETIME = "Y-m-d H:i:s";

	/** @var string */
	public const DATABASE_DATE = "Y-m-d";

	/**
	 * @param boolean $maximumTime
	 * @return DateTime
	 */
	public static function today(bool $maximumTime = false): DateTime
	{
		$today = Dates::now();
		if ($maximumTime)
		{
			$today->setTime(23, 59, 59);
		}
		else
		{
			$today->setTime(0, 0);
		}
		return $today;
	}

	/**
	 * @return DateTime
	 */
	public static function now(): DateTime
	{
		try
		{
			$today = new DateTime();
		}
		catch (Exception $exception)
		{
			throw new RuntimeException("Creating new blank instance [e.g. new DateTime()] of DateTime was not successful.");
		}
		return $today;
	}

	/**
	 * @return string
	 */
	public static function databaseNow(): string
	{
		return Dates::now()->format(Dates::DATABASE_DATETIME);
	}

	/**
	 * @return string
	 */
	public static function databaseToday(): string
	{
		return Dates::now()->format(Dates::DATABASE_DATE);
	}

	/**
	 * @param int $year
	 * @return bool
	 */
	public static function isYearLeap(int $year): bool
	{
		return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
	}

	/**
	 * @author Modified algorithm from: https://www.itnetwork.cz/php/knihovny/php-tutorial-dokonceni-knihovny-dateutils-pro-cesky-datum-a-cas
	 *
	 * @param string|int $value
	 * @return DateTime
	 */
	public static function parse($value): DateTime
	{
		if (Scalars::tryParse($value, $value, Scalars::INT))
		{
			return Dates::fromTimestamp($value);
		}
		ParamsChecker::notWhiteSpaceOrNullString('$value', $value, __METHOD__);
		if (mb_substr_count($value, ':') == 1)
		{
			$value .= ':00';
		}
		$patterns = ['/([\.\:\/])\s+/', '/\s+([\.\:\/])/', '/\s{2,}/'];
		$replacements = ['\1', '\1', ' '];
		$value = trim(preg_replace($patterns, $replacements, $value));
		$patterns = ['/^0(\d+)/', '/([\.\/])0(\d+)/'];
		$replacements = ['\1', '\1\2'];
		$value = preg_replace($patterns, $replacements, $value);
		try
		{
			$dateTime = new DateTime($value);
			$errors = DateTime::getLastErrors();
			if ($errors["warning_count"] > 0)
			{
				throw new RuntimeException(sprintf("During creating instance of %s from value %s were raised these warnings: %s", DateTime::class, $value, Strings::join($errors["warning"], ", ", " and ")));
			}
		}
		catch (Exception $exception)
		{
			if ($exception instanceof RuntimeException)
			{
				throw $exception;
			}
			throw new RuntimeException(sprintf('Creating instance of %s from value %s failed.', DateTime::class, $value));
		}
		return $dateTime;
	}

	/**
	 * @param int $timestamp
	 * @return DateTime
	 */
	public static function fromTimestamp(int $timestamp): DateTime
	{
		ParamsChecker::range('$timestamp', $timestamp, 0, null, __METHOD__);
		try
		{
			$dateTime = new DateTime("@" . $timestamp);
		}
		catch (Exception $exception)
		{
			throw new RuntimeException(sprintf("Creating new instance of DateTime from timestamp '%s' was not successful.", $timestamp));
		}
		return $dateTime;
	}

	/**
	 * @param int|null $month
	 * @param int|null $year
	 * @return int
	 */
	public static function getLastDayOfMonth(?int $month = null, ?int $year = null): int
	{
		return (int)self::getLastDateOfMonth($month, $year)->format("j");
	}

	/**
	 * @param int|null $month
	 * @param int|null $year
	 * @return DateTime
	 */
	public static function getLastDateOfMonth(?int $month = null, ?int $year = null): DateTime
	{
		ParamsChecker::range('$month', $month, 1, 12, __METHOD__);
		ParamsChecker::range('$year', $year, 1900, null, __METHOD__);
		if ($month === null && $year !== null)
		{
			throw new InvalidArgumentException('Parameter $month cannot be NULL when $year has a value.');
		}
		$now = self::now();
		if ($month === null)
		{
			$month = $now->format("n");
		}
		if ($year === null)
		{
			$year = $now->format("Y");
		}
		try
		{
			return new DateTime("last day of {$year}-{$month}");
		}
		catch (Exception $e)
		{
			throw new RuntimeException("Creating instance [e.g. new DateTime('last day of {$year}-{$month}')] of DateTime was not successful.");
		}
	}
}
