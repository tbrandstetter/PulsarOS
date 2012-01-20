<div id="main">
	<div class="box">
<div id='content'>
	<h1>STORAGE OPTIONS</h1>
	<div id='storageoptions'>
		<form id='Form{name}' method='post' action='index.php?storage/add'>
			{options}
			<div class='cell'>
				<input class="validate['required']" type='radio' name='raidlevel' value='{raidlevel}' />
				<img src='/images/{raidlevel}.png' alt='{raidlevel}' />
			</div>
			{/options}
			{disks}
			<input type='hidden' name='name' value='{name}' />
			<input type='hidden' name='{device}' value='{device} {capacity} {id}' />
			{/disks}
			<div id='result{name}'>
				<input class="validate['submit'] submit" type='submit' onclick="formValidateAjax('{name}','index.php?storage')" value='Create' />
				<div class='clear'></div>
			</div>
		</form>
	</div>
</div>