<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 *
 * @property string $text
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ButtonField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'Button';

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
	 * @since 1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->text = (string) $this->element['text'];
			$this->onclick = (string) $this->element['onclick'];
		}

		return $return;
	}

	/**
	 * @return string Html
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function getInput(): string
	{
		/** @var CMSApplication $app */
		$app = Factory::getApplication();
		$document = $app->getDocument();

		if ($app->isClient('site'))
		{
			$wa = $document->getWebAssetManager();

			$wa->addInlineStyle(
				'#jformparamscategories{width:auto;}
                #attrib-selecting_categories .control-label{width:65px;}
                #attrib-selecting_categories .controls{margin-left:0;}
                #general .subform-table-layout input{max-width:unset;}
                #general .subform-table-layout input.file-link{min-width:440px;}
                #general .subform-table-layout .control-group .chzn-container{width:auto!important;}
                #general .subform-table-layout .chzn-container-single .chzn-single span{padding:0 10px 0 0;}'
			);
		}

		$html = '<button class="btn btn-warning button" type="button" onclick="' . $this->onclick . '">';
		$html .= Text::_($this->text);
		$html .= '</button>';

		return $html;
	}
}
