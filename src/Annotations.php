<?php


namespace Pechynho\Utility;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
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
	public static function getPropertyAnnotation(string $class, string $property, string $annotation)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$property', $property, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
		$property = Reflections::getProperty($class, $property);
		$annotation = self::createAnnotationReader()->getPropertyAnnotation($property, $annotation);
		return $annotation;
	}

	/**
	 * @param string $class
	 * @param string $method
	 * @param string $annotation
	 * @return object|null
	 */
	public static function getMethodAnnotation(string $class, string $method, string $annotation)
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
	 * @return object|null
	 */
	public static function getClassAnnotation(string $class, string $annotation)
	{
		ParamsChecker::notWhiteSpaceOrNullString('$class', $class, __METHOD__);
		ParamsChecker::notWhiteSpaceOrNullString('$annotation', $annotation, __METHOD__);
		$reflectionClass = Reflections::createReflectionClass($class);
		$annotationReader = self::createAnnotationReader();
		while ($reflectionClass !== false)
		{
			$annotationInstance = $annotationReader->getClassAnnotation($reflectionClass, $annotation);
			if ($annotationInstance !== null)
			{
				return $annotationInstance;
			}
			$reflectionClass = $reflectionClass->getParentClass();
		}
		return null;
	}

	/**
	 * @return AnnotationReader
	 */
	public static function createAnnotationReader()
	{
		try
		{
			if (method_exists(AnnotationRegistry::class, "registerLoader"))
			{
				AnnotationRegistry::registerLoader("class_exists");
			}
			return new AnnotationReader();
		}
		catch (AnnotationException $exception)
		{
			throw new RuntimeException(sprintf("Creating new instance of '%s' class was not successful.", AnnotationReader::class));
		}
	}
}
