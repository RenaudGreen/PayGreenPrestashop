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
* @author    PayGreen <contact@paygreen.fr>
* @copyright 2014-2014 Watt It Is
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop <SA></SA>
*
*/

class PaygreenApiClient
{
    private static $instance = null;

    private $UI = '';
    private $CP = '';
    private $HOST = null;

    public static function getInstance($UI = '', $CP = '', $HOST = null)
    {
        if (self::$instance === null) {
            self::$instance = new PaygreenApiClient($UI, $CP, $HOST);
        }
        return (self::$instance);
    }

    private function __construct($UI, $CP, $HOST)
    {
        $this->IdsAreEmpty($UI, $CP);
        $this->setHost($HOST);
    }

    /**
    * Authentication to server paygreen
    *
    * @param string $email email of account paygreen
    * @param string $name name of shop
    * @param string $phone phone number, can be null
    * @param string $ipAdress ip Adress current, if null autodetect
    * @return string json datas
    * 1
    */
    public static function getOAuthServerAccess($email, $name, $phone = null, $ipAddress = null)
    {
        if (!isset($ipAddress)) {
            $ipAddress = $_SERVER['ADDR'];
        }
        $subParam = array(
            "ipAddress" => $ipAddress,
            "email" => $email,
            "name" => $name
        );
        $datas['content'] = $subParam ;

        return self::$instance->requestApi('oAuth-access', $datas);
    }

    /**
    * 3
    * return url of Authorization
    * @return string url of Authorization
    */
    public static function getOAuthAutorizeEndpoint()
    {
        return self::$instance->getUrlProd().'/auth/authorize';
    }

    /**
    * 4
    * return url of auth token
    * @return string url of Authentication
    */
    public static function getOAuthTokenEndpoint()
    {
        return self::$instance->getUrlProd().'/auth/access_token';
    }

    /**
    * return url of Authentication
    * 2
    * @return string url of Authentication
    */
    private static function getOAuthDeclareEndpoint()
    {
        return self::$instance->getUrlProd().'/auth';
    }

    public function getTransactionInfo($pid) 
    {
        return $this->requestApi('get-datas', array('pid' => $pid));
    }

    /**
    * Get Status of the shop
    * @return string json datas
    */
    public function getStatusShop()
    {
        return $this->requestApi('get-data', array('type'=>'shop'));
    }

    /**
    * Refund an order
    *
    * @param int $pid paygreen id of transaction
    * @param float $amount amount of refund
    * @return string json answer
    */
    public function refundOrder($pid, $amount)
    {
        if (empty($pid)) {
            return false;
        }

        $datas = array('pid' => $pid);
        if ($amount != null) {
            $datas['content'] = array('amount' => $amount * 100);
        }

        return $this->requestApi('refund', $datas);
    }

    public static function sendFingerprintDatas($data) {
        $datas['content'] = $data;
        return $this->requestApi('send-ccarbone', $datas);
    }

    /**
    * To validate the shop
    *
    * @param int $activate 1 or 0 to active the account
    * @return string json answer of false if activate != {0,1}
    */
    public function validateShop($activate)
    {
        if ($activate != 1 && $activate != 0) {
            return false;
        }
        $datas['content'] = array('activate' => $activate);
        return $this->requestApi('validate-shop', $datas);
    }

