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
    <fieldset>
        <legend>{l s='Configuration Paygreen Account' mod='paygreen'}</legend>
        <div class="flex-container row">
            <div class="flex-item logo text-center">
                <h2><img class="img-responsive center-block" src="{$imgdir|escape:'html':'UTF-8'}paygreen-green.png" style="max-height:50px;"/></h2/>

                    {if $connected == false}
                    <a href="{$urlBase|escape:'html':'UTF-8'}" class="button margin-top-10" role="button">{l s='Log in' mod='paygreen'}</a>
                    <a class="text-color-paygreen" href="http://paygreen.fr/subscribe">{l s='Create an account' mod='paygreen'}</a>
                    <form class="form-horizontal center-block" action="#" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3">
                                <abbr title="Set position 1 for Paygreen Hooks "><button style="margin-top:10px" type="submit" value="1" id="module_form_submit_btn" name="submitPaygreenModuleHook" class="btn btn-default center-block button">
                                    {l s='hook' mod='paygreen'}
                                </button></abbr>
                            </div>
                        </div>
                    </form>
                    {else}
                    <a href="{$urlBaseDeconnect|escape:'html':'UTF-8'}" class="button margin-top-10" role="button">{l s='Log out' mod='paygreen'}</a>
                    {/if}
                </div>


                {if $infoShop!=null}
                <div class="flex-item etape-1">
                    <h2>{l s='Step' mod='paygreen'} 1</h2>
                    <h4>{l s='To collect your first payment , thank you to fill in the required information by clicking on the link below' mod='paygreen'} :</h4>
                    <!--Pour encaisser vos premiers paiements, merci de renseigner les informations obligatoires en cliquant sur le lien ci dessous :-->
                    <a href="http://paygreen.fr/shop/wizard-activation" class="button margin-top-10" role="button">{l s='My information' mod='paygreen'}</a>

                </div>
                <div class="flex-item">


                    <h2 class="text-center">{l s='Step' mod='paygreen'} 2</h2>
                    <div class="flex-container row border">
                        <div class="flex-item infos">
                            <strong>{l s='Siret' mod='paygreen'}</strong>
                            {if $infoAccount['siret']!=""}<p class='text-success'>{$infoAccount['siret']|escape:'html':'UTF-8'}</p>{else}<p class='text-danger'>{l s='not specified' mod='paygreen'}</p>{/if}
                            <strong>{l s='Iban' mod='paygreen'}</strong>
                            {if $infoAccount['IBAN']!=""}<p class='text-success'>{$infoAccount['IBAN']|escape:'html':'UTF-8'}</p>{else}<p class='text-danger'>{l s='not specified' mod='paygreen'}</p>{/if}
                            <strong>{l s='Url' mod='paygreen'}</strong>
                            {if $infoAccount['url']!=""}<p class='text-success'><a href="{$infoAccount['url']|escape:'html':'UTF-8'}">{$infoAccount['url']|escape:'html':'UTF-8'}</a></p>{else}<p class='text-danger'>{l s='not specified' mod='paygreen'}</p>{/if}
                        </div>
                        <div class="flex-item infos">
                            {if $infoAccount['valide']==true}
                            {if $infoShop->activate==1} {$checkActiveAccountOn='checked="checked"'}
                            {else} {$checkActiveAccountOff='checked="checked"'} {/if}
                            {else}
                            {$checkOnDisable=disabled}
                            {/if}

                            {if $allowRefund==1} {$checkAllowRefundOn='checked="checked"'}
                            {else} {$checkAllowRefundOff='checked="checked"'} {/if}

                            <form class="form-horizontal" action="#" method="post" enctype="multipart/form-data">

                                <div class="form-group" data-toggle="tooltip" data-placement="top" title="{l s='Activate account for payment, available only if Siret, IBAN and URL are informed' mod='paygreen'}">
                                    <label>{l s='Activate my account' mod='paygreen'}</label>
                                    <span class="switch prestashop-switch fixed-width-lg" >
                                        <input name="PS_PG_activate_account" id="PS_PG_activate_account_on" {$checkActiveAccountOn|escape:'html':'UTF-8'} {$checkOnDisable|escape:'html':'UTF-8'} value="1" type="radio">
                                        <label for="PS_PG_activate_account_on" class="radioCheck">{l s='Yes' mod='paygreen'}</label>
                                        <input name="PS_PG_activate_account" id="PS_PG_activate_account_off" {$checkActiveAccountOff|escape:'html':'UTF-8'}  {$checkOnDisable|escape:'html':'UTF-8'} value="0"  type="radio">
                                        <label for="PS_PG_activate_account_off" class="radioCheck">{l s='No' mod='paygreen'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {else}
                <div class="flex-item text-center">
                    <h4 class="text-danger">{l s='The unique ID and private key have not been informed or they are incorrect. Please Login.' mod='paygreen'}</h4>
                    </div>
                    {/if}

                </div>
            </fieldset>
