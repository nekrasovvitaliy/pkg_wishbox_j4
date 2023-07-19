<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

use AddonCore;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutInterface;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\ConfigTable;
use Joomla\Registry\Registry;
use Wishbox\MainTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// phpcs:disable PSR1.Files.SideEffects
require_once JPATH_SITE . '/components/com_jshopping/addons/addon_core.php';
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property Language $language
 * @property CMSApplicationInterface $app
 * @since 1.0.0
 */
class Addon extends AddonCore implements AddonInterface
{
	use MainTrait;

	/**
	 * @var Addon $instance Addon
	 * @since 1.0.0
	 */
	private static Addon $instance;

	/**
	 * @var   ConfigTable $config Config
	 * @since 1.0.0
	 */
	protected ConfigTable $config;

	/**
	 * @var   string $title Title
	 * @since 1.0.0
	 */
	public string $title;

	/**
	 * @since 1.0.0
	 */
	public string $errorCategory = 'com_jshopping';

	/**
	 * @var string
	 * @since 1.0.0
	 */
	private string $addonAlias;

	/**
	 * @var Registry $params
	 * @since 1.0.0
	 */
	private mixed $params;

	/**
	 * @since 1.0.0
	 */
	private function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return self
	 * @since 1.0.0
	 */
	private function __clone()
	{
		// Отключаем клонирование
	}

	/**
	 * @since 1.0.0
	 */
	public function __wakeup()
	{
		// Отключаем десериализацию
	}

	/**
	 * @since 1.0.0
	 */
	public static function getInstance(): AddonInterface
	{
		$class = static::class;

		if (!isset(self::$instance[$class]))
		{
			self::$instance[$class] = new $class;
			self::$instance[$class]->addonAlias = strtolower(str_replace('Addon', '', $class));
			self::$instance[$class]->errorCategory = 'com_jshopping.addon_' . self::$instance[$class]->addon_alias;
			self::$instance[$class]->params = self::$instance[$class]->getAddonParams();
			self::$instance[$class]->checkParams();
			self::$instance[$class]->title = '_JSHOP_' . strtoupper(str_replace('addon', 'addon_', strtolower($class)));
			Log::addLogger(
				['text_file' => self::$instance[$class]->errorCategory . '.log.php'],
				Log::ALL,
				[self::$instance[$class]->errorCategory]
			);

			if (self::$instance[$class]->params->get('debug_mode', 0))
			{
							self::$instance[$class]->check();
			}
		}

		return self::$instance[$class];
	}

	/**
	 * @since 1.0.0
	 */
	public function getAddonParams(): Registry
	{
		$addonParams = parent::getAddonParams();
		$registry = new Registry;
		$registry->loadArray($addonParams);

		return $registry;
	}


	/**
	 * Returns addon alias
	 *
	 * Returns addon alias
	 * @since 1.0.0
	 */
	public function getAlias(): string
	{
		return $this->addon_alias;
	}

	/**
	 * @throws Exception
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function showAdminFormParams(): void
	{
		$this->language->load(
			'plg_jshoppingadmin_' . $this->addon_alias,
			JPATH_PLUGINS . '/jshoppingadmin/' . $this->addon_alias
		);
		$this->language->load('plg_jshoppingadmin_' . $this->addon_alias, JPATH_ADMINISTRATOR);
		$plugin_name = str_replace('wishbox', 'wishboxadmin', $this->addon_alias);
		$this->language->load('plg_jshoppingadmin_' . $plugin_name, JPATH_PLUGINS . '/jshoppingadmin/' . $plugin_name);
		$this->language->load('plg_jshoppingadmin_' . $plugin_name, JPATH_ADMINISTRATOR);
		Form::addFormPath(JPATH_SITE . '/components/com_jshopping/addons/' . $this->addon_alias . '/forms');
		$form = Factory::getContainer()
			->get(FormFactoryInterface::class)
			->createForm('configform', ['control' => 'params', 'load_data' => true]);
		PluginHelper::importPlugin('content');
		$event = AbstractEvent::create('onContentPrepareForm', [$form, []]);
		$this->app->getDispatcher()->dispatch($event->getName(), $event);
		$form->bind($this->params);
		$layoutData = [
			'form' => $form,
			'plugin_name' => $this->addon_alias
		];
		echo $this->getRenderer()->render($layoutData);
	}

	/**
	 * @throws Exception
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function renderSettingsForm(string $addonAlias): string
	{
		$this->language->load(
			'plg_jshoppingadmin_' . $addonAlias,
			JPATH_PLUGINS . '/jshoppingadmin/' . $addonAlias
		);
		$this->language->load('plg_jshoppingadmin_' . $addonAlias, JPATH_ADMINISTRATOR);

		$plugin_name = str_replace('wishbox', 'wishboxadmin', $addonAlias);
		$this->language->load('plg_jshoppingadmin_' . $plugin_name, JPATH_PLUGINS . '/jshoppingadmin/' . $plugin_name);
		$this->language->load('plg_jshoppingadmin_' . $plugin_name, JPATH_ADMINISTRATOR);

		Form::addFormPath(JPATH_SITE . '/components/com_jshopping/addons/' . $addonAlias . '/forms');
		$form = Factory::getContainer()
			->get(FormFactoryInterface::class)
			->createForm('configform', ['control' => 'params', 'load_data' => true]);
		PluginHelper::importPlugin('content');
		$event = AbstractEvent::create('onContentPrepareForm', [$form, []]);
		$this->app->getDispatcher()->dispatch($event->getName(), $event);
		$params = self::getAddonParamsByAlias($addonAlias);
		$form->bind($params);
		$layoutData = [
			'form' => $form,
			'plugin_name' => $addonAlias
		];

		return $this->getRenderer()->render($layoutData);
	}

	public static function getAddonParamsByAlias(string $addonAlias): Registry
	{
		$addonTable = JSFactory::getTable('addon');
		$addonTable->loadAlias($addonAlias);
		$addonParams = $addonTable->getParams();
		$registry = new Registry;
		$registry->loadArray($addonParams);

		return $registry;
	}

	/**
	 * Get the layout paths
	 *
	 * @return  array
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	protected function getLayoutPaths(): array
	{
		return [
			JPATH_SITE . '/libraries/Wishbox/src/layouts/addon/config',
		];
	}

	/**
	 * Get the plugin renderer
	 *
	 * @param   string $layoutId Layout identifier
	 *
	 * @return  LayoutInterface
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	protected function getRenderer(string $layoutId = 'default'): LayoutInterface
	{
		$renderer = new FileLayout($layoutId);
		$renderer->setIncludePaths($this->getLayoutPaths());

		return $renderer;
	}

	/**
	 * Render a layout of this plugin
	 *
	 * @param   string $layoutId Layout identifier
	 * @param   array $data Optional data for the layout
	 *
	 * @return  string
	 *
	 * @throws Exception
	 * @since   1.0.0
	 */
	public function render(string $layoutId, array $data = []): string
	{
		return $this->getRenderer($layoutId)->render($data);
	}

	/**
	 *
	 * @since   1.0.0
	 */
	protected function check(): void
	{
		$this->checkParams();
		$this->checkDatabase();
	}

	/**
	 *
	 * @since   1.0.0
	 */
	protected function checkDatabase(): void
	{
		//
		//
	}


	/**
	 *
	 * @since   1.0.0
	 */
	protected function checkParams(): void
	{
		//
		//
	}
}
