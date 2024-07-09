<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\Helper\Price\Enum;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 */
enum CurrencyPosition: string
{
	case Before = 'before';
	case After = 'after';
}
