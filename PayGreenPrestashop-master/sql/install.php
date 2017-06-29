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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paygreen_buttons` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(100) NULL,
  `image` VARCHAR(45) NULL,
  `height` INT NULL,
  `position` INT NULL DEFAULT 1,
  `displayType` VARCHAR(45) NULL DEFAULT \'default \',
  `nbPayment` INT NOT NULL DEFAULT 1,
  `perCentPayment` INT NULL,
  `subOption` INT DEFAULT 0,
  `minAmount` DECIMAL(10,2) NULL,
  `maxAmount` DECIMAL(10,2) NULL,
  `executedAt` INT NULL DEFAULT 0,
  `reportPayment` VARCHAR(15) DEFAULT NULL,
  PRIMARY KEY (`id`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paygreen_transactions` (
  `id_cart` int(11) NOT NULL,
  `pid` varchar(250) NOT NULL,
  `id_order` int(11) NOT NULL,
  `state` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  PRIMARY KEY (`id_cart`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paygreen_recurring_transaction` (
  `id` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `pid` varchar(250) NOT NULL,
  `amount` int(11) NOT NULL,
  `state` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `date_payment` date NOT NULL,
  PRIMARY KEY (`id`, `rank`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
