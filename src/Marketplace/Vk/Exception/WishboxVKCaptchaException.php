<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Marketplace\Vk\Exception;

use Exception;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Адрес не найден в базе адресов
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class WishboxVKCaptchaException extends Exception
{
	/**
	 * @var string $img Img
	 * @since 1.0.0
	 */
	private string $img;

	/**
	 * @var string $sid Sid
	 * @since 1.0.0
	 */
	private string $sid;

	/**
	 * @param   string $img Img
	 * @param   string $sid Sid
	 * @since 1.0.0
	 */
	public function __construct(string $img, string $sid)
	{
		parent::__construct('WishBoxVKCaptchaException');

		$this->img = $img;
		$this->sid = $sid;
	}

	/**
	 * @return string
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function getImg(): string
	{
		return $this->img;
	}

	/**
	 * @return string
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function getSid(): string
	{
		return $this->sid;
	}
}
