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
	{if $btn['displayType'] != 'bloc'}
	<div class="row">
		<div class="col-xs-12{if $btn['displayType'] == 'half'} col-md-6{/if}">
			{/if}
			<div id="checkAge_{$btn['id']|escape:'html':'UTF-8'}" class="row vertical-align border ">
				<div class="col-lg-5 col-md-4 col-sm-4 col-xs-4 payment_module">
					<img class="img-responsive" src="{$icondir|escape:'html':'UTF-8'}{if $btn['image']==''}paygreen_paiement1.6.png{else}{$btn['image']|escape:'html':'UTF-8'}{/if}"{if $btn['height']>0} height="{$btn['height']|escape:'html':'UTF-8'}"{/if} alt="">
					<h4 class="subtile_img">{$btn['label']|escape:'html':'UTF-8'}</h4>
				</div>

				<div class="col-lg-7 col-md-8 col-sm-8 col-xs-8 payment_module">
					<form class="truc form-inline" id="form_button_{$btn['id']|escape:'html':'UTF-8'}">
						<div class="row ">
							<h4 >{l s='Please indicate your date of birth to continue ' mod='paygreen'} <small class="control-label">({l s='yyyy/mm/dd' mod='paygreen'})</small></h4>
							<div class="form-group col-xs-6 col-lg-7 col-md-7 col-sm-7">
								<input type="text" class="date form-control" placeholder="{l s='yyyy/mm/dd' mod='paygreen'}">
							</div>
							<div class="col-xs-6 col-lg-5 col-md-5 col-sm-5">
								<button type="button" data-id="{$btn['id']|escape:'html':'UTF-8'}" class="checkAgeButton btn btn-default responsive-width">Check Age</button>
							</div>
						</div>
					</form>
					<span id="stringAlert" class="hidden" data-string-alert="{l s='You need to have 18 year to paid' mod='paygreen'}"></span>
					<span id="validStringAlert" class="hidden" data-valid-string-alert="{l s='Enter a valid date please' mod='paygreen'}"></span>
				</div>
			</div>
			{if $btn['displayType'] != 'bloc'}
		</div>
	</div>
	{/if}
	{/if}
	<span  id="paygreen_button_{$btn['id']|escape:'html':'UTF-8'}" class="{if $verify_adult ==1}hidden{/if}">


		{if $btn['displayType'] != 'bloc'}
		<div class="row">
			<div class="col-xs-12{if $btn['displayType'] == 'half'} col-md-6{/if}">
				{/if}
				<p class="payment_module">
					<a href="javascript:document.getElementById('PayGreenCashForm{$btn['id']|escape:'html':'UTF-8'}').submit()" title="{$btn['label']|escape:'html':'UTF-8'}" class="paygreen_button{if $btn['displayType'] != 'bloc'} cash{/if}">
						<img src="{$icondir|escape:'html':'UTF-8'}{if $btn['image']==''}paygreen_paiement1_6.png{else}{$btn['image']|escape:'html':'UTF-8'}{/if}"{if $btn['height']>0} height="{$btn['height']|escape:'html':'UTF-8'}"{/if} alt="">
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
	</span>
	{/foreach}
	<style type="text/css">
	p.payment_module a.paygreen_button.cash {
		padding-left:10px;
		background-image:none;
	}
	</style>
