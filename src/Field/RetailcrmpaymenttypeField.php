<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class RetailcrmpaymenttypeField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'retailcrmpaymenttype';

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
		$referencesModel = JSFactory::getModel('references', 'Site\\Wishbox\\Retailcrm');
		$orderStatuses = $referencesModel->getPaymentTypes();

		// Build the options list from the list of files.
		if (is_array($orderStatuses))
		{
			foreach ($orderStatuses as $code => $orderStatus)
			{
				$options[] = HTMLHelper::_('select.option', $code, $orderStatus->name);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
