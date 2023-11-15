<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use RuntimeException;
use SimpleXMLElement;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class JshoppingextrafieldField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'wishboxjshoppingextrafield';

	/**
	 * @var    array Extra field type
	 * @since 1.0.0
	 */
	protected array $extraFieldType;

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
	 * @var     SimpleXMLElement $element Element
	 * @since 1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$extraFieldType = explode(',', (string) $this->element['extrafield_type']);
			$extraFieldType = array_diff($extraFieldType, ['']);
			$this->extraFieldType = $extraFieldType;
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
		$extraFields = JSFactory::getAllProductExtraField();

		// Build the options list from the list of files.
		if (is_array($extraFields))
		{
			foreach ($extraFields as $extraField)
			{
				if (!count($this->extraFieldType) || in_array($extraField->type, $this->extraFieldType))
				{
					$options[] = HTMLHelper::_('select.option', $extraField->id, $extraField->name);
				}
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
