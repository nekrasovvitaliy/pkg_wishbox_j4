<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping\Model;

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Table\ShippingextTable;
use Joomla\Component\Jshopping\Site\Table\ShippingMethodPriceTable;
use Joomla\Component\Jshopping\Site\View\Checkout\HtmlView;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property Registry $addonParams
 * @property Language $language
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Shipping extends Base
{
	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getShippingMethodIds(): array
	{
		$shippingextAlias = 'sm_wishbox' . mb_strtolower(
			str_replace('Model', '', mb_substr($this::class, mb_strrpos($this::class, '\\') + 1))
		);

		/** @var ShippingextTable $shippingextTable */
		$shippingextTable = JSFactory::getTable('shippingext');

		$shippingextTable->loadFromAlias($shippingextAlias);

		/** @noinspection PhpUndefinedFieldInspection */
		$shippingMethodsKeyIds = unserialize($shippingextTable->shipping_method); // phpcs:ignore
		$shippingMethodsIds = [];

		foreach ($shippingMethodsKeyIds as $key => $value)
		{
			if ($value == 1)
			{
				$shippingMethodsIds[] = $key;
			}
		}

		return $shippingMethodsIds;
	}

	/**
	 * @param   HtmlView $view Shippings view
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function onBeforeDisplayCheckoutStep4View(HtmlView $view): void
	{
		if (!$this->addonParams->get('show_non_calculated_shipping_methods', 0))
		{
			$this->hideNonCalculatedShippingMethods($view);
		}

		$this->setDelivery($view);

		if ($this->addonParams->get('checkout_shippings_show_tariff_names', 0))
		{
			$this->setTariffName($view);
		}
	}

	/**
	 * @param   HtmlView $view Shippings view
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpVariableNamingConventionInspection
	 */
	public function hideNonCalculatedShippingMethods(HtmlView $view): void
	{
		$shippingMethodsIds = $this->getShippingMethodIds();

		/** @noinspection PhpUndefinedFieldInspection */
		foreach ($view->shipping_methods as $k => $shippingMethod) // phpcs:ignore
		{
			/** @var ShippingMethodPriceTable $shippingmethodpriceTable */
			$shippingmethodpriceTable = JSFactory::getTable('shippingmethodprice');

			$shippingmethodpriceTable->load($shippingMethod->sh_pr_method_id); // phpcs:ignore

			/** @noinspection PhpUndefinedFieldInspection */
			if (in_array($shippingMethod->shipping_id, $shippingMethodsIds) // phpcs:ignore
				&& $shippingMethod->calculeprice == $shippingmethodpriceTable->shipping_stand_price // phpcs:ignore
			)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				unset($view->shipping_methods[$k]); // phpcs:ignore
			}

			if ($shippingMethod->form == 'NO_OFFICES')
			{
				/** @noinspection PhpUndefinedFieldInspection */
				unset($view->shipping_methods[$k]); // phpcs:ignore
			}
		}
	}

	/**
	 * @param   HtmlView $view Shippings view
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function setDelivery(HtmlView $view): void
	{
		$shippingMethodsIds = $this->getShippingMethodIds();

		$class = mb_strtolower(
			str_replace(
				'Model',
				'',
				str_replace(
					'Joomla\\Component\\Jshopping\\Site\\Model\\Wishbox\\Shipping\\',
					'',
					$this::class
				)
			)
		);

		$this->language->load('plg_jshoppingadmin_wishbox' . $class, JPATH_ADMINISTRATOR);

		/** @noinspection PhpUndefinedFieldInspection */
		foreach ($view->shipping_methods as $k => $shippingMethod) // phpcs:ignore
		{
			if (in_array($shippingMethod->shipping_id, $shippingMethodsIds)) // phpcs:ignore
			{
				$class = mb_strtolower(
					str_replace('Model', '', mb_substr($this::class, mb_strrpos($this::class, '\\') + 1))
				);

				/** @var ShippingCalculatorInterface $shipingcalculatorModel */
				$shipingcalculatorModel = JSFactory::getModel($class, 'Site\\Wishbox\\Shippingcalculator');

				$tariff = $shipingcalculatorModel->getTariff($shippingMethod->sh_pr_method_id); // phpcs:ignore

				if ($tariff)
				{
					if ($tariff->periodMin > 0
						&& $tariff->periodMax > 0
						&& $tariff->periodMin != $tariff->periodMax)
					{
						// phpcs:ignore
						$view->shipping_methods[$k]->delivery = $tariff->periodMin
							. ' - ' . $tariff->periodMax
							. ' ' . Text::_('PLG_JSHOPPINGADMIN_WISHBOX' . mb_strtoupper($class) . '_DAYS');
					}
					elseif ($tariff->periodMin > 0
						&& $tariff->periodMax > 0
						&& $tariff->periodMin == $tariff->periodMax)
					{
						// phpcs:ignore
						$view->shipping_methods[$k]->delivery = $tariff->periodMin
							. ' '
							. Text::_('PLG_JSHOPPINGADMIN_WISHBOX' . mb_strtoupper($class) . '_DAYS');
					}
				}
			}
		}
	}

	/**
	 * @param   HtmlView $view Checkout view
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function setTariffName(HtmlView $view): void
	{
		$shippingMethodsIds = $this->getShippingMethodIds();

		$class = mb_strtolower(
			str_replace('Model', '', mb_substr($this::class, mb_strrpos($this::class, '\\') + 1))
		);

		/** @var ShippingCalculatorInterface $shipingcalculatorModel */
		$shipingcalculatorModel = JSFactory::getModel($class, 'Site\\Wishbox\\Shippingcalculator');

		/** @noinspection PhpUndefinedFieldInspection */
		foreach ($view->shipping_methods as $k => $shippingMethod) // phpcs:ignore
		{
			if (in_array($shippingMethod->shipping_id, $shippingMethodsIds)) // phpcs:ignore
			{
				$tariff = $shipingcalculatorModel->getTariff($shippingMethod->sh_pr_method_id); // phpcs:ignore

				if ($tariff)
				{
					if ($tariff->name)
					{
						// phpcs:ignore
						$view->shipping_methods[$k]->description .= '</div><div class="shipping_tariff">' . $tariff->name;
					}
				}
			}
		}
	}

	/**
	 * @param   HtmlView $view Checkout view
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getShippingDaysByShippingMethodId(HtmlView $view): array
	{
		$shippingDaysByShippingMethodId = [];
		$shippingMethodsIds = $this->getShippingMethodIds();

		/** @noinspection PhpUndefinedFieldInspection */
		foreach ($view->shipping_methods as $shippingMethod) // phpcs:ignore
		{
			if (in_array($shippingMethod->shipping_id, $shippingMethodsIds)) // phpcs:ignore
			{
				$class = mb_strtolower(
					str_replace('Model', '', mb_substr($this::class, mb_strrpos($this::class, '\\') + 1))
				);

				/** @var ShippingCalculatorInterface $shipingcalculatorModel */
				$shipingcalculatorModel = JSFactory::getModel($class, 'Site\\Wishbox\\Shippingcalculator');

				$tariff = $shipingcalculatorModel->getTariff($shippingMethod->sh_pr_method_id); // phpcs:ignore

				if ($tariff)
				{
					// phpcs:ignore
					$shippingDaysByShippingMethodId[$shippingMethod->shipping_id] = $tariff->periodMin
						+ $tariff->periodMax / 2;
				}
			}
		}

		return $shippingDaysByShippingMethodId;
	}
}
