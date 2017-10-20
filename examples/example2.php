<?php
require '../ztemplate.php'; // A better place would be 'libs/ztemplate.php'

$t->pages = array('foo', 'bar/qux', '/wibble/wobble');

$t->somecontent = 'Hello world!';

if (isset($_GET['page']) == false)
	$t->page = $t->pages[0];
else
	$t->page = $_GET['page'];

switch ($t->page) {
	case 'foo':
		Template('foo'); // == templates/foo.php
		break;
	
	case 'bar/qux':
		Template('bar', 'qux'); // == templates/bar/qux.php
		break;
		
	case '/wibble/wobble':
		Template(true, 'wibble', 'wobble'); // == wibble/wobble.php
		break;
		
	default:
		Template('notfound'); // == templates/foo.php
		break;
}
