<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Cdek\Interface;

use Joomla\Database\DatabaseDriver;

/**
 * @property DatabaseDriver $db
 *
 * @since 1.0.0
 */
interface CalculatorDelegateInterface
{
	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getShippingMethodId() : int;

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getSenderCityCode(): int;

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getReceiverCityCode(): int;

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getTotalWeight(): int;

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getTariffCodes(): array;

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getCalculationMethod(): string;

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getProducts(): array;

	/**
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	public function useDimencions(): bool;

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageWidth(): int;

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageHeight(): int;

	/**
	 * @return integer
	 *
	 * @since 1.0.0
	 */
	public function getPackageLength(): int;
}
