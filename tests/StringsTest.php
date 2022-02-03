<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use OutOfRangeException;
use Pechynho\Test\Traits\AssertExceptionTrait;
use Pechynho\Utility\Strings;
use PHPUnit\Framework\TestCase;

class StringsTest extends TestCase
{
	use AssertExceptionTrait;

	public function testCompare()
	{
		self::assertEquals(0, Strings::compare("a", "a"));
		self::assertEquals(1, Strings::compare("b", "a"));
		self::assertEquals(-1, Strings::compare("a", "b"));
		self::assertNotEquals(0, Strings::compare("a", "A", Strings::COMPARE_CASE_SENSITIVE));
		self::assertEquals(0, Strings::compare("a", "A", Strings::COMPARE_CASE_INSENSITIVE));
		self::assertException(function () { Strings::compare("a", "b", "something"); }, InvalidArgumentException::class);
	}

	public function testPadRight()
	{
		self::assertEquals("a   ", Strings::padRight("a", 4, " "));
		self::assertEquals("abcdefg", Strings::padRight("abcdefg", 4, " "));
		self::assertException(function () { Strings::padRight("a", 5, "--"); }, InvalidArgumentException::class);
	}

	public function testReplace()
	{
		self::assertEquals("PHP", Strings::replace("C#", "C#", "PHP"));
		self::assertEquals("", Strings::replace("", "a", "b"));
		self::assertException(function () { Strings::replace("a", "", "b"); }, InvalidArgumentException::class);

	}

	public function testInsert()
	{
		self::assertEquals("Hello World!", Strings::insert("Hello !", "World", 6));
		self::assertException(function () { Strings::insert("Hello !", "World", 20); }, OutOfRangeException::class);
		self::assertException(function () { Strings::insert("", "World", 6); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::insert("Hello !", "", 6); }, InvalidArgumentException::class);
	}

	public function testStripHtmlTags()
	{
		self::assertEquals("Hello World!", Strings::stripHtmlTags("Hello World!"));
		self::assertEquals("Hello World!", Strings::stripHtmlTags("<b>Hello <i>World</i>!</b>"));
	}

	public function testReplaceMultiple()
	{
		self::assertEquals("Yes No Maybe", Strings::replaceMultiple("1 2 3", ["1" => "Yes", "2" => "No", "3" => "Maybe"]));
		self::assertEquals("", Strings::replaceMultiple("", ["1" => "Yes", "2" => "No", "3" => "Maybe"]));
		self::assertException(function () { Strings::replaceMultiple("1 2 3", ["1" => "Yes", "" => "No", "3" => "Maybe"]); }, InvalidArgumentException::class);
	}

	public function testSubstring()
	{
		self::assertEquals("World!", Strings::substring("Hello World!", 6));
		self::assertEquals("World", Strings::substring("Hello World!", 6, 5));
		self::assertException(function () { Strings::substring("", 5); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::substring("Hello World!", 50); }, OutOfRangeException::class);
		self::assertException(function () { Strings::substring("Hello World!", 0, 50); }, OutOfRangeException::class);
	}

	public function testReplaceCzechSpecialCharsWithASCII()
	{
		self::assertEquals("Rericha", Strings::replaceCzechSpecialCharsWithASCII("Řeřicha"));
	}

	public function testIsNullOrEmpty()
	{
		self::assertEquals(true, Strings::isNullOrEmpty(""));
		self::assertEquals(true, Strings::isNullOrEmpty(null));
		self::assertEquals(false, Strings::isNullOrEmpty(" "));
	}

	public function testTrimStart()
	{
		self::assertEquals("Joe", Strings::trimStart("       Joe"));
		self::assertException(function () { Strings::trimStart("  Joe", []); }, InvalidArgumentException::class);
	}

	public function testEndsWith()
	{
		self::assertEquals(true, Strings::endsWith("Joe", "e"));
		self::assertEquals(true, Strings::endsWith("Joe", ""));
		self::assertEquals(false, Strings::endsWith("", "e"));
		self::assertEquals(true, Strings::endsWith("", ""));
	}

