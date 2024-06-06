<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\BPOnline;

use Exception;

/**
 * @package     Wishbox
 *
 * @since       1.0.0
 * @noinspection PhpUnused
 */
class ApiClient
{
	/**
	 * @var array $apiUrl API URL
	 * @since 1.0.0
	 */
	private array $apiUrl = [
		0 => 'https://5.375.ru/bpo-api/v1',
		1 => 'https://sync-dev.pvision.ru/bpo-api/v1'
	];

	/**
	 * @var string $apiKey API key
	 * @since 1.0.0
	 */
	private string $apiKey;

	/**
	 * @var string $baseId Base id
	 * @since 1.0.0
	 */
	private string $baseId;

	/**
	 * @var boolean $testMode Test mode
	 * @since 1.0.0
	 */
	private bool $testMode;

	/**
	 * Constructor for the ApiClient class.
	 *
	 * @param   string $apiKey   Authorization token (Bearer Token).
	 * @param   string $baseId   Base id
	 * @param   bool   $testMode Test mode
	 * @since 1.0.0
	 */
	public function __construct(string $apiKey, string $baseId, bool $testMode = false)
	{
		$this->apiKey = $apiKey;
		$this->baseId = $baseId;
		$this->testMode = $testMode;
	}

	/**
	 * Sends an HTTP request to the API.
	 *
	 * @param   string      $endpoint Relative path to the API endpoint.
	 * @param   string      $method   HTTP request method (GET, POST, etc.).
	 * @param   string|null $data     Data for POST request (if applicable).
	 * @return string API response.
	 * @throws Exception If the response code is not 200, throws an exception with an error message.
	 * @since 1.0.0
	 */
	private function sendRequest(string $endpoint, string $method, ?string $data = null): string
	{
		$url = $this->getApiUrl() . $endpoint;

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		if ($method === 'DELETE')
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}

		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			[
				'Authorization: Bearer ' . $this->apiKey,
				'Content-Type: application/json',
			]
		);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode !== 200)
		{
			$errorData = json_decode($response, true);
			$errorMessage = $errorData['error']['message'] ?? 'Unknown error';
			throw new Exception($errorMessage);
		}

		return $response;
	}

	/**
	 * Executes a GET request to the API.
	 *
	 * @param   string $endpoint Relative path to the API endpoint.
	 * @return string API response.
	 * @throws Exception If the response code is not 200, throws an exception with an error message.
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function getApiResponse(string $endpoint): string
	{
		return $this->sendRequest($endpoint, 'GET');
	}

	/**
	 * Creates an invoice via a POST request.
	 *
	 * @param   array $data Data for creating an invoice.
	 * @return string|null ID of the created invoice, or null if unable to get the ID.
	 * @throws Exception If the response code is not 200, throws an exception with an error message.
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function createInvoice(array $data): ?string
	{
		$response = $this->sendRequest('/doc-invoice', 'POST', json_encode($data));
		$responseData = json_decode($response, true);

		return $responseData['Object'] ?? null;
	}

	/**
	 * Deletes an invoice via a POST request.
	 *
	 * @param   array $data Data for creating an invoice.
	 * @return string|null ID of the created invoice, or null if unable to get the ID.
	 * @throws Exception If the response code is not 200, throws an exception with an error message.
	 * @since 1.0.0
	 * @noinspection PhpUnused
	 */
	public function deleteInvoice(string $id): ?string
	{
		$response = $this->sendRequest('/doc-invoice/'.$id, 'DELETE');
		$responseData = json_decode($response, true);

		return $responseData['Object'] ?? null;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function getApiUrl(): string
	{
		return $this->testMode ? ($this->apiUrl[1] . '/' . $this->baseId) : ($this->apiUrl[0] . '/' . $this->baseId);
	}
}
