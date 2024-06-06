<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Lib\TreeObjectList;
use Joomla\Database\DatabaseDriver;
use RuntimeException;
use SimpleXMLElement;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class JshoppingselectcategoryField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	public $type = 'jshoppingselectcategory';

	/**
	 * The rootOnly.
	 *
	 * @var    boolean
	 *
	 * @since  1.0
	 */
	protected bool $rootOnly = false;

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
			$rootOnly = (string) $this->element['root_only'];
			$this->rootOnly = ($rootOnly === 'true' || $rootOnly === 'rootOnly' || $rootOnly === '1');
		}

		return $return;
	}

	/**
	 * @return string
	 *
	 * @throws \Exception
	 */
	protected function getInput()
	{
		$html = parent::getInput();
		$app = Factory::getApplication();
		$document = $app->getDocument();
		$document->addStyleDeclaration(
			'.chzn-container-multi .chzn-drop{text-align:left;}'
		);

		return '
		<div class="thumbnail" style="text-align:center;background-color:#eee;display:inline-block;border:1px solid #000;">
			<button
				class="btn btn-small btn-success" style="margin:0 0 5px;"
				onclick="jQuery(this).parent().find(\'option\').attr(\'selected\',true).parent().trigger(\'liszt:updated\');
				return false"
			>'.
			Text::_('JGLOBAL_SELECTION_ALL').'
			</button>
			<button class="btn btn-small btn-danger" style="margin:0 0 5px;" onclick="jQuery(this).parent().find(\'option\').attr(\'selected\',false).parent().trigger(\'liszt:updated\');return false">'.
			Text::_('JGLOBAL_SELECTION_NONE').'
			</button>
			<br/>' .
			$html .
			'<div style="text-align:center;display:block;margin:5px 0 0;">
				<button class="btn btn-small btn-inverse" onclick="jQuery(\'#datacategory_ids\').chosen(\'destroy\').css(\'min-height\', \'300px\');return false">'.
			Text::_('JGLOBAL_SELECTION_INVERT').'
				</button>
				<button class="btn btn-small btn-primary" onclick="jQuery(\'#datacategory_ids\').chosen({placeholder_text_multiple: \''.Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS').'\'});return false">'.
			Text::_('JPREV').'
				</button>
			</div>
		</div>
		';
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
		if (!file_exists(JPATH_SITE . '/components/com_jshopping/bootstrap.php'))
		{
			throw new RuntimeException('Please install component \"joomshopping\"', 500);
		}

		require_once JPATH_SITE . '/components/com_jshopping/bootstrap.php';
		$options = [];
		$categories = $this->getCategories();

		foreach ($categories as $category)
		{
			$options[] = HTMLHelper::_(
				'select.option',
				$category->category_id, // phpcs:ignore
				$category->name
			);
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * @return array
	 * @since 1.0.0
	 */
	public function getCategories(): array
	{
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$lang = JSFactory::getLang();
		$query = $db->getQuery(true)
			->select($db->qn($lang->get('name'), 'name'))
			->select('category_id')
			->select('category_parent_id')
			->select('category_publish')
			->from('#__jshopping_categories');

		if ($this->rootOnly)
		{
			$query->where('category_parent_id = 0');
		}

		$query->order('category_parent_id, ordering');
		$db->setQuery($query);
		$list = $db->loadObJectList();
		$tree = new TreeObjectList($list, [
			'parent'    => 'category_parent_id',
			'id'        => 'category_id',
			'is_select' => 1
			]
		);

		return $tree->getList();
	}
}