	public function testTrimEnd()
	{
		self::assertEquals("Joe", Strings::trimEnd("Joe    "));
		self::assertException(function () { Strings::trimEnd("Joe   ", []); }, InvalidArgumentException::class);
	}

	public function testTruncate()
	{
		self::assertEquals("J...", Strings::truncate("Johanesburg", 4));
		self::assertException(function () { Strings::truncate("Johanesburg", 3); }, InvalidArgumentException::class);
		self::assertEquals("Joe", Strings::truncate("Joe", "5"));
	}

	public function testIndexOf()
	{
		self::assertEquals(2, Strings::indexOf("Joe", "e"));
		self::assertEquals(-1, Strings::indexOf("Joe", "O"));
		self::assertEquals(-1, Strings::indexOf("", "O"));
		self::assertException(function () { Strings::indexOf("e", ""); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::indexOf("Joe", "e", 3); }, OutOfRangeException::class);
		self::assertException(function () { Strings::indexOf("Joe", "e", -1); }, OutOfRangeException::class);
	}

	public function testLength()
	{
		self::assertEquals(0, Strings::length(""));
		self::assertEquals(5, Strings::length("World"));
	}

	public function testPadLeft()
	{
		self::assertEquals("   a", Strings::padLeft("a", 4, " "));
		self::assertEquals("abcdefg", Strings::padLeft("abcdefg", 4, " "));
		self::assertException(function () { Strings::padLeft("a", 5, "--"); }, InvalidArgumentException::class);
	}

	public function testToLower()
	{
		self::assertEquals("joe", Strings::toLower("Joe"));
		self::assertEquals("world", Strings::toLower("world"));
		self::assertEquals("", Strings::toLower(""));
	}

	public function testLastIndexOfAny()
	{
		self::assertEquals(2, Strings::lastIndexOfAny("Joe", ["u", "m", "e"]));
		self::assertEquals(-1, Strings::lastIndexOfAny("", ["u", "m", "e"]));
		self::assertException(function () { Strings::lastIndexOfAny("Joe", []); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::lastIndexOfAny("Joe", ["a", "e"], -1); }, OutOfRangeException::class);
		self::assertException(function () { Strings::lastIndexOfAny("Joe", ["a", "e"], 4); }, OutOfRangeException::class);
		self::assertException(function () { Strings::lastIndexOfAny("Joe", [""]); }, InvalidArgumentException::class);
	}

	public function testSplit()
	{
		self::assertEquals(["a", "a", "", ""], Strings::split("a a ,", [" ", ","], false));
		self::assertEquals(["a", "a"], Strings::split("a a ,", [" ", ","]));
		self::assertEquals(["a", "a"], Strings::split("a a ,", [" ", ","]));
		self::assertException(function () { Strings::split("a a", ["b", ""]); }, InvalidArgumentException::class);
	}

	public function testTrim()
	{
		self::assertEquals("Joe", Strings::trim("    Joe    "));
		self::assertException(function () { Strings::trim("   Joe   ", []); }, InvalidArgumentException::class);
	}

	public function testFirstToLower()
	{
		self::assertEquals("world", Strings::firstToLower("World"));
		self::assertEquals("", Strings::firstToLower(""));
		self::assertEquals("a", Strings::firstToLower("A"));
	}

	public function testToCharArray()
	{
		self::assertEquals(["a", "b", "c"], Strings::toCharArray("abc"));
		self::assertEquals(["a"], Strings::toCharArray("a"));
		self::assertEquals([], Strings::toCharArray(""));
	}

	public function testToUpper()
	{
		self::assertEquals("JOE", Strings::toUpper("Joe"));
		self::assertEquals("WORLD", Strings::toUpper("WORLD"));
		self::assertEquals("", Strings::toUpper(""));
	}

