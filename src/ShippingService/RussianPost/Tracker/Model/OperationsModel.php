<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker\Model;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Log\Log;
use SoapFault;
use Wishbox\ShippingService\Russianpost\Exception\StatusValidationException;
use Wishbox\ShippingService\Russianpost\StatusList;
use Wishbox\ShippingService\Russianpost\Tracker\Interface\DelegateInterface;
use Wishbox\ShippingService\Russianpost\Tracker\Exception\TicketNotReadyException;
use Wishbox\ShippingService\Russianpost\TrackingApiClient;
use function defined;

/**
 * @since 1.0.0
 */
class OperationsModel
{
	/**
	 * @var DelegateInterface $delegate Delegate
	 *
	 * @since 1.0.0
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
	 * Updates operations
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function update(): void
	{
		// Delete not actual operations by order status
		$this->removeNotActualByOrderStatus();

		$ticketsModel = new TicketsModel($this->delegate);

		// Get an array of ticket numbers created more than 15 minutes ago
		$ticketNumbers = $ticketsModel->getTicketNumbers();

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'Found ' . count($ticketNumbers) . ' tickets created more than 15 minutes ago.';
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}

		if (!count($ticketNumbers))
		{
			return;
		}

		// For each ticket number
		foreach ($ticketNumbers as $ticketNumber)
		{
			// Get an array of operations
			$operationsByTicket = $this->getOperationsByTicket($ticketNumber->ticket_number); // phpcs:ignore

			foreach ($operationsByTicket as $barcode => $operations)
			{
				$latestOperation = $operations[count($operations) - 1];
				$operationTable = $this->delegate->getOperationTable();
				$operationTable->load(['barcode' => $barcode]);
				$operationTable->barcode = $barcode;
				$operationTable->oper_type_id = $latestOperation->OperTypeID; // phpcs:ignore
				$operationTable->oper_ctg_id = $latestOperation->OperCtgID; // phpcs:ignore
				$operationTable->date_oper = date('Y-m-d H:i:s', strtotime($latestOperation->DateOper)); // phpcs:ignore
				$operationTable->store();

				if ($this->delegate->addonParams->get('debug_mode', 0))
				{
					$orderId = $this->delegate->getOrderIdByShippingNumber($operationTable->barcode);

					$message = 'Got operation for order id:' .
						$orderId . // phpcs:ignore
						' barcode:' .
						$operationTable->barcode .
						' oper_type_id:' .
						$operationTable->oper_type_id; // phpcs:ignore
					Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
				}
			}
		}
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getOperations(): array
	{
		// Get the datetime that was 1 day ago
		$targetTime = date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 10);

		$query = $this->delegate->db->getQuery(true)
			->select(
				[
					'barcode',
					'oper_type_id',
					'oper_ctg_id'
				]
			)
			->from($this->delegate->getOperationTable()->getTableName())
			->where('date_oper > ' . $this->delegate->db->q($targetTime));
		$this->delegate->db->setQuery($query);

		return $this->delegate->db->loadObjectList();
	}

	/**
	 * Delete not actual operations by order status.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function removeNotActualByOrderStatus(): void
	{
		$barcodesWithStartStatus = $this->delegate->getBarcodesWithStartStatus();

		$query = $this->delegate->db->getQuery(true)
			->select('o.id')
			->from($this->delegate->getOperationTable()->getTableName() . ' AS o')
			->whereNotIn('o.barcode', $barcodesWithStartStatus);
		$this->delegate->db->setQuery($query);
		$operationIds = $this->delegate->db->loadColumn();
		$count = count($operationIds);

		if ($count)
		{
			$query = $this->delegate->db->getQuery(true)
				->delete($this->delegate->getOperationTable()->getTableName())
				->where('id IN(' . implode(',', $operationIds) . ');');
			$this->delegate->db->setQuery($query);
			$this->delegate->db->execute();
		}

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'Removed ' . $count . ' tickets with not actual order status';
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}
	}

	/**
	 * @param   string  $ticketNumber  Ticket number
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getOperationsByTicket(string $ticketNumber): array
	{
		$trackingApiClient = $this->getTrackingApiClient();
		$statusList = new StatusList;
		$response = $trackingApiClient->getResponseByTicket($ticketNumber);

		if (isset($response->error))
		{
			throw new Exception($response->error->ErrorName, 500);
		}

		if (empty($response->value))
		{
			throw new TicketNotReadyException('Ответ по тикету ' . $ticketNumber . ' еще не готов.');
		}

		$result = !is_array($response->value->Item) ? [$response->value->Item] : $response->value->Item;

		foreach ($result as $key => &$item)
		{
			if (empty($item->Operation)) // phpcs:ignore
			{
				continue;
			}

			$barcode = (string) $item->Barcode;// phpcs:ignore

			if (!is_array($item->Operation)) // phpcs:ignore
			{
				$item = [$item->Operation]; // phpcs:ignore
			}
			else
			{
				$item = $item->Operation; // phpcs:ignore
			}

			foreach ($item as &$operation)
			{
				try
				{
					$statusInfo = $statusList->getInfo($operation->OperTypeID, $operation->OperCtgID); // phpcs:ignore
					$operation->OperCtgName = $statusInfo['substatusName']; // phpcs:ignore
					$operation->isFinal = $statusInfo['isFinal'];
				}
				catch (StatusValidationException $e)
				{
					$operation->OperCtgName = $e->getMessage(); // phpcs:ignore
					$operation->isFinal = false;
				}
			}

			$result[$barcode] = $item; // phpcs:ignore
			unset($result[$key]);
		}

		return $result;
	}

	/**
	 * @return TrackingApiClient
	 *
	 * @throws SoapFault
	 *
	 * @since 1.0.0
	 */
	private function getTrackingApiClient(): TrackingApiClient
	{
		return new TrackingApiClient(
			$this->delegate->addonParams->get('tracking_api_login', ''),
			$this->delegate->addonParams->get('tracking_api_password', ''),
			$this->delegate->addonParams->get('debug_mode', '')
		);
	}
}
