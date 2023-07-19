<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use ShippingFormRoot;
use Wishbox\MainTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property CMSApplicationInterface $app
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class ShippingForm extends ShippingFormRoot
{
	use MainTrait;

	/**
	 * @var integer $version
	 * @since 1.0.0
	 */
	public int $version = 2;

	/**
	 * @var string $title Title
	 * @since 1.0.0
	 */
	private string $title;

	/**
	 * Constructor
	 * @since 1.0.0
	 */
	public function __construct()
	{
		JSFactory::loadExtLanguageFile('sm_' . get_class($this));
		$this->title = '_JSHOP_SM_' . mb_strtoupper(get_class($this));
	}


	/**
	 * @param   integer $shipping_id  Shipping id
	 * @param   array   $shippinginfo Shipping info
	 * @param   array   $params       Params
	 * @return void
	 * @since 1.0.0
	 */
	public function showForm(
		$shipping_id, // phpcs:ignore
		$shippinginfo,
		$params
	)
	{
	}

	/**
	 * @return string[]
	 * @since 1.0.0
	 */
	protected function getLayoutPaths(): array
	{
		$template = $this->app->getTemplate();

		return [
			JPATH_SITE . '/templates/' .
			$template . '/html/layouts/components/com_jshopping/shippingform/' . get_class($this) . '/offices',
			JPATH_SITE . '/components/com_jshopping/addons/' . get_class($this) . '/layouts/shippingform/offices'
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
