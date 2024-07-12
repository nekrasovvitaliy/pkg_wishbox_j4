<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\MVC\Site\View\List;

use Exception;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use stdClass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML List View class for the component
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var  stdClass[] $items Items
	 *
	 * @since  1.0.0
	 */
	protected array $items = [];

	/**
	 * @var  CMSObject $state The model state
	 *
	 * @since 1.0.0
	 */
	protected CMSObject $state;

	/**
	 * @var   User|null $user The user object
	 *
	 * @since 1.0.0
	 */
	protected ?User $user = null;

	/**
	 * The page parameters
	 *
	 * @var    Registry|null
	 *
	 * @since  1.0.0
	 */
	protected ?Registry $params = null;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected string $pageclass_sfx = ''; // phpcs:ignore

	/**
	 * @var string $returnUrl Return URL
	 *
	 * @since 1.0.0
	 */
	protected string $returnUrl;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function display($tpl = null)
	{
		/** @var CMSWebApplicationInterface $app */
		$app = Factory::getApplication();

		$this->items		= $this->get('Items');
		$this->state		= $this->get('State');
		$this->returnUrl	= $this->get('ReturnUrl');

		$this->user = $this->getCurrentUser();

		$this->params = $this->state->get('params');

		$option = $app->input->getCmd('option', '');

		if ($this->user->guest)
		{
			$return = base64_encode(Uri::getInstance());
			$loginUrlWithReturn = Route::_('index.php?option=com_users&view=login&return=' . $return);
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
			$app->redirect($loginUrlWithReturn, 403);
		}
		else
		{
			$filterOwn = $app->input->getInt('filter_own', 0);

			$authorised = $this->user->authorise('items.view', $option)
				|| ($this->user->authorise('items.view.own', $option) && $filterOwn);

			if ($authorised !== true)
			{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);

				return false;
			}
		}

		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', '')); // phpcs:ignore
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	protected function _prepareDocument(): void // phpcs:ignore
	{
		/** @var CMSWebApplicationInterface $app */
		$app = Factory::getApplication();

		$option = $app->input->getCmd('option', '');

		$menu = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def(
				'page_heading',
				Text::_(mb_strtoupper($option) . '_' . mb_strtoupper($this->getName()))
			);
		}

		$title = $this->params->get('page_title', '');
		$this->setDocumentTitle($title);
	}
}
