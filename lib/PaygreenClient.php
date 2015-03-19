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

class PaygreenClient
{
    const VERSION = '0.1B';
    const CURRENCY_EUR = 'EUR';


    const STATUS_WAITING = "WAITING";
    const STATUS_PENDING = "PENDING";
    const STATUS_CANCELLING = "CANCELLED";
    const STATUS_REFUNDED = "REFUNDED";
    const STATUS_RESETED = "RESETED";
    const STATUS_FAILED = "FAILED";
    const STATUS_SUCCESSED = "SUCCESSED";
    const STATUS_REFUSED = "REFUSED";


    private static $host = "https://paygreen.fr/paiement/new/";

    private $token;
    private $key;
    protected $data = array();

    public function __construct($encryptKey, $rootUrl = null)
    {
        $this->key = $encryptKey;

        if($rootUrl != null)
            self::$host =  $rootUrl.'/paiement/new/';
    }

    public function privateKey($encryptKey)
    {
        $this->key = $encryptKey;
    }

    public function setToken($shopToken)
    {
        $this->token = base64_encode(time().":".$shopToken);
    }

    public function parseToken($token)
    {
        $this->token = $token;
        return split(':',base64_decode($token));
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function toArray()
    {
        return $this->data;
    }

    public function mergeData($data)
    {
      $this->data = array_merge($this->data, $data);
    }



    public function parseData($post)
    {
        $text = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $this->key, base64_decode($post), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
        $this->data = json_decode(utf8_decode($text), true);
    }

    public function generateData()
    {
        $text = utf8_encode(json_encode($this->data));
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $this->key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

    public function getActionForm()
    {
        return self::$host.$this->token;
    }

    public function renderForm()
    {
        ?>
        <form method="post" action="<?php echo $this->getActionForm(); ?>">
            <input type="hidden" name="data" value="<?php echo $this->generateData(); ?>?>" />
            <input type="submit" value="Payer" />
        </form>
        <?php
    }

    public function customer($id, $last_name, $first_name, $email, $country = "FRA")
    {
        $this->customer_id = $id;
        $this->customer_last_name = $last_name;
        $this->customer_first_name = $first_name;
        $this->customer_email = $email;
        $this->customer_country = $country;
    }


    public function immediatePaiement($transactionId, $amount, $currency = "EUR")
    {
        $this->transaction_id = $transactionId;

        $this->amount = $amount;
        $this->currency = $currency;
    }
}