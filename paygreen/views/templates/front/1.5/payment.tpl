{*
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
*}
{foreach from=$buttons item=btn}

    {if $verify_adult ==1}
        <div id="checkAge_{$btn['id']|escape:'html':'UTF-8'}">
            <div class="payment_module">
                <img class="img-responsive"
                     src="{$icondir|escape:'html':'UTF-8'}{if $btn['image']==''}paygreen_paiement.png{else}{$btn['image']|escape:'html':'UTF-8'}{/if}"{if $btn['height']>0} height="{$btn['height']|escape:'html':'UTF-8'}"{/if}
                     alt="">
                <h4 class="subtile_img">{$btn['label']|escape:'html':'UTF-8'}</h4>

                <form id="form_button_{$btn['id']|escape:'html':'UTF-8'}">
                    <h4>{l s='Please indicate your date of birth to continue ' mod='paygreen'}
                        <small class="control-label">({l s='yyyy/mm/dd' mod='paygreen'})</small>
                    </h4>
                    <input type="text" class="date form-control" placeholder="{l s='yyyy/mm/dd' mod='paygreen'}">
                    <button type="button" data-id="{$btn['id']|escape:'html':'UTF-8'}"
                            class="checkAgeButton btn btn-default responsive-width">Check Age
                    </button>
                </form>

                <span id="stringAlert" class="hidden"
                      data-string-alert="{l s='You need to have 18 year to paid' mod='paygreen'}"></span>
                <span id="validStringAlert" class="hidden"
                      data-valid-string-alert="{l s='Enter a valid date please' mod='paygreen'}"></span>
            </div>
        </div>
    {/if}
    <span id="paygreen_button_{$btn['id']|escape:'html':'UTF-8'}" class="{if $verify_adult ==1}hidden{/if}">

        <p class="payment_module">
            <a href="javascript:document.getElementById('PayGreenCashForm{$btn['id']|escape:'html':'UTF-8'}').submit()"
               title="{l s='Pay by bank transfer' mod='paygreen'}">
               <img src="{$icondir|escape:'html':'UTF-8'}{if $btn['image']==''}paygreen_paiement1_6.png{else}{$btn['image']|escape:'html':'UTF-8'}{/if}"
                    style="{if $btn['height']>0}height:{$btn['height']|escape:'html':'UTF-8'}px; !important;"{/if}
                    alt="">
                {$btn['label']|escape:'html':'UTF-8'}
            </a>
        </p>
        <form id="PayGreenCashForm{$btn['id']|escape:'html':'UTF-8'}"
              action="{$btn.paiement.action|escape:'html':'UTF-8'}"
              method="post">
            <input type="hidden" name="data" value="{$btn.paiement.paiementData|escape:'html':'UTF-8'}"/>
            <input type="hidden" name="referer" value="prestashop"/>
        </form>
    </span>
{/foreach}
<style type="text/css">
    p.payment_module a.paygreen_button.cash {
        padding-left: 10px;
        background-image: none;
    }
</style>
