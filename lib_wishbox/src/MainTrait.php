<?php
/**
 * @copyright   (c) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */
namespace Wishbox;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Database\DatabaseDriver;

/**
 * MainTrait
 *
 * @since 1.0.0
 */
trait MainTrait
{
	/**
	 * @var string  $title  Title
	 *
	 * @since 1.0.0
	 */
	public string $title;

	/**
	 * @param   string  $property  Property
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

		return match ($property) {
			'app' => Factory::getApplication(),
			'db' => Factory::getContainer()->get(DatabaseDriver::class),
			'user' => $app->getIdentity(),
			'lang' => JSFactory::getLang(),
			'language' => $app->getLanguage(),
			'config', 'jsconfig' => JSFactory::getConfig(),
			default => null,
		};

	}
}
