<?php

//
namespace kekse\counter;

//
define('KEKSE_COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');
define('COUNTER_HELP', 'https://github.com/kekse1/count.php/');
define('COUNTER_VERSION', '3.6.1');

//
define('KEKSE_LIMIT', 224); //reasonable maximum length for *some* strings.. e.g. path components (theoretically up to 255 chars @ unices..);
define('KEKSE_STRICT', true); //should stay (true)! don't change unless you know what you're doing..
define('KEKSE_ANSI', true); //colors, styles, etc.. @ CLI! ^_^

//
const DEFAULTS = array(
	'path' => 'count/',
	'log' => 'count.log',
	'threshold' => 7200,
	'auto' => 32,//false,
	'hide' => false,//true,
	'client' => true,//false,
	'server' => true,//false,
	'drawing' => true,
	'override' => false,//true,
	'content' => 'text/plain;charset=UTF-8',
	'radix' => 10,//16,
	'clean' => true,
	'limit' => 32768,
	'fonts' => 'fonts/',
	'font' => 'IntelOneMono',//'Candara',
	'size' => 64,
	'unit' => 'px',
	'fg' => '0,0,0,1',//'120,130,40',
	'bg' => '255,255,255,0',
	'angle' => 0,//20,
	'x' => 0,
	'y' => 0,
	'h' => 0,//64,
	'v' => 0,//0,
	'aa' => true,
	'type' => 'png',
	'privacy' => false,
	'hash' => 'sha3-256',
	'error' => '-',
	'none' => '/',
	'raw' => false,
	'modules' => 'modules/'
);

//
//maybe rather dynamic, in reading out the `modules` directory!?? //(much) TODO!/
const MODULES = array(
	'statistics',
	'notifications',
	'events'
);

//
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
const CONFIG_VECTOR = array(
	'path' => array('types' => [ 'string' ], 'static' => true, 'min' => 1, 'test' => true),
	'log' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'threshold' => array('types' => [ 'integer', 'NULL' ], 'min' => 0),
	'auto' => array('types' => [ 'boolean', 'integer', 'NULL' ], 'static' => true, 'min' => 0),
	'hide' => array('types' => [ 'boolean', 'string' ]),
	'client' => array('types' => [ 'boolean' ]),
	'server' => array('types' => [ 'boolean' ]),
	'drawing' => array('types' => [ 'boolean' ]),
	'override' => array('types' => [ 'boolean', 'string' ], 'static' => true, 'min' => 1),
	'content' => array('types' => [ 'string' ], 'min' => 1),
	'radix' => array('types' => [ 'integer' ], 'min' => 2, 'max' => 36),
	'clean' => array('types' => [ 'boolean', 'NULL', 'integer' ], 'min' => 0),
	'limit' => array('types' => [ 'integer' ], 'min' => 0, 'max' => 16777216),
	'fonts' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'font' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'size' => array('types' => [ 'integer', 'string' ], 'min' => 3, 'max' => 512, 'test' => true),
	'unit' => array('types' => [ 'string' ], 'min' => 2, 'max' => 2, 'test' => true),
	'fg' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'bg' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'angle' => array('types' => [ 'integer', 'string' ], 'test' => true),
	'x' => array('types' => [ 'integer' ], 'min' => -512, 'max' => 512),
	'y' => array('types' => [ 'integer' ], 'min' => -512, 'max' => 512),
	'h' => array('types' => [ 'integer' ], 'min' => -512, 'max' => 512),
	'v' => array('types' => [ 'integer' ], 'min' => -512, 'max' => 512),
	'aa' => array('types' => [ 'boolean' ]),
	'type' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'privacy' => array('types' => [ 'boolean' ]),
	'hash' => array('types' => [ 'string' ], 'static' => true, 'min' => 1, 'test' => true),
	'error' => array('types' => [ 'string', 'NULL' ]),
	'none' => array('types' => [ 'string' ]),
	'raw' => array('types' => [ 'boolean' ], 'static' => true, 'test' => null),
	'modules' => array('types' => [ 'string', 'NULL' ], 'test' => true)
);

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
	fprintf(STDERR, ' >> WARNING: CLI mode active, but missing \'argc\' and/or \'argv[]\'..! :-/' . PHP_EOL);
	exit(127);
}

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
	else if(array_key_exists($_key = strtolower($_key), $STATE))
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
namespace kekse;
$console_condition = (KEKSE_CLI || \kekse\counter\get_config('raw'));

function prompt(... $_args)
{
	global $console_condition;
	
	if($console_condition)
	{
		return \kekse\console\prompt(... $_args);
	}
	
	return false;
}

function log(... $_args)
{
	global $console_condition;

	if($console_condition)
	{
		return \kekse\console\log(... $_args);
	}

	return false;
}

function info(... $_args)
{
	global $console_condition;
	
	if($console_condition)
	{
		return \kekse\console\info(... $_args);
	}

	return false;
}

function error(... $_args)
{
	global $console_condition;

	if($console_condition)
	{
		return \kekse\console\error(... $_args);
	}

	return false;
}

function warn(... $_args)
{
	global $console_condition;
	
	if($console_condition)
	{
		return \kekse\console\warn(... $_args);
	}
	
	return false;
}

function debug(... $_args)
{
	global $console_condition;
	
	if($console_condition)
	{
		return \kekse\console\debug(... $_args);
	}
	
	return false;
}

namespace kekse\console;

