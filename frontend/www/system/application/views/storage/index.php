<div id="main">
	<div class='formtoggle'>
		<form id='Form' method='post' action='index.php?storage/cfg'>
		<h1>Storage</h1>
		<button class='b-icon addform'>
			<span class='s-icon add'></span>
		</button>
		Add new storage pool
		<div class='formcontainer'>
			<h1>Available Disks</h1>
			<?php echo $this->table->generate($disklist); ?>
			<span class='text'>
				<label>Poolname:</label>
				<input onclick="clearContent()" onblur="validateContent(this.value, 'pool')" class="validate['required','words[1,1]','alphanum']" type='text' name='name' value='' />
				<div id='validate' class='validate'></div>
			</span>
			<button class="validate['submit'] submit" type='submit' onclick="formValidate()" value='Next'>Next</button>
		</div>
		</form>
	</div>	
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