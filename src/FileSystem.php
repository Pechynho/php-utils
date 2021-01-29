<?php


namespace Pechynho\Utility;


use DirectoryIterator;
use InvalidArgumentException;
use RuntimeException;

/**
 * @author Jan Pech <pechynho@gmail.com>
 */
class FileSystem
{
	/** @var string */
	const SCAN_ALL = "SCAN_ALL";

	/** @var string */
	const SCAN_FILES = "SCAN_FILES";

	/** @var string */
	const SCAN_DIRECTORIES = "SCAN_DIRECTORIES";

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	public static function copy($source, $destination, $overwrite = false)
	{
		if (!is_string($source))
		{
			throw new InvalidArgumentException('Parameter $source has to be type of string.');
		}
		if (!is_string($destination))
		{
			throw new InvalidArgumentException('Parameter $destination has to be type of string.');
		}
		if (!is_bool($overwrite))
		{
			throw new InvalidArgumentException('Parameter $overwrite has to be type of boolean.');
		}
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
			if (copy($source, $destination) === false)
			{
				throw new RuntimeException(sprintf('Could not copy file from %s to %s.', $source, $destination));
			}
			return;
		}
		if (FileSystem::isDirectory($destination) && !$overwrite) throw new InvalidArgumentException("Directory '$destination' already exists.");
		if (!FileSystem::isDirectory($destination)) FileSystem::createDirectory($destination);
		$iterator = new DirectoryIterator($source);
		foreach ($iterator as $item)
		{
			if ($item->isDot())
			{
				continue;
			}
			$sourceItem = FileSystem::combinePath($source, $item->getFilename());
			$destinationItem = FileSystem::combinePath($destination, $item->getFilename());
			FileSystem::copy($sourceItem, $destinationItem, $overwrite);
		}
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public static function isFile($filename)
	{
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
		return file_exists($filename) && !is_dir($filename);
	}

	/**
	 * @param string $directory
	 * @return bool
	 */
	public static function isDirectory($directory)
	{
		if (!is_string($directory))
		{
			throw new InvalidArgumentException('Parameter $directory has to be type of string.');
		}
		return file_exists($directory) && is_dir($directory);
	}

