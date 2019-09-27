<?php

namespace Pechynho\Test;

use InvalidArgumentException;
use Pechynho\Utility\FileSystem;
use Pechynho\Utility\Strings;
use PHPUnit\Framework\TestCase;
use VladaHejda\AssertException;

class FileSystemTest extends TestCase
{
	use AssertException;

	private $baseDir = ".";

	private $structure = [
		"var" => [
			"directory_1" => ["directory_2", "directory_3" => ["file_1.txt"], "file_2.txt"],
			"directory_4" => ["directory_5" => ["directory_6", "directory_7" => ["file_3.txt"], "file_4.txt"], "file_5.txt"]
		]
	];

	protected function setUp()
	{
		$this->destroyEnvironment();
		$this->prepareEnvironment();
	}

	protected function tearDown()
	{
		$this->destroyEnvironment();
	}

	/**
	 * @param string $name
	 * @param array  $items
	 */
	private function createStructure($name, $items)
	{
		if (!file_exists($name))
		{
			mkdir($name);
		}
		foreach ($items as $key => $item)
		{
			if (is_array($item))
			{
				$this->createStructure($name . "/" . $key, $item);
				continue;
			}
			if (Strings::endsWith($item, ".txt") && !file_exists($name . "/" . $item))
			{
				$file = fopen($name . "/" . $item, "w");
				fwrite($file, $item);
				fclose($file);
				continue;
			}
			if (!file_exists($name . "/" . $item)) mkdir($name . "/" . $item);
		}
	}

	/**
	 * @param string $directory
	 */
	private function removeDirectory(string $directory)
	{
		if (!file_exists($directory))
		{
			return;
		}
		$items = array_diff(scandir($directory), [".", ".."]);
		foreach ($items as $item)
		{
			$item = $directory . "/" . $item;
			if (file_exists($item) and is_dir($item))
			{
				$this->removeDirectory($item);
				continue;
			}
			unlink($item);
		}
		rmdir($directory);
	}

	private function prepareEnvironment()
	{
		$this->createStructure($this->baseDir . "/var", $this->structure["var"]);
	}

	private function destroyEnvironment()
	{
		$this->removeDirectory($this->baseDir . "/var");
	}

	public function testCombinePath()
	{
		self::assertEquals("foo/bar/doe", FileSystem::combinePath("foo", "bar", "doe"));
		self::assertException(function () { FileSystem::combinePath(); }, InvalidArgumentException::class);
	}

	public function testCopy()
	{
		$source = $this->baseDir . "/var/directory_1/file_2.txt";
		$destination = $this->baseDir . "/var/directory_1/copied_file_2.txt";
		FileSystem::copy($source, $destination);
		self::assertEquals(true, FileSystem::isFile($destination));
		$source = $this->baseDir . "/var/directory_1";
		$destination = $this->baseDir . "/var/copied_directory_1";
		FileSystem::copy($source, $destination);
		self::assertEquals(true, FileSystem::isDirectory($destination));
		$source = $this->baseDir . "/var/copied_directory_1";
		$destination = $this->baseDir . "/var/directory_4";
		FileSystem::copy($source, $destination, true);
		self::assertEquals(true, FileSystem::isFile($this->baseDir . "/var/directory_4/copied_file_2.txt"));
	}

	public function testAppend()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::append($filename, "Appended line!");
		self::assertEquals(["file_2.txt", "Appended line!"], FileSystem::readAllLines($filename));
		$filename = $this->baseDir . "/var/directory_1/new_file.txt";
		FileSystem::append($filename, "New line!");
		self::assertEquals(["New line!"], FileSystem::readAllLines($filename));
	}

	public function testIsFileEmpty()
	{
		$filename = $this->baseDir . "/var/directory_1/new_file.txt";
		FileSystem::write($filename, "", true);
		self::assertEquals(true, FileSystem::isFileEmpty($filename));
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertEquals(false, FileSystem::isFileEmpty($filename));
	}

	public function testReadAllLines()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertEquals(["file_2.txt"], FileSystem::readAllLines($filename));
	}

	public function testWrite()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::write($filename, "Test", true);
		self::assertEquals("Test", FileSystem::readAllLines($filename)[0]);
		self::assertException(function () { FileSystem::write($this->baseDir . "/var/directory_1/file_2.txt", "Test") ;}, InvalidArgumentException::class);
	}

	public function testIsDirectory()
	{
		$path = $this->baseDir . "/var/directory_1";
		self::assertTrue(FileSystem::isDirectory($path));
		$path .= "/file_2.txt";
		self::assertFalse(FileSystem::isDirectory($path));
	}

	public function testDelete()
	{
		$directory = $this->baseDir . "/var";
		FileSystem::delete($directory);
		self::assertFalse(FileSystem::isDirectory($directory));
	}

	public function testReadLineByLine()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::append($filename, "Appended line!");
		$count = 0;
		foreach (FileSystem::readLineByLine($filename) as $line)
		{
			$count++;
		}
		self::assertEquals(2, $count);
	}

	public function testRename()
	{
		$source = $this->baseDir . "/var/directory_1";
		$destination =  $this->baseDir . "/var/renamed_directory_1";
		FileSystem::rename($source, $destination);
		self::assertTrue(FileSystem::isDirectory($destination));
		$this->prepareEnvironment();
		$source = $this->baseDir . "/var/directory_1/file_2.txt";
		$destination =  $this->baseDir . "/var/directory_1/renamed_file_2.txt";
		FileSystem::rename($source, $destination);
		self::assertTrue(FileSystem::isFile($destination));
	}

	public function testCreateDirectory()
	{
		$directory = $this->baseDir . "/var/directory_1/new_directory_1/new_directory_2";
		FileSystem::createDirectory($directory);
		self::assertTrue(FileSystem::isDirectory($directory));
	}

	public function testIsFile()
	{
		$path = $this->baseDir . "/var/directory_1";
		self::assertFalse(FileSystem::isFile($path));
		$path .= "/file_2.txt";
		self::assertTrue(FileSystem::isFile($path));
	}
}
