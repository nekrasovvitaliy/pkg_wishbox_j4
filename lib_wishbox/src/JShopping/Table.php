<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

use Joomla\Component\Jshopping\Site\Lib\Multilangfield;
use Joomla\Component\Jshopping\Site\Table\ShopbaseTable;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Wishbox\MainTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property DatabaseInterface  $db
 * @property Multilangfield     $lang
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Table extends ShopbaseTable
{
	use MainTrait;

	/**
	 * Object constructor to set table and key fields.  In most cases, this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string                   $table      Name of the table to model.
	 * @param   mixed                    $key        Name of the primary key field in the table or array of field names that compose the primary key.
	 * @param   DatabaseDriver           $db         DatabaseDriver object.
	 * @param   DispatcherInterface|null $dispatcher Event dispatcher for this table
	 *
	 * @since   1.0.0
	 */
	public function __construct($table, $key, DatabaseDriver $db, DispatcherInterface $dispatcher = null)
	{
		parent::__construct($table, $key, $db, $dispatcher);
	}

	/**
	 * @param   array $filter Filter
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getList(array $filter = []): array
	{
		$query = $this->db->getQuery(true)
			->select('*')
			->select($this->db->qn($this->lang->get('name'), 'name'))
			->from($this->getTableName())
			->order('ordering');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}
}
