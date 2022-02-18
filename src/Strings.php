<?php

namespace Pechynho\Utility;

use InvalidArgumentException;
use OutOfRangeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Strings
{
    /** @var string */
    public const EMPTY_STRING = "";
    /** @var string */
    public const COMPARE_CASE_SENSITIVE = "COMPARE_CASE_SENSITIVE";
    /** @var string */
    public const COMPARE_CASE_INSENSITIVE = "COMPARE_CASE_INSENSITIVE";
    /** @var string[] */
    public const TRIM_WHITE_SPACE_CHARS = [" ", "\t", "\n", "\r", "\0", "\x0B"];
    /** @var string */
    public const SLUGIFY_NORMAL = "SLUGIFY_NORMAL";
    /** @var string */
    public const SLUGIFY_FILENAME = "SLUGIFY_FILENAME";
    /** @var string */
    public const SLUGIFY_URL = "SLUGIFY_URL";
    /** @var string */
    public const CASE_PASCAL = "CASE_PASCAL";
    /** @var string */
    public const CASE_CAMEL = "CASE_CAMEL";

    /**
     * Indicates whether the specified string is null or an empty string ("").
     * @param string|null $subject The string to test.
     * @return bool Returns true if the $subject parameter is null or an empty string (""); otherwise, false.
     */
    public static function isNullOrEmpty(?string $subject): bool
    {
        return $subject === null || $subject === Strings::EMPTY_STRING;
    }

    /**
     * Indicates whether a specified string is null, empty, or consists only of white-space characters.
     * @param string|null $subject The string to test.
     * @return bool Returns true if the $subject parameter is null, empty string ("") or consists exclusively of white-space characters.
     */
    public static function isNullOrWhiteSpace(?string $subject): bool
    {
        return $subject === null || Strings::trim($subject) === Strings::EMPTY_STRING;
    }

    /**
     * @param string $subject
     * @param string[] $trimChars
     * @return string
     */
    public static function trim(string $subject, array $trimChars = Strings::TRIM_WHITE_SPACE_CHARS): string
    {
        if (empty($trimChars)) {
            throw new InvalidArgumentException('Parameter $trimChars cannot be empty array.');
        }
        return trim($subject, Strings::join($trimChars, ""));
    }

    /**
     * @param iterable $subject
     * @param string $separator
     * @param string|null $lastSeparator
     * @return string
     */
    public static function join(iterable $subject, string $separator, ?string $lastSeparator = null): string
    {
        if (is_array($subject)) {
            if (Arrays::isEmpty($subject)) {
                return "";
            }
            if ($lastSeparator === null || $separator === $lastSeparator) {
                return implode($separator, $subject);
            }
            if (function_exists("array_key_first")) {
                $lastKey = array_key_last($subject);
                $lastValue = $subject[$lastKey];
                unset($subject[$lastKey]);
                if (empty($subject)) {
                    return $lastValue;
                }
                return implode($separator, $subject) . $lastSeparator . $lastValue;
            }
        }
        $parts = [];
        $partsCount = 0;
        foreach ($subject as $item) {
            $parts[] = ["value" => $item, "separator" => $separator];
            $partsCount++;
        }
        if ($partsCount != 0) {
            $parts[$partsCount - 1]["separator"] = $lastSeparator === null ? $separator : $lastSeparator;
            $parts[0]["separator"] = Strings::EMPTY_STRING;
        }
        $output = Strings::EMPTY_STRING;
        foreach ($parts as $part) {
            $output .= $part["separator"] . $part["value"];
        }
        return $output;
    }

    /**
     * Compares two strings by their value.
     * @param string $strA First string to compare.
     * @param string $strB Second string to compare.
     * @param string $type Switch between case-sensitive and case-insensitive comparison.
     * @return int Return -1 if $strA is lesser than $strB; 1 if $strA is greater than $strB; otherwise 0.
     */
    public static function compare(string $strA, string $strB, string $type = Strings::COMPARE_CASE_SENSITIVE): int
    {
        if (!in_array($type, [Strings::COMPARE_CASE_SENSITIVE, Strings::COMPARE_CASE_INSENSITIVE])) {
            throw new InvalidArgumentException('Invalid value for argument $type.');
        }
        return $type === Strings::COMPARE_CASE_SENSITIVE ? strcmp($strA, $strB) : strcasecmp($strA, $strB);
    }

    /**
     * Returns a value indicating whether a specified substring occurs within passed string.
     * @param string $subject The string to seek in.
     * @param string $value The substring to seek.
     * @return bool Returns true if the $value parameter occurs within $subject parameter; otherwise false.
     */
    public static function contains(string $subject, string $value): bool
    {
        if (function_exists('str_contains')) {
            return str_contains($subject, $value);
        }
        if ($value === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $value cannot be empty string.');
        }
        return Strings::indexOf($subject, $value) > -1;
    }

    /**
     * Returns integer representing index on which given value occurs in given string.
     * @param string $subject The string to seek in.
     * @param string $value The value to seek.
     * @param int $startIndex Offset value from start of the string.
     * @return int Returns value indicating on which index occurs $value in $subject; if $subject doesn't contain $value, then it returns -1.
     */
    public static function indexOf(string $subject, string $value, int $startIndex = 0): int
    {
        if ($subject === Strings::EMPTY_STRING) {
            return -1;
        }
        if ($value === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $value cannot be empty string.');
        }
        if ($startIndex < 0 || $startIndex >= Strings::length($subject)) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        $result = mb_strpos($subject, $value, $startIndex);
        return $result === false ? -1 : $result;
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
     * Indicates if string ends with given value.
     * @param string $subject The string to seek in.
     * @param string $value The substring to seek at end.
     * @return bool Returns true if the $subject parameter ends with $value parameter; otherwise false;
     */
    public static function endsWith(string $subject, string $value): bool
    {
        if (function_exists("str_ends_with")) {
            return str_ends_with($subject, $value);
        }
        return $value === "" || (($temp = mb_strlen($subject) - mb_strlen($value)) >= 0 && strpos(
                    $subject,
                    $value,
                    $temp
                ) !== false);
    }

    /**
     * @param string $subject
     * @param iterable $values
     * @param int $startIndex
     * @return int
     */
    public static function indexOfAny(string $subject, iterable $values, int $startIndex = 0): int
    {
        if ($subject === Strings::EMPTY_STRING) {
            return -1;
        }
        if (empty($values)) {
            throw new InvalidArgumentException('Parameter $values cannot be empty array.');
        }
        if ($startIndex < 0 || $startIndex >= Strings::length($subject)) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        foreach ($values as $value) {
            $index = Strings::indexOf($subject, $value, $startIndex);
            if ($index > -1) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * @param string $subject
     * @param string $value
     * @param int $startIndex
     * @return string
     */
    public static function insert(string $subject, string $value, int $startIndex): string
    {
        if ($subject === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $subject cannot be empty string.');
        }
        if ($value === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $value cannot be empty string.');
        }
        if ($startIndex < 0 || $startIndex >= Strings::length($subject)) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        return substr_replace($subject, $value, $startIndex, 0);
    }

    /**
     * @param string $subject
     * @param iterable $values
     * @param int $startIndex
     * @return int
     */
    public static function lastIndexOfAny(string $subject, iterable $values, int $startIndex = 0): int
    {
        if ($subject === Strings::EMPTY_STRING) {
            return -1;
        }
        if (empty($values)) {
            throw new InvalidArgumentException('Parameter $values cannot be empty array.');
        }
        if ($startIndex < 0 || $startIndex >= Strings::length($subject)) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        foreach ($values as $value) {
            $index = Strings::lastIndexOf($subject, $value, $startIndex);
            if ($index > -1) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * @param string $subject
     * @param string $value
     * @param int $startIndex
     * @return int
     */
    public static function lastIndexOf(string $subject, string $value, int $startIndex = 0): int
    {
        if ($subject === Strings::EMPTY_STRING) {
            return -1;
        }
        if ($value === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $value cannot be empty string.');
        }
        if ($startIndex < 0 || $startIndex >= Strings::length($subject)) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        $result = mb_strrpos($subject, $value, $startIndex);
        return $result === false ? -1 : $result;
    }

    /**
     * @param string $subject
     * @param int $totalWidth
     * @param string $paddingChar
     * @return string
     */
    public static function padLeft(string $subject, int $totalWidth, string $paddingChar = " "): string
    {
        if (Strings::length($paddingChar) != 1) {
            throw new InvalidArgumentException('Parameter $paddingChar has to be single character.');
        }
        $iterations = $totalWidth - Strings::length($subject);
        for ($i = 0; $i < $iterations; $i++) {
            $subject = $paddingChar . $subject;
        }
        return $subject;
    }

    /**
     * @param string $subject
     * @param int $totalWidth
     * @param string $paddingChar
     * @return string
     */
    public static function padRight(string $subject, int $totalWidth, string $paddingChar = " "): string
    {
        if (Strings::length($paddingChar) != 1) {
            throw new InvalidArgumentException('Parameter $paddingChar has to be single character.');
        }
        $iterations = $totalWidth - Strings::length($subject);
        for ($i = 0; $i < $iterations; $i++) {
            $subject = $subject . $paddingChar;
        }
        return $subject;
    }

    /**
     * @param string $subject
     * @param int $startIndex
     * @param int|null $length
     * @return string
     */
    public static function remove(string $subject, int $startIndex, ?int $length = null): string
    {
        if ($subject === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('$subject $value cannot be empty string.');
        }
        if ($startIndex < 0 || $startIndex >= Strings::length($subject)) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        $subjectLength = Strings::length($subject);
        if ($length !== null && $startIndex + $length > $subjectLength) {
            throw new OutOfRangeException('Parameter $length is out of range.');
        }
        $part1 = $startIndex == 0 ? Strings::EMPTY_STRING : Strings::substring($subject, 0, $startIndex);
        $part2 = $length === null || $startIndex + $length == $subjectLength ? Strings::EMPTY_STRING : Strings::substring(
            $subject,
            $startIndex + $length
        );
        return $part1 . $part2;
    }

    /**
     * @param string $subject
     * @param int $startIndex
     * @param int|null $length
     * @return string
     */
    public static function substring(string $subject, int $startIndex, ?int $length = null): string
    {
        if ($subject === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $subject cannot be empty string.');
        }
        $subjectLength = Strings::length($subject);
        if ($startIndex < 0 || $startIndex >= $subjectLength) {
            throw new OutOfRangeException('Parameter $startIndex is out of range.');
        }
        if ($length !== null && $startIndex + $length > $subjectLength) {
            throw new OutOfRangeException('Parameter $length is out of range.');
        }
        return mb_substr($subject, $startIndex, $length);
    }

    /**
     * @param string $subject
     * @param string $oldValue
     * @param string $newValue
     * @return string
     */
    public static function replace(string $subject, string $oldValue, string $newValue): string
    {
        if ($oldValue === Strings::EMPTY_STRING) {
            throw new InvalidArgumentException('Parameter $oldValue cannot be empty string.');
        }
        return str_replace($oldValue, $newValue, $subject);
    }

    /**
     * @param string $subject
     * @param string[] $separators
     * @param bool $removeEmptyEntries
     * @return array
     */
    public static function split(string $subject, array $separators, bool $removeEmptyEntries = true): array
    {
        if (empty($separators)) {
            throw new InvalidArgumentException('Parameter $separators has to contain at least one value.');
        }
        $separatorsCount = count($separators);
        $replacements = [];
        for ($i = 0; $i < $separatorsCount; $i++) {
            if (!is_string($separators[$i]) || $separators[$i] === Strings::EMPTY_STRING) {
                throw new InvalidArgumentException("Value on index '{$i}' cannot be used as a separator.");
            }
            if ($i == 0) {
                continue;
            }
            $replacements[$separators[$i]] = $separators[0];
        }
        if (!empty($replacements)) {
            $subject = Strings::replaceMultiple($subject, $replacements);
        }
        $values = $removeEmptyEntries ? array_diff(explode($separators[0], $subject), [Strings::EMPTY_STRING]
        ) : explode($separators[0], $subject);
        return array_values($values);
    }

    /**
     * @param string $subject
     * @param string[] $replacements
     * @return string
     */
    public static function replaceMultiple(string $subject, array $replacements): string
    {
        $oldValues = array_keys($replacements);
        $newValues = array_values($replacements);
        if (empty($replacements)) {
            return $subject;
        }
        if (in_array("", $oldValues)) {
            throw new InvalidArgumentException(
                'Keys in parameter $replacements should be non-empty string values which should be replaced.'
            );
        }
        return str_replace($oldValues, $newValues, $subject);
    }

    /**
     * @param string $subject
     * @return string[]
     */
    public static function splitByCase(string $subject): array
    {
        $pattern = '/(?#! splitCamelCase Rev:20140412)
    			# Split camelCase "words". Two global alternatives. Either g1of2:
      			(?<=[a-z])      # Position is after a lowercase,
      			(?=[A-Z])       # and before an uppercase letter.
    			| (?<=[A-Z])    # Or g2of2; Position is after uppercase,
      			(?=[A-Z][a-z])  # and before upper-then-lower case.
    			/x';
        return preg_split($pattern, $subject);
    }

    /**
     * @param string $subject
     * @param string $value
     * @return bool
     */
    public static function startsWith(string $subject, string $value): bool
    {
        if (function_exists("str_starts_with ")) {
            return str_starts_with($subject, $value);
        }
        return $value === "" || strncmp($subject, $value, mb_strlen($value)) === 0;
    }

    /**
     * @param string $subject
     * @param string[] $trimChars
     * @return string
     */
    public static function trimStart(string $subject, array $trimChars = Strings::TRIM_WHITE_SPACE_CHARS): string
    {
        if (empty($trimChars)) {
            throw new InvalidArgumentException('Parameter $trimChars cannot be empty array.');
        }
        return ltrim($subject, Strings::join($trimChars, ""));
    }

    /**
     * @param string $subject
     * @param string[] $trimChars
     * @return string
     */
    public static function trimEnd(string $subject, array $trimChars = Strings::TRIM_WHITE_SPACE_CHARS): string
    {
        if (empty($trimChars)) {
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
        if ($subject === Strings::EMPTY_STRING) {
            return $subject;
        }
        if (Strings::length($subject) === 1) {
            return Strings::toUpper($subject);
        }
        return Strings::toUpper(Strings::substring($subject, 0, 1)) . Strings::substring($subject, 1);
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
     * @param string $subject
     * @param int $maximumLength
     * @return string
     */
    public static function truncate(string $subject, int $maximumLength): string
    {
        if ($maximumLength < 4) {
            throw new InvalidArgumentException('Parameter $maximumLength has to be greater than 3.');
        }
        if (Strings::length($subject) > $maximumLength) {
            $subject = Strings::substring($subject, 0, $maximumLength - 3) . '...';
        }
        return $subject;
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
     * @param string $separator
     * @param string $caseType
     * @return string
     */
    private static function convertToCase(
        string $subject,
        string $separator,
        string $caseType = Strings::CASE_PASCAL
    ): string {
        $result = str_replace(' ', '', mb_convert_case(str_replace($separator, ' ', $subject), MB_CASE_TITLE));
        if ($caseType === Strings::CASE_CAMEL) {
            $result = Strings::firstToLower($result);
        }

        return $result;
    }

    /**
     * @param string $subject
     * @return string
     */
    public static function firstToLower(string $subject): string
    {
        if ($subject === Strings::EMPTY_STRING) {
            return $subject;
        }
        if (Strings::length($subject) === 1) {
            return Strings::toLower($subject);
        }
        return Strings::toLower(Strings::substring($subject, 0, 1)) . Strings::substring($subject, 1);
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
     * @param string $separator
     * @return string
     */
    private static function convertFromCase(string $subject, string $separator): string
    {
        return ltrim(mb_strtolower(preg_replace('/[A-Z]/', $separator . '$0', $subject)), $separator);
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
     * @param bool $toLower
     * @return  string
     */
    public static function slugify(
        string $subject,
        string $separator = "-",
        string $slugifyType = Strings::SLUGIFY_NORMAL,
        bool $toLower = true
    ): string {
        if (!in_array($slugifyType, [Strings::SLUGIFY_NORMAL, Strings::SLUGIFY_FILENAME, Strings::SLUGIFY_URL])) {
            throw new InvalidArgumentException('Invalid value passed to parameter $slugifyType.');
        }
        if (Strings::length($separator) != 1) {
            throw new InvalidArgumentException('Parameter $separator has to be single character.');
        }
        $config = [
            Strings::SLUGIFY_NORMAL => "/\W+/",
            Strings::SLUGIFY_FILENAME => '/[\/\\?%*:|"<>. ]+/',
            Strings::SLUGIFY_URL => '/[!*\'%();:@&=+,?#\[\]\/]+/'
        ];
        if ($toLower) {
            $subject = Strings::toLower($subject);
        }
        $subject = Strings::toAscii($subject);
        $subject = preg_replace('!\s+!', $separator, $subject);
        $subject = preg_replace($config[$slugifyType], $separator, $subject);
        $subject = preg_replace('/[\\' . $separator . ']+/', $separator, $subject);
        return Strings::trim($subject);
    }

    /**
     * @param string $subject
     * @return string
     */
    public static function replaceCzechSpecialCharsWithASCII(string $subject): string
    {
        $chars = [
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A',
            chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A',
            chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A',
            chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I',
            chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O',
            chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O',
            chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O',
            chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U',
            chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U',
            chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a',
            chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e',
            chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e',
            chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i',
            chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i',
            chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A',
            chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A',
            chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A',
            chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C',
            chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C',
            chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C',
            chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C',
            chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D',
            chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D',
            chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E',
            chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E',
            chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E',
            chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E',
            chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E',
            chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G',
            chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G',
            chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G',
            chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G',
            chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H',
            chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H',
            chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I',
            chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I',
            chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I',
            chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I',
            chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I',
            chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ',
            chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J',
            chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K',
            chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k',
            chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l',
            chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l',
            chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l',
            chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l',
            chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l',
            chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n',
            chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n',
            chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n',
            chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n',
            chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O',
            chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O',
            chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O',
            chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE',
            chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R',
            chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R',
            chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R',
            chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S',
            chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S',
            chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S',
            chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S',
            chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T',
            chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T',
            chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T',
            chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U',
            chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U',
            chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U',
            chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U',
            chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U',
            chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U',
            chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W',
            chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y',
            chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y',
            chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z',
            chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z',
            chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z',
            chr(197) . chr(191) => 's',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => ''
        ];

        return strtr($subject, $chars);
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
        for ($i = 0; $i < $length; $i++) {
            $char = $characters[$i];
            if ($char == "<") {
                $inside = true;
                continue;
            } elseif ($char == ">") {
                $inside = false;
                continue;
            }
            if ($inside) {
                continue;
            }
            $output .= $char;
        }
        return $output;
    }

    /**
     * @param string $subject
     * @return array
     */
    public static function toCharArray(string $subject): array
    {
        return preg_split('//u', $subject, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param string $subject
     * @return string
     */
    public static function reverse(string $subject): string
    {
        return strrev($subject);
    }

    /**
     * @param string $subject
     * @return string
     */
    public static function toAscii(string $subject): string
    {
        $iconv = defined('ICONV_IMPL') ? trim(ICONV_IMPL, '"\'') : null;
        $transliterator = null;
        if ($transliterator === null) {
            if (class_exists('Transliterator', false)) {
                $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
            } else {
                trigger_error(__METHOD__ . "(): it is recommended to enable PHP extensions 'intl'.", E_USER_NOTICE);
                $transliterator = false;
            }
        }
        // remove control characters and check UTF-8 validity
        $subject = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u', '', $subject);
        // transliteration (by Transliterator and iconv) is not optimal, replace some characters directly
        $subject = strtr(
            $subject,
            [
                "\u{201E}" => '"',
                "\u{201C}" => '"',
                "\u{201D}" => '"',
                "\u{201A}" => "'",
                "\u{2018}" => "'",
                "\u{2019}" => "'",
                "\u{B0}" => '^',
                "\u{42F}" => 'Ya',
                "\u{44F}" => 'ya',
                "\u{42E}" => 'Yu',
                "\u{44E}" => 'yu',
                "\u{c4}" => 'Ae',
                "\u{d6}" => 'Oe',
                "\u{dc}" => 'Ue',
                "\u{1e9e}" => 'Ss',
                "\u{e4}" => 'ae',
                "\u{f6}" => 'oe',
                "\u{fc}" => 'ue',
                "\u{df}" => 'ss'
            ]
        ); // „ “ ” ‚ ‘ ’ ° Я я Ю ю Ä Ö Ü ẞ ä ö ü ß
        if ($iconv !== 'libiconv') {
            $subject = strtr(
                $subject,
                [
                    "\u{AE}" => '(R)',
                    "\u{A9}" => '(c)',
                    "\u{2026}" => '...',
                    "\u{AB}" => '<<',
                    "\u{BB}" => '>>',
                    "\u{A3}" => 'lb',
                    "\u{A5}" => 'yen',
                    "\u{B2}" => '^2',
                    "\u{B3}" => '^3',
                    "\u{B5}" => 'u',
                    "\u{B9}" => '^1',
                    "\u{BA}" => 'o',
                    "\u{BF}" => '?',
                    "\u{2CA}" => "'",
                    "\u{2CD}" => '_',
                    "\u{2DD}" => '"',
                    "\u{1FEF}" => '',
                    "\u{20AC}" => 'EUR',
                    "\u{2122}" => 'TM',
                    "\u{212E}" => 'e',
                    "\u{2190}" => '<-',
                    "\u{2191}" => '^',
                    "\u{2192}" => '->',
                    "\u{2193}" => 'V',
                    "\u{2194}" => '<->'
                ]
            ); // ® © … « » £ ¥ ² ³ µ ¹ º ¿ ˊ ˍ ˝ ` € ™ ℮ ← ↑ → ↓ ↔
        }
        if ($transliterator) {
            $subject = $transliterator->transliterate($subject);
            // use iconv because The transliterator leaves some characters out of ASCII, eg → ʾ
            if ($iconv === 'glibc') {
                $subject = strtr(
                    $subject,
                    '?',
                    "\x01"
                ); // temporarily hide ? to distinguish them from the garbage that iconv creates
                $subject = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $subject);
                $subject = str_replace(['?', "\x01"], ['', '?'], $subject); // remove garbage and restore ? characters
            } elseif ($iconv === 'libiconv') {
                $subject = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $subject);
            } else {
                // null or 'unknown' (#216)
                $subject = preg_replace('#[^\x00-\x7F]++#', '', $subject); // remove non-ascii chars
            }
        } elseif ($iconv === 'glibc' || $iconv === 'libiconv') {
            // temporarily hide these characters to distinguish them from the garbage that iconv creates
            $subject = strtr($subject, '`\'"^~?', "\x01\x02\x03\x04\x05\x06");
            if ($iconv === 'glibc') {
                // glibc implementation is very limited. transliterate into Windows-1250 and then into ASCII, so most Eastern European characters are preserved
                $subject = iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $subject);
                $subject = strtr(
                    $subject,
                    "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe\x96\xa0\x8b\x97\x9b\xa6\xad\xb7",
                    'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.'
                );
                $subject = preg_replace('#[^\x00-\x7F]++#', '', $subject);
            } else {
                $subject = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $subject);
            }
            // remove garbage that iconv creates during transliteration (eg Ý -> Y')
            $subject = str_replace(['`', "'", '"', '^', '~', '?'], '', $subject);
            // restore temporarily hidden characters
            $subject = strtr($subject, "\x01\x02\x03\x04\x05\x06", '`\'"^~?');
        } else {
            $subject = preg_replace('#[^\x00-\x7F]++#', '', $subject);
        }
        return $subject;
    }
}
