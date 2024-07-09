<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Wishbox\MVC\Site\Controller;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller tailored to suit most form-based admin operations.
 *
 * @since  1.0.0
 */
class FormController extends \Joomla\CMS\MVC\Controller\FormController implements FormFactoryAwareInterface
{
	/**
	 * The URL edit variable.
	 *
	 * @var string
	 *
	 * @since  1.0.0
	 */
	protected string $urlVar = 'a.id';

	/**
	 * Method to add a new record.
	 *
	 * @return  boolean  True if the record can be added, false if not.
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());

			return false;
		}

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item . '&a_id=0' // phpcs:ignore
				. $this->getRedirectToItemAppend(),
				false
			)
		);

		return true;
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function allowAdd($data = [])
	{
		$user = $this->app->getIdentity();

		return $user->authorise('core.create', $this->option);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;

		$user = $this->app->getIdentity();

		// Zero-record (id:0), return component edit permission by calling parent controller method
		if (!$recordId)
		{
			return parent::allowEdit($data, $key);
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->option)
			|| $user->authorise($this->name . '.edit', $this->option))
		{
			return true;
		}

		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->option)
			|| $user->authorise($this->name . '.edit.own', $this->option))
		{
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			// Grant if the current user is an owner of the record
			return $user->id == $record->created_by; // phpcs:ignore
		}

		return false;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   BaseDatabaseModel  $model      The data model object.
	 * @param   array              $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function postSaveHook(BaseDatabaseModel $model, $validData = [])
	{
		if (in_array($this->getTask(), ['save2copy', 'apply'], true))
		{
			return;
		}

		/** @var SiteApplication $app */
		$app = $this->app;

		$itemId = $app->getInput()->getInt('a_id');

		// Load the parameters.
		$params   = $app->getParams();
		$menuitem = (int) $params->get('redirect_menuitem');

		// Check for redirection after submission when creating a new article only
		if ($menuitem > 0 && $itemId == 0)
		{
			$lang = '';

			if (Multilanguage::isEnabled())
			{
				$item = $app->getMenu()->getItem($menuitem);
				$lang = !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
			}

			$this->setRedirect(Route::_('index.php?Itemid=' . $menuitem . $lang, false));
		}
		else
		{
			$this->setRedirect(Route::_($this->getReturnPage(), false));
		}
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return  string  The return URL.
	 *
	 * @since   1.0.0
	 */
	protected function getReturnPage(): string
	{
		/** @var SiteApplication $app */
		$app = $this->app;

		$params = $app->getParams();

		$menuitem = (int) $params->get('redirect_menuitem');

		if ($menuitem > 0)
		{
			return 'index.php?Itemid=' . $menuitem;
		}

		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return)))
		{
			return Uri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}

	/**
	 * Method to reload a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function reload($key = null, $urlVar = 'a_id')
	{
		parent::reload($key, $urlVar);
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		// Need to override the parent method completely.
		$tmpl = $this->input->get('tmpl');

		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();

		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}

		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 * (Sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		if (!$result)
		{
			$returnPage = $this->getReturnPage();
			$returnPage = Route::_($returnPage, false);

			$this->setRedirect($returnPage);
		}

		return $result;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function cancel($key = 'a_id')
	{
		$result = parent::cancel($key);

		/** @var SiteApplication $app */
		$app = $this->app;

		// Load the parameters.
		$params = $app->getParams();

		$customCancelRedir = (bool) $params->get('custom_cancel_redirect');

		if ($customCancelRedir)
		{
			$cancelMenuitemId = (int) $params->get('cancel_redirect_menuitem');

			if ($cancelMenuitemId > 0)
			{
				$item = $app->getMenu()->getItem($cancelMenuitemId);
				$lang = '';

				if (Multilanguage::isEnabled())
				{
					$lang = !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
				}

				// Redirect to the user-specified return page.
				$redirlink = $item->link . $lang . '&Itemid=' . $cancelMenuitemId;
			}
			else
			{
				// Redirect to the same article submission form (clean form).
				$redirlink = $app->getMenu()->getActive()->link . '&Itemid=' . $app->getMenu()->getActive()->id;
			}
		}
		else
		{
			$menuitemId = (int) $params->get('redirect_menuitem');

			if ($menuitemId > 0)
			{
				$lang = '';
				$item = $app->getMenu()->getItem($menuitemId);

				if (Multilanguage::isEnabled())
				{
					$lang = !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
				}

				// Redirect to the general (redirect_menuitem) user specified return page.
				$redirlink = $item->link . $lang . '&Itemid=' . $menuitemId;
			}
			else
			{
				// Redirect to the return page.
				$redirlink = $this->getReturnPage();
			}
		}

		$this->setRedirect(Route::_($redirlink, false));

		return $result;
	}
}
