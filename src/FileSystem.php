<?php


namespace Pechynho\Utility;


use DirectoryIterator;
use FilesystemIterator;
use Generator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

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
	 * @param bool $overwrite
	 */
	public static function copy(string $source, string $destination, bool $overwrite = false): void
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
	public static function isFile(string $filename): bool
	{
		return file_exists($filename) && !is_dir($filename);
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
	 * @param string $directory
	 * @param int $mode
	 */
	public static function createDirectory(string $directory, int $mode = 0777): void
	{
		if (Strings::isNullOrWhiteSpace($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory.");
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
	public static function normalizePath(string $path): string
	{
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
			[$wrapper, $path] = explode('://', $path, 2);
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
	 * @param bool $overwrite
	 */
	public static function rename(string $source, string $destination, bool $overwrite = false): void
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
		if (rename($source, $destination) === false)
		{
			throw new RuntimeException(sprintf("Could not rename %s to %s", $source, $destination));
		}
	}

	/**
	 * @param string $filename
	 * @param string $content
	 * @param bool $overwrite
	 */
	public static function write(string $filename, string $content, bool $overwrite = false): void
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
	public static function delete(string $path): void
	{
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
	 * @param bool $newLine
	 */
	public static function append(string $filename, string $content, bool $newLine = true): void
	{
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
	 * @param bool $trimEndOfLine
	 * @return array
	 */
	public static function readAllLines(string $filename, bool $trimEndOfLine = true): array
	{
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
	 * @param bool $trimEndOfLine
	 * @return iterable
	 */
	public static function readLineByLine(string $filename, bool $trimEndOfLine = true): iterable
	{
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
	 * @param bool $recursively
	 * @return string[]
	 */
	public static function scanDirectory(string $directory, string $mode = FileSystem::SCAN_ALL, bool $recursively = false): array
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

	/**
	 * @param string $path
	 * @return bool
	 */
	public static function exists(string $path): bool
	{
		return file_exists($path);
	}

	/**
	 * @param string $directory
	 * @param string $mode
	 * @param bool $recursively
	 * @return Generator
	 */
	public static function iterateDirectory(string $directory, string $mode = FileSystem::SCAN_ALL, bool $recursively = false): Generator
	{
		if (!FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if (!in_array($mode, [FileSystem::SCAN_ALL, FileSystem::SCAN_DIRECTORIES, FileSystem::SCAN_FILES]))
		{
			throw new InvalidArgumentException('Invalid value passed to parameter $mode.');
		}
		if (!$recursively)
		{
			$iterator = new DirectoryIterator($directory);
			foreach ($iterator as $item)
			{
				if ($item->isDot())
				{
					continue;
				}
				if (($item->isDir() && ($mode == FileSystem::SCAN_DIRECTORIES || $mode == FileSystem::SCAN_ALL)) || ($item->isFile() && ($mode == FileSystem::SCAN_FILES || $mode == FileSystem::SCAN_ALL)))
				{
					yield $item->getRealPath();
				}
			}
			return;
		}
		$iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS|FilesystemIterator::FOLLOW_SYMLINKS);
		$iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator as $item)
		{
			if (($item->isDir() && ($mode == FileSystem::SCAN_DIRECTORIES || $mode == FileSystem::SCAN_ALL)) || ($item->isFile() && ($mode == FileSystem::SCAN_FILES || $mode == FileSystem::SCAN_ALL)))
			{
				yield $item->getRealPath();
			}
		}
	}

	/**
	 * @param string|null $directory
	 * @param string|null $extension
	 * @return string
	 */
	public static function generateFilename(?string $directory = null, ?string $extension = null): string
	{
		if ($directory !== null && !FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if ($directory === null)
		{
			$directory = sys_get_temp_dir();
		}
		$suffix = "";
		if ($extension !== null && Strings::startsWith($extension,"."))
		{
			$suffix = $extension;
		}
		else if ($extension !== null && !Strings::startsWith($extension, "."))
		{
			$suffix = "." . $extension;
		}
		do
		{
			$filename = md5(uniqid()) . $suffix;
		} while (self::exists(self::combinePath($directory, $filename)));
		return self::combinePath($directory, $filename);
	}

	/**
	 * @param string|null $directory
	 * @param string|null $extension
	 * @param string $mode
	 * @return string
	 */
	public static function createTempFile(?string $directory = null, ?string $extension = null, string $mode = "wb"): string
	{
		if ($directory !== null && !FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if ($directory === null)
		{
			$directory = sys_get_temp_dir();
		}
		$filename = self::generateFilename($directory, $extension);
		$resource = fopen($filename, $mode);
		if ($resource === false)
		{
			throw new RuntimeException(sprintf("Function fopen('%s', '%s) has failed.", $filename, $mode));
		}
		if (fclose($resource) === false)
		{
			throw new RuntimeException(sprintf('Could not close (fclose) file %s.', $filename));
		}
		return $filename;
	}

	/**
	 * @param string|null $directory
	 * @param int $mode
	 * @return string
	 */
	public static function createTempDirectory(?string $directory = null, int $mode = 0777): string
	{
		if ($directory !== null && !FileSystem::isDirectory($directory))
		{
			throw new InvalidArgumentException("Given value '$directory' is not valid directory name.");
		}
		if ($directory === null)
		{
			$directory = sys_get_temp_dir();
		}
		$output = self::generateFilename($directory);
		self::createDirectory($output, $mode);
		return $output;
	}

	/**
	 * @param string $source
	 * @param string|null $destination
	 * @param bool $overwrite
	 * @param callable|null $filter
	 * @return string
	 */
	public static function zip(string $source, ?string $destination = null, bool $overwrite = false, ?callable $filter = null): string
	{
		if (!$overwrite && $destination !== null && self::exists($destination))
		{
			throw new RuntimeException(sprintf("Filename %s already exists.", $destination));
		}
		if (!self::exists($source))
		{
			throw new InvalidArgumentException(sprintf("Source %s does not exist.", $source));
		}
		if ($destination === null)
		{
			$destination = self::createTempFile(null, ".zip");
		}
		$source = realpath($source);
		$zip = new ZipArchive();
		if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true)
		{
			throw new RuntimeException(sprintf("Could not create %s zip archive.", $destination));
		}
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
		$sourceWithSeparator = $source . DIRECTORY_SEPARATOR;
		foreach ($files as $file)
		{
			if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), ['.', '..']))
			{
				continue;
			}
			if (is_callable($filter))
			{
				$skip = call_user_func($filter, (string)$file);
				if (!is_bool($skip))
				{
					throw new RuntimeException(sprintf('Parameter $filter has to contain callback which returns boolean.'));
				}
				if ($skip)
				{
					continue;
				}
			}
			if (is_dir($file) === true)
			{
				$zip->addEmptyDir(str_replace($sourceWithSeparator, '', $file . DIRECTORY_SEPARATOR));
			}
			else if (is_file($file) === true)
			{
				$zip->addFile($file, str_replace($sourceWithSeparator, '', $file));
			}
		}
		if ($zip->close() !== true)
		{
			throw new RuntimeException(sprintf("Could not close %s zip archive.", $destination));
		}
		return $destination;
	}

	/**
	 * @param string $source
	 * @param string|null $destination
	 * @return string
	 */
	public static function unzip(string $source, ?string $destination = null): string
	{
		if ($destination !== null && self::exists($destination))
		{
			throw new RuntimeException(sprintf("Filename %s already exists.", $destination));
		}
		if (!self::exists($source))
		{
			throw new InvalidArgumentException(sprintf("Source %s does not exist.", $source));
		}
		if ($destination === null)
		{
			$destination = self::createTempDirectory();
		}
		$zip = new ZipArchive();
		if ($zip->open($source) !== true)
		{
			throw new RuntimeException(sprintf("Could not open %s zip archive.", $source));
		}
		$zip->extractTo($destination);
		if ($zip->close() !== true)
		{
			throw new RuntimeException(sprintf("Could not close %s zip archive.", $source));
		}
		return $destination;
	}
}
