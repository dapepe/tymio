<?php
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>TeamviewerCollector Server</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"></link>
	<link rel="icon" href="favicon.ico" type="image/x-icon"></link>
	<link href='http://fonts.googleapis.com/css?family=Cantarell' rel='stylesheet' type='text/css'>
	<style type="text/css">
	<!--
	body {
		padding: 30px;
		margin: 0;
		font-size: 14px;
		color: #404040;
		background: #E6E6E6;
	}
	body, input, textarea { font-family: 'Cantarell', arial, serif; }

	label {
		font-weight: bold;
		display: block;
		margin-top: 5px;
	}
	.textbox {
		-moz-transition: border 0.2s linear 0s, box-shadow 0.2s linear 0s;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) inset;
		border: 1px solid #CCCCCC;
		border-radius: 3px 3px 3px 3px;
		color: #808080;
		display: inline-block;
		font-size: 13px;
		height: 18px;
		line-height: 18px;
		padding: 4px;
		width: 300px;
	}
	.textbox:focus {
		border-color: rgba(82, 168, 236, 0.8);
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) inset, 0 0 8px rgba(82, 168, 236, 0.6);
		outline: 0 none;
	}
	.btn {
		-moz-border-bottom-colors: none;
		-moz-border-image: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;
		-moz-transition: all 0.1s linear 0s;
		background-color: #E6E6E6;
		background-image: -moz-linear-gradient(center top , #FFFFFF, #FFFFFF 25%, #E6E6E6);
		background-repeat: no-repeat;
		border-color: #CCCCCC #CCCCCC #BBBBBB;
		border-radius: 4px 4px 4px 4px;
		border-style: solid;
		border-width: 1px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
		color: #333333;
		cursor: pointer;
		display: inline-block;
		font-size: 13px;
		line-height: normal;
		padding: 5px 14px 6px;
		text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
	}
	.btn:hover {
		background-position: 0 -15px;
		color: #333333;
		text-decoration: none;
	}
	.btn:focus {
		outline: 1px dotted #666666;
	}
	.error {
		background-color: #C43C35;
		background-image: -moz-linear-gradient(center top , #EE5F5B, #C43C35);
		background-repeat: repeat-x;
		border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
		text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		border-radius: 4px 4px 4px 4px;
		border-style: solid;
		border-width: 1px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.25) inset;
		margin-bottom: 18px;
		padding: 7px 15px;
		position: relative;
		text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		color: #FFFFFF;
	}
	.code, .panel {
		background-color: #FEFBF3;
		border: 1px solid rgba(0, 0, 0, 0.2);
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
		padding: 9px;
		border-radius: 3px 3px 3px 3px;
		display: block;
		font-size: 12px;
		line-height: 18px;
		margin-top: 18px;
	}
	.code {
		white-space: pre-wrap;
		word-wrap: break-word;
		font-family: Monaco,Andale Mono,Courier New,monospace;
	}
	#login {
		margin: 40px auto 0 auto;
		width: 380px;
	}
	#header {
		font-size: 24px;
		font-weight: bold;
		margin: 15px 0;
	}
	-->
	</style>
</head>
<body>
	<?php if (isset($ERROR)) {
		echo '<div class="error">'.$ERROR['message'].' in <i>'.$ERROR['file'].'</i> Line '.$ERROR['line'].'</div>';
		echo '<div class="code">'.$ERROR['trace'].'</div>';
	} else {
		if (isset($MSG))
			echo '<div class="error">'.$MSG.'</div>';
	?>
		<form action="index.php" method="post">
			<div id="login" class="panel">
				<div id="header">Login</div>
				<input type="password" class="textbox" name="password" />
				<input type="submit" class="btn" value="Ok" style="margin-left:10px;" />
			</div>
		</form>
	<?php } ?>
</body>
</html>