<?php
// ZTemplate 2.0.0
// © 2011 – 2015 Zdeněk Gromnica

// Main template function
// use this to include a file from the current template
function template($tfile, $tfolder = '', $tfolder2 = NULL) {
  global $currentTemplate,
    $t, $baseurl; // Variables templates have access to
		
	if (defined('TMP_DIR') == false)
		define('TMP_DIR', 'tmp');
  
	if (!isset($currentTemplate)) {
		$currentTemplate = '';
	}
	
	if (defined('TEMPLATES_DIR') == false)
		define('TEMPLATES_DIR', 'templates');
	if (!empty(TEMPLATES_DIR)) {
		$templatedir = TEMPLATES_DIR.'/';
	}
	
  if ($tfolder === true) // Use a template from a provided absolute path
    $tmfolder = $tfolder2;
  else // Use a template from the default path
    $tmfolder = $templatedir.(empty($currentTemplate) ? '' : $currentTemplate.'/').$tfolder;
  
  $thefile = $tmfolder.$tfile.'.php'; // Original file
  $tmpfolder = TMP_DIR.$tmfolder; // Parsed file dir
  $tmpfile = $tmpfolder.$tfile.'.php'; // Parsed file
	
	if (defined('CHECK_TEMPLATE_UPDATES') == false) // Check for newer template - set to false to increase speed
		define('CHECK_TEMPLATE_UPDATES', true);
  
  // Check if parsed version exists
  if (file_exists($tmpfile)) {
    // Check if the parsed version isn't older than the current template file
    if (CHECK_TEMPLATE_UPDATES && filemtime($thefile) <= filemtime($tmpfile)) {
      // Include it
      include $tmpfile;
      // We're done here
      return true;
    }
  }
  
  // Check if the template file exists
  if (!file_exists($thefile))
    return false;
  
  // Check if the tmp folder exists
  if (!is_dir($tmpfolder)) {
    // If not, create it
    mkdir($tmpfolder, 0777, true);
  }
  
  // Read the original file
  $thefilesize = filesize($thefile);
  if ($thefilesize > 0) {
    $handle = fopen($thefile, "r");
    $contents = fread($handle, $thefilesize);
    fclose($handle);
  } else {
    $contents = '';
  }
  
  // Parse it
  $contents = preg_replace(
    array(
      '/\{\*([^\*]|\*[^\}])*\*\}/', // {*comment*}
      '/\$t->([\w\d]+)/', // $t->var
      '/\{\$([^}]+)\}/', // {$var}
      '/\{(if|foreach) ([^}]+)}/', // {if foo}
      '/\{elseif ([^}]+)}/', // {elseif foo}
      '/\{else\}/', // {else}
      '/\{\/(if|foreach)\}/', // {/if}
      '/\{p ([^}]+)}/', // {p foo}
      '/\{([\w\d]+) ([^}]+)}/', // {foo bar}
      '/\{([^ \r\n\t][^}]+)}/', // {foo}
    ),
    array(
      '',
      '$t[\'\1\']', // $t['var']
      '<?php echo $\1 ?>',
      '<?php \1 (\2) { ?>',
      '<?php } elseif (\1) { ?>',
      '<?php } else { ?>',
      '<?php } ?>',
      '<?php \1 ?>',
      '<?php \1(\2) ?>',
      '<?php \1 ?>',
    ),
    $contents);
  
  // Save the parsed file
  $handle = fopen($tmpfile, "w");
  if ($thefilesize > 0)
    fwrite($handle, $contents);
  fclose($handle);
  
  // Include the parsed file
  include $tmpfile;
  return true;
}