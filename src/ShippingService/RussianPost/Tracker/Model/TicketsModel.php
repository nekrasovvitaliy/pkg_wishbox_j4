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
use InvalidArgumentException;
use Joomla\CMS\Log\Log;
use JSHelper;
use SoapFault;
use Wishbox\ShippingService\Russianpost\Tracker\Interface\DelegateInterface;
use Wishbox\ShippingService\Russianpost\TrackingApiClient;
use function defined;

/**
 * @since 1.0.0
 */
class TicketsModel
{
	/**
	 * @var DelegateInterface $delegate Delegate
	 *
	 * @since 1.0.0
	 */
	protected DelegateInterface $delegate;

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
	 * @return boolean
	 *
	 * @throws SoapFault
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function update(): bool
	{
		// Delete not actual ticket by order status
		if (!$this->removeNotActualByOrderStatus())
		{
			throw new Exception('removeNotActualByOrderStatus return false', 500);
		}

		// Remove tickets, older than 1 hour
		if (!$this->removeExpired())
		{
			throw new Exception('removeExpired return false', 500);
		}

		if (!$this->createNewEmptyTickets())
		{
			throw new Exception('createNewEmptyTickets return false', 500);
		}

		if (!$this->updateNumbers())
		{
			throw new Exception('updateNumbers return false', 500);
		}

		return true;
	}

	/**
	 * Delete not actual tickets by order status.
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private function removeNotActualByOrderStatus(): bool
	{
		$orderIdsWithStartStatus = $this->delegate->getOrderIdsWithStartStatus();

		$query = $this->delegate->db->getQuery(true)
			->select('t.id')
			->from($this->delegate->getTicketTable()->getTableName() . ' AS t')
			->whereNotIn('t.order_id', $orderIdsWithStartStatus);
		$this->delegate->db->setQuery($query);
		$ticketIds = $this->delegate->db->loadColumn();
		$count = count($ticketIds);

		if ($count)
		{
			$query = $this->delegate->db->getQuery(true)
				->delete($this->delegate->getTicketTable()->getTableName())
				->where('id IN(' . implode(',', $ticketIds) . ');');
			$this->delegate->db->setQuery($query);
			$this->delegate->db->execute();
		}

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'Removed ' . $count . ' tickets with not actual order status';
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}

		return true;
	}

	/**
	 * Clear the ticket_number and request_time field for tickets older than 24 hours (pochta.ru saves tickets only 32 hours)
	 *
	 * @return boolean The number of cleared tickets.
	 *
	 * @since 1.0.0
	 */
	private function removeExpired(): bool
	{
		// Get the timestamp that was 15 minutes ago
		$targetTime = date('Y-m-d H:i:s', time() - (60 * 60));
		$query = $this->delegate->db->getQuery(true)
			->delete($this->delegate->db->qn($this->delegate->getTicketTable()->getTableName()))
			->where('request_time > "0000-00-00 00:00:00"')
			->where('request_time < ' . $this->delegate->db->q($targetTime));
		$this->delegate->db->setQuery($query);
		$this->delegate->db->execute();
		$count = $this->delegate->db->getAffectedRows();

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'Removed ' . $count . ' tickets older more than 1 hour';
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}

