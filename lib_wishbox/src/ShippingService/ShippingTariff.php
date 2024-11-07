<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService;

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
	 * @return array
	 *
	 * @since 1.0.00
	 */
	public function toArray(): array
	{
		return [
			'periodMin' => $this->periodMin,
			'periodMax' => $this->periodMax,
			'shipping'  => $this->shipping,
			'package'   => $this->package,
			'code'      => $this->code,
			'name'      => $this->name,
		];
	}

	/**
	 * @param   array  $array  Array
	 *
	 * @return ShippingTariff
	 *
	 * @since 1.0.00
	 */
	public static function withArray(array $array): ShippingTariff
	{
		return (new ShippingTariff($array['shipping'], $array['package']))
			->setPeriodMin($array['periodMin'])
			->setPeriodMax($array['periodMax'])
			->setCode($array['code'])
			->setName($array['name']);
	}

	/**
	 * @param   integer  $periodMin  Min period (days)
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
	 * @param   integer  $periodMax  Max period (days)
	 *
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
	 * @param   float  $shipping  Shipping price
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
	 * @param   float  $package  Shipping price
	 *
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
	 * @param   string  $code  Shipping service code
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
	 * @param   string  $name  Shipping service name
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

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getPeriodMin(): int
	{
		return $this->periodMin;
	}

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getPeriodMax(): int
	{
		return $this->periodMax;
	}

	/**
	 * @return float
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getShipping(): float
	{
		return $this->shipping;
	}

	/**
	 * @return float
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getPackage(): float
	{
		return $this->package;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getName(): string
	{
		return $this->name;
	}
}
