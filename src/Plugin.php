<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox;

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;

/**
 * @since 1.0.0
 */
class Plugin extends CMSPlugin
{
	use MainTrait;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  $subject The object to observe
	 * @param   array                $config  An optional associative array of configuration settings.
	 *                                        Recognized key values include 'name', 'group', 'params', 'language'
	 *                                        (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->title = 'PLG_' . $config['type'] . '_' . $config['name'];
	}

	/**
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function getLayoutPaths(): array
	{
		$template = $this->app->getTemplate();

		return [
			JPATH_SITE . '/templates/' . $template . '/html/layouts/plugins/' . $this->_type . '/' . $this->_name,
			JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name . '/layouts'
		];
	}

	/**
	 * @param   string $layoutId Layout id
	 * @return FileLayout
	 * @since 1.0.0
	 */
	protected function getRenderer(string $layoutId = 'default'): FileLayout
	{
		$renderer = new FileLayout($layoutId);
		$renderer->setIncludePaths($this->getLayoutPaths());

		return $renderer;
	}
}
