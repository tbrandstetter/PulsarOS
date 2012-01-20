<div id="main">
	<div id="content">
		<form id='Form' method='post' action='index.php?networks/chg'>
			<h1>Network Settings</h1>
			<br />
			<div>
				<p>
					<label class='text'>DefaultGW</label>
					<input class="validate['length[1,15]','alphanum']" type='text' name="defaultgw" value='{defaultgw}' />
				</p>
				<p>
					<label class='text'>Nameserver</label>
					<input class="validate['length[1,15]','alphanum']" type='text' name='nameserver' value='{nameserver}' />
				</p>
			</div>
			<div class='clear'></div>
			{nwcards}
			<div id='{card}' class='left'>
				<h1>Interface: <b>{card}</b><img src='images/empty.png' onload="slideDiv('{card}','{dhcp}')" /></h1>
				<p>Activate <input type='checkbox' name='{card}_activate' value='y' {activate} /></p>
				<p>Using DHCP? <input class='addform' type='checkbox' name='{card}_dhcp' value='y' {dhcp} /></p>
				<div class='formcontainer'>
					<p>
						<label class='text'>IP</label>
						<input class="validate['length[1,15]','alphanum']" type='text' name='{card}_ipaddr' value='{ip}' />
					</p>
					<p>
						<label class='text'>Netmask</label>
						<input class="validate['length[1,15]','alphanum']" type='text' name='{card}_netmask' value='{netmask}' />
					</p>
					<p>
						<label class='text'>Gateway</label>
						<input class="validate['length[1,15]','alphanum']" type='text' name="{card}_gateway" value='{gateway}' />
					</p>
					<br />
					<p>
						<label class='text'>MTU Size</label>
						<input class="validate['length[1,4]','alphanum']" type='text' name="{card}_mtu" value='{mtu}' />
					</p>
				</div>
			</div>
			{/nwcards}
			<div class='clear'></div>
			<input type='hidden' name='cards' value='{cards}' />
			<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax()" value='Save' />
			<div id='result'></div>
		</form>
	</div>
</div>