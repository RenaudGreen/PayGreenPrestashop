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
*  International Registered Trademark & Property of PrestaShop SA
*
*/

class PaygreenApiClient
{

    public static $UI ='';
    public static $CP ='';

    public static $HOST = null;

    public static function setIds($UI, $CP)
    {
        self::setUI($UI);
        self::setCP($CP);
    }

    public static function setUI($UI)
    {
        self::$UI = $UI;
    }

    public static function setCP($CP)
    {
        self::$CP = $CP;
    }

    public static function setHost($host) {
        if(empty($host)) {
            $host = 'https://paygreen.fr';
        }
        self::$HOST = $host.'/api';
    }

    /**
    * Return method and url by function
    *
    * @param $function
    * @param datas
    * @param $preprod
    */
    public static function getDatasByFunction($function, $datas = null)
    {
        $http = "Authorization: Bearer ".self::$CP;

        switch($function){

            case 'oAuth-access':
            $datas = array(
                'method' => 'POST',
                'url' => self::getOAuthDeclareEndpoint(),
                'http' => ''
            );
            break;

            case 'validate-shop':
            $datas = array(
                'method' => 'PATCH',
                'url' =>  self::getUrlProd().'/'.self::getUI().'/shop',
                'http' => $http
            );
            break;

            case 'refund':
            if (empty($datas['pid'])) {
                return false;
            }

            $datas = array(
                'method' => 'DELETE',
                'url' => self::getUrlProd().'/'.self::getUI().'/payins/transaction/'.$datas['pid'],
                'http' => $http
            );
            break;

            case 'get-datas':
            if (empty($datas['pid'])) {
                return false;
            }

            $datas = array(
                'method' => 'GET',
                'url' => self::getUrlProd().'/'.self::getUI().'/payins/transaction/'.$datas['pid'],
                'http' => $http
            );
            break;

            case 'are-valid-ids':
            $datas = array(
                'method' => 'GET',
                'url' =>  self::getUrlProd().'/'.self::getUI(),
                'http' => $http
            );
            break;

            case 'get-data':
            $datas = array(
                'method' => 'GET',
                'url' =>  self::getUrlProd().'/'.self::getUI().'/'.$datas['type'],
                'http' => $http
            );

            break;

            case 'validate-rounding':
            $datas = array(
                'method' => 'PATCH',
                'url' =>  self::getUrlProd().'/'.self::getUI().'/solidarity/'.$datas['paymentToken'],
                'http' => $http
            );

            break;

            case 'get-rounding':
            $datas = array(
                'method' => 'GET',
                'url' =>  self::getUrlProd().'/'.self::getUI().'/solidarity/'.$datas['paymentToken'],
                'http' => $http
            );

            break;

            case 'refund-rounding':
            $datas = array(
                'method' => 'DELETE',
                'url' =>  self::getUrlProd().'/'.self::getUI().'/solidarity/'.$datas['paymentToken'],
                'http' => $http
            );

            break;

            case 'create-cash':
                $datas = array(
                    'method' => 'POST',
                    'url' => self::getUrlProd().'/'.self::getUI().'/payins/transaction/cash',
                    'http' => $http
            );

            break;

            default:
                return false;
        }

        return $datas;
    }

