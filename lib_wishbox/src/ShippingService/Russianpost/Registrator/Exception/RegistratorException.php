<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Exception;

use AntistressStore\CdekSDK2\Constants;

/**
 * @since 1.0.0
 */
class RegistratorException extends \Exception
{
	/**
	 * @param   integer   $code     Code
	 * @param   string    $message  Message
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function getTranslation(int $code, string $message): string
	{
		if (array_key_exists($code, Constants::ERRORS))
		{
			return Constants::ERRORS[$code] . '. ' . $message;
		}

		return $message;
	}
}
