<div id="main">
	<form id='Form' method='post' action='index.php?networks/chg'>
	<table>
		<tr>
			<td>
				<h1>Network Settings</h1>
				<span class='text'>
					<label>DefaultGW</label>
					<input class="validate['length[1,15]','alphanum']" type='text' name="defaultgw" value='{defaultgw}' />
				</span>
				<span class='text'>
					<label>Nameserver</label>
					<input class="validate['length[1,15]','alphanum']" type='text' name='nameserver' value='{nameserver}' />
				</span>
			</td>
		</tr>
		<tr>
			{nwcards}
			<td class='formtoggle'>
				<h1>Interface: <b>{card}</b><img src='images/empty.png' onload="slideDiv('{card}','{dhcp}')" /></h1>
				<span class='text'>
					<label>Activate</label>
					<input type='checkbox' name='{card}_activate' value='y' {activate} /></span>
				</span>
				<span class='text'>
					<label>Using DHCP?</label>
					<input class='addform' type='checkbox' name='{card}_dhcp' value='y' {dhcp} />
				</span>
				<div class='formcontainer'>
					<div class='network'>
						<span class='text'>
							<label>IP</label>
							<input class="validate['length[1,15]','alphanum']" type='text' name='{card}_ipaddr' value='{ip}' />
						</span>
						<span class='text'>
							<label>Netmask</label>
							<input class="validate['length[1,15]','alphanum']" type='text' name='{card}_netmask' value='{netmask}' />
						</span>
						<span class='text'>
							<label>Gateway</label>
							<input class="validate['length[1,15]','alphanum']" type='text' name="{card}_gateway" value='{gateway}' />
						</span>
						<span class='text'>
							<label>MTU Size</label>
							<input class="validate['length[1,4]','alphanum']" type='text' name="{card}_mtu" value='{mtu}' />
						</span>
					</div>
				</div>
			</td>
			{/nwcards}
		</tr>
	</table>
	<input type='hidden' name='cards' value='{cards}' />
	<button class="validate['submit'] submit" type='submit' onclick="formValidateAjax()" value='Save' />Save</button>
	<div id='result'></div>
	</form>
</div>