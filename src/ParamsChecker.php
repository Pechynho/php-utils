<?php


namespace Pechynho\Utility;


use InvalidArgumentException;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class ParamsChecker
{
	/**
	 * @param string $name
	 * @param array $arguments
	 */
	public static function __callStatic(string $name, array $arguments): void
	{
		if (Strings::startsWith($name, "is") && $name != "is") {
			if (count($arguments) < 1) {
				throw new InvalidArgumentException(sprintf('Invalid arguments count provided to %s:%s. Please provide at least two arguments - first one is parameter name, second one is value to check. e.g. %s:%s("$mode", $variable, __METHOD__);', ParamsChecker::class, $name, ParamsChecker::class, $name));
			}
			if (!is_string($arguments[0])) {
				throw new InvalidArgumentException('First parameter has to be string value representing parameter name.');
			}
			if (isset($arguments[2]) && $arguments[2] !== null && !is_string($arguments[2])) {
				throw new InvalidArgumentException('Third parameter has to be NULL or string value representing method or function.');
			}
			$name = Strings::substring($name, 2);
			$split = Strings::split($name, ["Or"]);
			$types = [];
			foreach ($split as $type) {
				$chars = Strings::toCharArray($type);
				if ($chars[0] === Strings::toLower($chars[0])) {
					$type = "Or" . $type;
				}
				$checkNotEmpty = Strings::startsWith($type, "NotEmpty");
				if ($checkNotEmpty) {
					$type = Strings::remove($type, 0, Strings::length("NotEmpty"));
				}
				$types[] = ["type" => $type, "checkNotEmpty" => $checkNotEmpty];
			}
			$success = false;
			foreach ($types as $item) {
				try {
					self::type($arguments[0], $arguments[1], $item["type"]);
					if ($item["checkNotEmpty"]) {
						self::notEmpty($arguments[0], $arguments[1]);
					}
					$success = true;
					break;
				}
				catch (InvalidArgumentException $exception) {
				}
			}
			if (!$success) {
				$message = "Parameter {parameter} is expected to be one of these types: ";
				foreach ($types as $item) {
					if (function_exists("is_" . Strings::toLower($item["type"]))) {
						$item["type"] = Strings::toLower($item["type"]);
					}
					$message .= ($item["checkNotEmpty"] ? "non empty " : "") . $item["type"] . ", ";
				}
				$message = Strings::remove($message, Strings::length($message) - 2) . ". Value passed to {parameter}: " . print_r($arguments[1], true);
				throw self::createException($arguments[0], $message, isset($arguments[2]) ? $arguments[2] : null);
			}
		} else {
			throw new RuntimeException("Unknown method '$name' called.");
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param string $type
	 * @param string|null $methodOrFunction
	 */
	public static function type(string $parameter, &$value, string $type, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		$function = "is_" . Strings::toLower($type);
		if (Scalars::isScalarTypeValid($type)) {
			if (Scalars::tryParse($value, $value, $type)) {
				return;
			}
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be a(n) $type. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		} else if (function_exists($function)) {
			if ($function($value)) {
				return;
			}
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be a(n) $type. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		}
		self::isInstanceOf($parameter, $value, $type, $methodOrFunction);
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param string|null $methodOrFunction
	 */
	public static function notWhiteSpaceOrNullString(string $parameter, $value, ?string $methodOrFunction = null): void
	{
		if (Strings::isNullOrWhiteSpace($parameter)) {
			throw self::createException('$parameter', sprintf('Parameter {parameter} cannot be NULL or an empty string ("") and cannot consists only from white-space characters. Value passed to {parameter}: %s', print_r($parameter, true)), __METHOD__);
		}
		if (Strings::isNullOrWhiteSpace($value)) {
			throw self::createException($parameter, sprintf('Parameter {parameter} cannot be NULL or an empty string ("") and cannot consists only from white-space characters. Value passed to {parameter}: %s', print_r($value, true)), $methodOrFunction);
		}
	}

	/**
	 * @param string $parameter
	 * @param string $message
	 * @param string|null $methodOrFunction
	 * @return InvalidArgumentException
	 */
	private static function createException(string $parameter, string $message, ?string $methodOrFunction = null): InvalidArgumentException
	{
		$message = ($methodOrFunction !== null ? sprintf("Wrong parameter value was provided to '%s'. ", $methodOrFunction) : "") . $message;
		$parameter = Strings::startsWith($parameter, "$") ? $parameter : "$" . $parameter;
		$message = Strings::replace($message, "{parameter}", $parameter);
		return new InvalidArgumentException($message);
	}

	/**
	 * @param string|null $methodOrFunction
	 * @param string $parameter
	 * @param object $value
	 * @param string $class
	 */
	public static function isInstanceOf(string $parameter, $value, string $class, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		self::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		self::classExists('$classToTest', $class, __METHOD__);
		if (is_subclass_of($value, $class, true) === false && is_a($value, $class, true) === false) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be an instance of a class '%s'. Value passed to {parameter}: %s", $class, print_r($value, true)), $methodOrFunction);
		}
	}

	/**
	 * @param string $parameter
	 * @param string $value
	 * @param string|null $methodOrFunction
	 */
	public static function classExists(string $parameter, string $value, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		if (!class_exists($value, true)) {
			throw self::createException($parameter, sprintf("Passed value to {parameter} is not a valid class name. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param string|null $methodOrFunction
	 */
	public static function notEmpty(string $parameter, $value, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		if (empty($value)) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is empty. Method empty({parameter}) was called to determine this. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param array $types
	 * @param string|null $methodOrFunction
	 */
	public static function types(string $parameter, &$value, array $types, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		self::isNotEmptyArray('$types', $types, __METHOD__);
		$success = false;
		foreach ($types as $type) {
			try {
				self::type($parameter, $value, $type, $methodOrFunction);
				$success = true;
				break;
			}
			catch (InvalidArgumentException $exception) {
			}
		}
		if (!$success) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be one of these types: %s. Value passed to {parameter}: %s", Strings::join($types, ",", "and"), print_r($value, true)), $methodOrFunction);
		}
	}

	/**
	 * @param string $parameter
	 * @param iterable $value
	 * @param int|null $minCount
	 * @param int|null $maxCount
	 * @param string|null $methodOrFunction
	 */
	public static function count(string $parameter, iterable $value, ?int $minCount, ?int $maxCount, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		$count = Arrays::count($value);
		if ($minCount === null && $maxCount !== null && $count > $maxCount) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to contain less or equal than $maxCount items. Count of passed {parameter} is %s (%s).", $count, print_r($value, true)), $methodOrFunction);
		} else if ($maxCount === null && $minCount !== null && $count < $minCount) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to contain more or equal than $minCount items. Count of passed {parameter} is %s (%s).", $count, print_r($value, true)), $methodOrFunction);
		} else if ($maxCount !== null && $minCount !== null && ($count < $minCount || $count > $maxCount)) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to contain items count in range from $minCount to $maxCount. Count of passed {parameter} is %s (%s).", $count, print_r($value, true)), $methodOrFunction);
		} else if ($maxCount === null && $minCount === null) {
			throw new InvalidArgumentException('Both parameter $minCount and $maxCount cannot be NULL at the same time.');
		}
	}

	/**
	 * @param string $parameter
	 * @param string $value
	 * @param int|null $minLength
	 * @param int|null $maxLength
	 * @param string|null $methodOrFunction
	 */
	public static function length(string $parameter, string $value, ?int $minLength, ?int $maxLength, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		$length = Strings::length($value);
		if ($minLength === null && $maxLength !== null && $length > $maxLength) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to have length lower than or equal $maxLength. Length of passed {parameter} is %s (%s).", $length, print_r($value, true)), $methodOrFunction);
		} else if ($maxLength === null && $minLength !== null && $length < $minLength) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to have length higher than or equal $minLength. Length of passed {parameter} is %s (%s).", $length, print_r($value, true)), $methodOrFunction);
		} else if ($maxLength !== null && $minLength !== null && ($length < $minLength || $length > $maxLength)) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to have length in range from $minLength to $maxLength. Length of passed {parameter} is %s (%s).", $length, print_r($value, true)), $methodOrFunction);
		} else if ($maxLength === null && $minLength === null) {
			throw new InvalidArgumentException('Both parameter $minLength and $maxLength cannot be NULL at the same time.');
		}
	}

	/**
	 * @param string $parameter
	 * @param int|float $value
	 * @param int|float|null $minValue
	 * @param int|float|null $maxValue
	 * @param string|null $methodOrFunction
	 */
	public static function range(string $parameter, $value, $minValue, $maxValue, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		self::isIntOrFloat('$value', $value, __METHOD__);
		self::isIntOrFloatOrNull('$minValue', $minValue, __METHOD__);
		self::isIntOrFloatOrNull('$maxValue', $maxValue, __METHOD__);
		if ($minValue === null && $maxValue !== null && $value > $maxValue) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be lower than or equal $maxValue. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		} else if ($maxValue === null && $minValue !== null && $value < $minValue) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be higher than or equal $minValue. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		} else if ($maxValue !== null && $minValue !== null && ($value < $minValue || $value > $maxValue)) {
			throw self::createException($parameter, sprintf("Parameter {parameter} is expected to be in range from $minValue to $maxValue. Value passed to {parameter}: %s", print_r($value, true)), $methodOrFunction);
		} else if ($maxValue === null && $minValue === null) {
			throw new InvalidArgumentException('Both parameter $minValue and $maxValue cannot be NULL at the same time.');
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param array $values
	 * @param string|null $methodOrFunction
	 */
	public static function inArray(string $parameter, $value, array $values, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		if (!in_array($value, $values, true)) {
			throw self::createException($parameter, sprintf("Invalid value provided to a parameter {parameter}. Parameter {parameter} expects one of these values: %s. Value passed to {parameter}: %s", Strings::join($values, ",", "and"), print_r($value, true)), $methodOrFunction);
		}
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param array $values
	 * @param string|null $methodOrFunction
	 */
	public static function notInArray(string $parameter, $value, array $values, ?string $methodOrFunction = null): void
	{
		self::notWhiteSpaceOrNullString('$parameter', $parameter, __METHOD__);
		if (in_array($value, $values, true)) {
			throw self::createException($parameter, sprintf("Invalid value provided to a parameter {parameter}. Parameter {parameter} expects not to be one of these values: %s. Value passed to {parameter}: %s", Strings::join($values, ",", "and"), print_r($value, true)), $methodOrFunction);
		}
	}
}
