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
    {if $color == 'green'} {$colorText='#25b373'} {else} {$colorText = $color } {/if}
    <section id="block_paygreen_infos" class="footer-block col-xs-12 col-sm-12 col-md-12 col-lg-4">
        <a href="{$backlink|escape:'html':'UTF-8'}" target="_blank" title="paygreen" >
            <img src="{$imgdir|escape:'html':'UTF-8'}footer/paygreen-{$color|escape:'html':'UTF-8'}.png"
            alt ="{l s='Secured Payment with Paygreen' mod='paygreen'}"/>
        </a>
    </section>
    <style type="text/css">
        #block_paygreen_infos>img {
            max-height: 15px !important;
            margin-top:-5px;
        }
        #block_paygreen_infos>span {
            color:{$colorText|escape:'html':'UTF-8'};
            font-style:italic;
        }
        #block_paygreen_infos>a>img {
            display: block;
            max-width: 204px !important;
            height: auto;
            max-height:50px !important;
            width: 100% !important;
        }
    </style>
