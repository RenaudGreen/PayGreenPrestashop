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
<script type="text/javascript" src="/modules/paygreen/views/js/1.5/back.js"></script>
<h3>{l s='Configuration payment buttons' mod='paygreen'}</h3>
{foreach from=$buttons item=btn}
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
                {if isset($btn['error'])}
                    <div class="alert alert-warning">
                        <strong>{$btn['error']|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                {/if}
                <!-- Text input-->
                <label>{l s='Label' mod='paygreen'}</label>
                <div class="margin-form">
                    <input id="label" name="label" type="text" placeholder="{l s='Button label' mod='paygreen'}"
                           class="fixed-width-xxl" required="required"
                           value="{$btn['label']|escape:'html':'UTF-8'}">
                    <span class="help-block">{l s='Text displayed to the right of the icon' mod='paygreen'}</span>
                </div>

                <!-- File Button -->
                <label>{l s='icon' mod='paygreen'}</label>
                <div class="margin-form">
                    <input id="image" name="image" class="input-file" type="file">
                </div>

                <!-- Image used -->

                <label>{l s='Image used' mod='paygreen'}</label>

                <div class="margin-form">
                    {if $btn['image'] > ""}
                        <a href="{$icondir|escape:'html':'UTF-8'}{$btn['image']|escape:'html':'UTF-8'}" target="_blank"
                           title="{l s='Image used' mod='paygreen'}"><img
                                    src="{$icondir|escape:'html':'UTF-8'}{$btn['image']|escape:'html':'UTF-8'}"
                                    style="max-height:40px;"/></a>
                    {else}
                        <img src="{$icondir|escape:'html':'UTF-8'}paygreen_paiement1_6.png" style="max-height:40px;"/>
                    {/if}
                </div>

                <!-- Text input-->
                <label>{l s='Image height' mod='paygreen'}</label>
                <div class="margin-form">
                    <input id="height" name="height" type="number" placeholder="" class="form-control input-md"
                           value="{if $btn['height'] > 0}{$btn['height']|escape:'html':'UTF-8'}{else}{60}{/if}">
                    <span class="help-block">{l s='If empty, the image will be displayed full-size' mod='paygreen'}</span>
                </div>

                <!-- Text input-->
                <label>{l s='Display Order' mod='paygreen'}</label>
                <div class="margin-form">
                    <input id="position" name="position" type="number" placeholder="" min="0"
                           value="{$btn['position'|escape:'html':'UTF-8']}">
                    <span class="help-block">{l s='if empty, the position will be automatic' mod='paygreen'}</span>
                </div>

                <!-- Select Basic -->
                <label>{l s='Payment type' mod='paygreen'}</label>
                <div class="margin-form">
                    <select id="executedAt" name="executedAt" class="form-control">
                        <option value="0"{if $btn['executedAt'] == '0'} selected="selected"{/if}>{l s='Cash' mod='paygreen'}</option>
                        <option value="1"{if $btn['executedAt'] == '1'} selected="selected"{/if}>{l s='Subscription' mod='paygreen'}</option>
                        <option value="3"{if $btn['executedAt'] == '3'} selected="selected"{/if}>{l s='In installments' mod='paygreen'}</option>
                        <option value="-1"{if $btn['executedAt'] == '-1'} selected="selected"{/if}>{l s='At the delivery' mod='paygreen'}</option>
                    </select>
                </div>

                <!-- Text input-->
                <label id="labelNbPayment">{l s='Number of installments' mod='paygreen'}</label>
                <div class="margin-form">
                    <input id="nbPayment" name="nbPayment" type="number" min="0" max="24" placeholder=""
                           value="{$btn['nbPayment'|escape:'html':'UTF-8']}">
                    <span class="help-block" id="spanNbPayment">{l s='Defines the payment duration in months' mod='paygreen'}</span>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" id="labelPerCentPayment"
                        for="perCentPayment">{l s='percentage of first installment' mod='paygreen'}</label>
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-addon">%</span>
                            <input id="perCentPayment" name="perCentPayment" type="number" min="1" max="99"
                                    class="form-control input-md" 
                                    {if $btn['perCentPayment'] neq 0}
                                    value="{$btn['perCentPayment'|escape:'html':'UTF-8']}"
                                     {/if}>
                        </div>
                                       
                          <span class="help-block" id="spanPerCentPayment">{l s='if empty, the position will be automatic' mod='paygreen'}</span>
                    </div>
                </div>

                <!-- CheckBox Basic -->
                <div class="form-group">
                    <label class="control-label col-md-4" id ="labelSubOption">{l s='Renew previous orders' mod='paygreen'}</label>
                    <div class="checkbox col-md-7">
                         <label id ="inputSubOption"><input type="checkBox" name="subOption" id="subOption" value="1" {if $btn['subOption'] == 1} checked {/if}>{l s='Enable option' mod='paygreen'}</label>
                         <p class="help-block" id ="spanSubOption">{l s='At each due payment, the previous order is recreated automatically' mod='paygreen'}</p>
                     </div>
                </div>

                <label id ="labelReport">{l s='Payment deferment' mod='paygreen'}</label>
                <div class="margin-form">
                    <select id="reportPayment" name="reportPayment" class="form-control">
                        <option value="0"{if $btn['reportPayment'] == '0'} selected="selected"{/if}>{l s='No report' mod='paygreen'}</option>
                        <option value="1 week"{if $btn['reportPayment'] == '1 week'} selected="selected"{/if}>{l s='1 week' mod='paygreen'}</option>
                        <option value="2 weeks"{if $btn['reportPayment'] == '2 weeks'} selected="selected"{/if}>{l s='2 weeks' mod='paygreen'}</option>
                        <option value="1 month"{if $btn['reportPayment'] == '1 month'} selected="selected"{/if}>{l s='1 month' mod='paygreen'}</option>
                        <option value="2 months"{if $btn['reportPayment'] == '2 months'} selected="selected"{/if}>{l s='2 months' mod='paygreen'}</option>
                        <option value="3 months"{if $btn['reportPayment'] == '3 months'} selected="selected"{/if}>{l s='3 months' mod='paygreen'}</option>
                    </select>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" id="labelReductionPayment"
                        for="ReductionPayment">{l s='Discount' mod='paygreen'}</label>
                    <div class="col-md-7">

                        {html_options name=reductionPayment options=$promoCode selected=$btn['reductionPayment']}
                                       
                        <span class="help-block" id="spanReductionPayment">{l s='Non visible Cart Rules' mod='paygreen'}</span>
                    </div>
                </div>

                <!-- Text input-->

                <label>{l s='Amount cart' mod='paygreen'}</label>
                <div class="margin-form">
                    <input id="minAmount" name="minAmount" type="number" placeholder=""
                           min="0" value="{if $btn['minAmount']>0}{$btn['minAmount'|escape:'html':'UTF-8']}{/if}">
                    <span class="help-block">{l s='Minimum' mod='paygreen'}</span>
                    {l s='to' mod='paygreen'}
                    <input id="maxAmount" name="maxAmount" type="number" placeholder=""
                           min="0" value="{if $btn['maxAmount']>0}{$btn['maxAmount'|escape:'html':'UTF-8']}{/if}">
                    <span class="help-block">{l s='Maximum' mod='paygreen'}</span>
                </div>

                <!-- Button (Double) -->

                <label></label>
                <div class="margin-form">
                    {if $btn['id'] > 0}
                        <input type="submit" id="module_form_submit_btn" value="Delete" name="submitPaygreenModuleButtonDelete" class="btn btn-default pull-right button">
                    {else}
                    <input type="reset" id="module_form_submit_btn" value="Reset" name="resetBtn" class="btn btn-default pull-right button">
                    {/if}
                  <!--  <button type="submit" value="1" id="validBtn" name="submitPaygreenModuleButton" class="btn btn-default pull-right button">
                                        {l s='Validate' mod='paygreen'}
                    </button>-->
                    <input type="submit" id="module_form_submit_btn" value="Save" name="submitPaygreenModuleButton" class="btn btn-default pull-right button">
                </div>

            </fieldset>
        </form>
    </div>
{/foreach}