	public function testUnderscoresToCase()
	{
		self::assertEquals("JoeDoe", Strings::underscoresToCase("joe_doe"));
		self::assertEquals("joeDoe", Strings::underscoresToCase("joe_doe", Strings::CASE_CAMEL));
		self::assertEquals("Joe", Strings::underscoresToCase("joe"));
		self::assertEquals("joe", Strings::underscoresToCase("joe", Strings::CASE_CAMEL));
		self::assertEquals("J", Strings::underscoresToCase("j"));
		self::assertEquals("j", Strings::underscoresToCase("j", Strings::CASE_CAMEL));
		self::assertEquals("", Strings::underscoresToCase(""));
		self::assertEquals("", Strings::underscoresToCase("", Strings::CASE_CAMEL));
		self::assertEquals("", Strings::underscoresToCase("_"));
		self::assertEquals("", Strings::underscoresToCase("_"), Strings::CASE_CAMEL);
	}

	public function testSlugify()
	{
		self::assertEquals("lorem-ipsum", Strings::slugify("Lorem && ipsum", "-", Strings::SLUGIFY_URL));
		self::assertEquals("lorem_ipsum", Strings::slugify("Lorem/ipsum", "_", Strings::SLUGIFY_FILENAME));
		self::assertEquals("lorem ipsum", Strings::slugify("Lorem ipsum?!", " ", Strings::SLUGIFY_NORMAL));
		self::assertEquals("", Strings::slugify("", "-"));
		self::assertException(function () { Strings::slugify("Lorem ipsum", ""); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::slugify("Lorem ipsum", "-", "something"); }, InvalidArgumentException::class);
	}

	public function testCaseToUnderscores()
	{
		self::assertEquals("joe_doe", Strings::caseToUnderscores("JoeDoe"));
		self::assertEquals("joe_doe", Strings::caseToUnderscores("joeDoe"));
		self::assertEquals("joe", Strings::caseToUnderscores("Joe"));
		self::assertEquals("j", Strings::caseToUnderscores("J"));
		self::assertEquals("", Strings::caseToUnderscores(""));
	}

	public function testFirstToUpper()
	{
		self::assertEquals("World", Strings::firstToUpper("world"));
		self::assertEquals("", Strings::firstToUpper(""));
		self::assertEquals("A", Strings::firstToUpper("a"));
	}

	public function testCaseToDashes()
	{
		self::assertEquals("joe-doe", Strings::caseToDashes("JoeDoe"));
		self::assertEquals("joe-doe", Strings::caseToDashes("joeDoe"));
		self::assertEquals("joe", Strings::caseToDashes("Joe"));
		self::assertEquals("j", Strings::caseToDashes("J"));
		self::assertEquals("", Strings::caseToDashes(""));
	}

	public function testContains()
	{
		self::assertEquals(true, Strings::contains("Joe", "e"));
		self::assertEquals(false, Strings::contains("Joe", "P"));
		self::assertEquals(false, Strings::contains("", "P"));
		self::assertException(function () { Strings::contains("a", ""); }, InvalidArgumentException::class);
	}

	public function testIndexOfAny()
	{
		self::assertEquals(2, Strings::indexOfAny("Joe", ["u", "m", "e"]));
		self::assertEquals(-1, Strings::indexOfAny("", ["u", "m", "e"]));
		self::assertException(function () { Strings::indexOfAny("Joe", []); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::indexOfAny("Joe", ["a", "e"], -1); }, OutOfRangeException::class);
		self::assertException(function () { Strings::indexOfAny("Joe", ["a", "e"], 4); }, OutOfRangeException::class);
		self::assertException(function () { Strings::indexOfAny("Joe", [""]); }, InvalidArgumentException::class);
	}

	public function testStartsWith()
	{
		self::assertEquals(true, Strings::startsWith("Joe", "J"));
		self::assertEquals(true, Strings::startsWith("Joe", ""));
		self::assertEquals(false, Strings::startsWith("", "e"));
		self::assertEquals(true, Strings::startsWith("", ""));
	}

