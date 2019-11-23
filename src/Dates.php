<?php


namespace Pechynho\Utility;


use DateTime;
use Exception;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Dates
{
	/** @var string */
	const DATABASE_DATETIME = "Y-m-d H:i:s";

	/** @var string */
	const DATABASE_DATE = "Y-m-d";

	/**
	 * @param boolean $maximumTime
	 * @return DateTime
	 */
	public static function today($maximumTime = false)
	{
		ParamsChecker::isBool('$maximumTime', $maximumTime, __METHOD__);
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
	public static function now()
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
	public static function databaseNow()
	{
		return Dates::now()->format(Dates::DATABASE_DATETIME);
	}

	/**
	 * @return string
	 */
	public static function databaseToday()
	{
		return Dates::now()->format(Dates::DATABASE_DATE);
	}

	/**
	 * @param int $year
	 * @return bool
	 */
	public static function isYearLeap($year)
	{
		ParamsChecker::isInt('$year', $year, __METHOD__);
		return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)));
	}

	/**
	 * @author Modified algorithm from: https://www.itnetwork.cz/php/knihovny/php-tutorial-dokonceni-knihovny-dateutils-pro-cesky-datum-a-cas
	 *
	 * @param string|int $value
	 * @return DateTime
	 */
	public static function parse($value)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$value', $value, __METHOD__);
		if (Scalars::tryParse($value, $value, Scalars::INT))
		{
			return Dates::fromTimestamp($value);
		}
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
	public static function fromTimestamp($timestamp)
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
}
