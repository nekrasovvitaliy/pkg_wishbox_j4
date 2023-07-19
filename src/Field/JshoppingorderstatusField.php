<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Database\DatabaseDriver;
use stdclass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingorderstatusField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $type = 'jshoppingorderstatus';

	/**
	 * Method to get the list of options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions(): array
	{
		$lang = JSFactory::getLang();
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$query = $db->getQuery(true)
			->select('os.status_id as value')
			->select('os.`' . $lang->get('name') . '` as text')
			->from('#__jshopping_order_status AS os');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		$none = new stdclass;
		$none->value = 0;
		$none->text = Text::_('JNONE');

		return array_merge([$none], $options);
	}
}
