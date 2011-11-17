<div id="main">
	<div class="box">
<div id='content'>
	<h1>Volumes</h1>
	<div id='container'>
	{pools}
		<div class='volumes'>
			<div class='formtoggle'>
				<form id='Form{name}' method='post' action='index.php?volumes/add'>
					<div>
						<div id='pool'><h1>Pool:<b>{name}</b></h1></div>
						<div class='stat'>
							<div id='capacity'>{capacity}</div>
							<div class='raidlevel {raidlevel}'>{raidlevel}</div>
							<a href='' class='addform'><img src='/images/plus.png' alt='add volume' onclick="createSlider('{name}', '{remaining}')" /></a>
						</div>
					</div>
					<div class='clear'></div>
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
								<input class="validate['submit'] other" type='submit' onclick="formValidateAjax('{name}','index.php?volumes')" value='Create' />
							</p>
						<div id='result{name}'></div>
					</div>
				</form>
			</div>
			<div class='clear'></div>
			{state}
			{volumes}
			<div class='list'>
				<div class='formtoggle'>
					<div class='name'>
						<span>
							<a href='index.php?share/{volume}'><img src='images/switch.png' alt='change settings' /></a>
							<div class='disk'>
								<div class='diskspace'>{size}</div>
							</div>
							<p> <b>{volume}</b></p>
						</span>
					</div>
					<div class='info'>
						<span>
							{share} <img src='/images/{status}.png' alt="{status}" />
							<a href='' class='addform'><img src='/images/settings.png' alt='change volume' onclick="changeSlider('{volume}','{remaining}', '{volsize}', '{usedsize}', '{maxsize}' )" /></a>
						</span>	
					</div>
					<div class='share'>
					</div>
					<div class='voldesc'>
						<span>{description}</span>
					</div>
					<div class='clear'></div>
					<form id='Form{volume}' method='post' action='index.php?volumes/chg'>
						<div class='formcontainer'>
							<input type='hidden' name='name' value='{volume}' />
							<input type='hidden' name='pool' value='{name}' />
							<input type='hidden' name='volsize' value='{volsize}' />
							<div id='size{volume}'></div>
							<div id='vol{volume}' class='slider'>
								<div class='panel'></div>
							</div>
							<input type='hidden' id='newsize{volume}' name='newsize' value='' />
							<div class='remove'>
								<p style="margin-top: 5px;">Delete Volume<input type="checkbox" name="delete" value='y' /></p>
							</div>
							<div id='result{volume}' class='result'>
								<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('{volume}','index.php?volumes')" value='Apply' />
							</div>
							<div class='clear'></div>
						</div>
					</form>
				</div>
			</div>
			<div class='clear'></div>
			<div id='validate{volume}'></div>
			{/volumes}
			{iscsi_volumes}
			<h2>ISCSI Volumes</h2>
			<div class='list'>
				<div class='formtoggle'>
				<div class='name'>
					<span>
						<a href='index.php?share/{volume}'><img src='images/switch.png' alt='change settings' /></a>
						<div class='disk'>
							<div class='diskspace'>{size}</div>
						</div>
						<p> <b>{volume}</b></p>
					</span>
				</div>
				<div class='info'>
					<span>
						{share} <img src='/images/{status}.png' alt="{status}" />
						<a href='' class='addform'><img src='/images/settings.png' alt='change volume' /></a>
					</span>
				</div>
				<div class='share'>	
				</div>
				<div class='voldesc'>
					<span>{description}</span>
				</div>
				<div class='clear'></div>
				<form id='Form{volume}' method='post' action='index.php?volumes/chg'>
					<div class='formcontainer'>
						<input type='hidden' name='name' value='{volume}' />
						<input type='hidden' name='pool' value='{name}' />
						<input type='hidden' name='iscsi' value='y' />
						<div class='remove'>
							<p style="margin-top: 5px;">Delete Volume<input type="checkbox" name="delete" value='y' /></p>
						</div>
						<div id='result{volume}' class='result'>
							<input class="validate['submit'] other" type='submit' onclick="formValidateAjax('{volume}','index.php?volumes')" value='Apply' />
						</div>
						<div class='clear'></div>
					</div>
				</form>
				</div>
			</div>
			<div class='clear'></div>
			<div id='validate{volume}'></div>
			{/iscsi_volumes}
		</div>
	{/pools}
	</div>
</div>