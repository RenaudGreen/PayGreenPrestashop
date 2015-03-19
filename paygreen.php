<?php
/**
* 2007-2015 PrestaShop
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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class PayGreen extends PaymentModule
{
	const _CONFIG_PRIVATE_KEY = '_PG_CONFIG_PRIVATE_KEY';
	const _CONFIG_SHOP_TOKEN = '_PG_CONFIG_SHOP_TOKEN';


	const ERROR_TYPE_BUYER 		= 1;
	const ERROR_TYPE_MERCHANT	= 2;
	const ERROR_TYPE_UNKNOWN	= 3;

	const RS_VALID_SIMPLE  		= 'valid';
	const RS_VALID_WALLET  		= 'wallet';
	const RS_SUBSCRIBE_REDIRECT = 'rec_redirect';
	const RS_RECURRING_APPROVED = 'rec_approved';

	public $aErrors = null;

	protected $aResultSets = array(
		self::RS_VALID_SIMPLE		=> array(0),
		self::RS_VALID_WALLET 		=> array(0, 2500, 2501), /* valid or valid with warning */
		self::RS_SUBSCRIBE_REDIRECT => array(2319, 2306), /* 02319 Payment cancelled by Buyer - 02306 Operation in progress */
		self::RS_RECURRING_APPROVED	=> array(0, 2500, 2501, 4003), /* valid or valid with warning */
	);

	protected $aPaymentErrors = array(
		self::ERROR_TYPE_BUYER => array(
			1100 => 'Do not honor',
			1101 => 'Card expired',
			1103 => 'Contact your bank for authorization',
			1108 => 'Contact your bank for special condition',
			1111 => 'Invalid card number',
			1113 => 'Expenses not accepted',
			1117 => 'Invalid PIN code',
			1118 => 'Card not registered',
			1119 => 'This transaction is not authorized',
			1120 => 'Transaction refused by terminal',
			1121 => 'Debit limit exceeded',
			1200 => 'Do not honor',
			1201 => 'Card expired',
			1206 => 'Maximum number of attempts reached',
			1208 => 'Card lost',
			1209 => 'Card stolen',
			1915 => 'Transaction is refused',
			2302 => 'Transaction is invalid',
		),
		self::ERROR_TYPE_MERCHANT => array(
			1109 => 'Invalid merchant',
			1110 => 'Invalid amount',
			1114 => 'This account does not exist',
			1115 => 'This function does not exist',
			1116 => 'Amount limit',
			1122 => 'Security violation',
			1123 => 'Debit transaction frequency exceeded',
			1125 => 'Inactive card',
			1126 => 'Invalid PIN format',
			1128 => 'Invalid ctrl PIN key',
			1129 => 'Counterfeith suspected',
			1130 => 'Invalid cvv2',
			1180 => 'Invalid bank',
			1181 => 'Invalid currency',
			1182 => 'Invalid currency conversion',
			1183 => 'Max amount exceeded',
			1184 => 'Max uses exceeded',
			1199 => 'GTM Internal Error',
			1202 => 'Fraud suspected',
			1207 => 'Special condition',
			1280 => 'Card bin not authorized',
			1902 => 'Invalid transaction',
			1904 => 'Bad format request',
			1907 => 'Card provider server error',
			1909 => 'Bank server Internal error',
			1912 => 'Card provider server unknown or unavailable',
			1913 => 'Transaction already exist',
			1914 => 'Transaction can not be found',
			1915 => 'Transaction is refused',
			1917 => 'This transaction is not resetable',
			1940 => 'Bank server unavailable',
			1941 => 'Bank server communication error',
			1942 => 'Invalid bank server response code',
			1943 => 'Invalid format for bank server response',
			2101 => 'Internal Error',
			2102 => 'External server communication error',
			2103 => 'Connection timeout, please try later',
			2301 => 'Transaction ID is invalid',
			2303 => 'Invalid contract number',
			2304 => 'No transaction found for this token',
			2305 => 'Invalid field format',
			2306 => 'Token is still valid',
			2307 => 'Invalid custom page code',
			2308 => 'Invalid value for payment mode',
			2319 => 'Payment cancelled by the buyer',
			2324 => 'The session expired before the consumer has finished the transaction',
			2534 => 'The consumer is not redirected on payment web pages and session is expired',
			2533 => 'The consumer is not redirected on payment web pages'
		)
	);


	public function __construct()
	{
		$this->name = 'paygreen';
		$this->tab = $this->isOldVersion() ? 'Payment' : 'payments_gateways';
		$this->version = '1.1';
		$this->author = 'Watt Is It';
		$this->module_key = '0403f32afdc88566f1209530d6f6241c';

		$this->need_instance = 1;
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->is_eu_compatible = 1;

		$this->bootstrap = true;

		$this->warning = $this->verifyConfiguration();

		parent::__construct();

		$this->displayName = $this->l('PayGreen');
		$this->description = $this->l('Votre système de paiement solidaire PayGreen.');

		if($this->isOldVersion()) {
			require_once(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'..'._MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PaygreenClient.php');
		} else {
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PaygreenClient.php');

		}
	}

	private function isOldVersion() {
		return Tools::substr(_PS_VERSION_, 0, 3) <= 1.3;
	}

	public function getConfig()
	{
		return Configuration::getMultiple(array(self::_CONFIG_PRIVATE_KEY, self::_CONFIG_SHOP_TOKEN));
	}

	public function getCaller()
	{
		return new PaygreenClient($this->getConfig()[self::_CONFIG_PRIVATE_KEY]);
	}

	public function install()
	{
		return $this->isOldVersion()? $this->installOldVersion() : $this->installNewVersion();

	}

	private function installOldVersion() {
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	private function installNewVersion() {
		$this->warning = null;
		if (is_null($this->warning)
				&& !(parent::install()
				&& $this->registerHook('displayPayment')
				&& $this->registerHook('displayPaymentReturn')))
			$this->warning = $this->l('There was an Error installing the module.');

		$this->registerHook('displayPaymentEU');
		return is_null($this->warning);
	}

	protected function verifyConfiguration()
	{
		$config = $this->getConfig();

		if (empty($config[self::_CONFIG_PRIVATE_KEY]) || empty($config[self::_CONFIG_SHOP_TOKEN]))
		{
			$warning = $this->l('Paramètres manquants :');
			if (empty($config[self::_CONFIG_PRIVATE_KEY])) 	  	$warning .= $this->l(' - Clée privée');
			if (empty($config[self::_CONFIG_SHOP_TOKEN]))  	 	$warning .= $this->l(' - Identifiant unique');

			return $warning;
		}

		return '';
	}

	public function getContent()
	{
		$output = '<img src="'.__PS_BASE_URI__.'/modules/paygreen/views/img/paygreen.png" /><br />';

		if (Tools::isSubmit('submit'.$this->name))
		{
			Configuration::updateValue(self::_CONFIG_PRIVATE_KEY, trim(Tools::getValue(self::_CONFIG_PRIVATE_KEY, '')));
			Configuration::updateValue(self::_CONFIG_SHOP_TOKEN, trim(Tools::getValue(self::_CONFIG_SHOP_TOKEN, '')));
			$output .= $this->displayConfirmation($this->l('Données sauvegardées'));
		}

		if($this->isOldVersion()) {
			global $smarty;
			$smarty->assign([
				'header' => $output,
				'action' => $_SERVER['REQUEST_URI'],
				'config' => $this->getConfig(),
			]);
			return $this->fetchTemplate('/views/templates/admin/', 'config');
		}
		return $output.$this->displayForm().'<div class="text-center">client version '.PaygreenClient::VERSION.'</div>';
	}

	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Init Fields form array
		$fields_form = array();
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Configuration du système de paiement'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Clé privée'),
					'name' => self::_CONFIG_PRIVATE_KEY,
					'size' => 28,
					'required' => true,
					'placeholder' => 'xxxx-xxxx-xxxx-xxxxxxxxxxxx',
					'class' => 'fixed-width-xxl'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Identifiant unique'),
					'name' => self::_CONFIG_SHOP_TOKEN,
					'size' => 33,
					'required' => true,
					'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
					'class' => 'fixed-width-xxl'
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right'
			)
		);

		$helper = new HelperForm();

		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		$helper->title = $this->displayName;
		$helper->show_toolbar = true;		// false -> remove toolbar
		$helper->toolbar_scroll = true;	  // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		$helper->fields_value = $this->getConfig();

		return $helper->generateForm($fields_form);
	}

	public function validateWebPayment($aData)
	{
		if (!isset($aData['data']))
			return false; //$this->log('ERROR : not enough parameters');

		$client = $this->getCaller();
		$client->parseData($aData['data']);

		$fAmount = $client->amount / 100;

		$oCart = new Cart($client->transaction_id);
		$oCustomer = new Customer((int)$oCart->id_customer);

		if ($client->result['status'] == PaygreenClient::STATUS_REFUSED)
			$status = _PS_OS_ERROR_;

		else if ($client->result['status'] == PaygreenClient::STATUS_CANCELLING)
			$status = _PS_OS_CANCELED_;
		else
			$status = _PS_OS_PAYMENT_;

		$aVars = $client->result;
		$aVars['date'] = time();

		$nOrderId = (int)Order::getOrderByCartId($oCart->id);

		if (!$nOrderId)
		{
			$this->validateOrder(
				$oCart->id,
				$status,
				$fAmount ,
				$this->displayName,
				'Transaction Paygreen : '.(int)$oCart->id.' (web)',
				$aVars,
				null,
				false,
				$oCustomer->secure_key
			);
			$nOrderId = (int)Order::getOrderByCartId((int)$oCart->id);
			//die("No exists");
		}

		if ($nOrderId)
		{
			$oOrder = new Order($nOrderId);

			if ($oOrder->current_state != $status)
			{
				$history = new OrderHistory();
				$history->id_order = (int)$oOrder->id;
				$history->changeIdOrderState($status, (int)($oOrder->id)); //order status=3
			}

			$bErr = ($status == _PS_OS_ERROR_);

			$this->redirectToConfirmationPage($oOrder, $bErr);
		}
		return true;
	}

	protected function redirectToConfirmationPage($oOrder, $bError = false)
	{
		$aQuery = array(
				'id_module' => $this->id,
				'id_cart' 	=> $oOrder->id_cart,
				'key' 		=> $oOrder->secure_key,
		);

		if ($bError)
			$aQuery['error'] = 'Payment error';

		Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?'.http_build_query($aQuery));

	}

	protected function isResultInSet($sResult, $mResultSet = self::RS_VALID_SIMPLE)
	{
		if (is_array($mResultSet))
			$aSet = $mResultSet;
		else if (is_scalar($mResultSet) && isset($this->aResultSets[$mResultSet]))

			$aSet = $this->aResultSets[$mResultSet];
		else
			$aSet = array();

		return in_array($sResult, $aSet, true);

	}

	public function getErrorDescription($sErrorCode)
	{
		if ($this->isResultInSet($sErrorCode, array_keys($this->aPaymentErrors[self::ERROR_TYPE_BUYER])))
			return $this->l('Buyer error').' ['.$sErrorCode.']'.$this->getL($this->aPaymentErrors[self::ERROR_TYPE_BUYER][$sErrorCode]);
		else if ($this->isResultInSet($sErrorCode, array_keys($this->aPaymentErrors[self::ERROR_TYPE_MERCHANT])))
			return $this->l('Merchant error').' ['.$sErrorCode.']'.$this->getL($this->aPaymentErrors[self::ERROR_TYPE_MERCHANT][$sErrorCode]);
		else
			return $this->l('Unknown error').' ['.$sErrorCode.']'.sprintf($this->l('Payment error %s'), $sErrorCode);
	}

	public function hookPayment()
	{
		if($this->isOldVersion()) {
			global $cookie, $cart, $shop, $smarty;
			$baseURL = _PS_BASE_URL_.__PS_BASE_URI__;
		} else {
			$cookie = $this->context->cookie;
			$cart = $this->context->cart;
			$baseURL = $this->context->shop->getBaseURL();
			$smarty = $this->context->smarty;
		}
		$cust = new Customer((int)$cookie->id_customer);
		$currency = new Currency((int)$cart->id_currency);

		$cash = $this->getCaller();
		$cash->setToken($this->getConfig()[self::_CONFIG_SHOP_TOKEN]);

		$cash->customer($cust->id, $cust->lastname, $cust->firstname, $cust->email);
		$cash->immediatePaiement($cart->id, round($cart->getOrderTotal() * 100), $currency->iso_code);
		$cash->return_cancel_url = $baseURL.'modules/paygreen/validation.php';
		$cash->return_url = $baseURL.'modules/paygreen/validation.php';
		$cash->return_callback_url = $baseURL.'modules/paygreen/notification.php';

		$smarty ->assign([
			'cash' => [
				'action' => $cash->getActionForm(),
				'paiementData' => $cash->generateData()
			]
		]);
		return $this->fetchTemplate('/views/templates/front/', 'cash_paiement');

	}

	public function hookPaymentReturn()
	{
		if($this->isOldVersion()) {
			global $smarty;
		} else {
			$smarty = $this->context->smarty;
		}
		$smarty->assign(array('error' =>  Tools::getValue('error') ?  Tools::getValue('error') : 0));
		return $this->fetchTemplate('/views/templates/front/', 'payment_return');
	}

	public function fetchTemplate($s_path, $s_name)
	{
		$s_template_path = ltrim($s_path.Tools::substr(_PS_VERSION_, 0, 3).'/'.$s_name.'.tpl', DIRECTORY_SEPARATOR);
		return $this->display(__FILE__, $s_template_path);
	}
}