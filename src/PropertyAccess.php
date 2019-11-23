<?php


namespace Pechynho\Utility;


use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class PropertyAccess
{
	/** @var PropertyAccessorInterface */
	private static $propertyAccessor = null;

	/**
	 * @param object|array                          $objectOrArray
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param bool                                  $throwException
	 * @param mixed                                 $defaultValue
	 * @param bool                                  $tryReflection
	 * @return mixed
	 */
	public static function getValue($objectOrArray, $propertyPath, bool $throwException = true, $defaultValue = null, bool $tryReflection = false)
	{
		if (!is_object($objectOrArray) && !is_array($objectOrArray))
		{
			throw new InvalidArgumentException('Parameter $objectOrArray has to be object or array.');
		}
		if (!is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		if (is_callable($propertyPath))
		{
			try
			{
				return $propertyPath($objectOrArray);
			}
			catch (Exception $exception)
			{
				if ($throwException)
				{
					throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
				}
				return $defaultValue;
			}
		}
		try
		{
			if (is_array($objectOrArray) && is_string($propertyPath) && preg_match("/^\[[\S]+\]$/", $propertyPath) === 0)
			{
				$propertyPath = "[" . $propertyPath . "]";
			}
			return self::getPropertyAccessor()->getValue($objectOrArray, $propertyPath);
		}
		catch (Exception $exception)
		{
			if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath))
			{
				try
				{
					$property = Reflections::getProperty(get_class($objectOrArray), $propertyPath);
					if ($property->isProtected() || $property->isPrivate())
					{
						$property->setAccessible(true);
					}
					$value = $property->getValue($objectOrArray);
					if ($property->isProtected() || $property->isPrivate())
					{
						$property->setAccessible(false);
					}
					return $value;
				}
				catch (Exception $reflectionException)
				{
					if ($throwException)
					{
						throw new RuntimeException($reflectionException->getMessage(), $reflectionException->getCode(), $reflectionException->getPrevious());
					}
					return $defaultValue;
				}
			}
			if ($throwException)
			{
				throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
			}
			return $defaultValue;
		}
	}

	/**
	 * @return PropertyAccessorInterface
	 */
	private static function getPropertyAccessor(): PropertyAccessorInterface
	{
		if (self::$propertyAccessor === null)
		{
			self::$propertyAccessor = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
		}
		return self::$propertyAccessor;
	}

	/**
	 * @param object|array                          $objectOrArray
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param mixed                                 $value
	 * @param bool                                  $throwException
	 * @param bool                                  $tryReflection
	 */
	public static function setValue(&$objectOrArray, $propertyPath, $value, bool $throwException = true, bool $tryReflection = false)
	{
		if (!is_object($objectOrArray) && !is_array($objectOrArray))
		{
			throw new InvalidArgumentException('Parameter $objectOrArray has to be object or array.');
		}
		if (!is_callable($propertyPath) && !is_string($propertyPath) && !$propertyPath instanceof PropertyPathInterface)
		{
			throw new InvalidArgumentException('Parameter $propertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.');
		}
		if (is_callable($propertyPath))
		{
			try
			{
				$propertyPath($objectOrArray, $value);
				return;
			}
			catch (Exception $exception)
			{
				if ($throwException)
				{
					throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
				}
			}
		}
		try
		{
			if (is_array($objectOrArray) && is_string($propertyPath) && preg_match("/^\[[\S]+\]$/", $propertyPath) === 0)
			{
				$propertyPath = "[" . $propertyPath . "]";
			}
			self::getPropertyAccessor()->setValue($objectOrArray, $propertyPath, $value);
			return;
		}
		catch (Exception $exception)
		{
			if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath))
			{
				try
				{
					$property = Reflections::getProperty(get_class($objectOrArray), $propertyPath);
					if ($property->isProtected() || $property->isPrivate())
					{
						$property->setAccessible(true);
					}
					$property->setValue($objectOrArray, $value);
					if ($property->isProtected() || $property->isPrivate())
					{
						$property->setAccessible(false);
					}
					return;
				}
				catch (Exception $reflectionException)
				{
					if ($throwException)
					{
						throw new RuntimeException($reflectionException->getMessage(), $reflectionException->getCode(), $reflectionException->getPrevious());
					}
				}
			}
			if ($throwException)
			{
				throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
			}
		}
	}
}
