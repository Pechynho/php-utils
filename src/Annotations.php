<?php


namespace Pechynho\Utility;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class Annotations
{
	/**
	 * @param string $class
	 * @param string $property
	 * @param string $annotation
	 * @return object|null
	 */
	public static function getPropertyAnnotation($class, $property, $annotation)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$property', $property, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
		$property = Reflections::getProperty($class, $property);
		$annotation = self::createAnnotationReader()->getPropertyAnnotation($property, $annotation);
		return $annotation;
	}

	/**
	 * @return AnnotationReader
	 */
	public static function createAnnotationReader()
	{
		try {
			if (method_exists(AnnotationRegistry::class, "registerLoader")) {
				AnnotationRegistry::registerLoader("class_exists");
			}
			return new AnnotationReader();
		}
		catch (AnnotationException $exception) {
			throw new RuntimeException(sprintf("Creating new instance of '%s' class was not successful.", AnnotationReader::class));
		}
	}

	/**
	 * @param string $class
	 * @param string $annotation
	 * @return ReflectionProperty[]
	 */
	public static function getPropertiesWithAnnotation($class, $annotation)
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
	public static function getMethodAnnotation($class, $method, $annotation)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$method', $method, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
		$method = Reflections::getMethod($class, $method);
		$annotation = self::createAnnotationReader()->getMethodAnnotation($method, $annotation);
		return $annotation;
	}

	/**
	 * @param string $class
	 * @param string $annotation
	 * @return ReflectionMethod[]
	 */
	public static function getMethodsWithAnnotation($class, $annotation)
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
	public static function getClassAnnotation($class, $annotation)
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
