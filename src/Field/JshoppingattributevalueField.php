<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\AttributvalueTable;
use RuntimeException;
use SimpleXMLElement;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingattributevalueField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'Jshoppingattributevalue';

	/**
	 * @var   integer
	 * @since 1.0.0
	 */
	protected int $attributeId;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement   $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @var   SimpleXMLElement $element Element
	 * @since 1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->attributeId = (int) $this->element['attribute_id'];
		}

		return $return;
	}

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
		/**
		 * @var AttributvalueTable $attributvalueTable
		 */
		$attributvalueTable = JSFactory::getTable('attributvalue');
		$attributeValues = $attributvalueTable->getAllValues($this->attributeId);

		if (is_array($attributeValues) && count($attributeValues))
		{
			foreach ($attributeValues as $attributeValue)
			{
				$options[] = HTMLHelper::_('select.option',
					$attributeValue->value_id, // phpcs:ignore
					$attributeValue->name
				);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
