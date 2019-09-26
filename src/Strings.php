<?php


namespace Pechynho\Utility;


use InvalidArgumentException;
use OutOfRangeException;

class Strings
{
	/** @var string */
	public const EMPTY_STRING = "";

	/** @var string */
	public const COMPARE_CASE_SENSITIVE = "compare_case_sensitive";

	/** @var string */
	public const COMPARE_CASE_INSENSITIVE = "compare_case_insensitive";

	/** @var string[] */
	public const TRIM_WHITE_CHARS_LIST = [" ", "\t", "\n", "\r", "\0", "\x0B"];

	/** @var string */
	public const SLUGIFY_NORMAL = "slugify_normal";

	/** @var string */
	public const SLUGIFY_FILENAME = "slugify_filename";

	/** @var string */
	public const SLUGIFY_URL = "slugify_url";

	/** @var string */
	public const CASE_PASCAL = "case_pascal";

	/** @var string */
	public const CASE_CAMEL = "case_camel";

	/**
	 * @param string|null $subject
	 * @return bool
	 */
	public static function isNullOrEmpty(?string $subject): bool
	{
		return $subject === null || $subject === Strings::EMPTY_STRING;
	}

	/**
	 * @param string|null $subject
	 * @return bool
	 */
	public static function isNullOrWhiteSpace(?string $subject): bool
	{
		return $subject === null || Strings::trim($subject) === Strings::EMPTY_STRING;
	}

