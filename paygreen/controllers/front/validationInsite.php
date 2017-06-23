<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PaygreenValidationInsiteModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        // Check that this payment option is still available
        //in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'paygreen') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validationInsite'));
        }
        $o_paygreen = new Paygreen();
        $client = $o_paygreen->getCaller();
        $pac = new PaygreenApiClient();
        $ui = $o_paygreen->getUniqueIdPP();
        $cp = Configuration::get($o_paygreen::_CONFIG_PRIVATE_KEY);
        $result = $pac->getTransactionInfo($ui, $cp, $_REQUEST['pid']);
        if ($result->success && $result->data->result->status == 'PENDING') {
            Tools::redirect('index.php?controller=order&step=3&insite='.$_REQUEST['id']);
        } else {
            var_dump($_REQUEST['cart_id']);
            $o_cart = new Cart($_REQUEST['cart_id']);
            var_dump($o_cart->id);
            $n_order_id = (int)Order::getOrderByCartId($o_cart->id);
            var_dump($n_order_id );
            $o_order = new Order($n_order_id);
            var_dump($o_order);
            die();
            $o_paygreen->redirectToConfirmationPage($o_order);
        }
    }
}
