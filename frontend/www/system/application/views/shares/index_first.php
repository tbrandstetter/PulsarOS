<div id="main">
	<div class="box">
		<div id='content'>
			<h1>SHARE OPTIONS</h1>
			<div id='container'>
				<form id='Form' method='post' action='index.php?shares/cfg'>
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
						<div class='clear'></div>
					</div>
				</form>
			</div>
		</div>