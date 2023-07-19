<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\AttributTable;
use RuntimeException;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingattributeField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'Jshoppingattribute';

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
		$options = [];
		/**
		 * @var AttributTable $attributTable
		 */
		$attributTable = JSFactory::getTable('attribut');
		$attributes = $attributTable->getAllAttributes();

		if (is_array($attributes) && count($attributes))
		{
			foreach ($attributes as $attribute)
			{
				$options[] = HTMLHelper::_(
					'select.option',
					$attribute->attr_id, // phpcs:ignore
					$attribute->name
				);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
