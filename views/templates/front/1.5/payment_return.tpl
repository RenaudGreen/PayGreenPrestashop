<div class="box box-small clearfix">
{if isset($error) AND $error}
	<p>
		<h3>Votre paiement avec Paygreen n'as pas aboutis</h3><br />
		Vous pouvez nous contacter pour en <a href="{$base_dir_ssl}index.php?controller=contact">savoir plus</a>
	</p>
{else}
	<p>
		<h3>Votre paiement avec Paygreen a été accepté</h3><br />
		Vous pouvez nous contacter pour en <a href="{$base_dir_ssl}index.php?controller=contact">savoir plus</a>
	</p>
{/if}
</div>