	/**
	 * @param string $directory
	 * @param int    $mode
	 */
	public static function createDirectory($directory, $mode = 0777)
	{
		if (Strings::isNullOrWhiteSpace($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if (!is_int($mode))
		{
			throw new InvalidArgumentException('Parameter $mode has to be type of int.');
		}
		if (!FileSystem::isDirectory($directory))
		{
			if (mkdir($directory, $mode, true) === false)
			{
				throw new RuntimeException(sprintf("Could not create directory %s.", $directory));
			}
		}
	}

	/**
	 * @param string[] ...$paths
	 * @return string
	 */
	public static function combinePath(...$paths)
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
		$finalPath = self::normalizePath($finalPath);
		if (false !== $realpath = realpath($finalPath))
		{
			$finalPath = $realpath;
		}
		return $finalPath;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public static function normalizePath($path)
	{
		if (!is_string($path))
		{
			throw new InvalidArgumentException('Parameter $path has to be type of string.');
		}
		$isStream = function ($path) {
			$schemeSeparator = strpos($path, '://');
			if (false === $schemeSeparator)
			{
				return false;
			}
			$stream = substr($path, 0, $schemeSeparator);
			return in_array($stream, stream_get_wrappers(), true);
		};
		$wrapper = '';
		if ($isStream($path))
		{
			list($wrapper, $path) = explode('://', $path, 2);
			$wrapper .= '://';
		}
		$path = str_replace('\\', '/', $path); // Standardise all paths to use '/'.
		$path = preg_replace('|(?<=.)/+|', '/', $path); // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
		if (':' === substr($path, 1, 1)) // Windows paths should uppercase the drive letter.
		{
			$path = ucfirst($path);
		}
		return $wrapper . $path;
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @param bool   $overwrite
	 */
	public static function rename($source, $destination, $overwrite = false)
	{
		if (!is_string($source))
		{
			throw new InvalidArgumentException('Parameter $source has to be type of string.');
		}
		if (!is_string($destination))
		{
			throw new InvalidArgumentException('Parameter $destination has to be type of string.');
		}
		if (!is_bool($overwrite))
		{
			throw new InvalidArgumentException('Parameter $overwrite has to be type of boolean.');
		}
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
		if (rename($source, $destination) === false)
		{
			throw new RuntimeException(sprintf("Could not rename %s to %s", $source, $destination));
		}
	}

	/**
	 * @param string $filename
	 * @param string $content
	 * @param bool   $overwrite
	 */
	public static function write($filename, $content, $overwrite = false)
	{
		ParamsChecker::isString('$content', $content, __METHOD__);
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
		if (!is_bool($overwrite))
		{
			throw new InvalidArgumentException('Parameter $overwrite has to be type of boolean.');
		}
		if (Strings::isNullOrWhiteSpace($filename))
		{
			throw new InvalidArgumentException("Given value '$filename' is not valid filename.");
		}
		if (FileSystem::isFile($filename) && !$overwrite)
		{
			throw new InvalidArgumentException("File '$filename' already exists.");
		}
		if (FileSystem::isFile($filename)) FileSystem::delete($filename);
		if (false === $file = fopen($filename, "w"))
		{
			throw new RuntimeException(sprintf("Function fopen('%s', 'w') failed.", $filename));
		}
		if (fwrite($file, $content) === false)
		{
			throw new RuntimeException(sprintf("Could not write (fwrite) content to %s.", $filename));
		}
		if (fclose($file) === false)
		{
			throw new RuntimeException(sprintf('Could not close (fclose) file %s.', $filename));
		}
		clearstatcache(true, $filename);
	}

	/**
	 * @param string $path
	 */
	public static function delete($path)
	{
		if (!is_string($path))
		{
			throw new InvalidArgumentException('Parameter $path has to be type of string.');
		}
		if (!file_exists($path))
		{
			throw new InvalidArgumentException("Path '$path' does not exist.");
		}
		if (FileSystem::isFile($path))
		{
			if (unlink($path) === false)
			{
				throw new RuntimeException(sprintf("Could not delete file %s.", $path));
			}
			return;
		}
		$iterator = new DirectoryIterator($path);
		foreach ($iterator as $item)
		{
			if ($item->isDot())
			{
				continue;
			}
			$item = FileSystem::combinePath($path, $item->getFilename());
			FileSystem::delete($item);
		}
		if (rmdir($path) === false)
		{
			throw new RuntimeException(sprintf("Could not delete file %s.", $path));
		}
	}

	/**
	 * @param string $filename
	 * @param string $content
	 * @param bool   $newLine
	 */
	public static function append($filename, $content, $newLine = true)
	{
		ParamsChecker::isString('$content', $content, __METHOD__);
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
		if (!is_bool($newLine))
		{
			throw new InvalidArgumentException('Parameter $newLine has to be type of boolean.');
		}
		if (Strings::isNullOrWhiteSpace($filename))
		{
			throw new InvalidArgumentException("Given value '$filename' is not valid filename.");
		}
		if (false === $file = fopen($filename, "a"))
		{
			throw new RuntimeException(sprintf("Could not open file fopen('%s', 'a').", $filename));
		}
		if (FileSystem::isEmpty($filename))
		{
			if (fwrite($file, $content) === false)
			{
				throw new RuntimeException(sprintf("Could not write content to file %s.", $filename));
			}
		}
		else if (!$newLine)
		{
			if (fwrite($file, $content) === false)
			{
				throw new RuntimeException(sprintf("Could not write content to file %s.", $filename));
			}
		}
		else
		{
			if (fwrite($file, PHP_EOL . $content) === false)
			{
				throw new RuntimeException(sprintf("Could not write content to file %s.", $filename));
			}
		}
		if (fclose($file) === false)
		{
			throw new RuntimeException(sprintf('Could not close (fclose) file %s.', $filename));
		}
		clearstatcache(true, $filename);
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public static function isEmpty($filename)
	{
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
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
	public static function size($path)
	{
		if (!is_string($path))
		{
			throw new InvalidArgumentException('Parameter $path has to be type of string.');
		}
		if (!file_exists($path))
		{
			throw new InvalidArgumentException("Path '$path' does not exist.");
		}
		if (FileSystem::isFile($path))
		{
			if (false === $size = filesize($path))
			{
				throw new RuntimeException(sprintf("Could not read file size of %s.", $path));
			}
			return $size;
		}
		$iterator = new DirectoryIterator($path);
		$size = 0;
		foreach ($iterator as $item)
		{
			if ($item->isDot())
			{
				continue;
			}
			$item = FileSystem::combinePath($path, $item->getFilename());
			$size = $size + FileSystem::size($item);
		}
		return $size;
	}

	/**
	 * @param string $filename
	 * @param bool   $trimEndOfLine
	 * @return array
	 */
	public static function readAllLines($filename, $trimEndOfLine = true)
	{
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
		if (!is_bool($trimEndOfLine))
		{
			throw new InvalidArgumentException('Parameter $trimEndOfLine has to be type of boolean.');
		}
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		if (false === $file = fopen($filename, "r"))
		{
			throw new RuntimeException(sprintf("Could not open file fopen('%s', 'r').", $filename));
		}
		$lines = [];
		while (!feof($file))
		{
			if (false === $line = fgets($file))
			{
				throw new RuntimeException(sprintf("Could not read line (fgets) of %s", $filename));
			}
			$lines[] = $trimEndOfLine ? Strings::trimEnd($line, [PHP_EOL]) : $line;
		}
		if (fclose($file) === false)
		{
			throw new RuntimeException(sprintf('Could not close (fclose) file %s.', $filename));
		}
		clearstatcache(true, $filename);
		return $lines;
	}

	/**
	 * @param $filename
	 * @return string
	 */
	public static function readAllText($filename)
	{
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
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
	 * @param bool   $trimEndOfLine
	 * @return iterable
	 */
	public static function readLineByLine($filename, $trimEndOfLine = true)
	{
		if (!is_string($filename))
		{
			throw new InvalidArgumentException('Parameter $filename has to be type of string.');
		}
		if (!is_bool($trimEndOfLine))
		{
			throw new InvalidArgumentException('Parameter $trimEndOfLine has to be type of boolean.');
		}
		if (!FileSystem::isFile($filename))
		{
			throw new InvalidArgumentException("File '$filename' does not exist.");
		}
		if (false === $file = fopen($filename, "r"))
		{
			throw new RuntimeException(sprintf("Could not open file fopen('%s', 'r').", $filename));
		}
		while (!feof($file))
		{
			if (false === $line = fgets($file))
			{
				throw new RuntimeException(sprintf("Could not read line (fgets) of %s", $filename));
			}
			yield $trimEndOfLine ? Strings::trimEnd($line, [PHP_EOL]) : $line;
		}
		if (fclose($file) === false)
		{
			throw new RuntimeException(sprintf('Could not close (fclose) file %s.', $filename));
		}
		clearstatcache(true, $filename);
	}

	/**
	 * @param string $directory
	 * @param string $mode
	 * @param bool   $recursively
	 * @return string[]
	 */
	public static function scanDirectory($directory, $mode = FileSystem::SCAN_ALL, $recursively = false)
	{
		if (!FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if (!in_array($mode, [FileSystem::SCAN_ALL, FileSystem::SCAN_DIRECTORIES, FileSystem::SCAN_FILES]))
		{
			throw new InvalidArgumentException('Invalid value passed to parameter $mode.');
		}
		if (!is_bool($recursively))
		{
			throw new InvalidArgumentException('Parameter $recursively has to be type of boolean.');
		}
		$output = [];
		$iterator = new DirectoryIterator($directory);
		foreach ($iterator as $item)
		{
			if ($item->isDot())
			{
				continue;
			}
			$item = FileSystem::combinePath($directory, $item->getFilename());
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
