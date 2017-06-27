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
        <table class="table table-striped">
            <tr>
                <th>id / id_cart</th>
                <th>pid</th>
                <th>id_order</th>
                <th>state</th>
                <th>date</th>
                <th>Stop abonnement</th>
                <th>Refund</th>
            </tr>
            {foreach from=$recurringPayments key=key item=rp}
            <tr>
                <td>{$rp['id_cart']|escape:'htmlall':'UTF-8'}</td>
                <td>{$rp['pid']|escape:'htmlall':'UTF-8'}</td>
                <td>{$rp['id_order']|escape:'htmlall':'UTF-8'}</td>
                <td>{$rp['state']|escape:'htmlall':'UTF-8'}</td>
                <td>{$rp['created_at']|date_format:"%D"|escape:'htmlall':'UTF-8'}</td>
                <td><a class="btn btn-default">Stop Abo</a></td>
                <td><a class="btn btn-default">Refund</a></td>
            </tr>
            {/foreach}
        </table>
    </div>
