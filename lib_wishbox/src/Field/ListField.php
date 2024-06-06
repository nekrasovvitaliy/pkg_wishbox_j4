<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\Form\Field\ListField as FieldListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ListField extends FieldListField
{
	/**
	 * The hideNone.
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected bool $hideNone = false;

	/**
	 * The hideDefault.
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected bool $hideDefault = false;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement   $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example, if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 *
	 * @var   SimpleXMLElement $element Element
	 *
	 * @since 1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$hideNone = (string) $this->element['hide_none'];
			$this->hideNone = ($hideNone == 'true' || $hideNone == 'hideNone' || $hideNone == '1');
			$hideDefault = (string) $this->element['hide_default'];
			$this->hideDefault = ($hideDefault == 'true' || $hideDefault == 'hideDefault' || $hideDefault == '1');
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

		// Prepend some default options based on field attributes.
		if (!$this->hideNone)
		{
			$options[] = HTMLHelper::_(
				'select.option',
				'-1',
				Text::alt(
					'JOPTION_DO_NOT_USE',
					preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)
				)
			);
		}

		if (!$this->hideDefault)
		{
			$options[] = HTMLHelper::_(
				'select.option',
				'',
				Text::alt(
					'JOPTION_USE_DEFAULT',
					preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)
				)
			);
		}

		// Merge any additional options in the XML definition.
		return array_merge($options, parent::getOptions());
	}

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutPaths()
	{
		$layoutPaths = parent::getLayoutPaths();

		$layoutPaths[] = JPATH_SITE . '/libraries/Wishbox/src/layouts';

		return $layoutPaths;
	}
}
