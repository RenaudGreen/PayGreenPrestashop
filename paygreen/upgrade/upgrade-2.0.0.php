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
 */

function upgrade_module_2_0_0($object)
{
    if (Tools::substr(_PS_VERSION_, 0, 3) == 1.7) {
        $object->registrerHook('payment');
    }
    $hookName =  array(
        'header',
        'displayPaymentReturn',
        'ActionObjectOrderSlipAddAfter',
        'actionOrderStatusPostUpdate',
        'displayFooter'
    );

    $version = Tools::substr(_PS_VERSION_, 0, 3);
    if ($version == 1.5 || $version == 1.6) {
        $hookName[] = 'displayBackOfficeHeader';
        $hookName[] = 'displayPayment';
    } else {
        $hookName[] =  'paymentOptions';
    }
    $object->updatePositionHook($hookName);
<<<<<<< HEAD
    Db::getInstance()->Execute('SET foreign_key_checks = 0');
=======
>>>>>>> iframe
    Db::getInstance()->Execute(
        'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'paygreen_recurring_transaction` (
        `id` int(11) NOT NULL,
        `rank` int(11) NOT NULL,
        `pid` varchar(250) NOT NULL,
        `amount` int(11) NOT NULL,
        `state` varchar(50) NOT NULL,
        `type` varchar(50) NOT NULL,
        `date_payment` date NOT NULL,
        CONSTRAINT `fk_rec_transac` FOREIGN KEY (`id`)REFERENCES `'._DB_PREFIX_.'paygreen_transactions` (`id_cart` )
        ON DELETE NO ACTION 
        ON UPDATE NO ACTION,
        PRIMARY KEY (`id`, `rank`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
    );
<<<<<<< HEAD
    Db::getInstance()->Execute('SET foreign_key_checks = 1');
=======
    Db::getInstance()->Execute(
        'ALTER TABLE '._DB_PREFIX_.'paygreen_buttons
        ADD [COLUMN] `integration` INT NOT NULL DEFAULT 0'
    );
>>>>>>> iframe
    return true;
}
