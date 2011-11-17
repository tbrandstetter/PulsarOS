<div id="main">
	<div id="content">
		<div class='left'>
			<h1>Backup</h1>
			<form id='Formdownload' method='post' action='index.php?admin/backup'>
				<br />
				<input type='hidden' name='backup' value='y' />
				<div id='resultdownload'>
					<input class="other" type='submit' onclick="formValidateAjax('download')" value='Backup' />
				</div>
			</form>	
		</div>
		<div class='leftnospace'>
			<h1>Restore</h1>
			<form id='Form' method='post' action='index.php?admin/restore' enctype='multipart/form-data'>
				<br />
				<input type='file' name='file' />
				<input class="other" type='submit' value='Restore' />
				<div id='result'>
					{status}
				</div>
			</form>
		</div>
		<div class='clear'></div>
	</div>
</div>
