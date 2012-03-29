<div id="main">
	<form id='Form' method='post' action='index.php?storage/add'>
	<h1>Storage Options</h1>
	<table>
		{options}
		<tr>
			<td>
				<input class="validate['required']" type='radio' name='raidlevel' value='{raidlevel}' />
			</td>
			<td>
				<span class='raidlevel {raidlevel}'>{raidlevel}</span>
			</td>
			<td>
				<p>{raid_info}</p>
				<p>{raid_plus}</p>
				<p>{raid_minus}</p>
			</td>
			{disks}
			<input type='hidden' name='name' value='{name}' />
			<input type='hidden' name='{device}' value='{device} {capacity} {id}' />
			{/disks}
		</tr>
		{/options}
	</table>
	<div id='result'>
		<button class="validate['submit'] submit" type='submit' onclick="formValidateAjax('','index.php?storage')" value='Create'>Create</button>
		<div class='clear'></div>
	</div>
	</form>
</div>