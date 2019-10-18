<?php


namespace Pechynho\Utility;


use InvalidArgumentException;

class FileSystem
{
	/** @var string[] */
	const SIZE_SI_UNITS = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];

	/** @var string[] */
	const SIZE_BINARY_UNITS = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

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
		rename($source, $destination);
	}

	/**
	 * @param string $filename
	 * @param mixed  $content
	 * @param bool   $overwrite
	 */
	public static function write($filename, $content, $overwrite = false)
	{
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
		$file = fopen($filename, "w");
		fwrite($file, $content);
		fclose($file);
	}

	/**
	 * @param string $filename
	 * @param mixed  $content
	 * @param bool   $newLine
	 */
	public static function append($filename, $content, $newLine = true)
	{
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
		$file = fopen($filename, "a");
		if (FileSystem::isEmpty($filename))
		{
			fwrite($file, $content);
		}
		else if (!$newLine)
		{
			fwrite($file, $content);
		}
		else fwrite($file, PHP_EOL . $content);
		fclose($file);
	}

	/**
	 * @param string $filename
	 * @param bool   $trimEndOfLine
	 * @return array
	 */
	public static function readAllLines($filename, $trimEndOfLine = true): array
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
		$file = fopen($filename, "r");
		$lines = [];
		while (!feof($file))
		{
			$lines[] = $trimEndOfLine ? Strings::trimEnd(fgets($file), [PHP_EOL]) : fgets($file);
		}
		fclose($file);
		return $lines;
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
		$file = fopen($filename, "r");
		while (!feof($file))
		{
			yield $trimEndOfLine ? Strings::trimEnd(fgets($file), [PHP_EOL]) : fgets($file);
		}
		fclose($file);
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
	 * @param int         $bytes
	 * @param string|null $unit
	 * @param string|null $format
	 * @param bool        $useSI
	 * @return string
	 */
	public static function formatSize($bytes, $unit = null, $format = null, $useSI = true)
	{
		if (!is_int($bytes))
		{
			throw new InvalidArgumentException('Parameter $bytes has to be type of int.');
		}
		if ($format != null && !is_string($format))
		{
			throw new InvalidArgumentException('Parameter $format has to be type of string or NULL.');
		}
		if (!is_bool($useSI))
		{
			throw new InvalidArgumentException('Parameter $useSI ha to be type of boolean.');
		}
		if ($bytes < 0)
		{
			throw new InvalidArgumentException('Parameter $bytes has to be greater or equal to 0.');
		}
		if ($unit !== null && !in_array($unit, self::SIZE_SI_UNITS, true) && !in_array($unit, self::SIZE_BINARY_UNITS, true))
		{
			throw new InvalidArgumentException('Invalid value of parameter $unit.');
		}
		$format = $format === null ? '%01.2f %s' : (string)$format;
		if ($useSI == false || (!Strings::isNullOrWhiteSpace($unit) && Strings::contains($unit, "i")))
		{
			$units = FileSystem::SIZE_BINARY_UNITS;
			$mod = 1024;
		}
		else
		{
			$units = FileSystem::SIZE_SI_UNITS;
			$mod = 1000;
		}
		$power = Arrays::keyOf($units, $unit);
		if ($power === null) $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
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
		$finalPath = preg_replace('/[\/]{2,}/', '/', $finalPath);
		return $finalPath;
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
			mkdir($directory, $mode, true);
		}
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
}