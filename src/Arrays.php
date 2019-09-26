<?php


namespace Pechynho\Utility;


class Arrays
{
	/**
	 * @param iterable $subject
	 * @param callable $predicate
	 * @param mixed    $defaultValue
	 * @return mixed
	 */
	public static function firstOrDefault(iterable $subject, callable $predicate, $defaultValue = null)
	{
		foreach ($subject as $item)
		{
			if ($predicate($item))
			{
				return $item;
			}
		}
		return $defaultValue;
	}
}