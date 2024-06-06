<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Cdek;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class Dimensions
{
	/**
	 * @var integer $x X
	 *
	 * @since 1.0.0
	 */
	private int $x;

	/**
	 * @var integer $y Y
	 *
	 * @since 1.0.0
	 */
	private int $y;

	/**
	 * @var integer $z Z
	 *
	 * @since 1.0.0
	 */
	private int $z;

	/**
	 * @param   integer  $x  X
	 * @param   integer  $y  Y
	 * @param   integer  $z  Z
	 *
	 * @since 1.0.0
	 */
	public function __construct(int $x, int $y, int $z)
	{
		$dimensions = [$x, $y, $z];
		sort($dimensions);
		$dimensions = array_reverse($dimensions);
		$this->x = $dimensions[0];
		$this->y = $dimensions[1];
		$this->z = $dimensions[2];
	}

	/**
	 * @param   array  $arrays  Arrays
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function arrayFromArray(array $arrays): array
	{
		$objectArray = [];

		foreach ($arrays as $array)
		{
			$objectArray[] = new Dimensions($array[0], $array[1], $array[2]);
		}

		return $objectArray;
	}

	/**
	 * @param   array  $arrays  Arrays
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function arrayFromAccos(array $arrays): array
	{
		$objectArray = [];

		foreach ($arrays as $array)
		{
			$arrayValues = array_values($array);
			$objectArray[] = new Dimensions($arrayValues[0], $arrayValues[1], $arrayValues[2]);
		}

		return $objectArray;
	}

	/**
	 * @param   array  $arraysA  Array A
	 * @param   array  $arraysB  Array B
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public static function arrayInArray(array $arraysA, array $arraysB): bool
	{
		foreach ($arraysA as $a)
		{
			if (!$a->inDimensions($arraysB))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param   array  $dimensions Dimensions
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function inDimensions(array $dimensions): bool
	{
		foreach ($dimensions as $dimension)
		{
			if ($this->x > $dimension->x
				|| $this->y > $dimension->y
				|| $this->z > $dimension->z
			)
			{
				return false;
			}
		}

		return true;
	}
}
