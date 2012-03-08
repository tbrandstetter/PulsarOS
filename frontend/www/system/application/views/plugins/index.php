<div id="main">
	<div id='content'>
		<h1>Plugins</h1>
		{plugin}
		<div class='storage'>
			<div class='img'>
				<a href="index.php?plugin/{name}" alt="{name}">
					<img class='hdd' src='http://plugins.pulsaros.com/plugins/{logo}' />
				</a>
			</div>
			<div class='fright'>
				<p><b>{name}</b></p>
				<p>{version}</p>
				<form id='Form' method='post' action='index.php?plugins/install'>
					<input type='hidden' name='install' value='y' />
					<input type='hidden' name='pluginname' value="{name}" />
					<input type='hidden' name='status' value="{status}" />
					<div id='result'>
						<input class="other" type='submit' onclick="formValidateAjax('','index.php?plugins')" value='{status}' />
					</div>
				</form>
			</div>
		</div>
		{/plugin}
	</div>
</div>