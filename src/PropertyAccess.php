<?php


namespace Pechynho\Utility;


use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class PropertyAccess
{
	/** @var PropertyAccessorInterface */
	private static $propertyAccessor = null;

	/**
	 * @return PropertyAccessorInterface
	 */
	private static function getPropertyAccessor()
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
	 * @param bool                                  $throwException
	 * @param mixed                                 $defaultValue
	 * @param bool                                  $tryReflection
	 * @return mixed
	 */
	public static function getValue($objectOrArray, $propertyPath, $throwException = true, $defaultValue = null, $tryReflection = false)
	{
		if (!is_bool($throwException))
		{
			throw new InvalidArgumentException('Parameter $throwException has to be boolean type.');
		}
		if (!is_bool($tryReflection))
		{
			throw new InvalidArgumentException('Parameter $tryReflection has to be boolean type.');
		}
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
				if ($throwException) throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
				return $defaultValue;
			}
		}
		try
		{
			return self::getPropertyAccessor()->getValue($objectOrArray, $propertyPath);
		}
		catch (Exception $exception)
		{
			if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath))
			{
				try
				{
					$property = PropertyAccess::getPropertyFromReflectionClass(new ReflectionObject($objectOrArray), $propertyPath);
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
					if ($throwException) throw new RuntimeException($reflectionException->getMessage(), $reflectionException->getCode(), $reflectionException->getPrevious());
					return $defaultValue;
				}
			}
			if ($throwException) throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
			return $defaultValue;
		}
	}

	/**
	 * @param object|array                          $objectOrArray
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param mixed                                 $value
	 * @param bool                                  $throwException
	 * @param bool                                  $tryReflection
	 */
	public static function setValue($objectOrArray, $propertyPath, $value, $throwException = true, $tryReflection = false)
	{
		if (!is_bool($throwException))
		{
			throw new InvalidArgumentException('Parameter $throwException has to be boolean type.');
		}
		if (!is_bool($tryReflection))
		{
			throw new InvalidArgumentException('Parameter $tryReflection has to be boolean type.');
		}
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
				if ($throwException) throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
			}
		}
		try
		{
			self::getPropertyAccessor()->setValue($objectOrArray, $propertyPath, $value);
			return;
		}
		catch (Exception $exception)
		{
			if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath))
			{
				try
				{
					$property = PropertyAccess::getPropertyFromReflectionClass(new ReflectionObject($objectOrArray), $propertyPath);
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
					if ($throwException) throw new RuntimeException($reflectionException->getMessage(), $reflectionException->getCode(), $reflectionException->getPrevious());
				}
			}
			if ($throwException) throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
		}
	}

	/**
	 * @param ReflectionClass $reflectionClass
	 * @param string          $propertyName
	 * @return ReflectionProperty
	 * @throws ReflectionException
	 */
	private static function getPropertyFromReflectionClass($reflectionClass, $propertyName)
	{
		while ($reflectionClass != null && !$reflectionClass->hasProperty($propertyName))
		{
			$reflectionClass = $reflectionClass->getParentClass();
		}
		if (!$reflectionClass instanceof ReflectionClass || !$reflectionClass->hasProperty($propertyName)) throw new ReflectionException("Property was not found in given reflection class.");
		return $reflectionClass->getProperty($propertyName);
	}
}