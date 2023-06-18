<?php

//
namespace kekse\counter;

//
define('KEKSE_COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');
define('COUNTER_HELP', 'https://github.com/kekse1/count.php/');
define('COUNTER_VERSION', '3.2.5');

//
define('KEKSE_LIMIT', 224); //reasonable maximum length for *some* strings.. e.g. path components (theoretically up to 255 chars @ unices..);
define('KEKSE_STRICT', true); //should stay (true)! don't change unless you know what you're doing..

//
const DEFAULTS = array(
	'path' => 'count/',
	'log' => 'count.log',
	'threshold' => 7200,
	'auto' => 32,
	'hide' => false,
	'client' => true,
	'server' => true,
	'drawing' => true,
	'override' => false,
	'content' => 'text/plain;charset=UTF-8',
	'radix' => 10,
	'clean' => true,
	'limit' => 32768,
	'fonts' => 'fonts/',
	'font' => 'IntelOneMono',
	'prefer' => true,
	'px' => 24,
	'fg' => '0,0,0,1',
	'bg' => '255,255,255,0',
	'x' => 0,
	'y' => 0,
	'h' => 0,
	'v' => 0,
	'aa' => true,
	'type' => 'png',
	'privacy' => false,
	'hash' => 'sha3-256',
	'error' => '-',
	'none' => '/',
	'raw' => false
);

$CONFIG = array();
$HASHES = array();

$STATE = array(
	'sent' => null,
	'fin' => null,
	'done' => null,
	
	'test' => null,
	'ro' => null,
	'zero' => null,
	'draw' => null,
	
	'path' => null,
	'log' => null,
	'fonts' => null,

	'value' => null,
	'dir' => null,
	'file' => null,
	'ip' => null,

	'address' => null,	
	'remote' => null,
	
	'overridden' => null,
	'host' => null,

	'cookie' => null
);

//
const CONFIG_STATIC = array(
	'path',
	'auto',
	'override',
	'hash',
	'raw'
);

const CONFIG_VECTOR = array(
	'path' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'log' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'threshold' => array('types' => [ 'integer', 'NULL' ], 'min' => 0),
	'auto' => array('types' => [ 'boolean', 'integer', 'NULL' ], 'min' => 0),
	'hide' => array('types' => [ 'boolean', 'string' ]),
	'client' => array('types' => [ 'boolean' ]),
	'server' => array('types' => [ 'boolean' ]),
	'drawing' => array('types' => [ 'boolean' ]),
	'override' => array('types' => [ 'boolean', 'string' ], 'min' => 1),
	'content' => array('types' => [ 'string' ], 'min' => 1),
	'radix' => array('types' => [ 'integer' ], 'min' => 2, 'max' => 36),
	'clean' => array('types' => [ 'boolean', 'NULL', 'integer' ], 'min' => 0),
	'limit' => array('types' => [ 'integer' ], 'min' => 0),
	'fonts' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'font' => array('types' => [ 'string' ], 'min' => 1),
	'prefer' => array('types' => [ 'boolean' ]),
	'px' => array('types' => [ 'integer'], 'min' => 4, 'max' => 512),
	'fg' => array('types' => [ 'string' ], 'min' => 1, 'without' => true),
	'bg' => array('types' => [ 'string' ], 'min' => 1, 'without' => true),
	'x' => array('types' => [ 'integer' ], 'min' => -512, 'max' => 512),
	'y' => array('types' => [ 'integer' ], 'min' => -512, 'max' => 512),
	'h' => array('types' => [ 'integer' ], 'without' => true),
	'v' => array('types' => [ 'integer' ], 'without' => true),
	'aa' => array('types' => [ 'boolean' ]),
	'type' => array('types' => [ 'string' ], 'min' => 1, 'without' => true),
	'privacy' => array('types' => [ 'boolean' ]),
	'hash' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'error' => array('types' => [ 'string', 'NULL' ]),
	'none' => array('types' => [ 'string' ]),
	'raw' => array('types' => [ 'boolean' ])
);

//
function get_state($_key, $_die = true)
{
	//
	global $STATE;
	
	//
	if(!is_string($_key) || empty($_key))
	{
		if($_die)
		{
			error('Invalid $_key (not a non-empty String)');
		}
		
		return null;
	}
	else
	{
		$_key = strtolower($_key);
	}

	if(array_key_exists($_key, $STATE))
	{
		return $STATE[$_key];
	}
	else if($_die)
	{
		error('Unknown state \'' . $_key . '\'');
	}
		
	return null;
}

function set_state($_key, $_value, $_die = true)
{
	//
	global $STATE;
	
	//
	if(!is_string($_key) || empty($_key))
	{
		if($_die)
		{
			error('Invalid $_key (not a non-empty String)');
		}
		
		return null;
	}
	else if(!array_key_exists($_key = strtolower($_key), $STATE))
	{
		if($_die)
		{
			error('Unknown state \'' . $_key . '\'');
		}

		return null;
	}
	
	$result = $STATE[$_key];
	$STATE[$_key] = $_value;
	return $result;
}

//
if(!defined('KEKSE_CLI'))
{
	define('KEKSE_CLI', (php_sapi_name() === 'cli'));
}

//
if(! (empty($argc) || empty($argv)))
{
	define('KEKSE_ARGC', $argc);
	define('KEKSE_ARGV', $argv);
}
else if(! (empty($_SERVER['argc']) || empty($_SERVER['argv'])))
{
	define('KEKSE_ARGC', $_SERVER['argc']);
	define('KEKSE_ARGV', $_SERVER['argv']);
}
else if(KEKSE_CLI)
{
	fprintf(STDERR, ' >> WARNING: CLI mode active, but missing \'$argc\' and/or \'$argv\'..! :-/' . PHP_EOL);
	exit(127);
}

//
namespace kekse;

//
function limit($_string, $_length = KEKSE_LIMIT)
{
	return substr($_string, 0, $_length);
}

//
function ends_with($_haystack, $_needle, $_case_sensitive = true)
{
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}
	else if(!$_case_sensitive)
	{
		$_haystack = strtolower($_haystack);
		$_needle = strtolower($_needle);
	}

	return (substr($_haystack, -strlen($_needle)) === $_needle);
}

function starts_with($_haystack, $_needle, $_case_sensitive = true)
{
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}
	else if(!$_case_sensitive)
	{
		$_haystack = strtolower($_haystack);
		$_needle = strtolower($_needle);
	}
	
	return (substr($_haystack, 0, strlen($_needle)) === $_needle);
}

function normalize($_path)
{
	if(!is_string($_path))
	{
		return null;
	}
	
	$len = strlen($_path);
	
	if($len === 0 || $len > KEKSE_LIMIT)
	{
		return '.';
	}
	
	$abs = ($_path[0] === '/');
	$dir = ($_path[$len - 1] === '/');
	$split = explode('/', $_path);
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
		return null;
	}

	$len = count($_args);
	$result = '';
	
	for($i = 0; $i < $len; ++$i)
	{
		if(!is_string($_args[$i]))
		{
			throw new Error('Invalid argument[' . $i . '] (no non-empty String)');
		}
		else if(!empty($_args[$i]))
		{
			$result .= $_args[$i] . '/';
		}
	}
	
	if(strlen($result) > 0)
	{
		$result = substr($result, 0, -1);
	}
	
	return normalize($result);
}

function timestamp($_diff = null)
{
	if(!is_int($_diff))
	{
		return time();
	}
	
	return (time() - $_diff);
}

function remove_white_spaces($_string)
{
	$result = '';
	$len = strlen($_string);
	
	for($i = 0; $i < $len; ++$i)
	{
		if(ord($_string[$i]) > 32)
		{
			$result .= $_string[$i];
		}
	}
	
	return $result;
}

function secure($_string)
{
	if(!is_string($_string))
	{
		return null;
	}
	
	$len = strlen($_string);
	
	if($len > KEKSE_LIMIT || $len === 0)
	{
		return null;
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
		else if($byte === 35)
		{
			$add = '#';
		}
		else
		{
			continue;
		}
		
		$result .= $add;
	}

	$len = strlen($result);

	if($len > 0)
	{
		$rem = 0;

		while(($len - 1 - $rem) >= 0 && ($result[$len - 1 - $rem] === '.' || $result[$len - 1 - $rem] === '+'))
		{
			++$rem;
		}

		if($rem > 0)
		{
			$result = substr($result, 0, -$rem);
			$rem = 0;
			$len = strlen($result);
		}

		while($rem < ($len - 1) && ($result[$rem] === '~' || $result[$rem] === '+' || $result[$rem] === '-') || $result[$rem] === '%')
		{
			++$rem;
		}

		if($rem > 0)
		{
			$result = substr($result, $rem);
		}

		$len = strlen($result);
	}

	if($len === 0)
	{
		return null;
	}
	
	return $result;
}

function secure_host($_string)
{
	$result = secure($_string);
	
	if($result !== null)
	{
		$result = strtolower($result);
	}
	
	return $result;
}

function secure_path($_string)
{
	return secure($_string);
}

//
function delete($_path, $_depth = 0, $_depth_current = 0)
{
	if($_depth === true)
	{
		$_depth = null;
	}
	else if($_depth === false)
	{
		$_depth = 0;
	}
	else if($_depth !== null && !(is_int($_depth) && $_depth >= 0))
	{
		$_depth = 0;
	}

	if(is_dir($_path))
	{
		if(is_int($_depth) && ($_depth <= $_depth_current || $_depth <= 0))
		{
			$handle = opendir($_path);

			if($handle === false)
			{
				return false;
			}

			$count = 0;

			while($sub = readdir($handle))
			{
				if($sub !== '.' && $sub !== '..')
				{
					++$count;
				}
			}

			closedir($handle);

			if($count < 0)
			{
				return false;
			}
			else if($count > 0)
			{
				return false;
			}
			else if(rmdir($_path) === false)
			{
				return false;
			}
			
			return true;
		}
		
		$handle = opendir($_path);

		if($handle === false)
		{
			return false;
		}
		
		$count = 0;

		while($sub = readdir($handle))
		{
			if($sub === '.' || $sub === '..')
			{
				continue;
			}
			else
			{
				++$count;
			}

			if(! is_writable(\kekse\join_path($_path, $sub)))
			{
				return false;
			}
			
			if(is_dir(\kekse\join_path($_path, $sub)))
			{
				if($_depth !== null && $_depth <= $_depth_current)
				{
					return false;
				}
				else if(!\kekse\delete(\kekse\join_path($_path, $sub), $_depth, $_depth_current + 1))
				{
					return false;
				}
				else
				{
					--$count;
				}
			}
			else if(unlink(\kekse\join_path($_path, $sub)) === false)
			{
				return false;
			}
			else
			{
				--$count;
			}
		}
		
		closedir($handle);

		if($count === -1)
		{
			return false;
		}
		else if($count > 0)
		{
			return false;
		}
		else if(!is_writable($_path))
		{
			return false;
		}

		return rmdir($_path);
	}
	else if(file_exists($_path) && is_writable($_path))
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
	
	return true;
}

