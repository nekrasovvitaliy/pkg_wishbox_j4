<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Entity\Request;

/**
 * @since 1.0.0
 */
interface RequestInterface
{
	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function prepareRequest(): array;
}
