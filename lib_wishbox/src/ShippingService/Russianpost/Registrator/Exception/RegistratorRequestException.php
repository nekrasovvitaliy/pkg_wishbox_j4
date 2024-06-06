<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Exception;

use Throwable;

/**
 * @since 1.0.0
 */
class RegistratorRequestException extends RegistratorException
{
	/**
	 * @var string|null $errorCode  Error code
	 *
	 * @since 1.0.0
	 */
	public array $errorCodes;

	/**
	 * @var string|null $errorMessage Error message
	 *
	 * @since 1.0.0
	 */
	public ?string $errorMessage;

	/**
	 * @param   string          $message       Message
	 * @param   integer         $code          Code
	 * @param   Throwable|null  $previous      Previous
	 * @param   string|null     $errorCode     Error code
	 * @param   string|null     $errorMessage  Error message
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		string $message,
		int $code = 0,
		Throwable $previous = null,
		?string $errorCode = null,
		?string $errorMessage = null
	)
	{
		parent::__construct($message, $code, $previous);

		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;
	}
}
