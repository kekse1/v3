<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 * v2.17.2
 */

//
define('VERSION', '2.17.2');
define('COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');
define('HELP', 'https://github.com/kekse1/count.php/');

//
define('AUTO', 32);
define('THRESHOLD', 7200);
define('DIR', 'count');
define('HIDE', false);
define('OVERRIDE', false);
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
define('DRAWING', true);
define('SIZE', 24);
define('SIZE_LIMIT', 512);
define('FONT', 'SourceCodePro');
define('FONTS', 'fonts');
define('H', 0);
define('H_LIMIT', 256);
define('V', 0);
define('V_LIMIT', 256);
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
define('CLI', (php_sapi_name() === 'cli'));

//
if(!CLI)
{
	define('TEST', (isset($_GET['test'])));
	define('READONLY', (TEST || (isset($_GET['readonly']) || isset($_GET['ro']))));
	define('ZERO', (DRAWING && isset($_GET['zero']) && extension_loaded('gd')));
	define('DRAW', (ZERO || (DRAWING && isset($_GET['draw']) && extension_loaded('gd'))));
}

//
function normalize($_string, $_die = true)
{
	if(gettype($_string) !== 'string')
	{
		if($_die)
		{
			die('Invalid $_string argument' . (CLI ? PHP_EOL : ''));
		}
		
		return null;
	}
	
	$len = strlen($_string);
	
	if($len === 0)
	{
		return '.';
	}
	
	$abs = ($_string[0] === '/');
	$dir = ($_string[$len - 1] === '/');
	$split = explode('/', $_string);
	$result = array();
	$minus = 0;
	$item = '';
	
	while(count($split) > 0)
	{
		switch($item = array_shift($split))
		{
			case '':
			case '.':
				break;
			case '..':
				if(count($result) === 0)
				{
					++$minus;
				}
				else
				{
					array_pop($result);
				}
				break;
			default:
				array_push($result, $item);
				break;
		}
	}
	
	if($abs)
	{
		array_unshift($result, '');
	}
	else while(--$minus >= 0)
	{
		array_unshift($result, '..');
	}
	
	if($dir)
	{
		array_push($result, '');
	}
	
	//
	return implode('/', $result);
}

function join_path(... $_args)
{
	if(count($_args) === 0)
	{
		die('Invalid argument count' . (CLI ? PHP_EOL : ''));
	}

	$len = count($_args);
	$result = '';
	
	for($i = 0; $i < $len; ++$i)
	{
		if(gettype($_args[$i]) !== 'string')
		{
			die('Invalid argument[' . $i . ']' . (CLI ? PHP_EOL : ''));
		}

		$result .= $_args[$i] . '/';
	}
	
	if(strlen($result) > 0)
	{
		$result = substr($result, 0, -1);
	}
	
	return normalize($result);
}

function check_path_char($_path)
{
	$_path = basename($_path);
	
	switch($_path[0])
	{
		case '~':
		case '+':
		case '-':
			return false;
	}
	
	return true;
}

function get_path($_path, $_check = false, $_file = false)
{
	if(gettype($_path) !== 'string')
	{
		die('Path needs to be (non-empty) String' . (CLI ? PHP_EOL : ''));
	}
	else if(empty($_path))
	{
		die('Path may not be empty' . (CLI ? PHP_EOL : ''));
	}
	else if(!check_path_char($_path))
	{
		die('Invalid path \'' . $_path . '\' (may not begin with \'~\', \'+\' or \'-\')' . (CLI ? PHP_EOL : ''));
	}
	else if($_path === '/')
	{
		die('The root directory is not allowed here' . (CLI ? PHP_EOL : ''));
	}
	
	$result = '';

	if($_path[0] === '/')
	{
		$result = $_path;
	}
	else if($_path === '.')
	{
		$result = __DIR__;
	}
	else if(substr($_path, 0, 2) === './')
	{
		$result = __DIR__ . substr($_path, 1);
	}
	else if(getcwd() !== false)
	{
		$result = getcwd() . '/' . $_path;
	}
	else if(($result = realpath($_path)) === false)
	{
		$result = $_path;
	}
	
	$result = normalize($result);
	
	if($result === '/')
	{
		die('Root directory reached, which is not allowed here' . (CLI ? PHP_EOL : ''));
	}

	if($_check)
	{
		if($_file)
		{
			if(!is_dir(dirname($result)))
			{
				die('Directory of path \'' . $_path . '\' doesn\'t exist' . (CLI ? PHP_EOL : ''));
			}
		}
		else if(!is_dir($result))
		{
			die('Directory \'' . $_path . '\' doesn\'t exist' . (CLI ? PHP_EOL : ''));
		}
	}

	return $result;
}

define('PATH', get_path(DIR, true, false));
define('PATH_LOG', get_path(LOG, false, true));

if(DRAWING)
{
	define('PATH_FONTS', get_path(FONTS, true, false));
}
else
{
	define('PATH_FONTS', null);
}

