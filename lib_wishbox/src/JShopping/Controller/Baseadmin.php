<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Controller;

use Joomla\Component\Jshopping\Administrator\Controller\BaseadminController;
use Wishbox\MainTrait;

/**
 * @property string $addonAlias
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Baseadmin extends BaseadminController
{
	use MainTrait;

	/**
	 * @since 1.0.0
	 *
	 * @return void
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function init()
	{
		$language = $this->app->getLanguage();
		$language->load(
			'plg_jshoppingadmin_' . $this->addonAlias,
			JPATH_PLUGINS . '/jshoppingadmin/' . $this->addonAlias
		);
	}
}
