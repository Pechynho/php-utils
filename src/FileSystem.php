<?php


namespace Pechynho\Utility;


use InvalidArgumentException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class FileSystem
{
	/** @var string */
	public const SCAN_ALL = "SCAN_ALL";

	/** @var string */
	public const SCAN_FILES = "SCAN_FILES";

	/** @var string */
	public const SCAN_DIRECTORIES = "SCAN_DIRECTORIES";

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	public static function copy(string $source, string $destination, bool $overwrite = false)
	{
		if (Strings::isNullOrWhiteSpace($destination))
		{
			throw new InvalidArgumentException("Given value '$destination' is not valid path.");
		}
		if (!file_exists($source))
		{
			throw new InvalidArgumentException("Path '$source' does not exist.");
		}
		if (FileSystem::isFile($source))
		{
			if (FileSystem::isFile($destination) && !$overwrite) throw new InvalidArgumentException("File '$destination' already exists.");
			copy($source, $destination);
			return;
		}
		if (FileSystem::isDirectory($destination) && !$overwrite) throw new InvalidArgumentException("Directory '$destination' already exists.");
		if (!FileSystem::isDirectory($destination)) FileSystem::createDirectory($destination);
		$items = array_diff(scandir($source), [".", ".."]);
		foreach ($items as $item)
		{
			$sourceItem = FileSystem::combinePath($source, $item);
			$destinationItem = FileSystem::combinePath($destination, $item);
			FileSystem::copy($sourceItem, $destinationItem, $overwrite);
		}
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	public static function rename(string $source, string $destination, bool $overwrite = false)
	{
		if (Strings::isNullOrWhiteSpace($destination))
		{
			throw new InvalidArgumentException("Given value '$destination' is not valid path.");
		}
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
		if (Strings::isNullOrWhiteSpace($filename))
		{
			throw new InvalidArgumentException("Given value '$filename' is not valid filename.");
		}
		if (FileSystem::isFile($filename) && !$overwrite)
		{
			throw new InvalidArgumentException("File '$filename' already exists.");
		}
		if (FileSystem::isFile($filename)) FileSystem::delete($filename);
		$file = fopen($filename, "w");
		fwrite($file, $text);
		fclose($file);
		clearstatcache();
	}

	/**
	 * @param string $filename
	 * @param string $text
	 * @param bool   $newLine
	 */
	public static function append(string $filename, string $text, bool $newLine = true)
	{
		if (Strings::isNullOrWhiteSpace($filename))
		{
			throw new InvalidArgumentException("Given value '$filename' is not valid filename.");
		}
		$file = fopen($filename, "a");
		if (FileSystem::isEmpty($filename))
		{
			fwrite($file, $text);
		}
		else if (!$newLine)
		{
			fwrite($file, $text);
		}
		else
		{
			fwrite($file, PHP_EOL . $text);
		}
		fclose($file);
		clearstatcache();
	}

	/**
	 * @param string $filename
	 * @param bool   $trimEndOfLine
	 * @return array
	 */
	public static function readAllLines(string $filename, bool $trimEndOfLine = true): array
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		$file = fopen($filename, "r");
		$lines = [];
		while (!feof($file))
		{
			$lines[] = $trimEndOfLine ? Strings::trimEnd(fgets($file), [PHP_EOL]) : fgets($file);
		}
		fclose($file);
		clearstatcache();
		return $lines;
	}

	/**
	 * @param string $filename
	 * @param bool   $trimEndOfLine
	 * @return iterable
	 */
	public static function readLineByLine(string $filename, bool $trimEndOfLine = true): iterable
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		$file = fopen($filename, "r");
		while (!feof($file))
		{
			yield $trimEndOfLine ? Strings::trimEnd(fgets($file), [PHP_EOL]) : fgets($file);
		}
		fclose($file);
		clearstatcache();
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public static function readAllText(string $filename): string
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		$output = Strings::EMPTY_STRING;
		foreach (FileSystem::readLineByLine($filename, false) as $line)
		{
			$output .= $line;
		}
		return $output;
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public static function isEmpty(string $filename): bool
	{
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		return FileSystem::size($filename) === 0;
	}

	/**
	 * @param string $path
	 * @return int
	 */
	public static function size(string $path): int
	{
		if (!file_exists($path))
		{
			throw new InvalidArgumentException("Path '$path' does not exist.");
		}
		if (FileSystem::isFile($path)) return filesize($path);
		$items = array_diff(scandir($path), [".", ".."]);
		$size = 0;
		foreach ($items as $item)
		{
			$item = FileSystem::combinePath($path, $item);
			$size = $size + FileSystem::size($item);
		}
		return $size;
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
		$finalPath = preg_replace('/[\/]{2,}/', '/', $finalPath);
		return $finalPath;
	}

	/**
	 * @param string $path
	 */
	public static function delete(string $path)
	{
		if (!file_exists($path))
		{
			throw new InvalidArgumentException("Path '$path' does not exist.");
		}
		if (FileSystem::isFile($path))
		{
			unlink($path);
			return;
		}
		$items = array_diff(scandir($path), [".", ".."]);
		foreach ($items as $item)
		{
			$item = FileSystem::combinePath($path, $item);
			FileSystem::delete($item);
		}
		rmdir($path);
	}

	/**
	 * @param string $directory
	 * @param int    $mode
	 */
	public static function createDirectory(string $directory, int $mode = 0777)
	{
		if (Strings::isNullOrWhiteSpace($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory.");
		}
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

	/**
	 * @param string $directory
	 * @param string $mode
	 * @param bool   $recursively
	 * @return string[]
	 */
	public static function scanDirectory(string $directory, string $mode = FileSystem::SCAN_ALL, bool $recursively = false)
	{
		if (!FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if (!in_array($mode, [FileSystem::SCAN_ALL, FileSystem::SCAN_DIRECTORIES, FileSystem::SCAN_FILES]))
		{
			throw new InvalidArgumentException('Invalid value passed to parameter $mode.');
		}
		$output = [];
		$items = array_diff(scandir($directory), [".", ".."]);
		foreach ($items as $item)
		{
			$item = FileSystem::combinePath($directory, $item);
			if (FileSystem::isFile($item) && ($mode == FileSystem::SCAN_FILES || $mode == FileSystem::SCAN_ALL))
			{
				$output[] = $item;
			}
			else if (FileSystem::isDirectory($item) && ($mode == FileSystem::SCAN_DIRECTORIES || $mode == FileSystem::SCAN_ALL))
			{
				$output[] = $item;
			}
			if ($recursively && FileSystem::isDirectory($item))
			{
				$output = array_merge($output, FileSystem::scanDirectory($item, $mode, $recursively));
			}
		}
		return $output;
	}
}
