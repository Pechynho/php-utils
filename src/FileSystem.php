<?php


namespace Pechynho\Utility;


use InvalidArgumentException;

class FileSystem
{
	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	public static function copy(string $source, string $destination, bool $overwrite = false)
	{
		if (FileSystem::isDirectory($source))
		{
			FileSystem::copyDirectory($source, $destination, $overwrite);
			return;
		}
		if (FileSystem::isFile($source))
		{
			FileSystem::copyFile($source, $destination, $overwrite);
			return;
		}
		throw new InvalidArgumentException("Path '$source' does not exist.");
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	private static function copyDirectory(string $source, string $destination, bool $overwrite)
	{
		if (!FileSystem::isDirectory($source))
		{
			throw new InvalidArgumentException("Directory '$source' does not exist or is not a directory.");
		}
		if (FileSystem::isDirectory($destination) && !$overwrite)
		{
			throw new InvalidArgumentException("Directory '$destination' already exists.");
		}
		if (!FileSystem::isDirectory($destination))
		{
			FileSystem::createDirectory($destination);
		}
		$items = array_diff(scandir($source), [".", ".."]);
		foreach ($items as $item)
		{
			$sourceItem = FileSystem::combinePath($source, $item);
			$destinationItem = FileSystem::combinePath($destination, $item);
			if (FileSystem::isDirectory($sourceItem))
			{
				FileSystem::copyDirectory($sourceItem, $destinationItem, $overwrite);
				continue;
			}
			FileSystem::copyFile($sourceItem, $destinationItem, $overwrite);
		}
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	private static function copyFile(string $source, string $destination, bool $overwrite = false)
	{
		if (!FileSystem::isFile($source))
		{
			throw new InvalidArgumentException("File '$source' does not exist.");
		}
		if (FileSystem::isFile($destination) && !$overwrite)
		{
			throw new InvalidArgumentException("File '$destination' already exists.");
		}
		copy($source, $destination);
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	public static function rename(string $source, string $destination, bool $overwrite = false)
	{
		if (!file_exists($source))
		{
			throw new InvalidArgumentException("File or directory '$source' does not exist.");
		}
		if (file_exists($destination) && !$overwrite)
		{
			throw new InvalidArgumentException("File or directory '$destination' already exists.");
		}
		rename($source, $destination);
	}

	/**
	 * @param string $filename
	 * @param string $text
	 * @param bool   $overwrite
	 */
	public static function write(string $filename, string $text, bool $overwrite = false)
	{
		if (FileSystem::isFile($filename) && !$overwrite)
		{
			throw new InvalidArgumentException("File '$filename' already exists.");
		}
		if (FileSystem::isFile($filename)) FileSystem::delete($filename);
		$file = fopen($filename, "w");
		fwrite($file, $text);
		fclose($file);
	}

	/**
	 * @param string $filename
	 * @param string $text
	 * @param bool   $newLine
	 */
	public static function append(string $filename, string $text, bool $newLine = true)
	{
		$file = fopen($filename, "a");
		fwrite($file, FileSystem::isFileEmpty($filename) ? $text : PHP_EOL . $text);
		fclose($file);
	}

	/**
	 * @param string $filename
	 * @return array
	 */
	public static function readAllLines(string $filename): array
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		$file = fopen($filename, "r");
		$lines = [];
		while (!feof($file))
		{
			$lines[] = Strings::trimEnd(fgets($file), [PHP_EOL]);
		}
		fclose($file);
		return $lines;
	}

	public static function isFileEmpty(string $filename): bool
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		return filesize($filename) == 0;
	}

	/**
	 * @param string $filename
	 * @return iterable
	 */
	public static function readLineByLine(string $filename): iterable
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		$file = fopen($filename, "r");
		while (!feof($file))
		{
			yield fgets($file);
		}
		fclose($file);
	}

	/**
	 * @param string[] ...$paths
	 * @return string
	 */
	public static function combinePath(...$paths): string
	{
		if (Arrays::isEmpty($paths))
		{
			throw new InvalidArgumentException("You have to provide at least one path.");
		}
		$finalPath = "";
		foreach ($paths as $index => $path)
		{
			$finalPath = $finalPath . ($index == 0 ? "" : "/") . $path;
		}
		return $finalPath;
	}

	/**
	 * @param string $path
	 */
	public static function delete(string $path)
	{
		if (FileSystem::isDirectory($path))
		{
			FileSystem::deleteDirectory($path);
			return;
		}
		if (FileSystem::isFile($path))
		{
			FileSystem::deleteFile($path);
			return;
		}
		throw new InvalidArgumentException("Path '$path' does not exist.");
	}

	/**
	 * @param string $filename
	 */
	private static function deleteFile(string $filename)
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist or is not a file.");
		}
		unlink($filename);
	}

	/**
	 * @param string $directory
	 */
	private static function deleteDirectory(string $directory)
	{
		if (!FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Directory '$directory' does not exist or is not a directory.");
		}
		$items = array_diff(scandir($directory), [".", ".."]);
		foreach ($items as $item)
		{
			$item = FileSystem::combinePath($directory, $item);
			if (FileSystem::isDirectory($item))
			{
				FileSystem::deleteDirectory($item);
				continue;
			}
			FileSystem::deleteFile($item);
		}
		rmdir($directory);
	}

	/**
	 * @param string $directory
	 * @param int    $mode
	 */
	public static function createDirectory(string $directory, int $mode = 0777)
	{
		if (!FileSystem::isDirectory($directory))
		{
			mkdir($directory, $mode, true);
		}
	}

	/**
	 * @param string $directory
	 * @return bool
	 */
	public static function isDirectory(string $directory): bool
	{
		return file_exists($directory) && is_dir($directory);
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public static function isFile(string $filename): bool
	{
		return file_exists($filename) && !is_dir($filename);
	}
}