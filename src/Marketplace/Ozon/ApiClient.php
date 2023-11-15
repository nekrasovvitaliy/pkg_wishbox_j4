<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Marketplace\Ozon;

use Joomla\CMS\Log\Log;
use Wishbox\Marketplace\Ozon\Exception\ProductStockImportException;

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
	private int $client_id;

	/**
	 *
	 */
	private string $api_key;

	/**
	 *
	 */
	private const ERROR_CATEGORY = 'com_jshopping.addon_wishboxozon.ozon';

	/**
	 *
	 */
	public function __construct(int $client_id, string $api_key)
	{
		$this->client_id = $client_id;
		$this->api_key = $api_key;
		Log::addLogger (
			['text_file' => self::ERROR_CATEGORY.'.log.php'],
			Log::ALL,
			[self::ERROR_CATEGORY]
		);
	}

	/**
	 *
	 */
	public function updateStocks(array $data)
	{
		$result = $this->sendRequest('/v1/product/import/stocks', $data);

		if (!count($result->result))
		{
			throw new \Exception('No count result', 500);
		}

		foreach ($result->result as $item)
		{
			if ($item->updated === false)
			{
				foreach ($item->errors as $error)
				{
					$exception = new ProductStockImportException($error->code.' '.json_encode($item), 500);
					$exception->ozon_product_id = $item->product_id;
					throw $exception;
				}
			}
		}
	}

	/**
	 *
	 */
	public function updatePrices(array $data)
	{
		$result = $this->sendRequest('/v1/product/import/prices', $data);

		if (!count($result->result))
		{
			throw new \Exception('No count result', 500);
		}

		foreach ($result->result as $item)
		{
			if ($item->updated === false)
			{
				foreach ($item->errors as $error)
				{
					$exception = new ProductPriceImportException($error->code.' '.json_encode($item), 500);
					$exception->ozon_product_id = $item->product_id;
					throw $exception;
				}
			}
		}
	}

	/**
	 *
	 */
	public function getProductStocks(array $data)
	{
		if ($result = $this->sendRequest('/v3/product/info/stocks', $data))
		{
			if (is_object($result))
			{
				return $result->result;
			}
		}

		return true;
	}

	/**
	 *
	 */
	public function getProductPrices(array $data)
	{
		if ($result = $this->sendRequest('/v4/product/info/prices', $data))
		{
			if (is_object($result))
			{
				return $result->result;
			}
		}

		return true;
	}

	/**
	 *
	 */
	public function getProductInfo(array $data)
	{
		$result = $this->sendRequest('/v2/product/info', $data);
		print_r($result);
		die;
	}

	/**
	 *
	 */
	public function getProductList(array $data): object
	{
		$result = $this->sendRequest('/v2/product/list', $data);

		return $result->result;
	}
	
	/**
	 *
	 */
	public function importProducts(array $data)
	{
		if ($this->sendRequest('/v2/product/import', $data));
		{
			return true;
		}

		return true;
	}
	
	/**
	 *
	 */
	public function getPosting(array $data)
	{
		if ($result = $this->sendRequest('/v3/posting/fbs/get', $data))
		{
			return $result->result;
		}

		return false;
	}
	
	/**
	 *
	 */
	public function getOrders(array $data): array
	{
		if ($result = $this->sendRequest('/v3/posting/fbs/list', $data))
		{
			return $result->result->postings;
		}

		return false;
	}

	/**
	 *
	 */
	public function ship(array $data)
	{
		$result = $this->sendRequest('/v3/posting/fbs/ship', $data);

		if ($result)
		{
			return $result->result;
		}

		return false;
	}

	/**
	 *
	 */
	public function packageLabel(array $data)
	{
		if (!count($data))
		{
			throw new \InalidArgumentException(__CLASS__ .'::'. __METHOD__ .' Argument array $data is empty', 500);
		}

		if (!count($data))
		{
			foreach ($data as $key => $value)
			{
				$value = trim($value);

				if (empty($value))
				{
					throw new \InalidArgumentException(__CLASS__ .'::'. __METHOD__ .' Argument array $data containts empty element', 500);
				}
			}
		}

		$result = $this->sendRequest('/v2/posting/fbs/package-label', $data);

		return $result;
	}
	
	/**
	 *
	 */
	private function sendRequest(string $url, array $data)
	{
		$response = $this->getResponse($url, $data);
		$result = null;
		$message = 'HTTP: '.$response->code
					.' HEADERS: '.json_encode($response->headers, true)
					.' Response: '.$response->body
					.' URL: '.$url.' POST: '.json_encode($data);
		Log::add($message, Log::INFO, self::ERROR_CATEGORY);
		$headers = [];
		foreach($response->headers as $name => $value) {
			$headers[mb_strtolower($name)] = $value;
		}
		if (is_array($headers['content-type']) && count($headers['content-type'])) {
			$content_type = $headers['content-type'][0];
			switch ($content_type) {
				case 'application/json' :
					$result = json_decode($response->body);
					if ($result === null) {
						throw new \Exception('Ozon API return not JSON response: '.$response->body);
						die;
					}

					if ($response->code != 200)
					{
						throw new \Exception(print_r($data, true) . $response->body, 500);
						die;
					}

					break;
				case 'application/pdf' :
					$result = $response->body;
					break;
				default :
					throw new \Exception('Ozon API return header with unknown "Content-Type" '.$message, 500);
			}
		} else {// иначе если в заголовках нет "Content-Type"
			throw new \Exception('Ozon API return headers without "Content-Type" '.$message, 500);
		}
		return $result;
	}
	
	
	/**
	 *
	 */
	private function getResponse(string $url, array $data, $method = 'post'): \Joomla\CMS\Http\Response
	{
		$http = \JHttpFactory::getHttp([], ['curl', 'stream']);
		$curlOptions = [];
		$curl_options[CURLOPT_SSL_VERIFYPEER] = false;
		$curl_options[CURLOPT_TIMEOUT] = 5;
		$http->setOption('transport.curl', $curl_options);
		$post = json_encode($data);
		$headers = [
			'Content-Type' => 'application/json',
			'Client-Id' => $this->client_id,
			'Api-Key' => $this->api_key
		];
		$response = $http->post('https://api-seller.ozon.ru'.$url, $post, $headers);

		return $response;
	}
}