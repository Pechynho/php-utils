<?php


namespace Pechynho\Utility;


use Countable;
use Exception;
use InvalidArgumentException;
use Pechynho\Utility\Exception\ItemNotFoundException;
use Pechynho\Utility\Exception\PropertyAccessException;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Traversable;

class Arrays
{
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
	public static function isIterableEmpty(iterable $subject): bool
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
	 * @throws ItemNotFoundException
	 */
	public static function first(iterable $subject, callable $predicate)
	{
		if (Arrays::isIterableEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		foreach ($subject as $item)
		{
			if ($predicate($subject))
			{
				return $item;
			}
		}
		throw new ItemNotFoundException("No item was found by given predicate.");
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
		if (Arrays::isIterableEmpty($subject))
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
		if (Arrays::isIterableEmpty($subject))
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
	 * @throws PropertyAccessException
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
			foreach ($subject as $item)
			{
				$output[] = PropertyAccess::getValue($item, $propertyPath);
			}
		}
		else
		{
			foreach ($subject as $key => $item)
			{
				$output[$key] = PropertyAccess::getValue($item, $propertyPath);
			}
		}
		return $output;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return mixed
	 * @throws PropertyAccessException
	 */
	public static function min(iterable $subject, $propertyPath = null)
	{
		if (Arrays::isIterableEmpty($subject))
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
			if ($value < $minValue)
			{
				$minValue = $value;
			}
		}
		return $minValue;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return mixed
	 * @throws PropertyAccessException
	 */
	public static function max(iterable $subject, $propertyPath = null)
	{
		if (Arrays::isIterableEmpty($subject))
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
			if ($value > $maxValue)
			{
				$maxValue = $value;
			}
		}
		return $maxValue;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return float
	 * @throws PropertyAccessException
	 */
	public static function average(iterable $subject, $propertyPath = null): float
	{
		if (Arrays::isIterableEmpty($subject))
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
	 * @throws PropertyAccessException
	 */
	public static function sum(iterable $subject, $propertyPath = null)
	{
		if (Arrays::isIterableEmpty($subject))
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
	 * @throws PropertyAccessException
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
	public static function binarySearch(array $subject, $value)
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
	 * @throws ItemNotFoundException
	 */
	public static function last(iterable $subject, callable $predicate)
	{
		if (Arrays::isIterableEmpty($subject))
		{
			throw new InvalidArgumentException('Parameter $subject is empty.');
		}
		$lastItem = null;
		$found = false;
		foreach ($subject as $item)
		{
			if ($predicate($subject))
			{
				$lastItem = $item;
				$found = true;
			}
		}
		if ($found)
		{
			return $lastItem;
		}
		throw new ItemNotFoundException("No item was found by given predicate.");
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

	public static function keyOf(array $subject, $value): int
	{
		foreach ($subject as $index => $item)
		{
			if ($item === $value)
			{
				return $index;
			}
		}
		return null;
	}

	public static function lastKeyOf(array $subject, $value): int
	{
		$lastKey = null;
		foreach ($subject as $key => $item)
		{
			if ($item === $value)
			{
				$lastKey = $key;
			}
		}
		return $lastKey;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @param callable|null                              $comparisonFunction
	 * @return array
	 */
	public static function orderBy(iterable $subject, $propertyPath = null, $comparisonFunction = null): array
	{
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
		usort($subject, function ($itemA, $itemB) use (&$propertyPath) {
			$valueA = $propertyPath === null ? $itemA : PropertyAccess::getValue($itemA, $propertyPath);
			$valueB = $propertyPath === null ? $itemB : PropertyAccess::getValue($itemB, $propertyPath);
			try
			{
				if (is_string($valueA) || is_string($valueB))
				{
					return Strings::compare($valueA, $valueB, Strings::COMPARE_CASE_INSENSITIVE);
				}
				if ($valueA < $valueB) return -1;
				if ($valueA > $valueB) return 1;
				return 0;
			}
			catch (Exception $exception)
			{
				throw new RuntimeException("Exception occurred during default comparison. Please, provide your own one.");
			}
		});
		return $subject;
	}

	/**
	 * @param iterable                                   $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @param callable|null                              $comparisonFunction
	 * @return array
	 */
	public static function orderByDescending(iterable $subject, $propertyPath = null, $comparisonFunction = null): array
	{
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
		usort($subject, function ($itemA, $itemB) use (&$propertyPath) {
			$valueA = $propertyPath === null ? $itemA : PropertyAccess::getValue($itemA, $propertyPath);
			$valueB = $propertyPath === null ? $itemB : PropertyAccess::getValue($itemB, $propertyPath);
			try
			{
				if (is_string($valueA) || is_string($valueB))
				{
					return -1 * Strings::compare($valueA, $valueB, Strings::COMPARE_CASE_INSENSITIVE);
				}
				if ($valueA < $valueB) return 1;
				if ($valueA > $valueB) return -1;
				return 0;
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
		if (is_array($subject))
		{
			return array_reverse($subject);
		}
		$output = [];
		foreach ($subject as $item)
		{
			$output[] = $item;
		}
		return array_reverse($output);
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
}