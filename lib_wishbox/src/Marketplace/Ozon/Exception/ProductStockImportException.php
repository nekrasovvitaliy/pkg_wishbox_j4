<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Marketplace\Ozon\Exception;

use RuntimeException;

defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class ProductStockImportException extends RuntimeException
{
	/**
	 * @var integer $ozonProductId Ozon product id
	 *
	 * @since 1.0.0
	 */
	public int $ozonProductId;
}
