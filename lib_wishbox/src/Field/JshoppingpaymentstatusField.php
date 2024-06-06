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

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class JshoppingpaymentstatusField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'jshoppingpaymentstatus';

	/**
	 * Method to get the list of options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions(): array
	{
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$lang = JSFactory::getLang();
		$options = [];
		$query = $db->getQuery(true)
			->select('id')
			->select($db->qn($lang->get('name'), 'name'))
			->from('#__jshopping_wishboxpaymentstatuses')
			->where('publish = 1')
			->order('ordering');
		$db->setQuery($query);
		$extraFields = $db->loadObjectList();

		// Build the option list from the list of files.
		if (is_array($extraFields))
		{
			foreach ($extraFields as $extraField)
			{
				$options[] = HTMLHelper::_('select.option', $extraField->id, $extraField->name);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
