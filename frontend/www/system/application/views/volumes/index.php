<div id="main">
	<h1>Volumes</h1>
	{pools}
		<div class='formtoggle'>
			<form id='Form{name}' method='post' action='index.php?volumes/add'>
				<table>
					<tr>
						<td class='pooldesc'>
							<button class='b-icon addform' onclick="createSlider('{name}', '{remaining}')">
								<span class='s-icon add'></span>
							</button>
							Pool: <b>{name}</b>
						</td>
						<td class='poolraid'>
							<span class='raidlevel-small {raidlevel}'>{raidlevel}</span>
						</td>
						<td class='poolsize'>
							<span>{capacity}</span>
						</td>
					</tr>
				</table>
				{state}
				<div id='formcontainer{name}' class='formcontainer'>
					<div>
						<label class='text'>Name:</label>
						<input onclick="clearContent('{name}')" onblur="validateContent(this.value, 'volume', '{name}')" class="validate['required','length[4,15]','words[1,1]','alphanum']" type='text' name='name' value='' />
						<div id='validate{name}' class='validate'></div>
					</div>
					<div class='clear'></div>
					<div>
						<label class='text'>Description:</label>
						<input id='description{name}' class="validate['required','length[1,40]','alphanum']" type='text' name='desc' value='' />
					</div>
					<div id='newsize{name}'></div>
					<div id='vol{name}' class='slidercreate'>
						<div class='panel'></div>
					</div>
					<input type='hidden' id='size{name}' name='size' value='' />
					<input type='hidden' name='pool' value='{name}' />
					<div class='clear'></div>
					<p>
						ISCSI <input class='addform' type='checkbox' name='iscsi' value='y' {iscsi} />
						<button class="validate['submit'] other" type='submit' onclick="formValidateAjax('{name}','index.php?volumes')" value='Create' />Create</button>
					</p>
					<div id='result{name}'></div>
				</div>
			</form>
		</div>
		<div class='volumelist'>
			{volumeentry}
		</div>
	{/pools}
</div>