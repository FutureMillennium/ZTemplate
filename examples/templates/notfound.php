<!doctype html>
<html>
	<head>
		<title>Page not found</title>
	</head>
	<body>
		<h1>Page not found</h1>
		<p>Page not found.</p>
		
		<h2>Pages:</h2>
		<ul>
			{foreach $t->pages as $val}
				{if $val == $t->page}
					<li><strong>{$val}</strong></li>
				{else}
					<li><a href="?page={$val}">{$val}</a></li>
				{/if}
			{/foreach}
		</ul>
	</body>
</html>
