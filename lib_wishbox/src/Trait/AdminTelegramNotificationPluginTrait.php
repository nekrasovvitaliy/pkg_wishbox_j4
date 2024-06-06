<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Trait;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property Registry $params
 *
 * @method getAdminTelegramNotificationRecipient(): string
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
trait AdminTelegramNotificationPluginTrait
{
	/**
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 *
	 * @since 1.0.0
	 */
	public function sendAdminTelegramNotification(): bool
	{
		$botApiToken = $this->getAdminTelegramNotificationBotApiToken();
		$chatId = $this->getAdminTelegramNotificationChatId();
		$text = $this->getAdminTelegramNotificationMessage();
		$query = http_build_query(
			[
				'chat_id' => $chatId,
				'text' => $text,
			]
		);
		$url = 'https://1api.telegram.org/bot' . $botApiToken . '/sendMessage?' . $query;

		$http = HttpFactory::getHttp([], ['curl', 'stream']);
		$curlOptions = [];
		$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
		$curlOptions[CURLOPT_TIMEOUT] = 5;
		$http->setOption('transport.curl', $curlOptions);
		$headers = [
			'Content-Type' => 'application/json',
		];

		$response = $http->get($url, $headers);
		$body = $response->body;

		return true;
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAdminTelegramNotificationMessage(): string
	{
		$adminTelegramNotification = (array) $this->params->get('admin_telegram_notification', []);

		if (empty($adminTelegramNotification['message']))
		{
			throw new Exception('$adminTelegramNotification[message] must not be empty', 500);
		}

		$message = $adminTelegramNotification['message'];

		return $this->prepareAdminTelegramNotificationMessage($message);
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getAdminTelegramNotificationLayoutData(): array
	{
		return [];
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAdminTelegramNotificationBotApiToken(): string
	{
		$adminTelegramNotification = (array) $this->params->get('admin_telegram_notification', []);

		if (empty($adminTelegramNotification['bot_api_token']))
		{
			throw new Exception('$adminTelegramNotificationBotApiToken must not be empty', 500);
		}

		return $adminTelegramNotification['bot_api_token'];
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAdminTelegramNotificationChatId(): string
	{
		$adminTelegramNotification = (array) $this->params->get('admin_telegram_notification', []);

		if (empty($adminTelegramNotification['chat_id']))
		{
			throw new Exception('$params[admin_telegram_notification][chat_id] must not be empty', 500);
		}

		return $adminTelegramNotification['chat_id'];
	}
}
