<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
class ShippingTariff
{
	/**
	 * @var integer|null $periodMin Min period
	 *
	 * @since 1.0.0
	 */
	public ?int $periodMin;

	/**
	 * @var integer|null $periodMax Max period
	 *
	 * @since 1.0.0
	 */
	public ?int $periodMax;

	/**
	 * @var float $shipping Shipping price
	 *
	 * @since 1.0.0
	 */
	public float $shipping;

	/**
	 * @var float $package Package price
	 *
	 * @since 1.0.0
	 */
	public float $package;

	/**
	 * @var string|null $code Shipping service code
	 *
	 * @since 1.0.0
	 */
	public ?string $code;

	/**
	 * @var string|null $name Shipping service name
	 *
	 * @since 1.0.0
	 */
	public ?string $name;

	/**
	 * Constructor
	 *
	 * @param   float $shipping Shipping price
	 * @param   float $package  Package price
	 *
	 * @since 1.0.0
	 */
	public function __construct(float $shipping, float $package)
	{
		$this->periodMin = null;
		$this->periodMax = null;
		$this->shipping = $shipping;
		$this->package = $package;
		$this->code = null;
		$this->name = null;
	}

	/**
	 * @param   int $periodMin Min period (days)
	 *
	 * @return ShippingTariff
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setPeriodMin(int $periodMin): ShippingTariff
	{
		$this->periodMin = $periodMin;

		return $this;
	}

	/**
	 * @param   int $periodMax Max period (days)
	 * @return self
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setPeriodMax(int $periodMax): ShippingTariff
	{
		$this->periodMax = $periodMax;

		return $this;
	}

	/**
	 * @param   float $shipping Shipping price
	 *
	 * @return ShippingTariff
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setShipping(float $shipping): ShippingTariff
	{
		$this->shipping = $shipping;

		return $this;
	}

	/**
	 * @param   float $package Shipping price
	 * @return ShippingTariff
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setPackage(float $package): ShippingTariff
	{
		$this->package = $package;

		return $this;
	}

	/**
	 * @param   string $code Shipping service code
	 *
	 * @return ShippingTariff
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setCode(string $code): ShippingTariff
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * @param   string $name Shipping service name
	 *
	 * @return ShippingTariff
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setName(string $name): ShippingTariff
	{
		$this->name = $name;

		return $this;
	}
}
