<div id="main">
	<div class="box">
<div id='content'>
	<h1>SHARE OPTIONS</h1>
	<div id='container'>
		<form id='Form' method='post' action='index.php?shares/cfg'>
			<br />
			<div id='shares'>
			<div class='left'>
				<h1>WINDOWS</h1>
				<p>
					<label class='text'>Activate</label>
					<input {samba} type='checkbox' name='samba' value='y' />
				</p>
				<p>
					<label class='text'>Readonly</label>
					<input {samba_readonly} type='checkbox' name='samba_readonly' value='y' />
				</p>
				<p>
					<label class='text'>Browsable</label>
					<input {browseable} type='checkbox' name='browseable' value='y' />
				</p>
			</div>
			<div class='left'>
				<h1>UNIX</h1>
				<p>
					<label class='text'>Activate</label>
					<input {nfs} type='checkbox' name='nfs' value='y' />
				</p>
				<p>
					<label class='text'>Readonly</label>
					<input {nfs_readonly} type='checkbox' name='nfs_readonly' value='ro' />
				</p>
				<p>
					<label class='text'>OSX OS</label>
					<input {nfs_osx} type='checkbox' name='nfs_osx' value='insecure' />
				</p>
			</div>
			<div class='leftnospace'>
				<h1>OSX</h1>
				<p>
					<label class='text'>Activate</label>
					<input {afp} type='checkbox' name='afp' value='y' />
				</p>
			</div>
			<div class='clear'></div>
			</div>
			<input type='hidden' name='name' value='{name}' />
			<div id='result'>
				<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('','index.php?share/{name}')" value='Save' />
			</div>
		</form>
		<br /><br />
		<div class='formtoggle'>
			<form method='post' action='index.php?shares/perm'>
				<h1>USERS<a href='' class='addform'><img src='/images/plus.png' alt='add' /></a></h1>
				<div class='formcontainer'>
					<div class='permissions'>
						<div class='left'>
							<select name='user'>
								{users}
								<option value="{user}">{user}</option>
								{/users}
							</select>
						</div>
						<div class='left'>
							<input type='hidden' name='name' value='{name}' />
							<input class="validate['submit'] other" type='submit' value='Add' />
						</div>
					</div>
					<div class='clear'></div>
				</div>
			</form>
		</div>
		{usershare}
		<div class='list'>
			<div class='formtoggle'>
				<div class='name'>
					<div>
						<span><p><b>{username}</b></p><img src='images/user.png' alt='user' /></span>
					</div>
				</div>
				<div class='info'>
					<a href='' class='addform'><img src='/images/settings.png' alt='change permissions' /></a>
				</div>
				<form method='post' action='index.php?shares/perm'>
					<div class='voldesc'>	
					<div>
						<div class='perm'>
							<label class='text-small'>Read</label>
							<input {read} type='checkbox' name='{username}_read' value='y' />
						</div>
						<div class='perm'>
							<label class='text-small'>Write</label>
							<input {write} type='checkbox' name='{username}_write' value='y' />
						</div>
						<div class='perm'>
							<label class='text-small'>Execute</label>
							<input {execute} type='checkbox' name='{username}_execute' value='y' />
						</div>
					</div>
				</div>
				<div class='clear'></div>
				<div class='formcontainer'>
					<div class='remove'>
						<p style="margin-top: 5px;">Delete User<input type="checkbox" name="removeuser" value='{username}' /></p>
					</div>
					<input type='hidden' name='user' value='{username}' />
					<input type='hidden' name='name' value='{name}' />
					<input type='hidden' name='permission_user' value='y' />
					<input class='other' type='submit' value='Save' />
					</div>
				</form>	
			</div>
		</div>
		{/usershare}
		<div class='formtoggle'>
			<form method='post' action='index.php?shares/perm'>
				<h1>GROUPS<a href='' class='addform'><img src='/images/plus.png' alt='add' /></a></h1>
				<div class='formcontainer'>
					<div class='permissions'>
						<div class='left'>
							<select name='group'>
								{groups}
								<option value="{group}">{group}</option>
								{/groups}
							</select>
						</div>
						<div class='left'>
							<input type='hidden' name='name' value='{name}' />
							<input class="validate['submit'] other" type='submit' onclick="formValidate()" value='Add' />
						</div>
					</div>
					<div class='clear'></div>
				</div>
			</form>
		</div>
		{groupshare}
		<div class='list'>
			<div class='formtoggle'>
				<div class='name'>
					<div>
						<span><p><b>{groupname}</b></p><img src='images/group.png' alt='group' /></span>
					</div>
				</div>
				<div class='info'>
					<a href='' class='addform'><img src='/images/settings.png' alt='change permissions' /></a>
				</div>
				<form method='post' action='index.php?shares/perm'>
					<div class='voldesc'>
						<div class='perm'>
							<label class='text-small'>Read</label>
							<input {read} type='checkbox' name='{groupname}_read' value='y' />
						</div>
						<div class='perm'>
							<label class='text-small'>Write</label>
							<input {write} type='checkbox' name='{groupname}_write' value='y' />
						</div>
						<div class='perm'>
							<label class='text-small'>Execute</label>
							<input {execute} type='checkbox' name='{groupname}_execute' value='y' />
						</div>
					</div>
					<div class='clear'></div>
					<div class='formcontainer'>
						<div class='remove'>
							<p style="margin-top: 5px;">Delete Group<input type="checkbox" name="removegroup" value='{groupname}' /></p>
						</div>
						<input type='hidden' name='user' value='{groupname}' />
						<input type='hidden' name='name' value='{name}' />
						<input type='hidden' name='permission_group' value='y' />
						<input class='other' type='submit' value='Save' />
					</div>
				</form>	
			</div>
		</div>
		{/groupshare}
	</div>
</div>