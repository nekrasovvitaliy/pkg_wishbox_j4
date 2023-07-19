<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Shippingservice\Russianpost;

use AddressNormalizationException;
use InvalidArgumentException;
use RuntimeException;

/**
 * @property string $title
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class ApiClient
{
	/**
	 * @var string $authorisationToken Authorisation token
	 * @since 1.0.0
	 */
	private string $authorisationToken;

	/**
	 * @var string $authorisationKey Authorisation key
	 * @since 1.0.0
	 */
	private string $authorisationKey;

	/**
	 * @var boolean $debugMode Debug mode
	 * @since 1.0.0
	 */
	private bool $debugMode;

	/**
	 * @var string $url URL of Russian Post API
	 * @since 1.0.0
	 */
	private string $url = 'https://otpravka-api.pochta.ru';

	/**
	 * @var integer $apiTimeout API timeout
	 * @since 1.0.0
	 */
	private int $apiTimeout;

	/**
	 * @param   string $authorisationToken Authorisation token
	 * @param   string $authorisationKey   Authorisation key
	 * @param   bool   $debugMode          Debug mode
	 * @since 1.0.0
	 */
	public function __construct(string $authorisationToken, string $authorisationKey, bool $debugMode)
	{
		if (empty($authorisationToken))
		{
			throw new InvalidArgumentException('Empty authorisationToken', 500);
		}

		if (empty($authorisationKey))
		{
			throw new InvalidArgumentException('Empty authorisationKey', 500);
		}

		$this->authorisationToken = $authorisationToken;
		$this->authorisationKey = $authorisationKey;
		$this->debugMode = $debugMode;
		$this->apiTimeout = 5;
	}

	/**
	 * @param   string $address Address
	 * @return integer
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function getIndex(string $address): int
	{
		echo $address;
		echo '<br />';
		$data = [];

		// URL АПИ Почты России
		$url = $this->url . '/1.0/clean/address';
		$data['id'] = 'adr 1';
		$data['original-address'] = $address;
		$post = '[' . json_encode($data) . ']';
		echo $post;
		echo '<br />';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json;charset=UTF-8',
			'Authorization: AccessToken ' . $this->authorisationToken,
			'X-User-Authorization: Basic ' . $this->authorisationKey
		];
		echo '<pre>';
		print_r($headers);
		echo '<pre>';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->apiTimeout);

		// Получаем ответ от АПИ Почты России
		$resultString = curl_exec($ch);
		curl_close($ch);

		if (empty($resultString))
		{
			throw new RuntimeException('_MESSAGE_API_RETURN_EMPTY_RESPONSE', 500);
		}

		$resultArray = json_decode($resultString, true);

		if (!is_array($resultArray))
		{
			throw new RuntimeException('Не получилось преобразовать ответ сервера в архив', 500);
		}

		$resultArray = $resultArray[0];

		if (!in_array($resultArray['quality-code'], ['GOOD', 'POSTAL_BOX', 'ON_DEMAND', 'UNDEF_05'])
			|| !in_array(
				$resultArray['validation-code'],
				['VALIDATED', 'OVERRIDDEN', 'CONFIRMED_MANUALLY', 'NOT_VALIDATED_HAS_AMBI']
			)
		)
		{
			throw new AddressNormalizationException(constant($this->title) . ': Адрес неопределён', 500);
		}

		// Print_r($result_array);
		// die;
		return $resultArray['index'];
	}

	/**
	 * @param   string $query Query
	 * @return array $array
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function shipmentSearch(string $query): array
	{
		if (empty($query))
		{
			throw new InvalidArgumentException('Empty query', 500);
		}

		$url = $this->url . '/1.0/shipment/search?query=' . ($query);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json;charset=UTF-8',
			'Authorization: AccessToken ' . $this->authorisationToken,
			'X-User-Authorization: Basic ' . $this->authorisationKey
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->apiTimeout);
		$resultString = curl_exec($ch);
		curl_close($ch);

		if (empty($resultString))
		{
			$resultString = '[]';

			// Throw new \Exception('MESSAGE_API_RETURN_EMPTY_RESPONSE', 500);
		}

		$resultArray = json_decode($resultString, true);

		if (!is_array($resultArray))
		{
			throw new RuntimeException('Не получилось преобразовать ответ сервера в архив', 500);
		}

		return $resultArray;
	}

	/**
	 * @param   string $query Query
	 * @return array
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function backlogSearch(string $query): array
	{
		if (empty($query))
		{
			throw new InvalidArgumentException('Empty query', 500);
		}

		$url = $this->url . '/1.0/backlog/search?query=' . ($query);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json;charset=UTF-8',
			'Authorization: AccessToken ' . $this->authorisationToken,
			'X-User-Authorization: Basic ' . $this->authorisationKey
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->apiTimeout);

		// Получаем ответ от АПИ Почты России
		$resultString = curl_exec($ch);
		curl_close($ch);

		if (empty($resultString))
		{
			$resultString = '[]';

			// Throw new \Exception('MESSAGE_API_RETURN_EMPTY_RESPONSE', 500);
		}

		$resultArray = json_decode($resultString, true);

		if (!is_array($resultArray))
		{
			throw new RuntimeException('Не получилось преобразовать ответ сервера в архив', 500);
		}

		return $resultArray;
	}

	/**
	 * @param   int $id Id
	 * @return array
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function backlog(int $id): array
	{
		if (!$id)
		{
			throw new InvalidArgumentException('Empty id', 500);
		}

		$url = $this->url . '/1.0/backlog/' . $id;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json;charset=UTF-8',
			'Authorization: AccessToken ' . $this->authorisationToken,
			'X-User-Authorization: Basic ' . $this->authorisationKey
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->apiTimeout);
		$resultString = curl_exec($ch);
		curl_close($ch);

		if (empty($resultString))
		{
			$resultString = '[]';

			// Throw new \Exception('MESSAGE_API_RETURN_EMPTY_RESPONSE', 500);
		}

		$resultArray = json_decode($resultString, true);

		if (!is_array($resultArray))
		{
			throw new RuntimeException('Не получилось преобразовать ответ сервера в массив', 500);
		}

		return $resultArray;
	}

	/**
	 * @param   string $lastname       Last name
	 * @param   string $firstname      First name
	 * @param   string $patronymic     Patronymic
	 * @param   int    $indexTo        indexTo
	 * @param   string $mailType       mailType
	 * @param   int    $weight         $weight
	 * @param   string $orderNum       orderNum
	 * @param   string $placeTo        placeTo
	 * @param   string $postofficeCode postofficeCode
	 * @param   string $regionTo       regionTo
	 * @param   string $streetTo       streetTo
	 * @param   int    $telAddress     telAddress
	 * @return ?int Order id
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function userBacklog(
		string  $lastname,
		string  $firstname,
		string  $patronymic,
		int     $indexTo,
		string  $mailType,
		int     $weight,
		string  $orderNum,
		string  $placeTo,
		string  $postofficeCode,
		string  $regionTo,
		string  $streetTo,
		int     $telAddress
	): ?int {
		$params = [];

		/*
		// Тип адреса
		// https://otpravka.pochta.ru/specification#/enums-base-address-type
		// DEFAULT  Стандартный (улица, дом, квартира)
		// PO_BOX   Абонентский ящик
		// DEMAND   До востребования
		*/
		$params['address-type-to'] = 'DEFAULT';

		/*
		// (Опционально) Район
		// $params['area-to'] = '';
		// (Опционально) Часть здания: Строение
		// $params['building-to'] = '';
		// comment - Строка (Опционально)
		// Комментарий:Номер заказа. Внешний идентификатор заказа, который формируется отправителем
		// $params['comment'] = '';
		// Строка (Опционально)
		// $params['corpus-to'] = '';
		// Логические: true или false (Опционально)
		// $params['courier'] = 0;
		// Декларация (Опционально)
		$params['customs-declaration'] = [
			'currency' => 'RUB',
			'customs-entries' => [
				'amount' => '0',
				'country-code' => '0',
				'description' => 'string',
				'tnved-code' => 'string',
				'value' => '0',
				'weight' => '0'
			],
			'entries-type' => 'GIFT'
		];

		if (!empty($response->order['width'])
			&& !empty($response->order['height']) && !empty($response->order['length']))
		{
			// Размеры (Опционально)
			$params['dimension'] = array();
			$params['dimension']['height'] = $response->order['height'];
			$params['dimension']['length'] = $response->order['length'];
			$params['dimension']['width'] = $response->order['width'];
		}

		// Строка (Опционально) Тип конверта - ГОСТ Р 51506-99.
		// $params['envelope-type'] = "C4";

		// Логические: true или false
		// Отметка Хрупкое
		// $params['fragile'] = 0;

		*/
		// ФИО получателя
		$params['given-name'] = implode(
			' ',
			array_diff(
				[
													$lastname,
													$firstname,
													$patronymic
												],
				['']
			)
		);

		/*
		 (Опционально) (Строка) Название гостиницы
		// $params['hotel-to'] = 'string';
		// Часть адреса: Номер здания
		// $params['house-to'] = '';
		*/
		// Целое число (Опционально)
		$params['index-to'] = $indexTo;

		// Целое число (Опционально) Сумма объявленной ценности (копейки)
		$params['insr-value'] = 0;

		// Строка (Опционально) Часть здания: Литера
		// $params['letter-to'] = '';

		// Строка (Опционально) Микрорайон
		// $params['location-to'] = '';

		// Категория РПО. См. Категория РПО
		// https://otpravka.pochta.ru/specification#/enums-base-mail-category
		$params['mail-category'] = 'ORDINARY';

		// Код страны
		$params['mail-direct'] = '643';

		// Вид РПО. См. Вид РПО
		$params['mail-type'] = $mailType;

		// Логические: true или false Отметка 'Ручной ввод адреса'

		$params['manual-address-input'] = false;
		$params['mass'] = $weight;

		// Отчество получателя
		$params['middle-name'] = $patronymic;

		/*
		// Строка (Опционально) Номер для а/я, войсковая часть, войсковая часть ЮЯ, полевая почта
		// $params['num-address-type-to'] = '';
		*/
		// Номер заказа. Внешний идентификатор заказа, который формируется отправителем
		$params['order-num'] = $orderNum;

		// Целое число (Опционально) Сумма наложенного платежа (копейки)
		$params['payment'] = 0;

		/*
		// Строка (Опционально) Способ оплаты. См. Способ оплаты
		// https://otpravka.pochta.ru/specification#/enums-payment-methods
		// $params['payment-method'] = "CASHLESS";
		*/
		// Населенный пункт
		$params['place-to'] = $placeTo;

		// Строка (Опционально) Индекс места приема
		$params['postoffice-code'] = $postofficeCode;

		// Строка Наименование получателя одной строкой (ФИО, наименование организации)
		$params['recipient-name'] = implode(
			' ',
			array_diff(
				[
					$lastname,
					$firstname,
					$patronymic
				],
				['']
			)
		);

		// Область, регион
		$params['region-to'] = $regionTo;

		/*
		// Строка (Опционально) Часть здания: Номер помещения
		// $params['room-to'] = '';
		// Строка (Опционально) Часть здания: Дробь
		// $params['slash-to'] = '';
		// Целое число (Опционально) Признак услуги SMS уведомления
		*/
		$params['sms-notice-recipient'] = 0;

		/*
		// Почтовый индекс (буквенно-цифровой)
		// $params['str-index-to'] = '';
		// Часть адреса: Улица
		*/
		$params['street-to'] = $streetTo;

		// Фамилия получателя
		$params['surname'] = $lastname;

		// Целое число (Опционально) Телефон получателя (может быть обязательным для некоторых типов отправлений)
		$params['tel-address'] = $telAddress;

		/*
		// Логические: true или false (Опционально) Отметка 'С заказным уведомлением'
		// $params['with-order-of-notice'] = false;
		// Логические: true или false (Опционально) Отметка 'С простым уведомлением'
		// $params['with-simple-notice'] = false;
		*/
		// Логические: true или false (Опционально) Отметка 'Без разряда'
		$params['wo-mail-rank'] = true;

		// URL АПИ Почты России для рассчёта стоимости
		$url = 'https://otpravka-api.pochta.ru/1.0/user/backlog';
		$json = '[' . json_encode($params) . ']';
		$headers = [];
		$headers[] = 'Authorization: AccessToken ' . $this->authorisationToken;
		$headers[] = 'X-User-Authorization: Basic ' . $this->authorisationKey;
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json;charset=UTF-8';
		$headers[] = 'Content-Length: ' . strlen($json);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->apiTimeout);
		$resultString = curl_exec($ch);
		$resultArray = json_decode($resultString, true);

		if (isset($resultArray['errors']))
		{
			throw new RuntimeException($resultArray['errors'][0]['error-codes'][0]['description'], 500);
		}

		return $resultArray['result-ids'][0];
	}
}
