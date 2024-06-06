<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\AddonTable;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class AddonHelper
{
	/**
	 * @param   string $addonAlias Addon alias
	 *
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public static function renderSettingsForm(string $addonAlias): string
	{
		$app = Factory::getApplication();
		$language = $app->getLanguage();
		$language->load(
			'plg_jshoppingadmin_' . $addonAlias,
			JPATH_PLUGINS . '/jshoppingadmin/' . $addonAlias
		);
		$language->load('plg_jshoppingadmin_' . $addonAlias, JPATH_ADMINISTRATOR);

        $language->load('file_jshopping_' . $addonAlias, JPATH_SITE);

		Form::addFormPath(JPATH_SITE . '/components/com_jshopping/addons/' . $addonAlias . '/forms');
		$form = Form::getInstance(
			'configform',
			'configform',
			[
				'control' => 'params',
				'load_data' => true
			],
			false
		);
		PluginHelper::importPlugin('content');
		Factory::getApplication()->triggerEvent('onContentPrepareForm', [$form, []]);
		$params = self::getAddonParamsByAlias($addonAlias);
		$form->bind($params);
		$layoutData = [
			'form' => $form,
			'plugin_name' => $addonAlias
		];
		$renderer = new FileLayout('default');
		$renderer->setIncludePaths([JPATH_SITE . '/libraries/wishbox/src/layouts/addon/config']);

		return $renderer->render($layoutData);
	}

	/**
	 * @param   string $addonAlias Addon alias
	 *
	 * @return Registry
	 *
	 * @since 1.0.0
	 */
	public static function getAddonParamsByAlias(string $addonAlias): Registry
	{
		/** @var AddonTable $addonTable */
		$addonTable = JSFactory::getTable('addon');

		$addonTable->loadAlias($addonAlias);
		$addonParams = $addonTable->getParams();
		$registry = new Registry;
		$registry->loadArray($addonParams);

		return $registry;
	}
}
