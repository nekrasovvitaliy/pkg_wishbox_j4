<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use RuntimeException;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\ProductlabelTable;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingproductlabelField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $type = 'jshoppingproductlabel';

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
		/**
		 * @var ProductlabelTable $productlabelTable
		 */
		$productlabelTable = JSFactory::getTable('productlabel');
		$productLabels = $productlabelTable->getListLabels();
		$options = [];
		$options[] = HTMLHelper::_('select.option', '-1', Text::_('JNONE'));

		foreach ($productLabels as $productLabel)
		{
			$options[] = HTMLHelper::_('select.option', $productLabel->id, $productLabel->name);
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
