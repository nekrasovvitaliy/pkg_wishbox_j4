<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\Component\Jshopping\Site\Model\CartModel;
use Joomla\Component\Jshopping\Site\Table\ShippingMethodPriceTable;
use Wishbox\JShopping\ShippingTariff;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property ?mixed $addon
 *
 * @since 1.0.0
 */
interface ShippingCalculatorInterface
{
	/**
	 * Возвращает стоимость доставки
	 *
	 * @param	CartModel                 $cart                 Cart
	 * @param	array                     $params               Params
	 * @param	array                     $price                Price
	 * @param	object                    $shippingExtRow       Shipping ext row
	 * @param	ShippingMethodPriceTable  $shippingMethodPrice  Shipping method price
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getPrice(
		CartModel $cart,
		array $params,
		array &$price,
		object &$shippingExtRow,
		ShippingMethodPriceTable &$shippingMethodPrice
	): array;

	/**
	 * getTariff
	 *
	 * @param   integer  $shPrMethodId  Shipping price method id
	 *
	 * @return ?ShippingTariff
	 *
	 * @since 1.0.0
	 */
	public function getTariff(int $shPrMethodId): ?ShippingTariff;
}
