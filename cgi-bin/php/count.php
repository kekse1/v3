<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 * v2.14.4
 */

//
define('VERSION', '2.14.4');
define('COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');

//
define('AUTO', 32);
define('THRESHOLD', 7200);//2 hours (60 * 60 * 2 seconds)
define('PATH', 'count');
define('OVERRIDE', true);
define('CLIENT', true);
define('SERVER', true);
define('HASH', 'sha3-256');
define('HASH_IP', false);
define('CONTENT', 'text/plain;charset=UTF-8');
define('CLEAN', true);
define('LIMIT', 32768);
define('LOG', 'count.log');
define('ERROR', '/');
define('NONE', '/');
define('DRAW', true);
define('SIZE', 24);
define('SIZE_LIMIT', 512);
define('SPACE', 1);
define('SPACE_LIMIT', 256);
define('PAD', 1);
define('PAD_LIMIT', 256);
define('FONT', 'SourceCodePro');
define('FONTS', 'fonts');
define('FG', '0, 0, 0, 1');
define('BG', '255, 255, 255, 0');
define('AA', true);
define('TYPE', 'png');

//
define('COOKIE_PATH', '/');
define('COOKIE_SAME_SITE', 'Strict');
define('COOKIE_SECURE', false);//(!empty($_SERVER['HTTPS']));
define('COOKIE_HTTP_ONLY', true);

//
function sendHeader($_type_value = CONTENT, $_raw = false)
{
	if(defined('SENT'))
	{
		return false;
	}
	else
	{
		define('SENT', true);
	}

	if($_raw)
	{
		header($_type_value);
	}
	else
	{
		header('Content-Type: ' . $_type_value);
	}

	return true;
}


function error($_reason, $_exit_code = 255, $_relay = false)
{
	if(gettype($_reason) !== 'string')
	{
		$_reason = (string)$_reason;
	}
	
	if(php_sapi_name() === 'cli')
	{
		if(defined('STDERR'))
		{
			fprintf(STDERR, ' >> ' . $_reason);
		}
		else
		{
			die(' >> ' . $_reason);
		}

		if(gettype($_exit_code) === 'integer')
		{
			exit($_exit_code);
		}

		exit(255);
	}
	else if(! defined('SENT'))
	{
		sendHeader(CONTENT);
	}

	if(gettype(ERROR) === 'string')
	{
		die(ERROR);
	}

	die($_reason);
}

function secure($_string, $_null = true, $_die = true)
{
	if(gettype($_string) !== 'string')
	{
		if($_die)
		{
			error('Invalid $_string argument');
		}
		
		if($_null)
		{
			return null;
		}
		
		return '';
	}
	
	$len = strlen($_string);
	
	if($len > 255)
	{
		if($_die)
		{
			error('Argument $_string exceeded length limit (255)');
		}

		if($_null)
		{
			return null;
		}
		
		return '';
	}
	
	$result = '';
	$byte = 0;
	$add = '';
	$l = 0;
	
	for($i = 0; $i < $len; ++$i)
	{
		if(($byte = ord($_string[$i])) >= 97 && $byte <= 122)
		{
			$add = chr($byte);
		}
		else if($byte >= 65 && $byte <= 90)
		{
			$add = chr($byte);
		}
		else if($byte >= 48 && $byte <= 57)
		{
			$add = chr($byte);
		}
		else if($byte === 46)
		{
			$l = strlen($result);
			
			if($l === 0)
			{
				$add = '';
			}
			else if($result[$l - 1] === '.')
			{
				$add = '';
			}
			else
			{
				$add = '.';
			}
		}
		else if($byte === 40 || $byte === 41)
		{
			$add = chr($byte);
		}
		else if($byte >= 43 && $byte <= 45)
		{
			$add = chr($byte);
		}
		else if($byte === 47 || $byte === 92)
		{
			$l = strlen($result);
			
			if($l === 0)
			{
				$add = '';
			}
			else if($result[$l - 1] === chr($byte))
			{
				$add = '';
			}
			else
			{
				$add = chr($byte);
			}
		}
		else if($byte === 58)
		{
			$add = ':';
		}
		else
		{
			continue;
		}
		
		$result .= $add;
	}
	
	if($_null && strlen($result) === 0)
	{
		$result = null;
	}
	
	return $result;
}

function secure_host($_string, $_null = true, $_die = true)
{
	$result = secure($_string, $_null, $_die);
	
	if($result !== null)
	{
		$result = strtolower($result);
	}
	
	return $result;
}

function secure_path($_string, $_null = true, $_die = true)
{
	$value = secure($_string, $_null, $_die);

	if($value === null)
	{
		return null;
	}

	$result = '';
	$len = strlen($value);

	for($i = 0; $i < $len; ++$i)
	{
		if($value[$i] === '-' || $value[$i] === '+' || $value[$i] === '~')
		{
			continue;
		}

		$result .= $value[$i];
	}

	if($_null && strlen($result) === 0)
	{
		return null;
	}

	return $result;
}

function get_param($_key, $_numeric = false, $_float = true, $_die = false)
{
	if(gettype($_key) !== 'string')
	{
		if($_die)
		{
			error('Invalid $_key argument (not a non-emptyString)');
		}
		
		return null;
	}
	else if(empty($_key))
	{
		if($_die)
		{
			error('Invalid $_key argument (may not be empty)');
		}

		return null;
	}
	else if(!isset($_GET[$_key]))
	{
		/*if($_numeric === null)
		{
			return false;
		}*/

		return null;
	}
	/*else if($_numeric === null)
	{
		return true;
	}*/

	$value = secure($_GET[$_key], true, $_die);

	if($_numeric === null && strlen($value) === 1)
	{
		if($value === '0' || $value === 'n' || $value === 'N')
		{
			return false;
		}
		else if($value === '1' || $value === 'y' || $value === 'Y')
		{
			return true;
		}

		return null;
	}
	
	$result = '';
	$byte = null;
	$hadPoint = false;
	$numeric = null;
	$set = '';
	$negative = false;
	$remove = 0;

	if($_numeric) while($value[$remove] === '+' || $value[$remove] === '-')
	{
		++$remove;

		if($value[0] === '-')
		{
			$negative = !$negative;
		}
	}

	if($remove > 0)
	{
		$value = substr($value, $remove);
	}

	$len = strlen($value);

	for($i = 0; $i < $len; ++$i)
	{
		if(($byte = ord($value[$i])) >= 97 && $byte <= 122)
		{
			$numeric = false;
			$set = chr($byte);
		}
		else if($byte >= 65 && $byte <= 90)
		{
			$numeric = false;
			$set = chr($byte);
		}
		else if($byte >= 48 && $byte <= 57)
		{
			$set = chr($byte);

			if($numeric === null)
			{
				$numeric = true;
			}
		}
		else if($byte === 46)
		{
			$set = '.';

			if($hadPoint)
			{
				$numeric = false;
			}
			else if(!$_float)
			{
				$numeric = false;
			}
		}
		else if($byte === 44)
		{
			$set = ',';
			$numeric = false;
		}
		else if($byte === 58)
		{
			$set = ':';
		}
		else
		{
			continue;
		}

		$result .= $set;
	}

	if(strlen($result) === 0)
	{
		$result = null;
		$numeric = false;
	}
	else if(! $_numeric)
	{
		$numeric = false;
	}
	else if(! $_float && $hadPoint && $numeric)
	{
		$numeric = false;
	}

	if($numeric)
	{
		if($result === '.')
		{
			$result = null;
			$numeric = false;
		}
		else
		{
			if($result[0] === '.')
			{
				$result = '0' . $result;
			}
			else if($result[strlen($result) - 1] === '.')
			{
				$result = substr($result, 0, -1);
				$hadPoint = false;
			}

			if($hadPoint)
			{
				$result = (double)$result;
			}
			else
			{
				$result = (int)$result;
			}

			if($negative)
			{
				$result = -$result;
			}
		}
	}

	return $result;
}

function ends_with($_haystack, $_needle)
{
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}

	return (substr($_haystack, -strlen($_needle)) === $_needle);
}

function log_error($_reason, $_source = '', $_path = '', $_die = true)
{
	$noLog = false;
	$data = null;
	
	if(!defined('LOG') || empty(LOG))
	{
		$noLog = $_die = true;
		$data = '';
	}
	else
	{
		$data = '[' . (string)time() . ']';
	}

	if(!empty($_source))
	{
		$data .= $_source . '(';

		if(!empty($_path))
		{
			$data .= $_path;
		}

		$data .= ')';
	}
	else if(!empty($_path))
	{
		$data .= '(' . $_path . ')';
	}

	$data .= ': ' . $_reason . PHP_EOL;
	
	if($noLog)
	{
		error($result = $data);
	}
	else
	{
		$result = file_put_contents(LOG, $data, FILE_APPEND);

		if($result === false)
		{
			error('Logging error: ' . substr($data, 0, -1));
		}
		else if($_die)
		{
			error('');
		}
	}

	return $result;
}

//
function check_path($_path = PATH, $_file = PATH_FILE)
{
	//
	if(!file_exists($_path))
	{
		error('Directory doesn\'t exist!');
	}
	else if(!is_dir($_path))
	{
		error('Path doesn\'t point to a directory');
	}
	else if(!is_writable($_path))
	{
		error('Directory isn\'t writable');
	}
	else if((AUTO !== true || OVERRIDDEN) && !is_file($_file))
	{
		if(OVERRIDDEN)
		{
			error(NONE);
		}
		else if(AUTO === false)
		{
			//log_error('AUTO is false', 'check_path', $_file, false);
			error(NONE);
		}
		else if(gettype(AUTO) === 'integer')
		{
			$count = count_files($_path, false, true, false);

			if($count === null)
			{
				//log_error('Seems to be an invalid PATH (can\'t count_files())', 'check_path', $_path, false);
				error(NONE);
			}
			else if($count >= AUTO)
			{
				//log_error('AUTO is too low', 'check_path', $_file, false);
				error(NONE);
			}
		}
		else
		{
			//log_error('Invalid \'AUTO\' constant', 'check_path', $_file);
			error('Invalid \'AUTO\' constant');
		}
	}
	else if(!is_writable($_file))
	{
		log_error('File is not writable', 'check_path', $_file);
		error('File is not writable');
	}
	
	//
	return true;
}

