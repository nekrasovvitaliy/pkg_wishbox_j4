<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker;

use Exception;
use Wishbox\ShippingService\Russianpost\Tracker\Interface\DelegateInterface;
use Wishbox\ShippingService\Russianpost\Tracker\Model\OperationsModel;
use Wishbox\ShippingService\Russianpost\Tracker\Model\OrderstatusesModel;
use Wishbox\ShippingService\Russianpost\Tracker\Model\OrdershippingnumbersModel;
use Wishbox\ShippingService\Russianpost\Tracker\Model\TicketsModel;

/**
 * @since 1.0.0
 */
class Client
{
	/**
	 * @param   DelegateInterface $delegate Delegate
	 *
	 * @since 1.0.0
	 */
	public function __construct(DelegateInterface $delegate)
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
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function updateShippingNumbers(): void
	{
		$ordershippingnumbersModel = new OrdershippingnumbersModel;

		$ordershippingnumbersModel->setDelegate($this->delegate);

		// Set empty shipping numbers
		$ordershippingnumbersModel->update();
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function updateOrderStatuses(): void
	{
		$ticketsModel = new TicketsModel($this->delegate);

		// Update tickets
		$ticketsModel->update();

		// Get an Operations model
		$operationsModel = new OperationsModel($this->delegate);

		// Update operations
		$operationsModel->update();

		// Get an Orderstatuses model
		$orderstatusesModel = new OrderstatusesModel($this->delegate);

		// Update order statuses
		$orderstatusesModel->update();
	}
}
