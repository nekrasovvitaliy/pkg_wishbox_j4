<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Wishbox\MVC\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Admin model.
 *
 * @since  1.0.0
 */
class AdminModel extends \Joomla\CMS\MVC\Model\AdminModel
{
	/**
	 * @var CMSObject|null $item Item
	 *
	 * @since 1.0.0
	 */
	protected ?CMSObject $item = null;

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getForm($data = [], $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		$option = $app->input->getCmd('option', '');

		// Get the form.
		$form = $this->loadForm(
			$option . '.' . $this->getName(),
			$this->getName(),
			[
				'control'	=> 'jform',
				'load_data' => $loadData
			]
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function loadFormData()
	{
		/** @var SiteApplication $app */
		$app = Factory::getApplication();

		$option = $app->input->getCmd('option', '');

		// Check the session for previously entered form data.
		$data = $app->getUserState($option . '.edit.' . $this->getName() . '.data', []);

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Prepare and sanitize the table prior to saving.
	 *
	 * @param   Table  $table  Table Object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function prepareTable($table)
	{
		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if ($table->ordering === '')
			{
				$db = Factory::getContainer()->get(DatabaseDriver::class);
				$db->setQuery('SELECT MAX(ordering) FROM ' . $table->getTableName());
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function canDelete($record)
	{
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user->authorise('core.delete', $this->option))
		{
			return true;
		}

		if ($user->authorise('core.delete.own', $this->option) && $user->id == $record->created_by) // phpcs:ignore
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   null  $pk  The id of the primary key.
	 *
	 * @return  CMSObject|boolean  Object on success, false on failure.
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem();

		$app = Factory::getApplication();
		$user = $this->getCurrentUser();

		$option = $app->input->get('option', '');
		$view = $app->input->get('view', '');

		if (!isset($item->params))
		{
			$item->params = new Registry;
		}

		if (!$user->guest)
		{
			// Check general edit permission first.
			if ($user->authorise('core.edit', $option))
			{
				$item->params->set('access-edit', true);
			}
			elseif (!empty($userId)
				&& ($user->authorise('core.edit.own', $option)
					|| $user->authorise('item.edit.own', $option)
					|| $user->authorise($view . '.edit.own', $option)))
			{
				// Now check if edit.own is available.
				// Check for a valid user and that they are the owner.
				if ($userId == $option->created_by) // phpcs:ignore
				{
					$option->params->set('access-edit', true);
				}
			}
		}

		return $item;
	}
}