    /**
    * To check if private Key and Unique Id are valids
    *
    * @return string json answer of false if activate != {0,1}
    */
    public function validIdShop()
    {
        $valid = $this->requestApi('are-valid-ids', null);

        if ($valid != false) {
            if (isset($valid->error)) {
                return $valid;
            }
            if ($valid->success == 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
    * Get shop informations
    * @return string json datas
    */
    public function getAccountInfos()
    {
        $infosAccount = array();

        $account = $this->requestApi('get-data', array('type'=>'account'));
        if ($this->isContainsError($account)) {
            return $account->error;
        }
        if ($account == false) {
            return false;
        }
        $infosAccount['siret'] = $account->data->siret;

        $bank  = $this->requestApi('get-data', array('type' => 'bank'));
        if ($this->isContainsError($bank)) {
            return $bank->error;
        }
        if ($bank == false) {
            return false;
        }
        $infosAccount['IBAN']  = $bank->data->iban;

        $shop = $this->requestApi('get-data', array('type'=> 'shop'));
        if ($this->isContainsError($bank)) {
            return $shop->error;
        }
        if ($shop == false) {
            return false;
        }
        $infosAccount['url'] = $shop->data->url;

        $infosAccount['valide'] = true;
        foreach ($infosAccount as $key => $info) {
            if (empty($info)) {
                $infosAccount['valide'] = false;
            }
        }
        return $infosAccount;
    }

    public function validDeliveryPayment($pid)
    {
        return $this->requestApi('delivery', array('pid' => $pid));
    }

    public function createCash($data)
    {
        return $this->requestApi('create-cash', $data);
    }

    public function createXTime($data)
    {
        return $this->requestApi('create-xtime', $data);
    }

    public function createSubscription($data)
    {
        return $this->requestApi('create-subscription', $data);
    }

    public function createTokenize($data)
    {
        return $this->requestApi('create-tokenize', $data);
    }

    /************************************************************
                        Private functions
    ************************************************************/

    /**
    * Check if the unique if contains prefix preprod : PP
    * @return string $UI Unique Id
    */
    private function getUI()
    {
        $UI = $this->UI;
        if (substr($this->UI, 0, 2) == 'PP') {
            $UI = substr($this->UI, 2);
        }
        return $UI;
    }

    /**
    * Return url of preprod or prod
    * @return string url
    */
    private function getUrlProd()
    {
        return $this->HOST;
    }

    /**
    * Check if error is defined in object
    * @param object $var
    * @return boolean
    */
    private function isContainsError($var)
    {
        if (isset($var->error)) {
            return true;
        }
        return false;
    }

    /**
    * Check if UI and CP are empty and set them
    * @param string $UI
    * @param string $CP
    */
    private function IdsAreEmpty($UI, $CP)
    {
        if (empty($this->CP)) {
            $this->CP = $CP;
        }
        if (empty($this->UI)) {
            $this->UI = $UI;
        }
    }

    /**
    * Set $host if empty
    * @param string $host
    */
    private function setHost($host = null)
    {
        if (empty($host)) {
            $host = 'https://paygreen.fr';
        }
        $this->HOST = $host.'/api';
    }

    /**
    * Return method and url by function name
    *
    * @param string $function
    * @param array $datas
    * @return object page
    */
    private function requestApi($function, $datas = null)
    {
        $http           = "Authorization: Bearer ".$this->CP;

        $lowerName      = strtolower($function);
        $function_name  = str_replace('-', '_', $lowerName);
        $datas_request  = $this->$function_name($datas, $http);
        
        $content        = '';
        if (isset($datas['content'])) {
            $content = json_encode($datas['content']);
        }
        if (extension_loaded('curl')) {
            $page = $this->request_api_curl($datas_request, $content);
        } elseif (ini_get('allow_url_fopen')) {
            $page = $this->request_api_fopen($datas_request, $content);
        } else {
            return ((object)array('error' => 0));
        }
        if ($page === false) {
            return ((object)array('error' => 1));
        }
        return json_decode($page);
    }

    private function request_api_curl($datas_request, $content)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            // CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_URL => $datas_request['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $datas_request['method'],
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                $datas_request['http'],
                "cache-control: no-cache",
                "content-type: application/json",
                ),
        ));
        $page = curl_exec($ch);
        curl_close($ch);
        return ($page);
    }

    private function request_api_fopen($datas_request, $content)
    {
        $opts = array(
            'http' => array(
                'method'    =>  $datas_request['method'],
                'header'    =>  "Accept: application/json\r\n" .
                "Content-Type: application/json\r\n".
                $datas_request['http'],
                'content'   =>  $content
            )
        );
        $context = stream_context_create($opts);
        $page = @file_get_contents($datas_request['url'], false, $context);
        return ($page);
    }

    /************************************************************
                Private functions called by requestApi
    ************************************************************/
    private function oauth_access($datas, $http)
    {
        return ($data = array(
            'method'    =>  'POST',
            'url'       =>  self::getOAuthDeclareEndpoint(),
            'http'      =>  ''
        ));
    }

    private function validate_shop($datas, $http)
    {
        return ($data = array (
            'method'    =>  'PATCH',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/shop',
            'http'      =>  $http
        ));
    }

    private function refund($datas, $http)
    {
        if (empty($datas['pid'])) {
            return (false);
        }
        return ($data = array (
            'method'    =>  'DELETE',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/'.$datas['pid'],
            'http'      =>  $http
        ));
    }

    private function are_valid_ids($datas, $http)
    {
        return ($data = array(
            'method'    =>  'GET',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI(),
            'http'      =>  $http
        ));
    }

    private function get_data($datas, $http)
    {
        return ($data = array (
            'method'    =>  'GET',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/'.$datas['type'],
            'http'      =>  $http
        ));
    }

    private function delivery($datas, $http)
    {
        return ($data = array(
            'method'    =>  'PUT',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/'.$datas['pid'],
            'http'      =>  $http
        ));
    }

    private function create_cash($datas, $http)
    {
        return ($data = array(
            'method'    =>  'POST',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/cash',
            'http'      =>  $http
        ));
    }

    private function create_subscription($datas, $http)
    {
        return ($data = array(
            'method'    =>  'POST',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/subscription',
            'http'      =>  $http
        ));
    }

    private function create_tokenize($datas, $http)
    {
        return ($data = array(
            'method'    =>  'POST',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/tokenize',
            'http'      =>  $http
        ));
    }

    private function create_xtime($datas, $http)
    {
        return ($data = array(
            'method'    =>  'POST',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/xTime',
            'http'      =>  $http
        ));
    }

    private function get_datas($datas, $http) {
        if (empty($datas['pid'])) {
            return false;
        }
        return ($data = array(
            'method'    =>  'GET',
            'url'       =>  $this->getUrlProd().'/'.$this->getUI().'/payins/transaction/'.$datas['pid'],
            'http'      =>  $http
        ));
    }

    private function send_ccarbone($datas, $http) {
        return ($data = array(
            'method' => 'POST',
            'url' => self::getUrlProd().'/'.self::getUI().'/payins/ccarbone',
            'http' => $http
        ));
    }    
}
