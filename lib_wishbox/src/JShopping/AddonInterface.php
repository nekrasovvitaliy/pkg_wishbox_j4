<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

/**
 * @since 1.0.0
 */
interface AddonInterface
{
	/**
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public static function getInstance(): mixed;
}
