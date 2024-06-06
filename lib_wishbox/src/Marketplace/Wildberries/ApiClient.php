<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Marketplace\Wildberries;

use Exception;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Log\Log;
use stdClass;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class ApiClient
{
	/**
	 * @var string $apiKey API key
	 *
	 * @since 1.0.0
	 */
	private string $apiKey;

	/**
	 * @since 1.0.0
	 */
	protected const ERROR_CATEGORY = 'com_jshopping.addon_wishboxwildberries.wildberries';

	/**
	 * @param   string  $apiKey  API key
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $apiKey)
	{
		$this->apiKey = $apiKey;
		Log::addLogger(
			['text_file' => self::ERROR_CATEGORY . '.log.php'],
			Log::ALL,
			[self::ERROR_CATEGORY]
		);
	}

	/**
	 * @param   array  $data  Data
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function ship(array $data): stdClass|bool
	{
		throw new Exception('supply_id param must not be empty', 500);

		if ($result = $this->sendRequest('/api/v2/orders', $data, 'put'))
		{
			return $result;
		}

		return false;
	}

	/**
	 * @param   array  $params  Params
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getSupplies(array $params): bool|stdClass
	{
		if ($result = $this->sendRequest('/api/v3/supplies?' . http_build_query($params)))
		{
			return $result;
		}

		return false;
	}

	/**
	 * @param   array  $params  Params
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function createSupply(array $params): bool|stdClass
	{
		if ($result = $this->sendRequest('/api/v3/supplies', $params, 'post'))
		{
			return $result;
		}

		return false;
	}

	/**
	 * @param   array  $params  Params
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function deleteSupply(array $params): bool|stdClass
	{
		if ($result = $this->sendRequest('/api/v3/supplies/' . $params['id'], [], 'delete'))
		{
			return $result;
		}

		return false;
	}

	/**
	 * @param   string  $supplyId  Supply id
	 * @param   string  $orderId   Order id
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function supplyAddOrder(string $supplyId, string $orderId): bool|stdClass
	{
		if ($result = $this->sendRequest(
			'/api/v3/supplies/' . $supplyId . '/orders/' . $orderId,
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
	 * @param   array    $params       Params
	 * @param   integer  $warehouseId  Warehouse id
	 *
	 * @return array|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getStocks(array $params, int $warehouseId): array|bool
	{
		if ($warehouseId <= 0)
		{
			throw new Exception('warehouse_id param must be more than zero', 500);
		}

		if ($result = $this->sendRequest('/api/v3/stocks/' . $warehouseId, $params, 'post'))
		{
			return $result->stocks;
		}

		return false;
	}

	/**
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getCards(): bool|stdClass
	{
		$params = [
			'sort' => [
				'cursor' => [
					'limit' => 1000
				],
				'filter' => [
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
	 * @param   array     $data         Data
	 * @param   integer   $warehouseId  Warehouse id
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function updateStocks(array $data, int $warehouseId): stdclass|bool
	{
		if ($warehouseId <= 0)
		{
			throw new Exception('warehouse_id param must be more than zero', 500);
		}

		$result = $this->sendRequest(
			'/api/v3/stocks/' . $warehouseId,
			$data,
			'put',
			[],
			[204]
		);

		if ($result !== true)
		{
			throw new Exception('sendRequest return false', 500);
		}

		return $result;
	}

	/**
	 * @param   array  $data  Data
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function updatePrices(array $data): bool|stdClass
	{
		if ($result = $this->sendRequest('/public/api/v1/prices', $data, 'post'))
		{
			return $result;
		}

		return true;
	}

	/**
	 * @param   array  $params  Params
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getOrders(array $params): stdclass|bool
	{
		if ($result = $this->sendRequest('/api/v3/orders?' . http_build_query($params)))
		{
			if (!isset($result->error))
			{
				return $result;
			}
		}

		return false;
	}

	/**
	 * @param   array  $params Params
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getOrdersStatus(array $params): stdclass|bool
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
	 * @param   array  $data  Data
	 *
	 * @return boolean|stdClass
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getOrdersStickersPng(array $data): bool|stdClass
	{
		$result = $this->sendRequest('/api/v3/orders/stickers?type=png&width=40&height=30', $data, 'post');

		if (isset($result->error) && $result->error === true)
		{
			throw new Exception(__FILE__ . __LINE__ . $result->errorText . print_r($data, true), 500);
		}

		return $result;
	}

	/**
	 * @param   string      $url                   URL
	 * @param   array|null  $data                  Data
	 * @param   string      $method                Method
	 * @param   array       $expectedContentTypes  Expected content types
	 * @param   array       $successResponseCodes  Success response codes
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function sendRequest(
		string	$url,
		?array	$data					= null,
		string	$method					= 'get',
		array	$expectedContentTypes	= [],
		array	$successResponseCodes	= []
	): stdclass|bool
	{
		$response = $this->getResponse($url, $data, $method);
		$message = 'HTTP: ' . $response->code
			. ' HEADERS: ' . json_encode($response->headers, true)
			. ' Response: ' . $response->body
			. ' URL: ' . $url
			. ' POST: ' . json_encode($data);
		Log::add($message, Log::INFO, self::ERROR_CATEGORY);
		$contentType = self::getContentType($response->headers);

		// Если в заголовке ответа нет content-type
		if (!$contentType)
		{
			throw new Exception('Wildberries API does not return header content-type ' . $message, 500);
		}

		// Если в заголовке ответа пустой content-type
		if (empty($contentType))
		{
			throw new Exception('Wildberries API does return empty header content-type ' . $message, 500);
		}

		if (count($expectedContentTypes) && !in_array($response->headers['content-type'], $expectedContentTypes))
		{
			throw new Exception('Wildberries API does return not expected content-type ' . $message, 500);
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

			if (!$result)
			{
				throw new Exception($result, 500);
			}

			// If not empty array of success response codes, and it does not include our response code
			if (count($successResponseCodes) && !in_array($response->code, $successResponseCodes))
			{
				throw new Exception($response->body, 500);
			}
		}

		return $result;
	}

	/**
	 * @param   array  $headers  Headers
	 *
	 * @return string|null
	 *
	 * @since 1.0.0
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
	 * @param   string      $url     URL
	 * @param   array|null  $data    Data
	 * @param   string      $method  Method
	 *
	 * @return Response
	 *
	 * @since 1.0.0
	 */
	private function getResponse(string $url, ?array $data, string $method = 'get'): Response
	{
		$http = HttpFactory::getHttp([], ['curl', 'stream']);
		$curlOptions = [];
		$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
		$curlOptions[CURLOPT_TIMEOUT] = 5;
		$http->setOption('transport.curl', $curlOptions);
		$post = json_encode($data);
		$headers = [
			'accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => $this->apiKey
		];
		$response = null;

		if ($method == 'get')
		{
			$response = $http->get('https://suppliers-api.wildberries.ru' . $url, $headers);
		}
		elseif ($method == 'post')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru' . $url, $post, $headers);
		}
		elseif ($method == 'put')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru' . $url, $post, $headers);
		}
		elseif ($method == 'delete')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru' . $url, $headers);
		}
		elseif ($method == 'patch')
		{
			$response = $http->$method('https://suppliers-api.wildberries.ru' . $url, $post, $headers);
		}

		return $response;
	}
}
