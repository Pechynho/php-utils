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
