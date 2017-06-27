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

var checkAge = {
    init:function() {
        $('.checkAgeButton').on('click', this.check);
    },
    check : function(){

        var id = $(this).data("id");

        var datetime   = $('#form_button_'+id+' input.date').val();
        var format = $('.date').attr('placeholder');

        if(format=='yyyy/mm/dd'){
            var validDate=/^[0-9]{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/;
        }else{
            var validDate=/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/;
        }

        if (!datetime || datetime==null || !datetime.match(validDate)){
            var validStringAlert = $('#validStringAlert').data('validStringAlert');
            return alert(validStringAlert);
        }

        var date  = datetime.split('/');

        if(date.length!=3){
            return false;
        }
        var day   = date[0];
        var month = date[1];
        var year  = date[2];
        var maxDate = new Date();

        var error = 0 ;
        if ( day > 31 || day < 1)                         { error=1; }
        if ( month > 12 || month < 1)                     { error=1; }
        if ( year > maxDate.getFullYear() || year < 1900) { error=1; }

        var mydate = new Date();
        mydate.setFullYear(year, month - 1, day);
        var maxDate = new Date();
        maxDate.setYear(maxDate.getYear() - 18);

        if (maxDate < mydate) {
            var stringAlert = $('#stringAlert').data('stringAlert');
            alert(stringAlert);
        }else{
            checkAge.affiche(id);
        }

    },
    affiche: function(id){
        $('#checkAge_'+id).addClass('hidden');
        $('#paygreen_button_'+id).removeClass('hidden');
    }
}
$( document ).ready(function() {
    checkAge.init();
});

