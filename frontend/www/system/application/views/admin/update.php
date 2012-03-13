<div id="main">
	<form id='Form' method='post' action='index.php?admin/update'>
	<table>
		<tr>
			<td>
				<h1>Update</h1>
				<span>
					<input type='hidden' name='update' value='y' />
					<input class="other" type='submit' onclick="formValidateAjax()" value='Update' />
				</span>
			</td>
		</tr>	
	</table>
	<div id='result'><p>{update}</p></div>
	</form>
</div>