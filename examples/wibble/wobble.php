<!doctype html>
<html>
	<head>
		<title>{$t->page} – ZTemplate example 2</title>
	</head>
	<body>
		<h1>{$t->page}</h1>
	
		<p>{$t->somecontent}</p>
		
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
