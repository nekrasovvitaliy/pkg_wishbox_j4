<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property DatabaseInterface $db
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class Orders extends Base
{

	/**
	 * @param   array $filters Filters
	 * @return integer
	 * @noinspection PhpUnused
	 * @since 1.0.0
	 */
	public function getCountAllOrders(array $filters): int
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(o.order_id)')
			->from('#__jshopping_orders AS o');
		$this->setWheres($query, $filters);
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * @param   integer $limitstart       Limit start
	 * @param   int     $limit            Limit
	 * @param   array   $filters          Filters
	 * @param   string  $filterOrder      Filter order
	 * @param   string  $filterOrderDir   Filter order direction
	 * @return array
	 * @noinspection PhpUnused
	 * @since 1.0.0
	 */
	public function getAllOrders(
		int $limitstart,
		int $limit,
		array $filters,
		string $filterOrder = 'order_id',
		string $filterOrderDir = 'ASC'
	): array {
		$query = $this->db->getQuery(true)
			->select('o.*')
			->from('#__jshopping_orders AS o');
		$this->setWheres($query, $filters);
		$query->order($filterOrder . ' ' . $filterOrderDir);
		$this->db->setQuery($query, $limitstart, $limit);

		return $this->db->loadObjectList();
	}

	/**
	 * @param   QueryInterface $query   Query
	 * @param   array          $filters Filters
	 * @return void
	 * @since 1.0.0
	 */
	public function setWheres(QueryInterface $query, array $filters): void
	{
		if (!empty($filters['order_number']))
		{
			$query->where('o.order_number LIKE ' . $this->db->quote('%' . $filters['order_number'] . '%'));
		}

		if (isset($filters['buyer']) && !empty($filters['buyer']))
		{
			$query->where('(
							o.f_name LIKE ' . $this->db->quote('%' . $filters['buyer'] . '%') . '
							OR o.l_name LIKE ' . $this->db->quote('%' . $filters['buyer'] . '%') . '
							OR o.m_name LIKE ' . $this->db->quote('%' . $filters['buyer'] . '%') . '
							OR o.phone LIKE ' . $this->db->quote('%' . $filters['buyer'] . '%') . '
							OR o.mobil_phone LIKE ' . $this->db->quote('%' . $filters['buyer'] . '%') . ')'
			);
		}

		if (isset($filters['order_date_from']) && !empty($filters['order_date_from']))
		{
			$orderDateFrom = $filters['order_date_from'];
			$orderDateFrom = strtotime($orderDateFrom);
			$orderDateFrom = date('Y-m-d', $orderDateFrom) . ' 00:00:00';
			$query->where('o.order_date > ' . $this->db->quote($orderDateFrom));
		}

		if (isset($filters['order_date_to']) && !empty($filters['order_date_to']))
		{
			$orderDateTo = $filters['order_date_to'];
			$orderDateTo = strtotime($orderDateTo);
			$orderDateTo = date('Y-m-d', $orderDateTo) . ' 23:59:59';
			$query->where('o.order_date < ' . $this->db->quote($orderDateTo));
		}

		if (isset($filters['product']) && !empty($filters['product']))
		{
			$query->join('LEFT', '#__jshopping_order_item AS oi ON oi.order_id = o.order_id');
			$query->where('(oi.product_name LIKE ' . $this->db->quote('%' . $filters['product'] . '%') . '
							OR oi.product_EAN LIKE ' . $this->db->quote('%' . $filters['product'] . '%') . ')'
			);
		}

		if (isset($filters['status_ids']) && !empty($filters['status_ids']))
		{
			$query->where('o.order_status IN (' . implode(',', $filters['status_ids']) . ')');
		}

		if (isset($filters['shipping_method_ids']) && !empty($filters['shipping_method_ids']))
		{
			$query->where('o.shipping_method_id IN (' . implode(',', $filters['shipping_method_ids']) . ')');
		}

		if (isset($filters['wishbox_shipping_number_is_empty']) && $filters['wishbox_shipping_number_is_empty'])
		{
			$query->where('(o.wishbox_shipping_number = "" OR o.wishbox_shipping_number IS NULL)');
		}

		if (isset($filters['wishbox_shipping_number_is_not_empty'])
			&& $filters['wishbox_shipping_number_is_not_empty'])
		{
			$query->where('(o.wishbox_shipping_number <> "" AND o.wishbox_shipping_number IS NOT NULL)');
		}

		if (isset($filters['user_id']) && $filters['user_id'])
		{
			$query->where('o.user_id = ' . $filters['user_id']);
		}
	}
}
