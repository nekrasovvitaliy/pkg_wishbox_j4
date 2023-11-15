<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Tracker\Interface;

use Joomla\CMS\Table\TableInterface;

/**
 * @since 1.0.0
 */
interface OperationTableInterface extends TableInterface
{
	/**
	 * @return string
	 * @since 1.0.0
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getTableName();
}
