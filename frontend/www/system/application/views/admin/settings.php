<div id="main">
	<form id='Form' method='post' action='index.php?settings/chg'>
	<table>
		<tr>
			<td>
				<h1>System Settings</h1>
				<span class='text'>
					<label>Timeserver</label>
					<input type='text' name='timeserver' value='{timeserver}' />
				</span>	
				<span class='text'>	
					<label>Hostname</label>
					<input class="validate['required','length[1,15]','alphanum']" type='text' name='hostname' value='{hostname}' />
				</span>
				<span class='text'>
					<label>Timezone</label>
					<select name='timezone'>
						<option value='UTC-2' {UTC-2}>Central Europe Summer</option>
						<option value='UTC-1' {UTC-1}>Central Europe Winter</option>
					</select>
				</span>
			</td>
			<td>
				<h1>Admin</h1>
				<span class='text'>
					<label>Password</label>
					<input class="validate['words[1,1]']" type='password' name='password' value='' />
				</span>
				<span class='text'>
					<label>Confirm</label>
					<input class="validate['confirm[password]']" type='password' name='confirm' value='' />
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<h1>Mail Settings</h1>
				{mailsettings}
				<span class='text'>
					<label>Email</label>
					<input class="validate['email']" type='text' name='email' value='{email}' />
				</span>
				<span class='text'>
					<label>Smtp</label>
					<input type='text' name='smtp' value='{smtp}' />
				</span>
				<span class='text'>
					<label>Password</label>
					<input class="validate['words[1,1]']" type='password' name='pass' value='{pass}' />
				</span>
				<span class='text'>
					<label>Confirm</label>
					<input class="validate['confirm[pass]']" type='password' name='passconfirm' value='' />
				</span>
				<span class='text'>
					<label>Use TLS?</label>
					<input {tls} type='checkbox' name='tls' value='y' />
				</span>
				{/mailsettings}
			</td>
			<td>
				<h1>Power Settings</h1>
				<span class='smalltext'>
					<label>Harddisk Spindown after</label>
					<input type='text' name='spindown' value='{spindown}' />
					<label>seconds</label>
				</span>
				<span class='smalltext'>
					<label>Powersave</label>
					<select name='powermode'>
						<option value='none' {none}>None</option>
						<option value='poweroff' {poweroff}>Poweroff</option>
						<option value='standby' {standby}>Standby</option>
					</select>
					<label>after</label>
					<input type='text' name='timeout' value='{timeout}' />
					<label>seconds</label>
				</span>
			</td>
		</tr>
	</table>
	<button class="validate['submit'] submit" type='submit' onclick="formValidateAjax('','index.php?settings')" value='Save'>Save</button>
	<div id='result'></div>
	</form>
</div>