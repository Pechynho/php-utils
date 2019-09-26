<?php


namespace Pechynho\Utility;


use Exception;
use InvalidArgumentException;
use Pechynho\Utility\Exception\PropertyAccessException;
use ReflectionObject;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class PropertyAccess
{
	/** @var PropertyAccessorInterface */
	private static $propertyAccessor = null;

	/**
	 * @return PropertyAccessorInterface
	 */
	public static function getPropertyAccessor(): PropertyAccessorInterface
	{
		if (self::$propertyAccessor === null) self::$propertyAccessor = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();
		return self::$propertyAccessor;
	}

	/**
	 * @param object|array                          $objectOrArray
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param bool                                  $throwException
	 * @param mixed                                 $defaultValue
	 * @param bool                                  $tryReflection
	 * @return mixed
	 * @throws PropertyAccessException
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
				if ($throwException) throw new PropertyAccessException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
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
					$reflectionObject = new ReflectionObject($objectOrArray);
					while ($reflectionObject != null && !$reflectionObject->hasProperty($propertyPath))
					{
						$reflectionObject = $reflectionObject->getParentClass();
					}
					$property = $reflectionObject->getProperty($propertyPath);
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
					if ($throwException) throw new PropertyAccessException($reflectionException->getMessage(), $reflectionException->getCode(), $reflectionException->getPrevious());
					return $defaultValue;
				}
			}
			if ($throwException) throw new PropertyAccessException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
			return $defaultValue;
		}
	}

	/**
	 * @param object|array                          $objectOrArray
	 * @param callable|string|PropertyPathInterface $propertyPath
	 * @param mixed                                 $value
	 * @param bool                                  $throwException
	 * @param bool                                  $tryReflection
	 * @throws PropertyAccessException
	 */
	public static function setValue($objectOrArray, $propertyPath, $value, bool $throwException = true, bool $tryReflection = false)
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
			}
			catch (Exception $exception)
			{
				if ($throwException) throw new PropertyAccessException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
			}
		}
		try
		{
			self::getPropertyAccessor()->setValue($objectOrArray, $propertyPath, $value);
		}
		catch (Exception $exception)
		{
			if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath))
			{
				try
				{
					$reflectionObject = new ReflectionObject($objectOrArray);
					while ($reflectionObject != null && !$reflectionObject->hasProperty($propertyPath))
					{
						$reflectionObject = $reflectionObject->getParentClass();
					}
					$property = $reflectionObject->getProperty($propertyPath);
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
					if ($throwException) throw new PropertyAccessException($reflectionException->getMessage(), $reflectionException->getCode(), $reflectionException->getPrevious());
				}
			}
			if ($throwException) throw new PropertyAccessException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
		}
	}
}