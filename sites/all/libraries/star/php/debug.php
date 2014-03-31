<?php
// Debugging functions:

$dumpMode = false;

/**
 * Get or set the dump mode.
 * @param bool $mode
 */
function set_dump_mode($mode = NULL) {
	global $dumpMode;
	if ($mode === NULL) {
	  return $dumpMode;
	}
	else {
	  $dumpMode = $mode;
	}
}

function dumpOn() {
  dumpMode(TRUE);
}

function dumpOff() {
  dumpMode(FALSE);
}

function dumpBeginPrint() {
	print("<pre style='color: Red'>\n");
}

function dumpEndPrint() {
	print("</pre>\n");
}

function dump($var, $funcName = '') {
	global $dumpMode;
	if ($dumpMode) {
		dumpBeginPrint();
		if ($funcName != '') {
			print "<b>$funcName:</b> ";
		}

		if (is_array($var)) {
			print_r($var);
		}
		else if (is_object($var)) {
			var_dump($var);
		}
		else if (is_bool($var)) {
			print ($var ? 'TRUE' : 'FALSE') . "<br />\n";
		}
		else if ($var === NULL) {
			print "NULL" . "<br />\n";
		}
		else {
			print htmlspecialchars($var) . "<br />\n";
		}

		dumpEndPrint();
	}
}

function dumpAll($printPreTags = true) {
	global $dumpMode;
	if ($dumpMode) {
		if ($printPreTags) {
			dumpBeginPrint();
		}
		var_dump(get_defined_vars());
		if ($printPreTags) {
			dumpEndPrint();
		}
	}
}

function dumpExit($str = '') {
	global $dumpMode;
	if ($dumpMode) {
		exit($str);
	}
}

function dump_theme(){
  dump(theme_get_registry());
}

function profiler(){
  global $timer;
  $no = count($timer);
  $no++;
  $last_time = $timer[$no];
  $no++;
  $timer[$no] = time();
  if(empty($last_time))
    $elapsed = "starting here";
  else
    $elapsed = $timer[$no]-$last_time;
  print "[Time since last step: {$elapsed}]<br>";
}

function print_array($array){
  foreach($array as $key => $value) {
    print "[{$key}] => ";
    if(is_array($value)) print_array($value);
    else print $value;
  }
}
