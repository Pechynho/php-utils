<?php


namespace Pechynho\Utility;


use Countable;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Traversable;

class Arrays
{
	/** @var string */
	public const ORDER_DIRECTION_ASCENDING = "ORDER_DIRECTION_ASCENDING";

	/** @var string */
	public const ORDER_DIRECTION_DESCENDING = "ORDER_DIRECTION_DESCENDING";

	/**
	 * @param mixed $subject
	 * @return bool
	 */
	public static function isCountable($subject): bool
	{
		return is_array($subject) || $subject instanceof Countable;
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	public static function isIterable($value): bool
	{
		return is_array($value) || $value instanceof Traversable;
	}

	/**
	 * @param iterable $subject
	 * @return bool
	 */
	public static function isEmpty(iterable $subject): bool
	{
		foreach ($subject as $item)
		{
			return false;
		}
		return true;
	}

	/**
	 * @param iterable $subject
	 * @param callable $predicate
	 * @return mixed
	 */
	public static function first(iterable $subject, callable $predicate)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		foreach ($subject as $item)
		{
			if ($predicate($item))
			{
				return $item;
			}
		}
		throw new RuntimeException("No item was found by given predicate.");
	}

	/**
	 * @param iterable $subject
	 * @param callable $predicate
	 * @param mixed    $defaultValue
	 * @return mixed
	 */
	public static function firstOrDefault(iterable $subject, callable $predicate, $defaultValue = null)
	{
		foreach ($subject as $item)
		{
			if ($predicate($item))
			{
				return $item;
			}
		}
		return $defaultValue;
	}

	/**
	 * @param $subject
	 * @return int|string
	 */
	public static function firstKey(array $subject)
	{
		if (empty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if (function_exists("array_key_first")) return array_key_first($subject);
		foreach ($subject as $key => $item)
		{
			return $key;
		}
	}

	/**
	 * @param iterable $subject
	 * @return mixed
	 */
	public static function firstValue(iterable $subject)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		foreach ($subject as $item)
		{
			return $item;
		}
	}