function get_param($_key, $_numeric = false, $_float = true, $_strict = KEKSE_STRICT, $_fallback = true)
{
	if(!is_bool($_strict))
	{
		$_strict = KEKSE_STRICT;
	}
	
	if(!is_string($_key) || empty($_key))
	{
		return null;
	}
	else
	{
		$len = strlen($_key);
		
		if($len === 0 || $len > KEKSE_LIMIT)
		{
			return null;
		}
	}
	
	$store = null;
	
	if(KEKSE_CLI || empty($_GET))
	{
		if($_fallback && isset($_SERVER))
		{
			$store = $_SERVER;
		}
		else
		{
			return null;
		}
	}
	else if(!empty($_GET))
	{
		$store = $_GET;
	}
	else
	{
		return null;
	}
	
	if(! array_key_exists($_key, $store))
	{
		return null;
	}

	$value = $store[$_key];
	$len = strlen($value);

	if($len > KEKSE_LIMIT || $len === 0)
	{
		return null;
	}
	else if($_numeric === null) switch(strtolower($value[0]))
	{
		case '0':
		case 'y':
			return false;
		case '1':
		case 'n':
			return false;
		default:
			if($_strict)
			{
				return null;
			}
			break;
	}
	
	$result = '';
	$byte = null;
	$hadPoint = false;
	$numeric = null;
	$set = '';
	$negative = false;
	$remove = 0;

	if($_numeric) while($remove < $len && ($value[$remove] === '+' || $value[$remove] === '-'))
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
			
			if($result[strlen($result) - 1] === '.')
			{
				$set = '';
			}
			else if($hadPoint)
			{
				$numeric = false;
			}
			else if(!$_float)
			{
				$numeric = false;
			}
			else
			{
				$hadPoint = true;
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
		else if($byte === 35)
		{
			$set = '#';
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
			if($_strict)
			{
				return null;
			}
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
	else if($_numeric && $_strict)
	{
		return null;
	}

	return $result;
}

if(KEKSE_CLI)
{
	function prompt($_string, $_return = false, $_repeat = true)
	{
		$confirm = function() use (&$_string, &$_return) {
			$res = readline($_string);
			
			if($_return)
			{
				return $res;
			}
			else if(empty($res))
			{
				return null;
			}
			else switch(strtolower($res[0]))
			{
				case 'y': case '1': return true;
				case 'n': case '0': return false;
			}
			
			return null;
		};
		
		$result = $confirm();
		
		if(is_string($result))
		{
			return $result;
		}
		else while($result === null)
		{
			$result = $confirm();
		}
		
		return $result;
	}
}

//
namespace kekse\counter;

//
function send_header($_type = null)
{
	if(!is_string($_type) || empty($_type))
	{
		$_type = get_config('content');
	}
	
	if(get_state('sent'))
	{
		return false;
	}
	else if(\kekse\starts_with($_type, 'content-type', false))
	{
		header($_type);
	}
	else
	{
		header('Content-Type: ' . $_type);
	}
	
	set_state('sent', true);
	return true;
}

function error($_reason, $_exit_code = 224)
{
	$ex = null;
	
	if($_reason instanceof \Exception)
	{
		$ex = $_reason;
		$_reason = $_reason->getMessage();
	}
	
	if(get_config('raw'))
	{
		if($ex === null)
		{
			throw new \Exception($_reason);
		}

		throw $ex;
	}
	else if(get_state('fin'))
	{
		return null;
	}
	else if(KEKSE_CLI)
	{
		if(defined('STDERR'))
		{
			fprintf(STDERR, ' >> ' . $_reason . PHP_EOL);
		}
		else
		{
			die(' >> ' . $_reason . PHP_EOL);
		}

		if(is_int($_exit_code))
		{
			exit($_exit_code);
		}

		exit(224);
	}
	else if(! get_state('sent'))
	{
		send_header();
	}

	if(is_string(get_config('error')))
	{
		die(get_config('error'));
	}

	die($_reason);
}
		
//
function check_path_char($_path, $_basename = true)
{
	if($_basename)
	{
		$_path = basename($_path);
	}
	
	switch($_path[0])
	{
		case '~':
		case '+':
		case '-':
		case '%':
			return false;
	}
	
	return true;
}

function get_path($_path, $_check = false, $_file = false, $_die = true)
{
	if(!is_string($_path))
	{
		if($_die)
		{
			error('Path needs to be (non-empty) String');
		}
		
		return false;
	}
	else if(empty($_path))
	{
		if($_die)
		{
			error('Path may not be empty');
		}
		
		return false;
	}
	
	$result = '';

	if($_path[0] === '/')
	{
		$result = $_path;
	}
	else if($_path === '.')
	{
		if(($result = getcwd()) === false)
		{
			if(($result = realpath($_path)) === false)
			{
				$result = $_path;
			}
		}
	}
	else if(substr($_path, 0, 2) === './')
	{
		if(getcwd() !== false)
		{
			$result = getcwd() . substr($_path, 1);
		}
		else if($_die)
		{
			error('The \'getcwd()\' function doesn\'t work');
		}
		else
		{
			return null;
		}
	}
	else
	{
		$result = __DIR__ . ($_path[0] === '/' ? '' : '/') . $_path;
	}
	
	if(is_string($result))
	{
		$result = \kekse\normalize($result);
	}
	else
	{
		return null;
	}
	
	if($result === '/')
	{
		if($_die)
		{
			error('Root directory reached, which is not allowed here');
		}
		
		return null;
	}
	else if(!check_path_char($result, true))
	{
		if($_die)
		{
			error('Path may not start with one of [ \'~\', \'+\', \'-\', \'%\' ]');
		}
		
		return null;
	}

	if($_check)
	{
		if($_file)
		{
			$dir = dirname($result);

			if(!is_dir($dir) && !mkdir($dir, 01777, true))
			{
				if($_die)
				{
					error('Directory of path \'' . $_path . '\' doesn\'t exist');
				}
				
				return null;
			}
		}
		else if(!is_dir($result) && !mkdir($result, 01777, true))
		{
			if($_die)
			{
				error('Directory \'' . $_path . '\' doesn\'t exist');
			}
			
			return null;
		}
	}
	else if($_file)
	{
		$dir = dirname($result);

		if(!is_dir($dir))
		{
			mkdir($dir, 01777, true);
		}
	}
	else
	{
		$dir = $result;

		if(!is_dir($dir))
		{
			mkdir($dir, 01777, true);
		}
	}

	return $result;
}

function check_config_item($_key, $_value = null, $_bool = false)
{
	$result = null;
	
	if(! array_key_exists($_key, CONFIG_VECTOR))
	{
		if($_bool)
		{
			return false;
		}
		else
		{
			$result = 'No such config item \'' . $_key . '\' available';
		}
		
		return [ $_key, false, $result, null, null, null, null ];
	}

	$item = CONFIG_VECTOR[$_key];
	$valueType = gettype($_value);
	$typesLen = count($item['types']);
	$validTypes = '[ ' . implode(', ', $item['types']) . ' ]';
	$validType = in_array($valueType, $item['types']);

	if(!$validType)
	{
		if($_bool)
		{
			return false;
		}
		else
		{
			$result = 'Invalid type';
		}
		
		return [ $_key, false, $result, $valueType, $validTypes, null, null ];
	}

	$validLength = true;
	$min = $max = null;

	if(isset($item['min']))
	{
		$min = $item['min'];
		
		if($valueType === 'string')
		{
			if(strlen($_value) < $item['min'])
			{
				$validLength = false;
			}
		}
		else if($valueType === 'integer')
		{
			$validMin = true;
			$validMax = true;

			if($_value < $item['min'])
			{
				$validMin = false;
			}

			if(isset($item['max']))
			{
				$max = $item['max'];
				
				if($_value > $item['max'])
				{
					$validMax = false;
				}
			}

			if($validMin && $validMax)
			{
				$validLength = true;
			}
		}
		else
		{
			$validLength = null;
		}
	}
	else if(isset($item['max']))
	{
		$max = $item['max'];
		
		if($valueType === 'string')
		{
			if(strlen($_value) > $item['max'])
			{
				$validLength = false;
			}
		}
		else if($valueType === 'integer')
		{
			if($_value > $item['max'])
			{
				$validLength = false;
			}
		}
		else
		{
			$validLength = null;
		}
	}

	if($validLength === false)
	{
		if($_bool)
		{
			return false;
		}
		else
		{
			$result = 'Invalid length (';

			if(isset($item['min']))
			{
				$result .= 'minimum is ' . $item['min'];

				if(isset($item['max']))
				{
					$result .= ', ';
				}
			}

			if(isset($item['max']))
			{
				$result .= 'maximum is ' . $item['max'];
			}
			
			$result .= ')';
		}
		
		return [ $_key, false, $result, $valueType, $validTypes, $min, $max ];
	}

	$validTest = null;

	if(isset($item['test']) && $item['test'])
	{
		switch($_key)
		{
			case 'path':
				$validTest = (get_path($_value, true, false, false) !== false);
				break;
			case 'log':
				$validTest = (get_path($_value, true, true, false) !== false);
				break;
			case 'fonts':
				$validTest = (get_path($_value, true, false, false) !== false);
				break;
			case 'hash':
				$validTest = in_array($_value, hash_algos());
				break;
			default:
				$validTest = null;
				break;
		}
	}
	else
	{
		$validTest = true;
	}
	
	if($validTest === null)
	{
		if($_bool)
		{
			return null;
		}
		else
		{
			$result = 'Warning (test incomplete)';
		}
		
		return [ $_key, false, $result, $valueType, $validTypes, $min, $max ];
	}
	else if($validTest === false)
	{
		if($_bool)
		{
			return false;
		}
		else
		{
			$result = 'Failed any extended test';
		}
		
		return [ $_key, false, $result, $valueType, $validTypes, $min, $max ];
	}

	//
	if($_bool)
	{
		return true;
	}
	else
	{
		$result = 'Passed';

		if(isset($item['without']) && $item['without'])
		{
			$result .= ' (without further tests)';
		}
	}
	
	return [ $_key, true, $result, $valueType, $validTypes, $min, $max ];
}

function check_config_key($_key, $_die = true)
{
	if(!is_string($_key) || empty($_key))
	{
		if($_die)
		{
			error('Invalid $_key argument');
		}

		return false;
	}
	else if(in_array($_key, CONFIG_STATIC))
	{
		return null;
	}
	
	return array_key_exists($_key, CONFIG_VECTOR);
}

function check_config($_config = null, $_bool = false, $_die = true)
{
	//
	global $CONFIG;

	//
	$def = null;

	if(is_array($_config))
	{
		$def = true;
	}
	else
	{
		$_config = DEFAULTS;
		$def = false;
	}

	//
	$result = array();

	//
	foreach($_config as $key => $value)
	{
		$k = check_config_key($key, $_die);
		$result[$key] = check_config_item($key, $value, $_bool);

		if($k === null && $def)
		{
			if($_bool)
			{
				$result[$key] = false;
			}
			else
			{
				$result[$key][1] = false;
				$result[$key][2] = 'Static setting, can\'t be overwritten';
			}
		}
		else if($k === false)
		{
			if($_bool)
			{
				$result[$key] = false;
			}
			else
			{
				$result[$key][1] = false;
				$result[$key][2] = 'Unknown setting';
			}
		}
	}

	//
	if(!$def)
	{
		$keys = array_keys(CONFIG_VECTOR);
		$len = count($keys);

		for($i = 0; $i < $len; ++$i)
		{
			if(!array_key_exists($keys[$i], $_config))
			{
				$item = CONFIG_VECTOR[$keys[$i]];

				if($_bool)
				{
					$result[$keys[$i]] = false;
				}
				else
				{
					$result[$keys[$i]] = [ $keys[$i], false, 'Missing!', null, '[ ' . implode(', ', CONFIG_VECTOR[$keys[$i]]['types']) . ' ]', null, null ];
				}
			}
		}
	}

	//
	return $result;
}

function check_host_config($_host, $_load = true, $_bool = false, $_die = true)
{
	//
	global $CONFIG;

	//
	$config = null;

	if($_load)
	{
		if(($config = load_config(\kekse\join_path(get_state('path'), '%' . $_host))) === null)
		{
			if($_die)
			{
				error('Couldn\'t load host configaration');
			}

			return null;
		}
		else
		{
			$config = $config[0];
		}
	}
	else if(isset($CONFIG[$_host]))
	{
		$config = $CONFIG[$_host];
	}
	else if($_die)
	{
		error('Host configuration not available');
	}
	else
	{
		return null;
	}

	//
	return check_config($config, $_bool, $_die);
}

function unset_invalid_config(&$_config, $check)
{
	foreach($check as $key => $state)
	{
		$bool = (is_bool($state) ? $state : $state[1]);

		if(!$bool)
		{
			unset($_config[$key]);
		}
	}

	return $_config;	
}

function get_config($_key, $_host = null, $_die = true)
{
	//
	if(is_string($_key) && !empty($_key))
	{
		$_key = strtolower($_key);
	}
	else if($_die)
	{
		error('Invalid $_key (no non-empty String)');
	}
	else
	{
		return null;
	}
	
	//
	global $CONFIG;

	//
	$config = null;

	if(!is_string($_host) || empty($_host))
	{
		$_host = get_state('host');
	}
	
	if(isset($CONFIG[$_host]))
	{
		$config = $CONFIG[$_host];
	}

	//
	if($config !== null && array_key_exists($_key, $config))
	{
		return $config[$_key];
	}
	else if(array_key_exists($_key, DEFAULTS))
	{
		return DEFAULTS[$_key];
	}

	//
	return null;
}

function config_loaded($_host = null)
{
	//
	global $CONFIG;

	//
	$result = null;

	if(is_string($_host) && !empty($_host))
	{
		$result = isset($CONFIG[$_host]);
	}
	else
	{
		$result = array_keys($CONFIG);
	}

	//
	return $result;
}

function make_config($_host, $_reload = null, $_unset = true, $_restore = false)
{
	//
	global $CONFIG;
	global $HASHES;

	//
	$data = null;
	$path = \kekse\join_path(get_state('path'), '%' . $_host);

	if(!(is_file($path) && is_readable($path)))
	{
		if($_unset)
		{
			unload_config($_host);
		}
		else if($_restore && isset($CONFIG[$_host]))
		{
			return $CONFIG[$_host];
		}

		return null;
	}
	else if(isset($CONFIG[$_host]))
	{
		if($_reload === false)
		{
			return $CONFIG[$_host];
		}
		else if($_reload === null)
		{
			if(!isset($HASHES[$_host]))
			{
				return $CONFIG[$_host];
			}
			else if(($data = file_get_contents($path)) === false)
			{
				return $CONFIG[$_host];
			}

			if(hash(get_config('hash'), $data) === $HASHES[$_host])
			{
				return $CONFIG[$_host];
			}
		}
	}

	//
	$conf = ($data === null ? load_config($path) : load_config(null, $data));
	$data = $conf[2];
	$hash = $conf[1];
	$conf = $conf[0];

	if($conf === null)
	{
		return null;
	}
	else
	{
		$HASHES[$_host] = $hash;
		$chk = check_config($conf, true, false);
		unset_invalid_config($conf, $chk);
	}

	//
	return ($CONFIG[$_host] = $conf);
}

function unload_config($_host)
{
	global $CONFIG;
	global $HASHES;

	if(array_key_exists($_host, $CONFIG))
	{
		unset($CONFIG[$_host]);
		unset($HASHES[$_host]);
		return true;
	}

	return false;
}

function count_config($_path)
{
	$result = load_config($_path);
	
	if($result === null)
	{
		return -1;
	}
	else if($chk = check_config($result = $result[0], true, false))
	{
		$result = unset_invalid_config($result, $chk);
	}
	else
	{
		return null;
	}

	return count($result);
}

function count_host_config($_host)
{
	return count_config(\kekse\join_path(get_state('path'), '%' . $_host));
}

function load_config($_path, $_data = null, $_depth = 8)
{
	$data = null;

	if(is_string($_data))
	{
		$data = $_data;
		$_path = null;
	}
	else if(! (is_file($_path) && is_readable($_path)))
	{
		return null;
	}

	$result = null;

	if($data === null)
	{
		if(($data = file_get_contents($_path)) === false)
		{
			return null;
		}
	}

	if(!is_array($result = json_decode($data, true, $_depth)))
	{
		return null;
	}
	else if(check_config($result, true, false) === null)
	{
		return null;
	}

	$hash = hash(get_config('hash'), $data);
	return [ $result, $hash, $data ];
}

//
function counter($_host = null, $_read_only = null)
{
	//
	if(!is_bool($_read_only))
	{
		$_read_only = !!get_config('raw');
	}
	
	function log_error($_reason, $_source = '', $_path = '', $_die = true)
	{
		//
		if(get_config('raw'))
		{
			if($_reason instanceof \Exception)
			{
				throw $_reason;
			}
			
			throw new \Exception($_reason);
		}
		else if(KEKSE_CLI)
		{
			if($_die)
			{
				return error($_reason);
			}
			
			return null;
		}
		else if($_reason instanceof \Exception)
		{
			$_reason = $_reason->getMessage();
		}
		
		$data = '[' . (string)time() . ']';

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

		if(get_state('log') !== null)
		{
			$result = file_put_contents(get_state('log'), $data, FILE_APPEND);

			if($result === false)
			{
				if($_die)
				{
					error('Logging error');
				}

				return null;
			}
		}
		
		if($_die)
		{
			return error($_reason);
		}

		return $data;
	}

	//
	set_state('test', (KEKSE_CLI ? null : (isset($_GET['test']))));
	set_state('ro', (KEKSE_CLI ? null : (get_state('test') || (isset($_GET['readonly']) || isset($_GET['ro'])))));
	set_state('zero', (KEKSE_CLI ? null : (get_config('drawing') && isset($_GET['zero']) && extension_loaded('gd'))));
	set_state('draw', (KEKSE_CLI ? null : (get_state('zero') || (get_config('drawing') && isset($_GET['draw']) && extension_loaded('gd')))));

	//
	set_state('path', get_path(get_config('path'), true, false, true));
	set_state('log', get_path(get_config('log'), true, true, true));

	if(file_exists(get_state('log')) && !(is_file(get_state('log')) || is_writable(get_state('log'))))
	{
		set_state('log', null);
	}

	if(get_config('drawing') || KEKSE_CLI)
	{
		set_state('fonts', get_path(get_config('fonts'), (KEKSE_CLI ? false : true), false, true));
	}
	else
	{
		set_state('fonts', null);
	}

	//
	if(! (is_readable(get_state('path'))))// && is_writable(get_state('path'))))
	{
		error('Your \'PATH\' is not readable');
	}
	else if(get_config('drawing') && (!is_string(get_state('fonts')) || !is_readable(get_state('fonts'))))
	{
		error('Your \'FONTS\' path is not readable');
	}
	else if(!is_dir(dirname(get_state('log'))))
	{
		set_state('log', null);
		//error('Your \'LOG\' directory is not a directory');
	}
	
	//
	function cli()
	{
		//
		define('COUNTER_VALUE', 1);
		define('COUNTER_DIR', 2);
		define('COUNTER_FILE', 4);
		define('COUNTER_CONFIG', 8);

		//
		function get_arguments($_index, $_secure = false, $_null = true, $_unique = true)
		{
			if(!is_int($_index) || $_index < 0)
			{
				if($_null)
				{
					return null;
				}
				
				return array();
			}
			
			if(KEKSE_ARGC <= $_index)
			{
				if($_null)
				{
					return null;
				}
				
				return array();
			}
			
			$result = array();

			for($i = $_index + 1, $j = 0; $i < KEKSE_ARGC; ++$i)
			{
				$item = KEKSE_ARGV[$i];
				$len = strlen($item);

				if($item[0] === '-')
				{
					break;
				}
				else if($len === 0 || $len > KEKSE_LIMIT)
				{
					continue;
				}

				if($_secure && ($item = \kekse\secure($item)) === null)
				{
					continue;
				}

				if($_unique && in_array($item, $result))
				{
					continue;
				}

				$result[$j++] = $item;
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

		//
		function get_host_item($_host, &$_result, $_check = true)
		{
			if($_host[0] === '.' || strlen($_host) === 1)
			{
				return 0;
			}
			
			$type = $_host[0];

			switch($type)
			{
				case '~':
					if($_check && !is_file(\kekse\join_path(get_state('path'), $_host)))
					{
						return 0;
					}

					$type = COUNTER_VALUE;
					break;
				case '+':
					if($_check && !is_dir(\kekse\join_path(get_state('path'), $_host)))
					{
						return 0;
					}

					$type = COUNTER_DIR;
					break;
				case '-':
					if($_check && !is_file(\kekse\join_path(get_state('path'), $_host)))
					{
						return 0;
					}

					$type = COUNTER_FILE;
					break;
				case '%':
					if($_check && !is_file(\kekse\join_path(get_state('path'), $_host)))
					{
						return 0;
					}
					
					$type = COUNTER_CONFIG;
					break;
				default:
					return 0;
			}

			if(!isset($_result[$_host = substr($_host, 1)]))
			{
				$_result[$_host] = 0;
			}
			
			$_result[$_host] |= $type;
			return $type;
		}

		function get_hosts($_index = null, $_sort = true)
		{
			//
			$list = null;
			
			if(is_int($_index) && $_index > -1)
			{
				$list = get_arguments($_index, false, true, true);
			}

			$result = array();
			$found = 0;

			if($list === null)
			{
				$handle = opendir(get_state('path'));
				
				if($handle === false)
				{
					fprintf(STDERR, ' >> Couldn\'t opendir()' . PHP_EOL);
					exit(1);
				}
				
				while($host = readdir($handle))
				{
					if(get_host_item($host, $result) > 0)
					{
						++$found;
					}
				}
				
				closedir($handle);
			}
			else
			{
				$len = count($list);

				for($i = 0; $i < $len; ++$i)
				{
					$sub = \kekse\join_path(get_state('path'), '{~,+,-,%}' . strtolower($list[$i]));
					$sub = glob($sub, GLOB_BRACE);
					$subLen = count($sub);

					if($subLen === 0)
					{
						continue;
					}
					else for($j = 0; $j < $subLen; ++$j)
					{
						if(get_host_item(basename($sub[$j]), $result) > 0)
						{
							++$found;
						}
					}
				}
			}

			//
			if($found === 0)
			{
				$result = null;
			}
			else if($_sort)
			{
				ksort($result, SORT_STRING | SORT_NATURAL | SORT_FLAG_CASE);
			}

			//
			return $result;
		}

		//
		function info($_index = null, $_version = true, $_copyright = true)
		{
			if($_version)
			{
				printf('v' . COUNTER_VERSION . PHP_EOL);
			}

			if($_copyright)
			{
				printf('Copyright (c) %s' . PHP_EOL, KEKSE_COPYRIGHT);
			}

			exit(0);
		}

		function help($_index = null)
		{
			printf(' >> Visit: <' . COUNTER_HELP . '>' . PHP_EOL);
			printf(' >> Available parameters (use only one at the same time, please):' . PHP_EOL . PHP_EOL);

			printf('    -? / --help' . PHP_EOL);
			printf('    -V / --version' . PHP_EOL);
			printf('    -C / --copyright' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -c / --check [*]' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -v / --values [*]' . PHP_EOL);
			printf('    -s / --sync [*]' . PHP_EOL);
			printf('    -l / --clean [*]' . PHP_EOL);
			printf('    -p / --purge [*]' . PHP_EOL);
			printf('    -z / --sanitize [--allow-without-values / -w]' . PHP_EOL);
			printf('    -r / --remove [*]' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -t / --set (host) [value = 0]' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -f / --fonts [*]' . PHP_EOL);
			printf('    -y / --types' . PHP_EOL);
			printf('    -h / --hashes' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -e / --errors' . PHP_EOL);
			printf('    -u / --unlog' . PHP_EOL);
			printf(PHP_EOL);
			printf('The \'*\' arguments should all support GLOBs (which you should escape or quote).' . PHP_EOL);
			printf('Arguments within \'[]\' are optional, and those within \'()\' are required..' . PHP_EOL);

			exit(0);
		}

		function hashes($_index = null)
		{
			$list = hash_algos();
			$len = count($list);
			
			for($i = 0; $i < $len; ++$i)
			{
				printf($list[$i] . PHP_EOL);
			}
			
			exit(0);
		}

		function fonts($_index = null)
		{
			//
			if(!is_string(get_state('fonts')) || empty(get_state('fonts')))
			{
				fprintf(STDERR, ' >> \'FONTS\' directory is not properly configured' . PHP_EOL);
				exit(1);
			}
			else if(! is_dir(get_state('fonts')))
			{
				fprintf(STDERR, ' >> \'FONTS\' directory doesn\'t exist.' . PHP_EOL);
				exit(2);
			}

			$fonts = get_arguments($_index, false, true, true);
			$result = array();
			$defined;

			if($fonts === null)
			{
				$defined = -1;
				$result = glob(\kekse\join_path(get_state('fonts'), '*.ttf'), GLOB_BRACE);
				$len = count($result);
				
				if($len === 0)
				{
					$result = null;
				}
				else for($i = 0; $i < $len; ++$i)
				{
					$result[$i] = basename($result[$i], '.ttf');
				}
			}
			else
			{
				$defined = count($fonts);
				$len = count($fonts);
				$idx = 0;
				
				for($i = 0; $i < $len; ++$i)
				{

					$sub = glob(\kekse\join_path(get_state('fonts'), basename($fonts[$i], '.ttf') . '.ttf'));
					$subLen = count($sub);

					if($subLen === 0)
					{
						continue;
					}
					else for($j = 0; $j < $subLen; ++$j)
					{
						$result[$idx++] = basename($sub[$j], '.ttf');
					}
				}
				
				if($idx === 0)
				{
					$result = null;
				}
			}

			if($result === null)
			{
				fprintf(STDERR, ' >> No fonts found' . ($defined < 0 ? '' : ' (with your ' . $defined . ' globs)') . PHP_EOL);
				exit(3);
			}
			
			$len = count($result);
			printf(' >> Found %d fonts' . ($defined < 0 ? '' : ' (by %d globs)') . PHP_EOL . PHP_EOL, $len, $defined);
			
			for($i = 0; $i < $len; ++$i)
			{
				printf($result[$i] . PHP_EOL);
			}

			printf(PHP_EOL);
			exit(0);
		}

		function types($_index = null)
		{
			if(!extension_loaded('gd'))
			{
				fprintf(STDERR, ' >> The GD library/extension is not loaded/available' . PHP_EOL);
				exit(1);
			}
			else
			{
				printf(' >> Supporting the following image types here:' . PHP_EOL . PHP_EOL);
			}

			$types = imagetypes();

			printf('\'png\': %s' . PHP_EOL, ($types & IMG_PNG ? 'yes' : 'no'));
			printf('\'jpg\': %s' . PHP_EOL, ($types & IMG_JPG ? 'yes' : 'no'));

			printf(PHP_EOL);
			exit(0);
		}
	
		function check($_index = null)
		{
			//
			$params = 0;
			$hosts = array();
			$cnt = 0;
			$idx = 0;

			//
			if(is_int($_index) && $_index > -1)
			{
				for($i = $_index + 1; $i < KEKSE_ARGC; ++$i)
				{
					$item = KEKSE_ARGV[$i];
					$len = strlen($item);

					if($item[0] === '-')
					{
						break;
					}
					else if($len === 0 || $len > KEKSE_LIMIT)
					{
						continue;
					}
					else
					{
						++$cnt;

						$item = \kekse\join_path(get_state('path'), '%' . $item);
						$item = glob($item, GLOB_BRACE);
						$len = count($item);
						
						for($j = 0; $j < $len; ++$j)
						{
							if(!is_file($item[$j]) || !is_readable($item[$j]))
							{
								continue;
							}
							else if(in_array($item[$j] = substr(basename($item[$j]), 1), $hosts))
							{
								continue;
							}

							$hosts[$idx++] = $item[$j];
						}
					}
				}

				if($cnt === 0)
				{
					$hosts = null;
				}
				else if($idx === 0)
				{
					fprintf(STDERR, ' >> NONE of your %d hosts/globs has own configuration' . PHP_EOL, $cnt);
					exit(1);
				}
			}
			else
			{
				$hosts = null;
			}

			//
			$result = null;
			$h = 0;

			if($hosts === null)
			{
				printf(' >> Checking your DEFAULT configuration (no hosts specified).' . PHP_EOL . PHP_EOL);
				$result = check_config();
			}
			else
			{
				printf(' >> Found %d per-host configuration' . ($idx === 1 ? '' : 's') . ' (by %d glob' . ($cnt === 1 ? '' : 's') . ' in total).' . PHP_EOL . PHP_EOL, $idx, $cnt);
				$len = count($hosts);
				$result = array();

				for($i = 0; $i < $len; ++$i)
				{
					if(($result[$hosts[$i]] = check_host_config($hosts[$i], true, false)) === null)
					{
						unset($result[$hosts[$i]]);
					}
					else
					{
						++$h;
					}
				}
			}

			//			
			$ok = 0;
			$bad = 0;
			$len = $maxLen = $maxLenKey = 0;

			if($hosts === null)
			{
				foreach($result as $key => $state)
				{
					if(($len = strlen($state[2])) > $maxLen)
					{
						$maxLen = $len;
					}

					if(($len = strlen($state[0])) > $maxLenKey)
					{
						$maxLenKey = $len;
					}
				}
				
				$maxLenKey += 3;
				$format = ('%' . $maxLenKey . 's ]  %-4s  %-' . $maxLen . 's  %10s%-14s %s' . PHP_EOL);
				
				foreach($result as $key => $state)
				{
					$limits = '';
					
					if($state[5] !== null)
					{
						$limits .= '(' . $state[5] . '..';
						
						if($state[6] !== null)
						{
							$limits .= $state[6];
						}
						
						$limits .= ')';
					}
					else if($state[6] !== null)
					{
						$limits .= '(..' . $state[6] . ')';
					}

					if($state[1])
					{
						printf($format, '[ ' . $state[0], 'OK', $state[2], $state[3], $limits, $state[4]);
						++$ok;
					}
					else
					{
						fprintf(STDERR, $format, '[ ' . $state[0], 'BAD', $state[2], $state[3], $limits, $state[4]);
						++$bad;
					}
				}
			}
			else
			{
				$maxLenHost = 0;

				foreach($result as $host => $item)
				{
					foreach($item as $key => $state)
					{
						if(($len = strlen($state[2])) > $maxLen)
						{
							$maxLen = $len;
						}

						if(($len = strlen($state[0])) > $maxLenKey)
						{
							$maxLenKey = $len;
						}
					}

					if(($len = strlen($host)) > $maxLenHost)
					{
						$maxLenHost = $len;
					}
				}

				$maxLenKey += 3;

				if($h === 1)
				{
					$format = ('%s%' . $maxLenKey . 's ]  %-4s  %-' . $maxLen . 's  %10s%-14s %s' . PHP_EOL);
				}
				else
				{
					$format = (' %' . $maxLenHost . 's %' . $maxLenKey . 's ]  %-4s  %-' . $maxLen . 's  %10s%-14s %s' . PHP_EOL);
				}
				
				foreach($result as $host => $item)
				{
					foreach($item as $key => $state)
					{
						$limits = '';

						if($state[5] !== null)
						{
							$limits .= '(' . $state[5] . '..';
							
							if($state[6] !== null)
							{
								$limits .= $state[6];
							}
							
							$limits .= ')';
						}
						else if($state[6] !== null)
						{
							$limits .= '(..' . $state[6] . ')';
						}
						
						if($state[1])
						{
							printf($format, ($h === 1 ? '' : $host), '[ ' . $state[0], 'OK', $state[2], $state[3], $limits, $state[4]);
							++$ok;
						}
						else
						{
							fprintf(STDERR, $format, ($h === 1 ? '' : $host), '[ ' . $state[0], 'BAD', $state[2], $state[3], $limits, $state[4]);
							++$bad;
						}
					}
				}
			}

			printf(PHP_EOL);

			if(get_config('raw'))
			{
				fprintf(STDERR, ' >> WARNING: `RAW` mode enabled, which leads to a changed behavior..' . PHP_EOL);
			}
			
			//
			if($bad === 0)
			{
				printf(' >> All %d were OK! :-)', $ok);
				printf('.' . PHP_EOL);
			}
			else if($ok === 0)
			{
				fprintf(STDERR, ' >> NO single item was valid (%d errors)...' . PHP_EOL, $bad);
			}
			else
			{
				printf(' >> Only %d items were valid.. %d caused errors!' . PHP_EOL, $ok, $bad);
			}

			if($bad === 0)
			{
				exit(0);
			}

			exit(2);
		}
		
		function set($_index = null)
		{
			$host = null;
			$value = 0;

			if(is_int($_index) && $_index > -1)
			{
				for($i = $_index + 1, $set = 0; $i < KEKSE_ARGC; ++$i)
				{
					if($set === 2)
					{
						break;
					}

					$item = KEKSE_ARGV[$i];
					$len = strlen($item);

					if($item[0] === '-')
					{
						break;
					}
					else if($len === 0 || $len > KEKSE_LIMIT)
					{
						continue;
					}
					else if(is_numeric($item) && $set === 1)
					{
						if(($value = (int)$item) < 0)
						{
							$value = 0;
						}

						++$set;
					}
					else if(($item = \kekse\secure_host($item)) === null)
					{
						continue;
					}
					else if($set === 0)
					{
						$host = $item;
						++$set;
					}
				}
				
				if($host === null)
				{
					fprintf(STDERR, ' >> No valid target host specified!' . PHP_EOL);
					exit(1);
				}
			}
			else
			{
				fprintf(STDERR, ' >> Can\'t locate arguments!' . PHP_EOL);
				exit(2);
			}

			$p = \kekse\join_path(get_state('path'), '~' . $host);
			$existed = file_exists($p);
			$orig = null;

			if($existed)
			{
				if(!is_file($p))
				{
					fprintf(STDERR, ' >> Target is not a file!' . PHP_EOL);
					exit(3);
				}
				else if(!is_writable($p) && is_readable($p))
				{
					fprintf(STDERR, ' >> File is not readable (n)or writable!' . PHP_EOL);
					exit(4);
				}
				else if(($orig = file_get_contents($p)) === false)
				{
					$orig = null;
				}
				else if(($orig = (int)$orig) === $value)
				{
					printf(' >> File is already set to (%d).. so nothing changes! :-)' . PHP_EOL, $value);
					exit(0);
				}
			}

			printf(' >> Target file is \'%s\'' . ($existed ? ' (already existed)' : '') . '.' . PHP_EOL, $p);

			if($orig === null)
			{
				if(!\kekse\prompt('Initialize file with value (' . $value . ') now [yes/no]? '))
				{
					fprintf(STDERR, ' >> Aborting..' . PHP_EOL);
					exit(5);
				}
			}
			else if(!\kekse\prompt('Replace old value (' . $orig . ') by new one (' . $value . ') now [yes/no]? '))
			{
				fprintf(STDERR, ' >> Aborting..' . PHP_EOL);
				exit(6);
			}

			if(file_put_contents($p, (string)$value) === false)
			{
				fprintf(STDERR, ' >> Unable to write value: %d' . PHP_EOL, $value);
				exit(7);
			}
			else if($orig === null)
			{
				printf(' >> Successfully initialized file with value: %d' . PHP_EOL, $value, $p);
			}
			else
			{
				printf(' >> Successfully replaced old value (%d) by new value: %d' . PHP_EOL, $orig, $value);
			}

			//
			exit(0);
		}

		//
		function purge($_index = null)
		{
			//
			$list = get_hosts($_index);

			if($list === null)
			{
				fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
				exit(1);
			}
			else
			{
				printf(' >> This will *delete* the whole cache!' . PHP_EOL);
				printf(' >> Maybe you\'d rather like to \'--clean/-c\' instead!?' . PHP_EOL . PHP_EOL);
			}

			$dirs = array();
			$files = array();
			$total = 0;
			$len = 0;
			$d = 0;
			$f = 0;

			foreach($list as $key => $value)
			{
				++$len;
				$c = 0;
				
				if($value & COUNTER_DIR)
				{
					$dirs[$d++] = $key;
					++$c;
				}
				
				if($value & COUNTER_FILE)
				{
					$files[$f++] = $key;
					++$c;
				}
				
				if($c > 0)
				{
					++$total;
					printf($key . PHP_EOL);
				}
			}

			if($total === 0)
			{
				fprintf(STDERR, ' >> All of the %d found hosts are already free of special files.' . PHP_EOL, $len);
				exit(0);
			}

			printf(PHP_EOL . ' >> Found %d directories and %d files - for %d hosts.' . PHP_EOL, count($dirs), count($files), $total);

			if(!\kekse\prompt('Do you really want to delete them [yes/no]? '))
			{
				fprintf(STDERR, ' >> Good, we\'re aborting here, as requested.' . PHP_EOL);
				exit(2);
			}
			else
			{
				printf(PHP_EOL);
			}

			$errors = 0;
			$good = 0;

			for($i = 0; $i < $d; ++$i)
			{
				if(\kekse\delete(\kekse\join_path(get_state('path'), '+' . $dirs[$i]), true))
				{
					++$good;
				}
				else
				{
					++$errors;
				}
			}

			for($i = 0; $i < $f; ++$i)
			{
				if(\kekse\delete(\kekse\join_path(get_state('path'), '-' . $files[$i]), false))
				{
					++$good;
				}
				else
				{
					++$errors;
				}
			}

			if($errors === 0)
			{
				printf(' >> Great, all %d files deleted successfully!' . PHP_EOL, $good);
				exit(0);
			}
			else if($good > 0)
			{
				printf(' >> %d files sucessfully deleted..' . PHP_EOL, $good);
				fprintf(STDERR, ' >> ... but %d files could *not* be removed. :-/' . PHP_EOL, $errors);
				exit(3);
			}
			
			fprintf(STDERR, ' >> NONE of the selected %d files deleted! :-(' . PHP_EOL, $total);
			exit(4);
		}
		
		function clean($_index = null)
		{
			//
			$list = get_hosts($_index);

			if($list === null)
			{
				fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
				exit(1);
			}

			$orig = count($list);

			foreach($list as $host => $type)
			{
				if(! ($type & COUNTER_DIR))
				{
					unset($list[$host]);
				}
			}

			$len = count($list);

			if($len === 0)
			{
				printf(' >> No caches stored for %d host' . ($orig === 1 ? '' : 's') . '.' . PHP_EOL, $orig);
				exit(0);
			}
			else
			{
				printf(' >> Found %d host' . ($len === 1 ? '' : 's') . ' (of %d) with caches:' . PHP_EOL . PHP_EOL, $len, $orig);
			}

			foreach($list as $host => $type)
			{
				printf('    ' . $host . PHP_EOL);
			}

			printf(PHP_EOL);

			if(!\kekse\prompt('Do you want to continue [yes/no]? '))
			{
				fprintf(STDERR, ' >> Abort by request.' . PHP_EOL);
				exit(2);
			}

			$result = array();
			$errors = array();
			$delete = array();

			foreach($list as $host => $type)
			{
				$result[$host] = 0;
				$errors[$host] = 0;
				$delete[$host] = 0;

				$handle = opendir(\kekse\join_path(get_state('path'), '+' . $host));

				if($handle === false)
				{
					$errors[$host] = 1;
					continue;
				}
				else while($sub = readdir($handle))
				{
					if($sub[0] === '.' || strlen($sub) === 1)
					{
						continue;
					}

					$p = \kekse\join_path(get_state('path'), '+' . $host, $sub);

					if(!is_file($p))
					{
						continue;
					}
					else
					{
						++$result[$host];
					}

					$val = file_get_contents($p);

					if($val === false)
					{
						++$errors[$host];
					}
					else if(get_config('threshold') === null || \kekse\timestamp($val = (int)$val) >= get_config('threshold'))
					{
						if(\kekse\delete($p, false))
						{
							++$delete[$host];
							--$result[$host];
						}
						else
						{
							++$errors[$host];
						}
					}
				}

				closedir($handle);

				if($delete[$host] === 0)
				{
					unset($delete[$host]);
				}
			}

			//
			foreach($result as $host => $count)
			{
				if($count === 0)
				{
					if(is_writable(\kekse\join_path(get_state('path'), '+' . $host))) \kekse\delete(\kekse\join_path(get_state('path'), '+' . $host), true);
					if(is_writable(\kekse\join_path(get_state('path'), '-' . $host))) \kekse\delete(\kekse\join_path(get_state('path'), '-' . $host), false);
				}
				else if(is_writable(\kekse\join_path(get_state('path'), '-' . $host)))
				{
					if(! file_put_contents(\kekse\join_path(get_state('path'), '-' . $host), (string)$count))
					{
						++$errors[$host];
					}
				}
				
				if($errors[$host] === 0)
				{
					unset($errors[$host]);
				}
			}

			//
			$e = count($errors);
			$d = count($delete);

			printf(PHP_EOL);

			if($d > 0)
			{
				if($e === 0)
				{
					printf(' >> Great, not a single error!');
				}
				else
				{
					$total = 0;

					foreach($errors as $h => $c)
					{
						$total += $c;
					}

					fprintf(STDERR, ' >> Hm, %d host' . ($e === 0 ? '' : 's') . ' caused %d errors.', $e, $total);
				}
			}

			if($d === 0)
			{
				printf(' >> ' . ($e === 0 ? 'N' : 'But n') . 'o deletions, so nothing changed at all.' . PHP_EOL);
				exit(0);
			}
			else
			{
				printf(' Deleted files per host:' . PHP_EOL . PHP_EOL);
			}

			$sum = 0;
			$len = 0;
			$maxLen = 0;

			foreach($delete as $host => $del)
			{
				$sum += $del;

				if(($len = strlen($host)) > $maxLen)
				{
					$maxLen = $len;
				}
			}

			$s = ' %' . $maxLen . 's: %d' . PHP_EOL;

			foreach($delete as $host => $del)
			{
				printf($s, $host, $del);
			}

			//
			printf(PHP_EOL . ' >> Totally deleted %d files.' . PHP_EOL, $sum);
			exit(0);
		}
		
		//
		function sanitize($_index = null, $_allow_without_values = false)
		{
			//
			if(is_int($_index) && $_index > -1) for($i = $_index + 1; $i < KEKSE_ARGC; ++$i)
			{
				$item = KEKSE_ARGV[$i];
				$len = strlen($item);

				if($item[0] === '-')
				{
					break;
				}
				else if($len < 2 || $len > KEKSE_LIMIT)
				{
					continue;
				}
				else switch($item)
				{
					case '--allow-without-values':
					case '-w':
						$_allow_without_values = true;
						break;
				}

				if($_allow_without_values)
				{
					break;
				}
			}

			$delete = array();
			$index = 0;
			
			//
			$handle = opendir(get_state('path'));
			
			if($handle === false)
			{
				fprintf(STDERR, ' >> Failed to open directory (\'' . get_state('path') . '\')' . PHP_EOL);
				exit(1);
			}
			else while($sub = readdir($handle))
			{
				if($sub === '.' || $sub === '..')
				{
					continue;
				}
				
				$p = \kekse\join_path(get_state('path'), $sub);
				
				if($sub[0] === '~')
				{
					if(strlen($sub) === 1 || !is_file($p))
					{
						$delete[$index++] = $p;
					}
				}
				else if($sub[0] === '+')
				{
					if(strlen($sub) === 1 || !is_dir($p))
					{
						$delete[$index++] = $p;
					}
					else if(!$_allow_without_values && !is_file(\kekse\join_path(get_state('path'), '~' . substr($sub, 1))))
					{
						$delete[$index++] = $p;
					}
				}
				else if($sub[0] === '-')
				{
					if(strlen($sub) === 1 || !is_file($p))
					{
						$delete[$index++] = $p;
					}
					else if(!$_allow_without_values && !is_file(\kekse\join_path(get_state('path'), '~' . substr($sub, 1))))
					{
						$delete[$index++] = $p;
					}
				}
				else if($sub[0] === '%')
				{
					if(strlen($sub) === 1 || !is_file($p))
					{
						$delete[$index++] = $p;
					}
					else if(!$_allow_without_values && !is_file(\kekse\join_path(get_state('path'), '~' . substr($sub, 1))))
					{
						$delete[$index++] = $p;
					}
				}
				else
				{
					$delete[$index++] = $p;
				}
			}
				
			closedir($handle);
			
			//
			$len = count($delete);
			
			if($len === 0)
			{
				printf(' >> No files found to delete.' . PHP_EOL);
				exit(0);
			}
			else
			{
				printf(' >> Please allow to delete %d files (non-existing value files %s)..' . PHP_EOL, $len, ($_allow_without_values ? 'are allowed' : 'will also delete caches'));
				
				if(!\kekse\prompt('Do you really want to continue [yes/no]? '))
				{
					fprintf(STDERR, ' >> Aborted, as requested.' . PHP_EOL);
					exit(2);
				}
				else
				{
					printf(PHP_EOL);
				}
			}
			
			$result = 0;
			$errors = 0;
			
			for($i = 0; $i < count($delete); ++$i)
			{
				if(file_exists($delete[$i]))
				{
					if(\kekse\delete($delete[$i], true))
					{
						++$result;
					}
					else
					{
						++$errors;
					}
				}
				else
				{
					array_splice($delete, $i--, 1);
				}
			}
			
			if($result === 0)
			{
				fprintf(STDERR, ' >> NO files deleted (only %d errors occured)' . PHP_EOL, $errors);
				exit(3);
			}
			else
			{
				printf(' >> Operation deleted %d files, with' . ($errors === 0 ? 'out' : ' %d') . ' errors:' . PHP_EOL . PHP_EOL, $result, $errors);
			}
			
			$len = count($delete);
			
			for($i = 0; $i < $len; ++$i)
			{
				printf('    ' . $delete[$i] . PHP_EOL);
			}
			
			printf(PHP_EOL);

			//
			if($errors === 0)
			{
				exit(0);
			}
			
			exit(4);
		}
		
		function remove($_index = null)
		{
			//
			$list = get_hosts($_index);
			
			if($list === null)
			{
				fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
				exit(1);
			}

			$delete = array();
			$index = 0;
			$h = 0;
			$c = count($list);

			foreach($list as $host => $type)
			{
				++$h;
				$i = 0;
				$p = $orig = get_state('path');

				if(($type & COUNTER_VALUE) && is_writable($p = \kekse\join_path($orig, '~' . $host)))
				{
					$delete[$index++] = $p;
					++$i;
				}

				if(($type & COUNTER_DIR) && is_writable($p = \kekse\join_path($orig, '+' . $host)))
				{
					$delete[$index++] = $p;
					++$i;
				}

				if(($type & COUNTER_FILE) && is_writable($p = \kekse\join_path($orig, '-' . $host)))
				{
					$delete[$index++] = $p;
					++$i;
				}

				if(($type & COUNTER_CONFIG) && is_writable($p = \kekse\join_path($orig, '%' . $host)))
				{
					$delete[$index++] = $p;
					++$i;
				}

				if($i === 0)
				{
					--$h;
					unset($list[$host]);
				}
			}

			if($index === 0)
			{
				printf(' >> No files (of %d hosts) found.' . PHP_EOL, $c);
				exit(0);
			}
			else
			{
				printf(' >> Found %d files of %d hosts requested, for these %d hosts left:' . PHP_EOL . PHP_EOL, $index, $c, $h);

				$len = $maxLen = 0;
				$keys = array_keys($list);
				$keysLen = count($keys);

				for($i = 0; $i < $keysLen; ++$i)
				{
					if(($len = strlen($keys[$i])) > $maxLen)
					{
						$maxLen = $len;
					}
				}

				$format = '    %' . $maxLen . 's    %s' . PHP_EOL;

				foreach($list as $host => $type)
				{
					$types = '';

					if($type & COUNTER_VALUE)
					{
						$types .= '~ ';
					}
					else
					{
						$types .= '  ';
					}

					if($type & COUNTER_DIR)
					{
						$types .= '+ ';
					}
					else
					{
						$types .= '  ';
					}

					if($type & COUNTER_FILE)
					{
						$types .= '- ';
					}
					else
					{
						$types .= '  ';
					}

					if($type & COUNTER_CONFIG)
					{
						$types .= '% ';
					}
					else
					{
						$types .= '  ';
					}

					$types = substr($types, 0, -1);
					printf($format, $host, $types);
				}

				printf(PHP_EOL);
			}

			if(!\kekse\prompt('Do you really want to delete ' . $index . ' files [yes/no]? '))
			{
				fprintf(STDERR, ' >> Abort requested.' . PHP_EOL);
				exit(2);
			}

			$ok = 0;
			$err = 0;

			for($i = 0; $i < $index; ++$i)
			{
				if(\kekse\delete($delete[$i], true))
				{
					++$ok;
				}
				else
				{
					++$err;
				}
			}

			if($err === 0)
			{
				printf(' >> Successfully deleted all %d files!' . PHP_EOL, $ok);
				exit(0);
			}
			else if($ok === 0)
			{
				fprintf(STDERR, ' >> NONE of %d files could be deleted! :-/' . PHP_EOL, $index);
				exit(3);
			}

			printf(' >> Successfully deleted %d files.' . PHP_EOL, $ol);
			fprintf(STDERR, ' >> BUT also %d errors occured.' . PHP_EOL, $err);

			//
			exit(0);
		}
		
		//
		function sync($_index = null, $_purge = true)
		{
			return values($_index, true, $_purge);
		}

		function values($_index = null, $_sync = false, $_purge = true)
		{
			//
			$list = get_hosts($_index);

			if($list === null)
			{
				fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
				exit(1);
			}

			$result = array();
			$maxLen = 0;
			$len = 0;

			foreach($list as $key => $value)
			{
				++$len;
				$c = strlen($key);
				
				if($c > $maxLen)
				{
					$maxLen = $c;
				}

				$val = null;
				$real = null;
				$cache = null;
				$config = null;

				if($value & COUNTER_DIR)
				{
					$handle = opendir(\kekse\join_path(get_state('path'), '+' . $key));
					
					if($handle === false)
					{
						$real = null;
					}
					else
					{
						$real = 0;
						
						while($sub = readdir($handle))
						{
							if($sub[0] === '.' || strlen($sub) === 1)
							{
								continue;
							}
							else if(is_file(\kekse\join_path(get_state('path'), '+' . $key, $sub)))
							{
								++$real;
							}
							else if($_purge)
							{
								\kekse\delete(\kekse\join_path(get_state('path'), '+' . $key, $sub), true);
							}
						}
						
						closedir($handle);
					}
				}
				
				if($value & COUNTER_FILE)
				{
					$cache = file_get_contents(\kekse\join_path(get_state('path'), '-' . $key));
					
					if($cache === false)
					{
						$cache = null;
					}
					else
					{
						$cache = (int)$cache;
					}
				}

				if($value & COUNTER_CONFIG)
				{
					$config = count_host_config($key);
				}
				else
				{
					$config = null;
				}

				if($value & COUNTER_VALUE)
				{
					$val = file_get_contents(\kekse\join_path(get_state('path'), '~' . $key));
					
					if($val === false)
					{
						$val = null;
					}
					else
					{
						$val = (int)$val;
					}
				}
				else
				{
					$val = null;
				}

				$result[$key] = [
					($val === null ? null : (int)$val), $cache, $real, $config ];
			}

			printf(PHP_EOL);
			$f = ' %' . $maxLen . 's    %-10s %6s / %-6s    %-6s' . PHP_EOL;
			$sync = array();
			
			foreach($result as $h => $v)
			{
				$c = ($v[3] === null ? '' : ($v[3] === -1 ? '  @' : '% ' . $v[3]));
				printf($f, $h, ($v[0] === null ? '-' : (string)$v[0]), ($v[1] === null ? '-' : (string)$v[1]), ($v[2] === null ? '-' : (string)$v[2]), $c);

				if($v[2] === 0 && (($list[$h] & COUNTER_DIR) || ($list[$h] & COUNTER_FILE)))
				{
					$sync[$h] = 0;
				}
				else if($v[2] !== null && $v[2] !== $v[1])
				{
					$sync[$h] = $v[2];
				}
				else if($v[2] === null && $v[1] !== null)
				{
					$sync[$h] = 0;
				}
			}
			
			printf(PHP_EOL);
			$s = count($sync);

			if(!$_sync)
			{
				exit(0);
			}
			else if($s === 0)
			{
				fprintf(STDERR, ' >> No hosts to synchronize.' . PHP_EOL);
				exit(0);
			}

			if(!\kekse\prompt('Do you want to synchronize ' . count($sync) . ' hosts now [yes/no]? '))
			{
				fprintf(STDERR, ' >> Good, aborting sync.' . PHP_EOL);
				exit(2);
			}

			$tot = 0;
			$chg = 0;
			$del = 0;
			$err = 0;
			$p;

			foreach($sync as $host => $val)
			{
				if($val === 0)
				{
					if(is_dir($p = \kekse\join_path(get_state('path'), '+' . $host)))
					{
						if(\kekse\delete($p, true))
						{
							++$del;
						}
						else
						{
							++$err;
						}
					}

					if(is_file($p = \kekse\join_path(get_state('path'), '-' . $host)))
					{
						if(\kekse\delete($p, false))
						{
							++$del;
						}
						else
						{
							++$err;
						}
					}
				}
				else if(file_put_contents(\kekse\join_path(get_state('path'), '-' . $host), (string)$val))
				{
					++$chg;
				}
				else
				{
					++$err;
				}

				++$tot;
			}

			if($tot > 0)
			{
				printf(PHP_EOL . ' >> Synchronization of %d hosts:' . PHP_EOL, $tot);

				if($chg > 0)
				{
					printf('    Changed %d cache values' . PHP_EOL, $chg);
				}

				if($del > 0)
				{
					printf('    Deleted %d cache items' . PHP_EOL, $del);
				}

				if($err > 0)
				{
					fprintf(STDERR, '     ' . (($chg > 0 || $del > 0) ? 'But ' : '') . '%d errors occured. :-/' . PHP_EOL, $err);
				}
			}
			
			//
			exit(0);
		}

		function unlog($_index = null)
		{
			error_reporting(0);

			if(! file_exists(get_state('log')))
			{
				fprintf(STDERR, ' >> There is no \'%s\' which could be deleted. .. that\'s good for you. :)~' . PHP_EOL, basename(get_state('log')));
				exit(1);
			}
			else if(!is_file(get_state('log')))
			{
				fprintf(STDERR, ' >> The \'%s\' is not a regular file. Please replace/remove it asap!' . PHP_EOL, get_state('log'));
			}

			if(!\kekse\prompt('Do you really want to delete the file \'' . basename(get_state('log')) . '\' [yes/no]? '))
			{
				fprintf(STDERR, ' >> Log file deletion aborted (by request).' . PHP_EOL);
				exit(2);
			}
			else if(\kekse\delete(get_state('log'), false) === false)
			{
				fprintf(STDERR, ' >> The \'%s\' couldn\'t be deleted!!' . PHP_EOL, basename(get_state('log')));

				if(! is_file(get_state('log')))
				{
					fprintf(STDERR, ' >> I think it\'s not a regular file, could this be the reason why?' . PHP_EOL);
				}

				exit(2);
			}
			else
			{
				printf(' >> The \'%s\' is no longer.. :-)' . PHP_EOL, basename(get_state('log')));
			}

			exit(0);
		}

		function errors($_index = null)
		{
			if(! file_exists(get_state('log')))
			{
				printf(' >> No errors logged! :-D' . PHP_EOL);
				exit(0);
			}
			else if(!is_file(get_state('log')))
			{
				fprintf(STDERR, ' >> \'%s\' is not a file! Please delete asap!!' . PHP_EOL, basename(get_state('log')));
				exit(1);
			}
			else if(!is_readable(get_state('log')))
			{
				fprintf(STDERR, ' >> Log file \'%s\' is not readable! Please correct this asap!!' . PHP_EOL, basename(get_state('log')));
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

			$result = count_lines(get_state('log'));

			if($result < 0)
			{
				$result = 0;
			}

			printf(' >> There are %d error log lines in \'%s\'..' . PHP_EOL, $result, basename(get_state('log')));
			exit(0);
		}

		//
		if(KEKSE_ARGC > 1) for($i = 1; $i < KEKSE_ARGC; ++$i)
		{
			$item = KEKSE_ARGV[$i];
			$len = strlen($item);
			
			if($item[0] !== '-' || $len < 2 || $len > KEKSE_LIMIT)
			{
				continue;
			}
			else if($len === 2) switch($item)
			{
				case '-?':
					help($i);
					break;
				case '-V':
					info($i, true, false);
					break;
				case '-C':
					info($i, false, true);
					break;
				case '-c':
					check($i);
					break;
				case '-v':
					values($i);
					break;
				case '-s':
					sync($i);
					break;
				case '-l':
					clean($i);
					break;
				case '-p':
					purge($i);
					break;
				case '-z':
					sanitize($i);
					break;
				case '-r':
					remove($i);
					break;
				case '-t':
					set($i);
					break;
				case '-f':
					fonts($i);
					break;
				case '-y':
					types($i);
					break;
				case '-h':
					hashes($i);
					break;
				case '-e':
					errors($i);
					break;
				case '-u':
					unlog($i);
					break;
			}
			else switch($item)
			{
				case '--help':
					help($i);
					break;
				case '--version':
					info($i, true, false);
					break;
				case '--copyright':
					info($i, false, true);
					break;
				case '--check':
					check($i);
					break;
				case '--values':
					values($i);
					break;
				case '--sync':
					sync($i);
					break;
				case '--clean':
					clean($i);
					break;
				case '--purge':
					purge($i);
					break;
				case '--sanitize':
					sanitize($i);
					break;
				case '--remove':
					remove($i);
					break;
				case '--set':
					set($i);
					break;
				case '--fonts':
					fonts($i);
					break;
				case '--types':
					types($i);
					break;
				case '--hashes':
					hashes($i);
					break;
				case '--errors':
					errors($i);
					break;
				case '--unlog':
					unlog($i);
					break;
			}
		}
		
		//
		printf(' >> Call with `--help/-?` to see a list of available parameters.' . PHP_EOL);
		values(0);
		exit();
	}

	//
	if(KEKSE_CLI)
	{
		if(!get_config('raw') || KEKSE_ARGC > 1)
		{
			return cli();
		}
		else if((!is_string($_host) || empty($_host)) && (!is_string(get_config('override')) || empty(get_config('override'))))
		{
			error('Invalid $_host (needs to be defined in CLI mode)');
		}
		else
		{
			set_state('address', null);
		}
	}
	else
	{
		set_state('address', \kekse\secure_host($_SERVER['REMOTE_ADDR']));
	}

	//
	function get_host($_host = null, $_die = true)
	{
		//
		$result = null;
		$overridden = null;

		//
		if(is_string($_host) && !empty($_host))
		{
			$result = $_host;
			$overridden = true;
		}
		else if(is_string(get_config('override')) && !empty(get_config('override')))
		{
			$result = get_config('override');
			$overridden = true;
		}
		else if(is_string($result = \kekse\get_param('override', false)) && !empty($result))
		{
			if(! get_config('override'))
			{
				$result = null;

				if($_die)
				{
					error('You can\'t define \'?override\' without \'OVERRIDE\' enabled');
				}
			}
			else
			{
				$overridden = true;
			}
		}
		else if(! empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'][0] !== ':')
		{
			$result = $_SERVER['HTTP_HOST'];
			$overridden = false;
		}
		else if(! empty($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'][0] !== ':')
		{
			$result = $_SERVER['SERVER_NAME'];
			$overridden = false;
		}
		else if($_die)
		{
			error('No server host/name applicable');
		}
		
		//
		if(!is_bool($overridden))
		{
			$overridden = false;
		}
		
		set_state('overridden', $overridden);

		//
		if($result !== null)
		{
			$result = \kekse\secure_host(remove_port($result, $_die));

			if($result === null && $_die)
			{
				error('Invalid host');
			}
		}
		
		return $result;
	}

	function remove_port($_host)
	{
		$result = null;
		$port = null;
		
		if(empty($_SERVER['SERVER_PORT']))
		{
			return $_host;
		}
		else
		{
			$port = (string)$_SERVER['SERVER_PORT'];
		}
		
		if($port !== null && \kekse\ends_with($_host, (':' . $port)))
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
	set_state('host', get_host($_host));
	$r = make_config(get_host($_host));

	//
	if(!get_state('test'))
	{
		//
		set_state('cookie', \kekse\limit(hash(get_config('hash'), get_state('host'))));

		//
		set_state('value', \kekse\join_path(get_state('path'), '~' . get_state('host')));
		set_state('dir', \kekse\join_path(get_state('path'), '+' . get_state('host')));
		set_state('file', \kekse\join_path(get_state('path'), '-' . get_state('host')));

		//
		if(get_state('address') === null)
		{
			set_state('ip', null);
			set_state('remote', null);
		}
		else
		{
			if(get_config('privacy'))
			{
				set_state('remote', \kekse\limit(hash(get_config('hash'), get_state('address'))));
			}
			else
			{
				set_state('remote', \kekse\secure_host(get_state('address')));
			}

			set_state('ip', \kekse\join_path(get_state('dir'), \kekse\secure_path(get_state('remote'))));
		}

		//
		function check_auto()
		{
			if(get_config('auto') === null)
			{
				error(get_config('none'));
			}
			else if(get_config('auto') === true && !get_state('overridden'))
			{
				return;
			}
			else if(!is_file(get_state('value')))
			{
				if(is_string($_host) && !empty($_host))
				{
					return;
				}
				else if(is_string(get_config('override')) && !empty(get_config('override')))
				{
					return;
				}
				else if(get_state('overridden'))
				{
					error(get_config('none'));
				}
				else if(get_config('auto') === false)
				{
					error(get_config('none'));
				}
				else if(is_int(get_config('auto')))
				{
					$count = 0;
					$handle = opendir(get_state('path'));

					if($handle === false)
					{
						log_error('Can\'t opendir()', 'check_path', get_state('path'), false);
						error(get_config('none'));
					}

					while($sub = readdir($handle))
					{
						if($sub[0] === '~')
						{
							++$count;
						}
					}

					closedir($handle);

					if($count >= get_config('auto'))
					{
						error(get_config('none'));
					}
				}
				else
				{
					log_error('Invalid \'AUTO\' setting', 'check_path', '', false);
					error('Invalid \'AUTO\' setting');
				}
			}
			else if(file_exists(get_state('value')) && !is_writable(get_state('value')))
			{
				log_error('File is not writable', 'check_path', get_state('value'));
				error('File is not writable');
			}
		}

		//
		function with_server()
		{
			$conf = get_config('threshold');
			
			if($conf === null || $conf <= 0)
			{
				return false;
			}

			return get_config('server');
		}

		function with_client()
		{
			$conf = get_config('threshold');
			
			if($conf === null || $conf <= 0)
			{
				return false;
			}

			return get_config('client');
		}

		//
		function test()
		{
			$result = true;

			if(with_client() && !get_state('overridden'))
			{
				$result = test_cookie();
			}

			if($result && with_server())
			{
				$result = test_file();
			}

			return $result;
		}

		function test_file()
		{
			//
			if(get_state('ip') === null)
			{
				return true;
			}
			else if(get_config('threshold') === null)
			{
				return true;
			}
			else if(file_exists(get_state('ip')))
			{
				if(!is_file(get_state('ip')))
				{
					log_error('Is not a regular file', 'test_file', get_state('ip'));
					error('Is not a regular file');
				}
				else if(!is_readable(get_state('ip')))
				{
					log_error('File can\'t be read', 'test_file', get_state('ip'));
					error('File can\'t be read');
				}
				else if(\kekse\timestamp(read_timestamp()) < get_config('threshold'))
				{
					return false;
				}
			}

			return true;
		}

		function test_cookie()
		{
			if(get_config('threshold') === null)
			{
				return true;
			}
			else if(empty($_COOKIE[get_state('cookie')]))
			{
				make_cookie();
			}
			else if(\kekse\timestamp((int)$_COOKIE[get_state('cookie')]) < get_config('threshold'))
			{
				return false;
			}

			return true;
		}

		function make_cookie()
		{
			$threshold = null;
			
			if(($threshold = get_config('threshold')) === null)
			{
				return null;
			}
			else if($threshold <= 0)
			{
				$threshold = 0;
			}
			
			return setcookie(get_state('cookie'), (string)\kekse\timestamp(), array(
				'expires' => (time() + $threshold),
				'path' => '/',
				'samesite' => 'Strict',
				'secure' => false, //!empty($_SERVER['HTTPS']);
				'httponly' => true
			));
		}

		function clean_files()
		{
			//
			if(get_config('clean') === null)
			{
				log_error('Called function, but `CLEAN === null`', 'clean_files', '', false);
				return -1;
			}
			else if(!is_dir(get_state('dir')))
			{
				return init_count(false);
			}

			$handle = opendir(get_state('dir'));
			
			if(!$handle)
			{
				log_error('Can\'t opendir()', 'clean_files', get_state('dir'), false);
				return -1;
			}
			
			$result = 0;
			
			while($sub = readdir($handle))
			{
				if($sub[0] === '.')
				{
					continue;
				}
				else
				{
					$sub = \kekse\join_path(get_state('dir'), $sub);
				}
				
				if(!is_file($sub))
				{
					continue;
				}
				else if(get_config('threshold') !== null && \kekse\timestamp((int)file_get_contents($sub)) < get_config('threshold'))
				{
					++$result;
				}
				else if(! \kekse\delete($sub, false))
				{
					log_error('Unable to delete() outdated file', 'clean_files', $sub, false);
					++$result;
				}
			}
			
			closedir($handle);
			return write_count($result, false);
		}

		function init_count()
		{
			//
			$result = 0;

			if(is_dir(get_state('dir')))
			{
				$handle = opendir(get_state('dir'));

				if($handle === false)
				{
					log_error('Can\'t opendir()', 'init_count', get_state('dir'), false);
					return null;
				}

				while($sub = readdir($handle))
				{
					if($sub[0] === '.')
					{
						continue;
					}
					else if(is_file(\kekse\join_path(get_state('dir'), $sub)))
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

			$written = file_put_contents(get_state('file'), (string)$result);

			if($written === false)
			{
				log_error('Couldn\'t initialize count', 'init_count', get_state('file'), false);
				return null;
			}

			return $result;
		}

		function read_count()
		{
			//
			if(!file_exists(get_state('file')))
			{
				return init_count();
			}
			else if(!is_file(get_state('file')) || !is_writable(get_state('file')))
			{
				log_error('Count file is not a file, or it\'s not writable', 'read_count', get_state('file'), false);
				return 0;
			}

			$result = file_get_contents(get_state('file'));

			if($result === false)
			{
				log_error('Couldn\'t read count value', 'read_count', get_state('file'), false);
				return null;
			}

			return (int)$result;
		}

		function write_count($_value, $_get = true)
		{
			//
			$result = null;

			if(file_exists(get_state('file')))
			{
				if(!is_file(get_state('file')))
				{
					log_error('Count file is not a regular file', 'write_count', get_state('file'), false);
					return null;
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

			$written = file_put_contents(get_state('file'), (string)$_value);

			if($written === false)
			{
				log_error('Unable to write count', 'write_count', get_state('file'), false);
				return null;
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
			//
			if(get_state('ip') === null)
			{
				return 0;
			}
			else if(file_exists(get_state('ip')))
			{
				if(!is_file(get_state('ip')))
				{
					log_error('Is not a file', 'read_timestamp', get_state('ip'), false);
					return 0;
				}
				else if(!is_readable(get_state('ip')))
				{
					log_error('File not readable', 'read_timestamp', get_state('ip'), false);
					return 0;
				}

				$result = file_get_contents(get_state('ip'));

				if($result === false)
				{
					log_error('Unable to read timestamp', 'read_timestamp', get_state('ip'));
					return 0;
				}

				return (int)$result;
			}

			return 0;
		}

		function write_timestamp($_clean = null)
		{
			//
			if(!is_bool($_clean))
			{
				$_clean = (get_config('clean') !== null);
			}
			
			//
			if(get_state('ip') === null)
			{
				return 0;
			}
			else if(!file_exists(get_state('dir')))
			{
				if(!mkdir(get_state('dir'), 01777, true))
				{
					log_error('Can\'t mkdir()', 'write_timestamp', get_state('dir'), false);
					return 0;
				}
			}
			else if(!is_dir(get_state('dir')))
			{
				log_error('Path isn\'t a directory', 'write_timestamp', get_state('dir'), false);
				return 0;
			}
			
			$existed = file_exists(get_state('ip'));

			if($existed)
			{
				if(!is_file(get_state('ip')))
				{
					log_error('It\'s no regular file', 'write_timestamp', get_state('ip'), false);
					return 0;
				}
				else if(!is_writable(get_state('ip')))
				{
					log_error('Not a writable file', 'write_timestamp', get_state('ip'), false);
					return 0;
				}
			}
			else if(read_count() > get_config('limit'))
			{
				if($_clean)
				{
					if(clean_files() > get_config('limit'))
					{
						log_error('LIMIT (' . get_config('limit') . ') exceeded, even after clean_files()', 'write_timestamp', get_state('ip'), false);
						return 0;
					}
				}
				else
				{
					log_error('LIMIT (' . get_config('limit') . ') exceeded (and no clean_files() called)', 'write_timestamp', get_state('ip'), false);
					return 0;
				}
			}

			$result = file_put_contents(get_state('ip'), (string)\kekse\timestamp());

			if($result === false)
			{
				log_error('Unable to write timestamp', 'write_timestamp', get_state('ip'), false);
				return 0;
			}
			else if(!$existed)
			{
				increase_count();
			}

			return $result;
		}

		function read_value()
		{
			//
			if(file_exists(get_state('value')))
			{
				if(!is_file(get_state('value')))
				{
					log_error('It\'s not a regular file', 'read_value', get_state('value'));
					error('It\'s not a regular file');
				}
				else if(!is_readable(get_state('value')))
				{
					log_error('File is not readable', 'read_value', get_state('value'));
					error('File is not readable');
				}

				$result = file_get_contents(get_state('value'));

				if($result === false)
				{
					log_error('Unable to read value', 'read_value', get_state('value'));
					error('Unable to read value');
				}

				return (int)$result;
			}
			else if((file_put_contents(get_state('value'), '0')) === false)
			{
				touch(get_state('value'));
			}

			return 0;
		}

		function write_value($_value)
		{
			//
			if(!is_int($_value) || $_value < 0)
			{
				log_error('Value was no integer, or it was below zero', 'write_value', '', true);
				error('Value was no integer, or it was below zero');
			}

			if(file_exists(get_state('value')))
			{
				if(!is_file(get_state('value')))
				{
					log_error('Not a regular file', 'write_value', get_state('value'), true);
					error('Not a regular file');
				}
				else if(!is_writable(get_state('value')))
				{
					log_error('File is not writable', 'write_value', get_state('value'), true);
					error('File is not writable');
				}
			}

			$result = file_put_contents(get_state('value'), (string)$_value);

			if($result === false)
			{
				log_error('Unable to write value', 'write_value', get_state('value'), true);
				error('Unable to write value');
			}

			return $result;
		}
		
		//
		if(get_config('auto') === null)
		{
			error(get_config('none'));
		}
		else
		{
			check_auto();
		}
	}

	//
	if(get_config('drawing') && !get_config('raw'))
	{
		//
		function draw($_text, $_zero = null)
		{
			//
			if(!is_bool($_zero))
			{
				$_zero = !!get_state('zero');
			}
			
			//
			function draw_error($_reason)
			{
				return error($_reason);
			}

			function get_drawing_type()
			{
				//
				$result = \kekse\get_param('type', false);

				if(!is_string($result))
				{
					$result = get_config('type');
				}

				$types = imagetypes();

				switch($result = strtolower($result))
				{
					case 'png':
						if(! ($types & IMG_PNG))
						{
							draw_error('\'?type\' is not supported');
							return null;
						}
						break;
					case 'jpg':
						if(! ($types & IMG_JPG))
						{
							draw_error('\'?type\' is not supported');
							return null;
						}
						break;
					default:
						draw_error('\'?type\' is not supported');
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
					if(get_state('sent'))
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
							if($sent = send_header('image/png'))
							{
								imagepng($image);
							}
							break;
						case 'jpg':
							if($sent = send_header('image/jpeg'))
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
			function get_font($_name)
			{
				if(!is_string($_name) || empty($_name))
				{
					return null;
				}
				else if(substr($_name, -4) !== '.ttf')
				{
					$_name .= '.ttf';
				}
				else if(get_state('fonts') === null)
				{
					return null;
				}
				
				$result = \kekse\join_path(get_state('fonts'), $_name);

				if(is_file($result) && is_readable($result))
				{
					return $result;
				}
				
				return null;
			}

			function get_drawing_options($_die = false)
			{
				//
				$result = array();

				//
				if(($result['type'] = get_drawing_type()) === null)
				{
					return null;
				}
				else
				{
					$result['px'] = \kekse\get_param('px', true, false);
					$result['prefer'] = \kekse\get_param('prefer', null);
					$result['font'] = \kekse\get_param('font', false);
					$result['fg'] = \kekse\get_param('fg', false);
					$result['bg'] = \kekse\get_param('bg', false);
					$result['h'] = \kekse\get_param('h', true, false);
					$result['v'] = \kekse\get_param('v', true, false);
					$result['x'] = \kekse\get_param('x', true, false);
					$result['y'] = \kekse\get_param('y', true, false);
					$result['aa'] = \kekse\get_param('aa', null);
				}

				//
				if($result['px'] === null)
				{
					$result['px'] = get_config('px');
				}
				else if($result['px'] > 512 || $result['px'] < 4)
				{
					if($_die)
					{
						draw_error('\'?px\' exceeds limit (4..512)');
						return null;
					}
					
					$result['px'] = get_config('px');
				}
				
				if($result['prefer'] === null)
				{
					$result['prefer'] = get_config('prefer');
				}

				if($result['h'] === null)
				{
					$result['h'] = get_config('h');
				}
				else if($result['h'] > 512 || $result['h'] < -512)
				{
					if($_die)
					{
						draw_error('\'?h\' exceeds limit');
						return null;
					}
					
					$result['h'] = get_config('h');
				}

				if($result['v'] === null)
				{
					$result['v'] = get_config('v');
				}
				else if($result['v'] > 512 || $result['v'] < -512)
				{
					if($_die)
					{
						draw_error('\'?v\' exceeds limit');
						return null;
					}
					
					$result['v'] = get_config('v');
				}

				if($result['font'] === null)
				{
					$result['font'] = get_config('font');
				}

				if(($result['font'] = get_font($result['font'])) === null)
				{
					if($_die)
					{
						draw_error('\'?font\' is not available');
						return null;
					}
					
					if(($result['font'] = get_font(get_config('font'))) === null)
					{
						draw_error('Default font is not available (used as fallback)');
						return null;
					}
				}

				if($result['fg'] === null)
				{
					$result['fg'] = get_config('fg');
				}

				if($result['bg'] === null)
				{
					$result['bg'] = get_config('bg');
				}

				$result['fg'] = get_color($result['fg']);

				if($result['fg'] === null)
				{
					if($_die)
					{
						draw_error('\'?fg\' is no valid color');
						return null;
					}
					
					if(($result['fg'] = get_color(get_config('fg'))) === null)
					{
						draw_error('Default FG color is not valid (used as fallback)');
						return null;
					}
				}

				$result['bg'] = get_color($result['bg']);

				if($result['bg'] === null)
				{
					if($_die)
					{
						draw_error('\'?bg\' is no valid color');
						return null;
					}
					
					if(($result['bg'] = get_color(get_config('bg'))) === null)
					{
						draw_error('Default BG color is not valid (used as fallback)');
						return null;
					}
				}

				if($result['x'] === null)
				{
					$result['x'] = get_config('x');
				}
				else if($result['x'] > 512 || $result['x'] < -512)
				{
					if($_die)
					{
						draw_error('\'?x\' exceeds limit');
						return null;
					}
					
					$result['x'] = get_config('x');
				}

				if($result['y'] === null)
				{
					$result['y'] = get_config('y');
				}
				else if($result['x'] > 512 || $result['y'] < -512)
				{
					if($_die)
					{
						draw_error('\'?y\' exceeds limit');
						return null;
					}
					
					$result['y'] = get_config('y');
				}
				
				if(!is_bool($result['aa']))
				{
					$result['aa'] = get_config('aa');
				}

				return $result;
			}

			//
			function get_color_fix(&$_array, $_fix_gd = true)
			{
				$result = &$_array;
				$len = count($result);

				if($len === 3)
				{
					$result[3] = 1.0;
				}
				else if($len === 4)
				{
					if(is_int($result[3]))
					{
						if($result[3] < 0)
						{
							return null;
						}
						else if($result[3] <= 1)
						{
							$result[3] = (float)$result[3];
						}
						else if($result[3] <= 255)
						{
							$result[3] = (float)($result[3] / 255);
						}
					}
				}

				for($i = 0; $i < 4; ++$i)
				{
					if(is_int($result[$i]))
					{
						if($result[$i] < 0)
						{
							$result = null;
						}
						else if($result[$i] > 255)
						{
							$result = null;
						}
					}
					else if($result[$i] < 0.0)
					{
						$result = null;
					}
					else if($result[$i] > 1.0)
					{
						$result = null;
					}
				}

				if($_fix_gd)
				{
					$result[3] = (int)(127 - ($result[3] * 127));
				}

				return $result;
			}

			function get_color_hex_symbol($_char)
			{
				$res = '';
				$ord = null;
				
				//
				if(($ord = ord($_char)) >= 48 && $ord <= 57)
				{
					$res = $_char;
				}
				else if($ord >= 97 && $ord <= 102)
				{
					$res = $_char;
				}
				else if($ord >= 65 && $ord <= 70)
				{
					$res = chr($ord + 32);
				}
				else
				{
					$res = '';
				}
				
				return $res;
			}
			
			function get_color_hex($_string)
			{
				//
				$_string = \kekse\remove_white_spaces($_string);
				
				//
				if($_string[0] === '#')
				{
					$_string = substr($_string, 1);
				}
				
				//
				$result = array();
				$len = strlen($_string);
				$sub = '';
				
				//
				if($len === 3 || $len === 4) for($i = 0; $i < $len; ++$i)
				{
					if(strlen($sub .= get_color_hex_symbol($_string[$i])) > 0)
					{
						$result[$i] = hexdec($sub . $sub);
						
						if($result[$i] < 0 || $result[$i] > 255)
						{
							return null;
						}
						else
						{
							$sub = '';
						}
					}
				}
				else if($len === 6 || $len === 8) for($i = 0, $j = 0; $i < $len; ++$i)
				{
					if(strlen($sub .= get_color_hex_symbol($_string[$i])) > 1)
					{
						$result[$j] = hexdec($sub);
						
						if($result[$j] < 0 || $result[$j] > 255)
						{
							return null;
						}
						else
						{
							$sub = '';
							++$j;
						}
					}
				}
				else
				{
					return null;
				}
				
				//
				$len = count($result);
				
				if($len === 3)
				{
					$result[3] = 1.0;
				}
				else if($len === 4)
				{
					$result[3] = (float)($result[3] / 255);
				}

				//
				return $result;
			}
			
			function get_color_array($_string)
			{
				//
				$_string = \kekse\remove_white_spaces($_string) . ',';
				$len = strlen($_string);
				$result = array();
				$comma = false;
				$byte = -1;
				$item = '';

				//
				for($i = 0, $j = 0; $i < $len; ++$i)
				{
					if($_string[$i] === ',')
					{
						if(strlen($item) === 0)
						{
							continue;
						}
						else if($comma)
						{
							if(\kekse\ends_with($item, '.'))
							{
								$item .= '0';
							}
							
							if($item[0] === '.')
							{
								$item = '0' . $item;
							}
						}

						if($comma)
						{
							$item = (double)$item;
							
							if($item < 0 || $item > 1)
							{
								return null;
							}
						}
						else
						{
							$item = (int)$item;
							
							if($j === 3)
							{
								if($item < 0)
								{
									return null;
								}
								else if($item <= 1)
								{
									$item = (double)$item;
								}
								else if($item <= 255)
								{
									$item = (double)($item / 255);
								}
								else
								{
									return null;
								}
							}
						}
						
						if($j === 2)
						{
						}
					
						$result[$j++] = $item;
						$item = '';
						$comma = false;
					}
					else if($_string[$i] === '.')
					{
						$comma = true;
						$item .= '.';
					}
					else if(($byte = ord($_string[$i])) >= 48 && $byte <= 57)
					{
						$item .= $_string[$i];
					}
				}

				//
				return $result;
			}

			//
			function is_hex_format($_string)
			{
				$_string = \kekse\remove_white_spaces($_string);
				
				if($_string[0] === '#')
				{
					$_string = substr($_string, 1);
				}
				
				$len = strlen($_string);
				
				switch($len)
				{
					case 3:
					case 4:
					case 6:
					case 8:
						break;
					default:
						return false;
				}
				
				for($i = 0; $i < $len; ++$i)
				{
					if(strlen(get_color_hex_symbol($_string[$i])) === 0)
					{
						return false;
					}
				}

				return true;
			}
			
			function get_color($_string, $_fix_gd = true)
			{
				if(!is_string($_string))
				{
					return null;
				}
				else
				{
					$len = strlen($_string);

					if($len === 0 || $len > KEKSE_LIMIT)
					{
						return null;
					}
				}

				//
				if(substr($_string, 0, 5) === 'rgba(')
				{
					$_string = substr($_string, 5);
				}
				else if(substr($_string, 0, 4) === 'rgb(')
				{
					$_string = substr($_string, 4);
				}

				if($_string[strlen($_string) - 1] === ')')
				{
					$_string = substr($_string, 0, -1);
				}

				$result = null;

				if(is_hex_format($_string))
				{
					$result = get_color_hex($_string);
				}
				else
				{
					$result = get_color_array($_string);
				}

				if($result !== null)
				{
					$result = get_color_fix($result, $_fix_gd);
				}
				
				return $result;
			}
			
			function draw_text($_text, $_font, $_px = null, $_fg = null, $_bg = null, $_h = null, $_v = null, $_x = null, $_y = null, $_aa = null, $_type = null, $_prefer = null)
			{
				//
				if(func_num_args() === 2)
				{
					if(is_array($_font))
					{
						$_prefer = $_font['prefer'];
						$_type = $_font['type'];
						$_aa = $_font['aa'];
						$_y = $_font['y'];
						$_x = $_font['x'];
						$_v = $_font['v'];
						$_h = $_font['h'];
						$_bg = $_font['bg'];
						$_fg = $_font['fg'];
						$_px = $_font['px'];
						$_font = $_font['font'];
					}
					else
					{
						draw_error('Invalid arguments to draw_text()');
						return null;
					}
					
				}

				//
				if(get_state('sent'))
				{
					draw_error('Header already sent (unexpected here)');
					return null;
				}
				
				//
				$imageSize = $_px * 0.75;
				$fontSize = $_px;
				
				//
				$measure = imagettfbbox($fontSize, 0, $_font, $_text);
				
				// just to be sure.. isn't necessary for real, i think..
				$textWidth = [ $measure[4] - $measure[6], $measure[2] - $measure[0] ];
				$textHeight = [ $measure[1] - $measure[7], $measure[3] - $measure[5] ];
				
				if($textWidth[0] === $textWidth[1])
				{
					$textWidth = $textWidth[0];
				}
				else
				{
					die('debug: w');
				}
				
				if($textHeight[0] === $textHeight[1])
				{
					$textHeight = $textHeight[0];
				}
				else
				{
					die('debug: h');
				}
				
				//
				$factor = min($imageSize / $textHeight, 1);
				
				//
				if($_prefer)
				{
					$fontSize *= $factor;
					$textWidth *= $factor;
				}
				else
				{
					$imageSize /= $factor;
				}

				//
				$imageWidth = ($textWidth + $_h * 2);
				$imageHeight = $imageSize / 0.75;

				//
				if($imageWidth < 1)
				{
					$imageWidth = 1;
				}

				if($imageHeight < 1)
				{
					$imageHeight = 1;
				}
				
				//
				$MOVE = 2;
				$y;
				$x = (int)(-$MOVE + $_x);
				
				if($_prefer)
				{
					$y = (int)($MOVE+((($imageHeight + $textHeight) / 2) * $factor + $_y));
				}
				else
				{
					$y = (int)($MOVE+((($imageHeight + $textHeight * $factor) / 2) + $_y));
				}
				
				//
				$imageWidth = (int)($textWidth + 2 * $_h);
				$imageHeight = (int)($imageHeight + 2 * $_v);

				//
				$image = imagecreatetruecolor($imageWidth, $imageHeight);
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
				imagettftext($image, $fontSize, 0, $x, $y, $_fg, $_font, $_text);

				//
				$sent = null;
				
				switch(strtolower($_type))
				{
					case 'png':
						if($sent = send_header('image/png'))
						{
							imagepng($image);
						}
						break;
					case 'jpg':
						if($sent = send_header('image/jpeg'))
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
			return draw_text($_text, get_drawing_options());
		}
	}
	
	function get_radix($_value = null)
	{
		if(is_int($_value) && $_value >= 2 && $_value <= 36)
		{
			return $_value;
		}
		
		$result = \kekse\get_param('radix', true, false);

		if(is_int($result) && $result >= 2 && $result <= 36)
		{
			return $result;
		}
		else if(is_int($result = get_config('radix')) && $result >= 2 && $result <= 36)
		{
			return $result;
		}
		
		return 10;
	}

	//
	$real = (get_state('test') ? rand() : read_value());

	if(get_state('done') || ($_read_only && get_config('raw')))
	{
		return $real;
	}

	$value = $real;
	
	//
	if(!(get_state('ro') || get_state('test') || $_read_only || get_state('done')))
	{
		if(test())
		{
			write_value(++$value);
		}

		if(with_client() && !get_state('overridden') && !KEKSE_CLI)
		{
			make_cookie();
		}
	}

	//
	if(!get_config('raw'))
	{
		$hide = get_config('hide');
		
		if(is_string($hide) && !get_state('test'))
		{
			$value = $hide;
		}
		else
		{
			if($hide === true && !get_state('test'))
			{
				$value = (string)rand();
			}
			else
			{
				$value = (string)$value;
			}
			
			$radix = get_radix();
			
			if($radix !== 10)
			{
				$value = base_convert($value, 10, $radix);
			}
		}

		if(strlen($value) > 64)
		{
			log_error('$value length exceeds limit (' . strlen($value) . ' chars)', '', '', false);
			$value = 'e';
		}

		//
		if(get_state('draw') || get_state('zero'))
		{
			draw($value, get_state('zero'));
		}
		else
		{
			send_header();
			header('Content-Length: ' . strlen($value));
			echo $value;
		}

		//
		set_state('fin', true);
	}

	//
	if(!(get_state('ro') || get_state('test') || $_read_only || get_state('done')) && with_server())
	{
		//
		write_timestamp();

		//
		if(get_config('clean') === true)
		{
			clean_files();
		}
		else if(is_int(get_config('clean')))
		{
			$count = read_count();

			if($count >= get_config('clean'))
			{
				clean_files();
			}
		}
	}

	//
	set_state('done', true);
	
	//
	return $real;
}

//
if(!get_config('raw') || (KEKSE_CLI && KEKSE_ARGC > 1))
{
	counter();
}

?>