	/**
	 * @param string $strA
	 * @param string $strB
	 * @param string $type
	 * @return int
	 */
	public static function compare(string $strA, string $strB, string $type = Strings::COMPARE_CASE_SENSITIVE): int
	{
		if (!in_array($type, [Strings::COMPARE_CASE_SENSITIVE, Strings::COMPARE_CASE_INSENSITIVE], true))
		{
			throw new InvalidArgumentException('Invalid value for argument $type.');
		}
		return $type === Strings::COMPARE_CASE_SENSITIVE ? strcmp($strA, $strB) : strcasecmp($strA, $strB);
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @return bool
	 */
	public static function contains(string $subject, string $value): bool
	{
		if ($value === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $value cannot be empty string.');
		}
		return Strings::indexOf($subject, $value) > -1;
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @return bool
	 */
	public static function endsWith(string $subject, string $value): bool
	{
		return $value === "" || (($temp = mb_strlen($subject) - mb_strlen($value)) >= 0 && strpos($subject, $value, $temp) !== false);
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @param int    $startIndex
	 * @return int
	 */
	public static function indexOf(string $subject, string $value, int $startIndex = 0): int
	{
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
	 * @param string   $subject
	 * @param iterable $values
	 * @param int      $startIndex
	 * @return int
	 */
	public static function indexOfAny(string $subject, iterable $values, int $startIndex = 0): int
	{
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
	public static function insert(string $subject, string $value, int $startIndex): string
	{
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
	 * @param iterable    $subject
	 * @param string      $separator
	 * @param string|null $lastSeparator
	 * @return string
	 */
	public static function join(iterable $subject, string $separator, ?string $lastSeparator = null): string
	{
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
	public static function lastIndexOf(string $subject, string $value, int $startIndex = 0): int
	{
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
	 * @param string   $subject
	 * @param iterable $values
	 * @param int      $startIndex
	 * @return int
	 */
	public static function lastIndexOfAny(string $subject, iterable $values, int $startIndex = 0): int
	{
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
	public static function length(string $subject): int
	{
		return mb_strlen($subject);
	}

	/**
	 * @param string $subject
	 * @param int    $totalWidth
	 * @param string $paddingChar
	 * @return string
	 */
	public static function padLeft(string $subject, int $totalWidth, string $paddingChar = " "): string
	{
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
	public static function padRight(string $subject, int $totalWidth, string $paddingChar = " "): string
	{
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
	public static function remove(string $subject, int $startIndex, ?int $length = null): string
	{
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
	public static function replace(string $subject, string $oldValue, string $newValue): string
	{
		if ($subject === Strings::EMPTY_STRING)
		{
			throw new InvalidArgumentException('Parameter $subject cannot be empty string.');
		}
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
	public static function replaceMultiple(string $subject, array $replacements): string
	{
		foreach ($replacements as $oldValue => $newValue)
		{
			$subject = Strings::replace($subject, $oldValue, $newValue);
		}
		return $subject;
	}

	/**
	 * @param string   $subject
	 * @param string[] $separators
	 * @param bool     $removeEmptyEntries
	 * @return array
	 */
	public static function split(string $subject, array $separators, bool $removeEmptyEntries = true): array
	{
		if (empty($separators))
		{
			throw new InvalidArgumentException('Parameter $separators has to contain at least one value.');
		}
		$separatorsCount = count($separators);
		for ($i = 0; $i < $separatorsCount; $i++)
		{
			if (!is_string($separators[$i]) || $separators[$i] === Strings::EMPTY_STRING)
			{
				throw new InvalidArgumentException("Value on index '{$i}' cannot be used as a separator.");
			}
			if ($i == 0) continue;
			$subject = Strings::replace($subject, $separators[$i], $separators[0]);
		}
		return $removeEmptyEntries ? array_diff(explode($separators[0], $subject), [Strings::EMPTY_STRING]) : explode($separators[0], $subject);
	}

	/**
	 * @param string $subject
	 * @param string $value
	 * @return bool
	 */
	public static function startsWith(string $subject, string $value): bool
	{
		return $value === "" || strncmp($subject, $value, mb_strlen($value)) === 0;
	}

	/**
	 * @param string   $subject
	 * @param int      $startIndex
	 * @param int|null $length
	 * @return string
	 */
	public static function substring(string $subject, int $startIndex, ?int $length = null): string
	{
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
	public static function toCharArray(string $subject): array
	{
		return preg_split('//u', $subject, null, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function toLower(string $subject): string
	{
		return mb_strtolower($subject);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function toUpper(string $subject): string
	{
		return mb_strtoupper($subject);
	}

	/**
	 * @param string   $subject
	 * @param string[] $trimChars
	 * @return string
	 */
	public static function trim(string $subject, array $trimChars = Strings::TRIM_WHITE_CHARS_LIST): string
	{
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
	public static function trimStart(string $subject, array $trimChars = Strings::TRIM_WHITE_CHARS_LIST): string
	{
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
	public static function trimEnd(string $subject, array $trimChars = Strings::TRIM_WHITE_CHARS_LIST): string
	{
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
	public static function firstToUpper(string $subject): string
	{
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
	public static function firstToLower(string $subject): string
	{
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
	public static function truncate(string $subject, int $maximumLength): string
	{
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
	private static function convertToCase(string $subject, string $separator, string $caseType = Strings::CASE_PASCAL): string
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
	private static function convertFromCase(string $subject, string $separator): string
	{
		return ltrim(mb_strtolower(preg_replace('/[A-Z]/', $separator . '$0', $subject)), $separator);
	}

	/**
	 * @param string $subject
	 * @param string $caseType
	 * @return string
	 */
	public static function dashesToCase(string $subject, string $caseType = Strings::CASE_PASCAL): string
	{
		return self::convertToCase($subject, '-', $caseType);
	}

	/**
	 * @param string $subject
	 * @param string $caseType
	 * @return string
	 */
	public static function underscoresToCase(string $subject, string $caseType = Strings::CASE_PASCAL): string
	{
		return self::convertToCase($subject, '_', $caseType);
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function caseToDashes(string $subject): string
	{
		return self::convertFromCase($subject, '-');
	}

	/**
	 * @param string $subject
	 * @return string
	 */
	public static function caseToUnderscores(string $subject): string
	{
		return self::convertFromCase($subject, '_');
	}

	/**
	 * @param string $subject
	 * @param string $separator
	 * @param string $slugifyType
	 * @return  string
	 */
	public static function slugify(string $subject, string $separator = "-", string $slugifyType = Strings::SLUGIFY_NORMAL): string
	{
		if (!in_array($slugifyType, [Strings::SLUGIFY_NORMAL, Strings::SLUGIFY_FILENAME, Strings::SLUGIFY_URL], true))
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
		$subject = Strings::toLower($subject);
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
	public static function stripHtmlTags(string $subject): string
	{
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
	public static function replaceCzechSpecialCharsWithASCII(string $subject): string
	{
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