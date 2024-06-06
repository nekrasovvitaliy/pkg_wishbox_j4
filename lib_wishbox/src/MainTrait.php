<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox;

use Exception;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Database\DatabaseDriver;
use Wishbox\JShopping\AddonHelper;

/**
 * MainTrait
 *
 * @since 1.0.0
 */
trait MainTrait
{
	/**
	 * @var string $title Title
	 *
	 * @since 1.0.0
	 */
	public string $title;

	/**
	 * @param   string $property Property
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function __get($property): mixed
	{
		$app = Factory::getApplication();

		switch ($property)
		{
			case 'addon':
				// phpcs:ignore
				if (empty($this->addon_alias) && empty($this->addonAlias))
				{
					throw new Exception('$ddon_alias and $addonAlias are empty', 500);
				}

				$addonAlias = '';
				// phpcs:ignore
				if (!empty($this->addon_alias))
				{
					$addonAlias = $this->addon_alias; // phpcs:ignore
				}

				if (!empty($this->addonAlias))
				{
					$addonAlias = $this->addonAlias;
				}

				$addonClass = 'Addon' . ucfirst(mb_strtolower($addonAlias));
				JLoader::registerAlias(
					$addonClass,
					JPATH_SITE . '/components/com_jshopping/addons/' . $addonAlias . '/' . $addonAlias . '.php'
				);

				return call_user_func($addonClass . '::getInstance');

			case 'addonParams':
				// phpcs:ignore
				if (empty($this->addon_alias) && empty($this->addonAlias))
				{
					throw new Exception('Addon_alias is empty', 500);
				}

				$addonAlias = '';

				if (!empty($this->addon_alias)) // phpcs:ignore
				{
					$addonAlias = $this->addon_alias; // phpcs:ignore
				}

				if (!empty($this->addonAlias))
				{
					$addonAlias = $this->addonAlias;
				}

				return AddonHelper::getAddonParamsByAlias($addonAlias);

			case 'errorCategory':
				// phpcs:ignore
				if (empty($this->addon_alias) && empty($this->addonAlias))
				{
					throw new Exception('Addon_alias is empty', 500);
				}

				$addonAlias = '';

				if (!empty($this->addon_alias)) // phpcs:ignore
				{
					$addonAlias = $this->addon_alias; // phpcs:ignore
				}

				if (!empty($this->addonAlias))
				{
					$addonAlias = $this->addonAlias;
				}

				return  'com_jshopping.addon_' . $addonAlias;
			case 'adv_user':
			case 'advUser':
				return JSFactory::getUser();
			case 'app':
				return Factory::getApplication();
			case 'db':
				return Factory::getContainer()->get(DatabaseDriver::class);
			case 'user':
				return $app->getIdentity();
			case 'lang':
				return JSFactory::getLang();
			case 'language':
				return $app->getLanguage();
			case 'config':
			case 'jsconfig':
				return JSFactory::getConfig();
		}

		return null;
	}
}
