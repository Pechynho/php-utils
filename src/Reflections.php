<?php


namespace Pechynho\Utility;


use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Reflections
{
	/**
	 * @param string $class
	 * @return ReflectionClass
	 */
	public static function createReflectionClass($class)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		try
		{
			return new ReflectionClass($class);
		}
		catch (ReflectionException $e)
		{
			throw new RuntimeException(sprintf('Creating reflection class for "%s" was not successful', $class));
		}
	}

	/**
	 * @param string $class
	 * @param string $property
	 * @param bool   $returnNullIfNotFound
	 * @return ReflectionProperty
	 */
	public static function getProperty($class, $property, $returnNullIfNotFound = false)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$property', $property, __METHOD__);
		ParamsChecker::isBool('$returnNullIfNotFound', $returnNullIfNotFound, __METHOD__);
		$reflectionClass = self::createReflectionClass($class);
		while ($reflectionClass !== false)
		{
			if ($reflectionClass->hasProperty($property))
			{
				try
				{
					return $reflectionClass->getProperty($property);
				}
				catch (ReflectionException $e)
				{
					throw new RuntimeException(sprintf("Retrieving property '%s' of class '%s' was not successful.", $property, $class));
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		if ($returnNullIfNotFound)
		{
			return null;
		}
		throw new RuntimeException(sprintf("Retrieving property '%s' of class '%s' was not successful.", $property, $class));
	}

	/**
	 * @param string $class
	 * @param string $method
	 * @param bool   $returnNullIfNotFound
	 * @return ReflectionMethod
	 */
	public static function getMethod($class, $method, $returnNullIfNotFound = false)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$method', $method, __METHOD__);
		ParamsChecker::isBool('$returnNullIfNotFound', $returnNullIfNotFound, __METHOD__);
		$reflectionClass = self::createReflectionClass($class);
		while ($reflectionClass !== false)
		{
			if ($reflectionClass->hasMethod($method))
			{
				try
				{
					return $reflectionClass->getMethod($method);
				}
				catch (ReflectionException $e)
				{
					throw new RuntimeException(sprintf("Retrieving method '%s' of class '%s' was not successful.", $method, $class));
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		if ($returnNullIfNotFound)
		{
			return null;
		}
		throw new RuntimeException(sprintf("Retrieving method '%s' of class '%s' was not successful.", $method, $class));
	}
}