	public function testRemove()
	{
		self::assertEquals("Hello", Strings::remove("Hello World!", 5));
		self::assertEquals("Hello", Strings::remove("Hello World!", 5, 7));
		self::assertEquals("Hello!", Strings::remove("Hello World!", 5, 6));
		self::assertException(function () { Strings::remove("", 6); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::remove("Hello", 7); }, OutOfRangeException::class);
		self::assertException(function () { Strings::remove("Hello", 0, 7); }, OutOfRangeException::class);
	}

	public function testDashesToCase()
	{
		self::assertEquals("JoeDoe", Strings::dashesToCase("joe-doe"));
		self::assertEquals("joeDoe", Strings::dashesToCase("joe-doe", Strings::CASE_CAMEL));
		self::assertEquals("Joe", Strings::dashesToCase("joe"));
		self::assertEquals("joe", Strings::dashesToCase("joe", Strings::CASE_CAMEL));
		self::assertEquals("J", Strings::dashesToCase("j"));
		self::assertEquals("j", Strings::dashesToCase("j", Strings::CASE_CAMEL));
		self::assertEquals("", Strings::dashesToCase(""));
		self::assertEquals("", Strings::dashesToCase("", Strings::CASE_CAMEL));
		self::assertEquals("", Strings::dashesToCase("-"));
		self::assertEquals("", Strings::dashesToCase("-", Strings::CASE_CAMEL));
	}

	public function testIsNullOrWhiteSpace()
	{
		self::assertEquals(true, Strings::isNullOrWhiteSpace(null));
		self::assertEquals(true, Strings::isNullOrWhiteSpace(""));
		self::assertEquals(true, Strings::isNullOrWhiteSpace("     "));
		self::assertEquals(false, Strings::isNullOrWhiteSpace("Hello"));
	}

	public function testLastIndexOf()
	{
		self::assertEquals(2, Strings::lastIndexOf("Joe", "e"));
		self::assertEquals(-1, Strings::lastIndexOf("Joe", "O"));
		self::assertEquals(-1, Strings::lastIndexOf("", "O"));
		self::assertException(function () { Strings::lastIndexOf("e", ""); }, InvalidArgumentException::class);
		self::assertException(function () { Strings::lastIndexOf("Joe", "e", 3); }, OutOfRangeException::class);
		self::assertException(function () { Strings::lastIndexOf("Joe", "e", -1); }, OutOfRangeException::class);
	}

	public function testJoin()
	{
		$items = ["Joe", "Michelle", "Johny"];
		self::assertEquals("Joe, Michelle and Johny", Strings::join($items, ", ", " and "));
		self::assertEquals("Joe, Michelle, Johny", Strings::join($items, ", "));
		self::assertEquals("Joe", Strings::join(["Joe"], ", "));
		self::assertEquals("Joe", Strings::join(["Joe"], ", ", " and "));
		self::assertEquals("Joe", Strings::join(["Joe"], ", ", ", "));
		self::assertEquals("", Strings::join([], ", "));
		self::assertEquals("", Strings::join([], ", ", " and "));
		self::assertEquals("", Strings::join([], ", ", ", "));
		self::assertEquals("Joe and John", Strings::join(["Joe", "John"], ", ", " and "));
	}

	public function testReverse()
	{
		self::assertEquals("johA", Strings::reverse("Ahoj"));
		self::assertEquals("", Strings::reverse(""));
	}

	public function testSplitCase()
	{
		self::assertEquals(["find", "By", "ID"], Strings::splitByCase("findByID"));
	}

	public function testToAscii()
	{
		self::assertEquals("aAcCeEeEiInNoOrRsStTuUuUyYzZ", Strings::toAscii("áÁčČěĚéÉíÍňŇóÓřŘšŠťŤúÚůŮýÝžŽ"));
		self::assertEquals("shi", Strings::toAscii("是"));
	}
}
