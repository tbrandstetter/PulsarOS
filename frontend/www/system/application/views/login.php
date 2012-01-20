<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>PulsarOS Frontend</title>
	<link href="/css/style.css" type="text/css" rel="stylesheet" media="screen" />
	<link href="/css/formcheck/theme/classic/formcheck.css" type="text/css" rel="stylesheet" media="screen" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en-us" />
	<meta name="author" content="Thomas Brandstetter" />
	<script src="/js/mootools-1.2.4-core-yc.js" type="text/javascript"></script>
	<script src="/js/mootools-1.2.4.4-more.js" type="text/javascript"></script>
	<script src="/js/formcheck/lang/en.js" type="text/javascript"></script>
	<script src="/js/formcheck/formcheck.js" type="text/javascript"></script>
	<script src="/js/pulsaros.js" type="text/javascript"></script>
</head>
<body>
	<div id='wrapper'>
		<div id='logo'>
		</div>
		<div id='menu'>
			<ul><h1>Login</h1></ul>
		</div>
		<div id='main'>
			<div id='content'>
				<div id='loginbox'>
				<form id='Formlogin' method='post' action='index.php?login'>
					<div class='loginform'>
						<div>
							<label class='text'>Username:</label>
							<input class="validate['required','words[1,1]','alphanum']" type='text' name='user' value='' />
						</div>
						<div class='clear'></div>
						<div>
							<label class='text'>Password:</label>
							<input class="validate['required','words[1,1]']" type='password' name='password' value='' />
						</div>
						<div class='clear'></div>
						<div class='right'>
							<input class="validate['submit'] submit" type='submit' onclick="formValidate('login')" value='Login' />
						</div>
						<div class='right' id='resultlogin'>
							{status}
						</div>
					</div>
				</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>