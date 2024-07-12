<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\Helper\Price;

use Wishbox\Helper\Price\Enum\CurrencyPosition;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class PriceHelper
{
	/**
	 * @param   float             $price              Price
	 * @param   string|null       $currencyCode       Currency code
	 * @param   float             $currencyExchange   Currency exchange
	 * @param   CurrencyPosition  $currencyPosition   Currency position
	 * @param   integer           $decimalCount       Decimal count
	 * @param   string            $decimalSymbol      Decimal symbol
	 * @param   string            $thousandSeparator  A Thousand separator
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function format(
		float $price,
		?string $currencyCode = 'RUB',
		float $currencyExchange = 1,
		CurrencyPosition $currencyPosition = CurrencyPosition::After,
		int $decimalCount = 2,
		string $decimalSymbol = '.',
		string $thousandSeparator = ' '
	): string
	{
		if ($currencyExchange)
		{
			$price = $price * $currencyExchange;
		}

		$price = number_format(
			$price,
			$decimalCount,
			$decimalSymbol,
			$thousandSeparator
		);

		if ($currencyPosition == CurrencyPosition::After)
		{
			$price = $price . ' <span class="currencycode">' . $currencyCode . '</span>';
		}
		else
		{
			$price = '<span class="currencycode">' . $currencyCode . '</span> ' . $price;
		}

		return $price;
	}
}
