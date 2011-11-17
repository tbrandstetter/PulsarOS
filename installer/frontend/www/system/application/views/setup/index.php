<div id="main">
	<div class="box">
		<div id='content'>
			<h1>SYSTEM INSTALLATION</h1>
			<p>Welcome to the PulsarOS system installation. In the next steps you need to choose your installation disk and configure your system for the first use.</p><br />
			<h1>SYSTEM DISK</h1>
			<p>Please choose the right disk to install PulsarOS on your system.</p>
			<div class='formtoggle'>
			<form id='Formsetup' method='post' action='index.php?setup/install'>
				<div id='disk_container'>
					{disks}
					<div class='storage'>
						<input class="validate['required']" type='radio' name='disk' value='{name}' />
						<div class='img'>
							<img class='hdd' src='/images/hdd.png' />
							<br />
							<img class='stat' src='/images/on.png' />
						</div>
						<div class='desc'>
							<p><b>{name}</b></p>
							<p>{capacity}</p>
						</div>
					</div>
					{/disks}
				</div>
				<div class='clear'></div><br />
				<h1>SYSTEM CONFIGURATION</h1>
				<p>
					<label class='text'>Hostname:</label>
					<input class="validate['required','words[1,1]','alphanum']" type='text' name='hostname' value='' />
				</p>
				{nwcards}
				<p><input class="validate['required']" type='radio' name='nwcard' value='{nwname}' />Interface: {nwname}</p>
				{/nwcards}
				<p>Using DHCP? <input class='addform' type='checkbox' name='dhcp' value='y' checked='yes' /></p><br />
				<div class='formcontainer'>
					<p>
						<label class='text'>IP Address:</label>
						<input type='text' name='ipaddr' value='' />
					</p>
					<p>
						<label class='text'>Netmask:</label>
						<input type='text' name='netmask' value='' />
					</p>
					<p>
						<label class='text'>Gateway:</label>
						<input type='text' name="gateway" value='' />
					</p>
					<p>
						<label class='text'>Nameserver:</label>
						<input type='text' name='nameserver' value='' />
					</p>
				</div><br />
				<div id='resultsetup' class='result'>
					<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('setup')" value='Install' />
				</div>
			</form>
		</div>
	</div>