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

$(document).ready(function() {
    var selectlist = document.querySelectorAll('[id=executedAt]');
    for (var k = 0;k < selectlist.length; k++) {
        checkExecutedAt(selectlist[k]);
        selectlist[k].onchange = function() {
        checkExecutedAt(
            this
        );
    }
    }
});

function checkExecutedAt(select) {
    var temp = document.querySelectorAll('[id=executedAt]');
    var paymentDue = document.querySelectorAll('[id=nbPayment]');
    var labelPaymentDue = document.querySelectorAll('[id=labelNbPayment]');
    var labelReport = document.querySelectorAll('[id=labelReport]');
    var reportPayment = document.querySelectorAll('[id=reportPayment]');
    var labelPercent = document.querySelectorAll('[id=labelPerCentPayment]');
    var labelSubOption = document.querySelectorAll('[id=labelSubOption]');
    var checkbox = document.querySelectorAll('[id=subOption]');
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
    if (select.value == 1) {
/*<<<<<<< HEAD
        displayAllPayment(labelPaymentDue[n], labelReport[n], "block");
        displayPerCentPayment(labelPercent[n], "none");
        displaySubOption(labelSubOption[n], "block");
    } else if (select.value == 3) {
        displayPaymentReport(labelReport[n], "none");
        displayPaymentDue(labelPaymentDue[n], "block");
        displayPerCentPayment(labelPercent[n], "block");
        displaySubOption(labelSubOption[n], "none");
        reportPayment[n].value = 0;
        checkbox[n].checked = false;
    } else {
        displayAllPayment(labelPaymentDue[n], labelReport[n], "none");
        displayPerCentPayment(labelPercent[n], "none");
        displaySubOption(labelSubOption[n], "none");
        paymentDue[n].value = 1;
        reportPayment[n].value = 0;
        checkbox.checked = false;
    }
}

function displaySubOption(labelSubOption, mode) {
    labelSubOption.parentNode.style.display = mode;
}

function displayPerCentPayment(labelPercent, mode) {
    labelPercent.parentNode.style.display = mode;
}

function displayAllPayment(labelPaymentDue, labelReport, mode) {
    displayPaymentDue(labelPaymentDue, mode);
    displayPaymentReport(labelReport, mode);
}

function displayPaymentDue(labelPaymentDue, mode) {
    labelPaymentDue.parentNode.style.display = mode;
}

function displayPaymentReport(labelReport, mode) {
    labelReport.parentNode.style.display = mode;
}
=======*/
        displayAllPayment(paymentDue[n], help[n], label[n], paymentReport[n], selectReport[n], "block");
    } else if (select.value == 3) {
        displayPaymentReport(paymentReport[n], selectReport[n], "none");
        displayPaymentDue(paymentDue[n], help[n], label[n], "block");
    } else {
        displayAllPayment(paymentDue[n], help[n], label[n], paymentReport[n], selectReport[n], "none");
    }
}

function displayAllPayment(paymentDue, help, label, paymentReport, selectReport, mode) {
    displayPaymentDue(paymentDue, help, label, mode);
    displayPaymentReport(paymentReport, selectReport, mode);
}

function displayPaymentDue(paymentDue, help, label, mode) {
    paymentDue.style.display = mode;
    help.style.display = mode;
    label.style.display = mode;
}

function displayPaymentReport(paymentReport, selectReport, mode) {
    paymentReport.style.display = mode;
    selectReport.style.display = mode;
}

function checkInstallments() {
    paymentDue = document.getElementById("nbPayment");
    alert(paymentDue.value);
}

