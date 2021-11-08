<?php

namespace Pechynho\Utility;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use Throwable;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Reflections
{
    /**
     * @param object $object
     * @return ReflectionObject
     */
    public static function createReflectionObject(object $object): ReflectionObject
    {
        try {
            return new ReflectionObject($object);
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Creating reflection object for instance of ' . get_class($object) . ' was not successful.',
                intval($e->getCode()),
                $e
            );
        }
    }

    /**
     * @param string $class
     * @param string $property
     * @param bool $returnNullIfNotFound
     * @return ReflectionProperty|null
     */
    public static function getProperty(
        string $class,
        string $property,
        bool $returnNullIfNotFound = false
    ): ?ReflectionProperty {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$property', $property, __METHOD__);
        $reflectionClass = self::createReflectionClass($class);
        while ($reflectionClass !== false) {
            if ($reflectionClass->hasProperty($property)) {
                try {
                    return $reflectionClass->getProperty($property);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        sprintf("Retrieving property '%s' of class '%s' was not successful.", $property, $class),
                        intval($e->getCode()),
                        $e
                    );
                }
            }
            $reflectionClass = $reflectionClass->getParentClass();
        }
        if ($returnNullIfNotFound) {
            return null;
        }
        throw new RuntimeException(
            sprintf("Retrieving property '%s' of class '%s' was not successful.", $property, $class)
        );
    }

    /**
     * @param object|string $classOrObject
     * @return ReflectionClass
     */
    public static function createReflectionClass(object|string $classOrObject): ReflectionClass
    {
        ParamsChecker::isNotEmptyStringOrObject('$classOrObject', $classOrObject, __METHOD__);
        try {
            if (is_string($classOrObject)) {
                return new ReflectionClass($classOrObject);
            }
            return new ReflectionClass(get_class($classOrObject));
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf(
                    'Creating reflection class for "%s" was not successful.',
                    is_string($classOrObject) ? $classOrObject : get_class($classOrObject)
                ),
                intval($e->getCode()),
                $e
            );
        }
    }

    /**
     * @param string $class
     * @param string $method
     * @param bool $returnNullIfNotFound
     * @return ReflectionMethod|null
     */
    public static function getMethod(
        string $class,
        string $method,
        bool $returnNullIfNotFound = false
    ): ?ReflectionMethod {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$method', $method, __METHOD__);
        $reflectionClass = self::createReflectionClass($class);
        while ($reflectionClass !== false) {
            if ($reflectionClass->hasMethod($method)) {
                try {
                    return $reflectionClass->getMethod($method);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        sprintf("Retrieving method '%s' of class '%s' was not successful.", $method, $class),
                        intval($e->getCode()),
                        $e
                    );
                }
            }
            $reflectionClass = $reflectionClass->getParentClass();
        }
        if ($returnNullIfNotFound) {
            return null;
        }
        throw new RuntimeException(
            sprintf("Retrieving method '%s' of class '%s' was not successful.", $method, $class)
        );
    }
}
