<?php


namespace Pechynho\Utility;


use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Scalars
{
	/** @var string */
	const INTEGER = "INTEGER";

	/** @var string */
	const FLOAT = "FLOAT";

	/** @var string */
	const STRING = "STRING";

	/** @var string */
	const BOOLEAN = "BOOLEAN";

	/** @var string */
	const BOOL = "BOOL";

	/** @var string */
	const INT = "INT";

	/**
	 * @param mixed $scalarValue
	 * @param mixed $result
	 * @param string $scalarType
	 * @return bool
	 */
	public static function tryParse($scalarValue, &$result, $scalarType)
	{
		try {
			$result = Scalars::parse($scalarValue, $scalarType);
			return true;
		}
		catch (Exception $exception) {
			return false;
		}
	}

	/**
	 * @param string|int|float|boolean $scalarValue
	 * @param string $scalarType
	 * @return string|int|float|boolean
	 */
	public static function parse($scalarValue, $scalarType)
	{
		if (!is_scalar($scalarValue)) {
			throw new InvalidArgumentException('Parameter $scalarType is not scalar type.');
		}
		if (!Scalars::isScalarTypeValid($scalarType)) {
			throw new InvalidArgumentException('Unknown value given to parameter $scalarType.');
		}
		$scalarType = Strings::toUpper($scalarType);
		$config = [
			Scalars::INTEGER => FILTER_VALIDATE_INT,
			Scalars::FLOAT => FILTER_VALIDATE_FLOAT,
			Scalars::BOOLEAN => FILTER_VALIDATE_BOOLEAN,
			Scalars::BOOL => FILTER_VALIDATE_BOOLEAN,
			Scalars::INT => FILTER_VALIDATE_INT
		];
		if ($scalarType == Scalars::STRING) {
			if (is_string($scalarValue)) {
				return $scalarValue;
			} else if (is_bool($scalarValue)) {
				return $scalarValue === true ? "true" : "false";
			}
			return (string)$scalarValue;
		}
		if (is_string($scalarValue)) {
			$scalarValue = Strings::trim($scalarValue);
		}
		if (($scalarType == Scalars::BOOLEAN || $scalarType == Scalars::BOOL) && ($scalarValue === "0" || Strings::toLower($scalarValue) === "false" || $scalarValue === 0 || $scalarValue === 0.0 || $scalarValue === false)) {
			return false;
		}
		$parsedValue = filter_var($scalarValue, $config[$scalarType]);
		if ($parsedValue === false) {
			throw new RuntimeException(sprintf("Parameter %s containing value '%s' couldn't be parsed to given scalar type '%s'.", '$scalarTypeValue', $scalarValue, $scalarType));
		}
		return $parsedValue;
	}

	/**
	 * @param string $scalarType
	 * @return bool
	 */
	public static function isScalarTypeValid($scalarType)
	{
		$scalarType = Strings::toUpper($scalarType);
		return in_array($scalarType, [Scalars::BOOLEAN, Scalars::INTEGER, Scalars::FLOAT, Scalars::STRING, Scalars::BOOL, Scalars::INT]);
	}
}
