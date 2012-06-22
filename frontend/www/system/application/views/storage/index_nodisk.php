<div id="main">
	<h1>Storage</h1>
	{pools}
	<img src='images/empty.png' onload="refreshContent('{mdname}')" />
	<div class='formtoggle'>
		<form id='Form{name}' method='post' action='index.php?storage/del'>
			<table>
				<tr>
					<td class='pooldesc'>
						<button class='b-icon addform'>
							<span class='s-icon add'></span>
						</button>
						Pool: <b>{name}</b>
					</td>
					<td class='poolraid'>
						<span class='raidlevel-small {raidlevel}'>{raidlevel}</span>
					</td>
					<td class='poolsize'>
						<span>{size}</span>
					</td>
					<td class='poolstatus'>
						<div id='response{mdname}'></div>
					</td>
				</tr>
			</table>
			<div class='formcontainer'>
				<p class='centered'>Delete Pool? All your data and volumes on the disk will be DELETED!</p>
				<input type='hidden' name='name' value="{name}" />
				<div id='result{name}' class='result centered'>
					<button class="validate['submit'] other" type='submit' onclick="formValidateAjax('{name}','index.php?storage')" value='Delete'>Delete</button>
				</div>
			</div>
		</form>
	</div>
	{/pools}
</div>