if(! (is_readable(PATH)))// && is_writable(PATH)))
{
	die('Your \'DIR\' path is not readable' . (CLI ? PHP_EOL : ''));// and writable');
}
else if(DRAWING && !is_readable(PATH_FONTS))
{
	die('Your \'FONTS\' path is not readable' . (CLI ? PHP_EOL : ''));
}
else if(!is_dir(dirname(PATH_LOG)))
{
	die('Your \'LOG\' directory is not a directory' . (CLI ? PHP_EOL : ''));
}
else if(is_file(PATH_LOG) && !is_writable(PATH_LOG))
{
	die('Your existing \'LOG\' file is not writable' . (CLI ? PHP_EOL : ''));
}

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
	if(defined('FIN'))// && FIN)
	{
		return null;
	}
	else if(gettype($_reason) !== 'string')
	{
		$_reason = (string)$_reason;
	}
	
	if(CLI)
	{
		if(defined('STDERR'))
		{
			fprintf(STDERR, ' >> ' . $_reason . PHP_EOL);
		}
		else
		{
			die(' >> ' . $_reason . PHP_EOL);
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
		else if($byte === 47)
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

	$len = strlen($result);

	if($_null && $len === 0)
	{
		$result = null;
	}
	else if($len > 0)
	{
		$rem = 0;

		while(($len - 1 - $rem) >= 0 && $result[$len - 1 - $rem] === '.')
		{
			++$rem;
		}

		if($rem > 0)
		{
			$result = substr($result, 0, -$rem);
		}
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
		if(strlen($result) === 0 && ($value[$i] === '-' || $value[$i] === '+' || $value[$i] === '~'))
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
	$len = strlen($value);

	if($_numeric) while($remove < ($len - 1) && $value[$remove] === '+' || $value[$remove] === '-')
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
	
	if(!defined('PATH_LOG') || empty(PATH_LOG))
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
			$data .= basename($_path);
		}

		$data .= ')';
	}
	else if(!empty($_path))
	{
		$data .= '(' . basename($_path) . ')';
	}

	$data .= ': ' . $_reason . PHP_EOL;
	
	if($noLog)
	{
		error($result = $data);
	}
	else
	{
		$result = file_put_contents(PATH_LOG, $data, FILE_APPEND);

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
//FIXME/TODO/get_files und count_files evtl. im CLI-bereich allein..!?! wird doch nicht sonst eingesetzt, eh? :D~
//
function get_files($_path, $_count = false, $_null = true, $_die = true)
{
	if(!is_dir($_path))
	{
		log_error('Path is not an existing directory', 'get_files', $_path, $_die);

		if($_die)
		{
			error('Path is not an existing directory');
		}
		
		return null;
	}
	
	$handle = opendir($_path);
	
	if($handle === false)
	{
		log_error('Directory couldn\'t be opened', 'get_files', $_path, $_die);
		
		if($_die)
		{
			error('Directory couldn\'t be opened');
		}
		
		return null;
	}
	
	$result = ($_count ? 0 : array());
	$index = 0;
	
	while($sub = readdir($handle))
	{
		if($sub[0] === '.' || $sub === '..')
		{
			continue;
		}
		else if($_count)
		{
			++$result;
		}
		else
		{
			$result[$index++] = $sub;
		}
	}
	
	closedir($handle);
	
	if($_null && !$_count && count($result) === 0)
	{
		return null;
	}
	
	return $result;
}

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
		if($sub[0] === '.' || $sub === '..')
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
			if(!is_file(join_path($_path, $sub)))
			{
				continue;
			}
		}
		else if($_dir === true)
		{
			if(!is_dir(join_path($_path, $sub)))
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
			if($_filter === true || $_filter === null)
			{
				if(in_array($sub, $result))
				{
					continue;
				}
			}

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
			if($sub === '.' || $sub === '..')
			{
				continue;
			}
			else if(is_dir(join_path($_path, $sub)))
			{
				remove($_path . '/' . $sub, true, $_die, $_depth_current + 1);
			}
			else if(unlink(join_path($_path, $sub)))
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
if(CLI)
{
	//
	if(! (defined('STDIN') && defined('STDOUT')))
	{
		die(' >> Running in CLI mode, but \'STDIN\' and/or \'STDOUT\' are not set!' . PHP_EOL);
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
	define('TYPE_VALUE', 1);
	define('TYPE_DIR', 2);
	define('TYPE_FILE', 4);

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
	
	function get_arguments($_index, $_null = true, $_unique = true)
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
			if(strlen(ARGV[$i]) === 0)
			{
				continue;
			}
			else if(ARGV[$i][0] === '-')
			{
				break;
			}
			else if($_unique && in_array(ARGV[$i], $result))
			{
				continue;
			}
			
			$result[$j++] = ARGV[$i];
		}

		if(count($result) === 0)
		{
			if($_null)
			{
				return null;
			}
		}

		return $result;		
	}

	function get_list($_index, $_values)
	{
		$list = null;

		if(gettype($_index) === 'integer')
		{
			$list = get_arguments($_index, true, true);
		}

		$result = array();
		$result['host'] = array();
		$result['dir'] = array();
		$result['file'] = array();
		$result['value'] = array();
		$result['type'] = array();

		if($list === null)
		{
			$list = count_files(PATH, ($_values ? false : null),
				($_values ? true : false), true, false, false);

			if($list === null || count($list) === 0)
			{
				$result = null;
			}
			else
			{
				$len = count($list);

				for($i = 0, $h = 0, $d = 0, $f = 0, $v = 0; $i < $len; ++$i)
				{
					if($list[$i][0] !== '~' && $list[$i][0] !== '+' && $list[$i][0] !== '-')
					{
						continue;
					}

					$type = $list[$i][0];
					$host = substr($list[$i], 1);

					if(!in_array($host, $result['host']))
					{
						$result['host'][$h++] = $host;
					}

					if(!isset($result['type'][$host]))
					{
						$result['type'][$host] = 0;
					}

					switch($type)
					{
						case '~':
							if(!in_array($host, $result['value']))
							{
								$result['value'][$v++] = $host;
							}
							$result['type'][$host] |= TYPE_VALUE;
							break;
						case '+':
							if(!in_array($host, $result['dir']))
							{
								$result['dir'][$d++] = $host;
							}
							$result['type'][$host] |= TYPE_DIR;
							break;
						case '-':
							if(!in_array($host, $result['file']))
							{
								$result['file'][$f++] = $host;
							}
							$result['type'][$host] |= TYPE_FILE;
							break;
					}
				}
			}
		}
		else
		{
			$item = '';
			$len = count($list);

			for($i = 0, $h = 0, $d = 0, $f = 0, $v = 0; $i < $len; ++$i)
			{
				$item = PATH . '/';

				if($list[$i][0] !== '~' && $list[$i][0] !== '+' && $list[$i][0] !== '-')
				{
					if($_values === true)
					{
						$item .= '~';
					}
					else if($_values === false)
					{
						$item .= '{+,-}';
					}
					else if($_values === null)
					{
						$item .= '?';
					}
				}

				$item .= $list[$i];
				$item = glob($item, GLOB_BRACE);
				$sub = count($item);
				
				for($j = 0; $j < $sub; ++$j)
				{
					$base = basename($item[$j]);
					$host = substr($base, 1);
					$type = $base[0];

					if(!in_array($host, $result['host']))
					{
						$result['host'][$h++] = $host;
					}

					if(!isset($result['type'][$host]))
					{
						$result['type'][$host] = 0;
					}

					switch($type)
					{
						case '~':
							if(!in_array($host, $result['value']))
							{
								$result['value'][$v++] = $host;
							}
							$result['type'][$host] |= TYPE_VALUE;
							break;
						case '+':
							if(!in_array($host, $result['dir']))
							{
								$result['dir'][$d++] = $host;
							}
							$result['type'][$host] |= TYPE_DIR;
							break;
						case '-':
							if(!in_array($host, $result['file']))
							{
								$result['file'][$f++] = $host;
							}
							$result['type'][$host] |= TYPE_FILE;
							break;
					}
				}
			}
		}

		if($result !== null)
		{
			$c = 0;

			if(count($result['host']) === 0)
			{
				$result['host'] = null;
				++$c;
			}

			if(count($result['dir']) === 0)
			{
				$result['dir'] = null;
				++$c;
			}

			if(count($result['file']) === 0)
			{
				$result['file'] = null;
				++$c;
			}

			if(count($result['value']) === 0)
			{
				$result['value'] = null;
				++$c;
			}

			if($c === 4)
			{
				$result = null;
			}
		}

		return $result;
	}

//
/*$a=get_list(1,true);
$b=get_list(1,false);
var_dump($a);
printf(PHP_EOL.PHP_EOL);
var_dump($b);
die('   ..........');*/

	//
	function get_hosts($_values = true)
	{
		$list = count_files(PATH, ($_values ? false : null), ($_values ? true : false), true, null);

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
		printf('    -? / --help' . PHP_EOL);
		printf('    -V / --version' . PHP_EOL);
		printf('    -C / --copyright' . PHP_EOL);
		printf('    -h / --hashes' . PHP_EOL);
		printf('    -f / --fonts' . PHP_EOL);
		printf('    -t / --types' . PHP_EOL);
		printf('    -c / --config' . PHP_EOL);
		printf('    -v / --values (*)' . PHP_EOL);
		printf('    -n / --sync (*)' . PHP_EOL);
		printf('    -l / --clean (*)' . PHP_EOL);
		printf('    -p / --purge (*)' . PHP_EOL);
		printf('    -e / --errors' . PHP_EOL);
		printf('    -u / --unlog' . PHP_EOL);
		printf(PHP_EOL);

		exit(0);
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
		if(gettype(PATH_FONTS) !== 'string' || empty(PATH_FONTS))
		{
			fprintf(STDERR, ' >> \'FONTS\' directory is not properly configured' . PHP_EOL);
			exit(1);
		}
		else if(! is_dir(PATH_FONTS))
		{
			fprintf(STDERR, ' >> \'FONTS\' directory doesn\'t exist.' . PHP_EOL);
			exit(2);
		}

		$fonts = get_arguments($_index + 1, true, true);

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

		$available = glob(join_path(PATH_FONTS, '/*.ttf'));
		$len = count($available);
		
		if($len === 0)
		{
			fprintf(STDERR, ' >> No fonts installed in your fonts directory \'%s\'!' . PHP_EOL, basename(PATH_FONTS));
			exit(4);
		}
		else for($i = 0; $i < $len; ++$i)
		{
			$available[$i] = basename($available[$i], '.ttf');
		}

		printf(' >> You have %d fonts installed (in directory \'%s\')! :-)' . PHP_EOL . PHP_EOL, $len, basename(PATH_FONTS));
		
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

		printf(PHP_EOL);
		exit(0);
	}

	function types($_index = -1)
	{
		if(!extension_loaded('gd'))
		{
			fprintf(STDERR, ' >> The GD library/extension is not loaded/available' . PHP_EOL);
			exit(1);
		}

		$selection = get_arguments($_index + 1, true, true);
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
				printf(START.'Non-empty path String (writable directory exists, but no further tests)' . PHP_EOL, 'DIR', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'Non-empty path String, BUT is not an existing directory' . PHP_EOL, 'DIR', 'BAD');
				++$errors;
			}
		}
		else
		{
			fprintf(STDERR, START.'No non-empty path String' . PHP_EOL, 'DIR', 'BAD');
			++$errors;
		}
		
		//
		if(gettype(HIDE) === 'boolean')
		{
			printf(START.'Is a Boolean, and may also be a String' . PHP_EOL, 'HIDE', 'OK');
			++$ok;
		}
		else if(gettype(HIDE) === 'string')
		{
			printf(START.'Is a String, and may also be a Boolean' . PHP_EOL, 'HIDE', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Needs to be a String or Boolean!' . PHP_EOL, 'HIDE', 'BAD');
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
		if(gettype(PATH_LOG) === 'string' && !empty(PATH_LOG))
		{
			if(!file_exists(PATH_LOG) || (is_file(PATH_LOG) && is_writable(PATH_LOG)))
			{
				printf(START.'Is a valid, usable path' . PHP_EOL, 'LOG', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'Valid path string, but seems not to be correct' . PHP_EOL, 'LOG', 'BAD');
				++$errors;
			}
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
		if(gettype(DRAWING) === 'boolean')
		{
			if(DRAWING)
			{
				if(extension_loaded('gd'))
				{
					printf(START.'Enabled drawing option, and the \'GD Library\' is installed.' . PHP_EOL, 'DRAWING', 'OK');
					++$ok;
				}
				else
				{
					fprintf(STDERR, START.'Enabled drawing option, but the \'GD Library\' is not installed (at least in CLI mode)' . PHP_EOL, 'DRAWING', 'WARN');
					++$warnings;
				}
			}
			else
			{
				printf(START.'Disabled drawing. That\'s also OK.' . PHP_EOL, 'DRAWING', 'OK');
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'DRAWING', 'BAD');
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
		
		if(gettype(H) === 'integer')
		{
			$limit = H_LIMIT;
			
			if(gettype($limit) !== 'integer' || $limit > 512 || $limit < 0)
			{
				$limit = null;
			}
			
			if($limit === null)
			{
				fprintf(STDERR, START.'Integer (WARNING: can\'t test against invalid H_LIMIT)' . PHP_EOL, 'H', 'WARN');
				++$warnings;
			}
			else if(H > $limit || H < -$limit)
			{
				fprintf(STDERR, START.'Integer exceeds H_LIMIT (%d)' . PHP_EOL, 'H', 'BAD', $limit);
				++$errors;
			}
			else
			{
				printf(START.'Integer within H_LIMIT (%d)' . PHP_EOL, 'H', 'OK', $limit);
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer (within H_LIMIT)' . PHP_EOL, 'H', 'BAD');
			++$errors;
		}
		
		if(gettype(H_LIMIT) === 'integer' && H_LIMIT >= 0 && H_LIMIT <= 512)
		{
			printf(START.'Integer above -1 and below or equal to 512' . PHP_EOL, 'H_LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Not an Integer above -1 and below or equal to 512' . PHP_EOL, 'H_LIMIT', 'BAD');
			++$errors;
		}
		
		if(gettype(V) === 'integer')
		{
			$limit = V_LIMIT;
			
			if(gettype($limit) !== 'integer' || $limit > 512 || $limit < 0)
			{
				$limit = null;
			}
			
			if($limit === null)
			{
				fprintf(STDERR, START.'Integer (WARNING: can\'t test against invalid V_LIMIT)' . PHP_EOL, 'V', 'WARN');
				++$warnings;
			}
			else if(V > $limit || V < -$limit)
			{
				fprintf(STDERR, START.'Integer exceeds V_IMIT (%d)' . PHP_EOL, 'V', 'BAD', $limit);
				++$errors;
			}
			else
			{
				printf(START.'Integer within V_LIMIT (%d)' . PHP_EOL, 'V', 'OK', V_LIMIT);
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer (within V_LIMIT)'. PHP_EOL, 'V', 'BAD');
			++$errors;
		}
		
		if(gettype(V_LIMIT) === 'integer' && V_LIMIT >= 0 && V_LIMIT <= 512)
		{
			printf(START.'Integer above -1 and below or equal to 512' . PHP_EOL, 'V_LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'Not an Integer above -1 and below or equal to 512' . PHP_EOL, 'V_LIMIT', 'BAD');
			++$errors;
		}

		if(gettype(FONT) === 'string' && !empty(FONT))
		{
			$test = null;
			
			if(is_dir(PATH_FONTS))
			{
				$test = join_path(PATH_FONTS, FONT . '.ttf');
				
				if(is_file($test) && is_readable($test))
				{
					$test = true;
				}
			}

			if($test === null)
			{
				fprintf(STDERR, START.'Valid string (but can\'t test against invalid \'FONTS\' path)' . PHP_EOL, 'FONT', 'WARN');
				++$warnings;
			}
			else if($test)
			{
				printf(START.'Valid string (and also available in \'FONTS\' directory)' . PHP_EOL, 'FONT', 'OK');
				++$ok;
			}
			else
			{
				fprintf(START.'Valid string, BUT is not available in \'FONTS\' directory' . PHP_EOL, 'FONT', 'BAD');
				++$errors;
			}
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'FONT', 'BAD');
			++$errors;
		}

		if(gettype(PATH_FONTS) === 'string' && !empty(PATH_FONTS))
		{
			if(is_dir(PATH_FONTS))
			{
				$test = glob(PATH_FONTS . '/*.ttf');
				$len = count($test);
				
				if($len > 0)
				{
					printf(START.'Valid directory, and contains %d \'.ttf\' font files' . PHP_EOL, 'FONTS', 'OK', $len);
					++$ok;
				}
				else
				{
					fprintf(STDERR, START.'Valid directory, but contains no \'.ttf\' font files' . PHP_EOL, 'FONTS', 'WARN');
					++$warnings;
				}
			}
			else
			{
				fprintf(STDERR, START.'Valid String, BUT is not an existing directory' . PHP_EOL, 'FONTS', 'WARN');
				++$warnings;
			}
		}
		else
		{
			fprintf(STDERR, START.'No valid path String (non-empty)' . PHP_EOL, 'FONTS', 'BAD');
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
	
	function values($_index = -1)
	{
		//
		$hosts = get_arguments($_index + 1, true, true);
		$defined = null;

		if($hosts === null)
		{
			$hosts = get_hosts(true);
			$defined = false;
		}
		else
		{
			$defined = true;
		}

		if($hosts === null)
		{
			fprintf(STDERR, ' >> No hosts found!' . PHP_EOL);
			exit(1);
		}

		$files = array();
		$item = null;
		$origLen = count($hosts);

		for($i = 0, $j = 0; $i < count($hosts); ++$i)
		{
			if($defined)
			{
				$item = join_path(PATH, '~' . $hosts[$i]);
				$item = glob($item, GLOB_BRACE);
				$len = count($item);

				if($len === 0)
				{
					fprintf(STDERR, ' >> No hosts matching glob \'%s\'' . PHP_EOL, $hosts[$i]);
				}
				else
				{
					for($k = 0; $k < $len; ++$k)
					{
						if(!in_array($item[$k], $files))
						{
							$files[$j++] = $item[$k];
						}
					}
				}
			}
			else if(is_file($item = join_path(PATH, '~' . $hosts[$i])) && is_readable($item))
			{
				if(!in_array($item, $files))
				{
					$files[$j++] = $item;
				}
			}
			else
			{
				fprintf(STDERR, ' >> File for host \'%s\' couldn\'t be read' . PHP_EOL, $hosts[$i]);
			}
		}
		
		$len = count($files);

		if($len === 0)
		{
			fprintf(STDERR, ' >> No hosts left' . ($origLen > 0 ? ' (from originally %d defined ones)' : '') . PHP_EOL, $origLen);
			exit(2);
		}
		else
		{
			$hosts = array();
			
			for($i = 0; $i < $len; ++$i)
			{
				$hosts[$i] = substr(basename($files[$i]), 1);
			}
		}

		$value = -1;
		$maxLen = 0;
		$currLen = 0;

		for($i = 0; $i < count($hosts); ++$i)
		{
			if(($currLen = strlen($hosts[$i])) > $maxLen)
			{
				$maxLen = $currLen;
			}
		}

		$maxLen += 2;
		$format = '%' . $maxLen . 's:  %-8d %1s' . PHP_EOL;
		$cache = null;
		$real = null;
		$host = null;
		$item = null;
		$info = '';
		$err = array();

		for($i = 0, $e = 0; $i < $len; ++$i)
		{
			$host = substr(basename($files[$i]), 1);

			if(($value = (int)file_get_contents($files[$i])) === false)
			{
				$err[$e++] = $host;
			}
			else
			{
				if(is_dir($item = join_path(PATH, '+' . $host)) && is_readable($item))
				{
					if(($item = count_files($item, false, false, false, false, false)) === null)
					{
						$real = -1;
					}
					else
					{
						$real = $item;
					}
				}
				else
				{
					$real = -1;
				}

				if(is_file($item = join_path(PATH, '-' . $host)) && is_readable($item))
				{
					if(!($cache = (int)file_get_contents($item)))
					{
						$cache = -1;
					}
				}
				else
				{
					$cache = -1;
				}

				if($real === -1 && $cache === -1)
				{
					$info = ' ';
				}
				else if($real > -1 && $cache !== $real)
				{
					if(!file_exists($item) || is_writable($item))
					{
						if(file_put_contents($item, (string)$real))
						{
							$info = '+';
						}
						else
						{
							$info = '-';
						}
					}
					else
					{
						$info = '!';
					}
				}
				else if($real === -1)
				{
					$info = '/';
				}
				else
				{
					$info = (string)$real;
				}

				printf($format, $host, $value, $info);
			}
		}

		$errLen = count($err);
		
		if($errLen > 0)
		{
			fprintf(STDERR, PHP_EOL . ' >> Unable to read value files for the following hosts:' . PHP_EOL);
			
			for($i = 0; $i < $errLen; ++$i)
			{
				fprintf(STDERR, '    %s' . PHP_EOL, $err[$i]);
			}
			
			printf(PHP_EOL);
			exit(3);
		}

		//
		exit(0);
	}

	function sync($_index = -1)
	{
die('TODO (sync() w/ glob(); look above)');
		//
		$hosts = get_arguments($_index + 1, true, true);
		$defined = null;
		
		if($hosts === null)
		{
			$hosts = get_hosts(false);
			$defined = false;
		}
		else
		{
			$defined = true;
		}

		if($hosts === null)
		{
			fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
			exit(1);
		}

		$host = array();
		$dirs = array();
		$files = array();
		$types = array();
		$item = null;
		$len = -1;
		$good = 0;
		$ignored = 0;
		
		if($defined)
		{
			$len = count($hosts);
			
			for($i = 0, $f = 0, $d = 0, $h = 0; $i < $len; ++$i)
			{
				$item = join_path(PATH, '-' . $hosts[$i]);
				$item = glob($item, GLOB_BRACE);
				$len = count($item);
				
				for($j = 0; $j < $len; ++$j)
				{
					if(!(is_readable($item[$j]) && is_writable($item[$j])))
					{
						++$ignored;
					}
					else
					{
						$files[$f++] = $item[$j];
						$item = substr(basename($item[$j]), 1);
						
						if(!in_array($item, $host))
						{
							$host[$h++] = $item;
						}
						
						if(! isset($types[$item]))
						{
							$types[$item] = 0;
						}
						
						$types[$item] |= 1;
						++$good;
					}
				}
				
				$item = join_path(PATH, '+' . $hosts[$i]);
				$item = glob($item, GLOB_BRACE | GLOB_ONLYDIR);
				$len = count($item);
				
				for($j = 0; $j < $len; ++$j)
				{
					if(!(is_readable($item[$j]) && is_writable($item[$j])))
					{
						++$ignored;
					}
					else
					{
						$dirs[$d++] = $item[$j];
						$item = substr(basename($item[$j]), 1);
						
						if(!in_array($item, $host))
						{
							$host[$h++] = $item;
						}

						if(! isset($types[$item]))
						{
							$types[$item] = 0;
						}
						
						$types[$item] |= 2;
						++$good;
					}
				}
			}
		}
		else
		{
			$len = count($hosts);

			for($i = 0, $f = 0, $d = 0, $h = 0; $i < $len; ++$i)
			{
				if(! isset($types[$hosts[$i]]))
				{
					$types[$hosts[$i]] = 0;
				}

				$item = join_path(PATH, '-' . $hosts[$i]);
				
				if(is_file($item))
				{
					if(! (is_readable($item) && is_writable($item)))
					{
						++$ignored;
					}
					else
					{
						$files[$f++] = $item;
						
						if(!in_array($hosts[$i], $host))
						{
							$host[$h++] = $hosts[$i];
						}
						
						$types[$hosts[$i]] |= 1;
						++$good;
					}
				}
				
				$item = join_path(PATH, '+' . $hosts[$i]);
				
				if(is_dir($item))
				{
					if(! (is_readable($item) && is_writable($item)))
					{
						++$ignored;
					}
					else
					{
						$dirs[$d++] = $item;
						
						if(!in_array($hosts[$i], $host))
						{
							$host[$h++] = $hosts[$i];
						}
						
						$types[$hosts[$i]] |= 2;
						++$good;
					}
				}
			}
		}
		
		if($good === 0)
		{
			fprintf(STDERR, ' >> No items found' . PHP_EOL);
			exit(2);
		}
		else
		{
			printf(' >> %d items can be synced' . PHP_EOL, $good);
		}
		
		if($ignored > 0)
		{
			fprintf(STDERR, ' >> %d items must be ignored (insufficient permissions)' . PHP_EOL, $ignored);
		}
		
		//
		//
		//purge() und clean() gibt's noch (unten).
		//purge() wird brute rmdir sein.. w/ prompt()!!!!!
		//clean() saeubert (a) outdated '+' files.. und dekrementiert den zaehl-caeche entsprechend der anzahl geloeschter elemente..
		//etc..
		//
		//
		//BEST waere, ich setzt MIN. obige routine in eine abstrakte funktion, die auf dem gemeinsamen nenner basiert,
		//dass wir eben + und - dateien erhalten, die existieren.. sowie beteiligte hosts und noch die codes, ob nichts, -, +, oder beides..
		//kann man sicher gut gebrauchen, sowohl in diesem sync() wie auch purge() und clean().
		//
		//ueberpruefe das.. purge? muss jedenfalls hosts aus glob() codieren. und muss wissen, ob '+' fuer die hosts existieren, um sie zu
		//loeschen.. und wenn '-' existieren, muessen die ebenso geloescht werden.. das geht mit beiden files[] sowie dirs[] gut, auf basis
		//entweder aller hosts oder der glob() ausgewaehlten.. also gut, das ist schonmal gemeinsam.
		//
		//clean? braucht jedenfalls ebenso hosts aus glob(), oder eben alle... braucht auch evtl. '-'-cache, um dort zu dekrementieren nach
		//der loesch-anzahl der '+'-files (!! also + nehmen, um count_files(), ...). oder will ich direkt durch count_files() in sync gehen?
		//
		//hm. clean() ist evtl. einfacher.. aber wir brauchen ebenso die hosts [bei denen '+'-files existieren], auch @ glob()!
		//aber es reichen die hosts, um nur noch is_dir() zu testen, um dann count_files() alle dortigen files einzulesen, die dann in ihrem
		//timestamp-inhalt geprueft werden. bestenfalls verwende ich garnicht mal die '-'-caches, sondern, da ich sowieso alle dateien ein-
		//lesen muss, kann ich gleich die result-anzahl(-diff) als "neues" '-'-cache setzen!!! ...
		//
		//purge() wiederum braucht auch hosts, etc., auch @ glob(). aber nichtmal die datei-liste, das waere zuviel overhead im prinzip.
		//es genuegt, das verzeichnis $_recursive=true zu loeschen @ remote()!!! danach, FALLS GANZ GELOSCHT (ERFOLGREICH), auch noch die
		//evtl. is_file('-'..) loeschen. falls NICHT ERFOLGREICH geloescht, muss wohl am besten count_files() w/ int-result, um die neue
		//'-' aus den rest-files zu init()...
		//
		//also, purge() braucht nur beteiligte hosts[]. den rest macht es selbst mit is_dir und is_file, etc.
		//sowie clean() braucht .. lediglich alle '+'-verz., evtl. selbst durch blosze hosts() gefunden. schreibt - ohne lesen zu muessen.
		//
		//ich denke mir fast.. am besten eine abstrakte funktion allein dazu, dass..
		//(a) je nachdem alle hosts als result.. erfahrungsgemaesz 'get_hosts()' somit ersetzt, aber eher dafuer noch der $values-param,
		//	um zu unterscheiden zw. den '~'-value-files, die manchmal noetig sind, sowie den '-+'-cache-/..-files, manchmal noetig.
		//	.. aber immer mit glob() support aus get_arguments()!! hier einfach je nach '$values' im glob noch '~' dazu am anfang,
		//	oder sonst '?' als glob-char, um am ende evtl. noch heraus zu sortieren..!?!
		//(b) EVTL. will ich die $code[s]-sache dazu? oder eher nur hier im sync?! JA...

var_dump($host);
var_dump($dirs);
var_dump($files);
var_dump($types);
die(' ....//');

		//PS: '-'/'~'/'+'-filter einfach angeben durch entweder "?" fuer alle, "~" fuer values, oder GLOB_BRACE @ "{+,-}"!!!! :D~


		//@sync() ONLY:
		//
		// wenn im array[a], so hash[key] |= 1; in[b] hash[key] |= 2; am ende nur "if(&1) => in[a]" und "if(&2) => in[b]",
		// jeweils mit "$code &= 1|2". am ende if(($code & 1) && ($code & 2)) => IN BOTH ARRAYS! sonst nur (&1 in [a]) oder (&2) in [b], as "|=1/2"! ^_^
		// 
		//
		// wenn ein '+', aber kein '-', muss '-' aus count_files() @ '+' erzeugt werden @ init... //zu protokoll @ init[]
		// wenn ein '-', aber kein '+', wird einfach der '-'-file-cache unlink'ed.. //zu protokoll @ delete[]/..
		// wenn beides, vergleich zw. count_files('+') sowie cache-'-'.
		// if(in sync), bitte sync[]-protokoll-gebung; sonst update[]-protokoll, mit setzen der eben gezeahlten datei-menge unter '+' in die '-' hinein.
		// FALLS ABER die '+'-zaehlung der ip-timestamp-files (0) ist.. so (a) entweder '-' und '+' ganz loeschen (JA!!!), sonst '-' mit (0)-init.. a: delete[];
		//
		//again:
		//
		//(a) wenn ein '+', aber kein '-', muss '-' aus count_files() der '+' neu entstehen @ init. w/ ++$inits;
		//(b) wenn ein '-', aber kein '+', loeschen des '-'-wertes.. w/ ++$unlinks;
		//(c) wenn beides, vergleich zw. den count's..
		//	wenn in sync, ++$ok; wenn nicht, ++$sync;


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

			if($new === null)
			{
				fprintf(STDERR, ' >> Failed to scan directory for host \'%s\'' . PHP_EOL, $hosts[$i]);
				++$errors;
			}
			else if($orig === $new)
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

	function clean($_index = -1, $_host = null)
	{
die('TODO (clean() w/ glob(); look above..)');
		//
		$hosts = get_arguments($_index + 1, true, true);
		
		if($hosts === null)
		{
			$hosts = get_hosts(false);
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

			if(! is_dir(join_path(PATH, '+' . $hosts[$i])))
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
			if(($len = count($files = count_files(join_path(PATH, '+' . $hosts[$i]), false, false, true))) === 0)
			{
				printf(' >> No cache files for host \'%s\' collected' . PHP_EOL, $hosts[$i]);
			}
			else if($len === null)
			{
				fprintf(STDERR, ' >> Failed to scan directory for host \'%s\'' . PHP_EOL, $hosts[$i]);
				++$err;
			}
			else for($k = 0; $k < $len; ++$k)
			{
				$item = join_path(PATH, '+' . $hosts[$i], $files[$k]);

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
			$item = count_files(join_path(PATH, '+' . ($adapted[$i] = $adapted[$i][0])), false, false, false, false);

			if($item === null)
			{
				fprintf(STDERR, ' >> Failed to scan directory for host \'%s\'' . PHP_EOL, $adapted[$i]);
			}
			else if(is_writable(join_path(PATH, '-' . $adapted[$i])))
			{
				if(file_put_contents(join_path(PATH, '-' . $adapted[$i]), (string)$item) === false)
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

	function purge($_index = -1, $_host = null)
	{
die('TODO (purge() w/ glob(); look above..)');
		//
		$hosts = get_arguments($_index + 1, true, true);

		if($hosts === null)
		{
			$hosts = get_hosts(false);
		}

		if($hosts === null)
		{
			printf('No hosts available to purge their cache files.' . PHP_EOL);
			exit(0);
		}
		else for($i = 0; $i < count($hosts); ++$i)
		{
			if(!is_dir(join_path(PATH, '+' . $hosts[$i])) && !is_file(join_path(PATH, '-' . $hosts[$i])))
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
			$sub = join_path(PATH, '-' . $hosts[$i]);
			
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
			
			$sub = join_path(PATH, '+' . $hosts[$i]);
			
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

	function unlog($_index = -1)
	{
		error_reporting(0);

		if(! file_exists(PATH_LOG))
		{
			fprintf(STDERR, ' >> There is no \'%s\' which could be deleted. .. that\'s good for you. :)~' . PHP_EOL, basename(PATH_LOG));
			exit(1);
		}
		else if(!is_file(PATH_LOG))
		{
			fprintf(STDERR, ' >> The \'%s\' is not a regular file. Please replace/remove it asap!' . PHP_EOL, PATH_LOG);
		}

		$input = prompt('Do you really want to delete the file \'' . basename(PATH_LOG) . '\' [yes/no]? ');

		if(!$input)
		{
			fprintf(STDERR, ' >> Log file deletion aborted (by request).' . PHP_EOL);
			exit(2);
		}
		else if(remove(PATH_LOG, false, true) === false)
		{
			fprintf(STDERR, ' >> The \'%s\' couldn\'t be deleted!!' . PHP_EOL, basename(PATH_LOG));

			if(! is_file(PATH_LOG))
			{
				fprintf(STDERR, ' >> I think it\'s not a regular file, could this be the reason why?' . PHP_EOL);
			}

			exit(2);
		}
		else
		{
			printf(' >> The \'%s\' is no longer.. :-)' . PHP_EOL, basename(PATH_LOG));
		}

		exit(0);
	}

	function errors($_index = -1)
	{
		if(! file_exists(PATH_LOG))
		{
			printf(' >> No errors logged! :-D' . PHP_EOL);
			exit(0);
		}
		else if(!is_file(PATH_LOG))
		{
			fprintf(STDERR, ' >> \'%s\' is not a file! Please delete asap!!' . PHP_EOL, basename(PATH_LOG));
			exit(1);
		}
		else if(!is_readable(PATH_LOG))
		{
			fprintf(STDERR, ' >> Log file \'%s\' is not readable! Please correct this asap!!' . PHP_EOL, basename(PATH_LOG));
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

		$result = count_lines(PATH_LOG);

		if($result < 0)
		{
			$result = 0;
		}

		printf(' >> There are %d error log lines in \'%s\'..' . PHP_EOL, $result, basename(PATH_LOG));
		exit(0);
	}

	//
	function help($_index = -1)
	{
		printf(HELP . PHP_EOL);
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
if(!TEST)
{
	//
	$host = get_host(true);
	define('HOST', $host);
	define('COOKIE', hash(HASH, $host));
	unset($host);

	//
	define('PATH_FILE', join_path(PATH, '~' . secure_path(HOST, false)));
	define('PATH_DIR', join_path(PATH, '+' . secure_path(HOST, false)));
	define('PATH_COUNT', join_path(PATH, '-' . secure_path(HOST, false)));
	define('PATH_IP', join_path(PATH_DIR, secure_path((HASH_IP ? hash(HASH, $_SERVER['REMOTE_ADDR']) : secure_host($_SERVER['REMOTE_ADDR'], false)), false)));

	//
	function check_auto()
	{
		if(AUTO === true && !OVERRIDDEN)
		{
			return;
		}
		else if(!is_file(PATH_FILE))
		{
			if(OVERRIDDEN)
			{
				error(NONE);
			}
			else if(AUTO === false)
			{
				error(NONE);
			}
			else if(gettype(AUTO) === 'integer')
			{
				$count = 0;
				$handle = opendir(PATH);

				if($handle === false)
				{
					log_error('Can\'t opendir()', 'check_path', PATH, false);
					error(NONE);
				}

				while($sub = readdir($handle))
				{
					if($sub[0] === '~')
					{
						++$count;
					}
				}

				closedir($handle);

				if($count >= AUTO)
				{
					error(NONE);
				}
			}
			else
			{
				log_error('Invalid \'AUTO\' constant', 'check_path', '', false);
				error('Invalid \'AUTO\' constant');
			}
		}
		else if(file_exists(PATH_FILE) && !is_writable(PATH_FILE))
		{
			log_error('File is not writable', 'check_path', PATH_FILE);
			error('File is not writable');
		}
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

	function test_file()
	{
		if(file_exists(PATH_IP))
		{
			if(!is_file(PATH_IP))
			{
				log_error('Is not a regular file', 'test_file', PATH_IP);
				error('Is not a regular file');
			}
			else if(!is_readable(PATH_IP))
			{
				log_error('File can\'t be read', 'test_file', PATH_IP);
				error('File can\'t be read');
			}
			else if(timestamp(read_timestamp()) < THRESHOLD)
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

	function clean_files()
	{
		if(CLEAN === null)
		{
			log_error('Called function, but CLEAN === null', 'clean_files', '', false);
			return -1;
		}
		else if(!is_dir(PATH_DIR))
		{
			return init_count(false);
		}

		$handle = opendir(PATH_DIR);
		
		if(!$handle)
		{
			log_error('Can\'t opendir()', 'clean_files', PATH_DIR);
			error('Can\'t opendir()');
		}
		
		$result = 0;
		
		while($sub = readdir($handle))
		{
			if($sub[0] === '.' || $sub === '..')
			{
				continue;
			}
			else
			{
				$sub = join_path(PATH_DIR, $sub);
			}
			
			if(!is_file($sub))
			{
				continue;
			}
			else if(timestamp((int)file_get_contents($sub)) <= THRESHOLD)
			{
				++$result;
			}
			else if(! remove($sub, false, false))
			{
				log_error('Unable to remove() outdated file', 'clean_files', $sub, false);
				++$result;
			}
		}
		
		closedir($handle);
		return write_count($result, false);
	}

	function init_count($_die = false)
	{
		$result = 0;

		if(is_dir(PATH_DIR))
		{
			$handle = opendir(PATH_DIR);

			if($handle === false)
			{
				log_error('Can\'t opendir()', 'init_count', PATH_DIR, $_die);
				return result;
			}

			while($sub = readdir($handle))
			{
				if($sub[0] === '.' || $sub === '..')
				{
					continue;
				}
				else if(is_file(join_path(PATH_DIR, $sub)))
				{
					++$result;
				}
			}

			closedir($handle);
		}
		else
		{
			$result = 0;
		}

		$written = file_put_contents(PATH_COUNT, (string)$result);

		if($written === false)
		{
			log_error('Couldn\'t initialize count', 'init_count', PATH_COUNT, $_die);

			if($_die)
			{
				error('Couldn\'t initialize count');
			}
			
			return false;
		}

		return $result;
	}

	function read_count()
	{
		if(!file_exists(PATH_COUNT))
		{
			return init_count();
		}
		else if(!is_file(PATH_COUNT) || !is_writable(PATH_COUNT))
		{
			log_error('Count file is not a file, or it\'s not writable', 'read_count', PATH_COUNT);
			error('Count file is not a file, or it\'s not writable');
		}

		$result = file_get_contents(PATH_COUNT);

		if($result === false)
		{
			log_error('Couldn\'t read count value', 'read_count', PATH_COUNT);
			error('Couldn\'t read count value');
		}

		return (int)$result;
	}

	function write_count($_value, $_get = true)
	{
		$result = null;

		if(file_exists(PATH_COUNT))
		{
			if(!is_file(PATH_COUNT))
			{
				log_error('Count file is not a regular file', 'write_count', PATH_COUNT);
				error('Count file is not a regular file');
			}
			else if($_get)
			{
				$result = read_count();
			}
		}
		else
		{
			$result = init_count();
		}

		$written = file_put_contents(PATH_COUNT, (string)$_value);

		if($written === false)
		{
			log_error('Unable to write count', 'write_count', PATH_COUNT);
			error('Unable to write count');
		}

		return $result;
	}

	function increase_count($_by = 1)
	{
		$result = (read_count() + $_by);
		write_count($result, false);
		return $result;
	}

	function decrease_count($_by = 1)
	{
		$result = (read_count() - $_by);

		if($result < 0)
		{
			$result = 0;
		}

		write_count($result, false);
		return $result;
	}

	function read_timestamp()
	{
		if(file_exists(PATH_IP))
		{
			if(!is_file(PATH_IP))
			{
				log_error('Is not a file', 'read_timestamp', PATH_IP);
				error('Is not a file');
			}
			else if(!is_readable(PATH_IP))
			{
				log_error('File not readable', 'read_timestamp', PATH_IP);
				error('File not readable');
			}

			$result = file_get_contents(PATH_IP);

			if($result === false)
			{
				log_error('Unable to read timestamp', 'read_timestamp', PATH_IP);
				error('Unable to read timestamp');
			}

			return (int)$result;
		}

		return 0;
	}

	function write_timestamp($_clean = (CLEAN !== null))
	{
		$existed = file_exists(PATH_IP);

		if($existed)
		{
			if(!is_file(PATH_IP))
			{
				log_error('It\'s no regular file', 'write_timestamp', PATH_IP);
				error('It\'s no regular file');
			}
			else if(!is_writable(PATH_IP))
			{
				log_error('Not a writable file', 'write_timestamp', PATH_IP);
				error('Not a writable file');
			}
		}
		else if(read_count() > LIMIT)
		{
			if($_clean)
			{
				if(clean_files() > LIMIT)
				{
					log_error('LIMIT exceeded, even after clean_files()', 'write_timestamp', PATH_IP);
					error('LIMIT exceeded, even after clean_files()');
					return null;
				}
			}
			else
			{
				log_error('LIMIT exceeded (and no clean_files() called)', 'write_timestamp', PATH_IP);
				error('LIMIT exceeded; w/o clean_files() call');
				return null;
			}
		}

		$result = file_put_contents(PATH_IP, (string)timestamp());

		if($result === false)
		{
			log_error('Unable to write timestamp', 'write_timestamp', PATH_IP);
			error('Unable to write timestamp');
		}
		else if(!$existed)
		{
			increase_count();
		}

		return $result;
	}

	function read_value()
	{
		if(file_exists(PATH_FILE))
		{
			if(!is_file(PATH_FILE))
			{
				log_error('It\'s not a regular file', 'read_value', PATH_FILE);
				error('It\'s not a regular file');
			}
			else if(!is_readable(PATH_FILE))
			{
				log_error('File is not readable', 'read_value', PATH_FILE);
				error('File is not readable');
			}

			$result = file_get_contents(PATH_FILE);

			if($result === false)
			{
				log_error('Unable to read value', 'read_value', PATH_FILE);
				error('Unable to read value');
			}

			return (int)$result;
		}
		else if((file_put_contents(PATH_FILE, '0')) === false)
		{
			touch(PATH_FILE);
		}

		return 0;
	}

	function write_value($_value, $_die = false)
	{
		if(gettype($_value) !== 'integer' || $_value < 0)
		{
			log_error('Value was no integer, or it was below zero', 'write_value', '', $_die);
			
			if($_die)
			{
				error('Value was no integer, or it was below zero');
			}
			
			return 0;
		}

		if(file_exists(PATH_FILE))
		{
			if(!is_file(PATH_FILE))
			{
				log_error('Not a regular file', 'write_value', PATH_FILE, $_die);

				if($_die)
				{
					error('Not a regular file');
				}
			}
			else if(!is_writable(PATH_FILE))
			{
				log_error('File is not writable', 'write_value', PATH_FILE, $_die);

				if($_die)
				{
					error('File is not writable');
				}
			}
		}

		$result = file_put_contents(PATH_FILE, (string)$_value);

		if($result === false)
		{
			log_error('Unable to write value', 'write_value', PATH_FILE, $_die);

			if($_die)
			{
				error('Unable to write value');
			}
		}

		return $result;
	}
	
	//
	if(AUTO === null)
	{
		error(NONE);
	}

	check_auto();
}

//
function draw($_text, $_zero = ZERO)
{
	//
	function get_drawing_type($_die = true)
	{
		$result = get_param('type', false);

		if(gettype($result) !== 'string')
		{
			$result = TYPE;
		}

		$types = imagetypes();

		switch($result = strtolower($result))
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

	//
	if($_zero)
	{
		//
		function draw_zero($_type)
		{
			//
			if(defined('SENT'))
			{
				draw_error('Header already sent (unexpected here)');
				return null;
			}

			//
			$image = imagecreatetruecolor(1, 1);
			imagesavealpha($image, true);
			imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 255, 255, 127));
			
			//
			$sent = null;
			
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
		return draw_zero(get_drawing_type());
	}

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
	function get_font($_name)
	{
		if(gettype($_name) !== 'string')
		{
			return null;
		}
		else if(substr($_name, -4) !== '.ttf')
		{
			$_name .= '.ttf';
		}
		
		$result = join_path(PATH_FONTS, $_name);

		if(is_file($result) && is_readable($result))
		{
			return $result;
		}
		
		return null;
	}
	
	function get_drawing_options($_die = true)
	{
		//
		$result = array();

		//
		if(($result['type'] = get_drawing_type($_die)) === null)
		{
			return null;
		}
		else
		{
			$result['size'] = get_param('size', true, false);
			$result['font'] = get_param('font', false);
			$result['fg'] = get_param('fg', false);
			$result['bg'] = get_param('bg', false);
			$result['x'] = get_param('x', true, false);
			$result['y'] = get_param('y', true, false);
			$result['h'] = get_param('h', true, false);
			$result['v'] = get_param('v', true, false);
			$result['aa'] = get_param('aa', null);
		}

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

		if(! is_numeric($result['h']))
		{
			$result['h'] = H;
		}
		else if($result['h'] > H_LIMIT || $result['h'] < -H_LIMIT)
		{
			draw_error('\'?h\' exceeds limit (' . H_LIMIT . ')', $_die);
			return null;
		}

		if(! is_numeric($result['v']))
		{
			$result['v'] = V;
		}
		else if($result['v'] > V_LIMIT || $result['v'] < -V_LIMIT)
		{
			draw_error('\'?v\' exceeds limit (' . V_LIMIT . ')', $_die);
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

	function pt2px($_pt)
	{
		return ($_pt * 0.75);
	}

	function px2pt($_px)
	{
		return ($_px / 0.75);
	}
	
	function draw_text($_text, $_font, $_size, $_fg, $_bg, $_h, $_v, $_x, $_y, $_aa, $_type)
	{
		//
		if(defined('SENT'))
		{
			draw_error('Header already sent (unexpected here)');
			return null;
		}

		//
		$px = $_size;
		$pt = px2pt($px);

		//
		$measure = imagettfbbox($pt, 0, $_font, $_text);
		$textWidth = ($measure[2] - $measure[0]);
		$textHeight = ($measure[1] - $measure[7]);

		//
		$width = px2pt($textWidth + ($_h * 2));
		$height = px2pt($textHeight + ($_v * 2));

		//
		if($width < 1 || $height < 1)
		{
			draw_error('Resulting width/height below 1');
			return null;
		}

		//
		$image = imagecreatetruecolor($width, $height);
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
		$x = pt2px(($width - $textWidth + $_h) / 2) + $_x;
		$y = (($height + $textHeight) / 2) + $_y;

		//
		imagettftext($image, $pt, 0, $x, $y, $_fg, $_font, $_text);

		//
		$sent = null;
		
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
	return draw_text($_text, $options['font'], $options['size'], $options['fg'], $options['bg'], $options['h'], $options['v'], $options['x'], $options['y'], $options['aa'], $options['type']);
}

//
$value = (TEST ? rand() : read_value());

//
if(! (READONLY || TEST))
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
if(gettype(HIDE) === 'string' && !TEST)
{
	$value = HIDE;
}
else if(HIDE === true && !TEST)
{
	$value = (string)rand();
}
else
{
	$value = (string)$value;
}

if(strlen($value) > 64)
{
	log_error('$value length exceeds limit (' . strlen($value) . ' chars)', '', '', false);
	$value = 'e';
}

//
if(DRAW || ZERO)
{
	draw($value, ZERO);
}
else
{
	sendHeader(CONTENT);
	header('Content-Length: ' . strlen($value));
	echo $value;
}

//
define('FIN', true);

//
if(SERVER && !(READONLY || TEST))
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
