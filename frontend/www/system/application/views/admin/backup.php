<div id="main">
	<table>
		<tr>
			<td>
				<form id='Formdownload' method='post' action='index.php?admin/backup'>
				<h1>Backup</h1>
				<span>
					<input type='hidden' name='backup' value='y' />
					<button submit" type='submit' onclick="formValidateAjax('download','')" value='Backup' />Backup</button>
				</span>
				<div id='resultdownload'></div>
				</form>
			</td>
			<td>
				<form id='Form' method='post' action='index.php?admin/restore' enctype='multipart/form-data'>
				<h1>Restore</h1>
				<span>
					<input type='file' name='file' />
					<button submit" type='submit' value='Restore' />Restore</button>
				</span>
				<div id='result'>{status}</div>
				</form>
			</td>
		<tr>
	</table>
</div>
