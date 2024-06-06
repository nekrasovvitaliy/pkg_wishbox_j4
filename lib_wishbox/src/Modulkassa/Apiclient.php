<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Modulkassa;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Http\Response;
use Joomla\CMS\Log\Log;
use stdClass;
use Wishbox\Modulkassa\Enum\DocumentType;
use Wishbox\Modulkassa\Enum\PaymentType;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Apiclient
{
	/**
	 * @var string $login Login
	 *
	 * @since 1.0.0
	 */
	protected string $login;

	/**
	 * @var string $password Password
	 *
	 * @since 1.0.0
	 */
	protected string $password;

	/**
	 * @var boolean $testMode Test mode
	 *
	 * @since 1.0.0
	 */
	protected bool $testMode;

	/**
	 * @var string $retailPointId Retail Point Id
	 *
	 * @since 1.0.0
	 */
	protected string $retailPointId;

	/**
	 * @since 1.0.0
	 */
	protected const ERROR_CATEGORY = 'com_jshopping.addon_wishboxmodulkassa.modulkassa';

	/**
	 * @param   string  $login         Login
	 * @param   string  $password      Password
	 * @param   string  $retailPointId Repail point id
	 * @param   boolean $testMode      Test mode
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $login, string $password, string $retailPointId, bool $testMode)
	{
		$this->login = $login;
		$this->password = $password;
		$this->retailPointId = $retailPointId;
		$this->testMode = $testMode;
		Log::addLogger(
			['text_file' => self::ERROR_CATEGORY . '.log.php'],
			Log::ALL,
			[self::ERROR_CATEGORY]
		);
	}

	/**
	 * @return ?stdclass
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAuthorisation(): ?stdclass
	{
		return $this->sendRequest(
			'/api/fn/v1/associate/' . $this->retailPointId,
			[],
			'post',
			[
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
				CURLOPT_USERPWD => $this->login . ':' . $this->password,
				CURLOPT_TIMEOUT => 30,
			]
		);
	}

	/**
	 * Send
	 * @param   DocumentType $docType      Doc type
	 * @param   array        $orders       Orders
	 * @param   PaymentType  $paymentType  Payment type
	 * @param   bool         $printReceipt Print receipt
	 *
	 * @return ?stdclass[]
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function send(DocumentType $docType, array $orders, PaymentType $paymentType, bool $printReceipt = true): ?array
	{
		if (count($orders) == 0)
		{
			throw new InvalidArgumentException('$orders param must not be empty', 500);
		}

		$results = [];

		foreach ($orders as $order)
		{
			$pp = 0;
			$productsLogsis = [];

			foreach ($order->items as $orderItem)
			{
				$productsLogsis[$pp]['articul'] = $orderItem->get('product_ean');
				$productsLogsis[$pp]['artname'] = $orderItem->get('product_name');
				$productsLogsis[$pp]['count']   = $orderItem->get('product_quantity');
				$productsLogsis[$pp]['weight']  = $orderItem->get('weight');
				$productsLogsis[$pp]['price']   = $orderItem->get('product_item_price');
				$productsLogsis[$pp]['nds']     = 2;
				$pp++;
			}

			$orderDate  = strtotime($order->get('order_date'));
			$paymentMethodId = $order->get('payment_method_id');

			if ($paymentType == '')
			{
				if ($paymentMethodId == 2)
				{
					$paymentType = PaymentType::CASH;
				}
				else
				{
					$paymentType = PaymentType::CARD;
				}
			}

			$inventPositions = [];
			$moneyPositions  = [];

			for ($i = 0; $i < count($productsLogsis); $i++)
			{
				$row = $productsLogsis[$i];
				$inventPositions[] = [
					'barcode'		=> $row['articul'],
					'name'			=> $row['artname'],
					'price'			=> $row['price'],
					'discSum'		=> 0,
					'quantity'		=> $row['count'],
					'vatTag'		=> 1105,
					'paymentObject' => 'commodity',
					'paymentMethod' => 'full_payment',
				];
				$moneyPositions[] = [
					'paymentType' => $paymentType,
					'sum' => $row['price'] * $row['count'],
				];
			}

			if ($order->order_shipping > 0) // phpcs:ignore
			{
				$inventPositions[] = [
					'barcode'		=> 'ДОСТАВКА',
					'name'			=> 'Доставка',
					'price'			=> $order->order_shipping, // phpcs:ignore
					'discSum'		=> 0,
					'quantity'		=> 1,
					'vatTag'		=> 1105,
					'paymentObject' => 'service',
					'paymentMethod' => 'full_payment',
				];
				$moneyPositions[] = [
					'paymentType'	=> $paymentType,
					'sum'			=> $order->order_shipping, // phpcs:ignore
				];
			}

			$postFields = [
				'docNum'			=> $order->order_number, // phpcs:ignore
				'docType'			=> $docType,
				'checkoutDateTime'	=> date('c', $orderDate),
				'email'				=> $order->email, // адрес почты или телефон покупателя
				'printReceipt'		=> $printReceipt,
				'id'				=> microtime(true) . "",
				'taxMode'			=> 'SIMPLIFIED_WITH_EXPENSE',
				'inventPositions'	=> $inventPositions,
				'moneyPositions'	=> $moneyPositions,
			];
			$authorisation = $this->getAuthorisation();
			// phpcs:ignore
			$results[$order->order_id] = $this->sendRequest(
				'/api/fn/v1/doc',
				$postFields,
				'post',
				[
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
					CURLOPT_USERPWD => $authorisation->userName . ':' . $authorisation->password,
					CURLOPT_TIMEOUT => 30,
				]
			);
		}

		return $results;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function getUrl(): string
	{
		return $this->testMode ? 'demo.modulpos.ru' : 'my.modulkassa.ru';
	}

	/**
	 * @param   string $url            Url
	 * @param   array  $data           Data
	 * @param   string $method         Method
	 * @param   array  $addCurlOptions Additional CURL options
	 *
	 * @return ?stdClass
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function sendRequest(string $url, array $data, string $method = 'post', array $addCurlOptions = []): ?stdClass
	{
		$response = $this->getResponse($url, $data, $method, $addCurlOptions);

		// $result = null;
		$message = 'HTTP: ' . $response->code
			. ' HEADERS: ' . json_encode($response->headers, true)
			. ' Response: ' . $response->body
			. ' URL: ' . $url . ' POST: ' . json_encode($data);
		Log::add($message, Log::INFO, self::ERROR_CATEGORY);

		switch ($response->headers['Content-Type'])
		{
			case 'application/json;charset=UTF-8' :
			case 'application/json' :
				$result = json_decode($response->body);

				if ($result === null)
				{
					throw new Exception('API return not JSON response: ' . $response->body);
				}

				if ($response->code != 200)
				{
					throw new Exception('API return HTTP no 200 ' . $message);
				}
				break;
			default :
				if (!isset($response->headers['Content-Type']))
				{
					throw new Exception('API return headers without "Content-Type" ' . $message, 500);
				}
				else
				{
					throw new Exception('API return header with unknown "Content-Type" ' . $message, 500);
				}
		}

		return $result;
	}

	/**
	 * @param   string $url            URL
	 * @param   array  $data           Data
	 * @param   string $method         Method
	 * @param   array  $addCurlOptions Additional CURL options
	 *
	 * @return Response
	 *
	 * @since 1.0.0
	 */
	private function getResponse(string $url, array $data, string $method = 'post', array $addCurlOptions = []): Response
	{
		$http = HttpFactory::getHttp(null, ['curl', 'stream']);
		$curlOptions = [];
		$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
		$curlOptions[CURLOPT_TIMEOUT] = 5;

		foreach ($addCurlOptions as $key => $addCurlOption)
		{
			$curlOptions[$key] = $addCurlOption;
		}

		$http->setOption('transport.curl', $curlOptions);
		$headers = [];
		$post = '';

		if (count($data))
		{
			$post = json_encode($data);
			$headers['Content-Type'] = 'application/json';
		}

		$url = 'https://' . $this->getUrl() . $url;

		return $http->$method($url, $post, $headers);
	}
}
