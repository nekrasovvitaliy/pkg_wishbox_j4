<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Wishbox\MVC\Site\Model;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Wishboxceilcalc\Site\Trait\ComponentTrait;
use RuntimeException;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * List Model
 *
 * @since  1.0.0
 */
abstract class ListModel extends \Joomla\CMS\MVC\Model\ListModel
{
	use ComponentTrait;

	/**
	 * @var CMSApplicationInterface|null $app App
	 *
	 * @since 1.0.0
	 */
	protected ?CMSApplicationInterface $app;

	/**
	 * Constructor
	 *
	 * @param   array                     $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface|null  $factory  The factory.
	 *
	 * @throws Exception
	 *
	 * @since   1.0
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		parent::__construct($config);

		$this->app = Factory::getApplication();
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getReturnUrl(): string
	{
		return base64_encode(Uri::getInstance());
	}

	/**
	 * Method to autopopulate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   The field to order on.
	 * @param   string  $direction  The direction to order on.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (!($this->app instanceof SiteApplication))
		{
			throw new RuntimeException('', 500);
		}

		// Load the parameters. Merge Global and Menu Item params into a new object
		$params = $this->app->getParams();
		$this->setState('params', $params);

		parent::populateState($ordering, $direction);
	}
}