//
function count_files($_path = PATH, $_dir = false, $_exclude = true, $_list = false, $_filter = false, $_die = false)
{
	if(gettype($_path) !== 'string')
	{
		log_error('Path is not a string', 'count_files', '', true);
		error('Path is not a string');
	}
	else if(! is_dir($_path))
	{
		log_error('This is not a directory', 'count_files', $_path, $_die);

		if($_die)
		{
			error('This is not a directory' . (defined('STDERR') ? PHP_EOL : ''));
		}

		return null;
	}

	$handle = opendir($_path);

	if($handle === false)
	{
		log_error('Couldn\'t opendir()', 'count_files', $_path, false);
		return false;
	}

	$result = ($_list ? array() : 0);
	$index = 0;

	while($sub = readdir($handle))
	{
		if($sub === false)
		{
			break;
		}
		else if($sub === '.' || $sub === '..')
		{
			continue;
		}
		else if($sub[0] === '.')
		{
			continue;
		}
		else if($sub === LOG)
		{
			continue;
		}
		else if($_exclude === true || $_exclude === null)
		{
			if($sub[0] === '-' || $sub[0] === '+')
			{
				continue;
			}
			else if($_exclude === null && $sub[0] === '~')
			{
				continue;
			}
		}

		if($_dir === false)
		{
			if(!is_file($_path . '/' . $sub))
			{
				continue;
			}
		}
		else if($_dir === true)
		{
			if(!is_dir($_path . '/' . $sub))
			{
				continue;
			}
		}
		
		if($_filter === true || $_filter === null)
		{
			if($sub[0] === '~' || $sub[0] === '+' || $sub[0] === '-')
			{
				$sub = substr($sub, 1);
			}
			else if($_filter === null)
			{
				continue;
			}
		}

		if($_list)
		{
			$result[$index++] = $sub;
		}
		else
		{
			++$result;
		}
	}

	closedir($handle);
	return $result;
}

function count_fonts($_path, $_count = false, $_null = true)
{
	$result = count_files(FONTS, false, false, true, false);

	if(!$result)
	{
		return null;
	}

	for($i = 0; $i < count($result); ++$i)
	{
		if(ends_with($result[$i], '.ttf'))
		{
			if(!$_count)
			{
				$result[$i] = substr($result[$i], 0, -4);
			}
		}
		else
		{
			array_splice($result, $i--, 1);
		}
	}

	if($_count)
	{
		return count($result);
	}
	else if($_null && count($result) === 0)
	{
		return null;
	}
	
	return $result;
}

//
function remove($_path, $_recursive = true, $_die = true, $_depth_current = 0)
{
	//
	if($_path[0] === '~' || basename($_path)[0] === '~')
	{
		log_error('Removing a value file is not permitted', 'remove', $_path, $_die);

		if($_die)
		{
			error('Removing a value file is not permitted!');
		}
		
		return false;
	}
	else if(is_dir($_path))
	{
		if(! $_recursive)
		{
			if(rmdir($_path) === false)
			{
				log_error('Unable to rmdir() w/o _recursive', 'remove', $_path, $_die);

				if($_die)
				{
					error('Unable to rmdir() w/o _recursive');
				}

				return false;
			}

			return true;
		}
		
		$handle = opendir($_path);

		if($handle === false)
		{
			log_error('Couldn\'t opendir()', 'remove', $_path, $_die);

			if($_die)
			{
				error('Couldn\'t opendir()');
			}

			return false;
		}
		
		while($sub = readdir($handle))
		{
			if($sub === false)
			{
				break;
			}
			else if($sub === '.' || $sub === '..')
			{
				continue;
			}
			else if(is_dir($_path . '/' . $sub))
			{
				remove($_path . '/' . $sub, true, $_die, $_depth_current + 1);
			}
			else if(unlink($_path . '/' . $sub) === false)
			{
				return false;
			}
		}
		
		closedir($handle);
		
		if(rmdir($_path) === false)
		{
			return false;
		}
	}
	else if(file_exists($_path))
	{
		if(unlink($_path) === false)
		{
			return false;
		}
	}
	else
	{
		return false;
	}
	
	//
	return true;
}

