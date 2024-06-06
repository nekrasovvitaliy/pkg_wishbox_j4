<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Entity\Request\Order;

use Wishbox\ShippingService\Russianpost\Registrator\Entity\Request\Source;

/**
 * @since 1.0.0
 */
class Dimension extends Source
{
	/**
	 * @var integer $height (Опционально) Линейная высота (сантиметры)
	 *
	 * @since 1.0.0
	 */
	protected int $height;

	/**
	 * @var integer $length (Опционально) Линейная длина (сантиметры)
	 *
	 * @since 1.0.0
	 */
	protected int $length;

	/**
	 * @var integer $width (Опционально) Линейная ширина (сантиметры)
	 *
	 * @since 1.0.0
	 */
	protected int $width;

	/**
	 * @param   integer  $height  Height
	 * @param   integer  $length  Length
	 * @param   integer  $width   Width
	 *
	 * @since 1.0.0
	 */
	public function __construct(int $height, int $length, int $width)
	{
		$this->height = $height;
		$this->length = $length;
		$this->width = $width;
	}
}
