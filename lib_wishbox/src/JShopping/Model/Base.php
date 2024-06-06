<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Component\Jshopping\Site\Model\BaseModel;
use Wishbox\MainTrait;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property CMSApplicationInterface $app
 *
 * @since 1.0.0
 */
class Base extends BaseModel
{
	use MainTrait;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{

	}
}
