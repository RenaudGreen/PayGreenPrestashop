<?php
/**
* 2014 - 2015 Watt Is It
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
*  @author    PayGreen <contact@paygreen.fr>
*  @copyright 2014-2014 Watt It Is
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Paygreen extends PaymentModule
{
    const _CONFIG_PRIVATE_KEY = '_PG_CONFIG_PRIVATE_KEY';
    const _CONFIG_SHOP_TOKEN = '_PG_CONFIG_SHOP_TOKEN';
    const _CONFIG_SHOP_INPUT_METHOD = '_PG_CONFIG_SHOP_INPUT_METHOD';
    const _CONFIG_PAIEMENT_ACCEPTED = '_PG_PAIEMENT_ACCEPTED';
    const _CONFIG_PAIEMENT_REFUSED = '_PG_PAIEMENT_REFUSED';
    const _CONFIG_CANCEL_ACTION = '_PG_CANCEL_ACTION';
    const _CONFIG_VISIBLE = '_PG_VISIBLE';
    const _CONFIG_ORDER_AUTH = '_PG_ORDER_AUTH_OK';
    const _CONFIG_ORDER_TEST = '_PG_ORDER_AUTH_TEST';
    const _CONFIG_PAYMENT_REFUND = '_PG_CONFIG_PAYMENT_REFUND';
    const _CONFIG_FOOTER_DISPLAY = '_PG_CONFIG_FOOTER_DISPLAY';
    const _CONFIG_FOOTER_LOGO_COLOR = '_PG_CONFIG_FOOTER_LOGO_COLOR';
    const _CONFIG_VERIF_ADULT = '_PG_CONFIG_VERIF_ADULT';
    const _CONFIG_BACKLINK_SECURE = '_PG_CONFIG_BACKLING_SEC';

    const BUTTON_IFRAME = 1;
    const BUTTON_EXTERNAL = 0;

    const ERROR_TYPE_BUYER = 1;
    const ERROR_TYPE_MERCHANT = 2;
    const ERROR_TYPE_UNKNOWN = 3;

    const RS_VALID_SIMPLE = 'valid';
    const RS_VALID_WALLET = 'wallet';
    const RS_SUBSCRIBE_REDIRECT = 'rec_redirect';
    const RS_RECURRING_APPROVED = 'rec_approved';

    const DISPLAYB_LOGO = 1;
    const DISPLAYB_LABEL = 2;
    const DISPLAYB_LABEL_LOGO = 3;

    const CASH_PAYMENT = 0;
    const SUB_PAYMENT = 1;
    const DEL_PAYMENT = -1;
    const REC_PAYMENT = 3;

    static protected $accepted_countries = array(
        'ZA',
        'AX',
        'DE',
        'AU',
        'AT',
        'BE',
        'BR',
        'BG',
        'CA',
        'CY',
        'KR',
        'HR',
        'DK',
        'ES',
        'EE',
        'US',
        'FI',
        'FR',
        'GR',
        'GP',
        'GF',
        'HK',
        'HU',
        'IN',
        'IE',
        'IS',
        'IT',
        'JP',
        'LV',
        'LI',
        'LT',
        'LU',
        'MT',
        'MQ',
        'YT',
        'NO',
        'NC',
        'NL',
        'PL',
        'PF',
        'PT',
        'CZ',
        'RE',
        'RO',
        'GB',
        'BL',
        'MF',
        'SG',
        'SK',
        'SI',
        'PM',
        'SE',
        'CH',
        'WF'
    );

    protected $model_buttons = array(
        "id" => 0,
        "label" => null,
        "image" => null,
        "position" => null,
        "height" => 0,
        "displayType" => null,
        "perCentPayment" => null,
        "subOption" => 0,
        "reductionPayment" => "none",
        "nbPayment" => 1,
        "reportPayment" => 0,
        "minAmount" => null,
        "maxAmount" => null,
        "executedAt" => 0
    );
    protected $config_form = false;
    protected $button_list;
    public $preprod;
    public $a_errors;
    public $base_url;
    public $hookName;
    public function __construct()
    {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PaygreenApiClient.php');
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PaygreenClient.php');

        $this->preprod = false;

        $this->name = 'paygreen';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.0';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');
        $this->author = 'Watt Is It';
        $this->need_instance = 1;
        $this->module_key = '0403f32afdc88566f1209530d6f6241c';

        $this->hookName =  array(
            'header',
            'displayPaymentReturn',
            'ActionObjectOrderSlipAddAfter',
            'actionOrderStatusPostUpdate',
            'displayFooter'
        );

        $version = Tools::substr(_PS_VERSION_, 0, 3);
               
        if ($version == 1.5 || $version == 1.6) {
            $this->hookName[] = 'displayBackOfficeHeader';
            $this->hookName[] = 'displayPayment';
        } else {
            $this->hookName[] =  'paymentOptions';
        }
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        if (Module::isInstalled($this->name)) {
            $this->warning = $this->verifyConfiguration();
        }

        parent::__construct();

        $this->displayName = $this->l('PayGreen');
        $this->description = $this->l(
            'The french payment solution that has a positive impact on society and the environment'
        );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->limited_countries = static::$accepted_countries;

        $this->limited_currencies = array('EUR');

        $this->logger = new FileLogger(0);
        $this->logger->setFilename(dirname(__FILE__) . '/lib/debug.log');

        $this->createOrderStatuses();

        PaygreenApiClient::setHost($this->getPaygreenConfig('host'));
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false || $this->checkApi() != '') {
            $this->_errors[] = Tools::displayError(
                $this->l('You have to enable the cURL extension on your server to install this module')
            );
            $this->_errors[] = $this->checkApi();
            $this->log('Install', 'Installation failed: cURL Requirement.');
            return false;
        }
        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = Tools::displayError($this->l('This module is not available in your country'));
            $this->log('Install', 'Installation failed: Country not supported');
            return false;
        }

        /*Configuration::updateValue('PAYGREEN_LIVE_MODE', false);*/
        Configuration::updateValue(self::_CONFIG_PRIVATE_KEY, '');
        Configuration::updateValue(self::_CONFIG_SHOP_TOKEN, '');
        Configuration::updateValue(self::_CONFIG_SHOP_INPUT_METHOD, 'POST');
        Configuration::updateValue(self::_CONFIG_VISIBLE, 1);

        Configuration::updateValue(self::_CONFIG_PAIEMENT_ACCEPTED, $this->l('Your payment was accepted'));
        Configuration::updateValue(self::_CONFIG_PAIEMENT_REFUSED, $this->l('Your payment was unsuccessful'));
        
        // Allow to refund with Paygreen
        Configuration::updateValue(self::_CONFIG_PAYMENT_REFUND, 0);
        
        // Footer Config
        Configuration::updateValue(self::_CONFIG_FOOTER_DISPLAY, 1);
        Configuration::updateValue(self::_CONFIG_FOOTER_LOGO_COLOR, 'white');
        Configuration::updateValue(self::_CONFIG_VERIF_ADULT, 0);
        Configuration::updateValue('oauth_access', '');
        try {
            include(dirname(__FILE__).'/sql/install.php');
        } catch (Exception $ex) {
            $this->l('Instalation dataBase fail,check your id,password or your privileges');
            return false;
        }

        try {
            $btn = $this->getButtonsList();
        } catch (Exception $ex) {
            $this->l('Access to dataBase fail');
            return false;
        }
        // Add button paygreen in menu
        try {
            $tab_parent_id = Tab::getIdFromClassName('AdminParentModules');
            $tab = new Tab();
            $tab->class_name = 'AdminPaygreen';
            $tab->name[$this->context->language->id] = $this->l('Paygreen');
            $tab->id_parent = $tab_parent_id;
            $tab->module = $this->name;
            $tab->add();
        } catch (Exception $ex) {
            $tab = null;
        }
        $version = Tools::substr(_PS_VERSION_, 0, 3);
        //common hook for 1.5/1.6/1.7
        if (!parent::install()
            || !$this->createOrderStatuses()
            || !$this->initializeDb($btn, $version)
            || !$this->registerHook('header')
            || !$this->registerHook('displayPaymentReturn')
            || !$this->registerHook('ActionObjectOrderSlipAddAfter')
            || !$this->registerHook('postUpdateOrderStatus')
            || !$this->registerHook('displayFooter')
        ) {
            $this->l('Installation failed: hooks, configs, order states or sql.');
            return false;
        }
        //hook for 1.5/1.6
        if ($version == 1.5 || $version == 1.6) {
            if (!$this->registerHook('backOfficeHeader')
                || !$this->registerHook('displayPayment')
            ) {
                $this->l('Installation failed: hooks ' . $version . '.');
                return false;
            }
            $this->hookName[] = 'displayBackOfficeHeader';
            $this->hookName[] = 'displayPayment';
        }

        //hook for 1.7
        if ($version == 1.7) {
            if (!$this->registerHook('paymentOptions')) {
                $this->l('Installation failed: hooks for 1.7.');
                return false;
            }
            $this->hookName[] =  'paymentOptions';
        }
        if ($version != 1.5) {
            $return = $this->updatePositionHook($this->hookName);
            if ($return == false) {
                $this->l('Position hook failed.');
                return false;
            }
        }
        $this->l('Installation complete.');

        return true;
    }
    /*
     * uninstall method
     */
    public function uninstall()
    {
        /*Configuration::deleteByName('PAYGREEN_LIVE_MODE');*/
        Configuration::deleteByName(self::_CONFIG_PRIVATE_KEY);
        Configuration::deleteByName(self::_CONFIG_SHOP_TOKEN);

        Configuration::deleteByName(self::_CONFIG_SHOP_INPUT_METHOD);
        Configuration::deleteByName(self::_CONFIG_VISIBLE);

        Configuration::deleteByName(self::_CONFIG_PAIEMENT_ACCEPTED);
        Configuration::deleteByName(self::_CONFIG_PAIEMENT_REFUSED);
        Configuration::deleteByName(self::_CONFIG_PAYMENT_REFUND);

        Configuration::deleteByName(self::_CONFIG_FOOTER_DISPLAY);
        Configuration::deleteByName(self::_CONFIG_FOOTER_LOGO_COLOR);
        Configuration::deleteByName(self::_CONFIG_VERIF_ADULT);
        Configuration::deleteByName(self::_CONFIG_BACKLINK_SECURE);

        Db::getInstance()->delete('tab_lang', 'name=\'Paygreen\'');
        Db::getInstance()->delete('tab', 'class_name=\'Paygreen\'');

        include(dirname(__FILE__).'/sql/uninstall.php');
        
        $id_tab = (int)Tab::getIdFromClassName('AdminPaygreen');
        $tab = new Tab((int) $id_tab);
        $tab ->delete();
        
        if (!parent::uninstall()) {
            $this->l('Uninstallation failed.');
            return false;
        }
        $this->log('Uninstall', 'Uninstallation complete.');
        return true;
    }

    /**
     * Insert fingerprint, nbImage and useTime in database
     * @param $data
     */
    public function insertFingerprintDetails($data)
    {
        $fingerprint = $data['client'];
        $useTime = $data['useTime'];
        $nbImage = $data['nbImage'];
        $startAt = $data['startAt'];
        $nbImageQuery = Db::getInstance()->execute("INSERT INTO ps_fingerprintDetail VALUES ($fingerprint, 'useTime', $useTime, NOW(), $startAt)");
        $useTimeQuery = Db::getInstance()->execute("INSERT INTO ps_fingerprintDetail VALUES ($fingerprint, 'nbImage', $nbImage, NOW(), $startAt)");
        if (!$useTimeQuery && !$nbImageQuery)
            $message = array('error' => 'Failed inserted fingerprint ' . $fingerprint . ' with nbImage ' . $nbImage . ' and useTime ' . $useTime . 'into CLIENT table');
        else
            $message = array('succes' => 'ok');
        header('Content-Type: application/json');
        echo json_encode($message);
    }

    public function getBackLinkPayGreenSecure()
    {
        //_CONFIG_BACKLINK_SECURE
        $cachedValue = Configuration::get(self::_CONFIG_BACKLINK_SECURE);
        $returnedUrl = null;
        if (isset($cachedValue) && !empty($cachedValue)) {
            list($timer, $url) = explode('|', $cachedValue);
            if ($timer > strtotime('-1 day')) {
                $returnedUrl = $url;
            }
        }

        if (empty($returnedUrl)) {
            $securepage = Tools::file_get_contents('https://www.paygreen.fr/ressources/paygren-secure.json');
            Configuration::updateValue(self::_CONFIG_BACKLINK_SECURE, time().'|'.$securepage);
        }
        return $returnedUrl;
    }

    public function hookDisplayPaymentReturn()
    {
        $config = $this->getConfig();
        $this->context->smarty->assign(array(
            'prestashop' => Tools::substr(_PS_VERSION_, 0, 3),
            'error' => Tools::getValue('error') ? Tools::getValue('error') : 0,
            'config' => $config
        ));
        return $this->context->smarty->fetch($this->local_path . 'views/templates/front/payment_return.tpl');
    }


    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     * config_verif_adult disabled
     */
    public function hookHeader()
    {
        if ($this->active == false) {
            return;
        }
        $this->context->controller->addCSS(($this->_path).'views/css/1.7/payment_options.css', 'all');
        /*$version = Tools::substr(_PS_VERSION_, 0, 3);

        $this->context->controller->addJS($this->_path . 'views/js/' . $version . '/ion.rangeSlider.js');
        $this->context->controller->addJS($this->_path . 'views/js/' . $version . '/paygreenInsites.js');*/
        /*$this->context->controller->addCSS($this->_path . 'views/css/' . $version . '/front.css');*/
    }

    /**
     * hook for 1.5/1.6
     */
    public function hookDisplayPayment($params)
    {
        if ($this->verifyConfiguration() != '') {
            return false;
        }
        if ($this->getPaygreenConfig('type') != 'LC' && !$this->paygreenValidIds()) {
            return false;
        }

        $config = $this->getConfig();

        if (!$this->isVisible()) {
            return false;
        }

        $cust = new Customer((int)$this->context->cookie->id_customer);
        $currency = new Currency((int)$this->context->cart->id_currency);

        try {
            $currentConfigureListButtons = $this->getButtonsList();
        } catch (Exception $ex) {
            $this->l('Access to dataBase fail');
            return false;
        }
        $buttons = array();
        foreach ($currentConfigureListButtons as $btn) {
            //Test
            // SI false > continue
            if ($this->checkButton($btn) != '') {
                continue;
            }

            if (isset($btn['reductionPayment']) && $btn['reductionPayment'] != 'none') {
                $cart_rule = new CartRule($this->idPromocode($btn['reductionPayment']));
                $test = new Cart($this->context->cart->id);
                $test->addCartRule($cart_rule->id);
                $totalCart = $test->getOrderTotal();
                $test->removeCartRule($cart_rule->id);
                if ($totalCart <= 0) {
                    $totalCart = $test->getOrderTotal();
                }
                $this->resetQuantity($this->idPromocode($btn['reductionPayment']));
                $this->log('hookDisplayPayment', $this->context->cart);
            } else {
                $totalCart = $this->context->cart->getOrderTotal();
                $this->log('hookDisplayPayment', $this->context->cart);
            }

            if (isset($btn['minAmount'])) {
                if ($btn['minAmount'] > 0 && $totalCart < $btn['minAmount']) {
                    continue;
                }
            }

            if (isset($btn['maxAmount'])) {
                if ($btn['maxAmount'] > 0 && $totalCart > $btn['maxAmount']) {
                    continue;
                }
            }
            if (isset($btn['nbPayment']) && isset($btn['executedAt'])) {
                if (!isset($btn['reportPayment'])) {
                    $btn['reportPayment'] = 0;
                }
                $paiement = $this->generatePaiementData(
                    $this->context->cart->id,
                    $btn['nbPayment'],
                    $totalCart,
                    $currency->iso_code,
                    $btn['executedAt'],
                    $btn['reportPayment'],
                    $btn['perCentPayment']
                );
            }

            switch ($btn['executedAt']) {
                // At the delivery
                case -1:
                    $paiement->cardPrint();
                    break;
            }

            if (!isset($paiement)) {
                return false;
            }

            $paiement->customer(
                $cust->id,
                $cust->lastname,
                $cust->firstname,
                $cust->email
            );

            $paiement->cart_id = $this->context->cart->id;

            if (isset($btn['label'])) {
                $paiement->paiement_btn = $btn['label'];
            }

            if (isset($btn['reductionPayment']) && $this->checkPromoCode($btn['reductionPayment'])) {
                $paiement->reduction = $this->idPromocode($btn['reductionPayment']);
            } else {
                $paiement->reduction = 'none';
            }

            $paiement->return_cancel_url = $this->getShopUrl() . 'modules/paygreen/validation.php';
            $paiement->return_url = $this->getShopUrl() . 'modules/paygreen/validation.php';
            $paiement->return_callback_url = $this->getShopUrl() . 'modules/paygreen/notification.php';


            $address = new Address($this->context->cart->id_address_delivery);
            $paiement->shippingTo(
                $address->lastname,
                $address->firstname,
                $address->address1,
                $address->address2,
                $address->company,
                $address->postcode,
                $address->city,
                $address->country
            );

            $btn['debug'] = $paiement;
            $btn['paiement'] = array(
                'action' => $paiement->getActionForm(),
                'paiementData' => $paiement->generateData()
            );
            $buttons[] = $btn;
        }
        $this->context->smarty->assign(array(
            'prestashop' => Tools::substr(_PS_VERSION_, 0, 3),
            'verify_adult'=>Configuration::get(self::_CONFIG_VERIF_ADULT),
            'buttons' => $buttons,
            'icondir' => $this->getIconDirectory("", true),
            'imgdir' => $this->getImgDirectory("", true),
            'config' => $config
        ));
        $version = Tools::substr(_PS_VERSION_, 0, 3);
        $this->context->controller->addJS($this->_path . 'views/js/' . $version . '/front.js');
        $this->context->controller->addCSS($this->_path . 'views/css/' . $version . '/front.css');
        return $this->context->smarty->fetch($this->local_path . 'views/templates/front/' . $version . '/payment.tpl');
    }

    //HOOK SECTION

    /**
     * Hook When refund total_paid_tax_incl
     * @param array $params
     */
    public function hookPostUpdateOrderStatus($params)
    {
        if (!isset($params['id_order'])) {
            return false;
        }

        $id_order = $params['id_order'];

        if (!isset($params['newOrderStatus'])) {
            return false;
        }

        if ($params['newOrderStatus']->template != 'refund') {
            return false;
        }

        $refundStatus = $this->paygreenRefundTransaction($id_order);
        if (!$refundStatus) {
            return false;
        }
    }

    /**
     * Hook for different payment options
     * only for 1.7
     * @param array $params
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $payment_options = $this->getExternalPaymentOption();
        return $payment_options;
    }

    /**
     * Hook for when partial refund
     * @param $params
     */
    public function hookActionObjectOrderSlipAddAfter($params)
    {
        if (!isset($params['object'])) {
            return false;
        }

        $amount = $params['object']->amount + $params['object']->shipping_cost_amount;

        if ($amount <= 0) {
            return false;
        }

        return $this->paygreenRefundTransaction($params['object']->id_order, $amount);
    }

    public function hookDisplayFooter()
    {
        if (Configuration::get(self::_CONFIG_FOOTER_DISPLAY) != 1) {
            return '';
        }
        $bckLink =  $this->getBackLinkPayGreenSecure();
        if (empty($bckLink)) {
            return;
        }

        $this->context->smarty->assign(array(
            'imgdir' => $this->getImgDirectory('', true),
            'color' => Configuration::get(self::_CONFIG_FOOTER_LOGO_COLOR),
            'backlink' => $bckLink
        ));
        return $this->display(__FILE__, 'views/templates/hook/paygreen-footer.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     * @param array params
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }


    /**
     * @param string $identifier
     * @param string $data
     * @return add log on file debug.log
     */
    protected function log($identifier, $data)
    {
        $this->logger->logDebug('|' . microtime(true) . '|' . $identifier . '|' . Tools::jsonEncode($data));
    }

    /**
     * @param array $attr[token, privateKey, host, type]
     * @return return array(token, privateKey,host,type) if attr = null
     *  return value of attr else
     */
    public function getPaygreenConfig($attr = null)
    {
        $config = $this->getConfig();
        $host = null;
        $token = $config[self::_CONFIG_SHOP_TOKEN];
        $type = Tools::substr($token, 0, 2);

        if ($type == 'PP') {
            $host = 'http://preprod.paygreen.fr';
            $token = Tools::substr($token, 2);
        } elseif ($this->isPreprod()) {
            $host = 'http://preprod.paygreen.fr';
            $type = 'PP';
            $token = Tools::substr($token, 2);
        } elseif ($type == 'LC') {
            $host = 'http://local.paygreen.fr';
            $token = Tools::substr($token, 2);
        } else {
            $type = 'P';
            $host = 'https://paygreen.fr';
        }

        $data = array(
            'token' => $token,
            'privateKey' => $config[self::_CONFIG_PRIVATE_KEY],
            'host' => $host,
            'type' => $type
        );
        if (empty($attr)) {
            return $data;
        }
        return $data[$attr];
    }

    /**
     * getCaller() call getPaygreenConfig()
     * @return instance of PaygreenClient
     */
    public function getCaller()
    {
        $config = $this->getPaygreenConfig();

        $paiement = new PaygreenClient($config['privateKey'], $config['host']);
        $paiement->setToken($config['token']);

        return $paiement;
    }

    public function isVisible()
    {
        $config = $this->getConfig();
        if ($config[self::_CONFIG_VISIBLE] == 0) {
            $userCookie = new Cookie('psAdmin');
            if (isset($userCookie->id_employee) && $userCookie->id_employee > 0) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param [bool $checkBtnConfig]
     * @return empty string for no error
     *  else error string message
    */
    protected function verifyConfiguration($checkBtnConfig = false)
    {
        $config = $this->getConfig();
        $warning = '';

        if (empty($config[self::_CONFIG_PRIVATE_KEY])
            || empty($config[self::_CONFIG_SHOP_TOKEN])) {
            $warning = $this->l('Missing parameters') . ' : ';
            if (empty($config[self::_CONFIG_PRIVATE_KEY])
                && empty($config[self::_CONFIG_SHOP_TOKEN])) {
                $warning .= $this->l('Private key');
                $warning .= ' - ' . $this->l('Unique Identifier');
            } elseif (empty($config[self::_CONFIG_PRIVATE_KEY])) {
                $warning .= $this->l('Private key');
            } else {
                $warning .= $this->l('Unique Identifier');
            }
        }
        if (!extension_loaded('mcrypt')) {
            $warning .= ' - ' . $this->l('PHP module \'mcrypt\' is required.');
        }

        if ((extension_loaded('curl') == false || $this->checkApi() != '')
            && $this->getPaygreenConfig('type') != 'LC') {
            $warning .= ' - ' . $this->l('PHP module \'curl\' is required or url_fopen.');
        }

        if ($checkBtnConfig) {
            $error_tmp = '';
            $nb_error = 0;
            try {
                $btnList = $this->getButtonsList();
            } catch (Exception $ex) {
                return $warning . ' - ' . $this->l('Access to dataBase fail');
            }

            foreach ($btnList as $btn) {
                if ($this->checkButton($btn) != '') {
                    $nb_error++;
                    $error_tmp = $this->checkButton($btn);
                }
            }
            if ($nb_error > 1) {
                $warning .= ' - ' . $this->l('There are errors of button\'s configuration');
            } else {
                $warning .= ($error_tmp == '') ? null : ' - ' . $error_tmp;
            }
        }

        return $warning;
    }

    /**
     * @return boolean
     */
    protected function createOrderStatuses()
    {
        $create = true;

        if (!(Configuration::get(self::_CONFIG_ORDER_AUTH) > 0)) {
            $orderState = new OrderState();
        } else {
            $orderState = new OrderState((int)Configuration::get(self::_CONFIG_ORDER_AUTH));
        }

        $orderState->name = array();
        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $orderState->name[$language['id_lang']] = 'Paiement autorisé PAYGREEN';
            } else {
                $orderState->name[$language['id_lang']] = 'Paiement authorized PAYGREEN';
            }
        }
        $orderState->module_name = $this->name;
        $orderState->send_email = false;
        $orderState->color = '#337ab7';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;

        if ($orderState->id > 0) {
            $orderState->save();
            $createOrder = true;
        } else {
            $createOrder = $orderState->add();
        }

        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/views/img/rsx/order_auth.gif')) {
            @copy(
                _PS_MODULE_DIR_ . $this->name . '/views/img/rsx/order_auth.gif',
                _PS_IMG_DIR_ . 'os/' . (int)$orderState->id . '.gif'
            );
        }

        if ($createOrder) {
            Configuration::updateValue(self::_CONFIG_ORDER_AUTH, $orderState->id);
        } else {
            $create = false;
        }


        if (!(Configuration::get(self::_CONFIG_ORDER_TEST) > 0)) {
            $orderState = new OrderState();
        } else {
            $orderState = new OrderState((int)Configuration::get(self::_CONFIG_ORDER_TEST));
        }

        $orderState->name = array();
        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $orderState->name[$language['id_lang']] = 'TEST - Paiement accepté';
            } else {
                $orderState->name[$language['id_lang']] = 'TEST - Accepted payment';
            }
        }
        $orderState->module_name = $this->name;
        $orderState->send_email = false;
        $orderState->color = '#D4EA62';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;

        if ($orderState->id > 0) {
            $orderState->save();
            $createOrder = true;
        } else {
            $createOrder = $orderState->add();
        }

        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/views/img/rsx/order_test.gif')) {
            @copy(
                _PS_MODULE_DIR_ . $this->name . '/views/img/rsx/order_test.gif',
                _PS_IMG_DIR_ . 'os/' . (int)$orderState->id . '.gif'
            );
        }
        if ($createOrder) {
            Configuration::updateValue(self::_CONFIG_ORDER_TEST, $orderState->id);
        } else {
            $create = false;
        }
        return $create;
    }

    /**
    * @return List of all buttons
    */
    private function getButtonsList()
    {
        if (Module::isInstalled($this->name)) {
            return Db::getInstance()->executeS(
                "SELECT * FROM " . _DB_PREFIX_ . 'paygreen_buttons ORDER BY position ASC'
            );
        }
        return array();
    }

    /**
     * If connected
     * @return boolean true if connected or false;
     */
    public function isConnected()
    {
        if (trim(Configuration::get(self::_CONFIG_PRIVATE_KEY)) == '') {
            return false;
        }
        if (trim(Configuration::get(self::_CONFIG_SHOP_TOKEN)) == '') {
            return false;
        }
        return true;
    }

    /**
     * If Preprod
     * @return true if on preprod else false
     */
    public function isPreprod()
    {
        return $this->preprod;
    }

    /**
     * Set values for inputs.
     */
    protected function getConfig()
    {
        $config = Configuration::getMultiple(
            array(
                self::_CONFIG_PRIVATE_KEY,
                self::_CONFIG_SHOP_TOKEN,
                self::_CONFIG_PAIEMENT_ACCEPTED,
                self::_CONFIG_PAIEMENT_REFUSED,
                self::_CONFIG_SHOP_INPUT_METHOD,
                self::_CONFIG_CANCEL_ACTION,
                self::_CONFIG_VISIBLE,
                self::_CONFIG_PAYMENT_REFUND,
                self::_CONFIG_FOOTER_DISPLAY,
                self::_CONFIG_FOOTER_LOGO_COLOR,
                self::_CONFIG_VERIF_ADULT
            )
        );

        if (empty($config[self::_CONFIG_PAIEMENT_ACCEPTED])) {
            $config[self::_CONFIG_PAIEMENT_ACCEPTED] = $this->l('Your payment was accepted');
        }
        if (empty($config[self::_CONFIG_PAIEMENT_REFUSED])) {
            $config[self::_CONFIG_PAIEMENT_REFUSED] = $this->l('Your payment unsuccessful');
        }
        if (empty($config[self::_CONFIG_SHOP_INPUT_METHOD])) {
            $config[self::_CONFIG_SHOP_INPUT_METHOD] = 'POST';
        }
        if (empty($config[self::_CONFIG_CANCEL_ACTION])) {
            $config[self::_CONFIG_CANCEL_ACTION] = 0;
        }
        if (empty($config[self::_CONFIG_VISIBLE])) {
            $config[self::_CONFIG_VISIBLE] = 0;
        }
        if (empty($config[self::_CONFIG_PAYMENT_REFUND])) {
            $config[self::_CONFIG_PAYMENT_REFUND] = 0;
        }
        if (empty($config[self::_CONFIG_FOOTER_LOGO_COLOR])) {
            $config[self::_CONFIG_FOOTER_LOGO_COLOR] = 'white';
        }

        return $config;
    }
    /**
     * Return the Unique ID for API (remove PP)
     * @return String $token
     */
    public function getUniqueIdPP()
    {
        $token = Configuration::get(self::_CONFIG_SHOP_TOKEN);
        if (in_array(Tools::substr($token, 0, 2), array('PP', 'LC'))) {
            return Tools::substr($token, 2);
        }
        return $token;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $output = $this->postProcess();

        if (Configuration::get('URL_BASE')==null) {
            Configuration::updateValue('URL_BASE', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }

        if (Tools::getValue('connect') == 'true' || Tools::getValue('code') != '') {
            if (extension_loaded('curl') || ini_get('allow_url_fopen')) {
                $this->auth();
            } else {
                $this->errorPaygreenApiClient((object) array('error' => 0));
            }
        }

        if (Tools::getValue('deconnect')) {
            if (Tools::getValue('deconnect') == 'true') {
                $this->logout();
            }
        }
        try {
            $buttonsList = $this->getButtonsList();
        } catch (Exception $ex) {
            return $this->displayError($this->l('Access to dataBase fail'));
        }
        array_push($buttonsList, $this->model_buttons);
        $len = count($buttonsList);
        foreach ($buttonsList as $key => $btn) {
            if ($key != $len - 1) {
                $buttonsList[$key]['error'] = $this->checkButton($btn) == ''
                ? null : $this->checkButton($btn);
            }
            $reduction = $btn['reductionPayment'];
            if ($reduction != "none" && $this->checkQuantityPerUser($this->idPromocode($reduction)) < 1000) {
                $buttonsList[$key]['warning'] = $this->l("Le code promo a une quantité inferieur a 1000 attention!");
            }
        }

        $infoShop='';
        $infoAccount='';
        if ($this->isConnected() && $this->paygreenValidIds()) {
            $infoShop = $this->infoShop();
            if ($infoShop != false) {
                if (!$this->errorPaygreenApiClient($infoShop)) {
                    $infoShop = $infoShop->data;
                }
            }
            $infoAccount = $this->infoAccount();
            if ($infoAccount != false) {
                $this->errorPaygreenApiClient($infoAccount);
            }
        }
        // Action on ConnectButton
        $this->context->smarty->assign(array(
            'prestashop' => Tools::substr(_PS_VERSION_, 0, 3),
            'module_dir' => $this->_path,
            'connected' => $this->isConnected(),
            'urlBase' => Configuration::get('URL_BASE') . "&connect=true",
            'urlBaseDeconnect' => Configuration::get('URL_BASE') . "&deconnect=true",
            'request_uri' => $_SERVER['REQUEST_URI'],
            'buttons' => $buttonsList,
            'promoCode' => $this->getAllPromoCode(),
            'infoShop' => $infoShop,
            'infoAccount' => $infoAccount,
            'imgdir' => $this->getImgDirectory("", true),
            'icondir' => $this->getIconDirectory("", true),
            // 'recurringPayments' => $this->getRecurringPayment(),
            'allowRefund' => Configuration::get(self::_CONFIG_PAYMENT_REFUND)
        ));

        // check version for templates
        $version = Tools::substr(_PS_VERSION_, 0, 3);

        $output .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/' . $version . '/connectApi.tpl'
        );
        $output .= $this->renderForm();
        $this->context->controller->addJS($this->local_path . 'views/js/' . $version . '/back.js');
        $this->context->controller->addCSS($this->local_path . 'views/css/' . $version . '/back.css');

        $output .= $this->context->smarty->fetch(
            $this->local_path . 'views/templates/admin/' . $version . '/configureButtons.tpl'
        );
        $version = Tools::substr(_PS_VERSION_, 0, 3);
        if ($version != 1.5) {
            $output .=$this->context->smarty->fetch(
                $this->local_path . 'views/templates/admin/' . $version . '/configureHook.tpl'
            );
        }
        return $output;
    }

    /**
     * use PaygreenApiClient
     * @return getStatusShop for id and private key
     */
    private function infoShop()
    {
        return PaygreenApiClient::getStatusShop(
            $this->getUniqueIdPP(),
            Configuration::get(self::_CONFIG_PRIVATE_KEY)
        );
    }

    /**
     * use PaygreenApiClient
     * @return getAccountInfos for id and private key
     */
    private function infoAccount()
    {
        return PaygreenApiClient::getAccountInfos(
            $this->getUniqueIdPP(),
            Configuration::get(self::_CONFIG_PRIVATE_KEY)
        );
    }

    private function logout()
    {
        Configuration::updateValue('_PG_CONFIG_PRIVATE_KEY', '');
        Configuration::updateValue('_PG_CONFIG_SHOP_TOKEN', '');
        $this->log('OAuth', 'Logout');
    }

    public function paygreenValidIds()
    {
        $validID = PaygreenApiClient::validIdShop(
            $this->getUniqueIdPP(),
            Configuration::get(self::_CONFIG_PRIVATE_KEY)
        );
        if ($this->errorPaygreenApiClient($validID)) {
            return false;
        }
        return $validID;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPaygreenModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfig(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     * config_verif_adult disabled.
     */
    protected function getConfigForm()
    {
        $config_cancel_action = $this->createRadio(
            self::_CONFIG_CANCEL_ACTION,
            $this->l('Action following a payment cancellation'),
            $this->l('Order cancellation '),
            $this->l('Generates a cancellation order and sends a cancellation email to the customer.'),
            $this->l('No action'),
            $this->l('Redirects the customer to your online store.'),
            'cancel_action_'
        );
        $config_visible = $this->createRadio(
            self::_CONFIG_VISIBLE,
            $this->l('Module\'s public visibility'),
            $this->l('Activated module'),
            $this->l('The module is visible by all.'),
            $this->l('Invisible module (TEST mode)'),
            $this->l('The module is visible only by administrator'),
            'config_visible'
        );

        if (Tools::substr(_PS_VERSION_, 0, 3) == 1.5) {
            $config_payment_refund = $this->createRadio(
                self::_CONFIG_PAYMENT_REFUND,
                $this->l('Refunds'),
                $this->l('Yes'),
                $this->l('Allows the administrator to refund a customer directly from prestashop\'s backoffice.'),
                $this->l('No'),
                $this->l(
                    'Does not allow the administrator to refund a customer directly from prestashop\'s backoffice.'
                ),
                'config_refund'
            );


            $config_footer_display = $this->createRadio(
                self::_CONFIG_FOOTER_DISPLAY,
                $this->l('Display PayGreen security badge'),
                $this->l('Yes'),
                $this->l('Displays a "payment secured by PayGreen" badge on your website\'s footer'),
                $this->l('No'),
                $this->l(' '),
                'display_logo'
            );

           /* $config_verif_adult = $this->createRadio(
                self::_CONFIG_VERIF_ADULT,
                $this->l('Check age of clients'),
                $this->l('Date of birth asked'),
                $this->l(' '),
                $this->l('Date of birth don\'t asked'),
                $this->l(' '),
                'display_verif_adult'
            );*/
        } else {
            $config_payment_refund = $this->createSwitch(
                self::_CONFIG_PAYMENT_REFUND,
                $this->l('Paygreen refund'),
                $this->l('Allow refund with paygreen by your back office prestashop'),
                'active'
            );

            $config_footer_display = $this->createSwitch(
                self::_CONFIG_FOOTER_DISPLAY,
                $this->l('Show PayGreen security'),
                $this->l('Display Paygreen Logo in your footer website'),
                'logo'
            );
            //Age of Client
            /*$config_verif_adult = $this->createSwitch(
                self::_CONFIG_VERIF_ADULT,
                $this->l('Check age of clients'),
                $this->l('Ask the date of birth before payment to check majority'),
                'display_verif_adult'
            );*/
        }
        $colors = array(
            array(
                'id_option' => 'white',
                'name' => $this->l('white')
            ),
            array(
                'id_option' => 'green',
                'name' => $this->l('green')
            ),
            array(
                'id_option' => 'black',
                'name' => $this->l('black')
            )
        );
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Payment system configuration'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Unique Identifier'),
                        'name' => self::_CONFIG_SHOP_TOKEN,
                        'size' => 33,
                        'required' => true,
                        'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                        'class' => 'fixed-width-xxl'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Private key'),
                        'name' => self::_CONFIG_PRIVATE_KEY,
                        'size' => 28,
                        'required' => true,
                        'placeholder' => 'xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                        'class' => 'fixed-width-xxl'
                    ),
                    array(
                        'type' => 'button',
                        'name' => 'connectApi',
                        'value' => 'Connect'
                    ),
                    $config_cancel_action,
                    array(
                        'type' => 'text',
                        'label' => $this->l('Displayed text after a successful payment'),
                        'name' => self::_CONFIG_PAIEMENT_ACCEPTED,
                        'size' => 150,
                        'required' => false,
                        'placeholder' => $this->l('Your payment was accepted'),
                        'class' => 'fixed-width-xxl'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Displayed text after a unsuccessful payment'),
                        'name' => self::_CONFIG_PAIEMENT_REFUSED,
                        'size' => 150,
                        'required' => false,
                        'placeholder' => $this->l('Your payment was unsuccessful'),
                        'class' => 'fixed-width-xxl'
                    ),
                    $config_visible,
                    $config_payment_refund,
                    $config_footer_display,
                    array(
                        'type' => 'select',
                        'label' => $this->l('Security badge color'),
                        'desc' => $this->l('Color of the "payment secured by PayGreen" badge in the footer '),
                        'name' => self::_CONFIG_FOOTER_LOGO_COLOR,
                        'options' => array(
                            'query' => $colors,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    //$config_verif_adult,
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right button'
                )
            ),
        );
    }

    /**
     *  Authentication and full private key and unique id
     */
    private function auth()
    {
        $libOAuth =  DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'OAuth2' . DIRECTORY_SEPARATOR ;
        require_once(dirname(__FILE__) . $libOAuth . 'Client.php');
        require_once(dirname(__FILE__) . $libOAuth . 'GrantType' . DIRECTORY_SEPARATOR . 'IGrantType.php');
        require_once(dirname(__FILE__) . $libOAuth . 'GrantType' . DIRECTORY_SEPARATOR . 'AuthorizationCode.php');


        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $email = $this->context->cookie->email;
        $name = Configuration::get('PS_SHOP_NAME');
        $oauth_access = Configuration::get('oauth_access');

        if (empty($oauth_access) || $oauth_access='null') {
            $datas = PaygreenApiClient::getOAuthServerAccess(
                $email,
                $name,
                null,
                $ipAddress
            );

            if (!$this->errorPaygreenApiClient($datas)) {
                $encodedData = Tools::jsonEncode($datas);
                Configuration::updateValue('oauth_access', $encodedData);
            }
        }
        $REDIRECT_URI = Configuration::get('URL_BASE');


        $CLIENT_ID = Tools::jsonDecode(Configuration::get('oauth_access'))->data->accessPublic;
        $CLIENT_SECRET = Tools::jsonDecode(Configuration::get('oauth_access'))->data->accessSecret;

        $client = new OAuthClient($CLIENT_ID, $CLIENT_SECRET, OAuthClient::AUTH_TYPE_AUTHORIZATION_BASIC);


        if (Tools::getValue('code') == null) {
            $auth_url = $client->getAuthenticationUrl(
                PaygreenApiClient::getOAuthAutorizeEndpoint(),
                $REDIRECT_URI
            );
            Tools::redirect($auth_url);
        } else {
            $params = array('code' => Tools::getValue('code'), 'redirect_uri' => Configuration::get('URL_BASE'));

            $response = $client->getAccessToken(
                PaygreenApiClient::getOAuthTokenEndpoint(),
                'authorization_code',
                $params
            );

            if ($response['result']['success'] == 1) {
                Configuration::updateValue('_PG_CONFIG_PRIVATE_KEY', $response['result']['data']['privateKey']);
                $pp = '';
                if ($this->isPreprod()) {
                    $pp = 'PP';
                }
                Configuration::updateValue('_PG_CONFIG_SHOP_TOKEN', $pp.$response['result']['data']['id']);
                $this->log('OAuth', 'Login');
            } else {
                $stringError = $this->l(
                    'There is a problem with the module PayGreen'.
                    ',please contact the technical supports support@paygreen.fr'
                );
                $this->context->controller->errors[] =  $stringError . ' : ' . $response['result']['message'];
            }

            Configuration::deleteByName('oauth_access');
        }
    }

    private function getShopUrl()
    {
        if (Configuration::get('PS_SSL_ENABLED')) {
            return 'https://'.$this->context->shop->domain.$this->context->shop->getBaseURI();
        } else {
            return $this->context->shop->getBaseURL();
        }
    }

    protected function postProcess()
    {
        //set at postion 1 all hook
        if (Tools::isSubmit('submitPaygreenModuleHook')) {
            $return = $this->updatePositionHook($this->hookName);
            if ($return === false) {
                return $this->displayError($this->l('Position hook failed.'));
            }
            return $this->displayConfirmation($this->l('hook at position 1'));
        }

        if (Tools::isSubmit('submitPaygreenModule')) {
            $form_values = $this->getConfig();
            $validators = $this->getFormValidator();
            foreach (array_keys($form_values) as $key) {
                $fieldValue = trim(Tools::getValue($key));
                if (isset($validators[$key])) {
                    foreach ($validators[$key] as $fn => $conf) {
                        if (!$this->{$fn.'Validator'}($fieldValue, $conf['params'])) {
                            return $this->displayError($conf['msg']);
                        }
                    }
                }
                Configuration::updateValue($key, $fieldValue);
            }
            PaygreenApiClient::setIds(
                Configuration::get(self::_CONFIG_SHOP_TOKEN),
                Configuration::get(self::_CONFIG_PRIVATE_KEY)
            );
            // Recompile the template for footer
            $this->context->smarty->clearCompiledTemplate();
            return $this->displayConfirmation($this->l('Datas saved'));
        }

        if (Tools::isSubmit('submitPaygreenModuleAccount')) {
            $activate = Tools::getValue('PS_PG_activate_account');
            $this->log('submitPaygreenModuleAccount-PS_PG_activate_account', $activate);
            $token = $this->getUniqueIdPP();

            $validated = PaygreenApiClient::validateShop(
                $token,
                Configuration::get(self::_CONFIG_PRIVATE_KEY),
                $activate
            );
            $this->errorPaygreenApiClient($validated);
        }

        if (Tools::isSubmit('submitPaygreenModuleButton')) {
            $modelButton = $this->model_buttons;
            $id = Tools::getValue('id', '');

            //Upload image
            if (array_key_exists('image', $_FILES) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES['image']['error'] > 0) {
                    return $this->displayError($this->l('Any image is sent'));
                }
                //Check upload file
                $fileInfo = pathinfo($_FILES['image']['name']);
                if (!in_array(Tools::strtolower($fileInfo['extension']), array('jpg', 'jpeg', 'png'))) {
                    return $this->displayError($this->l('Any image is sent'));
                }
                preg_match("/[a-zA-Z0-9_-]*/", $fileInfo['filename'], $matches);
                if ($matches[0] != $fileInfo['filename']) {
                    return $this->displayError($this->l('Any image is sent'));
                }

                $sImagePath = $this->getIconDirectory($_FILES['image']['name']);
                move_uploaded_file($_FILES["image"]["tmp_name"], $sImagePath);
                $modelButton['image'] = $_FILES['image']['name'];
            } else {
                unset($modelButton['image']);
            }

            foreach (array_keys($modelButton) as $key) {
                $value = Tools::getValue($key, $modelButton[$key]);
                //$this->log("addButton", 'value : ' . $key . ' -> ' . $value);
                if (!empty($value) && isset($value)) {
                    $modelButton[$key] = is_numeric($value)? ((int)$value): pSQL($value);
                }
            }
            unset($modelButton['id']);

            $this->log('addButton', $modelButton);
            if ((int)($modelButton['position']) <= 0) {
                try {
                    $btn = $this->getButtonsList();
                } catch (Exception $ex) {
                    return $this->displayError($this->l('Access to dataBase fail'));
                }
                $modelButton['position'] = (int)(count($btn) + 1);
            }

            if ($id > 0) {
                try {
                    Db::getInstance()->update('paygreen_buttons', $modelButton, 'id=' . ((int)$id));
                } catch (Exception $ex) {
                    return $display = $this->displayError($this->l('Access to DataBase fail'));
                }
                $display = $this->displayConfirmation($this->l('Datas saved'));
            } else {
                try {
                    Db::getInstance()->insert('paygreen_buttons', $modelButton);
                } catch (Exception $ex) {
                    return $display = $this->displayError($this->l('Access to DataBase fail'));
                }
                $display = $this->displayConfirmation($this->l('Datas saved'));
            }

            return $display;

            //return Db::getInstance()->Execute($request);
        }

        if (Tools::isSubmit('submitPaygreenModuleButtonDelete')) {
            $id = Tools::getValue('id', '');
            if ($id == "") {
                return $this->displayError($this->l('Action no possible'));
            }
            try {
                Db::getInstance()->delete('paygreen_buttons', 'id=' . ((int)$id));
            } catch (Exception $ex) {
                return $display = $this->displayConfirmation($this->l('Access to DataBase fail'));
            }
            return $this->displayConfirmation($this->l('Button ') . Tools::getValue('label') . $this->l(' deleted'));
        }

        return '';
    }

    private function generatePaiementData(
        $transactionId,
        $nbPaiement,
        $amount,
        $currency,
        $typePayment,
        $reportPayment,
        $percent = null
    ) {
        ceil(round($amount * 100) * $percent / 100);
        $paiement = $this->getCaller();
        $paiement->transaction(
            $transactionId,
            round($amount * 100),
            $currency
        );


        if ($nbPaiement > 1) {
            switch ($typePayment) {
                case 1:
                    $startAtReportPayment = ($reportPayment == 0) ? null : strtotime($reportPayment);

                    $paiement->subscribtionPaiement(
                        PaygreenClient::RECURRING_DAILY,
                        $nbPaiement,
                        date('d'),
                        $startAtReportPayment
                    );
                    break;
                case 3:
                    if ($percent != null && $percent > 0 && $percent < 100) {
                        $paiement->setFirstAmount(ceil(round($amount * 100) * $percent / 100));
                    }
                    $paiement->xTimePaiement($nbPaiement, $reportPayment);
                    break;
            }
        }
        return $paiement;
    }

    /**
     * use for hookPaymentOPtion for external payment
     */
    public function getExternalPaymentOption()
    {
        $a_externalOption = array();
        if ($this->verifyConfiguration() != '') {
            return false;
        }
        if ($this->getPaygreenConfig('type') != 'LC' && !$this->paygreenValidIds()) {
            return false;
        }

        //$config = $this->getConfig();

        if (!$this->isVisible()) {
            return false;
        }

        $cust = new Customer((int)$this->context->cookie->id_customer);
        $currency = new Currency((int)$this->context->cart->id_currency);

        try {
            $currentConfigureListButtons = $this->getButtonsList();
        } catch (Exception $ex) {
            $this->l('Access to dataBase fail');
            return false;
        }

        foreach ($currentConfigureListButtons as $btn) {
            //Test
            // SI false > continue
            if ($this->checkButton($btn) != '') {
                continue;
            }

            if (isset($btn['reductionPayment']) && $btn['reductionPayment'] != 'none') {
                $cart_rule = new CartRule($this->idPromocode($btn['reductionPayment']));
                $test = new Cart($this->context->cart->id);
                $test->addCartRule($cart_rule->id);
                $totalCart = $test->getOrderTotal();
                $test->removeCartRule($cart_rule->id);
                $this->resetQuantity($this->idPromocode($btn['reductionPayment']));
                if ($totalCart <= 0) {
                    $totalCart = $test->getOrderTotal();
                }
                $buttons = array();
                $this->log('hookDisplayPayment', $this->context->cart);
            } else {
                $totalCart = $this->context->cart->getOrderTotal();
                $buttons = array();
                $this->log('hookDisplayPayment', $this->context->cart);
            }
            if (isset($btn['minAmount'])) {
                if ($btn['minAmount'] > 0 && $totalCart < $btn['minAmount']) {
                    continue;
                }
            }

            if (isset($btn['maxAmount'])) {
                if ($btn['maxAmount'] > 0 && $totalCart > $btn['maxAmount']) {
                    continue;
                }
            }
            if (isset($btn['nbPayment']) && isset($btn['executedAt'])) {
                if (!isset($btn['reportPayment'])) {
                    $btn['reportPayment'] = 0;
                }
                $paiement = $this->generatePaiementData(
                    $this->context->cart->id,
                    $btn['nbPayment'],
                    $totalCart,
                    $currency->iso_code,
                    $btn['executedAt'],
                    $btn['reportPayment'],
                    $btn['perCentPayment']
                );
            }

            switch ($btn['executedAt']) {
                // At the delivery
                case -1:
                    $paiement->cardPrint();
                    break;
            }

            if (!isset($paiement)) {
                return false;
            }

            $paiement->customer(
                $cust->id,
                $cust->lastname,
                $cust->firstname,
                $cust->email
            );

            $paiement->cart_id = $this->context->cart->id;

            if (isset($btn['label'])) {
                $paiement->paiement_btn = $btn['label'];
            }

            if (isset($btn['reductionPayment']) && $this->checkPromoCode($btn['reductionPayment'])) {
                $paiement->reduction = $this->idPromocode($btn['reductionPayment']);
            } else {
                $paiement->reduction = 'none';
            }

            $paiement->return_cancel_url = $this->getShopUrl() . 'modules/paygreen/validation.php';
            $paiement->return_url = $this->getShopUrl() . 'modules/paygreen/validation.php';
            $paiement->return_callback_url = $this->getShopUrl() . 'modules/paygreen/notification.php';


            $address = new Address($this->context->cart->id_address_delivery);
            $paiement->shippingTo(
                $address->lastname,
                $address->firstname,
                $address->address1,
                $address->address2,
                $address->company,
                $address->postcode,
                $address->city,
                $address->country
            );

            $btn['debug'] = $paiement;
            $btn['paiement'] = array(
                'action' => $paiement->getActionForm(),
                'paiementData' => $paiement->generateData()
            );
            $buttons[] = $btn;

            //add new Buton payment
            $btnPayment = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $icondir = $this->getIconDirectory("", true);
            if ($btn['image'] == '') {
                $icondir .= 'paygreen_paiement1_7.png';
            } else {
                $icondir .= $btn['image'];
            }
            $logos = Media::getMediaPath($icondir);
            $taille = getimagesize($logos);
            $largeur = $taille[0] / ($taille[1] / 40);
            $this->context->smarty->assign(array(
                'largeur' => $largeur,
                'src' => $logos
            ));
                
            $btnPayment->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                        ->setInputs(array(
                            'action' => array(
                                'name' =>'action',
                                'type' =>'hidden',
                                'value' =>$btn['paiement']['action'],
                            ),
                            'paiement' => array(
                                'name' =>'paiement',
                                'type' =>'hidden',
                                'value' =>$btn['paiement']['paiementData'],
                            )
                        ));
            if ($btn['displayType'] == self::DISPLAYB_LABEL_LOGO) {
                $btnPayment ->setLogo($logos)
                            ->setCallToActionText($this->l($btn['label']));
            } elseif ($btn['displayType'] == self::DISPLAYB_LOGO) {
                $btnPayment->setLogo($logos);
            } elseif ($btn['displayType'] == self::DISPLAYB_LABEL) {
                $btnPayment->setCallToActionText($this->l($btn['label']));
            }

            $a_externalOption[(int)$btn['position']] = $btnPayment;
        }
        return $a_externalOption;
    }

    /**
     * display Form.
     */
    protected function generateForm()
    {
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            array_push($months, sprintf("%02d", $i));
        }
        $years = array();
        for ($i = 0; $i <= 10; $i++) {
            array_push(date($years, 'Y', strtotime('+'.$i.' years')));
        }
        $this->context->smarty->assign(array(
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'months' => $months,
            'years' => $years,
        ));
        return $this->context->smarty->fetch('module:paygreen/views/templates/front/payment_form.tpl');
    }

    /**
     * display PaygreenInsite Form.
     */
    protected function generateIframeForm($id, $totalCart, $paiement)
    {
        $this->context->smarty->assign(array(
            'id' => $id,
            'amount' => $totalCart,
            'data' => array(
                'action' => $paiement->getActionForm(),
                'paiementData' => urlencode($paiement->generateData())
            )
        ));

        return $this->context->smarty->fetch(
            'module:paygreen/views/templates/front/1.7/iframe.tpl'
        );
    }
    /**
     * check Currency of your card
     * @param Cart $cart
     */
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function errorPaygreenApiClient($var)
    {
        if (isset($var->error)) {
            switch ($var->error) {
                case 0:
                    $this->context->controller->warnings[] = $this->l(
                        'Curl must be installed to use automatic connexion.'.
                        'Please install Curl or enter you Ids mannualy.'
                    );
                    break;
                case 1:
                    $this->context->controller->errors[] = $this->l(
                        'There is an error in the unique id or the private key'
                    );
                    break;
            }
            return true;
        }

        return false;
    }

    public function validateWebPayment($a_data, $isCallback = false)
    {
        $this->log('validateWebPayment', $isCallback ? 'CALLBACK' : 'RETURN');

        if (!isset($a_data['data'])) {
            list($name, $value) = explode('=', $_SERVER['QUERY_STRING'], 2);
            if ($name != 'data' && !isset($value)) {
                return false;
            }
            $a_data['data'] = urldecode($value);
        }
        $config = $this->getConfig();

        $client = $this->getCaller();
        $client->parseData($a_data['data']);
        $f_amount = $client->amount / 100;
        $o_cart = new Cart($client->cart_id);
        if ($client->reduction != 'none') {
            $cart_rule = new CartRule($client->reduction);
            // Add cart rule to cart and in order
            $o_cart->addCartRule($cart_rule->id);
            $this->resetQuantity($this->idPromocode($client->reduction));
            $this->log("Add promo code", $cart_rule->id);
        }
        $o_customer = new Customer((int)$o_cart->id_customer);


        if ($client->result['status'] == PaygreenClient::STATUS_REFUSED) {
            $status = Configuration::get('PS_OS_ERROR');
        } elseif ($client->result['status'] == PaygreenClient::STATUS_CANCELLING) {
            $status = Configuration::get('PS_OS_CANCELED');
            $this->log('validateWebPayment', $client);
        } elseif ($client->result['status'] == PaygreenClient::STATUS_PENDING_EXEC) {
            $status = Configuration::get(self::_CONFIG_ORDER_AUTH);
            $f_amount = 0;
        } elseif ($client->result['status'] == PaygreenClient::STATUS_SUCCESSED) {
            $status = Configuration::get('PS_OS_PAYMENT');
        } else {
            if (!$isCallback) {
                $this->redirectToConfirmationPage();
            }
            return true;
        }

        if ($config[self::_CONFIG_CANCEL_ACTION] == 0 && $status == _PS_OS_CANCELED_) {
            //Tools::redirect('order');
            if (!$isCallback) {
                $this->redirectToConfirmationPage();
            }
            return true;
        }
        if ((int)($client->testMode) == 1 && $status == Configuration::get('PS_OS_PAYMENT')) { //_CONFIG_CANCEL_ACTION
            $status = Configuration::get(self::_CONFIG_ORDER_TEST);
            $f_amount = 0;
        }

        $a_vars = $client->result;
        $a_vars['date'] = time();
        $a_vars['transaction_id'] = $client->transaction_id;
        $a_vars['mode'] = $client->mode;
        $a_vars['amount'] = $client->amount;
        $a_vars['currency'] = $client->currency;
        $a_vars['by'] = 'webPayment';

        $n_order_id = (int)Order::getOrderByCartId($o_cart->id);

        if (!$this->isPaygreenSamePID($client->cart_id, $client->pid)) {
            //TODO when card_id have 2 paygreen_id print message on prestashop
            //throw new Exception();
        }

        if (!$n_order_id) {
            $this->validateOrder(
                $o_cart->id,
                $status,
                $f_amount,
                $this->displayName,
                $this->l('Transaction Paygreen') . ': ' . (int)$o_cart->id . ' (' . $client->paiement_btn . ')' .
                ((int)($client->testMode) == 1 ? '|/!\ ' . $this->l('WARNING transaction in TEST mode') . '/!\ ' : ''),
                $a_vars,
                null,
                false,
                $o_customer->secure_key
            );
            $n_order_id = (int)Order::getOrderByCartId((int)$o_cart->id);
            $this->log('validateWebPayment-validateOrder', $n_order_id);
            $isValidation = false;
        } else {
            $this->log('Order already exists => ', $n_order_id);
            $isValidation = true;
        }

        if ($n_order_id) {
            if ($isValidation == true && $isCallback == false) {
                $o_order =  $this->duplicateOrder($n_order_id);
            } else {
                $o_order = new Order($n_order_id);
            }
            $this->insertPaygreenTransaction(
                $n_order_id,
                $client,
                $o_cart->id,
                $o_order->current_state,
                $client->amount
            );

            $this->log('Id existant : ', $n_order_id);
            $this->log('validateWebPayment-order', $o_order);

            if ($o_order->current_state ==  Configuration::get('PS_OS_ERROR') &&
                    ($status == Configuration::get('PS_OS_PAYMENT')
                        || $status == Configuration::get(self::_CONFIG_ORDER_AUTH)
                    ) ||
                    ($o_order->current_state == Configuration::get(self::_CONFIG_ORDER_AUTH) &&
                        $status == Configuration::get('PS_OS_PAYMENT')
                    )
            ) {
                $this->log('validateWebPayment-orderHistory', $o_order->current_state . ' TO ' . $status);
                $o_order->setCurrentState($status);
                $o_order->save();
            }
            if (!$isCallback) {
                $b_err = !in_array($status, array(
                    _PS_OS_PAYMENT_,
                    Configuration::get(self::_CONFIG_ORDER_AUTH),
                    Configuration::get(self::_CONFIG_ORDER_TEST)
                ));
                $this->redirectToConfirmationPage($o_order, $b_err);
            } else {
                print_r(Tools::jsonEncode($o_order));
            }
        }

        return true;
    }

    protected function redirectToConfirmationPage($o_order = null, $b_error = false)
    {
        if ($o_order == null) {
            $link = new Link();
            $error_url = $link->getPageLink('order', null, null, array('step' => 3));
            Tools::redirect($error_url);
            return;
        }
        $a_query = array(
            'id_module' => $this->id,
            'id_cart' => $o_order->id_cart,
            'key' => $o_order->secure_key,
        );

        if ($b_error) {
            $a_query['error'] = 'Payment error';
        }

        Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?' . http_build_query($a_query));
    }

    public function duplicateOrder($id_order)
    {
        $new_ref = Tools::strtoupper(Tools::passwdGen(9, 'NO_NUMERIC'));
        $order = new Order($id_order);
        $duplicatedOrder = $order->duplicateObject();
        $date = new DateTime();
        $transaction = array();
        $transaction['date_add'] = pSQL($date->format('Y-m-d H:i:s'));
        $transaction['reference'] = $new_ref;
        try {
            Db::getInstance()->update('orders', $transaction, 'id_order=' . (int)$duplicatedOrder->id);
            $duplicatedOrder->date_add = pSQL($date->format('Y-m-d H:i:s'));
            $duplicatedOrder->reference = $new_ref;
        } catch (Exception $ex) {
            return false;
        }
        $orderDetailList = $order->getOrderDetailList();
        $this->log("orderdetaillist", $order->getOrderDetailList());
        foreach ($orderDetailList as $detail) {
            $orderDetail = new orderDetail($detail['id_order_detail']);
            $duplicatedOrderDetail = $orderDetail->duplicateObject();
            $duplicatedOrderDetail->id_order = $duplicatedOrder->id;
            $duplicatedOrderDetail->save();
        }

        $orderHistoryList = $order->getHistory(Configuration::get('PS_LANG_DEFAULT'));
        foreach ($orderHistoryList as $history) {
            $orderHistory = new OrderHistory($history['id_order']);
            var_dump($orderHistory);
            $duplicatedOrderHistory = $orderHistory->duplicateObject();
            var_dump($duplicatedOrderHistory);
            $duplicatedOrderHistory->id_order = $duplicatedOrder->id;
            $duplicatedOrderHistory->save();
        }
        var_dump($duplicatedOrder->getHistory(Configuration::get('PS_LANG_DEFAULT')));
        $this->log("duplicate order", $duplicatedOrder);
        return $duplicatedOrder;
    }


    /**
     * Insert paygreen transaction with id_order and pid
     * @param $id_order
     * @param $client
     * @param $id_cart
     * @param $current_state
     * @return true or false
     */
    protected function insertPaygreenTransaction($id_order, $client, $id_cart, $current_state, $amount)
    {
        $this->log("insertPaygreen pid", pSQL($client->pid));
        if ($this->isPaygreenSamePID($id_cart, $client->pid)) {
            return false;
        }
        $date = new DateTime();
        $paygreenTransaction = array();
        $paygreenTransaction['id_cart'] = ((int)$id_cart);
        $paygreenTransaction['pid'] = pSQL($client->pid);
        $paygreenTransaction['id_order'] = ((int)$id_order);
        $paygreenTransaction['state'] = pSQL($current_state);
        $paygreenTransaction['type'] = pSQL($client->mode);
        $paygreenTransaction['created_at'] = pSQL($date->format('Y-m-d H:i:s'));

        $this->log("InsertPaygreenTransaction", "New Transaction");
        if ($this->insertTransaction($paygreenTransaction)) {
            // INSERTION Of deadlines if recurring payment
            if ($client->mode == 'RECURRING') {
                return $this->insertPaygreenRecurringTransaction($id_cart, $current_state, $client->pid, $amount);
            }
        }
        return false;
    }

    /**
     * Insert paygreen recurring transaction
     * @param $id_card
     * @param $current_state
     */
    protected function insertPaygreenRecurringTransaction($id_cart, $current_state, $pid, $amount)
    {
        $id = ((int)$id_cart);
        try {
            $last_recurring_t_state = Db::getInstance()->getValue(
                'SELECT state FROM ' . _DB_PREFIX_ . 'paygreen_transactions
                WHERE id_cart=' . (int)$id . ' ORDER BY created_at DESC '
            );
        } catch (Exception $ex) {
            return false;
        }

        // State cancel : 6
        // state error payment : 8
        if ($last_recurring_t_state == 6 || $last_recurring_t_state == 8) {
            return false;
        }
        try {
            $rank = Db::getInstance()->getValue(
                'SELECT COUNT(rank) FROM ' . _DB_PREFIX_ . 'paygreen_recurring_transaction
                WHERE id=' . (int)$id
            );
        } catch (Exception $ex) {
            return false;
        }
        try {
            $isValidate = Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM ' . _DB_PREFIX_ .'paygreen_recurring_transaction
                WHERE pid=' . pSQL($pid)
            );
        } catch (Exception $ex) {
            return false;
        }

        if ($isValidate == 0) {
            $date = new DateTime();
            $recurringTransaction = array();
            $recurringTransaction['id'] = $id;
            $recurringTransaction['rank'] = $rank;
            $recurringTransaction['pid'] = pSQL($pid);
            $recurringTransaction['amount'] = $amount;
            $recurringTransaction['state'] = pSQL($current_state);
            $recurringTransaction['date_payment'] = pSQL($date->format('Y-m-d H:i:s'));
            try {
                Db::getInstance()->insert('paygreen_recurring_transaction', $recurringTransaction);
            } catch (Exception $ex) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
    * Update status of paygreen transaction
    * @param $id_order id order
    * @param $state of order
    */
    public function updatePaygreenTransactionStatus($id_order, $state)
    {
        $date = new DateTime();
        $transaction = array();
        $transaction['state'] = pSQL($state);
        $transaction['updated_at'] = pSQL($date->format('Y-m-d H:i:s'));
        try {
            Db::getInstance()->update('paygreen_transactions', $transaction, 'id_order=' . (int)$id_order);
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @param $id_order
     * @param $amount
     */
    public function paygreenRefundTransaction($id_order, $amount = null)
    {
        if (Configuration::get(self::_CONFIG_PAYMENT_REFUND) != 1) {
            return false;
        }

        $pid = $this->getPIDByOrder($id_order);
        if (empty($pid)) {
            return false;
        }

        if (!$this->isPaygreenPayment($pid)) {
            return false;
        }

        if ($this->isRefunded($id_order)) {
            return false;
        }
        $token = Configuration::get(self::_CONFIG_SHOP_TOKEN);
        if (Tools::substr($token, 0, 2) == 'PP') {
            $token = Tools::substr($token, 2);
        }
        //round($this->getTotalRefundByIdOrder($id_order), 2)
        $this->log('REFUND', array($pid, $amount));

        $refundStatus = PaygreenApiClient::refundOrder(
            $this->getUniqueIdPP(),
            Configuration::get(self::_CONFIG_PRIVATE_KEY),
            $pid,
            round($amount, 2)
        );
        $this->errorPaygreenApiClient($refundStatus);
        if (!$refundStatus) {
            $this->log('PaygreenTRansaction update State ', 'Transacton '. $pid .' NOT refunded');
            return false;
        }
        if (isset($refundStatus->success)) {
            if (!$refundStatus->success) {
                $this->log('PaygreenTRansaction update State ', 'Transacton '. $pid .' NOT refunded');
                return false;
            }
        }

        if (isset($amount)) {
            $order = new Order($id_order);
            if (round($this->getTotalRefundByIdOrder($id_order), 2)>=$order->total_paid) {
                $this->updatePaygreenTransactionStatus($id_order, 7);
            }
        } else {
            $this->updatePaygreenTransactionStatus($id_order, 7);
        }
        return true;
    }

    /**
     * Check if a payment was done with Paygreen
     * @param $pid
     * @return true or false
     */
    protected function isPaygreenPayment($pid)
    {
        $paygreen_pid = Db::getInstance()->getValue(
            'SELECT `pid`
            FROM  `' . _DB_PREFIX_ . 'paygreen_transactions`
            WHERE `pid` = "' . pSQL($pid) . '"'
        );
        return $paygreen_pid == $pid ? true : false;
    }

     /**
     *   Return Payment module name by id order
     * @param $id_order
     * @return name of payment module (ex:paygreen)
     */
    protected function getPaymentByOrder($idOrder)
    {
        if (!(int)$idOrder) {
            return false;
        }

        return Db::getInstance()->getValue(
            'SELECT `payment`
            FROM `' . _DB_PREFIX_ . 'orders`
            WHERE `id_order` = ' . (int)$idOrder . ';'
        );
    }


    public function getTotalRefundByIdOrder($id_order)
    {
        if (!(int)$id_order) {
            return false;
        }

        return Db::getInstance()->getValue(
            'SELECT SUM(`unit_price_tax_incl` * `product_quantity_refunded`) FROM '.
            _DB_PREFIX_.'order_detail WHERE `id_order` = ' . (int)$id_order . ';'
        );
    }

    /**
     * test if Paygreen Transaction exists
     * @param $id_cart
     * @param $id paygreen
     * @return true of false
     */
    protected function isPaygreenSamePID($id_cart, $pid)
    {

        $transacPid = Db::getInstance()->getValue(
            'SELECT pid FROM ' . _DB_PREFIX_ . 'paygreen_transactions
            WHERE id_cart=' . ((int)$id_cart) . ';'
        );

        if ($transacPid == $pid) {
            $this->log("isPaygreenTransacExist", "Same pid");
            return true;
        }

        return false;
    }

    protected function getFormValidator()
    {
        return array(
            self::_CONFIG_PRIVATE_KEY => array(
                'regexp' => array(
                    'params' => '^[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{12}$',
                    'msg' => $this->l('Your private key is wrong.')
                ),
            ),
            self::_CONFIG_SHOP_TOKEN => array(
                'regexp' => array(
                    'params' => '^(PP|LC)?[a-f0-9]{32}$',
                    'msg' => $this->l('Your unique Identifier is wrong.')
                ),
            ),
        );
    }

    protected function regexpValidator($value, $args)
    {
        $output_array = null;
        preg_match("/".$args."/i", $value, $output_array);
        return $output_array!=null && count($output_array)>0;
    }

    /**
     * @return path of your icon
     */
    private function getIconDirectory($image = "", $toView = false)
    {
        $baseDn = ($toView == true) ? $this->getShopUrl() . 'modules/paygreen' : dirname(__FILE__);
        $separator = ($toView == true) ? '/' : DIRECTORY_SEPARATOR;
        return implode($separator, array($baseDn, 'views', 'img', 'icons', $image));
    }

    /**
     * @return path of your image
     */
    private function getImgDirectory($image = "", $toView = false)
    {
        $baseDn = ($toView == true) ? $this->getShopUrl() . 'modules/paygreen' : dirname(__FILE__);
        $separator = ($toView == true) ? '/' : DIRECTORY_SEPARATOR;
        return implode($separator, array($baseDn, 'views', 'img', $image));
    }

    /**
     * @param $name id of your switch
     * @param $label
     * @param $desc
     * @param $id
     * @return switch buton
     */
    private function createSwitch($name, $label, $desc, $id)
    {
        return array(
            'type' => 'switch',
            'label' => $label,
            'name' => $name,
            'is_bool' => true,
            'desc' => $desc,
            'values' => array(
                array(
                    'id' => $id . '_on',
                    'value' => 1,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => $id . '_off',
                    'value' => 0,
                    'label' => $this->l('Disabled')
                )
            ),
        );
    }
    /**
     * @param $name id of your switch
     * @param $label
     * @param $label_yes
     * @param $p_yes
     * @param $label_no
     * @param $p_no
     * @param $id
     * @return radio button
     */
    private function createRadio($name, $label, $label_yes, $p_yes, $label_no, $p_no, $id)
    {
        return array(
            'type' => 'radio',
            'label' => $label,
            'name' => $name,
            'values' => array(
                array(
                    'id' => $id . '_yes',
                    'label' => $label_yes,
                    'value' => 1,
                    'p' => $p_yes,
                ),
                array(
                    'id' => $id . '_no',
                    'label' => $label_no,
                    'value' => 0,
                    'p' => $p_no,
                )
            ),
            'class' => 'fixed-width-xxl'
        );
    }

    /**
     * insert first buton
     */
    public function initializeDb($btn, $version)
    {
        if (count($btn) == 0) {
            $display = $version == 1.7 ? '1' : 'bloc';
            Db::getInstance()->insert('paygreen_buttons', array (
                'label' => pSQL($this->l('Pay by bank card')),
                'position' => (int) 1,
                'height' => (int) 60,
                'displayType' => pSQL($display),
                'nbPayment' => (int) 1
                ));
        }
        return true;
    }

    /**
     * @param sSqlString string
     * execute sql query
     */
    protected function executeSqlString($sSqlString)
    {

        $sSqlString = str_replace('%%PREFIX%%', _DB_PREFIX_, $sSqlString);

        $aQueries = preg_split('#;\s*[\r\n]+#', $sSqlString);

        foreach (array_filter(array_map('trim', $aQueries)) as $sQuery) {
            $mResult = Db::getInstance()->execute($sQuery);
            if ($mResult === false) {
                Logger::addLog('Query FAILED ' . $sQuery);
                return false;
            }
        }
        return true;
    }

    /**
     * @param $listHook array of name hook
     * set at 1st postion all hook present in $listhook
     */
    public function updatePositionHook($listHook)
    {
        $idPaygreen = Db::getInstance()->getValue(
            'SELECT id_module FROM '. _DB_PREFIX_ . 'module 
            WHERE name = \'paygreen\''
        );
        foreach ($listHook as $hook) {
            $id_hook = Db::getInstance()->getValue(
                'SELECT id_hook FROM '. _DB_PREFIX_ . 'hook 
                WHERE name = ' . '\'' . pSQL($hook) . '\''
            );
            $idModule = Db::getInstance()->getValue(
                'SELECT id_module FROM ' . _DB_PREFIX_ . 'hook_module
                WHERE position = 1 AND id_hook = ' . (int)$id_hook
            );
            $positionPaygreen = Db::getInstance()->getValue(
                'SELECT position FROM ' . _DB_PREFIX_ . 'hook_module
                WHERE id_hook = ' . (int)$id_hook . ' AND id_module = ' . (int)$idPaygreen
            );
            $pos = array();
            $pos['position'] = (int)$positionPaygreen;
            $updateModulePosition = Db::getInstance()->update(
                'hook_module',
                $pos,
                'id_module = ' . (int)$idModule . ' AND id_hook = ' . (int)$id_hook
            );
            if ($updateModulePosition === false) {
                Logger::addLog('Query FAILED ');
                return false;
            }

            $pos = array();
            $pos['position'] = (int)1;
            $updateModulePosition = Db::getInstance()->update(
                'hook_module',
                $pos,
                'id_module = ' . (int)$idPaygreen . ' AND id_hook = ' . (int)$id_hook
            );
            if ($updateModulePosition === false) {
                Logger::addLog('Query FAILED');
                return false;
            }
        }
        return true;
    }

    /**
     * @param $paygreenTransaction
     * insertTransaction in DataBase
     */
    private function insertTransaction($paygreenTransaction)
    {
        $valid = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'paygreen_transactions 
        WHERE  id_cart = ' . (int)$paygreenTransaction['id_cart'];
        try {
            $isValid = Db::getInstance()->getValue($valid);
        } catch (Exception $ex) {
            return false;
        }
        if ($isValid == 0) {
            try {
                Db::getInstance()->insert('paygreen_transactions', $paygreenTransaction);
                return true;
            } catch (Exception $ex) {
                return false;
            }
        } else {
            $validate = 'SELECT state FROM ' . _DB_PREFIX_ . 'paygreen_transactions 
            WHERE  id_cart = ' . (int)$paygreenTransaction['id_cart'];
            try {
                $isValidate = Db::getInstance()->getValue($validate);
            } catch (Exception $ex) {
                return false;
            }
            if (($isValidate == Configuration::get('PS_OS_ERROR') &&
                    ($paygreenTransaction['state'] == Configuration::get('PS_OS_PAYMENT')
                        || $paygreenTransaction['state'] == Configuration::get(self::_CONFIG_ORDER_AUTH)
                    )
                ) ||
                ($isValidate == Configuration::get(self::_CONFIG_ORDER_AUTH) &&
                    $paygreenTransaction['state'] == Configuration::get('PS_OS_PAYMENT')
                )
            ) {
                $state = array();
                $state['state'] = pSQL($paygreenTransaction['state']);
                $update = Db::getInstance()->update(
                    'paygreen_transactions',
                    $state,
                    'id_cart = ' . (int)$paygreenTransaction['id_cart']
                );
                if ($update === false) {
                    Logger::addLog('Query FAILED ');
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * check all butons
     * call checkButon for all butons
     */
    protected function checkButtons()
    {
        $warning = '';
        $error_tmp = '';
        $nb_error = 0;
        try {
            $btnList = $this->getButtonsList();
        } catch (Exception $ex) {
            return $this->l('Acess to dataBase Fail');
        }
        foreach ($btnList as $btn) {
            if ($this->checkButton($btn) != '') {
                $nb_error++;
                $error_tmp = $this->checkButton($btn);
            }
        }
        if ($nb_error > 1) {
            $warning .= ' - ' . 'There are '.$nb_error.' errors of button\'s configuration';
        } else {
            $warning .= ($error_tmp == '') ? null : ' - ' . $error_tmp;
        }
        return $this->l($warning);
    }

    /**
     * @param $btn buton
     * check property of your $btn
     */
    protected function checkButton($btn)
    {
        $error = '';
        if (!isset($btn['executedAt'])) {
            return $error;
        }
        $type = $btn['executedAt'];

        if (!isset($btn['nbPayment'])) {
            return $error;
        }
        $nbPayment = $btn['nbPayment'];

        if (!isset($btn['reportPayment'])) {
            return $error;
        }
        $report = $btn['reportPayment'];

        if (!isset($btn['perCentPayment'])) {
            return $error;
        }
        $percent = $btn['perCentPayment'];

        if (!isset($btn['subOption'])) {
            return $error;
        }
        $subOption = $btn['subOption'];

        if (!isset($btn['reductionPayment'])) {
            return $error;
        }
        $reduction = $btn['reductionPayment'];

        if ($nbPayment > 1) {
            // Cash payment
            if ($type == self::CASH_PAYMENT) {
                $error .= $this->l('The payment cash must be only once');
            } else {
                // At the delivery
                if ($type == self::DEL_PAYMENT) {
                    $error .= $this->l('The payment at delivery must be only once');
                }
            }
        } else {
            // Subscription payment
            if ($type == self::SUB_PAYMENT) {
                $error .= $this->l('The subscription payment must have more than one payment due');
                // Recurring payment
            } else {
                if ($type == self::REC_PAYMENT) {
                    $error .= $this->l('The recurring payment must have more than one payment due');
                }
            }
        }
        if ($report > 0) {
            // Cash payment
            if ($type == self::CASH_PAYMENT) {
                $error .= $this->l('The cash payment can\'t have a report payment');
            } elseif ($type == self::DEL_PAYMENT) {
                $error .= $this->l('The payment at the delivery can\'t have a report payment');
            } elseif ($type == self::REC_PAYMENT) {
                $error .= $this->l('The recurring payment can\'t have a report payment');
            }
        }
        if ($percent != 0) {
            if ($type == self::REC_PAYMENT) {
                if (!($percent > 0 && $percent < 100)) {
                    $error .= $this->l('The percent must be  between 1 and 99');
                }
            } else {
                $error .= $this->l('This option is only for recurring payment');
            }
        }
        if ($subOption == 1 && $type != self::SUB_PAYMENT) {
            $error .= $this->l('The option is only for subscription payment');
        }

        if ($reduction != 'none') {
            if (!$this->checkPromoCode($reduction)) {
                $error .= $this->l('The promo code is available');
            }
        }
        return $error;
    }

    public function checkPromoCode($code)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'cart_rule
            WHERE code=\''.pSQL($code) . '\'';
            return Db::getInstance()->getValue($sql) >= 1;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function getVerifyConfig()
    {
        $config = $this->getConfig();
        if ($config[self::_CONFIG_PRIVATE_KEY] != '' && $config[self::_CONFIG_SHOP_TOKEN] != '') {
            try {
                $buttonsList = $this->getButtonsList();
            } catch (Exception $ex) {
                return null;
            }
            array_push($buttonsList, $this->model_buttons);
            array_pop($buttonsList);
            $data = array (
                'Configuration' => $config,
                'Buttons' => $buttonsList
            );
            $encode = new PaygreenClient($config[self::_CONFIG_PRIVATE_KEY]);
            $encode->mergeData($data);
            return $encode->generateData();
        }
        return null;
    }

    public function checkApi()
    {
        if (!ini_get('allow_url_fopen')) {
            return $this->l('Acces to PaygreenApi fail.');
        }
        return '';
    }

    /**
     * Check if an order is already refunded
     * @param $id_order
     * @return true or false
     */
    protected function isRefunded($id_order)
    {

        $stateTransac = $this->getStateTransactionByIdOrder($id_order);

        // 7 for refund
        if ($stateTransac == 7 || $stateTransac == null) {
            return true;
        }
        return false;
    }

    /**
     *   Return state of transaction by the id order
     * @param $id_order
     * @return state or false if not exists
     */
    protected function getStateTransactionByIdOrder($id_order)
    {

        return Db::getInstance()->getValue(
            'SELECT state FROM ' . _DB_PREFIX_ . 'paygreen_transactions
    WHERE id_order=' . ((int)$id_order) . ';'
        );
    }

    /**
     *   Return state of transaction by the id order
     * @param $id_order
     * @return state or false if not exists
     */
    protected function getPIDByOrder($id_order)
    {

        return Db::getInstance()->getValue(
            'SELECT pid FROM ' . _DB_PREFIX_ . 'paygreen_transactions
    WHERE id_order=' . ((int)$id_order) . ';'
        );
    }

    public function idPromocode($code)
    {
        return Db::getInstance()->getValue(
            'SELECT id_cart_rule FROM ' . _DB_PREFIX_ . 'cart_rule 
        WHERE code =\'' . pSQL($code) .'\''
        );
    }

    public function resetQuantity($id_cart_rule)
    {
        $update = array();
        $quantity = $this->checkQuantityPerUser($id_cart_rule);

        $update['quantity'] = (int)$quantity + 1;
        return Db::getInstance()->update(
            'cart_rule',
            $update,
            'id_cart_rule = ' . (int)$id_cart_rule
        );
    }

    public function getAllPromoCode()
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule
        WHERE highlight=0';
        $array = DB::getInstance()->executeS($sql);
        $n_array = array();
        $n_array["none"] = $this->l('Aucune réduction');
        for ($i=0; $i <count($array); $i++) {
            $n_array[$array[$i]["code"]] =  $array[$i]["description"];
        }
        return $n_array;
    }

    public function checkQuantityPerUser($id_cart_rule)
    {
        $sql = 'SELECT quantity_per_user FROM ' . _DB_PREFIX_ . 'cart_rule
        WHERE id_cart_rule = ' . (int) ($id_cart_rule);
        var_dump($id_cart_rule);
        return Db::getInstance()->getValue($sql);
    }
}
