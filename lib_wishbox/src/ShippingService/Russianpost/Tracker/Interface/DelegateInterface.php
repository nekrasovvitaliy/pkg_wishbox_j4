<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker\Interface;

use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use stdClass;

/**
 * @property DatabaseDriver $db
 * @property Registry       $addonParams
 * @property string         $errorCategory
 * @since 1.0.0
 */
interface DelegateInterface
{
	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getOrderIdsWithStartStatus(): array;

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getBarcodesWithStartStatus(): array;

	/**
	 * @return TicketTableInterface
	 *
	 * @since 1.0.0
	 */
	public function getTicketTable(): TicketTableInterface;

	/**
	 * @return OperationTableInterface
	 *
	 * @since 1.0.0
	 */
	public function getOperationTable(): OperationTableInterface;

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getOrderShippingNumbersByOrderId(): array;

	/**
	 * @param   string         $shippingNumber  Wishbox shipping number
	 * @param   integer|string $orderId         Order id
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setShippingNumberToOrder(string $shippingNumber, int|string $orderId): void;

	/**
	 * @param   string $shippingNumber Shipping number
	 *
	 * @return integer|string|null
	 *
	 * @since 1.0.0
	 */
	public function getOrderIdByShippingNumber(string $shippingNumber): int|string|null;

	/**
	 * @param   integer|string $orderId      OrderTable object
	 * @param   stdClass       $orderStatus  Order status
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function changeOrderStatus(int|string $orderId, stdClass $orderStatus): void;

	/**
	 * @param   string $shippingNumber Shipping number
	 *
	 * @return integer|string|null
	 *
	 * @since 1.0.0
	 */
	public function getOrderStatusByShippingNumber(string $shippingNumber): int|string|null;

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getOrderStatuses(): array;
}
