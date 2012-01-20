<div id="main">
	<div id='content'>
		<h1>Storage</h1>
		{pools}
		<div class='formtoggle'>
			<form id='Form{name}' method='post' action='index.php?storage/del'>
				<div class='pool'>
					<div>
						<div id='pool'><h1>Pool: <b>{name}</b></h1></div> 
						<div class='raidlevel {raidlevel}'>{raidlevel}</div>
						<div id='capacity'>{size}</div>
						<img src='images/empty.png' onload="refreshContent('{mdname}')" />
					</div>
					<div class='clear'></div>
					{devices}
					<div class='storage'>
						<div class='img'>
							<img class='hdd' src='/images/hdd.png' />
							<br />
							<img class='stat' src='/images/{availability}.png' />
						</div>
						<div class='desc'>
							<p><b>{device}</b></p>
							<p>{capacity}</p>
						</div>
					</div>
					{/devices}
				</div>
				<div class='status'>
					<h1>Status</h1>
					<div id='response{mdname}'></div>
				</div>
				<div class='delpool'>
					<a href='' class='addform'><img src='/images/minus.png' alt='delete pool' /></a>
				</div>
				<div class='clear'></div>
				<div class='formcontainer'>
					<p class='float'>Delete Pool? All your data and volumes on the disk will be DELETED!</p>
					<input type='hidden' name='name' value="{name}" />
					<div id='result{name}' class='result'>
						<input class="validate['submit'] other" type='submit' onclick="formValidateAjax('{name}','index.php?storage')" value='Delete' />
					</div>
				</div>
				<div class='clear'></div>
			</form>
		</div>
		{/pools}
	</div>
</div>