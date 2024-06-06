<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker\Model;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Log\Log;
use stdClass;
use Wishbox\ShippingService\Russianpost\ApiClient;
use Wishbox\ShippingService\Russianpost\Tracker\Interface\DelegateInterface;
use function defined;

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class OrdershippingnumbersModel
{
	/**
	 * @param   DelegateInterface $delegate Delegate
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setDelegate(DelegateInterface $delegate): void
	{
		$this->delegate = $delegate;
	}

	/**
	 * @var DelegateInterface $delegate Delegate
	 *
	 * @since 1.0.0
	 */
	private DelegateInterface $delegate;

	/**
	 * Updates operations
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function update(): bool
	{
		$orders = $this->delegate->getOrders();

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'Orders without shipping number: ' . count($orders);
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}

		if (count($orders))
		{
			foreach ($orders as $order)
			{
				$this->updateOrder($order);
			}
		}

		return true;
	}

	/**
	 * @param   stdClass $order Order
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function updateOrder(stdClass $order): void
	{
		if ($barcode = $this->getBarcodeByOrderNumber($order->order_number)) // phpcs:ignore
		{
			$this->delegate->setShippingNumberToOrder($barcode, $order->order_id); // phpcs:ignore

			if ($this->delegate->addonParams->get('debug_mode', 0))
			{
				$message = 'Order id:' . $order->order_id . ' received barcode ' . $barcode . ' from Russian Post API'; // phpcs:ignore
				Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
			}
		}
		else
		{
			if ($this->delegate->addonParams->get('debug_mode', 0))
			{
				$message = 'Order id:' . $order->order_id . ' not found in Russian Post API'; // phpcs:ignore
				Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
			}
		}
	}

	/**
	 * @param   string $orderNumber Order number
	 *
	 * @return ?string
	 *
	 * @since 1.0.0
	 */
	private function getBarcodeByOrderNumber(string $orderNumber): ?string
	{
		$apiClient = new Apiclient(
			$this->delegate->addonParams->get('authorisation_token', ''),
			$this->delegate->addonParams->get('authorisation_key', ''),
			$this->delegate->addonParams->get('debug_mode', '')
		);
		$russianpostOrders = $apiClient->backlogSearch($orderNumber); // phpcs:ignore

		if (!count($russianpostOrders))
		{
			$russianpostOrders = $apiClient->shipmentSearch($orderNumber); // phpcs:ignore
		}

		if (count($russianpostOrders))
		{
			foreach ($russianpostOrders as $russianpostOrder)
			{
				if ($russianpostOrder['order-num'] == $orderNumber)
				{
					return $russianpostOrder['barcode'];
				}
			}
		}

		return null;
	}
}
