<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Table\TableInterface;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use JSHelper;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property DatabaseDriver  $db
 * @property CMSApplication  $app
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ListadminModel extends Baseadmin
{
	/**
	 * @var string $itemName Item name
	 *
	 * @since 1.0.0
	 */
	protected string $itemName;

	/**
	 * @var string $listName List name
	 *
	 * @since 1.0.0
	 */
	protected string $listName;

	/**
	 * @param   string|null  $order     Order
	 * @param   string|null  $orderDir  Order direction
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getList(?string $order = null, ?string $orderDir = null): array
	{
		$query = $this->getListQuery($order, $orderDir);

		extract(JSHelper::js_add_trigger(get_defined_vars(), 'before'));

		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * @param   string|null  $order     Order
	 * @param   string|null  $orderDir  Order direction
	 *
	 * @return DatabaseQuery
	 *
	 * @since 1.0.0
	 */
	public function getListQuery(?string $order = null, ?string $orderDir = null): DatabaseQuery
	{
		$ordering = 'id';

		if ($order && $orderDir)
		{
			$ordering = $order . ' ' . $orderDir;
		}

		$lang = JSFactory::getLang();
		$query = $this->db->getQuery(true)
			->select($this->db->qn('item.id'))
			->select($this->db->qn('item.' . $lang->get('name'), 'name'))
			->select($this->db->qn('item.publish'))
			->from('#__jshopping_' . str_replace($this->addonAlias, $this->addonAlias . '_', $this->name) . ' AS item')
			->order($ordering);

		return $query;
	}

	/**
	 * @param   array  $post  Post
	 *
	 * @return TableInterface
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function save(array $post)
	{
		/** @var TableInterface $table */
		$table = JSFactory::getTable($this->itemName);

		$this->app->triggerEvent('onBeforeSave' . ucfirst($this->itemName), [&$post]);

		$table->bind($post);
		$table->store();

		$this->app->triggerEvent('onAfterSave' . ucfirst($this->itemName), [&$wishboxpaymentstatusTable]);

		return $table;
	}

	/**
	 * @param   array    $cid  Cid
	 * @param   integer  $msg  Msg
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function deleteList(array $cid, $msg = 1)
	{
		$res = [];
		$this->app->triggerEvent('onBeforeRemove' . ucfirst($this->itemName), [&$cid]);

		foreach ($cid as $id)
		{
			$table = $this->getDefaultTable();
			$table->load($id);
			$table->delete();

			if ($msg)
			{
				$this->app->enqueueMessage(_JSHOP_ITEM_DELETED, 'message');
			}

			$res[$id] = true;
		}

		$this->app->triggerEvent('onAfterRemove' . ucfirst($this->itemName), [&$cid]);

		return $res;
	}

	/**
	 * @param   array    $cid   Cid
	 * @param   integer  $flag  Flag
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function publish(array $cid, $flag)
	{
		$this->app->triggerEvent('onBeforePublish' . ucfirst($this->itemName), [&$cid, &$flag]);

		parent::publish($cid, $flag);

		$this->app->triggerEvent('onAfterPublish' . ucfirst($this->itemName), [&$cid, &$flag]);
	}
}