//
if(php_sapi_name() === 'cli')
{
	//
	if(! (defined('STDIN') && defined('STDOUT')))
	{
		die(' >> Running in \'cli\' mode, but \'STDIN\' and/or \'STDOUT\' are not set!' . PHP_EOL);
	}
	else if(!isset($argv))
	{
		fprintf(STDOUT, ' >> Warning: the \'%s\' is not defined (so no parameters can be defined here)' . PHP_EOL, '$argv');
	}
	else
	{
		define('ARGV', $argv);
		define('ARGC', $argc);
	}

	//
	function prompt($_string, $_return = false, $_repeat = true)
	{
		function get($_str, $_ret = false)
		{
			$res = readline($_str);
			
			if($_ret)
			{
				return $res;
			}
			else if(empty($res))
			{
				return null;
			}
			else
			{
				$res = strtolower($res);
				
				switch($res[0])
				{
					case 'y':
						return true;
					case 'n':
						return false;
				}
			}
			
			return null;
		}
		
		$result = get($_string, $_return);
		
		if(gettype($result) === 'string')
		{
			return $result;
		}
		else while($result === null)
		{
			$result = get($_string);
		}
		
		return $result;
	}
	
	function get_arguments($_index, $_hosts = true, $_unique = true)
	{
		if(gettype($_index) !== 'integer' || $_index < 0)
		{
			return null;
		}
		else if(ARGC <= $_index)
		{
			return null;
		}
		
		$result = array();

		for($i = $_index, $j = 0; $i < ARGC; ++$i)
		{
			if(empty(ARGV[$i]))
			{
				continue;
			}
			else if(ARGV[$i][0] === '-')
			{
				break;
			}
			else
			{
				$item = explode(',', ARGV[$i]);
				
				for($k = 0; $k < count($item); ++$k)
				{
					if(! empty($item[$k]))
					{
						$result[$j++] = $item[$k];
					}
				}
			}
		}

		if($_hosts) for($i = 0; $i < count($result); ++$i)
		{
			if(($result[$i] = secure_host($result[$i], true, false)) === null)
			{
				array_splice($result, $i--, 1);
			}
		}

		if(empty($result))
		{
			return null;
		}
		else if($_unique)
		{
			$result = array_unique($result);
		}

		return $result;		
	}

	//
	function get_hosts($_path = PATH, $_values = true)
	{
		$list = count_files($_path, ($_values ? false : null), ($_values ? true : false), true, null);

		if($list === null)
		{
			return null;
		}

		$result = array();

		for($i = 0, $j = 0; $i < count($list); ++$i)
		{
			if(! in_array($list[$i], $result))
			{
				$result[$j++] = $list[$i];
			}
		}
		
		if(count($result) === 0)
		{
			return null;
		}

		return $result;
	}

	//
	function info($_index = -1, $_version = true, $_copyright = true)
	{
		if($_version)
		{
			printf('v' . VERSION . PHP_EOL);
		}

		if($_copyright)
		{
			printf('Copyright (c) %s' . PHP_EOL, COPYRIGHT);
		}

		exit(0);
	}

	function syntax($_argc)
	{
		if($_argc > 1)
		{
			fprintf(STDERR, ' >> Invalid syntax (parameter, if any, not available)' . PHP_EOL);
		}
		else
		{
			printf(' >> Available parameters (use only one at the same time, please):' . PHP_EOL);
		}

		printf(PHP_EOL);
		printf('    -? / --help (TODO)' . PHP_EOL);
		printf('    -V / --version' . PHP_EOL);
		printf('    -C / --copyright' . PHP_EOL);
		printf('    -h / --hashes' . PHP_EOL);
		printf('    -f / --fonts' . PHP_EOL);
		printf('    -t / --types' . PHP_EOL);
		printf('    -c / --config' . PHP_EOL);
		printf('    -v / --values [host,..]' . PHP_EOL);
		printf('    -n / --sync [host,..]' . PHP_EOL);
		printf('    -l / --clean [host,..]' . PHP_EOL);
		printf('    -p / --purge [host,..]' . PHP_EOL);
		printf('    -e / --errors' . PHP_EOL);
		printf('    -u / --unlog' . PHP_EOL);
		printf(PHP_EOL);

		exit(0);
	}

	function help($_index = -1)
	{
		die('TODO: help()' . PHP_EOL);
	}
	
	function hashes($_index = -1)
	{
		printf(' >> So, these are the available hash(ing) algorithms:' . PHP_EOL . PHP_EOL);
		
		$list = hash_algos();
		$len = count($list);
		
		for($i = 0; $i < $len; ++$i)
		{
			printf(' >> \'%s\'' . PHP_EOL, $list[$i]);
		}
		
		exit(0);
	}

	function fonts($_index = -1)
	{
		if(gettype(PATH) !== 'string' || empty(PATH))
		{
			fprintf(STDERR, ' >> \'PATH\' is not properly configured' . PHP_EOL);
			exit(1);
		}
		else if(gettype(FONTS) !== 'string' || empty(FONTS))
		{
			fprintf(STDERR, ' >> \'FONTS\' path is not properly configured' . PHP_EOL);
			exit(2);
		}

		$fonts = get_arguments($_index + 1, false);

		if($fonts !== null)
		{
			for($i = 0; $i < count($fonts); ++$i)
			{
				if(($fonts[$i] = secure_path($fonts[$i], true)) === null)
				{
					array_splice($fonts, $i--, 1);
				}
				else if(ends_with($fonts[$i], '.ttf'))
				{
					$fonts[$i] = substr($fonts[$i], 0, -4);
				}
			}
			
			if(count($fonts) === 0)
			{
				fprintf(STDERR, ' >> No fonts left after securing their strings' . PHP_EOL);
				exit(3);
			}
		}

		$available = count_fonts(FONTS, false, true);

		if($available === null)
		{
			fprintf(STDERR, ' >> No fonts installed in your fonts directory \'%s\'!' . PHP_EOL, FONTS);
			exit(4);
		}

		$len = count($available);
		printf(' >> You have %d fonts installed (in directory \'%s\')! :-)' . PHP_EOL . PHP_EOL, $len, FONTS);
		
		if($fonts === null)
		{
			for($i = 0; $i < $len; ++$i)
			{
				printf('    %s' . PHP_EOL, $available[$i]);
			}
		}
		else
		{
			$item = 0;
			$maxLen = 0;
			$len = count($fonts);
			
			for($i = 0; $i < $len; ++$i)
			{
				if(($item = strlen($fonts[$i])) > $maxLen)
				{
					$maxLen = $item;
				}
			}
			
			++$maxLen;
			$format = ' %' . $maxLen . 's: %s' . PHP_EOL;
			
			for($i = 0; $i < $len; ++$i)
			{
				printf($format, $fonts[$i], (in_array($fonts[$i], $available) ? 'YES :-)' : 'NO!'));
			}
		}

		exit(0);
	}

	function types($_index = -1)
	{
		if(!extension_loaded('gd'))
		{
			fprintf(STDERR, ' >> The GD library/extension is not loaded/available' . PHP_EOL);
			exit(1);
		}

		$selection = get_arguments($_index + 1, false);
		$types = imagetypes();

		if($selection === null)
		{
			$png = ($types & IMG_PNG);
			$jpg = ($types & IMG_JPG);

			$avail = 0;
			$avail += ($png ? 1 : 0);
			$avail += ($jpg ? 1 : 0);

			printf(' >> These are the %d available image types:' . PHP_EOL . PHP_EOL, $avail);

			if($png)
			{
				printf('    png' . PHP_EOL);
			}

			if($jpg)
			{
				printf('    jpg' . PHP_EOL);
			}

			printf(PHP_EOL);
		}
		else
		{
			$sel = array();
			$len = count($selection);

			for($i = 0, $j = 0; $i < $len; ++$i)
			{
				if(($selection[$i] = strtolower($selection[$i]))[0] === '.')
				{
					$selection[$i] = substr($selection[$i], 1);
				}

				if($selection[$i] === 'png')
				{
					if(!in_array($selection[$i], $sel))
					{
						$sel[$j++] = $selection[$i];
					}
				}
				else if($selection[$i] === 'jpg')
				{
					if(!in_array($selection[$i], $sel))
					{
						$sel[$j++] = $selection[$i];
					}
				}
				else
				{
					fprintf(STDERR, ' >> Image type \'%s\' is invalid..' . PHP_EOL, $selection[$i]);
				}
			}

			$selection = $sel;
			$len = count($selection);

			if($len > 0)
			{
				printf(PHP_EOL);
			}

			for($i = 0; $i < $len; ++$i)
			{
				switch($selection[$i])
				{
					case 'png':
						if($types & IMG_PNG)
						{
							printf('    png: YES :-D' . PHP_EOL);
						}
						else
						{
							fprintf(STDERR, '    png: NO. :-(' . PHP_EOL);
						}
						break;
					case 'jpg':
						if($types & IMG_JPG)
						{
							printf('    jpg: YES :-D' . PHP_EOL);
						}
						else
						{
							fprintf(STDERR, '    jpg: NO. :-(' . PHP_EOL);
						}
						break;
				}
			}

			if($len > 0)
			{
				printf(PHP_EOL);
			}
		}

		exit(0);
	}
	
	function config($_index = -1)
	{
		//
		printf(' >> We\'re testing your configuration right now.' . PHP_EOL);
		fprintf(STDERR, ' >> Beware: the DRAWING options are not finished in this --config/-c function! JFYI..' . PHP_EOL);
		printf(PHP_EOL);

		//
		$ok = 0;
		$errors = 0;
		$warnings = 0;

		//
		define('START', '%12s: %-7s');

		//
		if(gettype(AUTO) === 'boolean')
		{
			printf(START.'Boolean type (and could also be an Integer above 0)' . PHP_EOL, 'AUTO', 'OK');
			++$ok;
		}
		else if(gettype(AUTO) === 'integer')
		{
			if(AUTO < 0)
			{
				fprintf(STDERR, START.'Integer, but below 0/1' . PHP_EOL, 'AUTO', 'BAD');
				++$errors;
			}
			else if(AUTO === 0)
			{
				fprintf(STDERR, START.'Integer, but equals 0 - where (false) would be better' . PHP_EOL, 'AUTO', 'WARN');
				++$warnings;
			}
			else
			{
				printf(START.'Integer above 0 (and could also be a Boolean)' . PHP_EOL, 'AUTO', 'OK');
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'Neither a Boolean nor an Integer above 0/1' . PHP_EOL, 'AUTO', 'BAD');
			++$errors;
		}

		//
		if(gettype(THRESHOLD) === 'integer' && THRESHOLD >= 0)
		{
			if(THRESHOLD < 1)
			{
				fprintf(STDERR, START.'Integer, but below 1' . PHP_EOL, 'THRESHOLD', 'BAD');
				++$errors;
			}
			else
			{
				printf(START.'Integer above 0' . PHP_EOL, 'THRESHOLD', 'OK');
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer above 0' . PHP_EOL, 'THRESHOLD', 'BAD');
			++$errors;
		}

		//
		if(gettype(PATH) === 'string' && !empty(PATH))
		{
			if(is_dir(PATH) && is_writable(PATH))
			{
				printf(START.'Non-empty PATH String (directory exists and is writable :-)' . PHP_EOL, 'PATH', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'Non-empty PATH String (BUT is no existing directory or is just not writable)' . PHP_EOL, 'PATH', 'WARN');
				++$warnings;
			}
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'PATH', 'BAD');
			++$errors;
		}

		//
		if(gettype(OVERRIDE) === 'boolean')
		{
			printf(START.'Boolean type, great.' . PHP_EOL, 'OVERRIDE', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Not a boolean type' . PHP_EOL, 'OVERRIDE', 'BAD');
			++$errors;
		}

		//
		if(gettype(CLIENT) === 'boolean')
		{
			printf(START.'Boolean type' . PHP_EOL, 'CLIENT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'CLIENT', 'BAD');
			++$errors;
		}

		//
		if(gettype(SERVER) === 'boolean')
		{
			printf(START.'Boolean type' . PHP_EOL, 'SERVER', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'SERVER', 'BAD');
			++$errors;
		}

		//
		if(gettype(HASH) === 'string' && !empty(HASH))
		{
			if(in_array(HASH, hash_algos()))
			{
				printf(START.'String which exists in `hash_algos()`' . PHP_EOL, 'HASH', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'String is not available in `hash_algos()`' . PHP_EOL, 'HASH', 'BAD');
				++$errors;
			}
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String (within `hash_algos()`)' . PHP_EOL, 'HASH', 'BAD');
			++$errors;
		}

		//
		if(gettype(HASH_IP) === 'boolean')
		{
			printf(START.'Boolean type' . PHP_EOL, 'HASH_IP', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'HASH_IP', 'ERROR');
			++$errors;
		}

		//
		if(gettype(CONTENT) === 'string' && !empty(CONTENT))
		{
			printf(START.'Non-empty String' . PHP_EOL, 'CONTENT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'CONTENT', 'BAD');
			++$errors;
		}

		//
		if(CLEAN === null)
		{
			printf(START.'Equals (null), and could also be a Boolean or an Integer above 0' . PHP_EOL, 'CLEAN', 'OK');
			++$ok;
		}
		else if(gettype(CLEAN) === 'boolean')
		{
			printf(START.'Boolean type, and could also be (null) or an Integer above 0' . PHP_EOL, 'CLEAN', 'OK');
			++$ok;
		}
		else if(gettype(CLEAN) === 'integer')
		{
			if(CLEAN <= 0)
			{
				fprintf(STDERR, START.'Integer, but below 0/1' . PHP_EOL, 'CLEAN', 'BAD');
				++$errors;
			}
			else if(CLEAN === 0)
			{
				fprintf(STDERR, START.'Integer, but equals 0 (that should be (true) instead)' . PHP_EOL, 'CLEAN', 'WARN');
				++$warnings;
			}
			else
			{
				printf(START.'Integer above 1 (and could also be (null) or a Boolean type)' . PHP_EOL, 'CLEAN', 'OK');
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'Neither (null), Boolean type nor Integer above 0/1' . PHP_EOL, 'CLEAN', 'BAD');
			++$errors;
		}

		//
		if(gettype(LIMIT) === 'integer' && LIMIT > -1)
		{
			printf(START.'Integer above or equal to 0' . PHP_EOL, 'LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Integer above or equal to 0' . PHP_EOL, 'LIMIT', 'BAD');
			++$errors;
		}

		//
		if(gettype(LOG) === 'string' && !empty(LOG))
		{
			printf(START.'Non-empty String (without further tests)' . PHP_EOL, 'LOG', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'LOG', 'BAD');
			++$errors;
		}

		//
		if(gettype(ERROR) === 'string')
		{
			printf(START.'String (can be zero-length; and can also be \'anything\')' . PHP_EOL, 'ERROR', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No String, but even that is O.K. here!' . PHP_EOL, 'ERROR', 'OK');
			++$ok;
		}

		if(gettype(NONE) === 'string')
		{
			printf(START.'String (can be zero-length)' . PHP_EOL, 'NONE', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No String' . PHP_EOL, 'NONE', 'BAD');
			++$errors;
		}

		//
		if(gettype(DRAW) === 'boolean')
		{
			if(DRAW)
			{
				if(extension_loaded('gd'))
				{
					printf(START.'Enabled drawing option, and the \'GD Library\' is installed.' . PHP_EOL, 'DRAW', 'OK');
					++$ok;
				}
				else
				{
					fprintf(STDERR, START.'Enabled drawing option, but the \'GD Library\' is not installed (at least in CLI mode)' . PHP_EOL, 'DRAW', 'WARN');
					++$warnings;
				}
			}
			else
			{
				printf(START.'Disabled drawing. That\'s also OK.' . PHP_EOL, 'DRAW', 'OK');
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'DRAW', 'BAD');
			++$errors;
		}

		if(gettype(SIZE) === 'integer' && SIZE > 5)
		{
			$limit = SIZE_LIMIT;

			if(gettype($limit) !== 'integer')
			{
				$limit = null;
			}
			else if($limit < 6 || $limit > 512)
			{
				$limit = null;
			}

			if($limit === null)
			{
				fprintf(STDERR, START.'Integer above 5 (WARNING: can\'t test against invalid SIZE_LIMIT)' . PHP_EOL, 'SIZE', 'WARN');
				++$warnings;
			}
			else if(SIZE > $limit)
			{
				fprintf(STDERR, START.'Integer exceeds SIZE_LIMIT (%d)' . PHP_EOL, 'SIZE', 'BAD', $limit);
				++$errors;
			}
			else
			{
				printf(START.'Integer above 5 and below or equal to SIZE_LIMIT (%d)' . PHP_EOL, 'SIZE', 'OK', $limit);
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer above 0 and below or equal to SIZE_LIMIT' . PHP_EOL, 'SIZE', 'BAD');
			++$errors;
		}

		if(gettype(SIZE_LIMIT) === 'integer' && SIZE_LIMIT > 6 && SIZE_LIMIT <= 512)
		{
			printf(START.'Integer above 5 and below or equal to 512' . PHP_EOL, 'SIZE_LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Integer above 5 and below or equal to 512' . PHP_EOL, 'SIZE_LIMIT', 'BAD');
			++$errors;
		}
		
		if(gettype(SPACE) === 'integer' && SPACE >= 0)
		{
			$limit = SPACE_LIMIT;
			
			if(gettype($limit) !== 'integer')
			{
				$limit = null;
			}
			else if($limit < 0 || $limit > 512)
			{
				$limit = null;
			}
			
			if($limit === null)
			{
				fprintf(STDERR, START.'Integer above -1 (WARNING: can\'t test against invalid SPACE_LIMIT)' . PHP_EOL, 'SPACE', 'WARN');
				++$warnings;
			}
			else if(SPACE > $limit)
			{
				fprintf(STDERR, START.'Integer exceeds SPACE_LIMIT (%d)' . PHP_EOL, 'SPACE', 'BAD', $limit);
				++$errors;
			}
			else
			{
				printf(START.'Integer above -1 and below or equal to SPACE_LIMIT (%d)' . PHP_EOL, 'SPACE', 'OK', $limit);
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer above -1 and below or equal to SPACE_LIMIT' . PHP_EOL, 'SPACE', 'BAD');
			++$errors;
		}
		
		if(gettype(SPACE_LIMIT) === 'integer' && SPACE_LIMIT >= 0 && SPACE_LIMIT <= 512)
		{
			printf(START.'Integer above -1 and below or equal to 512' . PHP_EOL, 'SPACE_LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Not an Integer above -1 and below or equal to 512' . PHP_EOL, 'SPACE_LIMIT', 'BAD');
			++$errors;
		}
		
		if(gettype(PAD) === 'integer' && PAD >= 0)
		{
			$limit = PAD_LIMIT;
			
			if(gettype($limit) !== 'integer')
			{
				$limit = null;
			}
			else if($limit < 0 || $limit > 512)
			{
				$limit = null;
			}
			
			if($limit === null)
			{
				fprintf(STDERR, START.'Integer above -1 (WARNING: can\'t test against invalid PAD_LIMIT)' . PHP_EOL, 'PAD', 'WARN');
				++$warnings;
			}
			else if(PAD > $limit)
			{
				fprintf(STDERR, START.'Integer exceeds SPACE_LIMIT (%d)' . PHP_EOL, 'PAD', 'BAD', $limit);
				++$errors;
			}
			else
			{
				printf(START.'Integer above -1 and below or equal to SPACE_LIMIT (%d)' . PHP_EOL, 'PAD', 'OK', SPACE_LIMIT);
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer above -1 and below or equal to SPACE_LIMIT'. PHP_EOL, 'PAD', 'BAD');
			++$errors;
		}
		
		if(gettype(PAD_LIMIT) === 'integer' && PAD_LIMIT >= 0 && PAD_LIMIT <= 512)
		{
			printf(START.'Integer above -1 and below or equal to 512' . PHP_EOL, 'PAD_LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Not an Integer above -1 and below or equal to 512' . PHP_EOL, 'PAD_LIMIT', 'BAD');
			++$errors;
		}

		if(gettype(FONT) === 'string' && !empty(FONT))
		{
			$available = count_fonts(FONTS, false, true);

			if($available === null)
			{
				fprintf(STDERR, START.'Valid string, but you also need to install some fonts' . PHP_EOL, 'FONT', 'WARN');
				++$warnings;
			}
			else if(in_array(FONT, $available))
			{
				printf(START.'Valid font string, and exists in your \'FONTS\' directory' . PHP_EOL, 'FONT', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'There\'s no such font available (see \'FONTS\')' . PHP_EOL, 'FONT', 'BAD');
				++$errors;
			}
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'FONT', 'BAD');
			++$errors;
		}

		if(gettype(FONTS) === 'string')
		{
			if(is_dir(FONTS) && is_readable(FONTS))//exectable? everywhere where dir!
			{
				$available = count_fonts(FONTS, true);
				
				if($available === 0)
				{
					fprintf(STDERR, START.'Valid path string (and directory exists), but NO FONTS installed there! :-(' . PHP_EOL, 'FONTS', 'WARN');
					++$warnings;
				}
				else
				{
					printf(START.'Valid path string, and %d fonts are available there! :-)' . PHP_EOL, 'FONTS', 'OK', $available);
					++$ok;
				}
			}
			else
			{
				fprintf(STDERR, START.'Non-empty String (BUT is not a directory or not readable)' . PHP_EOL, 'FONTS', 'WARN');
				++$warnings;
			}
		}
		else
		{
			fprintf(STDERR, START.'No valid PATH String (non-empty)' . PHP_EOL, 'FONTS', 'BAD');
			++$errors;
		}

		if(gettype(FG) === 'string' && !empty(FG))
		{
			printf(START.'Non-empty String (without further tests)' . PHP_EOL, 'FG', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'FG', 'BAD');
			++$errors;
		}

		if(gettype(BG) === 'string' && !empty(BG))
		{
			printf(START.'Non-empty String (without further tests)' . PHP_EOL, 'BG', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'BG', 'BAD');
			++$errors;
		}

		//
		if(gettype(AA) === 'boolean')
		{
			printf(START.'Is a boolean' . PHP_EOL, 'AA', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Not a Boolean' . PHP_EOL, 'AA', 'BAD');
			++$errors;
		}

		//
		if(gettype(TYPE) === 'string' && !empty(TYPE))
		{
			if(!extension_loaded('gd'))
			{
				printf(START.'A non-empty String is valid, but I can\'t check for supported types atm.' . PHP_EOL, 'TYPE', 'WARN');
				++$warnings;
			}
			else
			{
				$avail = imagetypes();
				$result = null;

				switch(strtolower(TYPE))
				{
					case 'png':
						$result = ($avail & IMG_PNG);
						break;
					case 'jpg':
						$result = ($avail & IMG_JPG);
						break;
					default:
						break;
				}

				if($result)
				{
					printf(START.'Valid, supported image type.' . PHP_EOL, 'TYPE', 'OK');
					++$ok;
				}
				else
				{
					fprintf(STDERR, START.'Unsupported image type..' . PHP_EOL, 'TYPE', 'BAD');
					++$errors;
				}
			}
		}
		else
		{
			fprintf(STDERR, START.'No valid (non-empty) String' . PHP_EOL, 'TYPE', 'BAD');
			++$errors;
		}

		//
		printf(PHP_EOL);

		if($errors === 0)
		{
			printf(' >> All %d OK', $ok);

			if($warnings > 0)
			{
				fprintf(STDERR, ', but there are %d warnings.', $warnings);
			}

			printf('.' . PHP_EOL);
		}
		else
		{
			printf(' >> Only %d were OK..' . PHP_EOL, $ok);
			fprintf(STDERR, ' >> %d errors' . PHP_EOL, $errors);
			fprintf(STDERR, ' >> %d warnings' . PHP_EOL, $warnings);
		}

		if($errors === 0)
		{
			if($warnings === 0)
			{
				printf(' >> So everything\'s fine. Just continue to use this script. ;)~' . PHP_EOL);
				exit(0);
			}

			fprintf(STDERR, ' >> So it\'s OK to use this script, but maybe you should fix the %d warnings?' . PHP_EOL, $warnings);
			exit(2);
		}

		fprintf(STDERR, ' >> So you\'ve to fix %d errors (and %d warnings) now.' . PHP_EOL, $errors, $warnings);
		exit(1);
	}
	
	function values($_index = -1, $_path = PATH)
	{
		//
		$hosts = get_arguments($_index + 1, true);

		//
		if($hosts === null)
		{
			$hosts = get_hosts($_path, true);
		}

		if($hosts === null)
		{
			fprintf(STDERR, ' >> No hosts found!' . PHP_EOL);
			exit(1);
		}
		
		$files = array();
		$file = null;
		$origLen = count($hosts);

		for($i = 0, $j = 0; $i < count($hosts); ++$i)
		{
			$file = $_path . '/~' . secure_path($hosts[$i], false);
			
			if(is_file($file) && is_readable($file))
			{
				$files[$j++] = $file;
			}
			else
			{
				fprintf(STDERR, ' >> No host \'%s\' (or it\'s file is not readable)' . PHP_EOL, $hosts[$i]);
				array_splice($hosts, $i--, 1);
			}
		}
		
		$len = count($files);

		if($len === 0)
		{
			fprintf(STDERR, ' >> No hosts left' . ($origLen > 0 ? ' (from originally %d defined ones)' : '') . PHP_EOL, $origLen);
			exit(2);
		}

		$value = -1;
		$maxLen = 0;
		$currLen = 0;

		for($i = 0; $i < $len; ++$i)
		{
			if(($currLen = strlen($hosts[$i])) > $maxLen)
			{
				$maxLen = $currLen;
			}
		}

		++$maxLen;
		$START = '%' . $maxLen . 's: ';
		
		for($i = 0; $i < $len; ++$i)
		{
			if(($value = (int)file_get_contents($files[$i])) === false)
			{
				fprintf(STDERR, $START . 'Unable to read host file' . PHP_EOL, $$hosts[$i]);
			}
			else
			{
				printf($START . (string)$value . PHP_EOL, $hosts[$i]);
			}
		}

		//
		exit(0);
	}

	function sync($_index = null, $_path = PATH)
	{
		//
		$hosts = get_arguments($_index + 1, true);
		
		if($hosts === null)
		{
			$hosts = get_hosts($_path, false);
		}

		if($hosts === null)
		{
			fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
			exit(1);
		}

		$gotFile = null;
		$gotDir = null;
		
		for($i = 0; $i < count($hosts); ++$i)
		{
			$hosts[$i] = secure_path($hosts[$i], false);

			$gotFile = is_file($_path . '/-' . $hosts[$i]);
			$gotDir = is_dir($_path . '/+' . $hosts[$i]);
			
			if($gotFile && !$gotDir)
			{
				if(is_writable($_path . '/-' . $hosts[$i]) && remove($_path . '/-' . $hosts[$i], false, true))
				{
					printf(' >> Count file for not existing cache directory of host \'%s\' deleted.' . PHP_EOL, $hosts[$i]);
				}
				else
				{
					fprintf(STDERR, ' >> Count file for non existing cache directory of host \'%s\' couldn\'t be deleted.' . PHP_EOL, $hosts[$i]);
				}

				array_splice($hosts, $i--, 1);
			}
			else if($gotFile && $gotDir)
			{
				if(!is_writable($_path . '/-' . $hosts[$i]))
				{
					fprintf(STDERR, ' >> Count file for host \'%s\' is not writable.' . PHP_EOL, $hosts[$i]);
					array_splice($hosts, $i--, 1);
				}
			}
			else if(!$gotDir)
			{
				if(is_file($_path . '/~' . $hosts[$i]))
				{
					fprintf(STDERR, ' >> No cache files for this host \'%s\' available (but the host itself exists)' . PHP_EOL, $hosts[$i]);
				}
				else
				{
					fprintf(STDERR, ' >> The whole host \'%s\' is not existing (neither cache nor value files)' . PHP_EOL, $hosts[$i]);
				}

				array_splice($hosts, $i--, 1);
			}
		}

		$len = count($hosts);

		if($len === 0)
		{
			fprintf(STDERR, ' >> No cache directories found.' . PHP_EOL);
			exit(2);
		}
		
		$new = null;
		$orig = null;

		$synced = 0;
		$correct = 0;
		$created = 0;
		$errors = 0;
		
		for($i = 0; $i < $len; ++$i)
		{
			if(is_file($_path . '/-' . $hosts[$i]))
			{
				if(($orig = file_get_contents($_path . '/-' . $hosts[$i])) !== false)
				{
					$orig = (int)$orig;
				}
				else
				{
					$orig = null;
				}
			}
			else
			{
				$orig = null;
			}
			
			$new = count_files($_path . '/+' . $hosts[$i], false, false, false);
			
			if($orig === $new)
			{
				printf(' >> Count for host \'%s\' *is* in sync. :-)' . PHP_EOL, $hosts[$i]);
				++$correct;
			}
			else
			{
				if(file_put_contents($_path . '/-' . $hosts[$i], (string)$new) === false)
				{
					fprintf(STDERR, ' >> Couldn\'t write count for host \'%s\': %d => %d' . PHP_EOL, $hosts[$i], (int)$orig, $new);
					++$errors;
				}
				else if($orig !== null)
				{
					fprintf(STDERR, ' >> Count for host \'%s\' NOT in sync; corrected: %d => %d' . PHP_EOL, $hosts[$i], $orig, $new);
					++$synced;
				}
				else
				{
					printf(' >> Count for host \'%s\' initialized: %d' . PHP_EOL, $hosts[$i], $new);
					++$created;
				}
			}
		}
		
		//
		printf(PHP_EOL . ' >> %d were correct!' . PHP_EOL, $correct);
		fprintf(STDERR, ' >> %d synchronized now..' . PHP_EOL, $synced);
		printf(' >> %d newly created.' . PHP_EOL, $created);
		fprintf(STDERR, ' >> %d errors.. ' . ($errors === 0 ? ':-)' : ':-/') . PHP_EOL, $errors);
		
		//
		exit();
	}

	function clean($_index = -1, $_path = PATH, $_host = null)
	{
		//
		$hosts = get_arguments($_index + 1, true);
		
		if($hosts === null)
		{
			$hosts = get_hosts($_path, false);
		}

		if($hosts === null)
		{
			fprintf(STDERR, ' >> No hosts available' . PHP_EOL);
			exit(1);
		}
		
		$originalLen = count($hosts);
		
		for($i = 0; $i < count($hosts); ++$i)
		{
			$hosts[$i] = secure_path($hosts[$i], false);

			if(! is_dir($_path . '/+' . $hosts[$i]))
			{
				fprintf(STDERR, ' >> No cache directory for host \'%s\'' . PHP_EOL, $hosts[$i]);
				array_splice($hosts, $i--, 1);
			}
		}

		$len = count($hosts);
				
		if($len === 0)
		{
			fprintf(STDERR, ' >> No host left (of %d found)' . PHP_EOL, $originalLen);
			exit(2);
		}
		else
		{
			$originalLen = $len;
		}

		//
		$adapted = array();
		$delete = array();
		$origLen = 0;
		$files = null;
		$item = null;
		$len = null;
		$err = 0;
		$inTime = 0;
		
		for($i = 0, $j = 0, $origLen = 0, $l = 0; $i < count($hosts); ++$i, $origLen = $j)
		{
			if(($len = count($files = count_files($_path . '/+' . $hosts[$i], false, false, true))) === 0)
			{
				printf(' >> No cache files for host \'%s\' collected' . PHP_EOL, $hosts[$i]);
			}
			else for($k = 0; $k < $len; ++$k)
			{
				$item = $_path . '/+' . $hosts[$i] . '/' . $files[$k];

				if(is_readable($item))
				{
					if(timestamp((int)file_get_contents($item)) > THRESHOLD)
					{
						if(is_writable($item))
						{
							$delete[$j++] = $item;
						}
						else
						{
							fprintf(STDERR, ' >> File \'%s\' is no longer within THRESHOLD, but it\'s not writable!' . PHP_EOL, $item);
							++$err;
						}
					}
					else
					{
						++$inTime;
					}
				}
				else
				{
					fprintf(STDERR, ' >> File \'%s\' is not readable, so we can\'t read the timestamp in it..' . PHP_EOL, $files[$k]);
					++$err;
				}
			}
			
			if($j > $origLen)
			{
				$adapted[$l++] = [ $hosts[$i], ($j - $origLen) ];
			}
		}

		$len = count($delete);

		if($len === 0)
		{
			printf(' >> No outdated files to delete (counted %d of these).' . PHP_EOL, $inTime);
			
			if($err > 0)
			{
				fprintf(STDERR, ' >> .. but there were %d errors. :-/' . PHP_EOL, $err);
				exit(3);
			}
			
			exit(0);
		}
		else
		{
			printf(' >> Found %d outdated cache files to delete; their hosts (w/ counts) are:' . PHP_EOL, $len, $inTime);
			$adaptedLen = count($adapted);
			
			for($i = 0; $i < $adaptedLen; ++$i)
			{
				printf('    %s (%d)' . PHP_EOL, $adapted[$i][0], $adapted[$i][1]);
			}
			
			printf(PHP_EOL);
		}
		
		if(! prompt('Do you really want to remove %d cache files [yes/no]? '))
		{
			fprintf(STDERR, ' >> Aborted.' . PHP_EOL);
			exit(4);
		}
		else
		{
			printf(PHP_EOL);
		}
		
		$ok = 0;
		$err = 0;
		$len = count($delete);
		
		for($i = 0; $i < $len; ++$i)
		{
			if(remove($delete[$i], false, true))
			{
				++$ok;
			}
			else
			{
				++$err;
			}
		}

		printf(' >> Successfully deleted %d files! :-)' . PHP_EOL, $ok);
		$ok = 0;
		
		$len = count($adapted);
		
		for($i = 0; $i < $len; ++$i)
		{
			$item = count_files($_path . '/+' . ($adapted[$i] = $adapted[$i][0]), false, false, false, false);
			
			if(is_writable($_path . '/-' . $adapted[$i]))
			{
				if(file_put_contents($_path . '/-' . $adapted[$i], (string)$item) === false)
				{
					fprintf(STDERR, ' >> Synchronization for host \'%s\' failed (couldn\'t write to file)!' . PHP_EOL, $adapted[$i]);
				}
				else
				{
					++$ok;
				}
			}
			else
			{
				fprintf(STDERR, ' >> Count file for host \'%s\' not writable (so synchronization not possible here)' . PHP_EOL, $adapted[$i]);
			}
		}
		
		printf(' >> Synchronization for %d hosts also succeeded! :-D' . PHP_EOL, $ok);
		
		if($err > 0)
		{
			fprintf(STDERR, ' >> Deletion of %d files failed.. :-(' . PHP_EOL, $err);
			exit(5);
		}

		exit(0);
	}

	function purge($_index = -1, $_path = PATH, $_host = null)
	{
		//
		$hosts = get_arguments($_index + 1);

		if($hosts === null)
		{
			$hosts = get_hosts($_path, false);
		}

		if($hosts === null)
		{
			printf('No hosts available to purge their cache files.' . PHP_EOL);
			exit(0);
		}
		else for($i = 0; $i < count($hosts); ++$i)
		{
			if(!is_dir($_path . '/+' . $hosts[$i]) && !is_file($_path . '/-' . $hosts[$i]))
			{
				fprintf(STDERR, ' >> Neither cache nor count file for host \'%s\' found.' . PHP_EOL, $hosts[$i]);
				array_splice($hosts, $i--, 1);
			}
		}
		
		$len = count($hosts);

		if($len === 0)
		{
			fprintf(STDERR, ' >> No hosts to purge the cache files' . PHP_EOL);
			exit(1);
		}
		else
		{
			printf(PHP_EOL . ' >> Selected following hosts for purging all their cache/count files:' . PHP_EOL);

			for($i = 0; $i < $len; ++$i)
			{
				printf('    %s' . PHP_EOL, $hosts[$i]);
			}

			printf(PHP_EOL);
		}

		if(! (prompt('Do you really want to purge the cache for ' . count($hosts) . ' hosts [yes/no]? ')))
		{
			fprintf(STDERR, ' >> Aborting, as wished by you!' . PHP_EOL);
			exit(2);
		}
		
		$result = array();
		$errors = array();
		$sub = null;
		
		for($i = 0, $r = 0, $e = 0; $i < count($hosts); ++$i)
		{
			$sub = $_path . '/-' . $hosts[$i];
			
			if(is_file($sub))
			{
				if(remove($sub, true, true))
				{
					$result[$r++] = $sub;
				}
				else
				{
					$errors[$e++] = $sub;
				}
			}
			else if(file_exists($sub))
			{
				$errors[$e++] = $sub;
			}
			
			$sub = $_path . '/+' . $hosts[$i];
			
			if(is_dir($sub))
			{
				if(remove($sub, true, false))
				{
					$result[$r++] = $sub;
				}
				else
				{
					$errors[$e++] = $sub;
				}
			}
			else if(file_exists($sub))
			{
				$errors[$e++] = $sub;
			}
		}

		$countResult = count($result);
		$countErrors = count($errors);
		
		if($countResult === 0 && $countErrors === 0)
		{
			printf(' >> No cache files for deletion available. :-)' . PHP_EOL);
			exit(0);
		}
		
		if($countResult === 0)
		{
			fprintf(STDERR, ' >> NO elements purged' . PHP_EOL);
		}
		else
		{
			printf(' >> %d elements purged' . PHP_EOL, $countResult);
		}
		
		if($countErrors === 0)
		{
			printf(' >> NO errors! :-D' . PHP_EOL);
		}
		else
		{
			fprintf(STDERR, ' >> %d errors occured' . PHP_EOL, $countErrors);
		}
		
		if($countResult > 0)
		{
			printf(PHP_EOL . ' >> This elements have been removed:' . PHP_EOL);
			
			for($i = 0; $i < $countResult; ++$i)
			{
				printf('    >> \'' . $result[$i] . '\'' . PHP_EOL);
			}
		}
		
		if($countErrors > 0)
		{
			fprintf(STDERR, PHP_EOL . ' >> This elements couldn\'t be removed:' . PHP_EOL);
			
			for($i = 0; $i < $countErrors; ++$i)
			{
				fprintf(STDERR, '    >> \'' . $errors[$i] . '\'' . PHP_EOL);
			}
		}

		//
		exit(0);
	}

	function unlog($_index = -1, $_path = LOG)
	{
		error_reporting(0);

		if(! file_exists($_path))
		{
			fprintf(STDERR, ' >> There was no \'%s\' which could be deleted.' . PHP_EOL, $_path);
			exit(1);
		}
		else if(!is_file($_path))
		{
			fprintf(STDERR, ' >> The \'%s\' is not a regular file. Please replace/remove it asap!' . PHP_EOL, $_path);
		}

		$input = prompt('Do you really want to delete the file \'' . $_path . '\' [yes/no]? ');

		if(!$input)
		{
			fprintf(STDERR, ' >> Log file deletion aborted (by request).' . PHP_EOL);
			exit(2);
		}
		else if(remove($_path, false, true) === false)
		{
			fprintf(STDERR, ' >> The \'%s\' couldn\'t be deleted!!' . PHP_EOL, $_path);

			if(! is_file($_path))
			{
				fprintf(STDERR, ' >> I think it\'s not a regular file, could this be the reason why?' . PHP_EOL);
			}

			exit(2);
		}
		else
		{
			printf(' >> The \'%s\' is no longer.. :-)' . PHP_EOL, $_path);
		}

		exit(0);
	}

	function errors($_index = -1, $_path = LOG)
	{
		if(! file_exists($_path))
		{
			printf(' >> No errors logged! :-D' . PHP_EOL, $_path);
			exit(0);
		}
		else if(!is_file($_path))
		{
			fprintf(STDERR, ' >> \'%s\' is not a file! Please delete asap!!' . PHP_EOL, $_path);
			exit(1);
		}
		else if(!is_readable($_path))
		{
			fprintf(STDERR, ' >> Log file \'%s\' is not readable! Please correct this asap!!' . PHP_EOL, $_path);
			exit(2);
		}

		function count_lines($_file, $_chunks = 4096)
		{
			$res = 0;
			$handle = fopen($_file, 'r');

			while(!feof($handle))
			{
				$line = fgets($handle, $_chunks);
				$res += substr_count($line, PHP_EOL);
			}

			fclose($handle);
			return $res;
		}

		$result = count_lines($_path);

		if($result < 0)
		{
			$result = 0;
		}

		printf(' >> There are %d error log lines in \'%s\'..' . PHP_EOL, $result, $_path);
		exit(0);
	}

	//
	if(isset($argv)) for($i = 1; $i < $argc; ++$i)
	{
		if(strlen($argv[$i]) < 2 || $argv[$i][0] !== '-')
		{
			continue;
		}
		else if($argv[$i] === '-?' || $argv[$i] === '--help')
		{
			help($i);
		}
		else if($argv[$i] === '-V' || $argv[$i] === '--version')
		{
			info($i, true, false);
		}
		else if($argv[$i] === '-C' || $argv[$i] === '--copyright')
		{
			info($i, false, true);
		}
		else if($argv[$i] === '-v' || $argv[$i] === '--values')
		{
			values($i);
		}
		else if($argv[$i] === '-n' || $argv[$i] === '--sync')
		{
			sync($i);
		}
		else if($argv[$i] === '-c' || $argv[$i] === '--config')
		{
			config($i);
		}
		else if($argv[$i] === '-h' || $argv[$i] === '--hashes')
		{
			hashes($i);
		}
		else if($argv[$i] === '-f' || $argv[$i] === '--fonts')
		{
			fonts($i);
		}
		else if($argv[$i] === '-t' || $argv[$i] === '--types')
		{
			types($i);
		}
		else if($argv[$i] === '-l' || $argv[$i] === '--clean')
		{
			clean($i);
		}
		else if($argv[$i] === '-p' || $argv[$i] === '--purge')
		{
			purge($i);
		}
		else if($argv[$i] === '-e' || $argv[$i] === '--errors')
		{
			errors($i);
		}
		else if($argv[$i] === '-u' || $argv[$i] === '--unlog')
		{
			unlog($i);
		}
	}
	
	//
	printf(' >> Running in CLI mode now (so outside any HTTPD).' . PHP_EOL);
	syntax($argc);
	exit();
}

//
function get_host($_die = true)
{
	$result = null;

	//
	if(OVERRIDE && ($result = get_param('override', false)))
	{
		define('OVERRIDDEN', true);
	}
	else if(! empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'][0] !== ':')
	{
		$result = $_SERVER['HTTP_HOST'];
	}
	else if(! empty($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'][0] !== ':')
	{
		$result = $_SERVER['SERVER_NAME'];
	}
	else if($_die)
	{
		error('No server host/name applicable');
	}
	
	//
	if(!defined('OVERRIDDEN'))
	{
		define('OVERRIDDEN', false);
	}

	//
	$result = secure_host(remove_port($result, $_die), true, $_die);

	if($result === null && $_die)
	{
		error('Invalid host');
	}

	return $result;
}

function remove_port($_host, $_die = false)
{
	$result = null;
	$port = null;
	
	if(empty($_SERVER['SERVER_PORT']))
	{
		if($_die)
		{
			error('No $_SERVER[\'SERVER_PORT\'] defined');
		}
		
		$result = $_host;
	}
	else
	{
		$port = (string)$_SERVER['SERVER_PORT'];
	}
	
	if($port !== null && ends_with($_host, (':' . $port)))
	{
		$result = substr($_host, 0, -(strlen($port) + 1));
	}
	else
	{
		$result = $_host;
	}
	
	return $result;
}

//
$host = get_host(true);
define('HOST', $host);
define('COOKIE', hash(HASH, $host));
unset($host);

//
define('PATH_FILE', PATH . '/~' . secure_path(HOST, false));
define('PATH_DIR', PATH . '/+' . secure_path(HOST, false));
define('PATH_COUNT', PATH . '/-' . secure_path(HOST, false));
define('PATH_IP', PATH_DIR . '/' . secure_path((HASH_IP ? hash(HASH, $_SERVER['REMOTE_ADDR']) : secure_host($_SERVER['REMOTE_ADDR'], false)), false));

//
if(AUTO === null)
{
	error(NONE);
}
else
{
	check_path(PATH, PATH_FILE);
}

//
function timestamp($_difference = null)
{
	if(gettype($_difference) !== 'integer')
	{
		return time();
	}
	
	return (time() - $_difference);
}

function test()
{
	$result = true;

	if(CLIENT && !OVERRIDDEN)
	{
		$result = test_cookie();
	}

	if($result && SERVER)
	{
		$result = test_file();
	}

	return $result;
}

function test_file($_path = PATH_IP)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			log_error('Is not a regular file', 'test_file', $_path);
			error('Is not a regular file');
		}
		else if(!is_readable($_path))
		{
			log_error('File can\'t be read', 'test_file', $_path);
			error('File can\'t be read');
		}
		else if(timestamp(read_timestamp($_path)) < THRESHOLD)
		{
			return false;
		}
	}

	return true;
}

function test_cookie()
{
	if(empty($_COOKIE[COOKIE]))
	{
		make_cookie();
	}
	else if(timestamp((int)$_COOKIE[COOKIE]) < THRESHOLD)
	{
		return false;
	}

	return true;
}

function make_cookie()
{
	return setcookie(COOKIE, timestamp(), array(
		'expires' => (time() + THRESHOLD),
		'secure' => COOKIE_SECURE,
		'path' => COOKIE_PATH,
		'samesite' => COOKIE_SAME_SITE,
		'httponly' => COOKIE_HTTP_ONLY//,
		//'domain' => COOKIE_DOMAIN
	));
}

function clean_files($_dir = PATH_DIR, $_file = PATH_COUNT)
{
	if(CLEAN === null)
	{
		log_error('Called function, but CLEAN === null', 'clean_files', '', false);
		return -1;
	}
	else if(!is_dir($_dir))
	{
		return init_count($_file, $_dir, false);
	}

	$files = count_files($_dir, false, false, true);
	$len = count($files);

	if($len === 0)
	{
		return 0;
	}
	
	$item = null;
	$items = array();
	
	for($i = 0, $j = 0; $i < $len; ++$i)
	{
		if(is_writable($item = $_dir . '/' . $files[$i]) && is_writable($item))
		{
			$items[$j++] = $item;
		}
		else
		{
			log_error('File is either not readable or not writable', 'clean_files', $item, false);
		}
	}

	if(count($items) === 0)
	{
		return 0;
	}
	else for($i = 0; $i < count($items); ++$i)
	{
		if(timestamp((int)file_get_contents($items[$i])) <= THRESHOLD)
		{
			array_splice($items, $i--, 1);
		}
	}
	
	if(($len = count($items)) === 0)
	{
		return 0;
	}

	$files = 0;

	for($i = 0; $i < $len; ++$i)
	{
		if(remove($items[$i], false, false) === false)
		{
			log_error('Unable to remove() outdated file', 'clean_files', $items[$i], false);
		}
		else
		{
			++$files;
		}
	}
	
	return decrease_count($_file, $files);
}

function init_count($_path = PATH_COUNT, $_directory = PATH_DIR, $_die = false)
{
	if(is_dir($_directory))
	{
		$result = count_files($_directory, false, false, false);
	}
	else
	{
		$result = 0;
	}

	$written = file_put_contents($_path, (string)$result);

	if($written === false)
	{
		log_error('Couldn\'t initialize count', 'init_count', $_path, $_die);

		if($_die)
		{
			error('Couldn\'t initialize count');
		}
		
		return false;
	}

	return $result;
}

function read_count($_path = PATH_COUNT)
{
	if(!file_exists($_path))
	{
		return init_count($_path);
	}
	else if(!is_file($_path))
	{
		log_error('Count file is not a file', 'read_count', $_path);
		error('Count file is not a file');
	}

	$result = file_get_contents($_path);

	if($result === false)
	{
		log_error('Couldn\'t read count value', 'read_count', $_path);
		error('Couldn\'t read count value');
	}

	return (int)$result;
}

function write_count($_value, $_path = PATH_COUNT, $_get = true)
{
	$result = null;

	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			log_error('Count file is not a regular file', 'write_count', $_path);
			error('Count file is not a regular file');
		}
		else if($_get)
		{
			$result = read_count($_path);
		}
	}
	else
	{
		$result = init_count($_path);
	}

	$written = file_put_contents($_path, (string)$_value);

	if($written === false)
	{
		log_error('Unable to write count', 'write_count', $_path);
		error('Unable to write count');
	}

	return $result;
}

function increase_count($_path = PATH_COUNT, $_by = 1)
{
	$count = (read_count($_path) + $_by);
	write_count($count, $_path, false);
	return $count;
}

function decrease_count($_path = PATH_COUNT, $_by = 1)
{
	$count = (read_count($_path) - $_by);

	if($count < 0)
	{
		$count = 0;
	}

	write_count($count, $_path, false);
	return $count;
}

function read_timestamp($_path = PATH_IP)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			log_error('Is not a file', 'read_timestamp', $_path);
			error('Is not a file');
		}
		else if(!is_readable($_path))
		{
			log_error('File not readable', 'read_timestamp', $_path);
			error('File not readable');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
			log_error('Unable to read timestamp', 'read_timestamp', $_path);
			error('Unable to read timestamp');
		}

		return (int)$result;
	}

	return 0;
}

function write_timestamp($_path = PATH_IP, $_clean = (CLEAN !== null))
{
	$existed = file_exists($_path);

	if($existed)
	{
		if(!is_file($_path))
		{
			log_error('It\'s no regular file', 'write_timestamp', $_path);
			error('It\'s no regular file');
		}
		else if(!is_writable($_path))
		{
			log_error('Not a writable file', 'write_timestamp', $_path);
			error('Not a writable file');
		}
	}
	else if(read_count() > LIMIT)
	{
		if($_clean)
		{
			if(clean_files() > LIMIT)
			{
				log_error('LIMIT exceeded, even after clean_files()', 'write_timestamp', $_path);
				error('LIMIT exceeded, even after clean_files()');
				return null;
			}
		}
		else
		{
			log_error('LIMIT exceeded (and no clean_files() called)', 'write_timestamp', $_path);
			error('LIMIT exceeded; w/o clean_files() call');
			return null;
		}
	}
	
	$result = file_put_contents($_path, (string)timestamp());

	if($result === false)
	{
		log_error('Unable to write timestamp', 'write_timestamp', $_path);
		error('Unable to write timestamp');
	}
	else if(!$existed)
	{
		increase_count();
	}

	return $result;
}

function read_value($_path = PATH_FILE)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			log_error('It\'s not a regular file', 'read_value', $_path);
			error('It\'s not a regular file');
		}
		else if(!is_readable($_path))
		{
			log_error('File is not readable', 'read_value', $_path);
			error('File is not readable');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
			log_error('Unable to read value', 'read_value', $_path);
			error('Unable to read value');
		}

		return (int)$result;
	}
	else
	{
		touch($_path);
	}

	return 0;
}

