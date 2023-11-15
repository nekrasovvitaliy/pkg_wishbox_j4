<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker\Model;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use stdClass;
use Wishbox\ShippingService\Russianpost\Tracker\Interface\DelegateInterface;
use function defined;

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class OrderstatusesModel
{
	/**
	 * @var string $addonAlias Addon alias
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	protected string $addonAlias = 'wishboxrussianpost';

	/**
	 * @var DelegateInterface $delegate Delegate
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	protected DelegateInterface $delegate;

	/**
	 * @param   DelegateInterface $delegate Delegate
	 * @since 1.0.0
	 */
	public function __construct(DelegateInterface $delegate)
	{
		$this->delegate = $delegate;
	}

	/**
	 * @return void
	 * @since 1.0.0
	 */
	public function update(): void
	{
		$operationsModel = new OperationsModel($this->delegate);

		// Get the latest operation per day
		$operations = $operationsModel->getOperations();
		$orderStatuses = $this->getOrderStatuses();

		foreach ($operations as $operation)
		{
			foreach ($orderStatuses as $orderStatus)
			{
				// phpcs:ignore
				if ($operation->oper_type_id == $orderStatus->oper_type_id
					&& ($operation->oper_ctg_id == $orderStatus->oper_ctg_id || $orderStatus->oper_ctg_id == '*')) // phpcs:ignore
				{
					$currentOrderStatus = $this->delegate->getOrderStatusByShippingNumber($operation->barcode);

					if (!$currentOrderStatus)
					{
						continue;
					}

					// phpcs:ignore
					if ($currentOrderStatus != $orderStatus->new_status_code)
					{
						$this->delegate->changeOrderStatus($operation->barcode, $orderStatus); // phpcs:ignore
					}
				}
			}
		}
	}

	/**
	 * @return stdClass[]
	 * @since 1.0.0
	 */
	public function getOrderStatuses(): array
	{
		return (array) $this->delegate->addonParams->get('order_statuses');
	}
}
