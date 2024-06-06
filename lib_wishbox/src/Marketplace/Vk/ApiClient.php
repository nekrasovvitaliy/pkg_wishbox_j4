<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Marketplace\Vk;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ApiClient
{
	/**
	 * @var integer $groupId Group id
	 *
	 * @since 1.0.0
	 */
	private int $groupId;

	/**
	 * @var string $accessToken Access token
	 *
	 * @since 1.0.0
	 */
	private string $accessToken;

	/**
	 * @var string $serviceToken Service token
	 *
	 * @since 1.0.0
	 */
	private string $serviceToken;

	/**
	 * @param   integer  $groupId       Group id
	 * @param   string   $accessToken   Access token
	 * @param   string   $serviceToken  Service token
	 *
	 * @since 1.0.0
	 */
	public function __construct(int $groupId, string $accessToken, string $serviceToken)
	{
		$this->groupId = $groupId;
		$this->accessToken = $accessToken;
		$this->serviceToken = $serviceToken;
	}

	/**
	 * @param   int  $userId User id
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function getUser(int $userId): mixed
	{
		$arr = [];
		$arr['v'] = '5.131';
		$arr['owner_id'] = '-' . $this->groupId;
		$arr['user_ids'] = implode(',', [$userId]);
		$arr['fields'] = implode(',', ['city', 'country']);
		$arr['access_token'] = $this->accessToken;
		$url = 'https://api.vk.com/method/users.get?';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($content);

		return $data->response[0];
	}

	/**
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function getOrders(): mixed
	{
		$arr = [];
		$arr['v'] = '5.199';
		$arr['offset'] = 0;
		$arr['count'] = 10;
		$arr['access_token'] = $this->accessToken;
		$url = 'https://api.vk.com/method/market.getOrders?';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		curl_close($ch);

		return json_decode($content);
	}

	/**
	 * @return mixed
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getGroupOrders(): mixed
	{
		$arr = [];
		$arr['v'] = '5.199';
		$arr['group_id'] = $this->groupId;
		$arr['offset'] = 0;
		$arr['count'] = 10;
		$arr['access_token'] = $this->accessToken;
		$url = 'https://api.vk.com/method/market.getGroupOrders?';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		curl_close($ch);

		return json_decode($content);
	}

	/**
	 * @param   integer  $userId   User id
	 * @param   integer  $orderId  Order id
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getOrderItems(int $userId, int $orderId): mixed
	{
		$arr = [];
		$arr['v'] = '5.199';
		$arr['user_id'] = $userId;
		$arr['order_id'] = $orderId;
		$arr['offset'] = 0;
		$arr['count'] = 999;
		$arr['access_token'] = $this->accessToken;
		$url = 'https://api.vk.com/method/market.getOrderItems?';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		curl_close($ch);

		return json_decode($content);
	}
}
