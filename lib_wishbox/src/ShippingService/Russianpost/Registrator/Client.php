<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Wishbox\ShippingService\Russianpost\Registrator\Entity\Request\Order;
use Wishbox\ShippingService\Russianpost\Registrator\Entity\Request\RequestInterface;
use Wishbox\ShippingService\Russianpost\Registrator\Entity\Response\EntityResponse;
use Wishbox\ShippingService\Russianpost\Registrator\Exception\RegistratorRequestException;

/**
 * @since 1.0.0
 */
final class Client
{
	/**
	 * $authorisationToken
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private string $authorisationToken;

	/**
	 * $authorisationKey
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	private string $authorisationKey;

	/**
	 * Настройки массив сохранения.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private array $memory;

	/**
	 * Коллбэк сохранения токэна.
	 *
	 * @var callable
	 *
	 * @since 1.0.0
	 */
	private $memorySaveFu;

	/**
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	private int $expire = 0;

	/**
	 * @var GuzzleClient
	 *
	 * @since 1.0.0
	 */
	private GuzzleClient $http;

	/**
	 * Конструктор клиента Guzzle.
	 *
	 * @param   string  $authorisationToken  Логин Account в сервисе Интеграции
	 * @param   string  $authorisationKey    Пароль Secure password в сервисе Интеграции
	 * @param   float   $timeout             Настройка клиента задающая общий тайм-аут запроса в секундах.
	 *                                       При использовании 0 ждать бесконечно долго (поведение по умолчанию)
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $authorisationToken, string $authorisationKey, float $timeout = 5.0)
	{
		$this->http = new GuzzleClient(
			[
				'base_uri' => Constants::API_URL,
				'timeout' => $timeout,
				'http_errors' => false,
			]
		);
		$this->authorisationToken = $authorisationToken;
		$this->authorisationKey = $authorisationKey;
	}

	/**
	 * Выполняет вызов к API.
	 *
	 * @param   string                 $type         Метод запроса
	 * @param   string                 $method       url path запроса
	 * @param   RequestInterface|null  $params       массив данных параметров запроса
	 * @param   boolean                $checkErrors  Check errors
	 *
	 * @return array
	 *
	 * @throws RegistratorRequestException|GuzzleException
	 *
	 * @since 1.0.0
	 */
	private function apiRequest(
		string $type,
		string $method,
		?RequestInterface $params = null,
		bool $checkErrors = true
	): array
	{
		$headers = [];
		$headers['Authorization'] = 'AccessToken ' . $this->authorisationToken;
		$headers['X-User-Authorization'] = 'Basic ' . $this->authorisationKey;
		$headers['Content-Type'] = 'application/json';
		$headers['Accept'] = 'application/json;charset=UTF-8';

		// $headers[] = 'Content-Length: ' . strlen($json);

		if (!empty($params) && is_object($params))
		{
			$params = [$params->prepareRequest()];
		}

		$response = null;

		switch ($type)
		{
			case 'GET':
				$response = $this->http->get($method, ['query' => $params, 'headers' => $headers]);
				break;
			case 'DELETE':
				$response = $this->http->delete($method, ['headers' => $headers]);
				break;
			case 'POST':
				$response = $this->http->post($method, ['json' => $params, 'headers' => $headers]);
				break;
			case 'PUT':
				$response = $this->http->put($method, ['json' => $params, 'headers' => $headers]);
				break;
			case 'PATCH':
				$response = $this->http->patch($method, ['json' => $params, 'headers' => $headers]);
				break;
		}

		$json = $response->getBody()->getContents();
		$apiResponse = json_decode($json, true);

		if ($checkErrors)
		{
			$this->checkErrors($method, $response, $apiResponse);
		}

		return $apiResponse;
	}

	/**
	 * Установить параметр настройки сохранения.
	 *
	 * @param   array|null  $memory  массив настройки сохранения
	 * @param   callable    $fu      колл бэк сохранения
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMemory(?array $memory, callable $fu): self
	{
		$this->memory = $memory;
		$this->memorySaveFu = $fu;

		return $this;
	}

	/**
	 * Проверяет передан ли сохраненный массив данных авторизации.
	 *
	 * @return array|null
	 *
	 * @since 1.0.0
	 */
	private function getMemory(): ?array
	{
		return $this->memory;
	}

	/**
	 * Проверка ответа на ошибки.
	 *
	 * @param   string  $method       Method
	 * @param   mixed   $response     Response
	 * @param   mixed   $apiResponse  Api response
	 *
	 * @return boolean
	 *
	 * @throws RegistratorRequestException
	 *
	 * @since 1.0.0
	 */
	private function checkErrors(string $method, $response, $apiResponse): bool
	{
		if (empty($apiResponse))
		{
			throw new RegistratorRequestException(
				'От API CDEK при вызове метода ' . $method . ' пришел пустой ответ',
				$response->getStatusCode()
			);
		}

		if ($response->getStatusCode() == 400
			&& isset($apiResponse['errors'][0]))
		{
			$errorCodes = $apiResponse['errors'][0]['error-codes'];

			throw new RegistratorRequestException(
				'От API CDEK при вызове метода ' . $method . ' получена ошибка: ' . $message,
				$response->getStatusCode(),
				null,
				$apiResponse['requests'][0]['errors'][0]['code'],
				$apiResponse['requests'][0]['errors'][0]['message']
			);
		}

		if ($response->getStatusCode() == 200 && isset($apiResponse['errors'])
			|| isset($apiResponse['state']) && $apiResponse['state'] == 'INVALID'
			|| $response->getStatusCode() !== 200 && isset($apiResponse['errors']))
		{
			$message = RegistratorRequestException::getTranslation(
				$apiResponse['errors'][0]['code'],
				$apiResponse['errors'][0]['message']
			);
			$e = new RegistratorRequestException(
				'От API CDEK при вызове метода ' . $method . ' получена ошибка: ' . $message,
				$response->getStatusCode()
			);

			throw $e;
		}

		if ($response->getStatusCode() > 202 && !isset($apiResponse['requests'][0]['errors']))
		{
			throw new RegistratorRequestException(
				'Неверный код ответа от сервера CDEK при вызове метода'
				. $method . ': ' . $response->getStatusCode(),
				$response->getStatusCode()
			);
		}

		return false;
	}

	/**
	 * Создание заказа.
	 *
	 * @param   Order  $order  - Параметры заказа
	 *
	 * @return EntityResponse
	 *
	 * @throws RegistratorRequestException|GuzzleException
	 *
	 * @since 1.0.0
	 */
	public function createOrder(Order $order): EntityResponse
	{
		return new EntityResponse(
			$this->apiRequest(
				'POST',
				'user/backlog',
				$order
			)
		);
	}
}
