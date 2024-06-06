<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Database\DatabaseDriver;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Wishboxclasses helper.
 *
 * @since  1.0.0
 *
 * @noinspection PhpUnused
 */
class OptionsHelper
{
	/**
	 * Get a class type list in text/value format for a select field
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getOperationCodeOptions(): array
	{
		$app = Factory::getApplication();
		$lang = JSFactory::getLang();
		$nameField = $lang->get('name');
		$options = [];
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$query = $db->getQuery(true)
			->select(
				[
					$db->qn('operationcode.id', 'value'),
					$db->qn($nameField, 'text'),
				]
			)
			->from($db->qn('#__jshopping_wishboxrussianpost_operationcodes', 'operationcode'))
			->where($db->qn('operationcode.publish') . ' = 1');

		$query->order($db->qn('text'));

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		return $options;
	}

	/**
	 * Get a class type list in text/value format for a select field
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public static function getOperationAttributeCodeOptions(): array
	{
		$app = Factory::getApplication();
		$lang = JSFactory::getLang();
		$nameField = $lang->get('name');
		$options = [];
		$db = Factory::getContainer()->get(DatabaseDriver::class);
		$query = $db->getQuery(true)
			->select(
				[
					$db->qn('attribute.id', 'value'),
					$db->qn('attribute.' . $nameField, 'text'),
					$db->qn('operation.' . $nameField, 'operation'),
				]
			)
			->from($db->qn('#__jshopping_wishboxrussianpost_operationattributecodes', 'attribute'))
			->leftJoin('#__jshopping_wishboxrussianpost_operationcodes AS operation ON operation.id = attribute.operationcode_id')
			->where($db->qn('attribute.publish') . ' = 1');

		$query->order($db->qn('text'));

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		foreach ($options as $k => $option)
		{
			$options[$k]->text = $options[$k]->operation . ' - ' . $options[$k]->text;
		}

		return $options;
	}
}
