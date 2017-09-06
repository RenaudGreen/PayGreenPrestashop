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

class PaygreenValidationModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely
     */
/*    public function postProcess()
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
            die($this->module->l('This payment method is not available.', 'validation'));
        }
        Tools::redirect($_REQUEST['action'].'?d='. urlencode($_REQUEST['paiement']) . '&ref=prestashop');
        $this->context->smarty->assign(array(
            'params' => $_REQUEST,
        ));
        $this->setTemplate('module:paygreen/views/templates/front/payment_return.tpl');
    }*/

    const CASH_PAYMENT = 0;
    const SUB_PAYMENT = 1;
    const DEL_PAYMENT = -1;
    const REC_PAYMENT = 3;

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
            die($this->module->l('This payment method is not available.', 'validation'));
        }
        $o_paygreen = new Paygreen();
        $ui = $o_paygreen->getUniqueIdPP();
        $cp = Configuration::get($o_paygreen::_CONFIG_PRIVATE_KEY);
        $host = $o_paygreen->getPaygreenConfig('host');
        $API = PaygreenApiClient::getInstance($ui, $cp, $host);

        if (isset($_REQUEST['pid']) && !empty($_REQUEST['pid'])) {
            $payment = $API->getTransactionInfo($_REQUEST['pid']);
        } else {
            $paymentType = $_REQUEST['paymentType'];
            $paymentData = json_decode($_REQUEST['paymentData']);
            $displayType = $_REQUEST['displayType'];
            $payment = $o_paygreen->createPayment($paymentType, $paymentData, $displayType);
        }
        // var_dump($payment);
        // die('qwe');
        if ($payment->success && $payment->data->result->status == 'PENDING') {
            Tools::redirect($payment->data->url);
        } else {
            $o_cart = new Cart($payment->data->metadata->cart_id);
            $n_order_id = (int)Order::getOrderByCartId($o_cart->id);
            $o_order = new Order($n_order_id);
            $o_paygreen->redirectToConfirmationPage($o_order);
        }
    }
}
