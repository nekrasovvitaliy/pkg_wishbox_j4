<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\Map;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Point
{
	/**
	 * @var float Latitude
	 *
	 * @since 1.0.0
	 */
	public float $latitude;

	/**
	 * @var float Longitude
	 *
	 * @since 1.0.0
	 */
	public float $longitude;

	/**
	 * @param   float  $latitude   Latitude
	 * @param   float  $longitude  Longitude
	 *
	 * @since 1.0.0
	 */
	public function __construct(float $latitude, float $longitude)
	{
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}
}
