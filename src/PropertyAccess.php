<?php

namespace Pechynho\Utility;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess as SymfonyPropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Throwable;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class PropertyAccess
{
    private static ?PropertyAccessorInterface $propertyAccessor = null;

    /**
     * @return PropertyAccessorInterface
     */
    private static function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (self::$propertyAccessor !== null) {
            return self::$propertyAccessor;
        }
        if (!class_exists(SymfonyPropertyAccess::class)) {
            throw new RuntimeException(
                sprintf(
                    '%s is required to use this library. Please run composer require symfony/property-access.',
                    SymfonyPropertyAccess::class
                )
            );
        }
        try {
            self::$propertyAccessor = SymfonyPropertyAccess::createPropertyAccessor();
            return self::$propertyAccessor;
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Calling %s::createPropertyAccessor was not successful.', SymfonyPropertyAccess::class),
                intval($e->getCode()),
                $e
            );
        }
    }

    /**
     * @param object|array $objectOrArray
     * @param callable|string|PropertyPathInterface $propertyPath
     * @param bool $throwException
     * @param mixed|null $defaultValue
     * @param bool $tryReflection
     * @return mixed
     */
    public static function getValue(
        object|array $objectOrArray,
        callable|PropertyPathInterface|string $propertyPath,
        bool $throwException = true,
        mixed $defaultValue = null,
        bool $tryReflection = false
    ): mixed {
        if (!is_object($objectOrArray) && !is_array($objectOrArray)) {
            throw new InvalidArgumentException('Parameter $objectOrArray has to be object or array.');
        }
        if (!is_callable($propertyPath) && !is_string(
                $propertyPath
            ) && !$propertyPath instanceof PropertyPathInterface) {
            throw new InvalidArgumentException(
                'Parameter $propertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.'
            );
        }
        if (is_callable($propertyPath) && !is_string($propertyPath)) {
            try {
                return $propertyPath($objectOrArray);
            } catch (Throwable $exception) {
                if ($throwException) {
                    throw new RuntimeException(
                        $exception->getMessage(),
                        $exception->getCode(),
                        $exception->getPrevious()
                    );
                }
                return $defaultValue;
            }
        }
        $propertyAccessor = self::getPropertyAccessor();
        try {
            if (is_array($objectOrArray) && is_string($propertyPath) && preg_match(
                    "/^\[[\S]+\]$/",
                    $propertyPath
                ) === 0) {
                $propertyPath = "[" . $propertyPath . "]";
            }
            return $propertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch (Throwable $exception) {
            if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath)) {
                try {
                    $property = Reflections::getProperty(get_class($objectOrArray), $propertyPath);
                    if ($property->isProtected() || $property->isPrivate()) {
                        $property->setAccessible(true);
                    }
                    $value = $property->getValue($objectOrArray);
                    if ($property->isProtected() || $property->isPrivate()) {
                        $property->setAccessible(false);
                    }
                    return $value;
                } catch (Throwable $reflectionException) {
                    if ($throwException) {
                        throw new RuntimeException(
                            $reflectionException->getMessage(),
                            $reflectionException->getCode(),
                            $reflectionException->getPrevious()
                        );
                    }
                    return $defaultValue;
                }
            }
            if ($throwException) {
                throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
            }
            return $defaultValue;
        }
    }

    /**
     * @param object|array $objectOrArray
     * @param callable|string|PropertyPathInterface $propertyPath
     * @param mixed $value
     * @param bool $throwException
     * @param bool $tryReflection
     */
    public static function setValue(
        object|array &$objectOrArray,
        callable|PropertyPathInterface|string $propertyPath,
        mixed $value,
        bool $throwException = true,
        bool $tryReflection = false
    ): void {
        if (!is_object($objectOrArray) && !is_array($objectOrArray)) {
            throw new InvalidArgumentException('Parameter $objectOrArray has to be object or array.');
        }
        if (!is_callable($propertyPath) && !is_string(
                $propertyPath
            ) && !$propertyPath instanceof PropertyPathInterface) {
            throw new InvalidArgumentException(
                'Parameter $propertyPath has to be callable, string or instance of ' . PropertyPathInterface::class . '.'
            );
        }
        if (is_callable($propertyPath) && !is_string($propertyPath)) {
            try {
                $propertyPath($objectOrArray, $value);
                return;
            } catch (Throwable $exception) {
                if ($throwException) {
                    throw new RuntimeException(
                        $exception->getMessage(),
                        $exception->getCode(),
                        $exception->getPrevious()
                    );
                }
            }
        }
        $propertyAccessor = self::getPropertyAccessor();
        try {
            if (is_array($objectOrArray) && is_string($propertyPath) && preg_match(
                    "/^\[[\S]+\]$/",
                    $propertyPath
                ) === 0) {
                $propertyPath = "[" . $propertyPath . "]";
            }
            $propertyAccessor->setValue($objectOrArray, $propertyPath, $value);
            return;
        } catch (Throwable $exception) {
            if ($tryReflection && is_object($objectOrArray) && is_string($propertyPath)) {
                try {
                    $property = Reflections::getProperty(get_class($objectOrArray), $propertyPath);
                    if ($property->isProtected() || $property->isPrivate()) {
                        $property->setAccessible(true);
                    }
                    $property->setValue($objectOrArray, $value);
                    if ($property->isProtected() || $property->isPrivate()) {
                        $property->setAccessible(false);
                    }
                    return;
                } catch (Throwable $reflectionException) {
                    if ($throwException) {
                        throw new RuntimeException(
                            $reflectionException->getMessage(),
                            $reflectionException->getCode(),
                            $reflectionException->getPrevious()
                        );
                    }
                }
            }
            if ($throwException) {
                throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
            }
        }
    }
}
