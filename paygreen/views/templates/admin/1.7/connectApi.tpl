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
            <i class="icon-image"></i> {l s='Configuration Paygreen Account' mod='paygreen'}
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 text-center ">
                <div class="margin-top-10 hidden-xs"></div>

                <h2><img class="img-responsive center-block" src="{$imgdir|escape:'html':'UTF-8'}paygreen.png" style="max-height:50px;"/></h2/>
                {if $connected == false}
                    <a href="{$urlBase|escape:'html':'UTF-8'}" class="btn btn-lg btn-default text-success" role="button">{l s='Log in' mod='paygreen'}</a>
                    <div class="row">
                        <a class="text-color-paygreen" href="http://paygreen.fr/subscribe">{l s='Create an account' mod='paygreen'}</a>
                    </div>
                    {else}
                    <div class="row">
                        <a href="{$urlBaseDeconnect|escape:'html':'UTF-8'}" class="btn btn-default text-success" role="button">{l s='Log out' mod='paygreen'}</a>
                    </div>
                    {/if}   
                    <hr class="visible-xs visible-sm">
                </div>
                {if $infoShop!=null}
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 text-center">
                    <div class="row">
                        <h2>{l s='Step' mod='paygreen'} 1</h2>
                    </div>
                    <div class="row">
                        <h4>{l s='To collect your first payment , thank you to fill in the required information by clicking on the link below' mod='paygreen'} :</h4>

                        <a href="http://paygreen.fr/shop/wizard-activation" class="btn btn-default" role="button">{l s='My information' mod='paygreen'}</a>
                    </div>
                    <div class="row">

                        <div class="margin-top-5">
                            <div>
                                <strong>{l s='Siret' mod='paygreen'} : </strong>
                                {if $infoAccount['siret']!=""}<span class='text-success'>{$infoAccount['siret']|escape:'html':'UTF-8'}</span>{else}<span class='text-danger'>{l s='not specified' mod='paygreen'}</span>{/if}
                            </div>
                            <div>
                                <strong>{l s='Iban' mod='paygreen'} : </strong>
                                {if $infoAccount['IBAN']!=""}<span class='text-success'>{$infoAccount['IBAN']|escape:'html':'UTF-8'}</span>{else}<span class='text-danger'>{l s='not specified' mod='paygreen'}</span>{/if}
                            </div>
                            <div>
                                <strong>{l s='Url' mod='paygreen'} : </strong>
                                {if $infoAccount['url']!=""}<span class='text-success'><a href="{$infoAccount['url']|escape:'html':'UTF-8'}">{$infoAccount['url']|escape:'html':'UTF-8'}</a></span>{else}<span class='text-danger'>{l s='not specified' mod='paygreen'}</span>{/if}
                            </div>
                        </div>

                        <hr class="visible-xs">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">

                    <div class="row">
                        <h2 class="text-center">{l s='Step' mod='paygreen'} 2</h2>
                    </div>
                    <div class="row">


                        {assign var='checkActiveAccountOn' value=''}
                        {assign var='checkActiveAccountOff' value=''}
                        {assign var='checkOnDisable' value=''}

                        {if $infoAccount['valide']==true}
                        {if $infoShop->activate==1} {$checkActiveAccountOn='checked="checked"'}
                        {else} {$checkActiveAccountOff='checked="checked"'} {/if}
                        {else}
                        {$checkOnDisable=disabled}
                        {/if}

                        {if $allowRefund==1} {$checkAllowRefundOn='checked="checked"'}
                        {else} {$checkAllowRefundOff='checked="checked"'} {/if}

                        <form class="form-horizontal center-block" action="#" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-lg-8 col-lg-offset-2 text-center">
                                    <div class="form-group" data-toggle="tooltip" data-placement="top" title="{l s='Activate account for payment, available only if Siret, IBAN and URL are informed' mod='paygreen'}">
                                        <label>{l s='Activate my account' mod='paygreen'}</label>
                                        <span class="switch prestashop-switch" >
                                            <input name="PS_PG_activate_account" id="PS_PG_activate_account_on" {$checkActiveAccountOn|escape:'html':'UTF-8'} {$checkOnDisable|escape:'html':'UTF-8'} value="1" type="radio">
                                            <label for="PS_PG_activate_account_on" class="radioCheck">{l s='Yes' mod='paygreen'}</label>
                                            <input name="PS_PG_activate_account" id="PS_PG_activate_account_off" {$checkActiveAccountOff|escape:'html':'UTF-8'}  {$checkOnDisable|escape:'html':'UTF-8'} value="0"  type="radio">
                                            <label for="PS_PG_activate_account_off" class="radioCheck">{l s='No' mod='paygreen'}</label>
                                            <a class="slide-button btn"></a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3">
                                    <button type="submit" value="1" id="module_form_submit_btn" name="submitPaygreenModuleAccount" class="btn btn-default center-block button">
                                        {l s='Save' mod='paygreen'}
                                    </button>
                                </div>
                            </div>
                        </form>


                    </div>

                </div>

                {else}
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 text-center">
                    <div class="panel">
                        <p class="text-danger h4">{l s='The unique ID and private key have not been informed or they are incorrect.' mod='paygreen'}</p>
                        <h4 class="text-success">{l s='Login' mod='paygreen'}</h4>
                    </div>
                </div>
                {/if}

            </div>
        </div>
