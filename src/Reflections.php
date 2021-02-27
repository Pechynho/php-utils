<?php


namespace Pechynho\Utility;


use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Reflections
{
	/**
	 * @param object $object
	 * @return ReflectionObject
	 */
	public static function createReflectionObject($object)
	{
		ParamsChecker::isObject('$object', $object, __METHOD__);
		try {
			return new ReflectionObject($object);
		}
		catch (ReflectionException $e) {
			throw new RuntimeException('Creating reflection object for instance of ' . get_class($object) . ' was not successful.');
		}
	}

	/**
	 * @param string $class
	 * @param string $property
	 * @param bool $returnNullIfNotFound
	 * @return ReflectionProperty
	 */
	public static function getProperty($class, $property, $returnNullIfNotFound = false)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$property', $property, __METHOD__);
		ParamsChecker::isBool('$returnNullIfNotFound', $returnNullIfNotFound, __METHOD__);
		$reflectionClass = self::createReflectionClass($class);
		while ($reflectionClass !== false) {
			if ($reflectionClass->hasProperty($property)) {
				try {
					return $reflectionClass->getProperty($property);
				}
				catch (ReflectionException $e) {
					throw new RuntimeException(sprintf("Retrieving property '%s' of class '%s' was not successful.", $property, $class));
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		if ($returnNullIfNotFound) {
			return null;
		}
		throw new RuntimeException(sprintf("Retrieving property '%s' of class '%s' was not successful.", $property, $class));
	}

	/**
	 * @param string|object $classOrObject
	 * @return ReflectionClass
	 */
	public static function createReflectionClass($classOrObject)
	{
		ParamsChecker::isNotEmptyStringOrObject('$classOrObject', $classOrObject, __METHOD__);
		try {
			if (is_string($classOrObject)) {
				return new ReflectionClass($classOrObject);
			}
			return new ReflectionClass(get_class($classOrObject));
		}
		catch (ReflectionException $e) {
			throw new RuntimeException(sprintf('Creating reflection class for "%s" was not successful.', is_string($classOrObject) ? $classOrObject : get_class($classOrObject)));
		}
	}

	/**
	 * @param string $class
	 * @param string $method
	 * @param bool $returnNullIfNotFound
	 * @return ReflectionMethod
	 */
	public static function getMethod($class, $method, $returnNullIfNotFound = false)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$method', $method, __METHOD__);
		ParamsChecker::isBool('$returnNullIfNotFound', $returnNullIfNotFound, __METHOD__);
		$reflectionClass = self::createReflectionClass($class);
		while ($reflectionClass !== false) {
			if ($reflectionClass->hasMethod($method)) {
				try {
					return $reflectionClass->getMethod($method);
				}
				catch (ReflectionException $e) {
					throw new RuntimeException(sprintf("Retrieving method '%s' of class '%s' was not successful.", $method, $class));
				}
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		if ($returnNullIfNotFound) {
			return null;
		}
		throw new RuntimeException(sprintf("Retrieving method '%s' of class '%s' was not successful.", $method, $class));
	}
}
