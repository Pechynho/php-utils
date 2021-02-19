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

	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		$this->prepareEnvironment();
	}

	private function prepareEnvironment()
	{
		$this->destroyEnvironment();
		$this->createStructure($this->baseDir . "/var", $this->structure["var"]);
	}

	private function destroyEnvironment()
	{
		$this->removeDirectory($this->baseDir . "/var");
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
	 * @inheritDoc
	 */
	protected function tearDown()
	{
		$this->destroyEnvironment();
	}

	public function testCombinePath()
	{
		self::assertEquals("foo/bar/doe", FileSystem::combinePath("foo", "bar", "doe"));
		self::assertEquals("foo/joe/doe/bar/doe", FileSystem::combinePath("foo//joe///doe", "bar", "doe"));
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
		self::assertEquals("file_2.txt", FileSystem::readAllText($this->baseDir . "/var/directory_4/copied_file_2.txt"));
		$this->prepareEnvironment();
		FileSystem::rename($this->baseDir . "/var/directory_1/file_2.txt", $this->baseDir . "/var/directory_1/file_5.txt");
		self::assertException(function () { FileSystem::copy($this->baseDir . "/var/directory_1", $this->baseDir . "/var/directory_4"); }, InvalidArgumentException::class);
		FileSystem::copy($this->baseDir . "/var/directory_1", $this->baseDir . "/var/directory_4", true);
		self::assertTrue(FileSystem::isFile($this->baseDir . "/var/directory_4/file_5.txt"));
		self::assertEquals("file_2.txt", FileSystem::readAllText($this->baseDir . "/var/directory_4/file_5.txt"));
		$this->prepareEnvironment();
		self::assertException(function () { FileSystem::copy("", $this->baseDir . "/var/directory_4"); }, InvalidArgumentException::class);
		self::assertException(function () { FileSystem::copy($this->baseDir . "/var/directory_4", ""); }, InvalidArgumentException::class);
	}

	public function testAppend()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::append($filename, "Appended line!");
		self::assertEquals(["file_2.txt", "Appended line!"], FileSystem::readAllLines($filename));
		$filename = $this->baseDir . "/var/directory_1/new_file.txt";
		FileSystem::append($filename, "New line!");
		self::assertEquals("New line!", FileSystem::readAllText($filename));
		FileSystem::append($filename, " Another text.", false);
		self::assertEquals("New line! Another text.", FileSystem::readAllText($filename));
		self::assertException(function () { FileSystem::append("", "Hello world!"); }, InvalidArgumentException::class);
	}

	public function testIsEmpty()
	{
		$filename = $this->baseDir . "/var/directory_1/new_file.txt";
		FileSystem::write($filename, "", true);
		self::assertEquals(true, FileSystem::isEmpty($filename));
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertEquals(false, FileSystem::isEmpty($filename));
		self::assertException(function () { FileSystem::isEmpty(""); }, InvalidArgumentException::class);
	}

	public function testReadAllLines()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertEquals(["file_2.txt"], FileSystem::readAllLines($filename));
		FileSystem::append($filename, "Another line!");
		self::assertEquals(["file_2.txt" . PHP_EOL, "Another line!"], FileSystem::readAllLines($filename, false));
		self::assertException(function () { FileSystem::readAllLines(""); }, InvalidArgumentException::class);
	}

	public function testReadAllText()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertEquals("file_2.txt", FileSystem::readAllText($filename));
		FileSystem::append($filename, "Another line!");
		self::assertEquals("file_2.txt" . PHP_EOL . "Another line!", FileSystem::readAllText($filename));
		self::assertException(function () { FileSystem::readAllText(""); }, InvalidArgumentException::class);
	}

	public function testWrite()
	{
		$filename = $this->baseDir . "/var/directory_1/new_file.txt";
		FileSystem::write($filename, "New file");
		self::assertEquals("New file", FileSystem::readAllText($filename));
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::write($filename, "Test", true);
		self::assertEquals("Test", FileSystem::readAllText($filename));
		self::assertException(function () use ($filename) { FileSystem::write($filename, "Test"); }, InvalidArgumentException::class);
		self::assertException(function () { FileSystem::write("", "Hello world!"); }, InvalidArgumentException::class);
	}

	public function testIsDirectory()
	{
		$path = $this->baseDir . "/var/directory_1";
		self::assertTrue(FileSystem::isDirectory($path));
		$path .= "/file_2.txt";
		self::assertFalse(FileSystem::isDirectory($path));
		self::assertFalse(FileSystem::isDirectory(""));
	}

	public function testDelete()
	{
		$filename = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::delete($filename);
		self::assertFalse(FileSystem::isFile($filename));
		$directory = $this->baseDir . "/var";
		FileSystem::delete($directory);
		self::assertFalse(FileSystem::isDirectory($directory));
		self::assertException(function () { FileSystem::delete(""); }, InvalidArgumentException::class);
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
		self::assertException(function () {
			foreach (FileSystem::readLineByLine("") as $line)
			{
			}
		}, InvalidArgumentException::class);
	}

	public function testRename()
	{
		$source = $this->baseDir . "/var/directory_1";
		$destination = $this->baseDir . "/var/renamed_directory_1";
		FileSystem::rename($source, $destination);
		self::assertTrue(FileSystem::isDirectory($destination));
		$this->prepareEnvironment();
		$source = $this->baseDir . "/var/directory_1/file_2.txt";
		$destination = $this->baseDir . "/var/directory_1/renamed_file_2.txt";
		FileSystem::rename($source, $destination);
		self::assertTrue(FileSystem::isFile($destination));
		$source = $destination;
		$destination = $this->baseDir . "/var/directory_1/file_2.txt";
		FileSystem::copy($source, $destination);
		self::assertException(function () use ($source) { FileSystem::rename($source, ""); }, InvalidArgumentException::class);
		self::assertException(function () use ($destination) { FileSystem::rename("", $destination); }, InvalidArgumentException::class);
		self::assertException(function () use ($source, $destination) { FileSystem::rename($source, $destination); }, InvalidArgumentException::class);
		FileSystem::rename($source, $destination, true);
		self::assertTrue(FileSystem::isFile($destination));
	}

	public function testCreateDirectory()
	{
		$directory = $this->baseDir . "/var/directory_1/new_directory_1/new_directory_2";
		FileSystem::createDirectory($directory);
		self::assertTrue(FileSystem::isDirectory($directory));
		FileSystem::createDirectory($directory);
		self::assertTrue(FileSystem::isDirectory($directory));
		self::assertException(function () { FileSystem::createDirectory(""); }, InvalidArgumentException::class);
	}

	public function testIsFile()
	{
		$path = $this->baseDir . "/var/directory_1";
		self::assertFalse(FileSystem::isFile($path));
		$path .= "/file_2.txt";
		self::assertTrue(FileSystem::isFile($path));
		self::assertFalse(FileSystem::isFile(""));
	}

	public function testSize()
	{
		$path = $this->baseDir . "/var";
		self::assertEquals(50, FileSystem::size($path));
		$path = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertEquals(10, FileSystem::size($path));
		self::assertException(function () { FileSystem::size(""); }, InvalidArgumentException::class);
	}

	public function testScanDirectory()
	{
		$path = $this->baseDir . "/var";
		self::assertEquals(5, count(FileSystem::scanDirectory($path, FileSystem::SCAN_FILES, true)));
		self::assertEquals(0, count(FileSystem::scanDirectory($path, FileSystem::SCAN_FILES)));
		self::assertEquals(7, count(FileSystem::scanDirectory($path, FileSystem::SCAN_DIRECTORIES, true)));
		self::assertEquals(2, count(FileSystem::scanDirectory($path, FileSystem::SCAN_DIRECTORIES)));
		self::assertEquals(2, count(FileSystem::scanDirectory($path, FileSystem::SCAN_ALL)));
		self::assertEquals(12, count(FileSystem::scanDirectory($path, FileSystem::SCAN_ALL, true)));
		self::assertException(function () use ($path) { FileSystem::scanDirectory(""); }, InvalidArgumentException::class);
		self::assertException(function () use ($path) { FileSystem::scanDirectory($path, ""); }, InvalidArgumentException::class);
	}

	public function testNormalizePath()
	{
		self::assertEquals("C:/Test/Test/Test/Test/Test", FileSystem::normalizePath("C:\\Test\Test/Test\\\\Test////Test"));
	}

	public function testExists()
	{
		$path = $this->baseDir . "/var";
		self::assertSame(true, FileSystem::exists($path));
		$path = $this->baseDir . "/var/directory_1/file_2.txt";
		self::assertSame(true, FileSystem::exists($path));
		self::assertSame(false, FileSystem::exists(""));
	}

	public function testIterateDirectory()
	{
		$files = [];
		$directories = [];
		$all = [];
		$filesRecursively = [];
		$directoriesRecursively = [];
		$allRecursively = [];
		foreach (FileSystem::iterateDirectory($this->baseDir . "/var", FileSystem::SCAN_FILES) as $path)
		{
			$files[] = $path;
		}
		foreach (FileSystem::iterateDirectory($this->baseDir. "/var", FileSystem::SCAN_DIRECTORIES) as $path)
		{
			$directories[] = $path;
		}
		foreach (FileSystem::iterateDirectory($this->baseDir. "/var", FileSystem::SCAN_ALL) as $path)
		{
			$all[] = $path;
		}
		foreach (FileSystem::iterateDirectory($this->baseDir. "/var", FileSystem::SCAN_FILES, true) as $path)
		{
			$filesRecursively[] = $path;
		}
		foreach (FileSystem::iterateDirectory($this->baseDir. "/var", FileSystem::SCAN_DIRECTORIES, true) as $path)
		{
			$directoriesRecursively[] = $path;
		}
		foreach (FileSystem::iterateDirectory($this->baseDir. "/var", FileSystem::SCAN_ALL, true) as $path)
		{
			$allRecursively[] = $path;
		}
		self::assertEquals(0, count($files));
		self::assertEquals(2, count($directories));
		self::assertEquals(2, count($all));
		self::assertEquals(5, count($filesRecursively));
		self::assertEquals(7, count($directoriesRecursively));
		self::assertEquals(12, count($allRecursively));
	}

	public function testGenerateFilename()
	{
		self::assertEquals(true, !FileSystem::exists(FileSystem::generateFilename()));
		self::assertEquals(true, !FileSystem::exists(FileSystem::generateFilename($this->baseDir . "/var")));
	}

	public function testCreateTempFile()
	{
		$temp = FileSystem::createTempFile();
		self::assertEquals(true, FileSystem::isFile($temp));
		$temp = FileSystem::createTempFile($this->baseDir . "/var");
		self::assertEquals(true, FileSystem::isFile($temp));
	}

	public function testCreateTempDirectory()
	{
		$temp = FileSystem::createTempDirectory();
		self::assertEquals(true, FileSystem::isDirectory($temp));
		$temp = FileSystem::createTempDirectory($this->baseDir . "/var");
		self::assertEquals(true, FileSystem::isDirectory($temp));
	}

	public function testZip()
	{
		$zip = FileSystem::zip($this->baseDir . "/var");
		self::assertEquals(true, FileSystem::isFile($zip));
	}

	public function testUnzip()
	{
		$zip = FileSystem::zip($this->baseDir . "/var");
		$unzip = FileSystem::unzip($zip);
		self::assertEquals(true, FileSystem::isDirectory($unzip));
	}
}
