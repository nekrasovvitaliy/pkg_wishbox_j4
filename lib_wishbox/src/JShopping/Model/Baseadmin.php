<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\Component\Jshopping\Administrator\Model\BaseadminModel;
use Wishbox\MainTrait;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Baseadmin extends BaseadminModel
{
	use MainTrait;

	public function getNameTable()
	{
		return $this->itemName . 'Table';
	}
}
