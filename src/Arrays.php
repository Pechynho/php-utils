<?php

namespace Pechynho\Utility;

use Countable;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Traversable;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Arrays
{
    /** @var string */
    public const ORDER_DIRECTION_ASCENDING = "ORDER_DIRECTION_ASCENDING";
    /** @var string */
    public const ORDER_DIRECTION_DESCENDING = "ORDER_DIRECTION_DESCENDING";

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isIterable(mixed $value): bool
    {
        return is_array($value) || $value instanceof Traversable;
    }

    /**
     * @param iterable $subject
     * @param callable $predicate
     * @return mixed
     */
    public static function first(iterable $subject, callable $predicate): mixed
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        foreach ($subject as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }
        throw new RuntimeException("No item was found by given predicate.");
    }

    /**
     * @param iterable $subject
     * @return bool
     */
    public static function isEmpty(iterable $subject): bool
    {
        foreach ($subject as $item) {
            return false;
        }
        return true;
    }

    /**
     * @param iterable $subject
     * @param callable $predicate
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public static function firstOrDefault(iterable $subject, callable $predicate, mixed $defaultValue = null): mixed
    {
        foreach ($subject as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }
        return $defaultValue;
    }

    /**
     * @param iterable $subject
     * @return mixed
     */
    public static function firstValue(iterable $subject): mixed
    {
        foreach ($subject as $item) {
            return $item;
        }
        throw new InvalidArgumentException('Parameter $subject is empty.');
    }

    /**
     * @param array $subject
     * @return int|string
     */
    public static function lastKey(array $subject): int|string
    {
        if (empty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        if (function_exists("array_key_last")) {
            return array_key_last($subject);
        }
        $lastKey = null;
        foreach ($subject as $key => $item) {
            $lastKey = $key;
        }
        return $lastKey;
    }

    /**
     * @param iterable $subject
     * @return mixed
     */
    public static function lastValue(iterable $subject): mixed
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        $lastValue = null;
        foreach ($subject as $item) {
            $lastValue = $item;
        }
        return $lastValue;
    }

    /**
     * @param iterable $subject
     * @param callable $predicate
     * @return array
     */
    public static function where(iterable $subject, callable $predicate): array
    {
        $output = [];
        foreach ($subject as $item) {
            if ($predicate($item)) {
                $output[] = $item;
            }
        }
        return $output;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface $propertyPath
     * @param bool $preserveKeys
     * @return array
     */
    public static function select(
        iterable $subject,
        callable|PropertyPathInterface|string $propertyPath,
        bool $preserveKeys = false
    ): array {
        $output = [];
        if ($preserveKeys) {
            foreach ($subject as $key => $item) {
                $output[$key] = PropertyAccess::getValue($item, $propertyPath);
            }
        } else {
            foreach ($subject as $item) {
                $output[] = PropertyAccess::getValue($item, $propertyPath);
            }
        }
        return $output;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @return mixed
     */
    public static function min(iterable $subject, callable|PropertyPathInterface|string $propertyPath = null): mixed
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        $minValue = null;
        foreach ($subject as $item) {
            $value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
            if ($minValue == null || $value < $minValue) {
                $minValue = $value;
            }
        }
        return $minValue;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @return array
     */
    public static function itemsWithMin(
        iterable $subject,
        callable|PropertyPathInterface|string $propertyPath = null
    ): array {
        if (Arrays::isEmpty($subject)) {
            return [];
        }
        $minValue = null;
        $items = [];
        foreach ($subject as $item) {
            $value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
            if ($value == $minValue) {
                $items[] = $item;
            }
            if ($minValue == null || $value < $minValue) {
                $minValue = $value;
                $items = [$item];
            }
        }
        return $items;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @return mixed
     */
    public static function max(iterable $subject, callable|PropertyPathInterface|string $propertyPath = null): mixed
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        $maxValue = null;
        foreach ($subject as $item) {
            $value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
            if ($maxValue == null || $value > $maxValue) {
                $maxValue = $value;
            }
        }
        return $maxValue;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @return array
     */
    public static function itemsWithMax(
        iterable $subject,
        callable|PropertyPathInterface|string $propertyPath = null
    ): array {
        if (Arrays::isEmpty($subject)) {
            return [];
        }
        $maxValue = null;
        $items = [];
        foreach ($subject as $item) {
            $value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
            if ($value == $maxValue) {
                $items[] = $item;
            }
            if ($maxValue == null || $value > $maxValue) {
                $maxValue = $value;
                $items = [$item];
            }
        }
        return $items;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @return float
     */
    public static function average(iterable $subject, callable|PropertyPathInterface|string $propertyPath = null): float
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        $sum = 0;
        $count = 0;
        foreach ($subject as $item) {
            $value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
            $sum = $sum + $value;
            $count++;
        }
        return $sum / $count;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @return int|float
     */
    public static function sum(iterable $subject, callable|PropertyPathInterface|string $propertyPath = null): float|int
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        $sum = 0;
        foreach ($subject as $item) {
            $value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
            $sum = $sum + $value;
        }
        return $sum;
    }

    /**
     * @param iterable $subject
     * @return int
     */
    public static function count(iterable $subject): int
    {
        if (Arrays::isCountable($subject)) {
            return count($subject);
        }
        $count = 0;
        foreach ($subject as $item) {
            $count++;
        }
        return $count;
    }

    /**
     * @param mixed $subject
     * @return bool
     */
    public static function isCountable(mixed $subject): bool
    {
        return is_array($subject) || $subject instanceof Countable;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface $propertyPath
     * @return array
     */
    public static function groupBy(iterable $subject, callable|PropertyPathInterface|string $propertyPath): array
    {
        $output = [];
        foreach ($subject as $item) {
            $key = PropertyAccess::getValue($item, $propertyPath);
            if (!is_string($key) && !is_int($key)) {
                throw new InvalidArgumentException(
                    'Items cannot be grouped by given property path, because its value is not int or string.'
                );
            }
            if (!isset($output[$key])) {
                $output[$key] = [];
            }
            $output[$key][] = $item;
        }
        return $output;
    }

    /**
     * @param array $subject
     * @param mixed $value
     * @return int|null
     */
    public static function binarySearch(array $subject, mixed $value): ?int
    {
        $left = 0;
        $right = count($subject) - 1;
        while ($left <= $right) {
            $mid = (int)floor(($left + $right) / 2);
            if ($subject[$mid] < $value) {
                $left = $mid + 1;
            } else {
                if ($subject[$mid] > $value) {
                    $right = $mid - 1;
                } else {
                    return $mid;
                }
            }
        }
        return null;
    }

    /**
     * @param iterable $subject
     * @param callable $predicate
     * @return mixed
     */
    public static function last(iterable $subject, callable $predicate): mixed
    {
        if (Arrays::isEmpty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        $lastItem = null;
        $found = false;
        foreach ($subject as $item) {
            if ($predicate($item)) {
                $lastItem = $item;
                $found = true;
            }
        }
        if ($found) {
            return $lastItem;
        }
        throw new RuntimeException("No item was found by given predicate.");
    }

    /**
     * @param iterable $subject
     * @param callable $predicate
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public static function lastOrDefault(iterable $subject, callable $predicate, mixed $defaultValue = null): mixed
    {
        $lastItem = null;
        $found = false;
        foreach ($subject as $item) {
            if ($predicate($item)) {
                $lastItem = $item;
                $found = true;
            }
        }
        return $found ? $lastItem : $defaultValue;
    }

    /**
     * @param iterable $subject
     * @param mixed $value
     * @return bool
     */
    public static function contains(iterable $subject, mixed $value): bool
    {
        if (is_array($subject)) {
            return in_array($value, $subject, true);
        }
        foreach ($subject as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $subject
     * @param       $value
     * @return string|int|null
     */
    public static function keyOf(array $subject, $value): int|string|null
    {
        $key = array_search($value, $subject, true);
        return $key === false ? null : $key;
    }

    /**
     * @param iterable $subject
     * @param string $direction
     * @param callable|string|PropertyPathInterface|null $propertyPath
     * @param callable|null $comparisonFunction
     * @return array
     */
    public static function orderBy(
        iterable $subject,
        string $direction = Arrays::ORDER_DIRECTION_ASCENDING,
        callable|PropertyPathInterface|string $propertyPath = null,
        callable $comparisonFunction = null
    ): array {
        if (!in_array($direction, [Arrays::ORDER_DIRECTION_ASCENDING, Arrays::ORDER_DIRECTION_DESCENDING])) {
            throw new InvalidArgumentException('Invalid value for argument $direction.');
        }
        $subject = Arrays::toArray($subject);
        if ($comparisonFunction !== null) {
            usort($subject, function ($itemA, $itemB) use (&$propertyPath, &$comparisonFunction) {
                $valueA = $propertyPath === null ? $itemA : PropertyAccess::getValue($itemA, $propertyPath);
                $valueB = $propertyPath === null ? $itemB : PropertyAccess::getValue($itemB, $propertyPath);
                return $comparisonFunction($valueA, $valueB);
            });
            return $subject;
        }
        usort($subject, function ($itemA, $itemB) use (&$propertyPath, $direction) {
            $valueA = $propertyPath === null ? $itemA : PropertyAccess::getValue($itemA, $propertyPath);
            $valueB = $propertyPath === null ? $itemB : PropertyAccess::getValue($itemB, $propertyPath);
            try {
                if (is_string($valueA) || is_string($valueB)) {
                    $comparisonResult = Strings::compare($valueA, $valueB, Strings::COMPARE_CASE_INSENSITIVE);
                } else {
                    $comparisonResult = 0;
                    if ($valueA < $valueB) {
                        $comparisonResult = -1;
                    }
                    if ($valueA > $valueB) {
                        $comparisonResult = 1;
                    }
                }
                return $direction === Arrays::ORDER_DIRECTION_ASCENDING ? $comparisonResult : -1 * $comparisonResult;
            } catch (Exception $exception) {
                throw new RuntimeException(
                    "Exception occurred during default comparison. Please, provide your own one."
                );
            }
        });
        return $subject;
    }

    /**
     * @param iterable $subject
     * @return array
     */
    public static function toArray(iterable $subject): array
    {
        if (is_array($subject)) {
            return $subject;
        }
        $output = [];
        foreach ($subject as $item) {
            $output[] = $item;
        }
        return $output;
    }

    /**
     * @param iterable $subject
     * @return array
     */
    public static function reverse(iterable $subject): array
    {
        return array_reverse(is_array($subject) ? $subject : Arrays::toArray($subject));
    }

    /**
     * @param array $subject
     * @return array
     */
    public static function flip(array $subject): array
    {
        return array_flip($subject);
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface $keyPropertyPath
     * @param callable|string|PropertyPathInterface $valuePropertyPath
     * @return array
     */
    public static function mapToPairs(
        iterable $subject,
        callable|PropertyPathInterface|string $keyPropertyPath,
        callable|PropertyPathInterface|string $valuePropertyPath
    ): array {
        $output = [];
        foreach ($subject as $item) {
            $output[PropertyAccess::getValue($item, $keyPropertyPath)] = PropertyAccess::getValue(
                $item,
                $valuePropertyPath
            );
        }
        return $output;
    }

    /**
     * @param iterable $subject
     * @param callable|string|PropertyPathInterface $keyPropertyPath
     * @return array
     */
    public static function mapByProperty(
        iterable $subject,
        callable|PropertyPathInterface|string $keyPropertyPath
    ): array {
        $output = [];
        foreach ($subject as $item) {
            $output[PropertyAccess::getValue($item, $keyPropertyPath)] = $item;
        }
        return $output;
    }

    /**
     * @param array $subject
     * @param int|string $key
     * @param mixed $value
     * @param int|string $insertAfterKey
     * @return array
     */
    public static function insertAfter(array $subject, int|string $key, mixed $value, int|string $insertAfterKey): array
    {
        $output = [];
        $inserted = false;
        foreach ($subject as $i => $item) {
            $output[$i] = $item;
            if (!$inserted && $i == $insertAfterKey) {
                $output[$key] = $value;
                $inserted = true;
            }
        }
        if (!$inserted) {
            $output[$key] = $value;
        }
        return $output;
    }

    /**
     * @param array $subject
     * @param int|string $key
     * @param mixed $value
     * @param int|string $insertBeforeKey
     * @return array
     */
    public static function insertBefore(
        array $subject,
        int|string $key,
        mixed $value,
        int|string $insertBeforeKey
    ): array {
        $output = [];
        $inserted = false;
        foreach ($subject as $i => $item) {
            if (!$inserted && $i == $insertBeforeKey) {
                $output[$key] = $value;
                $inserted = true;
            }
            $output[$i] = $item;
        }
        if (!$inserted) {
            $output[$key] = $value;
        }
        return $output;
    }

    /**
     * @param array $subject
     * @param string $keyPath
     * @param mixed $value
     * @return boolean
     */
    public static function recursiveGet(array $subject, string $keyPath, mixed &$value = null): bool
    {
        if (Strings::isNullOrWhiteSpace($keyPath)) {
            throw new InvalidArgumentException(
                'Parameter $keyPath has to be non empty ("") and non-whitespace string.'
            );
        }
        $keys = Strings::split($keyPath, ["[", "]"], true);
        $count = count($keys);
        foreach ($keys as $index => $key) {
            if (!is_array($subject) || !isset($subject[$key])) {
                break;
            }
            $subject = $subject[$key];
            if ($count == $index + 1) {
                $value = $subject;
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $subject
     * @param string $keyPath
     * @param mixed $value
     * @return array
     */
    public static function recursiveSet(array $subject, string $keyPath, mixed $value): array
    {
        if (Strings::isNullOrWhiteSpace($keyPath)) {
            throw new InvalidArgumentException(
                'Parameter $keyPath has to be non empty ("") and non-whitespace string.'
            );
        }
        $keys = Strings::split($keyPath, ["[", "]"], true);
        if (count($keys) == 1) {
            $subject[$keys[0]] = $value;
            return $subject;
        }
        $key = $keys[0];
        unset($keys[0]);
        $innerValue = isset($subject[$key]) ? $subject[$key] : [];
        $subject[$key] = self::recursiveSet($innerValue, "[" . Strings::join($keys, "][") . "]", $value);
        return $subject;
    }

    /**
     * @param array $config
     * @param array $defaultConfig
     * @param bool $deep
     * @return array
     */
    public static function mergeArrayConfig(array $config, array $defaultConfig, bool $deep = true): array
    {
        foreach ($defaultConfig as $option => $value) {
            if (!isset($config[$option])) {
                $config[$option] = $defaultConfig[$option];
            } else {
                if ($deep && isset($config[$option]) && is_array(
                        $config[$option]
                    ) && (empty($config[$option]) || is_string(Arrays::firstKey($config[$option])))) {
                    $config[$option] = Arrays::mergeArrayConfig($config[$option], $defaultConfig[$option], $deep);
                }
            }
        }
        return $config;
    }

    /**
     * @param array $subject
     * @return int|string
     */
    public static function firstKey(array $subject): int|string
    {
        if (empty($subject)) {
            throw new InvalidArgumentException('Parameter $subject is empty.');
        }
        if (function_exists("array_key_first")) {
            return array_key_first($subject);
        }
        foreach ($subject as $key => $item) {
            return $key;
        }
    }
}
