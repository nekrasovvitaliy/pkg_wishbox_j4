<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Database\DatabaseDriver;
use RuntimeException;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingordersourceField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'wishboxjshoppingordersource';

	/**
	 * Method to get the list of options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions(): array
	{
		if (!file_exists(JPATH_SITE . '/components/com_jshopping/bootstrap.php'))
		{
			throw new RuntimeException('Please install component \"joomshopping\"', 500);
		}

		require_once JPATH_SITE . '/components/com_jshopping/bootstrap.php';
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$lang = JSFactory::getLang();
		$nameField = $lang->get('name');
		$options = [];
		$query = $db->getQuery(true)
			->select('id')
			->select($db->qn($nameField, 'name'))
			->from('#__jshopping_wishboxordersources AS wos');
		$db->setQuery($query);
		$wishboxordersources = $db->loadObjectList();

		// Build the options list from the list of files.
		if (is_array($wishboxordersources))
		{
			foreach ($wishboxordersources as $wishboxordersource)
			{
				$options[] = HTMLHelper::_('select.option', $wishboxordersource->id, $wishboxordersource->name);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
