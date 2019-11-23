<?php


namespace Pechynho\Utility;


use InvalidArgumentException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Formatting
{
	/** @var string[] */
	public const SIZE_SI_UNITS = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];

	/** @var string[] */
	public const SIZE_BINARY_UNITS = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

	/**
	 * @param int|float $number
	 * @param int       $decimals
	 * @param string    $decimalPoint
	 * @param string    $thousandsSeparator
	 * @param bool      $removeTrailingZeroes
	 * @return string
	 */
	public static function formatNumber($number, int $decimals = 0, string $decimalPoint = ",", string $thousandsSeparator = " ", bool $removeTrailingZeroes = false): string
	{
		if (!is_int($number) && !is_float($number))
		{
			throw new InvalidArgumentException('Parameter $number has to be type of int or float.');
		}
		if (Strings::length($decimalPoint) != 1)
		{
			throw new InvalidArgumentException('Parameter $decimalPoint has to be one character.');
		}
		if (Strings::length($thousandsSeparator) != 1)
		{
			throw new InvalidArgumentException('Parameter $thousandsSeparator has to be one character.');
		}
		$formattedNumber = number_format($number, $decimals, $decimalPoint, $thousandsSeparator);
		if ($removeTrailingZeroes && Strings::contains($formattedNumber, $decimalPoint))
		{
			$formattedNumber = Strings::trimEnd($formattedNumber, ["0"]);
			$length = Strings::length($formattedNumber);
			if ($formattedNumber[$length - 1] === $decimalPoint)
			{
				$formattedNumber = Strings::substring($formattedNumber, 0, Strings::length($formattedNumber) - 1);
			}
		}
		return $formattedNumber;
	}

	/**
	 * @param int         $bytes
	 * @param string|null $unit
	 * @param string|null $format
	 * @param bool        $useSI
	 * @return string
	 */
	public static function formatFileSize(int $bytes, ?string $unit = null, ?string $format = null, bool $useSI = true): string
	{
		if ($bytes < 0)
		{
			throw new InvalidArgumentException('Parameter $bytes has to be greater or equal to 0.');
		}
		if ($unit !== null && !in_array($unit, Formatting::SIZE_SI_UNITS, true) && !in_array($unit, Formatting::SIZE_BINARY_UNITS, true))
		{
			throw new InvalidArgumentException('Invalid value of parameter $unit.');
		}
		$format = $format === null ? '%01.2f %s' : (string)$format;
		if ($useSI == false || (!Strings::isNullOrWhiteSpace($unit) && Strings::contains($unit, "i")))
		{
			$units = Formatting::SIZE_BINARY_UNITS;
			$mod = 1024;
		}
		else
		{
			$units = Formatting::SIZE_SI_UNITS;
			$mod = 1000;
		}
		$power = Arrays::keyOf($units, $unit);
		if ($power === null) $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}
}
