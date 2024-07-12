<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\MVC\Site\View\Form;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use stdClass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML Form View class for the component
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var  Form
	 *
	 * @since  1.0.0
	 */
	protected $form;

	/**
	 * The item being created
	 *
	 * @var  stdClass
	 *
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * The page to return to after the form is submitted
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $return_page = ''; // phpcs:ignore

	/**
	 * The model state
	 *
	 * @var  CMSObject
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * The page parameters
	 *
	 * @var Registry|null
	 *
	 * @since  1.0.0
	 */
	protected $params = null;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $pageclass_sfx = ''; // phpcs:ignore

	/**
	 * The user object
	 *
	 * @var User
	 *
	 * @since  1.0.0
	 */
	protected $user = null;

	/**
	 * Should we show a captcha form for the submission of the article?
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $captchaEnabled = false;

	/**
	 * Should we show Save As Copy button?
	 *
	 * @var    boolean
	 *
	 * @since  1.0.0
	 */
	protected $showSaveAsCopy = false;

	/**
	 * @var boolean $canApply Can apply
	 *
	 * @since 1.0.0
	 */
	protected bool $canApply;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void|boolean
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = $app->getIdentity();

		// Get model data.
		$this->state        = $this->get('State');

		$this->item         = $this->get('Item');

		$this->form         = $this->get('Form');
		$this->return_page  = $this->get('ReturnPage'); // phpcs:ignore
		$this->canApply		= $this->get('CanApply');

		$option = $app->input->getCmd('option', '');

		$name = str_replace('form', '', $this->getName());

		if (empty($this->item->id))
		{
			$authorised = $user->authorise(
				'core.create',
				$option
			) || $user->authorise(
				$name . '.create',
				$option
			);
		}
		else
		{
			$authorised = $this->item->params->get('access-edit');
		}

		if ($authorised !== true)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		if (!empty($this->item->id))
		{
			$tmp = new stdClass;
			$this->form->bind($tmp);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', '')); // phpcs:ignore

		$this->params = $params;

		// Override global params with article-specific params
		$this->params->merge($this->item->params);
		$this->user   = $user;

		$captchaSet = $params->get('captcha', Factory::getApplication()->get('captcha', '0'));

		foreach (PluginHelper::getPlugin('captcha') as $plugin)
		{
			if ($captchaSet === $plugin->name)
			{
				$this->captchaEnabled = true;

				break;
			}
		}

		// If the article is being edited, and the current user has permission to create an article
		if ($this->item->id
			&& ($user->authorise('core.create', $option))
		)
		{
			$this->showSaveAsCopy = true;
		}

		$this->componentParams = ComponentHelper::getParams($option);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 */
	protected function _prepareDocument(): void // phpcs:ignore
	{
		$app = Factory::getApplication();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_CONTENT_FORM_EDIT_ARTICLE'));
		}

		$title = $this->params->def('page_title', Text::_('COM_CONTENT_FORM_EDIT_ARTICLE'));

		$this->setDocumentTitle($title);

		$app->getPathway()->addItem($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->getDocument()->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$this->getDocument()->setMetaData('robots', $this->params->get('robots'));
		}
	}
}
