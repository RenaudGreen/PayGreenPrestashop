{$header}
<form action="{$action}" method="post">
	<fieldset>
		<legend><img src="../img/admin/cog.gif" />Configuration du système de paiement</legend>
		<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
			<tr><td colspan="2"><br /><br /></td></tr>
			<tr>
				<td width="130" style="height: 35px;">Clé privée</td>
				<td><input type="text" name="_PG_CONFIG_PRIVATE_KEY" value="{$config._PG_CONFIG_PRIVATE_KEY}" style="width: 300px;" placeholder="xxxx-xxxx-xxxx-xxxxxxxxxxxx" required="required" /></td>
			</tr>
			<tr>
				<td width="130" style="vertical-align: top;">Identifiant unique</td>
				<td><input type="text" name="_PG_CONFIG_SHOP_TOKEN" value="{$config._PG_CONFIG_SHOP_TOKEN}" style="width: 300px;" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required="required" /></td>
			</tr>
			<tr><td colspan="2" align="center"><br /><input class="button" name="submitpaygreen" value="Enregistrer" type="submit" /></td></tr>
		</table>
	</fieldset>
</form>