function write_value($_value = 0, $_path = PATH_FILE, $_die = false)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			log_error('Not a regular file', 'write_value', $_path, $_die);

			if($_die)
			{
				error('Not a regular file');
			}
		}
		else if(!is_writable($_path))
		{
			log_error('File is not writable', 'write_value', $_path, $_die);

			if($_die)
			{
				error('File is not writable');
			}
		}
	}

	$result = file_put_contents($_path, (string)$_value);

	if($result === false)
	{
		log_error('Unable to write value', 'write_value', $_path, $_die);

		if($_die)
		{
			error('Unable to write value');
		}
	}

	return $result;
}

//
function draw($_text)
{
	//
	function draw_error($_reason, $_die = true)
	{
		//
		$res = sendHeader(CONTENT);

		//
		if($_die)
		{
			error($_reason);
			exit(255);
		}

		return $res;
	}

	//
	function get_font($_name, $_dir = FONTS)
	{
		$available = count_fonts($_dir, false, true);

		if($available === null)
		{
			return null;
		}
		else if(ends_with($_name, '.ttf'))
		{
			$_name = substr($_name, 0, -4);
		}
		
		if(in_array($_name, $available))
		{
			return (FONTS . '/' . $_name . '.ttf');
		}

		return null;
	}

	function get_drawing_options($_die = true)
	{
		//
		$result = array();

		//
		$result['size'] = get_param('size', true, false);
		$result['space'] = get_param('space', true, false);
		$result['pad'] = get_param('pad', true, false);
		$result['font'] = get_param('font', false);
		$result['fg'] = get_param('fg', false);
		$result['bg'] = get_param('bg', false);
		$result['x'] = get_param('x', true, false);
		$result['y'] = get_param('y', true, false);
		$result['aa'] = get_param('aa', null);
		$result['type'] = get_param('type', false);

		//
		if(! is_numeric($result['size']))
		{
			$result['size'] = SIZE;
		}
		else if($result['size'] > SIZE_LIMIT || $result['size'] < 0)
		{
			draw_error('\'?size\' exceeds limit (0 / ' . SIZE_LIMIT . ')', $_die);
			return null;
		}

		if(! is_numeric($result['space']))
		{
			$result['space'] = SPACE;
		}
		else if($result['space'] > SPACE_LIMIT || $result['space'] < 0)
		{
			draw_error('\'?space\' exceeds limit (0 / ' . SPACE_LIMIT . ')', $_die);
			return null;
		}

		if(! is_numeric($result['pad']))
		{
			$result['pad'] = PAD;
		}
		else if($result['pad'] > PAD_LIMIT || $result['pad'] < 0)
		{
			draw_error('\'?pad\' exceeds limit (0 / ' . PAD_LIMIT . ')', $_die);
			return null;
		}

		if($result['font'] === null)
		{
			$result['font'] = FONT;
		}

		if(($result['font'] = get_font($result['font'])) === null)
		{
			draw_error('\'?font\' is not available', $_die);
			return null;
		}
		else if(!is_file($result['font'] = realpath($result['font'])))
		{
			draw_error('\'?font\' is valid, but no such file found', $_die);
			return null;
		}

		if($result['fg'] === null)
		{
			$result['fg'] = FG;
		}

		if($result['bg'] === null)
		{
			$result['bg'] = BG;
		}

		$result['fg'] = get_color($result['fg'], true);

		if($result['fg'] === null)
		{
			draw_error('\'?fg\' is no valid rgb/rgba color', $_die);
			return null;
		}

		$result['bg'] = get_color($result['bg'], true);

		if($result['bg'] === null)
		{
			draw_error('\'?bg\' is no valid rgb/rgba color', $_die);
			return null;
		}

		if($result['x'] === null)
		{
			$result['x'] = 0;
		}
		else if($result['x'] > 512 || $result['x'] < -512)
		{
			$result['x'] = 0;
		}

		if($result['y'] === null)
		{
			$result['y'] = 0;
		}
		else if($result['x'] > 512 || $result['y'] < -512)
		{
			$result['y'] = 0;
		}
		
		if(gettype($result['aa']) !== 'boolean')
		{
			$result['aa'] = AA;
		}

		if(gettype($result['type']) !== 'string')
		{
			$result['type'] = TYPE;
		}

		$types = imagetypes();

		switch($result['type'] = strtolower($result['type']))
		{
			case 'png':
				if(! ($types & IMG_PNG))
				{
					draw_error('\'?type\' is not supported', $_die);
					return null;
				}
				break;
			case 'jpg':
				if(! ($types & IMG_JPG))
				{
					draw_error('\'?type\' is not supported', $_die);
					return null;
				}
				break;
			default:
				draw_error('\'?type\' is not supported', $_die);
				return null;
		}

		return $result;
	}

	function get_color($_string, $_fix_gd = true)
	{
		//
		$was = null;

		if(substr($_string, 0, 5) === 'rgba(')
		{
			$_string = substr($_string, 5);
			$was = 'rgba';
		}
		else if(substr($_string, 0, 4) === 'rgb(')
		{
			$_string = substr($_string, 4);
			$was = 'rgb';
		}
			
		if($_string[strlen($_string) - 1] === ')')
		{
			if($was === null)
			{
				return null;
			}

			$_string = substr($_string, 0, -1);
		}
		else if($was !== null)
		{
			return null;
		}

		//
		$result = array();
		$item = '';
		$len = strlen($_string);
		$byte = null;
		$hadPoint = false;

		for($i = 0, $j = 0; $i < $len; ++$i)
		{
			if($_string[$i] === ',')
			{
				if(strlen($item) === 0)
				{
					return null;
				}

				if($j === 3 && $hadPoint)
				{
					if($item[strlen($item) - 1] === '.')
					{
						$item = substr($item, 0, -1);
						$hadPoint = false;
					}
				}

				if($hadPoint)
				{
					$result[$j] = (float)$item;
					break;
				}

				$result[$j++] = (int)$item;
				$item = '';
			}
			else if(($byte = ord($_string[$i])) >= 48 && $byte <= 57)
			{
				if($j < 3 && strlen($item) >= 3)
				{
					return null;
				}
				
				$item .= $_string[$i];
			}
			else if($_string[$i] === '.')
			{
				if($j < 3)
				{
					return null;
				}
				else if($hadPoint)
				{
					return null;
				}
				else if(strlen($item) === 0)
				{
					$item = '0';
				}

				$hadPoint = true;
				$item .= '.';
			}
		}

		if(strlen($item) > 0)
		{
			if($hadPoint)
			{
				if($item[0] === '.')
				{
					$item = '0' . $item;
				}
				else if($item[strlen($item) - 1] === '.')
				{
					$item = substr($item, 0, -1);
					$hadPoint = false;
				}
			}

			if($hadPoint)
			{
				$result[] = (float)$item;
			}
			else
			{
				$result[] = (int)$item;
			}
		}

		$len = count($result);

		if($len < 3)
		{
			return null;
		}
		else if($len < 4)
		{
			if($was === null || $was === 'rgb')
			{
				$result[3] = 1.0;
			}
			else
			{
				return null;
			}
		}
		else
		{
			if($was === 'rgb')
			{
				return null;
			}
			else if(gettype($result[3]) === 'integer')
			{
				$result[3] = (float)$result[3];
			}
		}

		if($result[3] < 0 || $result[3] > 1)
		{
			return null;
		}
		else if($_fix_gd)
		{
			$result[3] = (int)(127 - ($result[3] * 127));
		}

		for($i = 0; $i < 3; ++$i)
		{
			if(gettype($result[$i]) !== 'integer')
			{
				return null;
			}
			else if($result[$i] < 0 || $result[$i] > 255)
			{
				return null;
			}
		}

		return $result;
	}

	function ptToPx($_pt)
	{
		return ($_pt * 0.75);
	}

	function pxToPt($_px)
	{
		return ($_px / 0.75);
	}

	function draw_text($_text, $_font, $_size, $_fg, $_bg, $_pad, $_space, $_x, $_y, $_aa, $_type)
	{
		//
		if(defined('SENT'))
		{
			draw_error('Header already sent (unexpected here)');
			return null;
		}

		//
		$px = $_size;
		$pt = pxToPt($px);

		//
		$measure = imagettfbbox($pt, 0, $_font, $_text);
		$textWidth = ($measure[2] - $measure[0]);
		$textHeight = ($measure[1] - $measure[7]);

		//
		$width = pxToPt($textWidth + ($_space * 2));
		$height = pxToPt($textHeight + ($_pad * 2));

		//
		$image = imagecreatetruecolor($width, $height);
		imagealphablending($image, false);
		imagesavealpha($image, true);
		imageantialias($image, $_aa);
		imagealphablending($image, true);

		//
		$_fg = imagecolorallocatealpha($image, $_fg[0], $_fg[1], $_fg[2], $_fg[3]);

		if(!$_aa)
		{
			if(($_fg = -$_fg) === 0)
			{
				$_fg = -1;
			}
		}
		
		$_bg = imagecolorallocatealpha($image, $_bg[0], $_bg[1], $_bg[2], $_bg[3]);
		imagefill($image, 0, 0, $_bg);

		//
		$x = ptToPx(($width - $textWidth + $_space) / 2) + $_x;
		$y = (($height + $textHeight) / 2) + $_y;

		//
		imagettftext($image, $pt, 0, $x, $y, $_fg, $_font, $_text);

		//
		switch(strtolower($_type))
		{
			case 'png':
				if($sent = sendHeader('image/png'))
				{
					imagepng($image);
				}
				break;
			case 'jpg':
				if($sent = sendHeader('image/jpeg'))
				{
					imagejpeg($image);
				}
				break;
		}
		
		//
		imagedestroy($image);
		
		if(!$sent)
		{
			draw_error('Header couldn\'t be sent');
			return false;
		}

		return true;
	}

	//
	$options = get_drawing_options();
	return draw_text($_text, $options['font'], $options['size'], $options['fg'], $options['bg'], $options['pad'], $options['space'], $options['x'], $options['y'], $options['aa'], $options['type']);
}

//
if(SERVER)
{
	if(! file_exists(PATH_DIR))
	{
		mkdir(PATH_DIR);
	}
	else if(!is_dir(PATH_DIR))
	{
		log_error('Not a directory', '', PATH_DIR);
		error('Not a directory');
	}
}

//
$value = read_value();
$readonly = get_param('readonly', null);

if(gettype($readonly) !== 'boolean')
{
	$readonly = false;
}

if(!$readonly)
{
	if(test())
	{
		write_value(++$value);
	}

	if(CLIENT && !OVERRIDDEN)
	{
		make_cookie();
	}
}

//
$value = (string)$value;

if(strlen($value) > 64)
{
	error('e');
}

//
if(DRAW && isset($_GET['draw']) && extension_loaded('gd'))
{
	draw($value);
}
else
{
	sendHeader(CONTENT);
	header('Content-Length: ' . strlen($value));
	echo $value;
}

//
if(SERVER && !$readonly)
{
	//
	write_timestamp();

	//
	if(CLEAN === true)
	{
		clean_files();
	}
	else if(gettype(CLEAN) === 'integer')
	{
		$count = read_count();

		if($count >= CLEAN)
		{
			clean_files();
		}
	}
}

//
exit();

?>
