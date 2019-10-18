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
	const ORDER_DIRECTION_ASCENDING = "ORDER_DIRECTION_ASCENDING";

	/** @var string */
	const ORDER_DIRECTION_DESCENDING = "ORDER_DIRECTION_DESCENDING";

	/**
	 * @param mixed $subject
	 * @return bool
	 */
	public static function isCountable($subject)
	{
		return is_array($subject) || $subject instanceof Countable;
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	public static function isIterable($value)
	{
		return is_array($value) || $value instanceof Traversable;
	}

	/**
	 * @param array|Traversable $subject
	 * @return bool
	 */
	public static function isEmpty($subject)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		foreach ($subject as $item)
		{
			return false;
		}
		return true;
	}

	/**
	 * @param array|Traversable $subject
	 * @param callable          $predicate
	 * @return mixed
	 */
	public static function first($subject, $predicate)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		if (!is_callable($predicate))
		{
			throw new InvalidArgumentException('Parameter $predicate has to be type of callable.');
		}
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
	 * @param array|Traversable $subject
	 * @param callable          $predicate
	 * @param mixed             $defaultValue
	 * @return mixed
	 */
	public static function firstOrDefault($subject, $predicate, $defaultValue = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		if (!is_callable($predicate))
		{
			throw new InvalidArgumentException('Parameter $predicate has to be type of callable.');
		}
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
	 * @param array $subject
	 * @return int|string
	 */
	public static function firstKey($subject)
	{
		if (!is_array($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array.');
		}
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
	 * @param array|Traversable $subject
	 * @return mixed
	 */
	public static function firstValue($subject)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	public static function lastKey($subject)
	{
		if (!is_array($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array.');
		}
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
	 * @param array|Traversable $subject
	 * @return mixed
	 */
	public static function lastValue($subject)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable $subject
	 * @param callable          $predicate
	 * @return array
	 */
	public static function where($subject, $predicate)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		if (!is_callable($predicate))
		{
			throw new InvalidArgumentException('Parameter $predicate has to be type of callable.');
		}
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
	 * @param array|Traversable                     $subject
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param bool                                  $preserveKeys
	 * @return array
	 */
	public static function select($subject, $propertyPath, $preserveKeys = false)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		if (!is_bool($preserveKeys))
		{
			throw new InvalidArgumentException('Parameter $preserveKeys has to be type of boolean.');
		}
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
	 * @param array|iterable                             $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return mixed
	 */
	public static function min($subject, $propertyPath = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                          $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return array
	 */
	public static function itemsWithMin($subject, $propertyPath = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                          $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return mixed
	 */
	public static function max($subject, $propertyPath = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                          $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return array
	 */
	public static function itemsWithMax($subject, $propertyPath = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                          $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return float
	 */
	public static function average($subject, $propertyPath = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                          $subject
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @return int|float
	 */
	public static function sum($subject, $propertyPath = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable $subject
	 * @return int
	 */
	public static function count($subject)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                     $subject
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @return array
	 */
	public static function groupBy($subject, $propertyPath)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
		if (!is_array($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array.');
		}
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
	 * @param array|Traversable $subject
	 * @param callable          $predicate
	 * @return mixed
	 */
	public static function last($subject, $predicate)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		if (!is_callable($predicate))
		{
			throw new InvalidArgumentException('Parameter $predicate has ty pe type of callable.');
		}
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
	 * @param array|Traversable $subject
	 * @param callable          $predicate
	 * @param mixed             $defaultValue
	 * @return mixed
	 */
	public static function lastOrDefault($subject, $predicate, $defaultValue = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		if (!is_callable($predicate))
		{
			throw new InvalidArgumentException('Parameter $predicate has ty pe type of callable.');
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
		return $found ? $lastItem : $defaultValue;
	}

	/**
	 * @param array|Traversable $subject
	 * @param mixed             $value
	 * @return bool
	 */
	public static function contains($subject, $value)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	public static function keyOf($subject, $value)
	{
		if (!is_array($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array.');
		}
		$key = array_search($value, $subject, true);
		return $key === false ? null : $key;
	}

	/**
	 * @param array|Traversable                          $subject
	 * @param string                                     $direction
	 * @param callable|string|PropertyPathInterface|null $propertyPath
	 * @param callable|null                              $comparisonFunction
	 * @return array
	 */
	public static function orderBy($subject, $direction = Arrays::ORDER_DIRECTION_ASCENDING, $propertyPath = null, $comparisonFunction = null)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable $subject
	 * @return array
	 */
	public static function reverse($subject)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
		return array_reverse(is_array($subject) ? $subject : Arrays::toArray($subject));
	}

	/**
	 * @param array $subject
	 * @return array
	 */
	public static function flip($subject)
	{
		if (!is_array($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array.');
		}
		return array_flip($subject);
	}

	/**
	 * @param array|Traversable $subject
	 * @return array
	 */
	public static function toArray($subject)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to be type of array or Traversable.');
		}
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
	 * @param array|Traversable                     $subject
	 * @param string|callable|PropertyPathInterface $keyPropertyPath
	 * @param string|callable|PropertyPathInterface $valuePropertyPath
	 * @return array
	 */
	public static function mapToPairs($subject, $keyPropertyPath, $valuePropertyPath)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to by type of array or Traversable.');
		}
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
	 * @param array|Traversable                     $subject
	 * @param string|callable|PropertyPathInterface $keyPropertyPath
	 * @return array
	 */
	public static function mapByProperty($subject, $keyPropertyPath)
	{
		if (!Arrays::isIterable($subject))
		{
			throw new InvalidArgumentException('Parameter $subject has to by type of array or Traversable.');
		}
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