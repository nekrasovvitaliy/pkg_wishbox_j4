<?php
/**
 * @copyright (c) 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later;
 */
namespace Wishbox\MVC\Site\Controller;

use Joomla\CMS\Router\Route;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since  1.0.0
 */
class AdminController extends \Joomla\CMS\MVC\Controller\AdminController
{
	/**
	 * Removes an item.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function delete()
	{
		parent::delete();

		$return = $this->input->get('return', null, 'base64');

		if (!empty($return))
		{
			$this->setRedirect(
				Route::_(
					$return,
					false
				)
			);
		}
	}
}