if($console_condition)
{
	//
	define('KEKSE_ANSI_ESC', chr(27));
	define('KEKSE_ANSI_RESET', '[0m');
	define('KEKSE_ANSI_BOLD', '[1m');
	define('KEKSE_ANSI_RED', '[31m');
	define('KEKSE_ANSI_GREEN', '[32m');
	define('KEKSE_ANSI_YELLOW', '[33m');
	define('KEKSE_ANSI_BLUE', '[34m');
	define('KEKSE_ANSI_MAGENTA', '[35m');

	//
	//TODO/the others here, too!?
	//
	function bold($_string)
	{
		if(!KEKSE_ANSI)
		{
			return $_string;
		}

		return (KEKSE_ANSI_ESC . KEKSE_ANSI_BOLD . $_string . KEKSE_ANSI_ESC . KEKSE_ANSI_RESET);
	}

	//
	function insert_prefix($_string, $_color = null)
	{
		if(! is_string($_string))
		{
			return null;
		}
		else if(KEKSE_ANSI)
		{
			$seq = (KEKSE_ANSI_ESC . KEKSE_ANSI_BOLD);

			if(is_string($_color)) switch(strtolower($_color))
			{
				case 'red':
					$seq .= (KEKSE_ANSI_ESC . KEKSE_ANSI_RED);
					break;
				case 'green':
					$seq .= (KEKSE_ANSI_ESC . KEKSE_ANSI_GREEN);
					break;
				case 'yellow':
					$seq .= (KEKSE_ANSI_ESC . KEKSE_ANSI_YELLOW);
					break;
				case 'blue':
					$seq .= (KEKSE_ANSI_ESC . KEKSE_ANSI_BLUE);
					break;
				case 'magenta':
					$seq .= (KEKSE_ANSI_ESC . KEKSE_ANSI_MAGENTA);
					break;
			}

			return ($seq . ' >> ' . (KEKSE_ANSI_ESC . KEKSE_ANSI_RESET) . $_string);
		}

		return (' >> ' . $_string);
	}

	//
	function prompt($_string, $_return = false, $_repeat = true)
	{
		//
		$_string = insert_prefix($_string, 'yellow');
		
		//
		$confirm = function() use (&$_string, &$_return) {
			fprintf(STDERR, $_string);
			$res = readline();
			
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

		//		
		$result = null;
		
		while($result === null)
		{
			$result = $confirm();
			
			if($result !== null)
			{
				break;
			}
		}
		
		return $result;
	}
	
	//
	function console_output(&$_format, &$_args)
	{
		$eol = 1;
		
		if($_format === null)
		{
			$eol = 0;
			$_format = array_shift($_args);
		}
		else if(is_bool($_format))
		{
			$eol = ($_format ? 1 : 0);
			$_format = array_shift($_args);
		}
		else if(is_int($_format))
		{
			$eol = $_format;
			$_format = array_shift($_args);
		}
		
		return [ $eol, $_format, $_args ];
	}

	//
	function log($_format = '', ... $_args)
	{
		$result = console_output($_format, $_args);
		
		$eol = $result[0];
		$_format = $result[1];
		$_args = $result[2];
		$result = '';
		
		if(is_string($_format) && !empty($_format))
		{
			$result = insert_prefix(sprintf($_format, ... $_args), null);
		}
		
		printf($result);
		
		while(--$eol >= 0)
		{
			printf(PHP_EOL);
		}
		
		return $result;
	}

	function info($_format = '', ... $_args)
	{
		$result = console_output($_format, $_args);
		
		$eol = $result[0];
		$_format = $result[1];
		$_args = $result[2];
		$result = '';
		
		if(is_string($_format) && !empty($_format))
		{
			$result = insert_prefix(sprintf($_format, ... $_args), 'green');
		}
		
		printf($result);
		
		while(--$eol >= 0)
		{
			printf(PHP_EOL);
		}
		
		return $result;
	}

	function warn($_format = '', ... $_args)
	{
		$result = console_output($_format, $_args);
		
		$eol = $result[0];
		$_format = $result[1];
		$_args = $result[2];
		$result = '';
		
		if(is_string($_format) && !empty($_format))
		{
			$result = insert_prefix(sprintf($_format, ... $_args), 'magenta');
		}
		
		fprintf(STDERR, $result);
		
		while(--$eol >= 0)
		{
			fprintf(STDERR, PHP_EOL);
		}
		
		return $result;
	}

	function error($_format = '', ... $_args)
	{
		$result = console_output($_format, $_args);
		
		$eol = $result[0];
		$_format = $result[1];
		$_args = $result[2];
		$result = '';
		
		if(is_string($_format) && !empty($_format))
		{
			$result = insert_prefix(sprintf($_format, ... $_args), 'red');
		}
		
		fprintf(STDERR, $result);
		
		while(--$eol >= 0)
		{
			fprintf(STDERR, PHP_EOL);
		}
		
		return $result;
	}
	
	function debug($_format = '', ... $_args)
	{
		$result = console_output($_format, $_args);
		
		$eol = $result[0];
		$_format = $result[1];
		$_args = $result[2];
		$result = '';
		
		if(is_string($_format) && !empty($_format))
		{
			$result = insert_prefix(sprintf($_format, ... $_args), 'blue');
		}
		
		fprintf(STDERR, $result);
		
		while(--$eol >= 0)
		{
			fprintf(STDERR, PHP_EOL);
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

function error($_reason, $_own = false, $_exit_code = 224)
{
	$ex = null;
	
	if($_reason instanceof \Throwable)
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
	else if(KEKSE_CLI)
	{
		\kekse\error($_reason);

		if(is_int($_exit_code))
		{
			exit($_exit_code);
		}

		exit(224);
	}
	else if(get_state('fin'))
	{
		return null;
	}
	else if(! get_state('sent'))
	{
		send_header();
	}

	if(!$_own && is_string(get_config('error')))
	{
		die(get_config('error'));
	}

	die($_reason);
}
	
function log_error($_reason, $_source = '', $_path = '', $_die = true)
{
	//
	$ex = null;

	//
	if($_reason instanceof \Throwable)
	{
		$ex = $_reason;
		$_reason = $ex->getMessage();
	}

	//
	if(get_config('raw'))
	{
		if($ex)
		{
			throw $ex;
		}

		throw new \Exception($_reason);
	}
	else if(KEKSE_CLI)
	{
		if($_die)
		{
			if($ex)
			{
				return error($ex);
			}

			return error($_reason);
		}

		return null;
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
		case '@':
			return false;
	}
	
	return true;
}

//
function error_handler($_no, $_str, $_file, $_line)
{
	$result = '[Error ' . (string)$_no . '] ' . $_str . ' (in file \'' . $_file . '\':' . (string)$_line;
	return error($result, true);
}

function exception_handler($_ex)
{
	return error($_ex, true);
}

set_error_handler('\kekse\counter\error_handler');
set_exception_handler('\kekse\counter\exception_handler');

//
function get_path($_path, $_check = true, $_file = false, $_create = true, $_die = true)
{
	if(!is_string($_path))
	{
		if($_die)
		{
			error('Path needs to be (non-empty) String');
		}
		
		return null;
	}
	else if(empty($_path))
	{
		if($_die)
		{
			error('Path may not be empty');
		}
		
		return null;
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
			error('Path may not start with one of [ \'~\', \'+\', \'-\', \'@\' ]');
		}
		
		return null;
	}

	if($_check)
	{
		if($_file)
		{
			$dir = dirname($result);

			if(!is_dir($dir))
			{
				if($_create)
				{
					if(!mkdir($dir, 01777, true))
					{
						if($_die)
						{
							error('Directory of path \'' . $_path . '\' doesn\'t exist (and couldn\'t be created)');
						}

						return null;
					}
				}
				else if($_die)
				{
					error('Directory of path \'' . $_path . '\' doesn\'t exist');
				}
				else
				{
					return null;
				}
			}
			
			if(!file_exists($result))
			{
				touch($result);
				//chmod($result, 0640);
			}
		}
		else if(!is_dir($result))
		{
			if($_create)
			{
				if(!mkdir($result, 01777, true))
				{
					if($_die)
					{
						error('Directory \'' . $_path . '\' doesn\'t exist (and couldn\'t be created)');
					}
					
					return null;
				}
			}
			else if($_die)
			{
				error('Directory \'' . $_path . '\' doesn\'t exist');
			}
			else
			{
				return null;
			}
		}
	}
	else if($_file)
	{
		if($_create)
		{
			$dir = dirname($result);
			
			if(!file_exists($dir))
			{
				mkdir($dir, 01777, true);
			}
			
			if(!file_exists($result))
			{
				touch($result);
				//chmod($result, 0644);
			}
		}
	}
	else if($_create && !file_exists($result))
	{
		mkdir($result, 01777, true);
	}

	return $result;
}

function config_check_limits($_min, $_max)
{
	if(! is_int($_min))
	{
		$_min = null;
	}

	if(! is_int($_max))
	{
		$_max = null;
	}

	$result = '';

	if($_min !== null)
	{
		$result .= '(' . (string)$_min . '..';

		if($_max !== null)
		{
			$result .= (string)$_max;
		}

		$result .= ')';
	}
	else if($_max !== null)
	{
		$result .= '(..' . (string)$_max . ')';
	}

	return $result;
}

function config_check_item($_key, $_value = null, $_bool = false, $_defaults = true)
{
	$item = null;
	$type = null;
	$types = null;
	$validType = null;
	$validLength = null;
	$min = $max = null;
	$limits = '';
	$test = null;
	$validTest = null;
	$success = null;
	$static = null;

	$createReturn = function($_valid, $_string) use(&$_key, &$_value, &$_bool, &$type, &$types, &$validType, &$validLength, &$min, &$max, &$limits, &$test, &$validTest, &$static)
	{
		if($_bool)
		{
			return $_valid;
		}
		
		return array(
			'key' => $_key,
			'value' => $_value,
			'static' => $static,
			'valid' => $_valid,
			'string' => $_string,
			'type' => $type,
			'types' => $types,
			'validType' => $validType,
			'validLength' => $validLength,
			'min' => $min,
			'max' => $max,
			'limits' => $limits,
			'test' => $test,
			'validTest' => $validTest
		);
	};

	if(! isset(CONFIG_VECTOR[$_key]))
	{
		return $createReturn(false, 'No such config item available');
	}
	else
	{
		$item = CONFIG_VECTOR[$_key];
		$type = gettype($_value);
		$types = '[ ' . implode(', ', $item['types']) . ' ]';
		$validType = in_array($type, $item['types']);
		$test = (array_key_exists('test', $item) ? $item['test'] : false);
		$static = (isset($item['static']) && !!$item['static']);
	}

	if($static && !$_defaults)
	{
		return $createReturn(false, 'Static setting! Overwrite is invalid');
	}
	else if(!$validType)
	{
		return $createReturn(false, 'Invalid item type \'' . $type . '\'');
	}

	if(isset($item['max']) && is_int($item['min']))
	{
		$min = $item['min'];
	}
	
	if(isset($item['max']) && is_int($item['max']))
	{
		$max = $item['max'];
	}

	$limits = config_check_limits($min, $max);

	if($min !== null)
	{
		if($type === 'string')
		{
			if(strlen($_value) < $min)
			{
				$validLength = false;
			}
		}
		else if($type === 'integer' || $type === 'double')
		{
			if($_value < $min)
			{
				$validLength = false;
			}
		}
	}
	
	if($max !== null)
	{
		if($type === 'string')
		{
			if(strlen($_value) > $max)
			{
				$validLength = false;
			}
		}
		else if($type === 'integer' || $type === 'double')
		{
			if($_value > $max)
			{
				$validLength = false;
			}
		}
	}

	if($validLength === false)
	{
		$r = 'Invalid length (';
		
		if($min !== null)
		{
			$r .= 'minimum is ' . $min;
			
			if($max !== null)
			{
				$r .= ', ';
			}
		}
		
		if($max !== null)
		{
			$r .= 'maximum is ' . $max;
		}
		
		$r .= ')';
		
		return $createReturn(false, $r);
	}

	if($test)
	{
		switch($_key)
		{
			case 'angle':
				if(is_string($_value))
				{
					$tmp = \kekse\unit($_value, true, false);

					switch($tmp[1])
					{
						case 'deg':
						case 'rad':
							$validTest = true;
							$success = 'Unit is valid: \'' . $tmp[1] . '\'';
							break;
						case '':
							$validTest = true;
							$success = 'No unit in String, assuming \'deg\'';
							break;
						default:
							$validTest = 'Invalid unit \'' . $tmp[1] . '\'';
							break;
					}
				}
				else
				{
					$validTest = true;
				}
				break;
			case 'size':
				$tmp = \kekse\unit($_value, true, null);

				switch($tmp[1])
				{
					case 'px':
					case 'pt':
						if($tmp[0] >= 3 && $tmp[0] <= 512)
						{
							$validTest = true;
						}
						else
						{
							$validTest = 'Exceeds limit (4..512)';
						}
						break;
					default:
						$validTest = true;
						break;
				}
				break;
			case 'unit':
				switch($_value)
				{
					case 'px':
					case 'pt':
						$validTest = true;
						break;
					default:
						$validTest = 'Unknown unit [ px, pt ]';
						break;
				}
				break;
			case 'path':
				$validTest = (get_path($_value, true, false, false, false));// !== null);

				if($validTest !== null)
				{
					$count = \kekse\files($validTest, false, null, [ '~', '+', '-', '@' ], false, true, false, true);
					
					if($count === -1)
					{
						$validTest = 'Warning: not readable?';
					}
					else if($count === 0)
					{
						$validTest = true;
						$success = 'Just initialized (yet empty)';
					}
					else
					{
						$validTest = true;
						$success = $count . ' counters available';
					}
				}
				else
				{
					$validTest = 'No such directory.';
				}
				break;
			case 'log':
				$validTest = (get_path($_value, true, true, false, false) !== null);
				break;
			case 'fonts':
				$validTest = (get_path($_value, true, false, false, false));// !== null);

				if($validTest)
				{
					$count = \kekse\files($validTest, false, '.ttf', null, false, true, false);
					
					if($count === -1)
					{
						$validTest = 'Warning: not readable?';
					}
					else if($count === 0)
					{
						$validTest = true;
						$success = 'Without installed fonts';
					}
					else
					{
						$validTest = true;
						$success = $count . ' installed font' . ($count === 1 ? '' : 's');
					}
				}
				else
				{
					$validTest = 'No such directory.';
				}
				break;
			case 'modules':
				if($_value === null)
				{
					$validTest = true;
				}
				else
				{
					$validTest = (get_path($_value, true, false, false, false));// !== null);
	
					if($validTest)
					{
						$count = \kekse\files($validTest, false, '.php', null, false, true, false);
						
						if($count === -1)
						{
							$validTest = 'Warning: not readable?';
						}
						else if($count === 0)
						{
							$validTest = true;
							$success = 'No modules installed';
						}
						else
						{
							$validTest = true;
							$success = $count . ' installed module' . ($count === 1 ? '' : 's');
						}
					}
					else
					{
						$validTest = 'No such directory.';
					}
				}
				break;
			case 'font':
				$item['test'] = null;//placeholder..
				//check like 'fonts' above.. if not, false here, too.
				//if ok, check if this font is installed in fonts directory (and readable)!
				break;
			case 'drawing':
				$item['test'] = null;//placeholder..
				//test like above 'fonts' dir.. else false; sonst:
				//(1) 'font' is (correctly!) set (and readable - see above!!), else false here
				//(2) if ok, look if 'font' exists as .ttf in the 'fonts'! and readable.. etc.!!
				//if not all were true, result = 'Drawing is not possible this way.
				//AND: maybe the other neccessary config items for drawing to also test!?!
				break;
			case 'fg':
			case 'bg':
				//check if parsing is possible.. check what 'color()' (TODO) returns (!== null?!!?)!//
				//$validTest = true;//FIXME/!!!
				$item['test'] = null;//placeholder..
				break;
			case 'type':
				if(! extension_loaded('gd')) switch($_value)
				{
					case 'png':
					case 'jpg':
						$validTest = 'GD not loaded, but could be supported..';
						break;
					default:
						$validTest = 'Unsupported type';
						break;
				}
				else switch($_value)
				{
					case 'png':
						if(imagetypes() & IMG_PNG)
						{
							$validTest = true;
						}
						else
						{
							$validTest = 'Not supported (by GD library!)';
						}
						break;
					case 'jpg':
						if(imagetypes() & IMG_JPG)
						{
							$validTest = true;
						}
						else
						{
							$validTest = 'Not supported (by GD library!)';
						}
						break;
					default:
						$validTest = 'Unsupported type';
						break;
				}
				break;
			case 'hash':
				$validTest = in_array($_value, hash_algos());
				break;
			default:
				$validTest = true;
				break;
		}
	}
	
	if($validTest === false)
	{
		return $createReturn(false, 'Failed at extended test routine');
	}
	else if(is_string($validTest))
	{
		return $createReturn(false, $validTest);
	}

	//
	if(is_string($success))
	{
		return $createReturn(true, $success);
	}
	
	$r = 'Passed';
	
	if($test === null)
	{
		$r .= ' (without extended tests)';
	}
	
	return $createReturn(true, $r);
}

function config_check_item_static($_key)
{
	if(! isset(CONFIG_VECTOR[$_key]))
	{
		return null;
	}
	else if(array_key_exists('static', CONFIG_VECTOR[$_key]))
	{
		return !!CONFIG_VECTOR[$_key]['static'];
	}

	return false;
}

function config_check($_config = null, $_bool = false, $_die = true)
{
	//
	global $CONFIG;

	//
	$defaults = null;

	if(is_array($_config))
	{
		$defaults = false;
	}
	else
	{
		$_config = DEFAULTS;
		$defaults = true;
	}

	//
	$result = array();

	//
	foreach($_config as $key => $value)
	{
		$result[$key] = config_check_item($key, $value, $_bool, $defaults);
	}

	//
	if($defaults)
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
					$static = (isset($item['static']) ? !!$item['static'] : false);
					$types = '[ ' . implode(', ', $item['types']) . ' ]';
					$min = ((isset($item['min']) && is_int($item['min'])) ? $item['min'] : null);
					$max = ((isset($item['max']) && is_int($item['max'])) ? $item['max'] : null);
					$limits = config_check_limits($min, $max);
					$test = (array_key_exists('test', $item) ? $item['test'] : false);

					$result[$keys[$i]] = array(
						'key' => $keys[$i],
						'value' => null,
						'static' => $static,
						'valid' => false,
						'string' => 'Missing! Needs to be set in DEFAULTS',
						'type' => null,
						'types' => $types,
						'validType' => null,
						'validLength' => null,
						'min' => $min,
						'max' => $max,
						'limits' => $limits,
						'test' => $test,
						'validTest' => null
					);
				}
			}
		}
	}

	//
	return $result;
}

