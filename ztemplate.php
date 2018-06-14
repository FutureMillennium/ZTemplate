<?php
// ZTemplate 3.1
// © 2011 – 2018 Zdeněk Gromnica
// see docs/manual.html for more

/*
 * Defaults:
 *
	const TEMPLATES_DIR = 'templates';
	const TMP_DIR = 'templates';
	const CHECK_TEMPLATE_UPDATES = true;
 */

if (isset($t) == false) {
	$t = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
}

/*
 * Usage:
 *
	Template('foo'); // == templates/foo.php
	Template('bar', 'qux'); // == templates/bar/qux.php
	Template('bar/qux');    // == templates/bar/qux.php
	Template(true, 'wibble', 'wobble'); // == wibble/wobble.php
	Template(true, 'wibble/wobble');    // == wibble/wobble.php
 */
function Template($templateNameOrIsAbsolute) {
	global $t;

	if (defined('TEMPLATES_DIR')) {
		$templatesDir = TEMPLATES_DIR;
		if (TEMPLATES_DIR != '/')
			$templatesDir .= '/';
	} else {
		$templatesDir = 'templates/';
	}
	
	if (defined('TMP_DIR')) {
		$tmpDir = TMP_DIR;
		if (TMP_DIR != '/')
			$tmpDir .= '/';
	} else {
		$tmpDir = 'tmp/';
	}

	$numArgs = func_num_args();
	$args = func_get_args();
	
	if ($templateNameOrIsAbsolute === true) {

		if ($numArgs == 1)
			return false;

		array_shift($args);

		if ($args[0][0] == '/')
			$tmpDir .= 'root/';

		$dir = '';

	} else {
		if ($templateNameOrIsAbsolute === false) {
			if ($numArgs == 1)
				return false;

			array_shift($args);
		}

		$dir = $templatesDir;
	}

	if ($numArgs > 1) {
		$filename = array_pop($args);
		$dir .= implode('/', $args);
		if ($dir != '/')
			$dir .= '/';
	} else {
		$filename = $templateNameOrIsAbsolute;
	}

	$fileTemplate = $dir . $filename . '.php';
	$tmpDir .= $dir;
	$parsedFile = $tmpDir . $filename . '.php';

	if (file_exists($parsedFile)) {
		if (defined('CHECK_TEMPLATE_UPDATES') == false || (defined('CHECK_TEMPLATE_UPDATES') && CHECK_TEMPLATE_UPDATES)) {
			if (filemtime($fileTemplate) <= filemtime($parsedFile)) {
				include $parsedFile;
				return true;
			}
		}
	}
  
	if (file_exists($fileTemplate) == false) {
		return false;
	}

	echo $tmpDir;

	if (is_dir($tmpDir) == false) {
		if (mkdir($tmpDir, 0777, true) == false)
			return false;
	}
	
	$fileSize = filesize($fileTemplate);
	if ($fileSize > 0) {
		$handle = fopen($fileTemplate, "r");

		if ($handle === false)
			return false;

		$contents = fread($handle, $fileSize);
		fclose($handle);

		$contents = preg_replace(
			array(
				'/\{\*([^\*]|\*[^\}])*\*\}/', // {*comment*}
				'/\{\$([^}]+)\}/', // {$var}
				'/\{(if|foreach) ([^}]+)}/', // {if foo} // {foreach foo}
				'/\{elseif ([^}]+)}/', // {elseif foo}
				'/\{else\}/', // {else}
				'/\{\/(if|foreach)\}/', // {/if} // {/foreach}
				'/\{\= ([^}]+)}/', // {= foo}
				'/\{\? ([^}]+)}/', // {? foo}
				'/\{([\w\d\:]+) ([^}]+)}/', // {foo bar}
				'/\{([^ \r\n\t][^}]+)}/', // {foo}
			),
			array(
				'',
				'<?php echo $\1 ?>',
				'<?php \1 (\2) { ?>',
				'<?php } elseif (\1) { ?>',
				'<?php } else { ?>',
				'<?php } ?>',
				'<?php echo \1 ?>',
				'<?php \1 ?>',
				'<?php \1(\2) ?>',
				'<?php \1 ?>',
			),
			$contents
		);

	} else {
		$contents = '';
	}
  
	$handle = fopen($parsedFile, "w");
	if ($handle === false)
		return false;

	if ($fileSize > 0)
		fwrite($handle, $contents);
	fclose($handle);

	include $parsedFile;
	return true;
}
