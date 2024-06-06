<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\DPD\Entity;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Registry\Registry;
use Wishbox\MainTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property Registry $addonParams
 * @property CMSApplicationInterface $app
 *
 * @noinspection PhpUnused
 *
 * @since 1.0.0
 */
class CountryCode
{
	use MainTrait;

	/**
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $currentCountryCode;

	/**
	 * @var string $nextCountryCode;
	 *
	 * @since 1.0.0
	 */
	protected string $nextCountryCode;

	/**
	 * @var string $firstCountryCode;
	 *
	 * @since 1.0.0
	 */
	protected string $firstCountryCode;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		// Получаем массив кодов стран из параметров дополнения
		$countryCodes = $this->addonParams->get('country_codes', []);

		// Получаем код страны из запроса
		$countryCode = $this->app->input->getCmd('country_code', '');

		// Если не пустой код страны из запроса и он есть в массиве всех кодов
		if (!empty($countryCode) && in_array($countryCode, $countryCodes))
		{
			// Значит это текущий код страны
			$this->currentCountryCode = $countryCode;
		}
		else
		{
			// Текущий код страны первый
			$this->currentCountryCode = $countryCodes[0];
		}

		$currentCountryCodeIndex = array_search($this->currentCountryCode, $countryCodes);

		if (isset($countryCodes[$currentCountryCodeIndex + 1]))
		{
			$this->nextCountryCode = $countryCodes[$currentCountryCodeIndex + 1];
		}
		else
		{
			$this->nextCountryCode = null;
		}

		$this->firstCountryCode = $countryCodes[0];
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getCurrent(): string
	{
		return $this->currentCountryCode;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getFirst(): string
	{
		return $this->firstCountryCode;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getNext(): string
	{
		return $this->nextCountryCode;
	}
}
