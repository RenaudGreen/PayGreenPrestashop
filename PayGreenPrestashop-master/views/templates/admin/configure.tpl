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
<div class="panel">
	<div class="panel-heading">
		<i class="icon-image"></i> {l s='Configuration payment buttons' mod='paygreen'}
	</div>
	<div class="row">
	{foreach $buttons as $btn}
		<div id="formNewButton" class="col-sm-6 col-md-4">
			<form class="form-horizontal" action="#" method="post" enctype="multipart/form-data">
			<input type="hidden" name="id" value="{$btn['id']|escape:'html':'UTF-8'}">
				<fieldset>

				<!-- Form Name -->
				<legend>
				{if $btn['id'] > 0}
					{$btn['label']|escape:'htmlall':'UTF-8'}
				{else}
					{l s='New button' mod='paygreen'}
				{/if}</legend>

				<!-- Text input-->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="label">{l s='Label' mod='paygreen'}</label>
					<div class="col-md-7">
				  <input id="label" name="label" type="text" placeholder="{l s='Button labem' mod='paygreen'}" class="form-control input-md" required="required" value="{$btn['label']|escape:'html':'UTF-8'}">
				  <span class="help-block">{l s='Text displayed to the right of the icon' mod='paygreen'}</span>
				  </div>
				</div>

				<!-- File Button -->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="image">{l s='icon' mod='paygreen'}</label>
				  <div class="col-md-7">
				    <input id="image" name="image" class="input-file" type="file">
				  </div>
				</div>

				<div class="form-group">
					<label class="col-md-4 control-label" for="label">{l s='Image used' mod='paygreen'}</label>
					<div class="col-md-7 text-center">
					{if $btn['image'] > ""}
						<a href="{$icondir|escape:'html':'UTF-8'}{$btn['image']|escape:'html':'UTF-8'}" target="_blank" title="{l s='Image used' mod='paygreen'}"><img src="{$icondir|escape:'html':'UTF-8'}{$btn['image']|escape:'html':'UTF-8'}" style="max-height:40px;" /></a>
					{else}
						<img src="{$icondir|escape:'html':'UTF-8'}paygreen_paiement.png" style="max-height:40px;" />
					{/if}
					</div>
				</div>
				<!-- Text input-->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="height">{l s='Image height' mod='paygreen'}</label>
				  <div class="col-md-7">
				  <input id="height" name="height" type="number" placeholder="" class="form-control input-md" value="{$btn['height']|escape:'html':'UTF-8'}">
				    <span class="help-block">{l s='If empty, the image size will be the actual' mod='paygreen'}</span>
				  </div>
				</div>

				<!-- Select Basic -->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="displayType">{l s='Display type' mod='paygreen'}</label>
				  <div class="col-md-7">
				    <select id="displayType" name="displayType" class="form-control">
			    	<option value="bloc"{if $btn['displayType'] == 'bloc'} selected="selected"{/if}>{l s='bloc without arrow' mod='paygreen'}</option>
				      <option value="full"{if $btn['displayType'] == 'full'} selected="selected"{/if}>{l s='Complete ligne' mod='paygreen'}</option>
				      <option value="half"{if $btn['displayType'] == 'half'} selected="selected"{/if}>{l s='Half line' mod='paygreen'}</option>
				    </select>
				  </div>
				</div>

				<!-- Text input-->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="position">{l s='N° position' mod='paygreen'}</label>
				  <div class="col-md-7">
				  <input id="position" name="position" type="number" placeholder="" class="form-control input-md" value="{$btn['position'|escape:'html':'UTF-8']}">
				    <span class="help-block">{l s='if empty, will be calculated automaticaly' mod='paygreen'}</span>
				  </div>
				</div>

				<!-- Text input-->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="nbPayment">{l s='payment x times' mod='paygreen'}</label>
				  <div class="col-md-7">
				  <input id="nbPayment" name="nbPayment" type="number" placeholder="" class="form-control input-md" value="{$btn['nbPayment'|escape:'html':'UTF-8']}">
				    <span class="help-block">{l s='Allow to create a payment spread over X months' mod='paygreen'}</span>
				  </div>
				</div>

				<!-- Select Basic -->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="displayType">{l s='Payment type' mod='paygreen'}</label>
				  <div class="col-md-7">
				    <select id="executedAt" name="executedAt" class="form-control">
				    	<option value="0"{if $btn['executedAt'] == '0'} selected="selected"{/if}>{l s='Immediatly' mod='paygreen'}</option>
				      <option value="-1"{if $btn['executedAt'] == '-1'} selected="selected"{/if}>{l s='At the delivery' mod='paygreen'}</option>
				    </select>
				  </div>
				</div>

				<!-- Text input-->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="nbPayment">{l s='Amount cart' mod='paygreen'}</label>
				  <div class="col-xs-5 col-md-3">
				  <input id="minAmount" name="minAmount" type="number" placeholder="" class="form-control input-md" value="{if $btn['minAmount']>0}{$btn['minAmount'|escape:'html':'UTF-8']}{/if}">
				    <span class="help-block">{l s='Minimum' mod='paygreen'}</span>
				  </div>
				  <div class="col-xs-2 col-md-1 text-center">à</div>
				  <div class="col-xs-5 col-md-3">
				  	<input id="maxAmount" name="maxAmount" type="number" placeholder="" class="form-control input-md" value="{if $btn['maxAmount']>0}{$btn['maxAmount'|escape:'html':'UTF-8']}{/if}">
				    <span class="help-block">{l s='Maximum' mod='paygreen'}</span>
				  </div>
				</div>

				<!-- Button (Double) -->
				<div class="form-group">
				  <label class="col-md-4 control-label" for="resetBtn"></label>
				  <div class="col-md-8">
				  {if $btn['id'] > 0}
				  	<button id="resetBtn" name="submitPaygreenModuleButtonDelete" class="btn btn-danger">{l s='Delete' mod='paygreen'}</button>
				  {else}
				    <button id="resetBtn" name="resetBtn" type="reset" class="btn btn-danger">{l s='Cancel' mod='paygreen'}</button>
				  {/if}
				    <button id="validBtn" name="submitPaygreenModuleButton" class="btn btn-success">{l s='Validate' mod='paygreen'}</button>
				  </div>
				</div>

				</fieldset>
			</form>
		</div>
	{/foreach}
	</div>
</div>
