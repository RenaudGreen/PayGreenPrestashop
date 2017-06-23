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

const CASH = 0;
const SUB = 1;
const  REC = 3;
const DELIVERY = -1;


$(document).ready(function() {
    var selectList = document.querySelectorAll('[id=executedAt]');
    for (var k = 0;k < selectList.length; k++) {
        checkExecutedAt(selectList[k]);
        selectList[k].onchange = function() {
            checkExecutedAt(
                this
            );
        }
    }
});

function checkExecutedAt(select) {
    var temp = document.querySelectorAll('[id=executedAt]');
    var paymentDue = document.querySelectorAll('[id=nbPayment]');
    var help = document.querySelectorAll('[id=spanNbPayment]');
    var label = document.querySelectorAll('[id=labelNbPayment]');
    var paymentReport = document.querySelectorAll('[id=labelReport]');
    var selectReport = document.querySelectorAll('[id=reportPayment]');
    var paymentDue = document.querySelectorAll('[id=nbPayment]');
    var n;
    for (var i = 0;i < temp.length; ++i) {
        if (temp[i] == select) {
            n = i;
        }
    }
    if (select.value == SUB) {
        displayAllPayment(paymentDue[n], help[n], label[n], paymentReport[n], selectReport[n], "block", select.value);
    } else if (select.value == REC) {
        displayPaymentReport(paymentReport[n], selectReport[n], "none", select.value);
        displayPaymentDue(paymentDue[n], help[n], label[n], "block", select.value);
    } else {
        displayAllPayment(paymentDue[n], help[n], label[n], paymentReport[n], selectReport[n], "none", select.value);
    }
}

function displayAllPayment(paymentDue, help, label, paymentReport, selectReport, mode, value) {
    displayPaymentDue(paymentDue, help, label, mode, value);
    displayPaymentReport(paymentReport, selectReport, mode, value);
}

function displayPaymentDue(paymentDue, help, label, mode, value) {
    if (value == CASH || value == DELIVERY) {
        paymentDue.value = 1;
    }
    paymentDue.style.display = mode;
    help.style.display = mode;
    label.style.display = mode;
}

function displayPaymentReport(paymentReport, selectReport, mode, value) {
    if (value !=  SUB) {
        selectReport.value = "0";
    }
    paymentReport.style.display = mode;
    selectReport.style.display = mode;
}

function checkInstallments() {
    paymentDue = document.getElementById("nbPayment");
    alert(paymentDue.value);
}