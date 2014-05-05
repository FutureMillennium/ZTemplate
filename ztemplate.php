<?php
// ZTemplate 1.0.5
// © 2011 – 2013 Zdeněk Gromnica

// Basic usage:
/*

{*comment*}   ->  
$t->var       ->  $t['var']
$zt->var      ->  $zt['var']
{$var}        ->  <?php echo $var ?>
{if foo}      ->  <?php if (foo) { ?>
{foreach foo} ->  <?php foreach (foo) { ?>
{elseif foo}  ->  <?php } elseif (foo) { ?>
{else}        ->  <?php } else { ?>
{/if}         ->  <?php } ?>
{/foreach}    ->  <?php } ?>
{p foo}       ->  <?php foo ?>
{foo bar}     ->  <?php foo(bar) ?>
{foo(bar)}    ->  <?php foo(bar) ?>

1.0.5 – 14:53 1.11.2013
- removed global $warnings (why was it there?)
* $templatedir defaults to 'templates/'
* $currentTemplate defaults to ''
* $currentTemplate is written without the final /
*/

// Main template function
// use this to include a file from the current template
function template($tfile, $tfolder = '', $tfolder2 = NULL) {
  global $currentTemplate, $templatedir,
    $zi, $zt, $t, $baseurl; // Variables templates have access to
  
	if (!isset($currentTemplate)) {
		$currentTemplate = '';
	}
	if (!isset($templatedir)) {
		$templatedir = 'templates/';
	}
	
  if ($tfolder === true) // Use a template from a provided absolute path
    $tmfolder = $tfolder2;
  else // Use a template from the default path
    $tmfolder = $templatedir.(empty($currentTemplate) ? '' : $currentTemplate.'/').$tfolder;
  
  $thefile = $tmfolder.$tfile.'.php'; // Original file
  $tmpfolder = TMP_DIR.$tmfolder; // Parsed file dir
  $tmpfile = $tmpfolder.$tfile.'.php'; // Parsed file
  if (!isset($zi['checknewtemplates']))
    $zi['checknewtemplates'] = true; // Check for newer template - set to false to increase speed
  
  // Check if parsed version exists
  if (file_exists($tmpfile)) {
    // Check if the parsed version isn't older than the current template file
    if ($zi['checknewtemplates'] && filemtime($thefile) <= filemtime($tmpfile)) {
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
      '/\$zt->([\w\d]+)/', // $zt->var
      '/\{\$([^}]+)\}/', // {$var}
      '/\{(if|foreach) ([^}]+)}/', // {if foo}
      '/\{elseif ([^}]+)}/', // {elseif foo}
      '/\{else\}/', // {else}
      '/\{\/(if|foreach)\}/', // {/if}
      '/\{p ([^}]+)}/', // {pfoo}
      '/\{([\w\d]+) ([^}]+)}/', // {foo bar}
      '/\{([^ \r\n\t][^}]+)}/', // {foo}
    ),
    array(
      '',
      '$t[\'\1\']', // $t['var']
      '$zt[\'\1\']',
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