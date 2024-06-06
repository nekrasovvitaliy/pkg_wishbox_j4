<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use RuntimeException;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\ShippingMethodTable;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class JshoppingshippingmethodField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'jhoppingshippingmethod';

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

		/** @var ShippingmethodTable $shippingmethodTable */
		$shippingmethodTable = JSFactory::getTable('shippingmethod');

		$shippingMethods = $shippingmethodTable->getAllShippingMethods();

		// Build the option list from the list of files.
		if (is_array($shippingMethods))
		{
			foreach ($shippingMethods as $shippingMethod)
			{
				$options[] = HTMLHelper::_(
					'select.option',
					$shippingMethod->shipping_id, // phpcs:ignore
					$shippingMethod->name
				);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
