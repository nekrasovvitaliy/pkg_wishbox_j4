<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost;

use InvalidArgumentException;
use SoapClient;
use SoapFault;
use stdclass;

/**
 * @since 1.0.0
 * @noinspection PhpUnused
 */
class TrackingApiClient
{
	/**
	 * @var string $login
	 * @since 1.0.0
	 */
	private string $login;

	/**
	 * @var string $password
	 * @since 1.0.0
	 */
	private string $password;

	/**
	 * @var boolean $debugMode
	 * @since 1.0.0
	 */
	private bool $debugMode;

	/**
	 * URL АПИ Почты России
	 * @var string $url
	 * @since 1.0.0
	 */
	private string $url = 'https://tracking.russianpost.ru/fc?wsdl';

	/**
	 * @var integer $apiTimeout API timeout
	 * @since 1.0.0
	 */
	private int $apiTimeout;

	/**
	 * @var SoapClient $client
	 * @since 1.0.0
	 */
	private SoapClient $client;

	/**
	 * Constructor
	 * @param   string $login     Logimn
	 * @param   string $password  Password
	 * @param   bool   $debugMode Debug mode
	 * @throws SoapFault
	 * @since 1.0.0
	 */
	public function __construct(string $login, string $password, bool $debugMode)
	{
		if (empty($login))
		{
			throw new InvalidArgumentException('Empty login', 500);
		}

		if (empty($password))
		{
			throw new InvalidArgumentException('Empty password', 500);
		}

		$this->login = $login;
		$this->password = $password;
		$this->debugMode = $debugMode;
		$this->apiTimeout = 5;
		$this->client = new SoapClient($this->url, ['trace' => 1, 'soap_version' => SOAP_1_1]);
	}

	/**
	 * @param   array $barcodes Array of barcodes
	 * @return stdclass
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function getTicket(array $barcodes): stdclass
	{
		if (!count($barcodes))
		{
			throw new InvalidArgumentException('array $barcodes param must not be empty', 500);
		}

		$requestParams = new stdclass;
		$requestParams->request = new stdclass;
		$requestParams->request->Item = [];

		foreach ($barcodes as $barcode)
		{
			$item = new stdclass;
			$item->Barcode = $barcode; // phpcs:ignore
			$requestParams->request->Item[] = $item;
		}

		$requestParams->login = $this->login;
		$requestParams->password = $this->password;

		return $this->client->getTicket($requestParams);
	}

	/**
	 * @param   string $ticketNumber Ticket number
	 * @return array
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function getOperationsByTicket(string $ticketNumber): array
	{
		$statusList = new StatusList;

		// Получаем ответ
		$response = $this->getResponseByTicket($ticketNumber);

		if (!empty($response->error) || empty($response->value))
		{
			throw new TrackingException('Ответ по тикету ' . $ticketNumber . ' еще не готов.');
		}

		// Получаем результат
		$result = !is_array($response->value->Item) ? [$response->value->Item] : $response->value->Item;

		// Проставляем название подстатуса из справочника
		foreach ($result as $key => &$item)
		{
			if (empty($item->Operation)) // phpcs:ignore
			{
				continue;
			}

			$rpo = (string) $item->Barcode; // phpcs:ignore

			if (!is_array($item->Operation)) // phpcs:ignore
			{
				$item = [$item->Operation]; // phpcs:ignore
			}
			else
			{
				$item = $item->Operation; // phpcs:ignore
			}

			foreach ($item as &$operation)
			{
				try
				{
					$statusInfo = $statusList->getInfo($operation->OperTypeID, $operation->OperCtgID); // phpcs:ignore
					$operation->OperCtgName = $statusInfo['substatusName']; // phpcs:ignore
					$operation->isFinal = $statusInfo['isFinal'];
				}
				catch (StatusValidationException $e)
				{
					$operation->OperCtgName = $e->getMessage(); // phpcs:ignore
					$operation->isFinal = false;
				}
			}

			$result[$rpo] = $item;
			unset($result[$key]);
		}

		return $result;
	}

	/**
	 * @param   string $ticketNumber Ticket number
	 * @return stdclass
	 * @since 1.0.0
	 */
	public function getResponseByTicket(string $ticketNumber): stdclass
	{
		$requestParams = new stdclass;
		$requestParams->login = $this->login;
		$requestParams->password = $this->password;
		$requestParams->ticket = $ticketNumber;

		return $this->client->getResponseByTicket($requestParams);
	}
}
