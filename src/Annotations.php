<?php

namespace Pechynho\Utility;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use Throwable;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Annotations
{
    private static ?AnnotationReader $annotationReader = null;

    /**
     * @return AnnotationReader
     */
    public static function createAnnotationReader(): AnnotationReader
    {
        if (self::$annotationReader !== null) {
            return self::$annotationReader;
        }
        if (!class_exists(AnnotationReader::class)) {
            throw new RuntimeException(
                sprintf(
                    '%s is required to use this library. Please run composer require doctrine/annotations.',
                    AnnotationReader::class
                )
            );
        }
        try {
            if (class_exists(AnnotationRegistry::class) && method_exists(AnnotationRegistry::class, 'registerLoader')) {
                AnnotationRegistry::registerLoader('class_exists');
            }
            self::$annotationReader = new AnnotationReader();
            return self::$annotationReader;
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Creating new instance of %s was not successful.', AnnotationReader::class),
                intval($e->getCode()),
                $e
            );
        }
    }

    /**
     * @param string $class
     * @param string $property
     * @param string $annotation
     * @return object|null
     */
    public static function getPropertyAnnotation(string $class, string $property, string $annotation): ?object
    {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$property', $property, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
        $property = Reflections::getProperty($class, $property);
        return self::createAnnotationReader()->getPropertyAnnotation($property, $annotation);
    }

    /**
     * @param string $class
     * @param string $annotation
     * @return ReflectionProperty[]
     */
    public static function getPropertiesWithAnnotation(string $class, string $annotation): array
    {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
        $reflectionClass = Reflections::createReflectionClass($class);
        $properties = $reflectionClass->getProperties();
        $annotationReader = self::createAnnotationReader();
        $output = [];
        foreach ($properties as $property) {
            $annotation = $annotationReader->getPropertyAnnotation($property, $annotation);
            if ($annotation != null) {
                $output[] = $property;
            }
        }
        return $output;
    }

    /**
     * @param string $class
     * @param string $method
     * @param string $annotation
     * @return object|null
     */
    public static function getMethodAnnotation(string $class, string $method, string $annotation): ?object
    {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$method', $method, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
        $method = Reflections::getMethod($class, $method);
        return self::createAnnotationReader()->getMethodAnnotation($method, $annotation);
    }

    /**
     * @param string $class
     * @param string $annotation
     * @return ReflectionMethod[]
     */
    public static function getMethodsWithAnnotation(string $class, string $annotation): array
    {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
        $reflectionClass = Reflections::createReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $annotationReader = self::createAnnotationReader();
        $output = [];
        foreach ($methods as $method) {
            echo $method->getName();
            $annotationInstance = $annotationReader->getMethodAnnotation($method, $annotation);
            if ($annotationInstance != null) {
                $output[] = $method;
            }
        }
        return $output;
    }

    /**
     * @param string $class
     * @param string $annotation
     * @return object|null
     */
    public static function getClassAnnotation(string $class, string $annotation): ?object
    {
        ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
        ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
        $reflectionClass = Reflections::createReflectionClass($class);
        $annotationReader = self::createAnnotationReader();
        while ($reflectionClass !== false) {
            $annotationInstance = $annotationReader->getClassAnnotation($reflectionClass, $annotation);
            if ($annotationInstance !== null) {
                return $annotationInstance;
            }
            $reflectionClass = $reflectionClass->getParentClass();
        }
        return null;
    }
}
