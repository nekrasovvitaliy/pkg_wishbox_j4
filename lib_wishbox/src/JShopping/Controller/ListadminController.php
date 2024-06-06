<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Controller;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\MVC\View\ViewInterface;
use Joomla\Component\Jshopping\Administrator\Model\LanguagesModel;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\ConfigTable;
use JSHelperAdmin;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property ConfigTable $jsconfig
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ListadminController extends Baseadmin
{
	/**
	 * @var string $itemName Item name
	 *
	 * @since 1.0.0
	 */
	protected string $itemName;

	/**
	 * @var string $nameModel Model name
	 *
	 * @since 1.0.0
	 */
	protected $nameModel = null;

	/**
	 * @since 1.0.0
	 *
	 * @return void
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function init()
	{
		parent::init();

		$this->nameModel = $this->name;
		JSHelperAdmin::checkAccessController($this->name);
		JSHelperAdmin::addSubmenu('other');
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @return  ViewInterface  Reference to the view or an error.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getListView(): ViewInterface
	{
		$context = 'jshoping.list.admin.' . $this->name;
		$filterOrder = $this->app->getUserStateFromRequest(
			$context . 'filter_order',
			'filter_order',
			'id',
			'cmd'
		);
		$filterOrderDir = $this->app->getUserStateFromRequest(
			$context . 'filter_order_Dir',
			'filter_order_Dir',
			'asc',
			'cmd'
		);

		$listModel = JSFactory::getModel($this->name);

		$rows = $listModel->getList($filterOrder, $filterOrderDir);

		$view = $this->getView($this->name, 'html');
		$view->setLayout('list');
		$view->rows = $rows;
		$view->config = $this->jsconfig;
		$view->filterOrder = $filterOrder;
		$view->filterOrderDir = $filterOrderDir;
		$view->sidebar = Sidebar::render();
		$this->app->triggerEvent('onBeforeDisplay' . ucfirst($this->name), [&$view]);

		return $view;
	}

	/**
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = $this->getListView();
		$view->displayList();
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @return  ViewInterface  Reference to the view or an error.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getEditView(): ViewInterface
	{
		$id = $this->input->getInt('id');

		$table = JSFactory::getTable($this->itemName);

		$table->load($id);
		$edit = ($id) ? (1) : (0);

		/** @var LanguagesModel $languagesModel */
		$languagesModel = JSFactory::getModel('languages');

		$languages = $languagesModel->getAllLanguages();
		$multilang = count($languages) > 1;

		$form = Factory::getContainer()
			->get(FormFactoryInterface::class)
			->createForm(
				$this->itemName,
				[
					'control' => '',
					'load_data' => true
				]
			);
		$xml = file_get_contents(
			JPATH_SITE . '/components/com_jshopping/addons/' . $this->addonAlias . '/forms/' . $this->itemName . '.xml'
		);

		$form->load($xml);
		$form->bind($table);

		$view = $this->getView($this->name, 'html');
		$view->setLayout('edit');
		$view->row = $table;
		$view->form = $form;
		$view->config = $this->jsconfig;
		$view->edit = $edit;
		$view->languages = $languages;
		$view->multilang = $multilang;
		$view->etemplatevar = '';
		$this->app->triggerEvent('onBeforeEdit' . ucfirst($this->itemName), [&$view]);

		return $view;
	}

	/**
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function edit(): void
	{
		$view = $this->getEditView();
		$view->displayEdit();
	}
}
