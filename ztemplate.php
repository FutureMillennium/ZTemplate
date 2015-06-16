<?php
// ZTemplate 2.0.0
// © 2011 – 2015 Zdeněk Gromnica
// see manual.html for more

if (isset($t) == false) {
	$t = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
}

// Main template function
// use this to include a file from the current template
function template($isDirectoryAbsolute, $templateDirectory = null, $templateName = null) {
  global $currentTemplate,
    $t, $baseurl; // Variables templates have access to
	
	// Current template subdirectory
	if (!isset($currentTemplate)) {
		$currentTemplate = '';
	} else {
		$currentTemplate .= '/';
	}
	
	// Templates directory
	if (defined('TEMPLATES_DIR')) {
		$templatesdir = TEMPLATES_DIR;
		if (TEMPLATES_DIR != '/')
			$templatesdir .= '/';
	} else {
		$templatesdir = 'templates/';
	}
	
	// Temporary directory – parsed file dir
	if (defined('TMP_DIR')) {
		if (TMP_DIR == '/')
			$tmpdir = TMP_DIR;
		else
			$tmpdir = TMP_DIR.'/';
	} else {
		$tmpdir = 'tmp/';
	}
	
	// Template file name and directory
  if ($isDirectoryAbsolute === true) { // arg 1, using absolute template path
		if ($templateDirectory == '/') { // arg 2
			$tmpdir .= $templatesdir.'root/';
		} else {
			$subdir = $templateDirectory.'/';
			$tmpdir .= $templatesdir.$subdir.'/';
		}
		$filename = $templateName; // arg 3
	} else {
		if ($isDirectoryAbsolute === false) { // arg 1
			$subdir = $templatesdir.$currentTemplate.$templateDirectory.'/'; // arg 2
			$filename = $templateName; // arg 3
		} elseif (isset($templateDirectory)) { // Subdirectory specified
			$subdir = $templatesdir.$currentTemplate.$isDirectoryAbsolute.'/'; // arg 1
			$filename = $templateDirectory; // arg 2
		} else {
			$subdir = $templatesdir.$currentTemplate;
			$filename = $isDirectoryAbsolute; // arg 1
		}
		$tmpdir .= $subdir;
	}
	
  $templatefile = $subdir.$filename.'.php'; // Original file
	
  $tmpfile = $tmpdir.$filename.'.php'; // Parsed file
  
  // Check if parsed version exists
  if (file_exists($tmpfile)) {
		if (defined('CHECK_TEMPLATE_UPDATES') == false || (defined('CHECK_TEMPLATE_UPDATES') && CHECK_TEMPLATE_UPDATES)) {
			// Check if the parsed version isn't older than the current template file
			if (filemtime($templatefile) <= filemtime($tmpfile)) {
				// Include it
				include $tmpfile;
				// We're done here
				return true;
			}
		}
  }
  
  // Check if the template file exists
  if (!file_exists($templatefile)) {
		// TODO call error
    return false;
	}
  
  // Check if the tmp folder exists
  if (!is_dir($tmpdir)) {
    // If not, create it
    mkdir($tmpdir, 0777, true);
  }
  
  // Read the original file
  $filesize = filesize($templatefile);
  if ($filesize > 0) {
    $handle = fopen($templatefile, "r");
    $contents = fread($handle, $filesize);
    fclose($handle);
  } else {
    $contents = '';
  }
  
  // Parse it
  $contents = ZTemplateParse($contents);
  
  // Save the parsed file
  $handle = fopen($tmpfile, "w");
  if ($filesize > 0)
    fwrite($handle, $contents);
  fclose($handle);
  
  // Include the parsed file
  include $tmpfile;
  return true;
}

// Parse helper function
function ZTemplateParse($content) {
  return preg_replace(
    array(
      '/\{\*([^\*]|\*[^\}])*\*\}/', // {*comment*}
      '/\{\$([^}]+)\}/', // {$var}
      '/\{(if|foreach) ([^}]+)}/', // {if foo}
      '/\{elseif ([^}]+)}/', // {elseif foo}
      '/\{else\}/', // {else}
      '/\{\/(if|foreach)\}/', // {/if}
      '/\{\? ([^}]+)}/', // {? foo}
      '/\{([\w\d]+) ([^}]+)}/', // {foo bar}
      '/\{([^ \r\n\t][^}]+)}/', // {foo}
    ),
    array(
      '',
      '<?php echo $\1 ?>',
      '<?php \1 (\2) { ?>',
      '<?php } elseif (\1) { ?>',
      '<?php } else { ?>',
      '<?php } ?>',
      '<?php \1 ?>',
      '<?php \1(\2) ?>',
      '<?php \1 ?>',
    ),
    $content);
}