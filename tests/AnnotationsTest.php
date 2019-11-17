<?php

namespace Pechynho\Test;

use Doctrine\Common\Annotations\AnnotationReader;
use Pechynho\Test\Model\MyAnnotation;
use Pechynho\Test\Model\Worker;
use Pechynho\Utility\Annotations;
use PHPUnit\Framework\TestCase;
use VladaHejda\AssertException;

class AnnotationsTest extends TestCase
{
	use AssertException;

	public function testGetMethodAnnotation()
	{
		self::assertInstanceOf(MyAnnotation::class, Annotations::getMethodAnnotation(Worker::class, "getSurname", MyAnnotation::class));
		self::assertEquals(null, Annotations::getMethodAnnotation(Worker::class, "getForename", MyAnnotation::class));
	}

	public function testCreateAnnotationReader()
	{
		self::assertInstanceOf(AnnotationReader::class, Annotations::createAnnotationReader());
	}

	public function testGetPropertyAnnotation()
	{
		self::assertInstanceOf(MyAnnotation::class, Annotations::getPropertyAnnotation(Worker::class, "job", MyAnnotation::class));
		self::assertEquals(null, Annotations::getPropertyAnnotation(Worker::class, "forename", MyAnnotation::class));
	}

	public function testGetClassAnnotation()
	{
		self::assertInstanceOf(MyAnnotation::class, Annotations::getClassAnnotation(Worker::class, MyAnnotation::class));
	}
}
