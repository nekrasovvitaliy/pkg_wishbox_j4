<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\View\Listadmin;

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var string $tmpHtmlStart Tmp Html start
	 *
	 * @since 1.0.0
	 */
	public string $listText;

	/**
	 * @var string $tmpHtmlStart Tmp Html start
	 *
	 * @since 1.0.0
	 */
	public string $itemText;

	/**
	 * @var string $tmpHtmlStart Tmp Html start
	 *
	 * @since 1.0.0
	 */
	public string $tmpHtmlStart = '';

	/**
	 * @var string $tmpHtmlEnd Tmp Html end
	 *
	 * @since 1.0.0
	 */
	public string $tmpHtmlEnd = '';

	/**
	 * @var string $addonAlias Addon alias
	 *
	 * @since 1.0.0
	 */
	protected string $addonAlias;

	/**
	 * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function displayList(?string $tpl = null): void
	{
		ToolbarHelper::title(
			Text::_('PLG_JSHOPPINGADMIN_' . mb_strtoupper($this->addonAlias) . '_' . $this->listText . '_TITLE')
		);
		ToolbarHelper::addNew();
		ToolbarHelper::publishList();
		ToolbarHelper::unpublishList();
		ToolbarHelper::deleteList(Text::_('JSHOP_DELETE_ITEM_CAN_BE_USED'));

		parent::display($tpl);
	}

	/**
	 * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function displayEdit(?string $tpl = null): void
	{
		ToolbarHelper::title(
			$this->edit
				? Text::_('PLG_JSHOPPINGADMIN_' . mb_strtoupper($this->addonAlias) . '_WISHBOX_' . $this->itemText . '_EDIT')
				: Text::_('PLG_JSHOPPINGADMIN_' . mb_strtoupper($this->addonAlias) . '_WISHBOX_' . $this->itemText . '_NEW')
		);
		ToolbarHelper::save();
		ToolbarHelper::spacer();
		ToolbarHelper::apply();
		ToolbarHelper::spacer();
		ToolbarHelper::cancel();

		parent::display($tpl);
	}
}
