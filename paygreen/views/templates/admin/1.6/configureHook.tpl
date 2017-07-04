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
            <i class="icon-image"></i> {l s='Paygreen Action' mod='paygreen'}
        </div>
        <div class="row" style="margin-top:-10px">
            <div class="col-lg-2 col-sm-6 col-md-6 col-xs-12">
                <div class="form-group">
                    <form class="form-horizontal center-block" action="#" method="post" enctype="multipart/form-data">
                        <label class="col-md-4 control-label" for="height">{l s='Display position' mod='paygreen'}</label>
                        <div class="col-lg-2 col-lg-2 col-md-2 col-md-1">
                            <button type="submit" value="1" id="module_form_submit_btn" name="submitPaygreenModuleHook" class="btn btn-default center-block button">
                            {l s='BRING TO THE TOP' mod='paygreen'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <span class="help-block">{l s='Set PayGreen in first position' mod='paygreen'}</span>
    </div>