	/**
	 * @param array $subject
	 * @return int|string
	 */
	public static function lastKey(array $subject)
	{
		if (empty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if (function_exists("array_key_last")) return array_key_last($subject);
		$lastKey = null;
		foreach ($subject as $key => $item)
		{
			$lastKey = $key;
		}
		return $lastKey;
	}

	/**
	 * @param iterable $subject
	 * @return mixed
	 */
	public static function lastValue(iterable $subject)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		$lastValue = null;
		foreach ($subject as $item)
		{
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
		foreach ($subject as $item)
		{
			if ($predicate($item))
			{
				$output[] = $item;
			}
		}
		return $output;
	}

	/**
	 * @param iterable                              $subject
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param bool                                  $preserveKeys
	 * @return array
	 */
	public static function select(iterable $subject, $propertyPath, bool $preserveKeys = false): array
	{
		if (!is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		if ($preserveKeys && !is_array($subject))
		{
			throw new InvalidArgumentException('Cannot preserve keys if parameter $subject is not array.');
		}
		$output = [];
		if ($preserveKeys)
		{
			foreach ($subject as $key => $item)
			{
				$output[$key] = PropertyAccess::getValue($item, $propertyPath);
			}
		}
		else
		{
			foreach ($subject as $item)
			{
				$output[] = PropertyAccess::getValue($item, $propertyPath);
			}
		}
		return $output;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return mixed
	 */
	public static function min(iterable $subject, $propertyPath = null)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$minValue = null;
		foreach ($subject as $item)
		{
			$value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
			if ($minValue == null || $value < $minValue)
			{
				$minValue = $value;
			}
		}
		return $minValue;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return array
	 */
	public static function itemsWithMin(iterable $subject, $propertyPath = null): array
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$minValue = null;
		$items = [];
		foreach ($subject as $item)
		{
			$value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
			if ($value == $minValue)
			{
				$items[] = $item;
			}
			if ($minValue == null || $value < $minValue)
			{
				$minValue = $value;
				$items = [$item];
			}
		}
		return $items;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return mixed
	 */
	public static function max(iterable $subject, $propertyPath = null)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$maxValue = null;
		foreach ($subject as $item)
		{
			$value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
			if ($maxValue == null || $value > $maxValue)
			{
				$maxValue = $value;
			}
		}
		return $maxValue;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return array
	 */
	public static function itemsWithMax(iterable $subject, $propertyPath = null): array
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$maxValue = null;
		$items = [];
		foreach ($subject as $item)
		{
			$value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
			if ($value == $maxValue)
			{
				$items[] = $item;
			}
			if ($maxValue == null || $value > $maxValue)
			{
				$maxValue = $value;
				$items = [$item];
			}
		}
		return $items;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return float
	 */
	public static function average(iterable $subject, $propertyPath = null): float
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$sum = 0;
		$count = 0;
		foreach ($subject as $item)
		{
			$value = $propertyPath === null ? $item : PropertyAccess::getValue($item, $propertyPath);
			$sum = $sum + $value;
			$count++;
		}
		return $sum / $count;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return int|float
	 */
	public static function sum(iterable $subject, $propertyPath = null)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$sum = 0;
		foreach ($subject as $item)
		{
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
		if (Arrays::isCountable($subject))
		{
			return count($subject);
		}
		$count = 0;
		foreach ($subject as $item)
		{
			$count++;
		}
		return $count;
	}

	/**
	 * @param iterable                              $subject
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @return array
	 */
	public static function groupBy(iterable $subject, $propertyPath): array
	{
		if (!is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$output = [];
		foreach ($subject as $item)
		{
			$key = PropertyAccess::getValue($item, $propertyPath);
			if (!is_string($key) && !is_int($key))
			{
				throw new InvalidArgumentException('Items cannot be grouped by given property path, because its value is not int or string.');
			}
			if (!isset($output[$key]))
			{
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
	public static function binarySearch(array $subject, $value): ?int
	{
		$left = 0;
		$right = count($subject) - 1;
		while ($left <= $right)
		{
			$mid = (int)floor(($left + $right) / 2);
			if ($subject[$mid] < $value)
			{
				$left = $mid + 1;
			}
			else if ($subject[$mid] > $value)
			{
				$right = $mid - 1;
			}
			else
			{
				return $mid;
			}
		}
		return null;
	}

	/**
	 * @param iterable $subject
	 * @param callable $predicate
	 * @return mixed
	 */
	public static function last(iterable $subject, callable $predicate)
	{
		if (Arrays::isEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		$lastItem = null;
		$found = false;
		foreach ($subject as $item)
		{
			if ($predicate($item))
			{
				$lastItem = $item;
				$found = true;
			}
		}
		if ($found)
		{
			return $lastItem;
		}
		throw new RuntimeException("No item was found by given predicate.");
	}

	/**
	 * @param iterable $subject
	 * @param callable $predicate
	 * @param mixed    $defaultValue
	 * @return mixed
	 */
	public static function lastOrDefault(iterable $subject, callable $predicate, $defaultValue = null)
	{
		$lastItem = null;
		$found = false;
		foreach ($subject as $item)
		{
			if ($predicate($item))
			{
				$lastItem = $item;
				$found = true;
			}
		}
		return $found ? $lastItem : $defaultValue;
	}

	/**
	 * @param iterable $subject
	 * @param mixed    $value
	 * @return bool
	 */
	public static function contains(iterable $subject, $value): bool
	{
		if (is_array($subject))
		{
			return in_array($value, $subject, true);
		}
		foreach ($subject as $item)
		{
			if ($item === $value)
			{
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
	public static function keyOf(array $subject, $value)
	{
		$key = array_search($value, $subject, true);
		return $key === false ? null : $key;
	}

	/**
	 * @param iterable                                   $subject
	 * @param string                                     $direction
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @param callable|null                              $comparisonFunction
	 * @return array
	 */
	public static function orderBy(iterable $subject, string $direction = Arrays::ORDER_DIRECTION_ASCENDING, $propertyPath = null, $comparisonFunction = null): array
	{
		if (!in_array($direction, [Arrays::ORDER_DIRECTION_ASCENDING, Arrays::ORDER_DIRECTION_DESCENDING]))
		{
			throw new InvalidArgumentException('Invalid value for argument $direction.');
		}
		if ($propertyPath !== null && !is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be NULL, callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		if ($comparisonFunction !== null && !is_callable($comparisonFunction))
		{
			throw new InvalidArgumentException('Parameter $comparisonFunction has to be NULL or callable.');
		}
		$subject = Arrays::toArray($subject);
		if ($comparisonFunction !== null)
		{
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
			try
			{
				if (is_string($valueA) || is_string($valueB))
				{
					$comparisonResult = Strings::compare($valueA, $valueB, Strings::COMPARE_CASE_INSENSITIVE);
				}
				else
				{
					$comparisonResult = 0;
					if ($valueA < $valueB) $comparisonResult = -1;
					if ($valueA > $valueB) $comparisonResult = 1;
				}
				return $direction === Arrays::ORDER_DIRECTION_ASCENDING ? $comparisonResult : -1 * $comparisonResult;
			}
			catch (Exception $exception)
			{
				throw new RuntimeException("Exception occurred during default comparison. Please, provide your own one.");
			}
		});
		return $subject;
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
	 * @return array
	 */
	public static function toArray(iterable $subject): array
	{
		if (is_array($subject))
		{
			return $subject;
		}
		$output = [];
		foreach ($subject as $item)
		{
			$output[] = $item;
		}
		return $output;
	}

	/**
	 * @param iterable                              $subject
	 * @param string|callable|PropertyPathInterface $keyPropertyPath
	 * @param string|callable|PropertyPathInterface $valuePropertyPath
	 * @return array
	 */
	public static function mapToPairs(iterable $subject, $keyPropertyPath, $valuePropertyPath)
	{
		if (!is_callable($keyPropertyPath) && !is_string($keyPropertyPath) && !$keyPropertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $keyPropertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		if (!is_callable($valuePropertyPath) && !is_string($valuePropertyPath) && !$valuePropertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $valuePropertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$output = [];
		foreach ($subject as $item)
		{
			$output[PropertyAccess::getValue($item, $keyPropertyPath)] = PropertyAccess::getValue($item, $valuePropertyPath);
		}
		return $output;
	}

	/**
	 * @param iterable                              $subject
	 * @param string|callable|PropertyPathInterface $keyPropertyPath
	 * @return array
	 */
	public static function mapByProperty(iterable $subject, $keyPropertyPath)
	{
		if (!is_callable($keyPropertyPath) && !is_string($keyPropertyPath) && !$keyPropertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $keyPropertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		$output = [];
		foreach ($subject as $item)
		{
			$output[PropertyAccess::getValue($item, $keyPropertyPath)] = $item;
		}
		return $output;
	}
}