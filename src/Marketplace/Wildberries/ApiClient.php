<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Marketplace\Wildberries;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 *
 */
class ApiClient
{
	/**
	 *
	 */
	private string $api_key;
	
	/**
	 * 
	 */
	protected const ERROR_CATEGORY = 'com_jshopping.addon_wishboxwildberries.wildberries';
	
	/**
	 * 
	 */
	public function __construct(string $api_key)
	{
		$this->api_key = $api_key;
		Log::addLogger(
			['text_file' => self::ERROR_CATEGORY.'.log.php'],
			Log::ALL,
			[self::ERROR_CATEGORY]
		);
	}
	
	/**
	 *
	 */
	public function ship(array $data)
	{
		throw new \Exception('supply_id param must not be empty', 500);

		if ($result = $this->sendRequest('/api/v2/orders', $data, 'put'))
		{
			return $result;
		}

		return false;
	}

	/**
	 *
	 */
	public function getSupplies(array $params)
	{
		if ($result = $this->sendRequest('/api/v3/supplies'.'?'.http_build_query($params)))
		{
			return $result;
		}

		return false;
	}

	/**
	 *
	 */
	public function createSupply(array $params)
	{
		if ($result = $this->sendRequest('/api/v3/supplies', $params, 'post'))
		{
			return $result;
		}

		return false;
	}

	/**
	 *
	 */
	public function deleteSupply(array $params)
	{
		if ($result = $this->sendRequest('/api/v3/supplies/'.$params['id'], [], 'delete'))
		{
			return $result;
		}

		return false;
	}

	/**
	 *
	 */
	public function supplyAddOrder(string $supply_id, string $order_id)
	{
		if ($result = $this->sendRequest(
				'/api/v3/supplies/'.$supply_id.'/orders/'.$order_id,
				[],
				'patch',
				[],
				[204]
			))
		{
			return $result;
		}

		return false;
	}

	/**
	 *
	 */
	public function getStocks(array $params, int $warehouse_id)
	{
		if ($warehouse_id <= 0)
		{
			throw new Exception('warehouse_id param must be more than zero', 500);
		}																																				

		if ($result = $this->sendRequest('/api/v3/stocks/'.$warehouse_id, $params, 'post'))
		{
			return $result->stocks;
		}

		return false;
	}

	/**
	 *
	 */
	public function getCards()
	{
		$params = [
					'sort' => [
									'cursor'	=> [
														'limit' => 1000
													],
									'filter'	=> [
														'withPhoto' => -1
													]
							]
				];

		if ($result = $this->sendRequest('/content/v1/cards/cursor/list', $params, 'post', [], [200]))
		{
			return $result;
		}

		return false;
	}

	/**
	 *
	 */
	public function updateStocks(array $data, int $warehouse_id): \stdclass|bool
	{
		if ($warehouse_id <= 0)
		{
			throw new Exception('warehouse_id param must be more than zero', 500);
		}

		$result = $this->sendRequest(
			'/api/v3/stocks/'.$warehouse_id,
			$data,
			'put',
			[],
			[204]
		);

		if ($result !== true)
		{
			throw new \Exception('sendRequest return false', 500);
		}

		return $result;
	}

	/**
	 *
	 */
	public function updatePrices(array $data)
	{
		if ($result = $this->sendRequest('/public/api/v1/prices', $data, 'post'))
		{
			return $result;
		}

		return true;
	}

	/**
	 *
	 */
	public function getOrders(array $params): \stdclass
	{
		if ($result = $this->sendRequest('/api/v3/orders'.'?'.http_build_query($params)))
		{
			if (!isset($result->error))
			{
				return $result;
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function getOrdersStatus(array $params): \stdclass
	{
		if ($result = $this->sendRequest('/api/v3/orders/status', $params, 'post'))
		{
			if (!isset($result->error))
			{
				return $result;
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function getOrdersStickersPng(array $data)
	{
		$result = $this->sendRequest('/api/v3/orders/stickers?type=png&width=40&height=30', $data, 'post');

		if (isset($result->error) && $result->error === true)
		{
			throw new Exception(__FILE__ . __LINE__ .$result->errorText.print_r($data, true), 500);
		}

		return $result;
	}

	/**
	 *
	 */
	private function sendRequest(
									string	$url,
									?array	$data					= null,
									string	$method					= 'get',
									array	$expected_content_types	= [],
									array	$successResponseCodes	= []	
								): \stdclass|bool
	{
		$response = $this->getResponse($url, $data, $method);
		$message = 'HTTP: '.$response->code.' HEADERS: '.json_encode($response->headers, true).' Response: '.$response->body.' URL: '.$url.' POST: '.json_encode($data);
		Log::add($message, Log::INFO, self::ERROR_CATEGORY);
		$content_type = self::getContentType($response->headers);

		// Если в заголовке ответа нет content-type
		if (!$content_type)
		{
			throw new Exception('Wildberries API does not return header content-type '.$message, 500);
		}

		// Если в заголовке ответа пустой content-type
		if (empty($content_type))
		{
			throw new Exception('Wildberries API does return empty header content-type '.$message, 500);
		}

		if (count($expected_content_types) && !in_array($response->headers['content-type'], $expected_content_types))
		{
			throw new \Exception('Wildberries API does return not expected content-type '.$message, 500);
		}

		if ($response->code == 204)
		{
			$result = true;
		}
		elseif ($response->code == 200)
		{
			$result = json_decode($response->body);
		}
		else
		{
			$result = json_decode($response->body);

			if ($result == false)
			{
				throw new \Exception($result, 500);
			}

			// If not empty array of success response codes and it does not include our response code
			if (count($successResponseCodes) && !in_array($response->code, $successResponseCodes))
			{
				throw new \Exception($response->body, 500);
			}
		}

		return $result;
	}

	/**
	 *
	 */
	private static function getContentType(array $headers): ?string
	{
		if (isset($headers['content-type']))
		{
			return $headers['content-type'][0];
		}

		if (isset($headers['Content-Type']))
		{
			return $headers['Content-Type'][0];
		}

		return null;
	}

	/**
	 *
	 */
	private function getResponse(string $url, ?array $data, $method = 'get'): Response
	{
		$http = HttpFactory::getHttp([], ['curl', 'stream']);
		$curlOptions = [];
		$curl_options[CURLOPT_SSL_VERIFYPEER] = false;
		$curl_options[CURLOPT_TIMEOUT] = 5;
		$http->setOption('transport.curl', $curl_options);
		$post = json_encode($data);
		$headers = [
			'accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => $this->api_key
		];
		$response = null;

		if ($method == 'get')
		{
			$response = $http->get('https://suppliers-api.wildberries.ru'.$url, $headers);
		}
		elseif ($method == 'post')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru'.$url, $post, $headers);
		}
		elseif ($method == 'put')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru'.$url, $post, $headers);
		}
		elseif ($method == 'delete')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru'.$url, $headers);
		}
		elseif ($method == 'patch')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru'.$url, $post, $headers);
		}

		return $response;
	}
}
