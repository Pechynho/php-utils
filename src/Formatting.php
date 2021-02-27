<?php


namespace Pechynho\Utility;


use InvalidArgumentException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Formatting
{
	/** @var string[] */
	const SIZE_SI_UNITS = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];

	/** @var string[] */
	const SIZE_BINARY_UNITS = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

	/**
	 * @param int|float $number
	 * @param int $decimals
	 * @param string $decimalPoint
	 * @param string $thousandsSeparator
	 * @param bool $removeTrailingZeroes
	 * @return string
	 */
	public static function formatNumber($number, $decimals = 0, $decimalPoint = ",", $thousandsSeparator = " ", $removeTrailingZeroes = false)
	{
		ParamsChecker::isIntOrFloat('$number', $number, __METHOD__);
		if (!is_int($decimals)) {
			throw new InvalidArgumentException('Parameter $decimals has to be type of int.');
		}
		if (!is_string($decimalPoint)) {
			throw new InvalidArgumentException('Parameter $decimalPoint has to be type of string.');
		}
		if (!is_string($thousandsSeparator)) {
			throw new InvalidArgumentException('Parameter $thousandsSeparator has to be type of string.');
		}
		if (!is_bool($removeTrailingZeroes)) {
			throw new InvalidArgumentException('Parameter $removeTrailingZeroes has to be type of boolean.');
		}
		if (Strings::length($decimalPoint) != 1) {
			throw new InvalidArgumentException('Parameter $decimalPoint has to be one character.');
		}
		if (Strings::length($thousandsSeparator) != 1) {
			throw new InvalidArgumentException('Parameter $thousandsSeparator has to be one character.');
		}
		$formattedNumber = number_format($number, $decimals, $decimalPoint, $thousandsSeparator);
		if ($removeTrailingZeroes && Strings::contains($formattedNumber, $decimalPoint)) {
			$formattedNumber = Strings::trimEnd($formattedNumber, ["0"]);
			$length = Strings::length($formattedNumber);
			if ($formattedNumber[$length - 1] === $decimalPoint) {
				$formattedNumber = Strings::substring($formattedNumber, 0, Strings::length($formattedNumber) - 1);
			}
		}
		return $formattedNumber;
	}

	/**
	 * @param int $bytes
	 * @param string|null $unit
	 * @param string|null $format
	 * @param bool $useSI
	 * @return string
	 */
	public static function formatFileSize($bytes, $unit = null, $format = null, $useSI = true)
	{
		ParamsChecker::isInt('$bytes', $bytes, __METHOD__);
		if (!is_string($unit) && $unit !== null) {
			throw new InvalidArgumentException('Parameter $unit has to be type of string or NULL.');
		}
		if (!is_string($format) && $format !== null) {
			throw new InvalidArgumentException('Parameter $format has to be type of string or NULL.');
		}
		if (!is_bool($useSI)) {
			throw new InvalidArgumentException('Parameter $useSI has to be type of boolean.');
		}
		if ($bytes < 0) {
			throw new InvalidArgumentException('Parameter $bytes has to be greater or equal to 0.');
		}
		if ($unit !== null && !in_array($unit, Formatting::SIZE_SI_UNITS, true) && !in_array($unit, Formatting::SIZE_BINARY_UNITS, true)) {
			throw new InvalidArgumentException('Invalid value of parameter $unit.');
		}
		$format = $format === null ? '%01.2f %s' : (string)$format;
		if ($useSI == false || (!Strings::isNullOrWhiteSpace($unit) && Strings::contains($unit, "i"))) {
			$units = Formatting::SIZE_BINARY_UNITS;
			$mod = 1024;
		} else {
			$units = Formatting::SIZE_SI_UNITS;
			$mod = 1000;
		}
		$power = Arrays::keyOf($units, $unit);
		if ($power === null) {
			$power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		}
		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}
}
