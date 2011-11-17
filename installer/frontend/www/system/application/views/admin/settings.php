<div id="main">
	<div id="content">
		<form id='Form' method='post' action='index.php?settings/chg'>
			<div class='left'>
				<h1>System Settings</h1>
				<p>
					<label class='text'>Timeserver</label>
					<input type='text' name='timeserver' value='{timeserver}' />
				</p>
				<p>
					<label class='text'>Hostname</label>
					<input class="validate['required','length[1,15]','alphanum']" type='text' name='hostname' value='{hostname}' />
				</p>
				<p>
					<label class='text'>Timezone</label>
					<select name='timezone'>
						<option value='UTC-2' {UTC-2}>Central Europe Summer</option>
						<option value='UTC-1' {UTC-1}>Central Europe Winter</option>
					</select>
				</p>
				<br />
			</div>
			<div class='leftnospace'>
				<h1>Admin</h1>
				<p>
					<label class='text'>Password</label>
					<input class="validate['words[1,1]']" type='password' name='password' value='' />
				</p>
				<p>
					<label class='text'>Confirm</label>
					<input class="validate['confirm[password]']" type='password' name='confirm' value='' />
				</p>
				<br /><br /><br />
			</div>
			<div class='left'>
				<h1>Mail Settings</h1>
				{mailsettings}
				<p>
					<label class='text'>Email</label>
					<input class="validate['email']" type='text' name='email' value='{email}' />
				</p>
				<p>
					<label class='text'>Smtp</label>
					<input type='text' name='smtp' value='{smtp}' />
				</p>
				<p>
					<label class='text'>Password</label>
					<input class="validate['words[1,1]']" type='password' name='pass' value='{pass}' />
				</p>
				<p>
					<label class='text'>Confirm</label>
					<input class="validate['confirm[pass]']" type='password' name='passconfirm' value='' />
				</p>
				<div>
					<input {tls} type='checkbox' name='tls' value='y' />
					<label class='text'>Use TLS?</label>
				</div>
				{/mailsettings}
				<br />
			</div>
			<div id='power' class='leftnospace'>
				<h1>Power Settings</h1>
				<p>
					<label class='text'>Harddisk Spindown after</label>
					<input class='small' type='text' name='spindown' value='{spindown}' />
					<label class='text-small'>seconds</label>
				</p>
				<p>
					<label class='text-small'>Powersave</label>
					<select name='powermode'>
						<option value='none' {none}>None</option>
						<option value='poweroff' {poweroff}>Poweroff</option>
						<option value='standby' {standby}>Standby</option>
					</select>
					<label class='text-small'>after</label>
					<input class='small' type='text' name='timeout' value='{timeout}' />
					<label class='text-small'>seconds</label>
				</p>
			</div>
			<div class='clear'></div>
			<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('','index.php?settings')" value='Save' />
			<div class='clear'></div>
			<div id='result'></div>
		</form>
	</div>
</div>