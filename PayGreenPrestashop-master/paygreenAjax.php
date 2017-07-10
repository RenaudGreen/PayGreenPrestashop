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
*/

require_once implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', '..', 'config', 'config.inc.php'));
require_once implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', '..', 'init.php'));
require_once implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'paygreen.php'));

class API
{
    public function __construct()
    {
        $paygreen = new Paygreen();
        $this->routes();
    }
   
    private function routes()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'POST') {
            $data = $_POST;
            $this->setClientFingerprint($data);
        }
    }

    private function checkDatas($data) {
        if (isset($data['client']) && !empty($data['client']) &&
            isset($data['startAt']) && !empty($data['startAt']) &&
            isset($data['useTime']) && !empty($data['useTime']) &&
            isset($data['nbImage']) && !empty($data['nbImage'])) {
                return true;
            }
        return false;
    }

    private function setClientFingerprint($data)
    {
        if (isset($data) && !empty($data) && $this->checkDatas($data) == true) {
            $paygreen = new Paygreen();
            $paygreen->insertFingerprintDetails($data);
        } else {
            $data = array('error' => 'required parameters not given', 'data' => $data);
            header('Content-Type: application/json');
            echo json_encode($data);
        }
    }
}

$api = new API();
