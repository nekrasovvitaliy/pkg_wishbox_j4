<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Language;
use Joomla\Component\Jshopping\Administrator\View\Shippingsprices\HtmlView;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Model\CartModel;
use Joomla\Component\Jshopping\Site\Table\ConfigTable;
use Joomla\Component\Jshopping\Site\Table\ShippingExtTable;
use Joomla\Component\Jshopping\Site\Table\ShippingMethodPriceTable;
use ShippingExtRoot;
use stdClass;
use Wishbox\JShopping\Model\ShippingCalculatorInterface;
use Wishbox\MainTrait;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @noinspection PhpUnused
 */

/**
 * @property Language     $language
 * @property ConfigTable  $config
 *
 * @since 1.0.0
 */
class ShippingExt extends ShippingExtRoot
{
	use MainTrait;

	/**
	 * @var integer $version Version
	 *
	 * @since 1.0.0
	 */
	public int $version = 2;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->title = '_JSHOP_SM_WISHBOXCDEK';
	}

	/**
	 * @param   array            $params             Params
	 * @param   ShippingExtTable $shipping_ext_row   ShippingExtTable object
	 * @param   HtmlView         $template           HtmlView
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpVariableNamingConventionInspection
	 */
	public function showShippingPriceForm(
		mixed $params,
		mixed &$shipping_ext_row, // phpcs:ignore
		mixed &$template
	): void
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		$exec = $shipping_ext_row->exec; // phpcs:ignore
		$alias = $exec->addon->getAlias();
		$row = $template->sh_method_price; // phpcs:ignore
		$this->language->load(
			'plg_jshoppingadmin_wishboxadmin' . str_replace('wishbox', '', $alias),
			JPATH_ADMINISTRATOR
		);
		Form::addFieldPath($this->config->path . '/addons/' . $alias . '/fields');
		Form::addFieldPath($this->config->path . '/shippings/sm_' . $alias . '/fields');
		$form = Factory::getContainer()
			->get(FormFactoryInterface::class)
			->createForm($alias . '.shippingpriceform', ['control' => 'sm_params', 'load_data' => true]);
		$form->bind($row->getParams());
		echo '<tr>
                <td colspan="2">
                    <div class="form-horizontal">' .
						$form->renderFieldset('basic') .
					'</div>
                </td>
                </tr>';
	}

	/**
	 * @param   ConfigTable      $config       Config
	 * @param   ShippingextTable $shipping_ext Shipping extention
	 * @param   HtmlView         $template     HtmlView
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function showConfigForm(
		$config,
		&$shipping_ext, // phpcs:ignore
		&$template
	): void
	{
		// Parent::showConfigForm($config, $shipping_ext, $template);
	}

	/**
	 * @param   CartModel                $cart                  Cart model
	 * @param   mixed                    $params                Params
	 * @param   mixed                    $price                 Price
	 * @param   stdClass                 $shippingExtRow        Shipping ext row
	 * @param   ShippingmethodpriceTable $shippingMethodPrice   Shipping method price
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getPrices(
		CartModel $cart,
		array $params,
		array &$price,
		stdClass &$shippingExtRow,
		ShippingmethodpriceTable &$shippingMethodPrice
	): array
	{
		$calculatorModelClass = mb_substr(get_class($this), 10);

		/** @var ShippingCalculatorInterface $calculatorModel */
		$calculatorModel = JSFactory::getModel($calculatorModelClass, 'Site\\Wishbox\\Shippingcalculator');

		$price = $calculatorModel->getPrice($cart, $params, $price, $shippingExtRow, $shippingMethodPrice);

		return $price;
	}
}
