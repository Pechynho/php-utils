<?php


namespace Pechynho\Utility;


use InvalidArgumentException;
use OutOfRangeException;
use Traversable;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Strings
{
	/** @var string */
	const EMPTY_STRING = "";

	/** @var string */
	const COMPARE_CASE_SENSITIVE = "COMPARE_CASE_SENSITIVE";

	/** @var string */
	const COMPARE_CASE_INSENSITIVE = "COMPARE_CASE_INSENSITIVE";

	/** @var string[] */
	const TRIM_WHITE_SPACE_CHARS = [" ", "\t", "\n", "\r", "\0", "\x0B"];

	/** @var string */
	const SLUGIFY_NORMAL = "SLUGIFY_NORMAL";

	/** @var string */
	const SLUGIFY_FILENAME = "SLUGIFY_FILENAME";

	/** @var string */
	const SLUGIFY_URL = "SLUGIFY_URL";

	/** @var string */
	const CASE_PASCAL = "CASE_PASCAL";

	/** @var string */
	const CASE_CAMEL = "CASE_CAMEL";

	/**
	 * @param mixed  $value
	 * @param string $parameterName
	 * @param bool   $canValueBeNull
	 * @return string
	 */
	private static function parseToString($value, $parameterName, $canValueBeNull = false)
	{
		if (!$canValueBeNull && !is_scalar($value))
		{
			throw new InvalidArgumentException('Parameter ' . $parameterName . ' has to be one of scalar types.');
		}
		if ($canValueBeNull && $value !== null && !is_scalar($value))
		{
			throw new InvalidArgumentException('Parameter ' . $parameterName . ' has to be NULL or one of scalar types.');
		}
		return $canValueBeNull && $value === null ? null : (string)$value;
	}

	/**
	 * Indicates whether the specified string is null or an empty string ("").
	 * @param string|null $subject The string to test.
	 * @return bool Returns true if the $subject parameter is null or an empty string (""); otherwise, false.
	 */
	public static function isNullOrEmpty($subject)
	{
		$subject = Strings::parseToString($subject, '$subject', true);
		return $subject === null || $subject === Strings::EMPTY_STRING;
	}

	/**
	 * Indicates whether a specified string is null, empty, or consists only of white-space characters.
	 * @param string|null $subject The string to test.
	 * @return bool Returns true if the $subject parameter is null, empty string ("") or consists exclusively of white-space characters.
	 */
	public static function isNullOrWhiteSpace($subject)
	{
		$subject = Strings::parseToString($subject, '$subject', true);
		return $subject === null || Strings::trim($subject) === Strings::EMPTY_STRING;
	}

	/**
	 * Compares two strings by their value.
	 * @param string $strA First string to compare.
	 * @param string $strB Second string to compare.
	 * @param string $type Switch between case-sensitive and case-insensitive comparison.
	 * @return int Return -1 if $strA is lesser than $strB; 1 if $strA is greater than $strB; otherwise 0.
	 */
	public static function compare($strA, $strB, $type = Strings::COMPARE_CASE_SENSITIVE)
	{
		$strA = Strings::parseToString($strA, '$strA');
		$strB = Strings::parseToString($strB, '$strB');
		if (!in_array($type, [Strings::COMPARE_CASE_SENSITIVE, Strings::COMPARE_CASE_INSENSITIVE]))
		{
			throw new InvalidArgumentException('Invalid value for argument $type.');
		}
		return $type === Strings::COMPARE_CASE_SENSITIVE ? strcmp($strA, $strB) : strcasecmp($strA, $strB);
	}

	/**
	 * Returns a value indicating whether a specified substring occurs within passed string.
	 * @param string $subject The string to seek in.
	 * @param string $value   The substring to seek.
	 * @return bool Returns true if the $value parameter occurs within $subject parameter; otherwise false.
	 */
	public static function contains($subject, $value)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$value = Strings::parseToString($value, '$value');
		if ($value === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $value cannot be empty string.');
		}
		return Strings::indexOf($subject, $value) > -1;
	}

	/**
	 * Indicates if string ends with given value.
	 * @param string $subject The string to seek in.
	 * @param string $value   The substring to seek at end.
	 * @return bool Returns true if the $subject parameter ends with $value parameter; otherwise false;
	 */
	public static function endsWith($subject, $value)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$value = Strings::parseToString($value, '$value');
		return $value === "" || (($temp = mb_strlen($subject) - mb_strlen($value)) >= 0 && strpos($subject, $value, $temp) !== false);
	}

	/**
	 * Returns integer representing index on which given value occurs in given string.
	 * @param string $subject    The string to seek in.
	 * @param string $value      The value to seek.
	 * @param int    $startIndex Offset value from start of the string.
	 * @return int Returns value indicating on which index occurs $value in $subject; if $subject doesn't contain $value, then it returns -1.
	 */
	public static function indexOf($subject, $value, $startIndex = 0)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$value = Strings::parseToString($value, '$value');
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			return -1;
		}
		if ($value === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $value cannot be empty string.');
		}
		if ($startIndex < 0 || $startIndex >= Strings::length($subject))
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		$result = mb_strpos($subject, $value, $startIndex);
		return $result === false ? -1 : $result;
	}

	/**
	 * @param string            $subject
	 * @param array|Traversable $values
	 * @param int               $startIndex
	 * @return int
	 */
	public static function indexOfAny($subject, $values, $startIndex = 0)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!Arrays::isIterable($values))
		{
			throw new InvalidArgumentException('Parameter $values has to be type of array or Traversable.');
		}
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			return -1;
		}
		if (empty($values))
		{
			throw new InvalidArgumentException('Parameter $values cannot be empty array.');
		}
		if ($startIndex < 0 || $startIndex >= Strings::length($subject))
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		foreach ($values as $value)
		{
			$index = Strings::indexOf($subject, $value, $startIndex);
			if ($index > -1)
			{
				return $index;
			}
		}
		return -1;
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @param int    $startIndex
	 * @return string
	 */
	public static function insert($subject, $value, $startIndex)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$value = Strings::parseToString($value, '$value');
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $subject cannot be empty string.');
		}
		if ($value === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $value cannot be empty string.');
		}
		if ($startIndex < 0 || $startIndex >= Strings::length($subject))
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		return substr_replace($subject, $value, $startIndex, 0);
	}

	/**
	 * @param array|Traversable $subject
	 * @param string            $separator
	 * @param string|null       $lastSeparator
	 * @return string
	 */
	public static function join($subject, $separator, $lastSeparator = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		$separator = Strings::parseToString($separator, '$separator');
		$lastSeparator = Strings::parseToString($lastSeparator, '$lastSeparator', true);
		if (is_array($subject))
		{
			if ($lastSeparator === null || $separator === $lastSeparator)
			{
				return implode($separator, $subject);
			}
			if (function_exists("array_key_first"))
			{
				$lastKey = array_key_last($subject);
				$lastValue = $subject[$lastKey];
				unset($subject[$lastKey]);
				if (empty($subject))
				{
					return $lastValue;
				}
				return implode($separator, $subject) . $lastSeparator . $lastValue;
			}
		}
		$parts = [];
		$partsCount = 0;
		foreach ($subject as $item)
		{
			$parts[] = ["value" => $item, "separator" => $separator];
			$partsCount++;
		}
		if ($partsCount != 0)
		{
			$parts[$partsCount - 1]["separator"] = $lastSeparator === null ? $separator : $lastSeparator;
			$parts[0]["separator"] = Strings::EMPTY_STRING;
		}
		$output = Strings::EMPTY_STRING;
		foreach ($parts as $part)
		{
			$output .= $part["separator"] . $part["value"];
		}
		return $output;
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @param int    $startIndex
	 * @return int
	 */
	public static function lastIndexOf($subject, $value, $startIndex = 0)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$value = Strings::parseToString($value, '$value');
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			return -1;
		}
		if ($value === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $value cannot be empty string.');
		}
		if ($startIndex < 0 || $startIndex >= Strings::length($subject))
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		$result = mb_strrpos($subject, $value, $startIndex);
		return $result === false ? -1 : $result;
	}

	/**
	 * @param string            $subject
	 * @param array|Traversable $values
	 * @param int               $startIndex
	 * @return int
	 */
	public static function lastIndexOfAny($subject, $values, $startIndex = 0)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!Arrays::isIterable($values))
		{
			throw new InvalidArgumentException('Parameter $values has to be type of array or Traversable.');
		}
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			return -1;
		}
		if (empty($values))
		{
			throw new InvalidArgumentException('Parameter $values cannot be empty array.');
		}
		if ($startIndex < 0 || $startIndex >= Strings::length($subject))
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		foreach ($values as $value)
		{
			$index = Strings::lastIndexOf($subject, $value, $startIndex);
			if ($index > -1)
			{
				return $index;
			}
		}
		return -1;
	}

	/**
	 * @param string $subject
	 * @return int
	 */
	public static function length($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return mb_strlen($subject);
	}

	/**
	 * @param string $subject
	 * @param int    $totalWidth
	 * @param string $paddingChar
	 * @return string
	 */
	public static function padLeft($subject, $totalWidth, $paddingChar = " ")
	{
		$subject = Strings::parseToString($subject, '$subject');
		$paddingChar = Strings::parseToString($paddingChar, '$paddingChar');
		if (!is_int($totalWidth))
		{
			throw new InvalidArgumentException('Parameter $totalWidth has to be type of int.');
		}
		if (Strings::length($paddingChar) != 1)
		{
			throw new InvalidArgumentException('Parameter $paddingChar has to be single character.');
		}
		$iterations = $totalWidth - Strings::length($subject);
		for ($i = 0; $i < $iterations; $i++)
		{
			$subject = $paddingChar . $subject;
		}
		return $subject;
	}

	/**
	 * @param string $subject
	 * @param int    $totalWidth
	 * @param string $paddingChar
	 * @return string
	 */
	public static function padRight($subject, $totalWidth, $paddingChar = " ")
	{
		$subject = Strings::parseToString($subject, '$subject');
		$paddingChar = Strings::parseToString($paddingChar, '$paddingChar');
		if (!is_int($totalWidth))
		{
			throw new InvalidArgumentException('Parameter $totalWidth has to be type of int.');
		}
		if (Strings::length($paddingChar) != 1)
		{
			throw new InvalidArgumentException('Parameter $paddingChar has to be single character.');
		}
		$iterations = $totalWidth - Strings::length($subject);
		for ($i = 0; $i < $iterations; $i++)
		{
			$subject = $subject . $paddingChar;
		}
		return $subject;
	}

	/**
	 * @param string   $subject
	 * @param int      $startIndex
	 * @param int|null $length
	 * @return string
	 */
	public static function remove($subject, $startIndex, $length = null)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if (!is_int($length) && $length != null)
		{
			throw new InvalidArgumentException('Parameter $length has to be type of int or NULL.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('$subject $value cannot be empty string.');
		}
		if ($startIndex < 0 || $startIndex >= Strings::length($subject))
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		$subjectLength = Strings::length($subject);
		if ($length !== null && $startIndex + $length > $subjectLength)
		{
			throw new OutOfRangeException('Parameter $length is out of range.');
		}
		$part1 = $startIndex == 0 ? Strings::EMPTY_STRING : Strings::substring($subject, 0, $startIndex);
		$part2 = $length === null || $startIndex + $length == $subjectLength ? Strings::EMPTY_STRING : Strings::substring($subject, $startIndex + $length);
		return $part1 . $part2;
	}

	/**
	 * @param string $subject
	 * @param string $oldValue
	 * @param string $newValue
	 * @return string
	 */
	public static function replace($subject, $oldValue, $newValue)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$oldValue = Strings::parseToString($oldValue, '$oldValue');
		$newValue = Strings::parseToString($newValue, '$newValue');
		if ($oldValue === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $oldValue cannot be empty string.');
		}
		return str_replace($oldValue, $newValue, $subject);

	}

	/**
	 * @param string   $subject
	 * @param string[] $replacements
	 * @return string
	 */
	public static function replaceMultiple($subject, $replacements)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_array($replacements))
		{
			throw new InvalidArgumentException('Parameter $replacements has to be type of array.');
		}
		$oldValues = array_keys($replacements);
		$newValues = array_values($replacements);
		if (in_array("", $oldValues))
		{
			throw new InvalidArgumentException('Keys in parameter $replacements should be non-empty string values which should be replaced.');
		}
		return str_replace($oldValues, $newValues, $subject);
	}

	/**
	 * @param string   $subject
	 * @param string[] $separators
	 * @param bool     $removeEmptyEntries
	 * @return array
	 */
	public static function split($subject, $separators, $removeEmptyEntries = true)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_array($separators))
		{
			throw new InvalidArgumentException('Parameter $separators has to be type of array.');
		}
		if (!is_bool($removeEmptyEntries))
		{
			throw new InvalidArgumentException('Parameter $removeEmptyEntries has to be type of boolean.');
		}
		if (empty($separators))
		{
			throw new InvalidArgumentException('Parameter $separators has to contain at least one value.');
		}
		$separatorsCount = count($separators);
		$replacements = [];
		for ($i = 0; $i < $separatorsCount; $i++)
		{
			if (!is_string($separators[$i]) || $separators[$i] === Strings::EMPTY_STRING)
			{
				throw new InvalidArgumentException("Value on index '{$i}' cannot be used as a separator.");
			}
			if ($i == 0) continue;
			$replacements[$separators[$i]] = $separators[0];
		}
		if (!empty($replacements)) $subject = Strings::replaceMultiple($subject, $replacements);
		$values =  $removeEmptyEntries ? array_diff(explode($separators[0], $subject), [Strings::EMPTY_STRING]) : explode($separators[0], $subject);
		return  array_values($values);
	}

	/**
	 * @param string $subject
	 * @return string[]
	 */
	public static function splitByCase($subject)
	{
		self::parseToString($subject, '$subject');
		$pattern = '/(?#! splitCamelCase Rev:20140412)
    			# Split camelCase "words". Two global alternatives. Either g1of2:
      			(?<=[a-z])      # Position is after a lowercase,
      			(?=[A-Z])       # and before an uppercase letter.
    			| (?<=[A-Z])    # Or g2of2; Position is after uppercase,
      			(?=[A-Z][a-z])  # and before upper-then-lower case.
    			/x';
		$values = preg_split($pattern, $subject);
		return $values;
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @return bool
	 */
	public static function startsWith($subject, $value)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$value = Strings::parseToString($value, '$value');
		return $value === "" || strncmp($subject, $value, mb_strlen($value)) === 0;
	}

	/**
	 * @param string   $subject
	 * @param int      $startIndex
	 * @param int|null $length
	 * @return string
	 */
	public static function substring($subject, $startIndex, $length = null)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_int($startIndex))
		{
			throw new InvalidArgumentException('Parameter $startIndex has to be type of int.');
		}
		if (!is_int($length) && $length != null)
		{
			throw new InvalidArgumentException('Parameter $length has to be type of int or NULL.');
		}
		if ($subject === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $subject cannot be empty string.');
		}
		$subjectLength = Strings::length($subject);
		if ($startIndex < 0 || $startIndex >= $subjectLength)
		{
			throw new OutOfRangeException('Parameter $startIndex is out of range.');
		}
		if ($length !== null && $startIndex + $length > $subjectLength)
		{
			throw new OutOfRangeException('Parameter $length is out of range.');
		}
		return mb_substr($subject, $startIndex, $length);
	}

	/**
	 * @param string $subject
	 * @return array
	 */
	public static function toCharArray($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return preg_split('//u', $subject, null, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function toLower($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return mb_strtolower($subject);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function toUpper($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return mb_strtoupper($subject);
	}

	/**
	 * @param string   $subject
	 * @param string[] $trimChars
	 * @return string
	 */
	public static function trim($subject, array $trimChars = Strings::TRIM_WHITE_SPACE_CHARS)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_array($trimChars))
		{
			throw new InvalidArgumentException('Parameter $trimChars has to be type of array.');
		}
		if (empty($trimChars))
		{
			throw new InvalidArgumentException('Parameter $trimChars cannot be empty array.');
		}
		return trim($subject, Strings::join($trimChars, ""));
	}

	/**
	 * @param string   $subject
	 * @param string[] $trimChars
	 * @return string
	 */
	public static function trimStart($subject, $trimChars = Strings::TRIM_WHITE_SPACE_CHARS)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_array($trimChars))
		{
			throw new InvalidArgumentException('Parameter $trimChars has to be type of array.');
		}
		if (empty($trimChars))
		{
			throw new InvalidArgumentException('Parameter $trimChars cannot be empty array.');
		}
		return ltrim($subject, Strings::join($trimChars, ""));
	}

	/**
	 * @param string   $subject
	 * @param string[] $trimChars
	 * @return string
	 */
	public static function trimEnd($subject, $trimChars = Strings::TRIM_WHITE_SPACE_CHARS)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_array($trimChars))
		{
			throw new InvalidArgumentException('Parameter $trimChars has to be type of array.');
		}
		if (empty($trimChars))
		{
			throw new InvalidArgumentException('Parameter $trimChars cannot be empty array.');
		}
		return rtrim($subject, Strings::join($trimChars, ""));
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function firstToUpper($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if ($subject === Strings::EMPTY_STRING)
		{
			return $subject;
		}
		if (Strings::length($subject) === 1)
		{
			return Strings::toUpper($subject);
		}
		return Strings::toUpper(Strings::substring($subject, 0, 1)) . Strings::substring($subject, 1);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function firstToLower($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if ($subject === Strings::EMPTY_STRING)
		{
			return $subject;
		}
		if (Strings::length($subject) === 1)
		{
			return Strings::toLower($subject);
		}
		return Strings::toLower(Strings::substring($subject, 0, 1)) . Strings::substring($subject, 1);
	}

	/**
	 * @param string $subject
	 * @param int    $maximumLength
	 * @return string
	 */
	public static function truncate($subject, $maximumLength)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!is_int($maximumLength))
		{
			throw new InvalidArgumentException('Parameter $maximumLength has to be type of int.');
		}
		if ($maximumLength < 4)
		{
			throw new InvalidArgumentException('Parameter $maximumLength has to be greater than 3.');
		}
		if (Strings::length($subject) > $maximumLength)
		{
			$subject = Strings::substring($subject, 0, $maximumLength - 3) . '...';
		}
		return $subject;
	}

	/**
	 * @param string $subject
	 * @param string $separator
	 * @param string $caseType
	 * @return string
	 */
	private static function convertToCase($subject, $separator, $caseType = Strings::CASE_PASCAL)
	{
		$result = str_replace(' ', '', mb_convert_case(str_replace($separator, ' ', $subject), MB_CASE_TITLE));
		if ($caseType === Strings::CASE_CAMEL)
		{
			$result = Strings::firstToLower($result);
		}

		return $result;
	}

	/**
	 * @param string $subject
	 * @param string $separator
	 * @return string
	 */
	private static function convertFromCase($subject, $separator)
	{
		return ltrim(mb_strtolower(preg_replace('/[A-Z]/', $separator . '$0', $subject)), $separator);
	}

	/**
	 * @param string $subject
	 * @param string $caseType
	 * @return string
	 */
	public static function dashesToCase($subject, $caseType = Strings::CASE_PASCAL)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!in_array($caseType, [Strings::CASE_CAMEL, Strings::CASE_PASCAL]))
		{
			throw new InvalidArgumentException('Invalid value passed to parameter $caseType.');
		}
		return self::convertToCase($subject, '-', $caseType);
	}

	/**
	 * @param string $subject
	 * @param string $caseType
	 * @return string
	 */
	public static function underscoresToCase($subject, $caseType = Strings::CASE_PASCAL)
	{
		$subject = Strings::parseToString($subject, '$subject');
		if (!in_array($caseType, [Strings::CASE_CAMEL, Strings::CASE_PASCAL]))
		{
			throw new InvalidArgumentException('Invalid value passed to parameter $caseType.');
		}
		return self::convertToCase($subject, '_', $caseType);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function caseToDashes($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return self::convertFromCase($subject, '-');
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function caseToUnderscores($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return self::convertFromCase($subject, '_');
	}

	/**
	 * @param string $subject
	 * @param string $separator
	 * @param string $slugifyType
	 * @param bool   $toLower
	 * @return  string
	 */
	public static function slugify($subject, $separator = "-", $slugifyType = Strings::SLUGIFY_NORMAL, $toLower = true)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$separator = Strings::parseToString($separator, '$separator');
		if (!is_bool($toLower))
		{
			throw new InvalidArgumentException('Parameter $toLower has to be type of boolean.');
		}
		if (!in_array($slugifyType, [Strings::SLUGIFY_NORMAL, Strings::SLUGIFY_FILENAME, Strings::SLUGIFY_URL]))
		{
			throw new InvalidArgumentException('Invalid value passed to parameter $slugifyType.');
		}
		if (Strings::length($separator) != 1)
		{
			throw new InvalidArgumentException('Parameter $separator has to be single character.');
		}
		$config = [
			Strings::SLUGIFY_NORMAL   => "/\W+/",
			Strings::SLUGIFY_FILENAME => '/[\/\\?%*:|"<>. ]+/',
			Strings::SLUGIFY_URL      => '/[!*\'();:@&=+,?#\[\]\/]+/'
		];
		if ($toLower)
		{
			$subject = Strings::toLower($subject);
		}
		$subject = Strings::replaceCzechSpecialCharsWithASCII($subject);
		$subject = preg_replace('!\s+!', $separator, $subject);
		$subject = preg_replace($config[$slugifyType], $separator, $subject);
		$subject = preg_replace('/[\\' . $separator . ']+/', $separator, $subject);
		$subject = Strings::trim($subject);
		return $subject;
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function stripHtmlTags($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$inside = false;
		$output = Strings::EMPTY_STRING;
		$characters = Strings::toCharArray($subject);
		$length = count($characters);
		for ($i = 0; $i < $length; $i++)
		{
			$char = $characters[$i];
			if ($char == "<")
			{
				$inside = true;
				continue;
			}
			else if ($char == ">")
			{
				$inside = false;
				continue;
			}
			if ($inside)
			{
				continue;
			}
			$output .= $char;
		}
		return $output;
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function reverse($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		return strrev($subject);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function replaceCzechSpecialCharsWithASCII($subject)
	{
		$subject = Strings::parseToString($subject, '$subject');
		$chars = [
			// Decompositions for Latin-1 Supplement
			chr(195) . chr(128)            => 'A', chr(195) . chr(129) => 'A',
			chr(195) . chr(130)            => 'A', chr(195) . chr(131) => 'A',
			chr(195) . chr(132)            => 'A', chr(195) . chr(133) => 'A',
			chr(195) . chr(135)            => 'C', chr(195) . chr(136) => 'E',
			chr(195) . chr(137)            => 'E', chr(195) . chr(138) => 'E',
			chr(195) . chr(139)            => 'E', chr(195) . chr(140) => 'I',
			chr(195) . chr(141)            => 'I', chr(195) . chr(142) => 'I',
			chr(195) . chr(143)            => 'I', chr(195) . chr(145) => 'N',
			chr(195) . chr(146)            => 'O', chr(195) . chr(147) => 'O',
			chr(195) . chr(148)            => 'O', chr(195) . chr(149) => 'O',
			chr(195) . chr(150)            => 'O', chr(195) . chr(153) => 'U',
			chr(195) . chr(154)            => 'U', chr(195) . chr(155) => 'U',
			chr(195) . chr(156)            => 'U', chr(195) . chr(157) => 'Y',
			chr(195) . chr(159)            => 's', chr(195) . chr(160) => 'a',
			chr(195) . chr(161)            => 'a', chr(195) . chr(162) => 'a',
			chr(195) . chr(163)            => 'a', chr(195) . chr(164) => 'a',
			chr(195) . chr(165)            => 'a', chr(195) . chr(167) => 'c',
			chr(195) . chr(168)            => 'e', chr(195) . chr(169) => 'e',
			chr(195) . chr(170)            => 'e', chr(195) . chr(171) => 'e',
			chr(195) . chr(172)            => 'i', chr(195) . chr(173) => 'i',
			chr(195) . chr(174)            => 'i', chr(195) . chr(175) => 'i',
			chr(195) . chr(177)            => 'n', chr(195) . chr(178) => 'o',
			chr(195) . chr(179)            => 'o', chr(195) . chr(180) => 'o',
			chr(195) . chr(181)            => 'o', chr(195) . chr(182) => 'o',
			chr(195) . chr(182)            => 'o', chr(195) . chr(185) => 'u',
			chr(195) . chr(186)            => 'u', chr(195) . chr(187) => 'u',
			chr(195) . chr(188)            => 'u', chr(195) . chr(189) => 'y',
			chr(195) . chr(191)            => 'y',
			// Decompositions for Latin Extended-A
			chr(196) . chr(128)            => 'A', chr(196) . chr(129) => 'a',
			chr(196) . chr(130)            => 'A', chr(196) . chr(131) => 'a',
			chr(196) . chr(132)            => 'A', chr(196) . chr(133) => 'a',
			chr(196) . chr(134)            => 'C', chr(196) . chr(135) => 'c',
			chr(196) . chr(136)            => 'C', chr(196) . chr(137) => 'c',
			chr(196) . chr(138)            => 'C', chr(196) . chr(139) => 'c',
			chr(196) . chr(140)            => 'C', chr(196) . chr(141) => 'c',
			chr(196) . chr(142)            => 'D', chr(196) . chr(143) => 'd',
			chr(196) . chr(144)            => 'D', chr(196) . chr(145) => 'd',
			chr(196) . chr(146)            => 'E', chr(196) . chr(147) => 'e',
			chr(196) . chr(148)            => 'E', chr(196) . chr(149) => 'e',
			chr(196) . chr(150)            => 'E', chr(196) . chr(151) => 'e',
			chr(196) . chr(152)            => 'E', chr(196) . chr(153) => 'e',
			chr(196) . chr(154)            => 'E', chr(196) . chr(155) => 'e',
			chr(196) . chr(156)            => 'G', chr(196) . chr(157) => 'g',
			chr(196) . chr(158)            => 'G', chr(196) . chr(159) => 'g',
			chr(196) . chr(160)            => 'G', chr(196) . chr(161) => 'g',
			chr(196) . chr(162)            => 'G', chr(196) . chr(163) => 'g',
			chr(196) . chr(164)            => 'H', chr(196) . chr(165) => 'h',
			chr(196) . chr(166)            => 'H', chr(196) . chr(167) => 'h',
			chr(196) . chr(168)            => 'I', chr(196) . chr(169) => 'i',
			chr(196) . chr(170)            => 'I', chr(196) . chr(171) => 'i',
			chr(196) . chr(172)            => 'I', chr(196) . chr(173) => 'i',
			chr(196) . chr(174)            => 'I', chr(196) . chr(175) => 'i',
			chr(196) . chr(176)            => 'I', chr(196) . chr(177) => 'i',
			chr(196) . chr(178)            => 'IJ', chr(196) . chr(179) => 'ij',
			chr(196) . chr(180)            => 'J', chr(196) . chr(181) => 'j',
			chr(196) . chr(182)            => 'K', chr(196) . chr(183) => 'k',
			chr(196) . chr(184)            => 'k', chr(196) . chr(185) => 'L',
			chr(196) . chr(186)            => 'l', chr(196) . chr(187) => 'L',
			chr(196) . chr(188)            => 'l', chr(196) . chr(189) => 'L',
			chr(196) . chr(190)            => 'l', chr(196) . chr(191) => 'L',
			chr(197) . chr(128)            => 'l', chr(197) . chr(129) => 'L',
			chr(197) . chr(130)            => 'l', chr(197) . chr(131) => 'N',
			chr(197) . chr(132)            => 'n', chr(197) . chr(133) => 'N',
			chr(197) . chr(134)            => 'n', chr(197) . chr(135) => 'N',
			chr(197) . chr(136)            => 'n', chr(197) . chr(137) => 'N',
			chr(197) . chr(138)            => 'n', chr(197) . chr(139) => 'N',
			chr(197) . chr(140)            => 'O', chr(197) . chr(141) => 'o',
			chr(197) . chr(142)            => 'O', chr(197) . chr(143) => 'o',
			chr(197) . chr(144)            => 'O', chr(197) . chr(145) => 'o',
			chr(197) . chr(146)            => 'OE', chr(197) . chr(147) => 'oe',
			chr(197) . chr(148)            => 'R', chr(197) . chr(149) => 'r',
			chr(197) . chr(150)            => 'R', chr(197) . chr(151) => 'r',
			chr(197) . chr(152)            => 'R', chr(197) . chr(153) => 'r',
			chr(197) . chr(154)            => 'S', chr(197) . chr(155) => 's',
			chr(197) . chr(156)            => 'S', chr(197) . chr(157) => 's',
			chr(197) . chr(158)            => 'S', chr(197) . chr(159) => 's',
			chr(197) . chr(160)            => 'S', chr(197) . chr(161) => 's',
			chr(197) . chr(162)            => 'T', chr(197) . chr(163) => 't',
			chr(197) . chr(164)            => 'T', chr(197) . chr(165) => 't',
			chr(197) . chr(166)            => 'T', chr(197) . chr(167) => 't',
			chr(197) . chr(168)            => 'U', chr(197) . chr(169) => 'u',
			chr(197) . chr(170)            => 'U', chr(197) . chr(171) => 'u',
			chr(197) . chr(172)            => 'U', chr(197) . chr(173) => 'u',
			chr(197) . chr(174)            => 'U', chr(197) . chr(175) => 'u',
			chr(197) . chr(176)            => 'U', chr(197) . chr(177) => 'u',
			chr(197) . chr(178)            => 'U', chr(197) . chr(179) => 'u',
			chr(197) . chr(180)            => 'W', chr(197) . chr(181) => 'w',
			chr(197) . chr(182)            => 'Y', chr(197) . chr(183) => 'y',
			chr(197) . chr(184)            => 'Y', chr(197) . chr(185) => 'Z',
			chr(197) . chr(186)            => 'z', chr(197) . chr(187) => 'Z',
			chr(197) . chr(188)            => 'z', chr(197) . chr(189) => 'Z',
			chr(197) . chr(190)            => 'z', chr(197) . chr(191) => 's',
			// Euro Sign
			chr(226) . chr(130) . chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194) . chr(163)            => ''
		];

		return strtr($subject, $chars);
	}
}
