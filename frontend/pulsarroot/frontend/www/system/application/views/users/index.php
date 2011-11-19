<div id="main">
	<div class="box">
<div id='content'>
	<div id='container'>
		<div class='formtoggle'>
			
			<form id='Formuser' method='post' action='index.php?users/add'>
				<h1>Users<a href='' class='addform'><img src='/images/plus.png' alt='add' /></a></h1>
				<div class='formcontainer'>
					<div>
						<label class='text'>Username:</label>
						<input onclick="clearContent('user')" onblur="validateContent(this.value, 'user', 'user')" class="validate['required','words[1,1]','alphanum']" type='text' name='name' value='' />
						<div id='validateuser' class='validate'></div>
					</div>
					<div class='clear'></div>
					<div>
						<label class='text'>Password:</label>
						<input class="validate['required','words[1,1]']" type='password' name='password' value='' />
					</div>
					<div>
						<label class='text'>Confirm:</label>
						<input class="validate['confirm[password]']" type='password' name='confirm' value='' />
					</div>
					<div>
						<label class='text'>Home Directory:</label>
						<select>
							{pools}
							<option>{name}</option>
							{/pools}
						</select>
					</div>
					<div>
						<input type='checkbox' name='scponly' value='y' />
						<label class='text'>SSHFS/SCP</label>
					</div>
					<div class='clear'></div>
					<div id='resultuser'>
						<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('user', 'index.php?users')" value='Create' />
						<div class='clear'></div>
					</div>
				</div>
			</form>
		</div>
		{users}
		<div class='list'>
			<div class='formtoggle'>
				<div class='name'>
					<div>
						<span><p><b>{name}</b></p><img src='images/user.png' alt='user' /></span>
					</div>
				</div>
				<div class='info'>
					<a href='' class='addform'><img src='/images/settings.png' alt='change user' /></a>
				</div>
				<div class='desc'>
					<span>{desc}</span>
				</div>
				<div class='clear'></div>
				<form id='Form' method='post' action='index.php?users/chg'>
					<div class='formcontainer'>
						<div>
							<label class='text'>Description:</label>
							<input class="validate['required','length[1,40]','alphanum']" type='text' name='description' value='{desc}' />
						</div>
						<div>
							<label class='text'>New Pass:</label>
							<input class="validate['required','words[1,1]']" type='password' name='password' value='' />
						</div>
						<div>
							<label class='text'>Confirm:</label>
							<input class="validate['confirm[password]']" type='password' name='confirm' value='' />
						</div>
						<input type='hidden' name='name' value='{name}' />
						<div class='remove'>
							<p style="margin-top: 5px;">Delete User<input type="checkbox" name="removeuser" value='y' /></p>
						</div>
						<input class="validate['submit'] other" type='submit' onclick="formValidate('')" value='Save' />
					</div>
				</form>	
			</div>
		</div>
		{/users}
		<br />
		<div class='formtoggle'>
			<form id='Formgroup' method='post' action='index.php?groups/add'>
				<h1>Groups<a href='' class='addform'><img src='/images/plus.png' alt='add' /></a></h1>
				<div class='clear'></div>
				<div class='formcontainer'>
					<div>
						<label class='text'>Groupname:</label>
						<input onclick="clearContent('group')" onblur="validateContent(this.value, 'group', 'group')" class="validate['required','alphanum']" type='text' name='gname' value='' />
						<div id='validategroup' class='validate'></div>
					</div>
					<div class='clear'></div>
					<div id='resultgroup' class='result'>
						<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('group', 'index.php?users')" value='Create' />
					</div>
				</div>
			</form>
		</div>
		{groups}
		<div class='list'>
			<div class='formtoggle'>
				<div class='name'>
					<div>
						<span><p><b>{name}</b></p><img src='images/group.png' alt='group' /></span>
					</div>
				</div>
				<div class='info'>
					<a href='' class='addform'><img src='/images/settings.png' alt='change group' /></a>
				</div>
				<div class='desc'>
				</div>
				<div class='clear'></div>
				<form id='Form{group}' method='post' action='index.php?groups/del'>
					<div class='formcontainer'>
						<input type='hidden' name='gname' value='{name}' />
						<p class='float'>Do you really want to delete this group?</p>
						<div id='result{group}' class='result'>
							<input class="other" type='submit' onclick="formAjax('{group}', 'index.php?users')" value='Delete' />
						</div>
					</div>
				</form>	
			</div>
		</div>
		{/groups}
	</div>
</div>