function config_check_host($_host, $_load = true, $_bool = false, $_die = true)
{
	//
	global $CONFIG;

	//
	$config = null;

	if($_load)
	{
		if(($config = load_config(\kekse\join_path(get_state('path'), '@' . $_host))) === null)
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
	return config_check($config, $_bool, $_die);
}

function config_unset_invalid(&$_config, $check)
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
	$path = \kekse\join_path(get_state('path'), '@' . $_host);

	//	
	if(!((is_file($path) && is_readable($path))))
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
		$chk = config_check($conf, true, false);
		config_unset_invalid($conf, $chk);
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

function config_count($_path)
{
	$result = load_config($_path);
	
	if($result === null)
	{
		return -1;
	}
	else if($chk = config_check($result = $result[0], true, false))
	{
		$result = config_unset_invalid($result, $chk);
	}
	else
	{
		return null;
	}

	return count($result);
}

function config_count_host($_host)
{
	return config_count(\kekse\join_path(get_state('path'), '@' . $_host));
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
	else if(config_check($result, true, false) === null)
	{
		return null;
	}

	$hash = hash(get_config('hash'), $data);
	return [ $result, $hash, $data ];
}

//
namespace kekse;

//
function is_number($_item)
{
	return (is_int($_item) || is_float($_item));
}

function check_file($_path, $_file, $_log_error_source = null, $_die = false)
{
	$result = true;

	if(! file_exists($_path))
	{
		if($_file)
		{
			$dir = dirname($_path);

			if(file_exists($dir))
			{
				if(!is_dir($dir))
				{
					if(\kekse\delete($_path, true))
					{
						if(! mkdir($dir, 0700, true))
						{
							$result = false;
						}
					}
					else
					{
						$result = false;
					}
				}
			}
			else if(! mkdir($dir))
			{
				$result = false;
			}

			if($result && file_exists($_path))
			{
				if(!is_file($_path))
				{
					if(\kekse\delete($_path, true))
					{
						if(! touch($_path))
						{
							$result = false;
						}
					}
					else
					{
						$result = false;
					}
				}
			}
			else if(! touch($_path))
			{
				$result = false;
			}
		}
		else if(file_exists($_path))
		{
			if(! is_dir($_path))
			{
				if(\kekse\delete($_path, true))
				{
					if(! mkdir($_path, 0700, true))
					{
						$result = false;
					}
				}
				else
				{
					$result = false;
				}
			}
		}
		else if(! mkdir($_path, 0700, true))
		{
			$result = false;
		}
	}
	else if($_file && !is_file($_path))
	{
		if(\kekse\delete($_path, true))
		{
			if(! touch($_path))
			{
				$result = false;
			}
		}
		else
		{
			$result = false;
		}
	}

	if($result)
	{
		if($_file)
		{
			chmod($_path, 0600);
		}
		else
		{
			chmod($_path, 0700);
		}
	}
	else if(is_string($_log_error_source))
	{
		log_error('Failed with ' . ($_file ? 'file' : 'directory'), $_log_error_source, $_path, $_die);

		if($_die)
		{
			error('Failed with ' . ($_file ? 'file' : 'directory'));
		}
	}

	return $result;
}

function files($_dir, $_list = false, $_suffix = null, $_prefix = null, $_case_sensitive = false, $_remove = null, $_hidden = false, $_unique = false, $_equals = false)
{
	$handle = opendir($_dir);

	if(!$handle)
	{
		return false;
	}

	//
	if(is_string($_suffix))
	{
		$_suffix = array($_suffix);
	}

	if(is_string($_prefix))
	{
		$_prefix = array($_prefix);
	}

	$suffix = $prefix = 0;

	if(is_array($_suffix))
	{
		if(($suffix = count($_suffix)) === 0)
		{
			$_suffix = null;
		}
		else for($i = 0; $i < $suffix; ++$i)
		{
			if(!is_string($_suffix[$i]))
			{
				array_splice($_suffix, $i--, 1);
				--$suffix;
			}
			else if(empty($_suffix[$i]))
			{
				array_splice($_suffix, $i--, 1);
				--$suffix;
			}
		}
	}
	else
	{
		$_suffix = null;
	}

	if(is_array($_prefix))
	{
		if(($prefix = count($_prefix)) === 0)
		{
			$_prefix = null;
		}
		else for($i = 0; $i < $prefix; ++$i)
		{
			if(!is_string($_prefix[$i]))
			{
				array_splice($_prefix, $i--, 1);
				--$prefix;
			}
			else if(empty($_prefix[$i]))
			{
				array_splice($_prefix, $i--, 1);
				--$prefix;
			}
			else if(!$_hidden && $_prefix[$i][0] === '.')
			{
				$_hidden = true;
			}
		}
	}
	else
	{
		$_prefix = null;
	}

	if(!is_bool($_remove))
	{
		$_remove = (($suffix <= 1) && ($prefix <= 1));
	}

	if(!$_remove)
	{
		$_unique = false;
	}

	$result = (($_list || $_unique) ? array() : 0);
	$index = 0;

	while($sub = readdir($handle))
	{
		if($sub === '.' || $sub === '..')
		{
			continue;
		}
		else if(!$_hidden && $sub[0] === '.')
		{
			continue;
		}

		if($prefix)
		{
			$found = false;

			for($i = 0; $i < $prefix; ++$i)
			{
				if(\kekse\starts_with($sub, $_prefix[$i], $_case_sensitive))
				{
					if(!$_equals && $_prefix[$i] === $sub)
					{
						continue;
					}
					else if($_remove)
					{
						$sub = substr($sub, strlen($_prefix[$i]));
					}

					$found = true;
					break;
				}
			}

			if(!$found)
			{
				continue;
			}
		}

		if($suffix)
		{
			$found = false;

			for($i = 0; $i < $suffix; ++$i)
			{
				if(\kekse\ends_with($sub, $_suffix[$i], $_case_sensitive))
				{
					if(!$_equals && $_suffix[$i] === $sub)
					{
						continue;
					}
					else if($_remove)
					{
						$sub = substr($sub, 0, -strlen($_suffix[$i]));
					}

					$found = true;
					break;
				}
			}

			if(!$found)
			{
				continue;
			}
		}

		if($_unique)
		{
			if(! in_array($sub, $result))
			{
				$result[$index++] = $sub;
			}
		}
		else if($_list)
		{
			$result[$index++] = $sub;
		}
		else
		{
			++$result;
			++$index;
		}
	}

	closedir($handle);

	if($_list)
	{
		return $result;
	}

	return $index;
}

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

		while($rem < ($len - 1) && ($result[$rem] === '~' || $result[$rem] === '+' || $result[$rem] === '-') || $result[$rem] === '@')
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
	
	if(is_link($_path))
	{
		return !!unlink($_path);
	}
	else if(is_dir($_path))
	{
		if(is_int($_depth) && ($_depth <= $_depth_current || $_depth <= 0))
		{
			return rmdir($_path);
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

			$p = \kekse\join_path($_path, $sub);

			if(is_link($p))
			{
				if(!unlink($p))
				{
					return false;
				}
			}
			else if(! is_writable($p))
			{
				return false;
			}
			else if(is_dir($p))
			{
				if($_depth !== null && $_depth <= $_depth_current)
				{
					return false;
				}
				else if(!\kekse\delete($p, $_depth, $_depth_current + 1))
				{
					return false;
				}
				else
				{
					--$count;
				}
			}
			else if(unlink($p) === false)
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
	else if(!unlink($_path))
	{
		return false;
	}
	
	return true;
}

function get_param($_key, $_numeric = false, $_float = false, $_strict = KEKSE_STRICT, $_fallback = true)
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
	else
	{
		$value = \kekse\remove_white_spaces($value);
	}

	if($_numeric === null) switch(strtolower($value[0]))
	{
		case '0':
		case 'n':
			return false;
		case '1':
		case 'y':
			return true;
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
	$removed = '';

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
		$removed = substr($value, 0, $remove);
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
		return null;
	}
	else if(! $_numeric)
	{
		$numeric = false;
	}
	else if(!$_float && $numeric)
	{
		$hadPoint = false;
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
	else if($removed)
	{
		$result = $removed . $result;
	}

	return $result;
}

//
function unit($_string, $_float = false, $_null = true)
{
	$len = strlen($_string);
	
	if($len > KEKSE_LIMIT)
	{
		return null;
	}
	else
	{
		$_string = \kekse\remove_white_spaces(strtolower($_string));
	}

	$size = '';
	$unit = '';
	$state = false;
	$float = false;
	$wait = false;
	$byte = 0;
	$negative = false;

	$rem = 0;
	for($i = 0; $i < $len; ++$i)
	{
		if($_string[$i] === '-' || $_string[$i] === '+')
		{
			if($_string[$i] === '-')
			{
				$negative = !$negative;
			}

			++$rem;
		}
		else
		{
			break;
		}
	}

	if($rem > 0)
	{
		$_string = substr($_string, $rem);
		$len -= $rem;
	}
	
	for($i = 0; $i < $len; ++$i)
	{
		$byte = ord($_string[$i]);
		
		if($byte <= 32)
		{
			continue;
		}
		else if($byte >= 97 && $byte <= 122)
		{
			$unit .= chr($byte);
			$state = true;
		}
		else if($state)
		{
			break;
		}
		else if($wait)
		{
			continue;
		}
		else if($byte >= 48 && $byte <= 57)
		{
			$size .= chr($byte);
		}
		else if($byte === 46)
		{
			if(!$_float || $float)
			{
				$wait = true;
			}
			else if($i < ($len - 1))
			{
				$float = true;
				$size .= '.';
			}
		}
		else if($byte === 43 || $byte === 45)
		{
			//43 = +
			//45 = -
		}
	}
	
	$s = strlen($size);
	$u = strlen($unit);
	
	if($s === 0)
	{
		$size = 0;
	}
	else
	{
		if($float)
		{
			if($size[0] === '.')
			{
				$size = '0' . $size;
			}
			else if($size[$s - 1] === '.')
			{
				$size = substr($size, 0, -1);
				$float = false;
			}
		}
		
		if($float)
		{
			$size = (float)$size;
		}
		else
		{
			$size = (int)$size;
		}

		if($negative)
		{
			$size = -$size;
		}
	}
	
	if($u === 0 && $_null)
	{
		$unit = null;
	}
	
	return array($size, $unit);
}

//
$color_condition = (KEKSE_CLI || \kekse\counter\get_config('raw') || \kekse\counter\get_config('drawing'));

function color(... $_args)
{
	global $color_condition;
	
	if($color_condition)
	{
		return \kekse\color\color(... $_args);
	}
	
	return false;
}

//
namespace kekse\color;

if($color_condition)
{
	//
	function color($_string, $_gd = null)
	{
		if(!is_bool($_gd))
		{
			$_gd = extension_loaded('gd');
		}

		if(!is_string($_string))
		{
			if(is_array($_string) && color_check_array($_string))
			{
				return color_fix($_string, $_gd);
			}
			
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

		if(color_is_hexadecimal($_string))
		{
			$result = color_hexadecimal($_string);
		}
		else
		{
			$result = color_rgb_a($_string);
		}

		if($result !== null)
		{
			$result = color_fix($result, $_gd);
		}
		
		return $result;
	}

	//
	function color_fix($_array, $_gd = false)
	{
		$len = count($_array);
		
		if($len < 3 || $len > 4)
		{
			return null;
		}
		
		for($i = 0; $i < 3; ++$i)
		{
			if(is_float($_array[$i]))
			{
				if($_array[$i] < 0.0)
				{
					$_array[$i] = 0;
				}
				else if($_array[$i] > 1.0)
				{
					$_array[$i] = 255;
				}
				else
				{
					$_array[$i] = (int)($_array[$i] * 255);
				}
			}
			else if(is_int($_array[$i]))
			{
				if($_array[$i] < 0)
				{
					$_array[$i] = 0;
				}
				else if($_array[$i] > 255)
				{
					$_array[$i] = 255;
				}
			}
		}

		if($len === 3)
		{
			$_array[3] = 1.0;
		}
		else if(is_int($_array[3]))
		{
			$_array[3] = (float)($_array[3] / 255);
		}

		if($_gd)
		{
			$_array = color_fix_gd($_array);
		}
		
		return $_array;
	}

	function color_fix_gd($_array, $_int = false)
	{
		if(count($_array) === 3)
		{
			$_array[3] = 1.0;
		}
		else if($_int && is_int($_array[3]))
		{
			$_array[3] = (float)($_array[3] / 255);
		}

		$_array[3] = (int)(127 - ($_array[3] * 127));
		return $_array;
	}

	//
	function color_check_array($_array)
	{
		if(!is_array($_array))
		{
			return false;
		}
		
		$len = count($_array);
		
		if($len < 3 || $len > 4)
		{
			return false;
		}
		else for($i = 0; $i < 3; ++$i)
		{
			if(!is_int($_array[$i]))
			{
				return false;
			}
			else if($_array[$i] < 0 || $_array[$i] > 255)
			{
				return false;
			}
		}
		
		if($len === 3)
		{
			return true;
		}
		else if(is_int($_array[3]))
		{
			if($_array[3] < 0 || $_array[3] > 255)
			{
				return false;
			}
		}
		else if(is_float($_array[3]))
		{
			if($_array[3] < 0.0 || $_array[3] > 1.0)
			{
				return false;
			}
		}
		
		return true;
	}
	
	function color_rgb_a($_string)
	{
		//
		$_string .= ',';
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

	function color_hexadecimal($_string)
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
			if(strlen($sub .= color_symbol_hexadecimal($_string[$i])) > 0)
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
			if(strlen($sub .= color_symbol_hexadecimal($_string[$i])) > 1)
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
		return $result;
	}

	function color_symbol_hexadecimal($_char)
	{
		$b;
		
		if(($b = ord($_char)) >= 48 && $b <= 57)
		{
			return $_char;
		}
		else if($b >= 97 && $b <= 102)
		{
			return $_char;
		}
		else if($b >= 65 && $b <= 70)
		{
			return chr($b + 32);
		}
		
		return '';
	}
	
	function color_is_hexadecimal($_string)
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
			if(color_symbol_hexadecimal($_string[$i]) === '')
			{
				return false;
			}
		}

		return true;
	}
}

//
namespace kekse\counter;

//
function counter($_host = null, $_read_only = null)
{
	//
	global $CONFIG;

	//
	if(!is_bool($_read_only))
	{
		$_read_only = !!get_config('raw');
	}

	//
	set_state('test', (KEKSE_CLI ? null : (isset($_GET['test']))));
	set_state('ro', (KEKSE_CLI ? null : (get_state('test') || (isset($_GET['readonly']) || isset($_GET['ro'])))));
	set_state('zero', (KEKSE_CLI ? null : (get_config('drawing') && isset($_GET['zero']) && extension_loaded('gd'))));
	set_state('draw', (KEKSE_CLI ? null : (get_state('zero') || (get_config('drawing') && isset($_GET['draw']) && extension_loaded('gd')))));

	//
	set_state('path', get_path(get_config('path'), true, false, true, true));
	set_state('log', get_path(get_config('log'), true, true, true, true));

	if(file_exists(get_state('log')) && !(is_file(get_state('log')) || is_writable(get_state('log'))))
	{
		set_state('log', null);
	}

	if(get_config('drawing') || KEKSE_CLI)
	{
		set_state('fonts', get_path(get_config('fonts'), false, false, false));
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
		log_error('DRAWING is enabled, but FONTS directory doesn\'t exist', '', get_config('fonts'), false);
		set_state('fonts', null);
		set_state('zero', false);
		set_state('draw', false);
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
			$p = \kekse\join_path(get_state('path'), $_host);
			
			switch($type)
			{
				case '~':
					if($_check && !is_file($p))
					{
						return 0;
					}

					$type = COUNTER_VALUE;
					break;
				case '+':
					if($_check && !is_dir($p))
					{
						return 0;
					}

					$type = COUNTER_DIR;
					break;
				case '-':
					if($_check && !is_file($p))
					{
						return 0;
					}

					$type = COUNTER_FILE;
					break;
				case '@':
					if($_check && !is_file($p))
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
					\kekse\error('Couldn\'t opendir()');
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
					$sub = \kekse\join_path(get_state('path'), '{~,+,-,@}' . strtolower($list[$i]));
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
				\kekse\info('v' . COUNTER_VERSION);
			}

			if($_copyright)
			{
				\kekse\info('Copyright (c) %s', KEKSE_COPYRIGHT);
			}

			exit(0);
		}

		function help($_index = null)
		{
			\kekse\debug('Visit: <' . COUNTER_HELP . '>');
			\kekse\info('Available parameters (use only one at the same time, please):');
			\kekse\info();

			$b = function($_s)
			{
				return \kekse\console\bold($_s);
			};

			printf('    -' . $b('?') . ' | --' . $b('help') . PHP_EOL);
			printf('    -' . $b('V') . ' | --' . $b('version') . PHP_EOL);
			printf('    -' . $b('C') . ' | --' . $b('copyright') . PHP_EOL);
			printf(PHP_EOL);
			printf('    -' . $b('c') . ' | --' . $b('check') . ' [*]' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -' . $b('v') . ' | --' . $b('values') . ' [*]' . PHP_EOL);
			printf('    -' . $b('s') . ' | --' . $b('sync') . ' [*]' . PHP_EOL);
			printf('    -' . $b('l') . ' | --' . $b('clean') . ' [*]' . PHP_EOL);
			printf('    -' . $b('p') . ' | --' . $b('purge') . ' [*]' . PHP_EOL);
			printf('    -' . $b('r') . ' | --' . $b('remove') . ' [*]' . PHP_EOL);
			printf('    -' . $b('z') . ' | --' . $b('sanitize') . ' [--allow-without-values | -w / --dot-files | -d]' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -' . $b('t') . ' | --' . $b('set') . ' (host) [value = 0]' . PHP_EOL);
			printf(PHP_EOL);
			printf('    -' . $b('f') . ' | --' . $b('fonts') . ' [*]' . PHP_EOL);
			printf('    -' . $b('y') . ' | --' . $b('types') . PHP_EOL);
			printf('    -' . $b('h') . ' | --' . $b('hashes') . PHP_EOL);
			printf(PHP_EOL);
			printf('    -' . $b('e') . ' | --' . $b('errors') . PHP_EOL);
			printf('    -' . $b('u') . ' | --' . $b('unlog') . PHP_EOL);
			printf(PHP_EOL);
			
			\kekse\info('The \'*\' arguments should all support GLOBs (which you should escape or quote).');
			\kekse\info('Arguments within \'[]\' are optional, and those within \'()\' are required..');

			exit(0);
		}

		function hashes($_index = null)
		{
			$list = hash_algos();
			$len = count($list);
			
			\kekse\info(2, 'Found %d hashes', $len);
			
			for($i = 0; $i < $len; ++$i)
			{
				\kekse\debug($list[$i]);
			}
			
			exit(0);
		}

		function fonts($_index = null)
		{
			//
			if(!is_string(get_state('fonts')) || empty(get_state('fonts')))
			{
				\kekse\error('\'FONTS\' directory is not properly configured');
				exit(1);
			}
			else if(! is_dir(get_state('fonts')))
			{
				\kekse\error('\'FONTS\' directory doesn\'t exist.');
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
				error('No fonts found' . ($defined < 0 ? '' : ' (with your ' . $defined . ' globs)'));
				exit(3);
			}
			
			$len = count($result);
			\kekse\info('Found %d fonts' . ($defined < 0 ? '' : ' (by %d globs)'), $len, $defined);
			\kekse\info();
			
			for($i = 0; $i < $len; ++$i)
			{
				\kekse\debug($result[$i]);
			}

			printf(PHP_EOL);
			exit(0);
		}

		function types($_index = null)
		{
			if(!extension_loaded('gd'))
			{
				\kekse\error(' >> The GD library/extension is not loaded/available');
				exit(1);
			}
			
			$types = imagetypes();
			$supported = array();
			
			if($types & IMG_PNG)
			{
				$supported[] = 'png';
			}
			
			if($types & IMG_JPG)
			{
				$supported[] = 'jpg';
			}
			
			$len = count($supported);
			
			if($len === 0)
			{
				\kekse\error('Supporting NO image types. :-/');
				exit(1);
			}
			else
			{
				\kekse\info(2, 'Supporting %d image types:', $len);
			}
			
			for($i = 0; $i < $len; ++$i)
			{
				\kekse\debug($supported[$i]);
			}

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

						$item = \kekse\join_path(get_state('path'), '@' . $item);
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
					\kekse\warn('NONE of your %d hosts/globs has own configuration', $cnt);
					exit(1);
				}
			}
			else
			{
				$hosts = null;
			}

			//
			$result = null;

			if($hosts === null)
			{
				\kekse\info(2, 'Checking your DEFAULT configuration (no hosts specified).');
				$result = config_check();
			}
			else
			{
				\kekse\info('Found %d per-host configuration' . ($idx === 1 ? '' : 's') . ' (by %d glob' . ($cnt === 1 ? '' : 's') . ' in total).', $idx, $cnt);
				$len = count($hosts);
				$result = array();

				for($i = 0; $i < $len; ++$i)
				{
					if(($result[$hosts[$i]] = config_check_host($hosts[$i], true, false)) === null)
					{
						unset($result[$hosts[$i]]);
					}
				}
			}

			//			
			$ok = 0;
			$bad = 0;
			$maxLen = array('string' => 0, 'key' => 0, 'type' => 0, 'limits' => 0);

			$checkMaxLen = function($_state) use(&$maxLen)
			{
				$len = 0;

				if(($len = strlen($_state['string'])) > $maxLen['string'])
				{
					$maxLen['string'] = $len;
				}

				if(($len = strlen($_state['key'])) > $maxLen['key'])
				{
					$maxLen['key'] = $len;
				}

				if(is_string($_state['type']) && ($len = strlen($_state['type'])) > $maxLen['type'])
				{
					$maxLen['type'] = $len;
				}

				if(($len = strlen($_state['limits'])) > $maxLen['limits'])
				{
					$maxLen['limits'] = $len;
				}
			};

			if($hosts === null)
			{
				\kekse\info();

				foreach($result as $key => $state)
				{
					$checkMaxLen($state);
				}
				
				$maxLen['key'] += 3;
				$format = ('%' . $maxLen['key'] . 's ]  %-4s  %-' . $maxLen['string'] . 's    %' . $maxLen['type'] . 's %-' . $maxLen['limits'] . 's    %s' . PHP_EOL);
				
				foreach($result as $key => $state)
				{
					if($state['valid'])
					{
						printf($format, '[ ' . $state['key'], 'OK', $state['string'], $state['type'], $state['limits'], $state['types']);
						++$ok;
					}
					else
					{
						fprintf(STDERR, $format, '[ ' . $state['key'], 'BAD', $state['string'], $state['type'], $state['limits'], $state['types']);
						++$bad;
					}
				}
			}
			else
			{
				$maxLen['host'] = 0;
				$count = 0;

				foreach($result as $host => $item)
				{
					$c = count($item);

					if($c === 0)
					{
						continue;
					}
					else
					{
						$count += $c;
					}

					foreach($item as $key => $state)
					{
						$checkMaxLen($state);
						++$count;
					}

					if(($len = strlen($host)) > $maxLen['host'])
					{
						$maxLen['host'] = $len;
					}

					++$count;
				}

				if($count === 0)
				{
					\kekse\warn('No config items found');
					exit(2);
				}
				else
				{
					\kekse\info();
				}

				$maxLen['key'] += 3;
				$format = (' %' . $maxLen['host'] . 's %' . $maxLen['key'] . 's ]  %-4s  %-' . $maxLen['string'] . 's    %' . $maxLen['type'] . 's %-' . $maxLen['limits'] . 's    %s' . PHP_EOL);

				foreach($result as $host => $item)
				{
					foreach($item as $key => $state)
					{
						if($state['valid'])
						{
							printf($format, $host, '[ ' . $state['key'], 'OK', $state['string'], $state['type'], $state['limits'], $state['types']);
							++$ok;
						}
						else
						{
							fprintf(STDERR, $format, $host, '[ ' . $state['key'], 'BAD', $state['string'], $state['type'], $state['limits'], $state['types']);
							++$bad;
						}
					}
				}
			}

			printf(PHP_EOL);

			if(get_config('raw'))
			{
				\kekse\warn('Warning: `RAW` mode enabled, which leads to a changed behavior..');
			}
			
			//
			if($bad === 0)
			{
				\kekse\info('All %d were OK! :-)', $ok);
			}
			else if($ok === 0)
			{
				\kekse\error('NO single item was valid (%d errors)...', $bad);
			}
			else
			{
				\kekse\warn('Only %d item' . ($ok === 1 ? '' : 's') . ' ' . ($ok === 1 ? 'is' : 'are') . ' valid.. %d caused errors!', $ok, $bad);
			}

			if($bad === 0)
			{
				exit(0);
			}

			exit(3);
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
					\kekse\warn('No valid target host specified!');
					exit(1);
				}
			}
			else
			{
				\kekse\warn('Can\'t locate arguments!');
				exit(2);
			}

			$p = \kekse\join_path(get_state('path'), '~' . $host);
			$existed = file_exists($p);
			$orig = null;

			if($existed)
			{
				if(!is_file($p))
				{
					\kekse\error('Target is not a file!');
					exit(3);
				}
				else if(!is_writable($p) && is_readable($p))
				{
					\kekse\error('File is not readable (n)or writable!');
					exit(4);
				}
				else if(($orig = file_get_contents($p)) === false)
				{
					$orig = null;
				}
				else if(($orig = (int)$orig) === $value)
				{
					\kekse\info('File is already set to (%d).. so nothing changes! :-)', $value);
					exit(0);
				}
			}

			\kekse\info('Target file is \'%s\'' . ($existed ? ' (already existed)' : '') . '.', $p);

			if($orig === null)
			{
				if(!\kekse\prompt('Initialize file with value (' . $value . ') now [yes/no]? '))
				{
					\kekse\warn(' >> Aborting.. by request.');
					exit(5);
				}
			}
			else if(!\kekse\prompt('Replace old value (' . $orig . ') by new one (' . $value . ') now [yes/no]? '))
			{
				\kekse\warn('Aborting, as requested.');
				exit(6);
			}

			if(file_put_contents($p, (string)$value) === false)
			{
				\kekse\error('Unable to write value: %d', $value);
				exit(7);
			}
			else if($orig === null)
			{
				\kekse\info('Successfully initialized file with value: %d', $value, $p);
			}
			else
			{
				\kekse\info('Successfully replaced old value (%d) by new value: %d', $orig, $value);
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
				\kekse\warn('No hosts found.');
				exit(1);
			}
			else
			{
				\kekse\warn('This will *delete* the whole cache!');
				\kekse\debug(2, 'Maybe you\'d rather like to \'--clean/-c\' instead!?');
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
					printf('    ' . $key . PHP_EOL);
				}
			}

			if($total === 0)
			{
				\kekse\warn('All of the %d found hosts are already free of special files.', $len);
				exit(0);
			}

			\kekse\info();
			\kekse\info('Found %d directories and %d files - for %d hosts.', count($dirs), count($files), $total);

			if(!\kekse\prompt('Do you really want to delete them [yes/no]? '))
			{
				\kekse\warn('Good, we\'re aborting here, as requested.');
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
				\kekse\info('Great, all %d files deleted successfully!', $good);
				exit(0);
			}
			else if($good > 0)
			{
				\kekse\info('%d files sucessfully deleted..', $good);
				\kekse\error('... but %d files could *not* be removed. :-/', $errors);
				exit(3);
			}
			
			\kekse\warn('NONE of the selected %d files deleted! :-(', $total);
			exit(4);
		}
		
		function clean($_index = null)
		{
			//
			$list = get_hosts($_index);

			if($list === null)
			{
				\kekse\warn('No hosts found.');
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
				\kekse\info('No caches stored for %d host' . ($orig === 1 ? '' : 's') . '.', $orig);
				exit(0);
			}
			else
			{
				\kekse\info(2, 'Found %d host' . ($len === 1 ? '' : 's') . ' (of %d) with caches:', $len, $orig);
			}

			foreach($list as $host => $type)
			{
				printf('    ' . $host . PHP_EOL);
			}

			printf(PHP_EOL);

			if(!\kekse\prompt('Do you want to continue [yes/no]? '))
			{
				\kekse\warn('Abort by request.');
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
					\kekse\info(null, 'Great, not a single error!');
				}
				else
				{
					$total = 0;

					foreach($errors as $h => $c)
					{
						$total += $c;
					}

					\kekse\error(null, 'Hm, %d host' . ($e === 0 ? '' : 's') . ' caused %d errors.', $e, $total);
				}
			}

			if($d === 0)
			{
				\kekse\info(($e === 0 ? 'N' : 'But n') . 'o deletions, so nothing changed at all.');
				exit(0);
			}
			else
			{
				\kekse\info(2, ' Deleted files per host:');
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

			$s = ' %' . $maxLen . 's: %d';

			foreach($delete as $host => $del)
			{
				\kekse\debug($s, $host, $del);
			}

			//
			\kekse\info();
			\kekse\info('Totally deleted %d files.', $sum);
			exit(0);
		}
		
		//
		function sanitize($_index = null, $_allow_without_values = false, $_dot_files = false)
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
					case '--dot-files':
					case '-d':
						$_dot_files = true;
						break;
				}

				if($_allow_without_values && $_dot_files)
				{
					break;
				}
			}

			//
			$delete = array();
			$d = 0;
			
			//
			$handle = opendir(get_state('path'));
			
			if($handle === false)
			{
				\kekse\error('Failed to open directory (\'' . get_state('path') . '\')');
				exit(1);
			}
			else while($sub = readdir($handle))
			{
				if($sub === '.' || $sub === '..')
				{
					continue;
				}
				else if(!$_dot_files && $sub[0] === '.')
				{
					continue;
				}
				
				$p = \kekse\join_path(get_state('path'), $sub);
				$v = \kekse\join_path(get_state('path'), '~' . $sub);

				if(strlen($sub) === 1)
				{
					$delete[$d++] = $p;
				}
				else if($sub[0] === '~')
				{
					if(!is_file($p))
					{
						$delete[$d++] = $p;
					}
				}
				else if($sub[0] === '+')
				{
					if(!is_dir($p))
					{
						$delete[$d++] = $p;
					}
					else if(!$_allow_without_values && !is_file($v))
					{
						$delete[$d++] = $p;
					}
				}
				else if($sub[0] === '-')
				{
					if(!is_file($p))
					{
						$delete[$d++] = $p;
					}
					else if(!$_allow_without_values && !is_file($v))
					{
						$delete[$d++] = $p;
					}
				}
				else if($sub[0] === '@')
				{
					if(!is_file($p))
					{
						$delete[$d++] = $p;
					}
					else if(!$_allow_without_values && !is_file($v))
					{
						$delete[$d++] = $p;
					}
				}
				else
				{
					$delete[$d++] = $p;
				}
			}

			closedir($handle);
			
			//
			if($d === 0)
			{
				\kekse\info('No files found to delete.');
				exit(0);
			}
			else
			{
				\kekse\info('Allow deletion of %d files (non-existing value files %s)..', $d, ($_allow_without_values ? 'are allowed' : 'will also delete caches'));

				if(!\kekse\prompt('Do you really want to continue [yes/no]? '))
				{
					\kekse\warn('Aborted, as requested.');
					exit(1);
				}
				else
				{
					printf(PHP_EOL);
				}
			}
			
			$result = array();
			$errors = array();
			$r = 0;
			$e = 0;

			for($i = 0; $i < $d; ++$i)
			{
				if(\kekse\delete($delete[$i], true))
				{
					$result[$r++] = $delete[$i];
				}
				else
				{
					$errors[$e++] = $delete[$i];
				}
			}

			if($r === 0)
			{
				\kekse\warn('NO files deleted..');
				
				if($e > 0)
				{
					\kekse\error(2, 'AND %d errors:', $e);
					
					for($i = 0; $i < $e; ++$i)
					{
						fprintf(STDERR, '    ' . $errors[$i] . PHP_EOL);
					}
					
					printf(PHP_EOL);
				}

				exit(2);
			}
			else
			{
				\kekse\info(2, 'Operation deleted %d files:', $r);
			}

			for($i = 0; $i < $r; ++$i)
			{
				printf('    %s' . PHP_EOL, $result[$i]);
			}
			
			printf(PHP_EOL);

			if($e > 0)
			{
				\kekse\warn(2, 'But with %d errors:', $e);
				
				for($i = 0; $i < $e; ++$i)
				{
					fprintf(STDERR, '    %s' . PHP_EOL, $errors[$i]);
				}
				
				printf(PHP_EOL);
			}
			
			return ($e === 0 ? 0 : 3);
		}
		
		function remove($_index = null)
		{
			//
			$list = get_hosts($_index);
			
			if($list === null)
			{
				\kekse\warn('No hosts found.');
				exit(1);
			}

			$delete = array();
			$d = 0;
			$h = 0;
			$c = count($list);

			foreach($list as $host => $type)
			{
				$p = $orig = get_state('path');
				++$h;

				if($type & COUNTER_VALUE)
				{
					$delete[$d++] = \kekse\join_path($orig, '~' . $host);
				}

				if($type & COUNTER_DIR)
				{
					$delete[$d++] = \kekse\join_path($orig, '+' . $host);
				}

				if($type & COUNTER_FILE)
				{
					$delete[$d++] = \kekse\join_path($orig, '-' . $host);
				}

				if($type & COUNTER_CONFIG)
				{
					$delete[$d++] = \kekse\join_path($orig, '@' . $host);
				}
			}

			if($d === 0)
			{
				\kekse\info('No files found (of %d hosts).', $c);
				exit(0);
			}
			else
			{
				\kekse\info(2, 'Found %d files, for those %d hosts:', $d, $h);
			}

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
					$types .= '@ ';
				}
				else
				{
					$types .= '  ';
				}

				$types = substr($types, 0, -1);
				printf($format, $host, $types);
			}

			printf(PHP_EOL);

			//
			if(!\kekse\prompt('Do you really want to delete ' . $d . ' files [yes/no]? '))
			{
				\kekse\warn('Abort requested.');
				exit(2);
			}

			$ok = 0;
			$err = 0;

			for($i = 0; $i < $d; ++$i)
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
				\kekse\info('Successfully deleted all %d files!', $ok);
				exit(0);
			}
			else if($ok === 0)
			{
				\kekse\error('NONE of %d files could be deleted! :-/', $index);
				exit(3);
			}

			\kekse\info('Successfully deleted %d files.', $ol);
			\kekse\error('BuT also %d errors occured.', $err);

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
				\kekse\warn('No hosts found.');
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
				$c = ($v[3] === null ? '' : ($v[3] === -1 ? 'x' : '   +' . $v[3]));
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
				\kekse\info('No hosts to synchronize.');
				exit(0);
			}

			if(!\kekse\prompt('Do you want to synchronize ' . count($sync) . ' hosts now [yes/no]? '))
			{
				\kekse\warn('Good, aborting sync.');
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
				\kekse\info();
				\kekse\info('Synchronization of %d hosts:', $tot);

				if($chg > 0)
				{
					\kekse\info('Changed %d cache values', $chg);
				}

				if($del > 0)
				{
					\kekse\warn('Deleted %d cache items', $del);
				}

				if($err > 0)
				{
					\kekse\error((($chg > 0 || $del > 0) ? 'But ' : '') . '%d errors occured. :-/', $err);
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
				\kekse\info('There is no \'%s\' which could be deleted. .. that\'s good for you. :)~', basename(get_state('log')));
				exit(0);
			}
			else if(!is_file(get_state('log')))
			{
				\kekse\warn('The \'%s\' is not a regular file. Please replace/remove it asap!', get_state('log'));
				exit(1);
			}

			if(!\kekse\prompt('Do you really want to delete the file \'' . basename(get_state('log')) . '\' [yes/no]? '))
			{
				\kekse\warn('Log file deletion aborted (by request).');
				exit(1);
			}
			else if(\kekse\delete(get_state('log'), false) === false)
			{
				\kekse\error('The \'%s\' couldn\'t be deleted!!', basename(get_state('log')));

				if(! is_file(get_state('log')))
				{
					\kekse\debug('I think it\'s not a regular file, could this be the reason why?');
				}

				exit(2);
			}

			\kekse\info('The \'%s\' is no longer.. :-)', basename(get_state('log')));
			exit(0);
		}

		function errors($_index = null)
		{
			if(! file_exists(get_state('log')))
			{
				\kekse\info(' >> No errors logged! :-D' . PHP_EOL);
				exit(0);
			}
			else if(!is_file(get_state('log')))
			{
				\kekse\error('\'%s\' is not a file! Please delete asap!', basename(get_state('log')));
				exit(1);
			}
			else if(!is_readable(get_state('log')))
			{
				\kekse\error('Log file \'%s\' is not readable! Please correct this asap!', basename(get_state('log')));
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

			kekse\info('There are %d error log lines in \'%s\'..', $result, basename(get_state('log')));
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
		\kekse\info('Call with `--help/-?` to see a list of available parameters.');
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
	$host = get_host($_host);
	set_state('host', $host);
	make_config($host);
	unset($host);

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
		function check_auto($_host)
		{
			$auto = get_config('auto');

			if($auto === null)
			{
				error(get_config('none'), true);
			}

			$exists = is_file(get_state('value'));

			if($exists)
			{
				if(!get_state('test') && !is_readable(get_state('value')))
				{
					log_error('Value file for host \'' . (string)$_host . '\' is not readable', 'check_auto', get_state('value'), false);
					error(get_config('none'), true);
				}
				else if(!get_state('ro') && !is_writable(get_state('value')))
				{
					log_error('Value file for host \'' . (string)$_host . '\' is not writable', 'check_auto', get_state('value'), false);
					error(get_config('none'), true);
				}

				return;
			}

			$values = 0;
			$total = 0;
			
			$handle = opendir(get_state('path'));

			if($handle === false)
			{
				log_error('Couldn\'t opendir()', 'check_auto', get_state('path'));
				error('Couldn\'t opendir()');
			}
			else while($sub = readdir($handle))
			{
				if($sub === '.' || $sub === '..')
				{
					continue;
				}
				else
				{
					++$total;
				}

				if($sub[0] === '~')
				{
					++$values;
				}
			}

			closedir($handle);

			$limit = get_config('limit');

			if($total >= $limit)
			{
				log_error('Limit exceeded (' . (string)$limit . ')', 'check_auto', get_state('path'), false);
				error(get_config('none'), true);
			}
			else if(is_string($_host) && !empty($_host))
			{
				return;
			}
			else if(is_string(get_config('override')) && !empty(get_config('override')))
			{
				return;
			}
			else if(get_state('overridden'))
			{
				error(get_config('none'), true);
			}
			else if(is_int(get_config('auto')))
			{
				if($values >= get_config('auto'))
				{
					error(get_config('none'), true);
				}
			}
			else if(is_bool(get_config('auto')))
			{
				if(! get_config('auto'))
				{
					error(get_config('none'), true);
				}
			}
			else
			{
				log_error('Invalid \'AUTO\' config', 'check_auto', '', false);
				error(get_config('none'), true);
			}
		}

		//
		function with_server()
		{
			if(KEKSE_CLI)
			{
				return false;
			}

			$conf = get_config('threshold');
			
			if($conf === null || $conf <= 0)
			{
				return false;
			}

			return get_config('server');
		}

		function with_client()
		{
			if(KEKSE_CLI)
			{
				return false;
			}

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
		check_auto($_host);
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

			function check_angle($_value)
			{
				$result = null;

				if(is_string($_value))
				{
					$unit = \kekse\unit($_value, true, true);

					switch($unit[1])
					{
						case 'rad':
							$result = rad2deg($unit[0]);
							break;
						case 'deg':
						case null:
							$result = $unit[0];
							break;
						default:
							$result = null;
							break;
					}
				}
				else if(is_int($_value))
				{
					$result = (float)$_value;
				}
				else if(is_float($_value))
				{
					$result = $_value;
				}
				else
				{
					$result = null;
				}

				if($result !== null)
				{
					$result %= 360;
				}

				return $result;
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
					$result['unit'] = \kekse\get_param('unit', false);
					$result['size'] = \kekse\get_param('size', true, true, false);
					$result['font'] = \kekse\get_param('font', false);
					$result['fg'] = \kekse\get_param('fg', false);
					$result['bg'] = \kekse\get_param('bg', false);
					$result['h'] = \kekse\get_param('h', true, false, true);
					$result['v'] = \kekse\get_param('v', true, false, true);
					$result['x'] = \kekse\get_param('x', true, false, true);
					$result['y'] = \kekse\get_param('y', true, false, true);
					$result['aa'] = \kekse\get_param('aa', null, false, true);
					$result['angle'] = \kekse\get_param('angle', true, true, false);
				}
				
				//
				if($result['unit'] === null)
				{
					$result['unit'] = get_config('unit');
				}

				switch($result['unit'] = strtolower($result['unit']))
				{
					case 'px':
					case 'pt':
						break;
					default:
						if($_die)
						{
							draw_error('\'?unit\' is invalid; none of [ px, pt ]');
							return null;
						}
						
						$result['unit'] = get_config('unit');
						break;
				}

				//
				if($result['size'] === null)
				{
					$result['size'] = get_config('size');
				}
				
				if(is_string($result['size']))
				{
					$size = \kekse\unit($result['size'], true, true);
					
					switch($size[1])
					{
						case 'px':
						case 'pt':
							$result['unit'] = $size[1];
							break;
					}
					
					$result['size'] = $size[0];
				}
				
				if($result['size'] < 3 || $result['size'] > 512)
				{
					draw_error('\'?size\' exceeds limit (3..512)');
					return null;
				}

				//
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

				//
				if($result['fg'] === null)
				{
					$result['fg'] = get_config('fg');
				}

				if($result['bg'] === null)
				{
					$result['bg'] = get_config('bg');
				}

				$result['fg'] = \kekse\color($result['fg'], true);

				if($result['fg'] === null)
				{
					if($_die)
					{
						draw_error('\'?fg\' is no valid color');
						return null;
					}
					
					if(($result['fg'] = \kekse\color(get_config('fg'), true)) === null)
					{
						draw_error('Default FG color is not valid (used as fallback)');
						return null;
					}
				}

				$result['bg'] = \kekse\color($result['bg'], true);

				if($result['bg'] === null)
				{
					if($_die)
					{
						draw_error('\'?bg\' is no valid color');
						return null;
					}
					
					if(($result['bg'] = \kekse\color(get_config('bg'), true)) === null)
					{
						draw_error('Default BG color is not valid (used as fallback)');
						return null;
					}
				}
				
				//
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
				else if($result['y'] > 512 || $result['y'] < -512)
				{
					if($_die)
					{
						draw_error('\'?y\' exceeds limit');
						return null;
					}
					
					$result['y'] = get_config('y');
				}

				//
				if(!is_bool($result['aa']))
				{
					$result['aa'] = get_config('aa');
				}

				//
				if(($result['angle'] = check_angle($result['angle'])) === null)
				{
					if($_die)
					{
						draw_error('\'?angle\' is invalid');
						return null;
					}
					else if(($result['angle'] = check_angle(get_config('angle'))) === null)
					{
						draw_error('Invalid \'angle\' setting (after \'?angle\' was invalid or not specified');
						return null;
					}
				}

				//
				return $result;
			}

			//
			function px2pt($_px)
			{
				return ($_px * 0.75);
			}

			function pt2px($_pt)
			{
				return ($_pt / 0.75);
			}

			//
			function draw_text($_text, $_font, $_size = null, $_unit = null, $_fg = null, $_bg = null, $_h = null, $_v = null, $_x = null, $_y = null, $_aa = null, $_type = null, $_angle = null)
			{
				//
				if(get_state('sent'))
				{
					draw_error('Header already sent (unexpected here)');
					return null;
				}
				else if(func_num_args() === 2)
				{
					if(is_array($_font))
					{
						$_angle = $_font['angle'];
						$_type = $_font['type'];
						$_aa = $_font['aa'];
						$_y = $_font['y'];
						$_x = $_font['x'];
						$_v = $_font['v'];
						$_h = $_font['h'];
						$_bg = $_font['bg'];
						$_fg = $_font['fg'];
						$_unit = $_font['unit'];
						$_size = $_font['size'];
						$_font = $_font['font'];
					}
					else
					{
						draw_error('Invalid arguments to draw_text()');
						return null;
					}
				}

				//
				$px = 0;
				$pt = 0;

				switch($_unit)
				{
					case 'px':
						$px = $_size;
						$pt = px2pt($px);
						break;
					case 'pt':
						$pt = $_size;
						$px = pt2px($pt);
						break;
					default:
						draw_error('Invalid unit [ px, pt ]');
						return null;
				}

				//
				$measure = imagettfbbox($pt, 0, $_font, $_text);
				$textWidth = $textHeight = null;

				if(($measure[4] - $measure[6]) === ($measure[2] - $measure[0]))
				{
					$textWidth = ($measure[4] - $measure[6]);
				}
				else
				{
					throw new \Exception('debug width');
				}
				
				if(($measure[1] - $measure[7]) === ($measure[3] - $measure[5]))
				{
					$textHeight = ($measure[1] - $measure[7]);
				}
				else
				{
					throw new \Exception('debug height');
				}

				$vertical = ($textHeight + $measure[7]);
				$horizontal = $measure[0];

				//
				$image = null;

				//
				$createImage = function() use (&$textWidth, &$textHeight, &$image, &$_text, &$_aa, &$_fg, &$_bg, &$_font, &$_type, &$_h, &$_v, &$_x, &$_y, &$pt, &$px, &$_angle, &$vertical, &$horizontal)
				{
					//
					$drawImage = function() use(&$image, &$_type)
					{
						$result = null;
						
						switch(strtolower($_type))
						{
							case 'png':
								if($result = send_header('image/png'))
								{
									imagepng($image);
								}
								break;
							case 'jpg':
								if($result = send_header('image/jpeg'))
								{
									imagejpeg($image);
								}
								break;
						}
						
						imagedestroy($image);
						return $result;
					};
					
					$rotateImage = (($_angle %= 360) === 0 ? null : function() use(&$image, &$_angle, &$bg)
					{
						//
						$result = imagerotate($image, $_angle, $bg);

						if(!$result)
						{
							return null;
						}

						return $result;
					});

					//
					$add = 4;
					$x = (-$horizontal * 0.75);
					$y = ($textHeight - $vertical) + $add / 2;
					$textHeight += $add;

					//
					$scale = ($px / $textHeight);

					$textHeight *= $scale;
					$textWidth *= $scale;
					$px *= $scale;
					$pt *= $scale;
					$y *= $scale;
					$x *= $scale;
					
					if(($textWidth += ($_h * 2)) < 1)
					{
						$textWidth = 1;
					}
					
					if(($textHeight += ($_v * 2)) < 1)
					{
						$textHeight = 1;
					}
					
					$x += ($_x + $_h);
					$y += ($_y + $_v);

					//
					$image = imagecreatetruecolor($textWidth, $textHeight);
					imagesavealpha($image, true);
					imageantialias($image, $_aa);
					imagealphablending($image, true);

					//
					$bg = imagecolorallocatealpha($image, $_bg[0], $_bg[1], $_bg[2], $_bg[3]);
					imagefill($image, 0, 0, $bg);
					
					//
					$fg = imagecolorallocatealpha($image, $_fg[0], $_fg[1], $_fg[2], $_fg[3]);

					if(!$_aa)
					{
						if(($fg = -$fg) === 0)
						{
							$fg = -1;
						}
					}

					//
					imagettftext($image, $pt, 0, $x, $y, $fg, $_font, $_text);
					
					//
					if($rotateImage !== null)
					{
						if(($image = $rotateImage()) === null)
						{
							draw_error('Image couldn\'t be rotated');
							return null;
						}
					}

					//
					return $drawImage();
				};
				
				//
				$result = $createImage();
				
				//
				if(!$result)
				{
					draw_error('Header couldn\'t be sent');
					return null;
				}
				
				return $result;
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

//
namespace kekse;

function counter(... $_args)
{
	return \kekse\counter\counter(... $_args);
}

?>
