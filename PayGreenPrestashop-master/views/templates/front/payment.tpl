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
	{if $btn['displayType'] != 'bloc'}
<div class="row">
	<div class="col-xs-12{if $btn['displayType'] == 'half'} col-md-6{/if}">
	{/if}
		<p class="payment_module">
			<a href="javascript:document.getElementById('PayGreenCashForm{$btn['id']|escape:'html':'UTF-8'}').submit()" title="{l s='Pay by bank transfer' mod='paygreen'}" class="paygreen_button{if $btn['displayType'] != 'bloc'} cash{/if}">
				<img src="{$icondir|escape:'html':'UTF-8'}{if $btn['image']==''}paygreen_paiement.png{else}{$btn['image']|escape:'html':'UTF-8'}{/if}"{if $btn['height']>0} height="{$btn['height']|escape:'html':'UTF-8'}"{/if} alt="">
					{$btn['label']|escape:'html':'UTF-8'}
			</a>
		</p>
	{if $btn['displayType'] != 'bloc'}
		</div>
	</div>
	{/if}
<form id="PayGreenCashForm{$btn['id']|escape:'html':'UTF-8'}" action="{$btn.paiement.action|escape:'html':'UTF-8'}" method="post">
	<input type="hidden" name="data" value="{$btn.paiement.paiementData|escape:'html':'UTF-8'}" />
	<input type="hidden" name="referer" value="prestashop" />
</form>
{/foreach}
<style type="text/css">
p.payment_module a.paygreen_button.cash {
	padding-left:10px;
	background-image:none;
}
</style>
