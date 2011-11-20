<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Default Theme</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="stylesheet" type="text/css" href="{$root}public/resources/style" />
{block name=head}{/block}
</head>
<body>

	<div id="container">
	
		<div id="header">

			<h1>{$group_name}</h1>
			
			<div id="navigation">
				[ <a href="{$root}public">Home</a> ]
				[ <a href="{$root}public/events">Events</a> ]
				[ <a href="{$root}public/blog">Blog</a> ]
			</div>
			
			<hr />
		
		</div>
		
		<div id="content">
			<div id="contentWrapper" class="wrapper">
				{block name=body}{/block}
			</div>
		</div>
		
		<div id="footer">
			<hr />
			&copy; {$group_name}
		</div>
	
	</div>

</body>
</html>