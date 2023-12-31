<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\Component\Jshopping\Site\Model\CartModel;
use Joomla\Component\Jshopping\Site\Table\ProductTable;
use Joomla\Component\Jshopping\Site\Table\ShippingmethodpriceTable;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
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
class ShippingCalculator extends Base implements ShippingCalculatorInterface
{
	/**
	 * @var array $tariffs Tariffs
	 *
	 * @since 1.0.0
	 */
	protected static array $tariffs = [];

	/**
	 * @var boolean $calculated Is calculated
	 *
	 * @since 1.0.0
	 */
	protected bool $calculated = false;

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
	): array
	{
		return [];
	}

	/**
	 * getTariff
	 *
	 * @param   integer  $shPrMethodId  Shipping price method id
	 *
	 * @return ?ShippingTariff
	 *
	 * @since 1.0.0
	 */
	public function getTariff(int $shPrMethodId): ?ShippingTariff
	{
		if (!$this->calculated)
		{
			/** @var CartModel $cart */
			$cart = JSFactory::getModel('cart', 'Site');

			$cart->load();

			/** @var ShippingmethodpriceTable $shippingmethodpriceTable */
			$shippingmethodpriceTable = JSFactory::getTable('shippingMethodPrice');

			$shippingmethodpriceTable->load($shPrMethodId);
			$shippingmethodpriceTable->calculateSum($cart);
			$this->calculated = true;
		}

		return self::$tariffs[$shPrMethodId] ?? null;
	}

	/**
	 * @param   integer  $productId  Product id
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function getProductDimensions(int $productId): array
	{
		$dimencionUnitRatio = (float) $this->addon->params->get('dimencion_unit', 1);
		$widthExtraFieldId = $this->addon->params->get('width_extra_field_id', 0);
		$heightExtraFieldId = $this->addon->params->get('height_extra_field_id', 0);
		$lengthExtraFieldId = $this->addon->params->get('length_extra_field_id', 0);

		/** @var ProductTable $productTable */
		$productTable = JSFactory::getTable('product');
		$productTable->load($productId);

		return [
			'width' => $dimencionUnitRatio * (float) $productTable->{'extra_field_' . $widthExtraFieldId},
			'height' => $dimencionUnitRatio * (float) $productTable->{'extra_field_' . $heightExtraFieldId},
			'length' => $dimencionUnitRatio * (float) $productTable->{'extra_field_' . $lengthExtraFieldId}
		];
	}
}
