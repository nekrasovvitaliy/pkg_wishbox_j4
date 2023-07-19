<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class ShippingProduct
{
	/**
	 * @var integer $width Width
	 */
	public int $width;

	/**
	 * @var integer $height Height
	 */
	public int $height;

	/**
	 * @var integer $length Length
	 */
	public int $length;
}
