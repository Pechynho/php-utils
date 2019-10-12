<?php


namespace Pechynho\Utility;


use Exception;
use InvalidArgumentException;
use RuntimeException;

class Scalars
{
	public const INTEGER = "INTEGER";

	public const FLOAT = "FLOAT";

	public const STRING = "STRING";

	public const BOOLEAN = "BOOLEAN";

	public static function isScalarTypeValid(string $scalarType)
	{
		return in_array($scalarType, [Scalars::BOOLEAN, Scalars::INTEGER, Scalars::FLOAT, Scalars::STRING]);
	}

	/**
	 * @param string|int|float|boolean $scalarValue
	 * @param string                   $scalarType
	 * @return string|int|float|boolean
	 */
	public static function parse($scalarValue, string $scalarType)
	{
		if (!is_scalar($scalarValue))
		{
			throw new InvalidArgumentException('Parameter $scalarType is not scalar type.');
		}
		if (!Scalars::isScalarTypeValid($scalarType))
		{
			throw new InvalidArgumentException('Unknown value given to parameter $scalarType.');
		}
		$config = [
			Scalars::INTEGER => FILTER_VALIDATE_INT,
			Scalars::FLOAT   => FILTER_VALIDATE_FLOAT,
			Scalars::BOOLEAN => FILTER_VALIDATE_BOOLEAN
		];
		if ($scalarType == Scalars::STRING)
		{
			if (is_string($scalarValue))
			{
				return $scalarValue;
			}
			else if (is_bool($scalarValue))
			{
				return $scalarValue === true ? "true" : "false";
			}
			return (string)$scalarValue;
		}
		if (is_string($scalarValue))
		{
			$scalarValue = Strings::trim($scalarValue);
		}
		if ($scalarType == Scalars::BOOLEAN && ($scalarValue === "0" || Strings::toLower($scalarValue) === "false" || $scalarValue === 0))
		{
			return false;
		}
		$parsedValue = filter_var($scalarValue, $config[$scalarType]);
		if ($parsedValue === false) throw new RuntimeException(sprintf("Parameter %s containing value '%s' couldn't be parsed to given scalar type '%s'.", '$scalarTypeValue', $scalarValue, $scalarType));
		return $parsedValue;
	}

	/**
	 * @param mixed  $scalarValue
	 * @param mixed  $result
	 * @param string $scalarType
	 * @return bool
	 */
	public static function tryParse($scalarValue, &$result, string $scalarType)
	{
		try
		{
			$result = Scalars::parse($scalarValue, $scalarType);
			return true;
		}
		catch (Exception $exception)
		{
			return false;
		}
	}
}