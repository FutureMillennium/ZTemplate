<?php
require '../ztemplate.php'; // A better place would be 'libs/ztemplate.php'

$t->foo = 'Lorem ipsum';
$t->bar = 'dolor sit amet';

Template('baz'); // == templates/baz.php