		return true;
	}

	/**
	 * Create new empty tickets
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private function createNewEmptyTickets(): bool
	{
		// Get shipping number array by order id
		$orderShippingNumbersByOrderId = $this->delegate->getOrderShippingNumbersByOrderId();

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'Orders with actual statuses: ' . count($orderShippingNumbersByOrderId);
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}

		$count = 0;

		foreach ($orderShippingNumbersByOrderId as $orderId => $shippingNumber)
		{
			$ticketTable = $this->delegate->getTicketTable();
			$ticketTable->load(['order_id' => $orderId]);

			if ($ticketTable->getId())
			{
				if ($this->delegate->addonParams->get('debug_mode', 0))
				{
					// phpcs:ignore
					$message = 'Order id:' . $orderId . ' has ticket id:' . $ticketTable->getId();
					Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
				}
			}
			else
			{
				$ticketTable->setOrderId($orderId);
				$ticketTable->store();

				if ($this->delegate->addonParams->get('debug_mode', 0))
				{
					// phpcs:ignore
					$message = 'Order id:' . $orderId . ' got ticket id:' . $ticketTable->getOrderId();
					Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
				}

				$count++;
			}
		}

		if ($this->delegate->addonParams->get('debug_mode', 0))
		{
			$message = 'New tickets: ' . $count;
			Log::add($message, Log::DEBUG, $this->delegate->errorCategory);
		}

		return true;
	}

	/**
	 * Updates ticket number
	 *
	 * @throws SoapFault
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private function updateNumbers(): bool
	{
		// Get an array of shipping numbers by order id
		$barcodesByOrderId = $this->getTrackNumbersWithEmptyTicketNumbersByOrderId();

		if (!count($barcodesByOrderId))
		{
			if ($this->delegate->addonParams->get('debug_mode', 0))
			{
				Log::add('No tickets with empty numbers', Log::DEBUG, $this->delegate->errorCategory);
			}

			return true;
		}

		$trackingApiClient = new TrackingApiClient(
			$this->delegate->addonParams->get('tracking_api_login', ''),
			$this->delegate->addonParams->get('tracking_api_password', ''),
			$this->delegate->addonParams->get('debug_mode', 0)
		);

		// Get ticket number for our orders
		$result = $trackingApiClient->getTicket($barcodesByOrderId);

		if (isset($result->error))
		{
			echo $result->error->ErrorName;
			die;
		}

		if ($result->value)
		{
			$orderIds = array_keys($barcodesByOrderId);

			$this->setTicketNumberForOrderIds($result->value, $orderIds);

			if ($this->delegate->addonParams->get('debug_mode', 0))
			{
				Log::add(
					'Tickets with orders_id:(' . implode(', ', $orderIds) . ') got number ' . $result->value,
					Log::DEBUG,
					$this->delegate->errorCategory
				);
			}
		}

		return true;
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private function getTrackNumbersWithEmptyTicketNumbersByOrderId(): array
	{
		// Get shipping number array by order id
		$orderShippingNumbersByOrderId = $this->delegate->getOrderShippingNumbersByOrderId();

		$query = $this->delegate->db->getQuery(true)
			->select('t.order_id AS order_id')
			->from($this->delegate->getTicketTable()->getTableName() . ' AS t')
			->where('t.ticket_number = ""')
			->whereIn('t.order_id', array_keys($orderShippingNumbersByOrderId));
		$this->delegate->db->setQuery($query);
		$orderIds = $this->delegate->db->loadColumn();

		$noTicketOrders = [];

		foreach ($orderIds as $orderId)
		{
			$noTicketOrders[$orderId] = $orderShippingNumbersByOrderId[$orderId];
		}

		return $noTicketOrders;
	}

	/**
	 * Return ticket numbers created more than 15 minutes ago.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getTicketNumbers(): array
	{
		// Get the timestamp that was 15 minutes ago
		$targetTime = date('Y-m-d H:i:s', time() - 900);
		$query = $this->delegate->db->getQuery(true)
			->select('DISTINCT `ticket_number` AS ticket_number')
			->from($this->delegate->getTicketTable()->getTableName())
			->where('request_time < ' . $this->delegate->db->q($targetTime))
			->where('ticket_number <> ""');
		$this->delegate->db->setQuery($query);

		return $this->delegate->db->loadObjectList();
	}

	/**
	 * Метод устанавливает номер тикета в тикетах с указанными id заказов
	 *
	 * @param   string  $ticketNumber Ticket number
	 * @param   array   $orderIds     Array of order ids
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function setTicketNumberForOrderIds(string $ticketNumber, array $orderIds): void
	{
		if (empty($ticketNumber))
		{
			throw new InvalidArgumentException('Empty ticket number', 500);
		}

		if (count($orderIds) == 0)
		{
			throw new InvalidArgumentException('!count orderIds', 500);
		}

		foreach ($orderIds as $orderId)
		{
			if (!is_int($orderId))
			{
				throw new InvalidArgumentException('$orderId must be integer', 500);
			}
		}

		$query = $this->delegate->db->getQuery(true)
			->update($this->delegate->getTicketTable()->getTableName())
			->set('ticket_number = ' . $this->delegate->db->q($ticketNumber))
			->set('request_time = ' . $this->delegate->db->q(JSHelper::getJSDate()))
			->whereIn('order_id', $orderIds);
		$this->delegate->db->setQuery($query);
		$this->delegate->db->execute();
	}
}