    /**
    * Request to API paygreen
    *
    * @param $function function
    * @param array $datas datas in JSON
    * @return array JSON answer of request
    */
    public static function requestApi($function, $datas = null)
    {
        $datas_request = self::getDatasByFunction($function, $datas);
        $content = '';
        if (isset($datas['content'])) {
            $content = json_encode($datas['content']);
        }
        if (extension_loaded('curl'))
        {
             $ch = curl_init();

            curl_setopt_array($ch, array(
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
        } else if(ini_get('allow_url_fopen')) {

            $opts = array(
                'http'=>array(
                    'method'=>$datas_request['method'],
                    'header'=>"Accept: application/json\r\n" .
                    "Content-Type: application/json\r\n".
                    $datas_request['http'],
                    'content' => $content

                )
            );
            $context = stream_context_create($opts);
            $page = @file_get_contents($datas_request['url'], false, $context);
        } else {
            return (object)array('error' => 0);
        }
        if ($page === false) {
             return (object)array('error' => 1);
        }
        return json_decode($page);

    }

    /**
    * Get Status of the shop
    * @param string $UI unique id
    * @param string $CP private key
    * @return string json datas
    */
    public static function getStatusShop($UI, $CP)
    {
        self::IdsAreEmpty($UI, $CP);
        return self::requestApi('get-data', array('type'=>'shop'));
    }

    /**
    * Check if the unique if contains prefix preprod : PP
    * @param String $UI Unique Id
    * @return String $UI Unique Id
    */
    public static function getUI(){
        $UI = self::$UI;
        if(substr(self::$UI, 0, 2) == 'PP'){
            $UI =substr(self::$UI, 2);
        }
        return $UI;
    }

    public static function getTransactionInfo($UI, $CP, $pid) 
    {
        self::IdsAreEmpty($UI, $CP);
        return self::requestApi('get-datas', array('pid' => $pid));
    }
    /**
    * Get shop informations
    * @param string $UI unique id
    * @param string $CP private key
    * @return string json datas
    */
    public static function getAccountInfos($UI, $CP){
        self::IdsAreEmpty($UI, $CP);

        $infosAccount = array();

        $account = self::requestApi('get-data', array('type'=>'account'));
        if(self::isContainsError($account)){
            return $account->error;
        }
        if ($account==false) { return false; }
        $infosAccount['siret'] = $account->data->siret;

        $bank  = self::requestApi('get-data', array('type'=>'bank'));
        if(self::isContainsError($bank)){
            return $bank->error;
        }
        if ($bank==false) { return false; }
        $infosAccount['IBAN']  = $bank->data->iban;

        $shop = self::requestApi('get-data', array('type'=>'shop'));
        if(self::isContainsError($bank)){
            return $shop->error;
        }
        if ($shop==false) { return false; }
        $infosAccount['url']   = $shop->data->url;

        $infosAccount['valide'] = true;
        foreach($infosAccount as $key => $info){
            if(empty($info)){
                $infosAccount['valide'] = false;
            }
        }

        return $infosAccount;
    }

    /**
    * Get rounding informations for $paiementToken
    * @param string $UI unique id
    * @param string $CP private key
    * @param string $paiementToken paiementToken
    * @return string json datas
    */
    public static function getRoundingInfo($UI, $CP, $paiementToken)
    {
        self::IdsAreEmpty($UI, $CP);
        $transaction = self::requestApi('get-rounding', array('paymentToken' => $paiementToken));
        if(self::isContainsError($transaction)){
            return $transaction->error;
        }
        return $transaction;
    }

    public static function isContainsError($var){
        if(isset($var->error)){
            return true;
        }
        return false;
    }

    /**
    * To validate the shop
    *
    * @param string $UI unique id
    * @param string $CP private key
    * @param int $activate 1 or 0 to active the account
    * @return string json answer of false if activate != {0,1}
    */
    public static function validateShop($UI, $CP, $activate)
    {
        self::IdsAreEmpty($UI, $CP);
        if($activate!=1 && $activate!=0){
            return false;
        }
        $datas['content'] = array('activate'=>$activate);
        return self::requestApi('validate-shop',$datas);
    }

    /**
    * To check if private Key and Unique Id are valids
    *
    * @param string $UI unique id
    * @param string $CP private key
    * @return string json answer of false if activate != {0,1}
    */
    public static function validIdShop($UI, $CP)
    {
        self::IdsAreEmpty($UI, $CP);
        $valid = self::requestApi('are-valid-ids', null);

        if( $valid!=false ) {
            if(isset($valid->error)){
                return $valid;
            }
            if($valid->success==0) {
                return false;
            }
            return true;
        }
        return false;
    }

    public static function validateRounding($UI, $CP, $datas)
    {
        self::IdsAreEmpty($UI, $CP);
        $validate = self::requestApi('validate-rounding', $datas);

        if(self::isContainsError($validate)){
            return $validate->error;
        }
        return $validate;
    }



    public static function IdsAreEmpty($UI, $CP)
    {
        if(empty(self::$CP)){
            self::setCP($CP);
        }
        if(empty(self::$UI)){
            self::setUI($UI);
        }
    }

    /**
    * Refund an order
    *
    * @param string $UI unique id
    * @param string $CP private key
    * @param int $pid paygreen id of transaction
    * @param float $amount amount of refund
    * @return string json answer
    */
    public static function refundOrder($UI, $CP, $pid, $amount){
        self::IdsAreEmpty($UI, $CP);
        if(empty($pid)){
            return false;
        }

        $datas = array('pid'=>$pid);
        if ($amount!=null) {
            $datas['content'] = array('amount'=>$amount*100);
        }

        return self::requestApi('refund', $datas);
    }

    public static function refundRounding($UI, $CP, $datas)
    {
        self::IdsAreEmpty($UI, $CP);
        $refund = self::requestApi('refund-rounding', $datas);

        if(self::isContainsError($refund)){
            return $refund->error;
        }
        return $refund;
    }


    /**
    * Authentication to server paygreen
    *
    * @param string $email email of account paygreen
    * @param string $name name of shop
    * @param string $phone phone number, can be null
    * @param string $ipAdress ip Adress current, if null autodetect
    * @return string json datas
    */
    public static function getOAuthServerAccess($email, $name, $phone = null, $ipAddress = null){
        if (!isset($ipAddress)) { $ipAddress = $_SERVER['ADDR']; }
        $subParam = array(
            "ipAddress" => $ipAddress,
            "email" => $email,
            "name" => $name
        );
        $datas['content'] = $subParam ;

        return self::requestApi('oAuth-access', $datas);
    }

    /**
    * Return url of preprod or prod
    * @return string url
    */
    public static function getUrlProd() {
        return self::$HOST;
    }

    /**
    * return url of Authentication
    * @return string url of Authentication
    */
    public static function getOAuthDeclareEndpoint(){
        return self::getUrlProd().'/auth';
    }

    /**
    * return url of Authorization
    * @return string url of Authorization
    */
    public static function getOAuthAutorizeEndpoint(){
        return self::getUrlProd().'/auth/authorize';
    }

    /**
    * return url of auth token
    * @return string url of Authentication
    */
    public static function getOAuthTokenEndpoint(){
        return self::getUrlProd().'/auth/access_token';
    }
}
