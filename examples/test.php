<?php
require 'ztemplate.php';

//$t['title'] = 'Example title';
$t->title = 'Example title';

//echo '<pre>';
//print_r($t);

//$t['content'] = 'Hello world!';
$t->content = 'Hello world!';

//print_r($t);

if (isset($_GET['i']) == false)
	$_GET['i'] = 0;

switch ($_GET['i']) {
	case 1:
		template('second', 'testlevel');
		break;
		
	case 2:
		template(true, 'third', 'sometemplate');
		break;
		
	default:
		template('testtemplate');
}