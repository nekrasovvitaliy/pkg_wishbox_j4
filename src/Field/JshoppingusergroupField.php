<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Administrator\Model\UsergroupsModel;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use RuntimeException;

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingusergroupField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'wishboxjshoppingusergroup';

	/**
	 * Method to get the list of options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions(): array
	{
		$options = [];

		if (!file_exists(JPATH_SITE . '/components/com_jshopping/bootstrap.php'))
		{
			throw new RuntimeException('Please install component \"joomshopping\"', 500);
		}

		require_once JPATH_SITE . '/components/com_jshopping/bootstrap.php';
		/** @var UsergroupsModel $usergroupsModel */
		$usergroupsModel = JSFactory::getModel('usergroups');
		$userGroups = $usergroupsModel->getAllUsergroups();

		// Build the options list from the list of files.
		if (is_array($userGroups))
		{
			foreach ($userGroups as $userGroup)
			{
				$options[] = HTMLHelper::_(
					'select.option',
					$userGroup->usergroup_id, // phpcs:ignore
					$userGroup->usergroup_name // phpcs:ignore
				);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
