<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker\Interface;

use Joomla\CMS\Table\TableInterface;

/**
 * @since 1.0.0
 */
interface TicketTableInterface extends TableInterface
{
	/**
	 * @return string
	 * @since 1.0.0
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getTableName();

	/**
	 * @return integer
	 * @since 1.0.0
	 */
	public function getOrderId(): int;

	/**
	 * @param   integer $orderId Order id
	 * @return void
	 * @since 1.0.0
	 */
	public function setOrderId(int $orderId): void;
}
