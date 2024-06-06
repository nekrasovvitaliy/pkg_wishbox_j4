<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\JShopping;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Language;
use Joomla\Component\Jshopping\Administrator\View\Shippingsprices\HtmlView;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Joomla\Component\Jshopping\Site\Model\CartModel;
use Joomla\Component\Jshopping\Site\Table\ConfigTable;
use Joomla\Component\Jshopping\Site\Table\ShippingExtTable;
use Joomla\Component\Jshopping\Site\Table\ShippingMethodPriceTable;
use RuntimeException;
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
 * @property  Language     $language
 * @property  ConfigTable  $config
 *
 * @since 1.0.0
 */
class ShippingExt extends ShippingExtRoot
{
	use MainTrait;

	/**
	 * Version
	 *
	 * @var integer $version
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

	}

	/**
	 * @param   array             $params             Params
	 * @param   ShippingExtTable  $shipping_ext_row   ShippingExtTable object
	 * @param   HtmlView          $template           HtmlView
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
		$alias = str_replace('sm_', '', $exec::class);
		$class = str_replace('sm_wishbox', '', $exec::class);

		$row = $template->sh_method_price; // phpcs:ignore

		$this->language->load('plg_jshoppingadmin_wishbox' . $class);
		$this->language->load('file_jshopping_wishbox' . $class);

		$form = Factory::getContainer()
			->get(FormFactoryInterface::class)
			->createForm(
				$alias . '.shippingpriceform',
				[
					'control' => 'sm_params',
					'load_data' => true
				]
			);

		$form->load(
			file_get_contents(
				JPATH_SITE . '/components/com_jshopping/shippings/sm_wishbox' . $class . '/forms/shippingpriceform.xml'
			)
		);
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
	 * @param   ConfigTable       $config        Config
	 * @param   ShippingextTable  $shipping_ext  Shipping extention
	 * @param   HtmlView          $template      HtmlView
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function showConfigForm(
		$config,
		&$shipping_ext, // phpcs:ignore
		&$template
	)
	{
		// Parent::showConfigForm($config, $shipping_ext, $template);
	}

	/**
	 * @param   CartModel                 $cart                 Cart model
	 * @param   mixed                     $params               Params
	 * @param   mixed                     $price                Price
	 * @param   stdClass                  $shippingExtRow       Shipping ext row
	 * @param   ShippingmethodpriceTable  $shippingMethodPrice  Shipping method price
	 *
	 * @return mixed
	 *
	 * @throws Exception
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
		$app = Factory::getApplication();

		try
		{
			$calculatorModelClass = mb_substr(get_class($this), 10);

			/** @var ShippingCalculatorInterface $calculatorModel */
			$calculatorModel = JSFactory::getModel($calculatorModelClass, 'Site\\Wishbox\\Shippingcalculator');

			$price = $calculatorModel->getPrice($cart, $params, $price, $shippingExtRow, $shippingMethodPrice);
		}
		catch (Exception | RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		return $price;
	}
}
