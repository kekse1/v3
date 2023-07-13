<?php

//
namespace kekse\counter;

//
//change your default configuration here.. that should be all to change..!
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
	'radix' => 10,//3,
	'clean' => true,
	'limit' => 32768,
	'fonts' => 'fonts/',
	'font' => 'Candara',
	'size' => '56px',
	'min' => false,//true,
	'unit' => 'px',
	'fg' => '0,0,0',//'120,130,40',
	'bg' => '255,255,255,0',
	'angle' => 0.0,//'0.15rad',
	'x' => 0.0,
	'y' => 0.0,
	'h' => 0.0,
	'v' => 0.0,
	'type' => 'png',
	'privacy' => false,
	'hash' => 'sha3-256',
	'error' => '-',
	'none' => '/',
	'modules' => null//'modules/'
);

//
define('KEKSE_COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');
define('COUNTER_VERSION', '4.0.8');
define('COUNTER_WEBSITE', 'https://github.com/kekse1/count.php/');

//
define('KEKSE_ANSI', true); //colors, styles, etc.. @ CLI. _only_ if stdout/stderr is a tty! ^_^
define('KEKSE_LIMIT_TTY', 40); //in cli mode, when showing a list of files, limit output to this amount of lines..
define('KEKSE_LIMIT_TTY_PROMPT', true); //show a prompt to ask user whether to continue or not?
define('KEKSE_LIMIT_STRING', 224); //reasonable maximum length for (most) strings.. e.g. path components
// some excludes, mainly for `\kekse\delete()`..
define('KEKSE_KEEP', true); //don't delete '.keep'
define('KEKSE_KEEP_HTACCESS', true); //don't delete any '.htaccess'
define('KEKSE_KEEP_HIDDEN', true); //don't delete any '.' prefixed file
// maybe you want to use my `kekse` extensions (etc.) only, without the `kekse\counter` itself?
define('KEKSE_RAW', false); //will not call the main/base `counter()` function, and the whole `kekse\counter` won't be declared. ^_^
// normally this shouldn't be changed (but it's only an aesthetic thing..); BUT they need to be only one character [long], never longer!
define('COUNTER_VALUE_CHAR', '~');
define('COUNTER_DIR_CHAR', '+');
define('COUNTER_FILE_CHAR', '-');
define('COUNTER_CONFIG_CHAR', '@');
//problems with different httpd and console user? set to 0777/0666. but it's really insecure! use 0700/0600!
define('KEKSE_MODE_DIR', 0700); //file mode; set to (null) to never change (by default)
define('KEKSE_MODE_FILE', 0600); //dir mode; set to (null) to never change (by default)

//
define('KEKSE_CLI', (php_sapi_name() === 'cli'));

//
//maybe rather dynamic, in reading out the `modules` directory!?? //(much) TODO!/
const MODULES = array(
	'statistics',
	'notifications',
	'events'
);

// e.g. 'securityTest()' needs to know 'bout all used paths (stored in the $STATE)
const PATHS = array(
	array('path', false),
	array('log', true),
	array('fonts', false),
	array('modules', false)
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
	'modules' => null,

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
	'auto' => array('types' => [ 'boolean', 'integer', 'NULL' ], 'min' => 0),
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
	'size' => array('types' => [ 'double', 'integer', 'string' ], 'min' => 3, 'max' => 512, 'test' => true),
	'min' => array('types' => [ 'boolean' ]),
	'unit' => array('types' => [ 'string' ], 'min' => 2, 'max' => 2, 'test' => true),
	'fg' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'bg' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'angle' => array('types' => [ 'double', 'integer', 'string' ], 'test' => true),
	'x' => array('types' => [ 'double', 'integer' ], 'min' => -512, 'max' => 512),
	'y' => array('types' => [ 'double', 'integer' ], 'min' => -512, 'max' => 512),
	'h' => array('types' => [ 'double', 'integer' ], 'min' => -512, 'max' => 512),
	'v' => array('types' => [ 'double', 'integer' ], 'min' => -512, 'max' => 512),
	'type' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'privacy' => array('types' => [ 'boolean' ]),
	'hash' => array('types' => [ 'string' ], 'min' => 1, 'test' => true),
	'error' => array('types' => [ 'string', 'NULL' ]),
	'none' => array('types' => [ 'string' ]),
	'modules' => array('types' => [ 'string', 'NULL' ], 'test' => true)
);

//
if(KEKSE_CLI)
{
	$ARGC = null;
	$ARGV = null;
	
	if(is_int($argc) && isset($argv))
	{
		$ARGC = $argc;
		$ARGV = $argv;
	}
	else if(is_int($_SERVER['argc']) && isset($_SERVER['argv']))
	{
		$ARGC = $_SERVER['argc'];
		$ARGV = $_SERVER['argv'];
	}
	else if(is_int($GLOBALS['argc']) && isset($GLOBALS['argv']))
	{
		$ARGC = $GLOBALS['argc'];
		$ARGV = $GLOBALS['argv'];
	}
	else
	{
		fprintf(STDERR, ' >> WARNING: CLI mode active, but missing `argc` and/or `argv[]`..! :-/' . PHP_EOL);
		exit(127);
	}
	
	$GLOBALS['KEKSE_ARGC'] = $ARGC;
	$GLOBALS['KEKSE_ARGV'] = $ARGV;
	unset($ARGC);
	unset($ARGV);
}

//
namespace kekse;

//
function endsWith($_haystack, $_needle, $_case_sensitive = true)
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

function startsWith($_haystack, $_needle, $_case_sensitive = true)
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
		return false;
	}
	else if($_path === '')
	{
		return '.';
	}
	
	$len = strlen($_path);
	$abs = ($_path[0] === '/');
	$dir = ($_path[$len - 1] === '/');
	$split = explode('/', $_path);
	$result = array();
	$minus = 0;
	$item = '';
	
	while(count($split) > 0)
	{
		$item = array_shift($split);
		
		if($item === '')
		{
			continue;
		}
		else if(strlen($item) > KEKSE_LIMIT_STRING)
		{
			continue;
		}
		else switch($item)
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

function joinPath(... $_args)
{
	if(count($_args) === 0)
	{
		return false;
	}

	$len = count($_args);
	$result = '';
	
	for($i = 0; $i < $len; ++$i)
	{
		if(!is_string($_args[$i]))
		{
			throw new \Error('Invalid argument[' . $i . '] (no non-empty String)');
		}
		else if($_args[$i] !== '')
		{
			$result .= $_args[$i] . '/';
		}
	}
	
	if($result !== '')
	{
		$result = substr($result, 0, -1);
	}
	
	return normalize($result);
}

function checkPath($_path, $_file, $_source = null, $_delete = false, $_create = true, $_log = true, $_mode_dir = KEKSE_MODE_DIR, $_mode_file = KEKSE_MODE_FILE)
{
	//
	$log = (function_exists('\kekse\counter\logError') && !!$_log);

	//
	if(!is_string($_path) || $_path === '')
	{
		return false;
	}
	else if(($_path = normalize($_path)) === '/')
	{
		if($log)
		{
			\kekse\counter\logError('Path may not be the root of it all..', $_source, $_path, false);
		}
		
		return false;
	}
	
	//
	$exists = file_exists($_path);
	$available = true;
	
	//
	if(!$exists)
	{
		$dir = dirname($_path);

		if(file_exists($dir))
		{
			if(!is_dir($dir))
			{
				if($log)
				{
					\kekse\counter\logError('Base directory is no directory', $_source, $dir, false);
				}
			
				return false;
			}
		}
		else if($_create)
		{
			if(! mkdir($dir, $_mode_dir, true))
			{
				if($log)
				{
					\kekse\counter\logError('Base directory doesn\'t exist, and creation failed', $_source, $dir, false);
				}

				return false;
			}
		}
		else
		{
			if($log)
			{
				\kekse\counter\logError('Base directory doesn\'t exist', $_source, $dir, false);
			}
			
			return false;
		}
	}

	//
	if($_file)
	{
		if($exists)
		{
			if(!is_file($_path))
			{
				if($_delete)
				{
					if(\kekse\delete($_path, true, false))
					{
						if($_create)
						{
							if(touch($_path))
							{
								if($log)
								{
									\kekse\counter\logError('Path was no file, but could be deleted and then created again as file', $_source, $_path, false);
								}
							}
							else
							{
								if($log)
								{
									\kekse\counter\logError('Path was no file, and could be deleted, but not created again as file', $_source, $_path, false);
								}
								
								return false;
							}
						}
					}
					else
					{
						if($log)
						{
							\kekse\counter\logError('Path is no file, and couldn\'t even be deleted', $_source, $_path, false);
						}
						
						return false;
					}
				}
				else
				{
					if($log)
					{
						\kekse\counter\logError('Path is not a file', $_source, $_path, false);
					}
					
					return false;
				}
			}
		}
		else if($_create)
		{
			if(!touch($_path))
			{
				if($log)
				{
					\kekse\counter\logError('File doesn\'t exist, and couldn\'t be created', $_source, $_path, false);
				}

				$available = false;
			}
		}
		else
		{
			$available = false;
		}

		//
		if($available && is_int($_mode_file))
		{
			chmod($_path, $_mode_file);
		}
	}
	else
	{
		if($exists)
		{
			if(!is_dir($_path))
			{
				if($_delete)
				{
					if(\kekse\delete($_path, true, false))
					{
						if($_create)
						{
							if(mkdir($_path, $_mode_dir, true))
							{
								if($log)
								{
									\kekse\counter\logError('Path was no directory, but could be re-created', $_source, $_path, false);
								}
							}
							else
							{
								if($log)
								{
									\kekse\counter\logError('Path was no directory, and could be deleted, but not re-created again', $_source, $_path, false);
								}
								
								return false;
							}
						}
					}
					else
					{
						if($log)
						{
							\kekse\counter\logError('Path is no directory, and couldn\'t even be deleted', $_source, $_path, false);
						}
						
						return false;
					}
				}
				else
				{
					if($log)
					{
						\kekse\counter\logError('Path is not a directory', $_source, $_path, false);
					}
					
					return false;
				}
			}
		}
		else if($_create)
		{
			if(!mkdir($_path, $_mode_dir, true))
			{
				if($log)
				{
					\kekse\counter\logError('Directory doesn\'t exist, and also couldn\'t be created', $_source, $_path, false);
				}

				$available = false;
			}
		}
		else
		{
			$available = false;
		}

		//
		if($available && (is_int($_mode_dir) || is_int($_mode_file)))
		{
			if(KEKSE_CLI)
			{
				$traverse = function($_p, $_m = 1, $_d = 0) use(&$traverse, $_path, $_mode_dir, $_mode_file)
				{
					if($_d > $_m)
					{
						return false;
					}
					else if(is_int($_mode_dir))
					{
						chmod($_p, $_mode_dir);
					}

					$handle = opendir($_p);

					if($handle === false)
					{
						return false;
					}
					else while($sub = readdir($handle))
					{
						if($sub === '.' || $sub === '..')
						{
							continue;
						}

						$p = joinPath($_p, $sub);

						if(is_link($p))
						{
							continue;
						}
						else if(is_dir($p))
						{
							$traverse($p, $_m, $_d + 1);
						}
						else if(is_int($_mode_file))
						{
							chmod($p, $_mode_file);
						}
					}

					closedir($handle);
					return true;
				};

				$traverse($_path);
			}
			else if(!KEKSE_CLI && is_int($_mode_dir))
			{
				chmod($_path, $_mode_dir);
			}
		}
	}

	return true;
}

//
function readFile($_path, ... $_args)
{
	$size = filesize($_path);
	
	if($size === false)
	{
		return false;
	}
	else if($size === 0)
	{
		return '';
	}
	else if($size > KEKSE_LIMIT_STRING)
	{
		return false;
		return file_get_contents($_path, false, null, 0, KEKSE_LIMIT_STRING, ... $_args);
	}

	return file_get_contents($_path);//, false, null, 0, $size, ... $_args);
}

function writeFile($_path, $_value, ... $_args)
{
	if(!is_string($_value))
	{
		return false;
	}
	else if(\strlen($_value) > KEKSE_LIMIT_STRING)
	{
		return false;
		//$_value = limit($_value, KEKSE_LIMIT_STRING);
	}
	
	return file_put_contents($_path, $_value, ... $_args);
}

function readInt($_path, ... $_args)
{
	$result = readFile($_path);
	
	if($result === false)
	{
		return false;
	}
	else if($result === '')
	{
		$result = 0;
	}
	else
	{
		$result = (int)$result;
	}
	
	return $result;
}

function writeInt($_path, $_int, ... $_args)
{
	if(\kekse\is_number($_int))
	{
		$_int = (string)(int)$_int;
	}
	else if(!is_string($_int))
	{
		return false;
	}
	
	return writeFile($_path, $_int, ... $_args);
}

//
namespace kekse;

//
function is_number($_item)
{
	return (is_int($_item) || is_float($_item));
}

//
function limit($_string, $_length = KEKSE_LIMIT_STRING)
{
	return substr($_string, 0, $_length);
}

//
function timestamp($_diff = null)
{
	if(!is_int($_diff))
	{
		return time();
	}
	
	return (time() - $_diff);
}

function removeWhiteSpaces($_string)
{
	return removeBinary($_string, true);
}

function removeBinary($_string, $_space = false)
{
	if(!is_string($_string))
	{
		return false;
	}
	else if($_string === '')
	{
		return $_string;
	}
	
	$len = strlen($_string);
	$result = '';
	$byte;
	
	for($i = 0; $i < $len; ++$i)
	{
		if(($byte = ord($_string[$i])) < 32 || $byte === 127)
		{
			continue;
		}
		else if($_space && $byte === 33)
		{
			continue;
		}
		
		$result .= chr($byte);
	}
	
	return $result;
}

function secure($_string, $_lower_case = false)
{
	if(!is_string($_string) || $_string === '')
	{
		return null;
	}
	
	$l = strlen($_string);
	$result = '';
	$last = '';
	$byte = 0;
	$add = '';
	$len = 0;

	for($i = 0; $i < $l; ++$i)
	{
		if(($byte = ord($_string[$i])) >= 97 && $byte <= 122)
		{
			$add = chr($byte);
		}
		else if($byte >= 65 && $byte <= 90)
		{
			if($_lower_case)
			{
				$add = chr($byte + 32);
			}
			else
			{
				$add = chr($byte);
			}
		}
		else if($byte >= 48 && $byte <= 57)
		{
			$add = chr($byte);
		}
		else if($byte === 46)
		{
			if($result === '')
			{
				$add = '';
			}
			else if($last === '.')
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
			if($result === '')
			{
				$add = '';
			}
			else if($last === '/')
			{
				$add = '';
			}
			else
			{
				$add = '/';
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
		else if($byte === 61)
		{
			$add = '=';
		}
		else
		{
			$add = '';
		}

		if($add !== '')
		{
			$result .= $add;
			$last = $add;

			if(++$len > KEKSE_LIMIT_STRING)
			{
				break;
			}
		}
	}

	if($result !== '')
	{
		$rem = 0;
		
		while($rem < $len && $result[$rem] === '.' || $result[$rem] === COUNTER_VALUE_CHAR || $result[$rem] === COUNTER_DIR_CHAR || $result[$rem] === COUNTER_FILE_CHAR || $result[$rem] === COUNTER_CONFIG_CHAR)
		{
			++$rem;
		}
		
		if($rem > 0)
		{
			$result = substr($result, $rem);
			$len -= $rem;
		}
		
		if($result !== '')
		{
			$rem = 0;
			$pos;

			while(($pos = ($len - 1 - $rem)) >= 0 && $result[$pos] === '.')// || $result[$pos] === '+')
			{
				++$rem;
			}
			
			if($rem > 0)
			{
				$result = substr($result, 0, -$rem);
			}
		}
	}

	if($result === '')
	{
		return null;
	}
	
	return $result;
}

function secureHost($_string)
{
	return secure($_string, true);
}

function securePath($_string)
{
	return secure($_string, false);
}

//
function delete($_path, $_depth = 0, $_extended = false, $_depth_current = 0)
{
	//
	if(is_string($_path) && $_path !== '')
	{
		if(! is_link($_path))
		{
			$real = realpath($_path);
			
			if($real === false)
			{
				if($_extended === true)
				{
					return array(0, 0, 0, 0);
				}
				else if($_extended === null)
				{
					return array(array(), array(), array(), array());
				}
				
				return false;
			}
			else if($real === '/')
			{
				if($_extended === true)
				{
					return array(1, 0, 0, 1);
				}
				else if($_extended === null)
				{
					return array(array($_path), array(), array(), array($_path));
				}

				return null;
			}
		}
	}
	else if($_extended === true)
	{
		return array(0, 0, 0, 0);
	}
	else if($_extended === null)
	{
		return array(array(), array(), array(), array());
	}
	else
	{
		return false;
	}
	
	//
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

	//
	$basename = basename($_path);

	//
	if(KEKSE_KEEP_HIDDEN && $basename[0] === '.')
	{
		if($_extended === true)
		{
			return array(1, 0, 0, 1);
		}
		else if($_extended === null)
		{
			return array(array($_path), array(), array(), array($_path));
		}
		
		return null;
	}
	else if(KEKSE_KEEP_HTACCESS && $basename === '.htaccess')
	{
		if($_extended === true)
		{
			return array(1, 0, 0, 1);
		}
		else if($_extended === null)
		{
			return array(array($_path), array(), array(), array($_path));
		}
		
		return null;
	}
	else if(KEKSE_KEEP && $basename === '.keep')
	{
		if($_extended === true)
		{
			return array(1, 0, 0, 1);
		}
		else if($_extended === null)
		{
			return array(array($_path), array(), array(), array($_path));
		}
		
		return null;
	}
	else if($_depth !== null && $_depth_current > $_depth)
	{
		if($_extended === true)
		{
			return array(1, 0, 0, 1);
		}
		else if($_extended === null)
		{
			return array(array($_path), array(), array(), array($_path));
		}
		
		return null;
	}
	
	if(is_link($_path))
	{
		$d = 0;
		$f = 0;
		
		if(unlink($_path))
		{
			++$d;
		}
		else
		{
			++$f;
		}
		
		if($_extended === true)
		{
			return array(1, $d, $f, 0);
		}
		else if($_extended === null)
		{
			return array(array($_path), ($d === 0 ? array() : array($_path)), ($f === 0 ? array() : array($_path)), array());
		}
		
		return ($f === 0);
	}
	else if(is_dir($_path))
	{
		//
		if($_depth !== null && $_depth_current >= $_depth)
		{
			$d = 0;
			$f = 0;

			if(rmdir($_path))
			{
				++$d;
			}
			else
			{
				++$f;
			}

			if($_extended === true)
			{
				return array(1, $d, $f, 0);
			}
			else if($_extended === null)
			{
				return array(array($_path), ($d === 0 ? array() : array($_path)), ($f === 0 ? array() : array($_path)), array());
			}

			return ($f === 0);
		}

		//
		$handle = opendir($_path);
		
		if($handle === false)
		{
			if($_extended === true)
			{
				return array(1, 0, 1, 0);
			}
			else if($_extended === null)
			{
				return array(array($_path), array(), array($_path), array());
			}

			return false;
		}

		//
		$total = 0;
		$deleted = 0;
		$failed = 0;
		$ignored = 0;

		if($_extended === null)
		{
			$total = array();
			$deleted = array();
			$failed = array();
			$ignored = array();
		}

		//
		while($sub = readdir($handle))
		{
			if($sub !== '.' && $sub !== '..')
			{
				$res = delete(joinPath($_path, $sub), $_depth, ($_extended === null ? null : true), $_depth_current + 1);

				if($_extended === null)
				{
					array_push($total, ... $res[0]);
					array_push($deleted, ... $res[1]);
					array_push($failed, ... $res[2]);
					array_push($ignored, ... $res[3]);
				}
				else
				{
					$total += $res[0];
					$deleted += $res[1];
					$failed += $res[2];
					$ignored += $res[3];
				}
			}
		}

		//
		closedir($handle);

		//
		if($_extended === null)
		{
			array_push($total, $_path);
		}
		else
		{
			++$total;
		}
		
		//
		$f = ($_extended === null ? count($failed) : $failed);
		$i = ($_extended === null ? count($ignored) : $ignored);
		
		//
		if($f === 0 && $i === 0)
		{
			if(rmdir($_path))
			{
				if($_extended === false)
				{
					return true;
				}
				else if($_extended === null)
				{
					array_push($deleted, $_path);
				}
				else
				{
					++$deleted;
				}
			}
			else
			{
				if($_extended === false)
				{
					return false;
				}
				else if($_extended === null)
				{
					array_push($failed, $_path);
				}
				else
				{
					++$failed;
				}
			}
		}
		else if($_extended === false)
		{
			return ($ignored > 0 ? null : false);
		}
		else if($i > 0)
		{
			if($_extended === null)
			{
				array_push($ignored, $_path);
			}
			else
			{
				++$ignored;
			}
		}
		else if($_extended === null)
		{
			array_push($failed, $_path);
		}
		else
		{
			++$failed;
		}

		return array($total, $deleted, $failed, $ignored);
	}

	$d = 0;
	$f = 0;

	if(unlink($_path))
	{
		++$d;
	}
	else
	{
		++$f;
	}

	if($_extended === true)
	{
		return array(1, $d, $f, 0);
	}
	else if($_extended === null)
	{
		return array(array($_path), ($d === 0 ? array() : array($_path)), ($f === 0 ? array() : array($_path)), array());
	}
	
	return ($f === 0);	
}

function getParam($_key, $_numeric = false, $_float = false, $_strict = true, $_lower_case = false, $_fallback = true)
{
	if(!is_string($_key) || $_key === '')
	{
		return null;
	}
	else if(strlen($_key) > KEKSE_LIMIT_STRING)
	{
		return null;
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
	
	if(strlen($value) > KEKSE_LIMIT_STRING)
	{
		return null;
	}
	else
	{
		$value = removeWhiteSpaces($value);
	}

	if($_numeric === null) switch($value[0])
	{
		case '0':
		case 'n':
		case 'N':
		case 'f':
		case 'F':
			return false;
		case '1':
		case 'y':
		case 'Y':
		case 't':
		case 'T':
			return true;
		default:
			if($_strict)
			{
				return null;
			}
			break;
	}
	
	$len = strlen($value);
	$result = '';
	$last = '';
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
		$len -= $remove;
	}

	$l = 0;
	
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
			
			if($_lower_case)
			{
				$set = chr($byte + 32);
			}
			else
			{
				$set = chr($byte);
			}
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
			
			if($last === '.')
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
			$set = '';
		}

		if($set !== '')
		{
			$result .= $set;
			$last = $set;
			++$l;
		}
	}

	if($result === '')
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
			else if($result[$l - 1] === '.')
			{
				$result = substr($result, 0, -1);
				$hadPoint = false;
			}

			if($_float)
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
function unit($_string, $_float = false)
{
	$len = null;
	
	if($_string === '' || (($len = strlen($_string)) > KEKSE_LIMIT_STRING))
	{
		return array(($_float ? (float)0.0 : (int)0), '');
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
		if(ord($_string[$i]) <= 32)
		{
			++$rem;
		}
		else if($_string[$i] === '-' || $_string[$i] === '+')
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
	
	$s = 0;
	
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
		else if($byte >= 65 && $byte <= 90)
		{
			$unit .= chr($byte + 32);
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
			++$s;
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
				++$s;
			}
		}
	}

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
		
		if($_float)
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
	
	return array($size, $unit);
}

//
function color(... $_args)
{
	return color\color(... $_args);
}

function px2pt($_px)
{
	return ($_px * 0.75);
}

function pt2px($_pt)
{
	return ($_pt / 0.75);
}

//
namespace kekse\color;

//
function color($_string, $_gd = null)
{
	$len = null;
	
	if(!is_bool($_gd))
	{
		$_gd = (!KEKSE_CLI && extension_loaded('gd'));
	}

	if(!is_string($_string))
	{
		if(is_array($_string) && color_check_array($_string))
		{
			return color_fix($_string, $_gd);
		}
		
		return null;
	}
	else if($_string === '')
	{
		return null;
	}
	else if(($len = strlen($_string)) > KEKSE_LIMIT_STRING)
	{
		return null;
	}
	else
	{
		$_string = \kekse\removeWhiteSpaces($_string);
	}

	//
	if(substr($_string, 0, 5) === 'rgba(')
	{
		$_string = substr($_string, 5);
		$len -= 5;
	}
	else if(substr($_string, 0, 4) === 'rgb(')
	{
		$_string = substr($_string, 4);
		$len -= 4;
	}

	if($_string[$len - 1] === ')')
	{
		$_string = substr($_string, 0, -1);
		--$len;
	}

	$result = null;

	if(colorIsHexadecimal($_string))
	{
		$result = colorHexadecimal($_string);
	}
	else if(colorIsList($_string))
	{
		$result = colorList($_string);
	}

	if($result !== null)
	{
		$result = colorFix($result, $_gd);
	}
	
	return $result;
}

//
function colorFix($_array, $_gd = null)
{
	if(!is_bool($_gd))
	{
		$_gd = (!KEKSE_CLI && extension_loaded('gd'));
	}
	
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
		$_array = colorFixGD($_array);
	}
	
	return $_array;
}

function colorFixGD($_item, $_int = false)
{
	$array = null;
	$value = $_item;

	if(is_array($_item))
	{
		$len = count($array = $value);
		
		if($len === 0 || $len > 4)
		{
			return null;
		}
		else if($len === 3)
		{
			$value = 1.0;
		}
		else
		{
			$value = $array[3];
		}
	}

	if(is_int($value))
	{
		$value = (float)($value / 255);
	}
	else if(! is_float($value))
	{
		return null;
	}

	$value = (int)(127 - ($value * 127));

	if($array !== null)
	{
		$array[3] = $value;
		return $array;
	}
	
	return $value;
}

//
function colorCheckArray($_array)
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

function colorList($_string)
{
	//
	if(!is_string($_string))
	{
		if(is_array($_string))
		{
			$len = count($_string);
			
			if($len === 3 || $len === 4)
			{
				return $_string;
			}
		}
		
		return null;
	}
	else if($_string === '')
	{
		return null;
	}
	else if(strlen($_string) > KEKSE_LIMIT_STRING)
	{
		return null;
	}
	else if(strpos($_string, ',') === false)
	{
		return null;
	}
	else
	{
		$_string = \kekse\removeWhiteSpaces($_string);
	}
	
	//
	$split = explode(',', $_string);
	$result = array();
	$byte = null;
	$item = '';

	//
	$len = count($split);
	
	if($len === 3)
	{
		$split[3] = '1';
	}
	else if($len !== 4)
	{
		return null;
	}
	
	//
	for($i = 0; $i < 3; ++$i)
	{
		if(is_numeric($split[$i]))
		{
			$result[$i] = (int)str_replace('.', '', $split[$i]);
		}
		else
		{
			return null;
		}
	}
	
	$result[3] = (double)$split[3];
	return $result;
}

function colorSymbolHexadecimal($_char)
{
	$byte = null;
	
	if(($byte = ord($_char)) >= 48 && $byte <= 57)
	{
		return $_char;
	}
	else if($byte >= 97 && $byte <= 102)
	{
		return $_char;
	}
	else if($byte >= 65 && $byte <= 70)
	{
		return chr($byte + 32);
	}
	
	return '';
}

function colorIsList($_string)
{
	if(!is_string($_string) || $_string === '')
	{
		return false;
	}
	else if(strlen($_string) > KEKSE_LIMIT_STRING)
	{
		return false;
	}
	else if(strpos($_string, '#') !== false)
	{
		return false;
	}
	else if(strpos($_string, ',') === false)
	{
		return false;
	}
	
	$byte = null;
	$len = strlen($_string);

	for($i = 0; $i < $len; ++$i)
	{
		if(($byte = ord($_string[$i])) === 46 || $byte === 44)
		{
			continue;
		}
		else if($byte <= 32)
		{
			continue;
		}
		else if(! ctype_digit(chr($byte)))
		{
			return false;
		}
	}

	return true;
}

function colorIsHexadecimal($_string)
{
	$len = null;
	
	if(!is_string($_string) || $_string === '')
	{
		return false;
	}
	else if(($len = strlen($_string)) > KEKSE_LIMIT_STRING)
	{
		return false;
	}
	else if(strpos($_string, ',') !== false)
	{
		return false;
	}
	else
	{
		$_string = \kekse\removeWhiteSpaces($_string);
	}
	
	if($_string[0] === '#')
	{
		$_string = substr($_string, 1);
		--$len;
	}

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
		if(colorSymbolHexadecimal($_string[$i]) === '')
		{
			return false;
		}
	}

	return true;
}

//
namespace kekse;

//
$consoleCondition = (KEKSE_CLI || KEKSE_RAW);

function prompt(... $_args)
{
	global $consoleCondition;
	
	if($consoleCondition)
	{
		return console\prompt(... $_args);
	}
	
	return false;
}

function log(... $_args)
{
	global $consoleCondition;

	if($consoleCondition)
	{
		return console\log(... $_args);
	}

	return false;
}

function info(... $_args)
{
	global $consoleCondition;
	
	if($consoleCondition)
	{
		return console\info(... $_args);
	}

	return false;
}

function error(... $_args)
{
	global $consoleCondition;

	if($consoleCondition)
	{
		return console\error(... $_args);
	}

	return false;
}

function warn(... $_args)
{
	global $consoleCondition;
	
	if($consoleCondition)
	{
		return console\warn(... $_args);
	}
	
	return false;
}

function debug(... $_args)
{
	global $consoleCondition;
	
	if($consoleCondition)
	{
		return console\debug(... $_args);
	}
	
	return false;
}

//
namespace kekse\console;

if($consoleCondition)
{
	//
	function getStream($_item)
	{
		if(is_int($_item))
		{
			if($_item < -5 || $_item > 2)
			{
				return null;
			}
			else switch($_item)
			{
				case 0: return STDIN;
				case 1: return STDOUT;
				case 2: return STDERR;
				case -1: return STDOUT;
				case -2: return STDOUT;
				case -3: return STDERR;
				case -4: return STDERR;
				case -5: return STDERR;
				default: return null;
			}
		}
		else if(is_string($_item)) switch(strtolower($_item))
		{
			case 'stdin': return STDIN;
			case 'stdout': return STDOUT;
			case 'stderr': return STDERR;
			case 'log': return STDOUT;
			case 'info': return STDOUT;
			case 'warn': return STDERR;
			case 'error': return STDERR;
			case 'debug': return STDERR;
			default: return null;
		}
		else switch($_item)
		{
			case STDIN: return STDIN;
			case STDOUT: return STDOUT;
			case STDERR: return STDERR;
			default: return null;
		}
		
		return null;
	}

	//
	function withAnsi(... $_args)
	{
		return ansi\withAnsi(... $_args);
	}
	
	//
	function insertPrefix($_string, $_color = null, $_stream = true)
	{
		if(! is_string($_string))
		{
			return null;
		}
		else if(ansi\withAnsi($_stream))
		{
			$seq = (ansi\ESCAPE . ansi\BOLD);

			if(is_string($_color)) switch(strtolower($_color))
			{
				case 'red':
					$seq .= (ansi\ESCAPE . ansi\RED);
					break;
				case 'green':
					$seq .= (ansi\ESCAPE . ansi\GREEN);
					break;
				case 'yellow':
					$seq .= (ansi\ESCAPE . ansi\YELLOW);
					break;
				case 'blue':
					$seq .= (ansi\ESCAPE . ansi\BLUE);
					break;
				case 'magenta':
					$seq .= (ansi\ESCAPE . ansi\MAGENTA);
					break;
				default:
					$seq .= '';
					break;
			}

			if($seq !== null)
			{
				return ($seq . ' >> ' . ansi\ESCAPE . ansi\RESET . $_string);
			}
		}

		return (' >> ' . $_string);
	}

	//
	function prompt($_format, ... $_args)
	{
		//
		$_string = insertPrefix(ansi\bold(sprintf($_format, ... $_args), true, 2), 'yellow', STDERR);
		$len = \kekse\strlen($_string, true);
		$s = str_pad('', $len, ' ');

		//
		$confirm = function() use (&$_string, &$s) {
			fprintf(STDERR, $_string);
			$res = readline($s);
			
			if($res === '')
			{
				return null;
			}
			else switch(strtolower(substr($res, 0, 3)))
			{
				case 'y': case '1': case '+': case 'yes': return true;
				case 'n': case '0': case '-': case 'no': return false;
			}
			
			return null;
		};

		//		
		$result = null;
		
		while($result === null)
		{
			$result = $confirm();
		}
		
		return $result;
	}
	
	//
	function consoleOutput(&$_format, &$_args)
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
		[ $eol, $_format, $_args ] = consoleOutput($_format, $_args);
		$result = '';
		
		if(is_string($_format) && $_format !== '')
		{
			$result = insertPrefix(sprintf($_format, ... $_args), null, STDOUT);
		}

		while(--$eol >= 0)
		{
			$result .= PHP_EOL;
		}

		return fwrite(getStream('log'), $result);
	}

	function info($_format = '', ... $_args)
	{
		[ $eol, $_format, $_args ] = consoleOutput($_format, $_args);
		$result = '';

		if(is_string($_format) && $_format !== '')
		{
			$result = insertPrefix(sprintf($_format, ... $_args), 'green', STDOUT);
		}

		while(--$eol >= 0)
		{
			$result .= PHP_EOL;
		}

		return fwrite(getStream('info'), $result);
	}

	function warn($_format = '', ... $_args)
	{
		[ $eol, $_format, $_args ] = consoleOutput($_format, $_args);
		$result = '';
		
		if(is_string($_format) && $_format !== '')
		{
			$result = insertPrefix(sprintf($_format, ... $_args), 'magenta', STDERR);
		}
		
		while(--$eol >= 0)
		{
			$result .= PHP_EOL;
		}
		
		return fwrite(getStream('warn'), $result);
	}

	function error($_format = '', ... $_args)
	{
		[ $eol, $_format, $_args ] = consoleOutput($_format, $_args);
		$result = '';
		
		if(is_string($_format) && $_format !== '')
		{
			$result = insertPrefix(sprintf($_format, ... $_args), 'red', STDERR);
		}
		
		while(--$eol >= 0)
		{
			$result .= PHP_EOL;
		}
		
		return fwrite(getStream('error'), $result);
	}
	
	function debug($_format = '', ... $_args)
	{
		[ $eol, $_format, $_args ] = consoleOutput($_format, $_args);
		$result = '';
		
		if(is_string($_format) && $_format !== '')
		{
			$result = insertPrefix(sprintf($_format, ... $_args), 'blue', STDERR);
		}

		while(--$eol >= 0)
		{
			$result .= PHP_EOL;
		}
		
		return fwrite(getStream('debug'), $result);
	}
}

//
namespace kekse\console\ansi;

//
const ESCAPE = "\e";
const RESET = '[0m';
const BOLD = '[1m';
const BOLD_OFF = '[22m';
const FAINT = '[2m';
const FAINT_OFF = '[22m';
const ITALIC = '[3m';
const ITALIC_OFF = '[23m';
const UNDERLINE = '[4m';
const UNDERLINE_OFF = '[24m';
const COLOR_OFF = '[39m';
const RED = '[31m';
const GREEN = '[32m';
const YELLOW = '[33m';
const BLUE = '[34m';
const MAGENTA = '[35m';

//
namespace kekse;

//
function strlen($_string, $_filter = false, $_binary = null, $_hidden = true)
{
	//
	if(!is_bool($_binary))
	{
		if($_filter === false)
		{
			$_binary = true;
		}
		else
		{
			$_binary = false;
		}
	}
	
	//
	if(!is_string($_string))
	{
		return false;
	}
	else if($_string === '')
	{
		return 0;
	}
	else if($_filter === false && $_binary === true)
	{
		return \strlen($_string);
	}

	//
	$result = ($_filter === null ? '' : 0);
	$hidden = ($_filter === null ? '' : 0);
	$len = \strlen($_string);
	$open = false;
	$byte = null;

	//
	for($i = 0; $i < $len; ++$i)
	{
		if($open)
		{
			if($_string[$i] === 'm')
			{
				$open = false;
			}

			if($_hidden)
			{
				if(!$open)
				{
					if($_filter === null)
					{
						$hidden = '';
					}
					else
					{
						$hidden = 0;
					}
				}
				else if(! (!$_binary && ((($byte = ord($_string[$i])) < 32) || $byte === 127)))
				{
					if($_filter === null)
					{
						$hidden .= $_string[$i];
					}
					else
					{
						++$hidden;
					}
				}
			}
		}
		else if($_filter && $_string[$i] === "\e")
		{
			$open = true;

			if($_hidden)
			{
				if($_filter === null)
				{
					$hidden .= $_string[$i];
				}
				else
				{
					++$hidden;
				}
			}
		}
		else if(!$_binary && ((($byte = ord($_string[$i])) < 32) || $byte === 127))
		{
			continue;
		}
		else if($_filter === null)
		{
			$result .= $_string[$i];
		}
		else
		{
			++$result;
		}
	}

	if($open && $_hidden)
	{
		if($_filter === null)
		{
			$result .= $hidden;
		}
		else
		{
			$result += $hidden;
		}
	}

	return $result;
}

function less($_string, $_binary = false, $_hidden = true)
{
	return \kekse\strlen($_string, null, $_binary, $_hidden);
}

//
namespace kekse\console\ansi;

//
if($consoleCondition)
{
	//
	function withAnsi($_stream = null)
	{
		if(! KEKSE_CLI)
		{
			return false;
		}
		else if(! KEKSE_ANSI)
		{
			return false;
		}
		else if(defined('KEKSE_ANSI_DISABLED') && KEKSE_ANSI_DISABLED)
		{
			return false;
		}
		else if($_stream === true)
		{
			if(! (stream_isatty(STDOUT) && stream_isatty(STDERR)))
			{
				return false;
			}
		}
		else if($_stream !== null && $_stream !== false)
		{
			if(($_stream = \kekse\console\getStream($_stream)) !== null)
			{
				if(! stream_isatty($_stream))
				{
					return false;
				}
			}
		}

		return true;
	}

	//
	function reset($_stream = null)
	{
		if(! withAnsi($_stream))
		{
			return '';
		}
		
		return (ESCAPE . RESET);
	}

	//
	function bold($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . BOLD . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . BOLD_OFF);
		}
		
		return $result;
	}

	function underline($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . UNDERLINE . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . UNDERLINE_OFF);
		}
		
		return $result;
	}
	
	function faint($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . FAINT . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($reset === null)
		{
			$result .= (ESCAPE . FAINT_OFF);
		}
		
		return $result;
	}
	
	function italic($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . ITALIC . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . ITALIC_OFF);
		}
		
		return $result;
	}

	function boldFaint($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . FAINT . ESCAPE . BOLD . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . FAINT_OFF . ESCAPE . BOLD_OFF);
		}
		
		return $result;
	}

	function underlineFaint($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}

		$result = (ESCAPE . FAINT . ESCAPE . UNDERLINE . $_string);

		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . FAINT_OFF . ESCAPE . UNDERLINE_OFF);
		}

		return $result;
	}

	function soft($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . FAINT . ESCAPE . ITALIC . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . FAINT_OFF . ESCAPE . ITALIC_OFF);
		}
		
		return $result;
	}

	function hard($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . BOLD . ESCAPE . UNDERLINE . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . BOLD_OFF . ESCAPE . UNDERLINE_OFF);
		}
		
		return $result;
	}
	
	function red($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . RED . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . COLOR_OFF);
		}
		
		return $result;
	}
	
	function green($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . GREEN . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . COLOR_OFF);
		}
		
		return $result;
	}
	
	function yellow($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . YELLOW . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . COLOR_OFF);
		}
		
		return $result;
	}
	
	function blue($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}

		if(! withAnsi($_stream))
		{
			return $_string;
		}
		
		$result = (ESCAPE . BLUE . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . COLOR_OFF);
		}
		
		return $result;
	}
	
	function magenta($_string, $_reset = true, $_stream = null)
	{
		if(!is_string($_string))
		{
			$_string = '';
			$_reset = false;
		}
		
		if(! withAnsi($_stream))
		{
			return $_string;
		}

		$result = (ESCAPE . MAGENTA . $_string);
		
		if($_reset)
		{
			$result .= (ESCAPE . RESET);
		}
		else if($_reset === null)
		{
			$result .= (ESCAPE . COLOR_OFF);
		}
		
		return $result;
	}
}

//
namespace kekse\counter;

//
function counter($_read_only = null, $_host = null)
{
	//
	function getState($_key)
	{
		//
		global $STATE;
		
		//
		if(!is_string($_key) || $_key === '')
		{
			return null;
		}
		else if(array_key_exists($_key = strtolower($_key), $STATE))
		{
			return $STATE[$_key];
		}
			
		return null;
	}

	function setState($_key, $_value, $_die = true)
	{
		//
		global $STATE;
		
		//
		if(!is_string($_key) || $_key === '')
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
				error('Unknown state `' . $_key . '`');
			}

			return null;
		}
		
		$result = $STATE[$_key];
		$STATE[$_key] = $_value;
		return $result;
	}

	//
	function sendHeader($_type = null)
	{
		if(!is_string($_type) || $_type === '')
		{
			$_type = getConfig('content');
		}
		
		if(getState('sent'))
		{
			return false;
		}
		else if(\kekse\startsWith($_type, 'content-type', false))
		{
			header($_type);
		}
		else
		{
			header('Content-Type: ' . $_type);
		}
		
		setState('sent', true);
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
		
		if(KEKSE_CLI)
		{
			if(function_exists('\kekse\console\error'))
			{
				\kekse\console\error($_reason);
			}
			else
			{
				fprintf(STDERR, $_reason . PHP_EOL);
			}

			if(is_int($_exit_code))
			{
				exit($_exit_code);
			}

			exit(224);
		}
		else if(function_exists('getState'))
		{
			if(getState('fin'))
			{
				return false;
			}
			else if(! getState('sent'))
			{
				sendHeader();
			}
		}

		if(!$_own && is_string(getConfig('error')))
		{
			die(getConfig('error'));
		}

		die($_reason);
	}
		
	function logError($_reason, $_source = '', $_path = '', $_die = true, $_force_log_file = false)
	{
		//
		$ex = null;

		//
		if($_reason instanceof \Throwable)
		{
			$ex = $_reason;
			$_reason = $ex->getMessage();
			
			$file = $ex->getFile();
			$line = $ex->getLine();

			$reason = '';
			
			if(is_string($file))
			{
				$reason .= '[' . $file;
				
				if(is_int($line))
				{
					$reason .= ':' . $line;
				}
				
				$reason .= '] ';
			}
			else if(is_int($line))
			{
				$reason = ':' . (string)$line . ' ';
			}
			
			$_reason = $reason . $_reason;
		}

		//
		if(!$_force_log_file)
		{
			if(KEKSE_CLI)
			{
				$res;
				
				if(function_exists('\kekse\console\error'))
				{
					$res = \kekse\console\error($_reason);
				}
				else
				{
					$res = fprintf(STDERR, $_reason . PHP_EOL);
				}

				if($_die)
				{
					exit(224);
				}

				return $res;
			}
		}
		
		$data = '[' . (string)time() . ']';

		if(is_string($_source) && $_source !== '')
		{
			$data .= $_source . '(';

			if(is_string($_path) && $_path !== '')
			{
				$data .= basename($_path);
			}

			$data .= ')';
		}
		else if(is_string($_path) && $_path !== '')
		{
			$data .= '(' . basename($_path) . ')';
		}

		$data .= ': ' . $_reason . PHP_EOL;
		$doLog = false;
		
		if(function_exists('\kekse\counter\getState'))
		{
			if(function_exists('\kekse\counter\checkPath') && \kekse\counter\checkPath(getState('log'), true, 'logError', true, true))
			{
				$doLog = true;
			}
			else if(function_exists('\kekse\checkPath') && \kekse\checkPath(getState('log'), true, 'logError', true, true))
			{
				$doLog = true;
			}
			else if(is_string(getState('log')))
			{
				if(! file_exists(getState('log')))
				{
					$doLog = true;
				}
				else if(is_file(getState('log')))
				{
					$doLog = true;
				}
			}
		}

		if($doLog)
		{
			$res = file_put_contents(getState('log'), $data, FILE_APPEND);
			
			if($res === false)
			{
				if($_die)
				{
					error('Logging error');
				}
				
				return false;
			}
		}
		else if($_die)
		{
			return error($_reason);
		}
		else if(KEKSE_CLI)
		{
			$res;
			
			if(function_exists('\kekse\console\error'))
			{
				$res = \kekse\console\error(null, $data);
			}
			else
			{
				$res = fprintf(STDERR, $data);
			}
			
			return $res;
		}
		
		if($_die)
		{
			return error($_reason);
		}

		return false;
	}

	//
	function errorHandler($_no, $_str, $_file, $_line)
	{
		if(!($_no & E_WARNING))
		{
			$result = '[Error ' . (string)$_no . '] ' . $_str . ' (in file `' . $_file . '`:' . (string)$_line;
			return logError($result, 'errorHandler', '', false);
		}
	}

	//
	set_error_handler('\kekse\counter\errorHandler');

	//
	function configCheckLimits($_min, $_max)
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

	function configCheckItem($_key, $_value = null, $_bool = false, $_host = null)
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

		$createReturn = function($_valid, $_string) use(&$_key, &$_value, &$_bool, &$type, &$types, &$validType, &$validLength, &$min, &$max, &$limits, &$test, &$validTest, &$static, &$_host)
		{
			if($_bool)
			{
				return $_valid;
			}
			
			return array(
				'host' => $_host,
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

		if($static && $_host)
		{
			return $createReturn(false, 'Static setting, so not allowed here');
		}
		else if(!$validType)
		{
			return $createReturn(false, 'Invalid item type `' . $type . '`');
		}

		if(isset($item['max']) && is_int($item['min']))
		{
			$min = $item['min'];
		}
		
		if(isset($item['max']) && is_int($item['max']))
		{
			$max = $item['max'];
		}

		$limits = configCheckLimits($min, $max);

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
						$tmp = \kekse\unit($_value, true);

						switch($tmp[1])
						{
							case 'deg':
							case 'rad':
								$validTest = true;
								$success = 'Unit is valid: `' . $tmp[1] . '`';
								break;
							case '':
								$validTest = true;
								$success = 'No unit in String, assuming `deg`';
								break;
							default:
								$validTest = 'Invalid unit `' . $tmp[1] . '`';
								break;
						}
					}
					else
					{
						$validTest = true;
					}
					break;
				case 'size':
					$tmp = \kekse\unit($_value, true);

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
						case '':
							$validTest = true;
							$success = 'No unit in String, assuming whatever `unit` will be';
							break;
						default:
							$validTest = 'Invalid unit `' . $tmp[1] . '`';
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
					$validTest = getPath($_value, false, false);

					if($validTest !== null)
					{
						$count = files($validTest, false, null, [ COUNTER_VALUE_CHAR, COUNTER_DIR_CHAR, COUNTER_FILE_CHAR, COUNTER_CONFIG_CHAR ], false, true, true);
						
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
							$success = $count . ' counter' . ($count === 1 ? '' : 's') . ' available';
						}
					}
					else
					{
						$validTest = 'No such directory.';
					}
					break;
				case 'log':
					$validTest = (getPath($_value, true, false) !== null);
					break;
				case 'fonts':
					$validTest = getPath($_value, false, false);

					if($validTest)
					{
						$count = files($validTest, false, '.ttf', null, false, true);
						
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
						$validTest = getPath($_value, false, false);
		
						if($validTest)
						{
							$count = files($validTest, false, '.php', null, false, true);
							
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
							$validTest = 'GD not loaded, but valid types that could be supported';
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

	function configCheckItemStatic($_key)
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

	function configCheck($_config = null, $_bool = false, $_die = true)
	{
		//
		global $CONFIG;

		//
		$host = null;

		if(is_array($_config))
		{
			$host = true;
		}
		else
		{
			$_config = DEFAULTS;
			$host = false;
		}

		//
		$result = array();

		//
		foreach($_config as $key => $value)
		{
			$result[$key] = configCheckItem($key, $value, $_bool, $host);
		}

		//
		if(!$host)
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
						$limits = configCheckLimits($min, $max);
						$test = (array_key_exists('test', $item) ? $item['test'] : false);

						$result[$keys[$i]] = array(
							'host' => false,
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

	function configCheckHost($_host, $_load = true, $_bool = false, $_die = true)
	{
		//
		global $CONFIG;

		//
		$config = null;

		if($_load)
		{
			if(($config = loadConfig(\kekse\joinPath(getState('path'), COUNTER_CONFIG_CHAR . $_host), null, false)) === null)
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
		return configCheck($config, $_bool, $_die);
	}

	function configUnsetInvalid(&$_config, $check)
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

	function getConfig($_key, $_host = null, $_die = true)
	{
		//
		if(is_string($_key) && $_key !== '')
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

		if(!is_string($_host) || $_host === '')
		{
			$_host = getState('host');
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

	function configLoaded($_host = null)
	{
		//
		global $CONFIG;

		//
		$result = null;

		if(is_string($_host) && $_host !== '')
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

	function makeConfig($_host, $_reload = null, $_unset = true, $_restore = false)
	{
		//
		global $CONFIG;
		global $HASHES;

		//
		$data = null;
		$path = \kekse\joinPath(getState('path'), COUNTER_CONFIG_CHAR . $_host);
		$data = null;
		
		//
		if(file_exists($path))
		{
			if(is_file($path))
			{
				if(! checkPath($path, true, 'makeConfig', false))
				{
					unloadConfig($_host);
					return null;
				}
				else if(configLoaded($_host))
				{
					if($_reload === false)
					{
						return $CONFIG[$_host];
					}
					else if($_reload === null && isset($HASHES[$_host]) && checkPath($path, true, 'makeConfig', false))
					{
						$data = readFile($path);
						
						if($data !== false)
						{
							if(hash(getConfig('hash'), $data) === $HASHES[$_host])
							{
								return $CONFIG[$_host];
							}
						}
					}
					else
					{
						$data = null;
					}
					
					unloadConfig($_host);
				}
			}
			else
			{
				if(delete($path, true, false))
				{
					logError('Invalid per-host config file is deleted now.', 'makeConfig', $path, false);
				}
				
				if(configLoaded($_host))
				{
					unloadConfig($_host);
				}
				
				return null;
			}
		}
		else
		{
			if(configLoaded($_host))
			{
				unloadConfig($_host);
			}
			
			return null;
		}

		//
		$conf = ($data === null ? loadConfig($path, null, true) : loadConfig(null, $data, true));

		if($conf === null)
		{
			return null;
		}
		else if(count($conf) === 0)
		{
			return null;
		}

		//
		$data = $conf[2];
		$hash = $conf[1];
		$conf = $conf[0];

		//
		$HASHES[$_host] = $hash;
		
		//
		return ($CONFIG[$_host] = $conf);
	}

	function unloadConfig($_host)
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

	function configCount($_path, $_data = null, $_check = true)
	{
		$result = loadConfig($_path, $_data, $_check);
		
		if($result === null)
		{
			return null;
		}
		
		return count($result);
	}

	function configCountHost($_host, $_check = true)
	{
		return configCount(\kekse\joinPath(getState('path'), COUNTER_CONFIG_CHAR . $_host), null, $_check);
	}

	function configCheckJSON($_data)
	{
		if(!is_string($_data) || $_data === '')
		{
			return null;
		}
		else if(! is_array($_data = json_decode($_data, true, 4)))
		{
			return null;
		}
		
		return $_data;
	}

	function loadConfig($_path, $_data = null, $_check = true)
	{
		$data = null;

		if(is_string($_data))
		{
			if($_data === '')
			{
				return null;
			}
			
			$data = $_data;
			$_path = null;
		}

		if($data === null && is_string($_path) && $_path !== '')
		{
			if(checkPath($_path, true, 'loadConfig', false))
			{
				if(($data = readFile($_path)) === false)
				{
					logError('Coudln\'t read per-host configuration file!', 'loadConfig', $_path, false);
					return null;
				}
			}
			else if(file_exists($_path) && !is_file($_path))
			{
				$tmpStr = 'Invalid per-host configuration file found (no regular file), ';

				if(delete($_path, true, false))
				{
					logError($tmpStr . 'but is deleted now!', 'loadConfig', $_path, false);
				}
				else
				{
					logError($tmpStr . 'and it couldn\'t be deleted!', 'loadConfig', $_path, false);
				}

				return null;
			}
			else
			{
				return null;
			}
		}
		else if(!is_string($data) || $data === '')
		{
			return null;
		}

		$result = null;

		if(($result = configCheckJSON($data)) === null)
		{
			return null;
		}

		if($_check)
		{
			$chk = configCheck($result, true, false);
		
			if($chk === null)
			{
				return null;
			}
			else
			{
				$result = configUnsetInvalid($result, $chk);
			}
		}

		if(count($result) === 0)
		{
			return null;
		}
		
		return [ $result, hash(getConfig('hash'), $data), $data ];
	}

	//
	function checkPath($_path, ... $_args)
	{
		if(securityTest($_path, true, false))
		{
			return \kekse\checkPath($_path, ... $_args);
		}
		
		return false;
	}

	//
	function getPath($_path, $_file, $_die = true, $_create = true)
	{
		if(!is_string($_path))
		{
			if($_die)
			{
				error('Path needs to be (non-empty) String');
			}
			
			return null;
		}
		else if($_path === '')
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
			if(($result = getcwd()) !== false)
			{
				$result .= '/' . $_path;
			}
			else if($_die)
			{
				error('The `getcwd()` function doesn\'t work on this system.');
			}
			else
			{
				return null;
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
				error('The `getcwd()` function doesn\'t work');
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

		$base = basename($result);
		
		switch($base[0])
		{
			case COUNTER_VALUE_CHAR:
			case COUNTER_DIR_CHAR:
			case COUNTER_FILE_CHAR:
			case COUNTER_CONFIG_CHAR:
				if($_die)
				{
					error('Path may not start with these characters: [ `' . COUNTER_VALUE_CHAR . '`, `' . COUNTER_DIR_CHAR . '`, `' . COUNTER_FILE_CHAR . '`, `' . COUNTER_CONFIG_CHAR . '` ]');
				}
				
				return null;
		}


		if(!\kekse\checkPath($result, $_file, 'getPath', false, $_create))
		{
			if($_die)
			{
				error('Invalid path selected');
			}
			
			return null;
		}

		return $result;
	}

	//
	function securityTest($_path, $_log = true, $_die = true)
	{
		$result = true;
		
		if(!is_string($_path) || $_path === '')
		{
			return false;
		}
		else if($_path[0] !== '/')
		{
			$result = false;
		}
		else if(strpos($_path, '/../') !== false)
		{
			$result = false;
		}
		else
		{
			$split = explode('/', $_path);
			$len = count($split);
			
			for($i = 0; $i < $len; ++$i)
			{
				if($split[$i] === '..')
				{
					$result = false;
					break;
				}
			}

			if($result)
			{
				$res = null;

				$pw;
				$po;

				if($_path[strlen($_path) - 1] === '/')
				{
					$pw = $_path;
					$po = substr($_path, 0, -1);
				}
				else
				{
					$pw = $_path . '/';
					$po = $_path;
				}

				$test = function($_value, $_file = false) use(&$res, &$pw, &$po)
				{
					if($res)
					{
						return $res;
					}
					else if($_value === null)
					{
						return $res;
					}
					else if($_file)
					{
						return ($res = ($po === $_value));
					}

					$w;
					$o;

					if($_value[strlen($_value) - 1] === '/')
					{
						$w = $_value;
						$o = substr($_value, 0, -1);
					}
					else
					{
						$w = $_value . '/';
						$o = $_value;
					}

					if($pw === $w || $po === $o)
					{
						$res = true;
					}
					else
					{
						$res = \kekse\startsWith($pw, $w);
					}

					return $res;
				};

				$l = count(PATHS);
				for($i = 0; $i < $l; ++$i)
				{
					if($res = $test(getState(PATHS[$i][0]), PATHS[$i][1]))
					{
						break;
					}
				}

				if($res !== null)
				{
					$result = $res;
				}
			}
		}
		
		if(($_die || $_log) && !$result)
		{
			if($_log)
			{
				logError('Insecure path', 'security_test', $_path, $_die);
			}
			
			if($_die)
			{
				error('Insecure path \'' . $_path . '\'!');
			}
			
			if($_die)
			{
				exit(254);
			}
		}
		
		return $result;
	}
	
	function delete($_path, ... $_args)
	{
		if(securityTest($_path))
		{
			if(! \kekse\startsWith(basename($_path), COUNTER_VALUE_CHAR))
			{
				return \kekse\delete($_path, ... $_args);
			}
		}

		return false;
	}

	function loaddir($_path, ... $_args)
	{
		$result = false;

		if(securityTest($_path))
		{
			$result = \opendir($_path, ... $_args);

			if($result !== false && is_int(KEKSE_MODE_DIR))
			{
				chmod($_path, KEKSE_MODE_DIR);
			}
		}
		
		return $result;
	}

	function readFile($_path, ... $_args)
	{
		$result = false;
		
		if(securityTest($_path))
		{
			if(($result = \kekse\readFile($_path, ... $_args)) !== false)
			{
				if(is_int(KEKSE_MODE_FILE))
				{
					chmod($_path, KEKSE_MODE_FILE);
				}
			}
		}
		
		return $result;
	}

	function writeFile($_path, $_value, ... $_args)
	{
		$result = false;

		if(securityTest($_path))
		{
			if(($result = \kekse\writeFile($_path, $_value, ... $_args)) !== false)
			{
				if(is_int(KEKSE_MODE_FILE))
				{
					chmod($_path, KEKSE_MODE_FILE);
				}
			}
		}
		
		return $result;
	}

	function readInt($_path, ... $_args)
	{
		$result = readFile($_path, ... $_args);
		
		if($result !== false)
		{
			if($result === '')
			{
				$result = 0;
			}
			else
			{
				$result = (int)$result;
			}
		}
		
		return $result;
	}

	function writeInt($_path, $_int, ... $_args)
	{
		$result = false;
		
		if(securityTest($_path))
		{
			if(($result = \kekse\writeInt($_path, $_int, ... $_args)) !== false)
			{
				if(is_int(KEKSE_MODE_FILE))
				{
					chmod($_path, KEKSE_MODE_FILE);
				}
			}
		}
		
		return $result;
	}
	
	//
	if(is_string($_read_only))
	{
		error('You must have mixed up the $_read_only and $_host argument. The string is the 2nd argument..');
	}

	//
	setState('log', getPath(getConfig('log'), true, false, false));
	setState('path', getPath(getConfig('path'), false, true, true));
	setState('modules', getPath(getConfig('modules'), false, false, false));

	//
	global $CONFIG;

	//
	setState('address', (KEKSE_CLI ? null : \kekse\secureHost($_SERVER['REMOTE_ADDR'])));

	//
	if(KEKSE_CLI)
	{
		setState('test', null);
		setState('ro', null);
	}
	else
	{
		//
		$test = (isset($_GET['test']));
		$ro = ($test || (isset($_GET['readonly']) || isset($_GET['ro'])));
		
		//
		if(is_bool($_read_only))
		{
			$ro = $_read_only;
		}
		else
		{
			$_read_only = $ro;
		}

		//
		setState('ro', $ro);
		unset($ro);
		setState('test', $test);
		unset($test);
	}

	if(getConfig('drawing'))
	{
		setState('fonts', getPath(getConfig('fonts'), false, false));

		if(KEKSE_CLI)
		{
			setState('zero', null);
			setState('draw', null);
		}
		else
		{
			if(getState('fonts'))
			{
				setState('zero', ((getConfig('drawing') && isset($_GET['zero']) && extension_loaded('gd'))));
				setState('draw', ((getState('zero') || (getConfig('drawing') && isset($_GET['draw']) && extension_loaded('gd')))));
			}
			else
			{
				setState('draw', false);
			}
		}	
	}
	else
	{
		setState('fonts', null);
	}

	//
	if(KEKSE_CLI && !(KEKSE_RAW && $GLOBALS['KEKSE_ARGC'] === 1))
	{
		//
		function getArguments($_index, $_secure = false)
		{
			$result = array();
			$index = 0;

			for($i = $_index + 1; $i < $GLOBALS['KEKSE_ARGC']; ++$i)
			{
				$item = $GLOBALS['KEKSE_ARGV'][$i];

				if($item === '')
				{
					continue;
				}
				else if($item[0] === '-' && $item !== '-')
				{
					continue;
				}
				else if(strlen($item) > KEKSE_LIMIT_STRING)
				{
					continue;
				}
				else if($_secure && ($item = \kekse\secure($item)) === null)
				{
					continue;
				}
				else if(in_array($item, $result))
				{
					continue;
				}
				else
				{
					$result[$index++] = $item;
				}
			}

			if($index === 0)
			{
				return null;
			}

			return $result;		
		}

		function checkArguments($_items, $_value = false, $_numeric = false, $_float = false)
		{
			if(is_string($_items))
			{
				$_items = [ $_items ];
			}

			$len = count($_items);
			$long = array();
			$short = array();
			$found = array();
			$matched = null;
			$L = 0;
			$S = 0;

			for($i = 0; $i < $len; ++$i)
			{
				if(!is_string($_items[$i]) || $_items[$i] === '')
				{
					--$len;
				}
				else if($_items[$i][0] !== '-')
				{
					--$len;
				}
				else if($_items[$i] === '-')
				{
					--$len;
				}
				else if($_items[$i][1] === '-')
				{
					$long[$L++] = $_items[$i];
				}
				else
				{
					$short[$S++] = $_items[$i];
				}
			}

			if($len === 0)
			{
				return null;
			}
			
			$findValue = function($_index, $_value = null) use(&$_numeric, &$_float)
			{
				if($_value !== null)
				{
					if($_numeric)
					{
						if($_float)
						{
							$_value = (double)$_value;
						}
						else
						{
							$_value = (int)$_value;
						}
					}
					
					return array($_index, $_value);
				}
				
				$result = null;
				$index = null;
				
				for($i = $_index + 1; $i < $GLOBALS['KEKSE_ARGC']; ++$i)
				{
					$item = $GLOBALS['KEKSE_ARGV'][$i];
					
					if($item === '--')
					{
						return null;
					}
					else if($item === '')
					{
						$result = $item;
						break;
					}
					else if($_numeric)
					{
						if(is_numeric($item))
						{
							$result = $item;
							$index = $i;
						}
						else
						{
							continue;
						}
					}
					else
					{
						$result = $item;
						$index = $i;
					}
					
					break;
				}

				if($_numeric && $result !== null)
				{
					if(is_numeric($result))
					{
						if($_float)
						{
							$result = (double)$result;
						}
						else
						{
							$result = (int)$result;
						}
					}
					else
					{
						return null;
					}
				}
				else if($result === null)
				{
					return null;
				}

				return [ $index, $result ];
			};
			
			for($i = 1; $i < $GLOBALS['KEKSE_ARGC']; ++$i)
			{
				$item = $GLOBALS['KEKSE_ARGV'][$i];

				if($item === '')
				{
					continue;
				}
				else if($item[0] !== '-')
				{
					continue;
				}

				$value = null;
				$str = strlen($item);
				$assign = strpos($item, '=');
				
				if($assign !== false)
				{
					$value = substr($item, $assign + 1);
					$item = substr($item, 0, $assign);
				}

				if($item[1] !== '-' && $str > 2)
				{
					for($j = 0; $j < $S; ++$j)
					{
						for($k = 1; $k < $str; ++$k)
						{
							$sub = '-' . $item[$k];

							if($short[$j] === $sub)
							{
								if(!$_value)
								{
									return true;
								}
								else
								{
									$res = $findValue($i, $value);

									if($res === null)
									{
										$matched = $i;
									}
									else
									{
										return $res[1];
									}
								}
							}
						}
					}
				}
				else if($item[1] !== '-' && $str === 2) for($k = 0; $k < $S; ++$k)
				{
					if($short[$k] === $item)
					{
						if(!$_value)
						{
							return true;
						}
						else
						{
							$res = $findValue($i, $value);
						
							if($res === null)
							{
								$matched = $i;
							}
							else
							{
								return $res[1];
							}
						}
					}
				}
				else if($item[1] === '-') for($k = 0; $k < $L; ++$k)
				{
					if($long[$k] === $item)
					{
						if(!$_value)
						{
							return true;
						}
						else
						{
							$res = $findValue($i, $value);
						
							if($res === null)
							{
								$matched = $i;
							}
							else
							{
								return $res[1];
							}
						}
					}
				}
			}

			if($matched !== null)
			{
				return true;
			}

			return null;
		}

		//
		if(KEKSE_ANSI)
		{
			define('KEKSE_ANSI_DISABLED', checkArguments(['-N','--no-ansi']));
		
			if(KEKSE_ANSI_DISABLED)
			{
				\kekse\debug('ANSI escape sequences are disabled now (by parameter).');
			}
		}
		else
		{
			define('KEKSE_ANSI_DISABLED', true);
		}

		//
		$aarg = checkArguments(['-n','--lines'], true, true, false);

		if(is_int($aarg))
		{
			if($aarg <= 0)
			{
				$aarg = true;
			}
		}

		if($aarg === true)
		{
			\kekse\debug('Disabled limited list output (of %d row' . (KEKSE_LIMIT_TTY === 1 ? '' : 's') . ' by default), by parameter', KEKSE_LIMIT_TTY);
		}
		else if(is_int($aarg))
		{
			\kekse\debug('Changed limited list output to %d rows (default is %d rows), by parameter.', $aarg, KEKSE_LIMIT_TTY);
		}
		
		define('KEKSE_LIMIT_TTY_DISABLED', $aarg);
		unset($aarg);

		//
		define('COUNTER_VALUE', 1);
		define('COUNTER_DIR', 2);
		define('COUNTER_FILE', 4);
		define('COUNTER_CONFIG', 8);

		//
		function getHostItem($_host, &$_result, $_path)
		{
			if($_host[0] === '.' || strlen($_host) < 2)
			{
				return 0;
			}
			
			$type = $_host[0];
			$p = \kekse\joinPath($_path, $_host);
			
			switch($type)
			{
				case COUNTER_VALUE_CHAR:
					if(!is_file($p))
					{
						return 0;
					}

					$type = COUNTER_VALUE;
					break;
				case COUNTER_DIR_CHAR:
					if(!is_dir($p))
					{
						return 0;
					}

					$type = COUNTER_DIR;
					break;
				case COUNTER_FILE_CHAR:
					if(!is_file($p))
					{
						return 0;
					}

					$type = COUNTER_FILE;
					break;
				case COUNTER_CONFIG_CHAR:
					if(!is_file($p))
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

		function getHosts($_index = null, $_sort = true)
		{
			//
			$list = null;
			
			if(is_int($_index) && $_index > -1)
			{
				$list = getArguments($_index, false);
			}

			$result = array();
			$found = 0;
			$path = getState('path');

			if(! checkPath($path, false, 'getHosts', true))
			{
				\kekse\error('Can\'t access counter path `%s`!', $path);
				exit(1);
			}

			if($list === null)
			{
				$handle = opendir($path);
				
				if($handle === false)
				{
					\kekse\error('Can\'t opendir counter path `%s`!', $path);
					exit(2);
				}
				else while($host = readdir($handle))
				{
					if($host[0] !== '.' && strlen($host) > 1 && getHostItem($host, $result, $path) > 0)
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
					$sub = \kekse\joinPath($path, '{' . COUNTER_VALUE_CHAR . ',' . COUNTER_DIR_CHAR . ',' . COUNTER_FILE_CHAR . ',' . COUNTER_CONFIG_CHAR . '}' . strtolower($list[$i]));
					$sub = glob($sub, GLOB_BRACE);
					$subLen = count($sub);

					if($subLen === 0)
					{
						continue;
					}
					else for($j = 0; $j < $subLen; ++$j)
					{
						if($sub[$j][0] !== '.' && strlen($sub[$j]) > 1 && getHostItem(basename($sub[$j]), $result, $path) > 0)
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

		function files($_dir, $_list = false, $_suffix = null, $_prefix = null, $_case_sensitive = false, $_remove = null, $_unique = false, $_equals = false)
		{
			//
			if(is_string($_suffix) && $_suffix !== '')
			{
				$_suffix = array($_suffix);
			}

			if(is_string($_prefix) && $_prefix !== '')
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
					if(!is_string($_suffix[$i]) || $_suffix[$i] === '')
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
			
			$hidden = false;

			if(is_array($_prefix))
			{
				if(($prefix = count($_prefix)) === 0)
				{
					$_prefix = null;
				}
				else for($i = 0; $i < $prefix; ++$i)
				{
					if(!is_string($_prefix[$i]) || $_prefix[$i] === '')
					{
						array_splice($_prefix, $i--, 1);
						--$prefix;
					}
					else if($_prefix[$i][0] === '.')
					{
						$hidden = true;
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

			if(! checkPath($_dir, false, 'files', false))
			{
				return null;
			}

			$handle = opendir($_dir);
			
			if($handle === false)
			{
				return null;
			}

			$result = (($_list || $_unique) ? array() : 0);
			$index = 0;

			while($sub = readdir($handle))
			{
				if($sub === '.' || $sub === '..')
				{
					continue;
				}
				else if(!$hidden && $sub[0] === '.')
				{
					continue;
				}
				else if(KEKSE_KEEP && $sub === '.keep')
				{
					continue;
				}
				else if(KEKSE_KEEP_HTACCESS && $sub === '.htaccess')
				{
					continue;
				}

				if($prefix)
				{
					$found = false;

					for($i = 0; $i < $prefix; ++$i)
					{
						if(\kekse\startsWith($sub, $_prefix[$i], $_case_sensitive))
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
						if(\kekse\endsWith($sub, $_suffix[$i], $_case_sensitive))
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
		function info($_index, $_version = true, $_copyright = true, $_website = true, $_ansi = false, $_exit = 0)
		{
			if($_copyright)
			{
				$str = 'Copyright (c) %s' . PHP_EOL;
				
				if($_ansi)
				{
					\kekse\debug(null, $str, KEKSE_COPYRIGHT);
				}
				else
				{
					printf('Copyright (c) %s' . PHP_EOL, KEKSE_COPYRIGHT);
				}
			}

			if($_website)
			{
				if($_ansi)
				{
					\kekse\debug('%s', COUNTER_WEBSITE);
				}
				else
				{
					printf('%s' . PHP_EOL, COUNTER_WEBSITE);
				}
			}

			if($_version)
			{
				$str = COUNTER_VERSION;
				
				if($_copyright || $_website)
				{
					$str = 'Version: ' . $str;
				}
				else
				{
					$str = 'v' . $str;
				}
				
				if($_ansi)
				{
					\kekse\debug('%s', $str);
				}
				else
				{
					printf('%s' . PHP_EOL, $str);
				}
			}
			
			if(is_int($_exit))
			{
				exit($_exit);
			}
		}

		function help($_index, $_exit = 0)
		{
			//
			info($_index, true, true, true, true, null);
			printf(PHP_EOL);
			\kekse\info('Available parameters (use only one at the same time, please):');

			//
			$pad = str_pad('', 12, ' ');
			$format = ' %s' . \kekse\console\ansi\bold('%18s', true, 1) . ' / ' . \kekse\console\ansi\bold('%-6s', true, 1) . \kekse\console\ansi\boldFaint('%-8s', true, 1) . '  ' . \kekse\console\ansi\faint('%s', true, 1) . PHP_EOL;
			$mark = '*';
			
			//
			printf(PHP_EOL);
			printf($format, $pad, \kekse\console\ansi\boldFaint('-n', true, 1), \kekse\console\ansi\boldFaint('--lines', true, 1), $pad, 'Disable line limit or define amount of rows to show');
			printf($format, $pad, \kekse\console\ansi\boldFaint('-N', true, 1), \kekse\console\ansi\boldFaint('--no-ansi', true, 1), $pad, 'Disable ANSI escape sequences (colors and styles)');
			printf(PHP_EOL);
			printf($format, ' ', '--help', '-?', '', 'This screen');
			printf($format, ' ', '--version', '-V', '', 'Current version');
			printf($format, ' ', '--website', '-W', '', 'Project website');
			printf($format, ' ', '--copyright', '-C', '', 'Copyright info');
			printf($format, ' ', '--info', '-I', '', 'All infos together');
			printf(PHP_EOL);
			printf($format, ' ', '--values', '-v', '[ * ]', '[' . \kekse\console\ansi\underline('DEFAULT', null, 1) . '] Shows the counted values (w/ cache and config)');
			printf(PHP_EOL);
			printf($format, ' ', '--config', '-c', '[ * ]', 'Check your own configuration (defaults or per-host)');
			printf(PHP_EOL);
			printf($format, $mark, '--sync', '-y', '[ * ]', 'Synchronize caches which are out-of-sync.');
			printf($format, $mark, '--clean', '-l', '[ * ]', 'Remove out-dated cache files (remote address protocol)');
			printf(PHP_EOL);
			printf($format, $pad, \kekse\console\ansi\boldFaint('-w', true, 1), \kekse\console\ansi\boldFaint('--without-values', true, 1), '', 'Also remove caches for hosts without value file');
			printf(PHP_EOL);
			printf($format, $mark, '--purge', '-p', '[ * ]', 'Delete only the caches (with files plus directories)');
			printf($format, $mark, '--remove', '-r', '[ * ]', 'Delete all real counter files for all/some hosts');
			printf(PHP_EOL);
			printf($format, $pad, \kekse\console\ansi\boldFaint('-g', true, 1), \kekse\console\ansi\boldFaint('--config', true, 1), $pad, 'Include the `' . COUNTER_CONFIG_CHAR . '` configuration files as well');
			printf(PHP_EOL);
			printf($format, $mark, '--sanitize', '-z', '', 'Delete all non-valid counter files (cleaning up)');
			printf(PHP_EOL);
			printf($format, $pad, \kekse\console\ansi\boldFaint('-w', true, 1), \kekse\console\ansi\boldFaint('--without-values', true, 1), '', 'Betray hosts without own value files as non-valid');
			printf(PHP_EOL);
			printf($format, $mark, '--set', '-s', '[ *[=value] ]', 'Changes or initializes value files (0 by default)');
			printf(PHP_EOL);
			printf($format, ' ', '--fonts', '-f', '[ * ]', 'A list of all installed fonts, or selection');
			printf($format, ' ', '--types', '-p', '', 'A list of all usable image types');
			printf($format, ' ', '--hashes', '-h', '', 'A list of all available hashes');
			printf(PHP_EOL);
			printf($format, ' ', '--errors', '-e', '', 'Counts the amount of error lines in the log file');
			printf($format, ' ', '--unlog', '-u', '', 'Deletes the whole log file, if any');
			printf(PHP_EOL);

			//
			\kekse\warn('Lines starting with `' . $mark . '` are functions with \'hard\' write operations!');
			\kekse\info('These and all with \'[ * ]\' can also have at least one host, or globs!');

			//
			if(! is_int($_exit))
			{
				$_exit = 0;
			}
		}

		function fonts($_index)
		{
			//
			$fontsPath = getState('fonts');
			
			if(!is_string($fontsPath) || $fontsPath === '')
			{
				\kekse\error('`fonts` directory is not properly configured');
				exit(1);
			}
			else if(! \kekse\checkPath($fontsPath, false, null, false, true))
			{
				\kekse\error('`fonts` directory doesn\'t exist (or is no directory).');
				exit(2);
			}

			$fonts = getArguments($_index, false);
			$result = array();
			$defined;

			if($fonts === null)
			{
				$defined = -1;
				$result = glob(\kekse\joinPath($fontsPath, '*.ttf'), GLOB_BRACE);
				$len = count($result);
				
				if($len === 0)
				{
					$result = null;
				}
				else for($i = 0; $i < $len; ++$i)
				{
					if($result[$i][0] !== '.')
					{
						$result[$i] = basename($result[$i], '.ttf');
					}
				}
			}
			else
			{
				$defined = count($fonts);
				$len = count($fonts);
				$idx = 0;
				
				for($i = 0; $i < $len; ++$i)
				{

					$sub = glob(\kekse\joinPath($fontsPath, basename($fonts[$i], '.ttf') . '.ttf'));
					$subLen = count($sub);

					if($subLen === 0)
					{
						continue;
					}
					else for($j = 0; $j < $subLen; ++$j)
					{
						if($sub[$j][0] !== '.')
						{
							$result[$idx++] = basename($sub[$j], '.ttf');
						}
					}
				}
				
				if($idx === 0)
				{
					$result = null;
				}
			}

			if($result === null)
			{
				error('No fonts found' . ($defined < 0 ? '' : ' (with your ' . $defined . ' glob' . ($defined === 1 ? '' : 's') . ')'));
				exit(3);
			}
			
			$len = count($result);
			\kekse\info('Found %d font' . ($len === 1 ? '' : 's') . ($defined < 0 ? '' : ' (by %d glob' . ($defined === 1 ? '' : 's') . ')'), $len, $defined);
			printf(PHP_EOL);
			
			for($i = 0; $i < $len; ++$i)
			{
				\kekse\debug($result[$i]);
			}

			printf(PHP_EOL);
			exit(0);
		}

		function types($_index)
		{
			if(!extension_loaded('gd'))
			{
				\kekse\error('The GD library/extension is not loaded/available');
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
				\kekse\error('Supporting ' . \kekse\console\bold('none', true, 'error') . ' of our image types. :-/');
				exit(2);
			}
			else
			{
				\kekse\info(2, 'Supporting %d image type' . ($len === 1 ? '' : 's') . ':', $len);
			}
			
			for($i = 0; $i < $len; ++$i)
			{
				\kekse\debug($supported[$i]);
			}

			printf(PHP_EOL);
			exit(0);
		}
	
		function hashes($_index)
		{
			$list = hash_algos();
			$len = count($list);
			
			\kekse\info(2, 'Found %d hash' . ($len === 1 ? '' : 'es') . ':', $len);
			
			for($i = 0; $i < $len; ++$i)
			{
				printf('    %s' . PHP_EOL, $list[$i]);
			}

			printf(PHP_EOL);
			exit(0);
		}

		//
		function config($_index)
		{
			//
			$params = 0;
			$hosts = array();
			$cnt = 0;
			$idx = 0;
			$path = getState('path');
			$list = getArguments($_index, false);
			$cnt = 0;

			if($list === null)
			{
				$hosts = null;
			}
			else
			{
				$cnt = count($list);
				
				for($i = 0; $i < $cnt; ++$i)
				{
					$item = \kekse\joinPath($path, COUNTER_CONFIG_CHAR . strtolower($list[$i]));
					$item = glob($item, GLOB_BRACE);
					$len = count($item);
					
					for($j = 0; $j < $len; ++$j)
					{
						if(!is_file($item[$j]))
						{
							continue;
						}
						else if(in_array($item[$j] = substr(basename($item[$j]), 1), $hosts))
						{
							continue;
						}
						else
						{
							$hosts[$idx++] = $item[$j];
						}
					}
				}
			}
			
			if($cnt === 0)
			{
				$hosts = null;
			}
			else if($idx === 0)
			{
				\kekse\warn(\kekse\console\ansi\bold('None', true, 'warn') . ' of your %d host' . ($cnt === 1 ? '' : 's') . '/glob' . ($cnt === 1 ? '' : 's') . ' has own configuration', $cnt);
				exit(1);
			}

			//
			$result = null;
			$invalid = false;

			if($hosts === null)
			{
				\kekse\info('Checking your ' . \kekse\console\ansi\underline('DEFAULT', true, 'info') . ' configuration (no host(s) specified).');
				$result = configCheck();
			}
			else
			{
				\kekse\info('Found %d ' . \kekse\console\ansi\underline('per-host configuration' . ($idx === 1 ? '' : 's'), true, 'info') . ' (by %d glob' . ($cnt === 1 ? '' : 's') . ' in total).', $idx, $cnt);
				$len = count($hosts);
				$result = array();

				for($i = 0; $i < $len; ++$i)
				{
					if(($result[$hosts[$i]] = configCheckHost($hosts[$i], true, false, false)) === null)
					{
						$invalid = true;
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

				if(($len = \kekse\strlen($_state['string'], true)) > $maxLen['string'])
				{
					$maxLen['string'] = $len;
				}

				if(($len = \kekse\strlen($_state['key'], true)) > $maxLen['key'])
				{
					$maxLen['key'] = $len;
				}

				if(is_string($_state['type']) && ($len = \kekse\strlen($_state['type'], true)) > $maxLen['type'])
				{
					$maxLen['type'] = $len;
				}

				if(($len = \kekse\strlen($_state['limits'], true)) > $maxLen['limits'])
				{
					$maxLen['limits'] = $len;
				}
			};

			if($hosts === null)
			{
				printf(PHP_EOL);

				foreach($result as $key => $state)
				{
					$checkMaxLen($state);
				}

				$maxLen['key'] += 2;
				
				$header = \kekse\console\ansi\underlineFaint('   %-' . $maxLen['key'] . 's  %-5s  %-' . $maxLen['string'] . 's %-' . $maxLen['type'] . 's %-' . $maxLen['limits'] . 's %s ' . PHP_EOL, true, true);
				printf($header, ($maxLen['key'] <= 2 ? '' : ' Item'), 'State', ($maxLen['string'] === 0 ? '' : 'Comment'), ($maxLen['type'] === 0 ? '' : ' Type'), ($maxLen['limits'] === 0 ? '' : ' Limits'), ' Valid types');

				$format = (\kekse\console\ansi\bold(' %s ', true, true) . \kekse\console\ansi\bold('%' . $maxLen['key'] . 's', true, true) . ' ] ' . \kekse\console\ansi\underline('%5s', true, true) . ': %-' . $maxLen['string'] . 's  ' . \kekse\console\ansi\boldFaint('%' . $maxLen['type'] . 's', true, true) . ' ' . \kekse\console\ansi\soft('%-' . $maxLen['limits'] . 's', true, true) . ' ' . \kekse\console\ansi\faint('%s', true, true) . PHP_EOL);
				
				foreach($result as $key => $state)
				{
					if($state['valid'])
					{
						printf($format, ($state['static'] ? '*' : ' '), '[ ' . $state['key'], 'OK', $state['string'], $state['type'], $state['limits'], $state['types']);
						++$ok;
					}
					else
					{
						fprintf(STDERR, $format, ($state['static'] ? '*' : ' '), '[ ' . $state['key'], 'BAD', $state['string'], $state['type'], $state['limits'], $state['types']);
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
					if(($len = \kekse\strlen($host, true)) > $maxLen['host'])
					{
						$maxLen['host'] = $len;
					}

					if($item === null)
					{
						++$count;
						continue;
					}

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

					++$count;
				}

				if($count === 0)
				{
					\kekse\warn('No config items found');
					exit(2);
				}
				else
				{
					printf(PHP_EOL);
				}

				$header = \kekse\console\ansi\underlineFaint(' %-' . $maxLen['host'] . 's   %-' . $maxLen['key'] . 's    %-5s %-' . $maxLen['string'] . 's    %-' . $maxLen['type'] . 's%-' . $maxLen['limits'] . 's    %s ' . PHP_EOL, true, true);
				printf($header, ($maxLen['host'] === 0 ? '' : ' Host'), ($maxLen['key'] === 0 ? '' : ' Item'), ' State', ($maxLen['string'] === 0 ? '' : ' Comment'), ($maxLen['type'] === 0 ? '' : ' Type'), ($maxLen['limits'] === 0 ? '' : ' Limits'), ' Valid types');

				$invalid = 'NULL';
				$maxLen['string'] += 2;
				$maxLen['key'] += 2;
				$format = (\kekse\console\ansi\bold(' %s %' . $maxLen['host'] . 's', true, true) . ' [ ' . \kekse\console\ansi\hard('%-' . $maxLen['key'] . 's', true, true) . ' ] ' . \kekse\console\ansi\underline('%5s', true, true) . ': %-' . $maxLen['string'] . 's  ' . \kekse\console\ansi\boldFaint('%' . $maxLen['type'] . 's', true, true) . ' ' . \kekse\console\ansi\soft('%-' . $maxLen['limits'] . 's', true, true) . ' ' . \kekse\console\ansi\faint('%s', true, true) . PHP_EOL);
				$format_invalid = (' %' . $maxLen['host'] . 's = ' . $invalid . PHP_EOL);

				foreach($result as $host => $item)
				{
					if($item === null)
					{
						fprintf(STDERR, $format_invalid, $host);
						++$bad;
					}
					else foreach($item as $key => $state)
					{
						if($state['valid'])
						{
							printf($format, ($state['static'] ? '*' : ' '), $host, $state['key'], 'OK', $state['string'], $state['type'], $state['limits'], $state['types']);
							++$ok;
						}
						else
						{
							fprintf(STDERR, $format, ($state['static'] ? '*' : ' '), $host, $state['key'], 'BAD', $state['string'], $state['type'], $state['limits'], $state['types']);
							++$bad;
						}
					}
				}
			}

			printf(PHP_EOL);

			//
			if($bad === 0)
			{
				if($ok === 1)
				{
					\kekse\info('The only item was OK! :-)');
				}
				else
				{
					\kekse\info('All %d items were OK! :-)', $ok);
				}
			}
			else if($ok === 0)
			{
				\kekse\error('NO single item was valid (%d error' . ($bad === 1 ? '' : 's') . ')...', $bad);
			}
			else
			{
				\kekse\warn('Only %d item' . ($ok === 1 ? '' : 's') . ' ' . ($ok === 1 ? 'is' : 'are') . ' valid.. %d caused errors!', $ok, $bad);
			}

			if($bad > 0)
			{
				exit(3);
			}

			exit(0);
		}

		function set($_index)
		{
			$args = getArguments($_index, true);
			$host = null;
			$value = 0;

			if($args === null)
			{
				\kekse\warn('No valid target host(s) specified!');
				\kekse\info('Just argue with `host` or `host=value`, for as many hosts you want to change/init!');
				exit(1);
			}

			$len = count($args);

			$hosts = array();
			$newHosts = array();
			$nh = 0;
			$ah = 0;
			$maxLen = 0;
			$itemLen = 0;
			
			for($i = 0; $i < $len; ++$i)
			{
				$item = $args[$i];
				$pos = strpos($item, '=');
				$value = 0;

				if($pos !== false)
				{
					$value = substr($item, $pos + 1);
					
					$subPos = strpos($value, '.');
					
					if($subPos !== false)
					{
						$value = substr($value, 0, $subPos);
					}
					
					if($value === '' || $value[0] === '.')
					{
						$value = 0;
					}
					else
					{
						$value = (int)$value;
					}
					
					$item = substr($item, 0, $pos);
				}

				if(($item = \kekse\secureHost($item)) === null)
				{
					continue;
				}
				else
				{
					$hosts[$item] = $value;
				}
				
				if(! is_file(\kekse\joinPath(getState('path'), COUNTER_VALUE_CHAR . $item)))
				{
					$newHosts[$nh++] = $item;
				}
				
				if(($itemLen = \kekse\strlen($item, true)) > $maxLen)
				{
					$maxLen = $itemLen;
				}
				
				++$ah;
			}

			if($nh > 0)
			{
				\kekse\debug('Hosts starting with `*` are new hosts to be initialized.');
			}

			\kekse\info(2, 'You want to set the following host' . ($len === 1 ? '' : 's') . ' with these values (by default zero):');
			$format = \kekse\console\ansi\bold(' %s ', true, 1) . \kekse\console\ansi\hard(' %' . $maxLen . 's ', true, 1) . '        ' . \kekse\console\ansi\bold('%-10s', true, 1) . '    %-10s' . PHP_EOL;
			$limit = false;
			$i = 0;
			$count = 0;
			$same = 0;
			$result = array();

			$header = \kekse\console\ansi\underlineFaint('  %-' . $maxLen . 's       %-10s    %-10s ' . PHP_EOL, true, 1);
			printf($header, ' Item', ' New value', ' Current value');

			foreach($hosts as $host => $value)
			{
				++$count;
				$result[$host] = array($value);
				
				if($limit === false)
				{
					$limit = limitListCheck(++$i, $ah);
				}

				$current = \kekse\joinPath(getState('path'), COUNTER_VALUE_CHAR . $host);

				if(is_file($current))
				{
					$current = \kekse\readInt($current);

					if($current === false)
					{
						$current = '';
						$result[$host][1] = null;
					}
					else
					{
						$result[$host][1] = $current;
						
						if($current === $value)
						{
							++$same;
						}

						$current = (string)$current;
					}
				}
				else
				{
					$current = 'NULL';
					$result[$host][1] = null;
				}
				
				if(!$limit)
				{
					$current = \kekse\console\ansi\faint($current, true, 1);
					$value = (string)$value;
					printf($format, (in_array($host, $newHosts) ? '*' : ' '), $host, $value, $current);
				}
			}

			printf(PHP_EOL);

			//
			if($count === $same)
			{
				\kekse\info('All your values are exactly the same as the current ones. ' . \kekse\console\ansi\bold('Nothing changed', true, 'info') . '!');
				exit(0);
			}

			//
			if(! \kekse\prompt('Do you want to write ' . ($count === 1 ? 'this change' : 'these %d changes') . ' to disk [yes/no]? ', $count))
			{
				\kekse\warn('Not writing anything, aborting..');
				exit(2);
			}
			else
			{
				printf(PHP_EOL);
			}
			
			//
			foreach($result as $host => $values)
			{
				//
				$path = \kekse\joinPath(getState('path'), COUNTER_VALUE_CHAR . $host);
				$res = null;

				//
				if($values[0] === $values[1])
				{
					$res = null;
				}
				else
				{
					$res = \kekse\writeInt($path, $values[0]);
				}
				
				//
				$result[$host] = array($values, $res);
			}
			
			//
			$format = ' ' . \kekse\console\ansi\hard(' %' . $maxLen . 's ', true, 1) . '    %9s  =>  ' . \kekse\console\ansi\bold('%-9s', true, 1) . '  %s' . PHP_EOL;
			$limit = false;
			$i = 0;

			$header = \kekse\console\ansi\underlineFaint('%-' . $maxLen . 's        %-9s %-9s  %s ' . PHP_EOL, true, 1);
			printf($header, ($maxLen === 0 ? '' : ' Host'), ' Current', ' New', ' Return value');

			foreach($result as $host => $state)
			{
				if($limit === false && ($limit = limitListCheck(++$i, $count)))
				{
					break;
				}
				
				$val = $state[0];
				$res = $state[1];
				
				$old = ($val[1] === null ? 'NULL' : (string)$val[1]);
				$new = (string)$val[0];
				$success = ($res === null ? \kekse\console\ansi\soft('(unchanged)', true, 1) : ($res ? \kekse\console\ansi\bold('OK', true, 1) : 'BAD'));
				
				printf($format, $host, $old, $new, $success);
			}

			//
			printf(PHP_EOL);
			\kekse\info('Ready!');
			exit(0);
		}

		//
		function limitListCheck($_current, $_length = null, ... $_args)
		{
			$limit = null;
			
			if(KEKSE_LIMIT_TTY_DISABLED === true)
			{
				return null;
			}
			else if(is_int(KEKSE_LIMIT_TTY_DISABLED))
			{
				if($_current <= KEKSE_LIMIT_TTY_DISABLED)
				{
					return false;
				}
				else
				{
					$limit = KEKSE_LIMIT_TTY_DISABLED;
				}
			}
			else if($_current <= KEKSE_LIMIT_TTY)
			{
				return false;
			}
			else
			{
				$limit = KEKSE_LIMIT_TTY;
			}
			
			if(KEKSE_LIMIT_TTY_PROMPT && \kekse\prompt('Reached line %d' . (is_int($_length) ? ' of %d' : '') . '.. would you like to see the whole rest [yes/no]? ', ($_current - 1), $_length))
			{
				return null;
			}
			else if(is_int($_length))
			{
				limitList($_length, $limit, ... $_args);
			}

			return true;
		}

		function limitList($_length, $limit, $_stream = 'warn', $_eol = true, $_tabs = 1)
		{
			$output = '... (limited output to ' . $limit . ' of ' . $_length . ' lines; or use `' . \kekse\console\ansi\bold('--lines', true, $_stream) . ' / ' . \kekse\console\ansi\bold('-n', true, $_stream) . '`) ...';

			if($_tabs) while(--$_tabs >= 0)
			{
				$output = "\t" . $output;
			}

			if($_eol)
			{
				$output .= PHP_EOL;
			}

			if($_stream === null)
			{
				return 0;
			}
			
			$result = 0;

			if(is_int($_stream))
			{
				if($_stream < -5 || $_stream > 2 || $_stream === 0)
				{
					return $result;
				}
				else switch($_stream)
				{
					case 1:
						$result = printf($output);
						break;
					case 2:
						$result = fprintf(STDERR, $output);
						break;
					case -1:
						$result = \kekse\console\log(null, $output);
						break;
					case -2:
						$result = \kekse\console\info(null, $output);
						break;
					case -3:
						$result = \kekse\console\warn(null, $output);
						break;
					case -4:
						$result = \kekse\console\error(null, $output);
						break;
					case -5:
						$result = \kekse\console\debug(null, $output);
						break;
				}
			}
			else if(is_string($_stream))
			{
				switch(strtolower($_stream))
				{
					case 'stdout':
						$result = printf($output);
						break;
					case 'stderr':
						$result = fprintf(STDERR, $output);
						break;
					case 'log':
						$result = \kekse\console\log(null, $output);
						break;
					case 'info':
						$result = \kekse\console\info(null, $output);
						break;
					case 'warn':
						$result = \kekse\console\warn(null, $output);
						break;
					case 'error':
						$result = \kekse\console\error(null, $output);
						break;
					case 'debug':
						$result = \kekse\console\debug(null, $output);
						break;
				}
			}
			else if($_stream !== null) switch($_stream)
			{
				case STDOUT:
					$result = printf($output);
					break;
				case STDERR:
					$result = fprintf(STDERR, $output);
					break;
			}
			
			return $result;
		}

		//
		function values($_index, $_sync = false)
		{
			//
			$list = getHosts($_index);

			if($list === null)
			{
				\kekse\warn('No host(s) found.');
				exit(1);
			}

			//
			$maxLen = 0;
			$hostLen = 0;
			$hosts = array();
			$h = 0;
			$path = getState('path');
			
			$config = array();
			$values = array();
			$caches = array();
			$delete = array();
			
			$totalHosts = 0;
			$totalDelete = 0;
			$totalDirs = 0;
			
			$outOfSync = false;
			
			foreach($list as $host => $type)
			{
				//
				$delete[$host] = array();
				$d = 0;
				$real = 0;
				$cache = 0;
				
				//
				$p = \kekse\joinPath($path, COUNTER_VALUE_CHAR . $host);
				
				if($type & COUNTER_VALUE)
				{
					$p = \kekse\joinPath($path, COUNTER_VALUE_CHAR . $host);
					$values[$host] = readInt($p);
				}
				else
				{
					$values[$host] = 0;
					
					if(file_exists($p))
					{
						$delete[$host][$d++] = $p;
						$outOfSync = true;
						
						if(is_dir($p))
						{
							++$totalDirs;
						}
					}
				}
				
				//
				$p = \kekse\joinPath($path, COUNTER_FILE_CHAR . $host);
				
				if($type & COUNTER_FILE)
				{
					$cache = readInt($p);
					
					if($cache === false)
					{
						$cache = 0;
					}
				}
				else
				{
					$cache = 0;
					
					if(file_exists($p))
					{
						$delete[$host][$d++] = $p;
						$outOfSync = true;
						
						if(is_dir($p))
						{
							++$totalDirs;
						}
					}
				}

				//
				$p = \kekse\joinPath($path, COUNTER_DIR_CHAR . $host);
				
				if($type & COUNTER_DIR)
				{
					$handle = opendir($p);
					
					if($handle !== false)
					{
						while($sub = readdir($handle))
						{
							if($sub[0] === '.')
							{
								continue;
							}
							
							$pp = \kekse\joinPath($p, $sub);
							
							if(is_file($pp))
							{
								++$real;
							}
							else
							{
								$delete[$host][$d++] = $pp;
								$outOfSync = true;
								
								if(is_dir($pp))
								{
									++$totalDirs;
								}
							}
						}
						
						closedir($handle);

						if($real === 0)
						{
							++$totalDirs;
							$sp = \kekse\joinPath($path, COUNTER_DIR_CHAR . $host);
							$delete[$host][$d++] = $sp;
							$outOfSync = true;
						}
					}
					else
					{
						$real = 0;

						if($type & COUNTER_FILE)
						{
							$delete[$host][$d++] = \kekse\joinPath($path, COUNTER_FILE_CHAR . $host);
							$outOfSync = true;
						}
					}
				}
				else
				{
					if($type & COUNTER_FILE)
					{
						$delete[$host][$d++] = \kekse\joinPath($path, COUNTER_FILE_CHAR . $host);
						$outOfSync = true;
					}
					
					if(file_exists($p))
					{
						$delete[$host][$d++] = $p;
						$outOfSync = true;
						
						if(is_dir($p))
						{
							++$totalDirs;
						}
					}
				}
				
				$p = \kekse\joinPath($path, COUNTER_CONFIG_CHAR . $host);
				
				if($type & COUNTER_CONFIG)
				{
					$conf = loadConfig($p, null, false);
					$all = 0;
					$chk = 0;

					if($conf !== null)
					{
						$conf = $conf[0];
						$all = count($conf);
						$conf = configUnsetInvalid($conf, configCheck($conf, true, false));
						$chk = count($conf);
					}
					
					$config[$host] = [ $chk, $all ];
				}
				else
				{
					$config[$host] = [ 0, 0 ];
					
					if(file_exists($p))
					{
						$delete[$host][$d++] = $p;
						$outOfSync = true;
						
						if(is_dir($p))
						{
							++$totalDirs;
						}
					}
				}
				
				//
				$caches[$host] = [ $cache, $real ];
				
				if($cache !== $real)
				{
					$outOfSync = true;
				}

				//
				$td = count($delete[$host]);
				
				$totalDelete += $td;
				++$totalHosts;
				$hosts[$h++] = $host;

				if(($hostLen = \kekse\strlen($host, true)) > $maxLen)
				{
					$maxLen = $hostLen;
				}
			}

			//
			if($totalHosts === 0)
			{
				\kekse\info('No host infos found.');
			}

			$maxLen = array('host' => 0, 'value' => 0, 'cache' => 0, 'real' => 0, 'config' => 0);
			$strings = array();
			$currLen = 0;

			for($i = 0; $i < $h; ++$i)
			{
				//
				$host = $hosts[$i];
				
				if(($currLen = \kekse\strlen($host, true)) > $maxLen['host'])
				{
					$maxLen['host'] = $currLen;
				}
				
				//
				$strings[$host] = array();
				
				//
				$tmp = $values[$host];

				if($tmp === null)
				{
					$strings[$host]['value'] = '0';
				}
				else
				{
					$strings[$host]['value'] = ($tmp === 0 ? '0' : (string)$tmp);

					if(($currLen = \kekse\strlen((string)$tmp, true)) > $maxLen['value'])
					{
						$maxLen['value'] = $currLen;
					}
				}
				
				//
				$tmp = $caches[$host];
				
				if($tmp === null)
				{
					$strings[$host]['cache'] = '-';
					$strings[$host]['real'] = '-';
				}
				else
				{
					$c = $tmp[0];
					$r = $tmp[1];

					$strings[$host]['cache'] = (string)$c;
					$strings[$host]['real'] = (string)$r;
					
					if($r !== $c)
					{
						$outOfSync = true;
					}
				}
					
				if(($currLen = \kekse\strlen($strings[$host]['cache'], true)) > $maxLen['cache'])
				{
					$maxLen['cache'] = $currLen;
				}
				
				if(($currLen = \kekse\strlen($strings[$host]['real'], true)) > $maxLen['real'])
				{
					$maxLen['real'] = $currLen;
				}
				
				//
				$tmp = $delete[$host];
				
				if($tmp === null)
				{
					$strings[$host]['delete'] = '';
				}
				else
				{
					$cnt = count($tmp);
					$strings[$host]['delete'] = \kekse\console\ansi\soft('(' . $cnt . ' deletion' . ($cnt === 1 ? '' : 's') . ')', true, 1);
				}
				
				//
				$tmp = $config[$host];
				
				if($tmp === null)
				{
					$strings[$host]['config'] = '';
				}
				else
				{
					$chk = 0;
					$all = 0;
					
					if($tmp[0] === null)
					{
						$chk = 0;
					}
					else
					{
						$chk = $tmp[0];
					}
					
					if($tmp[1] === null)
					{
						$all = 0;
					}
					else
					{
						$all = $tmp[1];
					}

					if($all === 0)
					{
						$strings[$host]['config'] = '';
					}
					else if($all === $chk)
					{
						$strings[$host]['config'] = \kekse\console\ansi\boldFaint((string)$chk, true, 1);
					}
					
					if($all !== $chk)
					{
						$strings[$host]['config'] .= \kekse\console\ansi\boldFaint((string)$chk, true, 1) . ' ' . \kekse\console\ansi\faint('/ ' . $all, true, 1);
					}
					
					if(($currLen = \kekse\strlen($strings[$host]['config'], true)) > $maxLen['config'])
					{
						$maxLen['config'] = $currLen;
					}
				}
			}

			$maxLen['caches'] = max($maxLen['cache'], $maxLen['real']);
			\kekse\info(2, 'Found %d hosts:', $totalHosts);
			$limit = false;
			$i = 1;
			$format = ' ' . \kekse\console\ansi\hard(' %' . $maxLen['host'] . 's ', true, 1) . '      ' . \kekse\console\ansi\bold('%' . $maxLen['value'] . 's', true, 1) . '      %' . $maxLen['caches'] . 's / %-' . $maxLen['caches'] . 's        %' . $maxLen['config'] . 's' . PHP_EOL;

			$header = \kekse\console\ansi\underlineFaint('%-' . $maxLen['host'] . 's       %-' . $maxLen['value'] . 's %-' . $maxLen['caches'] . 's       %-' . $maxLen['config'] . 's ' . PHP_EOL, true, 1);
			printf($header, ($maxLen['host'] === 0 ? '' : ' Host'), ($maxLen['value'] === 0 ? '' : ' Value'), ($maxLen['caches'] === 0 ? '' : ' Cache'), ($maxLen['config'] === 0 ? '' : ' Configuration'));

			for($i = 0; $i < $h; ++$i)
			{
				if($limit === false && ($limit = limitListCheck($i + 1, $h)))
				{
					break;
				}

				$host = $hosts[$i];
				printf($format, $hosts[$i], $strings[$host]['value'], $strings[$host]['cache'], $strings[$host]['real'], $strings[$host]['config']);
			}
			
			unset($strings);
			unset($config);
			unset($values);

			printf(PHP_EOL);

			//
			if(! $_sync)
			{
				if($outOfSync)
				{
					\kekse\warn(\kekse\console\ansi\faint('Some states are ' . \kekse\console\ansi\underline('out of sync', null, 'warn') . '. Synchronization via ', true, 'warn') . ' `--sync / -y`');
					/*if(! \kekse\prompt('Or just allow me to continue with synchronization right here, right now [yes/no]? '))
					{
						\kekse\debug('Abort requested, so we won\'t continue here.');
						exit(1);
					}*/
					exit(1);
				}
				else
				{
					exit(0);
				}
			}
			else if(!$outOfSync)
			{
				\kekse\info('Nothing\'s out of sync. Great!');
				exit(0);
			}

			//
			if($h === 0)
			{
				\kekse\info('Nothing to change! So we finished here.');
				exit(0);
			}
			else
			{
				\kekse\info('The synchronization is done for %d hosts in total.', $h);
			}
			
			//
			if($totalDelete > 0)
			{
				\kekse\warn('So we have selected %d files for deletion.', $totalDelete);
				
				if($totalDirs > 0)
				{
					\kekse\debug('As %d items are directories, recursively deleted file count may grow.', $totalDirs);
				}
			}
			
			//
			$c = count($caches);
			$d = count($delete);
			
			//
			$result = array();
			$currLen = 0;
			$maxLen = 0;
			
			for($i = 0; $i < $h; ++$i)
			{
				//
				$host = $hosts[$i];
				$result[$host] = array();
				
				//
				if(($currLen = \kekse\strlen($host, true)) > $maxLen)
				{
					$maxLen = $currLen;
				}
				
				//
				if(isset($delete[$host]))
				{
					$l = count($delete[$host]);
					
					for($j = 0; $j < $l; ++$j)
					{
						if(file_exists($delete[$host][$j]))
						{
							$result[$host]['delete'][$j] = $delete[$host][$j];
						}
						else
						{
							--$totalDelete;
						}
					}
				}
				else
				{
					$result[$host]['delete'] = array();
				}
				
				//
				if(isset($caches[$host]))
				{
					if($caches[$host][0] !== $caches[$host][1])
					{
						$result[$host]['cache'] = $caches[$host];
					}
					else
					{
						$result[$host]['cache'] = $caches[$host][0];
					}
				}
				else
				{
					$result[$host]['cache'] = 0;
				}
			}

			//
			unset($delete);
			unset($caches);

			//
			printf(PHP_EOL);

			//
			$header = \kekse\console\ansi\underlineFaint('%-' . $maxLen . 's       %-20s%s     ' . PHP_EOL, true, 1);
			printf($header, ($maxLen === 0 ? '' : ' Host'), ' Changed', ' Deletions');

			//
			$format = ' ' . \kekse\console\ansi\hard(' %' . $maxLen . 's ', true, 1) . '      ' . \kekse\console\ansi\italic('%-20s', true, 1) . '   ' . \kekse\console\ansi\soft('%s', true, 1) . PHP_EOL;
			$limit = false;
			
			for($i = 0; $i < $h; ++$i)
			{
				if($limit === false && ($limit = limitListCheck($i + 1, $h)))
				{
					break;
				}
				
				$host = $hosts[$i];
				$cache = '';
				$delete = 0;
				
				if(is_int($result[$host]['cache']))
				{
					$cache = \kekse\console\ansi\bold((string)$result[$host]['cache'], true, 1);
				}
				else
				{
					$cache = $result[$host]['cache'][0] . ' => ' . \kekse\console\ansi\bold((string)$result[$host]['cache'][1], true, 1);
				}
				
				if(isset($result[$host]['delete']))
				{
					$cnt = count($result[$host]['delete']);
					$delete = 'with ' . \kekse\console\ansi\bold((string)$cnt, null, 1) . ' deletion' . ($cnt === 1 ? '' : 's');
				}
				
				printf($format, $host, $cache, $delete);
			}
			
			//
			printf(PHP_EOL);
			
			if(! \kekse\prompt('So, are these changes good for you [yes/no]? '))
			{
				\kekse\warn('Nothing changed, as abort was requested.');
				exit(2);
			}
			
			//
			$total = 0;
			$deleted = 0;
			$failed = 0;
			
			//
			$sync = 0;
			$syncFail = 0;
			
			//
			foreach($result as $host => $item)
			{
				//
				$cache = $item['cache'];
				
				if(is_array($cache) && $cache[1] > 0)
				{
					$path = \kekse\joinPath(getState('path'), COUNTER_FILE_CHAR . $host);
					
					if(\kekse\writeInt($path, $cache[1]) === false)
					{
						++$syncFail;
					}
					else
					{
						++$sync;
					}
				}
				
				//
				$len = count($item['delete']);
				$result[$host] = array(0, 0, 0);
				
				for($i = 0; $i < $len; ++$i)
				{
					$r = \kekse\delete($item['delete'][$i], true, true);
					
					$total += $r[0];
					$deleted += $r[1];
					$failed += $r[2];
				}
			}
			
			//
			if($sync > 0 || $syncFail > 0)
			{
				if($sync > 0)
				{
					\kekse\info('Cache synchronized %d time' . ($sync === 1 ? '' : 's'), $sync);
				}
				
				if($syncFail > 0)
				{
					$str = '';
					
					if($sync > 0)
					{
						$str .= 'But %d time' . ($syncFail === 1 ? '' : 's') . ' it failed!';
					}
					else
					{
						$str .= 'Cache couldn\'t be synchronized %d time' . ($syncFail === 1 ? '' : 's');
					}
					
					\kekse\warn($str, $syncFail);
				}
			}
			
			//
			if($total === $deleted)
			{
				\kekse\info('Successfully deleted %d file' . ($total === 1 ? '' : 's') . '!', $total);
			}
			else
			{
				if($deleted > 0)
				{
					\kekse\info('Deleting %d file' . ($deleted === 1 ? '' : 's') . ' of %d in total worked, but ...', $deleted, $total);
				}
				
				if($failed > 0)
				{
					\kekse\warn('.. deletion of %d file' . ($failed === 1 ? '' : 's') . ' of %d in total failed!', $failed, $total);
				}
				
				if($failed !== $total)
				{
					\kekse\debug('So we only deleted ' . \kekse\console\ansi\bold('%0.2f%%') . '! :-/', ($deleted / $total * 100));
				}
			}

			//
			exit(0);
		}

		//
		function sync($_index)
		{
			return values($_index, true);
		}

		//
		function clean($_index)
		{
			//
			$withoutValues = checkArguments(['-w','--without-values']);
			$baseThreshold = getConfig('threshold');
			
			if($baseThreshold === null || (is_int($baseThreshold) && $baseThreshold < 1))
			{
				$baseThreshold = 0;
			}
			else if(!is_int($baseThreshold))
			{
				\kekse\error('Invalid `threshold` setting. Please check your configuration!');
				exit(1);
			}
			
			//
			$list = getHosts($_index);

			if($list === null)
			{
				\kekse\warn('No host(s) found.');
				exit(2);
			}
			else
			{
				\kekse\warn('Warning: `threshold` setting is obtained per-host, if configured!');
				
				if($baseThreshold === 0)
				{
					\kekse\warn('Without per-host overwrite, the whole cache directory will be deleted (because of no real `threshold` time)');
				}
				else
				{
					\kekse\debug('If no per-host overwrite is available, the default `threshold` will be %d seconds!', $baseThreshold);
				}
			}

			//
			$count = array();
			$delete = array();
			$hosts = array();
			$thresholds = array();
			$h = 0;
			$td = 0;
			$path = getState('path');
			$maxLen = 0;
			$hostLen = 0;
			
			foreach($list as $host => $type)
			{
				$delete[$host] = array();
				$count[$host] = 0;
				$hosts[$h++] = $host;
				$d = 0;
				
				if($type & COUNTER_DIR)
				{
					if($withoutValues && !($type & COUNTER_VALUE))
					{
						$delete[$host][$d++] = $path;
						++$td;
						
						if(($hostLen = \kekse\strlen($host, true)) > $maxLen)
						{
							$maxLen = $hostLen;
						}

						continue;
					}
					else
					{
						//
						if($type & COUNTER_CONFIG)
						{
							$conf = loadConfig(\kekse\joinPath($path, COUNTER_CONFIG_CHAR . $host), null, true);
							
							if($conf && array_key_exists('threshold', $conf))
							{
								if(($conf = $conf['threshold']) === null)
								{
									$threshold[$host] = 0;
								}
								else if(is_int($conf))
								{
									if($conf < 0)
									{
										$conf = 0;
									}
									
									$threshold[$host] = $conf;
								}
								else
								{
									$threshold[$host] = null;
								}
							}
							else
							{
								$threshold[$host] = null;
							}
						}
						else
						{
							$threshold[$host] = null;
						}

						//
						$handle = opendir(\kekse\joinPath($path, COUNTER_DIR_CHAR . $host));
						
						if($handle === false)
						{
							$count[$host] = null;
							$delete[$host] = null;
						}
						else
						{
							$count[$host] = 0;
							$delete[$host] = array();
							$d = 0;
							
							while($sub = readdir($handle))
							{
								if($sub === '.' || $sub === '..')
								{
									continue;
								}
								else if(KEKSE_KEEP_HIDDEN && $sub[0] === '.')
								{
									continue;
								}
								else if(KEKSE_KEEP && $sub === '.keep')
								{
									continue;
								}
								else if(KEKSE_KEEP_HTACCESS && $sub === '.htaccess')
								{
									continue;
								}
								
								$p = \kekse\joinPath($path, COUNTER_DIR_CHAR . $host, $sub);
								
								if(!is_file($p))
								{
									$delete[$host][$d++] = $p;
									
									if(is_dir($p))
									{
										++$td;
									}
								}
								else
								{
									$time = readInt($p);
									$ts = ($threshold[$host] === null ? $baseThreshold : $threshold[$host]);
									
									if($time === false)
									{
										continue;
									}
									else if(\kekse\timestamp($time) > $ts)
									{
										$delete[$host][$d++] = $p;
									}
									else
									{
										++$count[$host];
									}
								}
							}
							
							closedir($handle);

							if($d >= 0)
							{
								if(($hostLen = \kekse\strlen($host, true)) > $maxLen)
								{
									$maxLen = $hostLen;
								}
							}

							if($count[$host] === 0)
							{
								$p = \kekse\joinPath(getState('path'), COUNTER_DIR_CHAR . $host);
								if(file_exists($p)) $delete[$host][$d++] = $p;
								$p = \kekse\joinPath(getState('path'), COUNTER_FILE_CHAR . $host);
								if(file_exists($p)) $delete[$host][$d++] = $p;
							}
						}
					}
				}
				else if($type & COUNTER_FILE)
				{
					if(($hostLen = \kekse\strlen($host, true)) > $maxLen)
					{
						$maxLen = $hostLen;
					}

					$delete[$host] = array(\kekse\joinPath(getState('path'), COUNTER_FILE_CHAR . $host));
				}
			}

			//
			$del = array();
			$t = 0;
			$td = 0;

			foreach($delete as $host => $array)
			{
				$del[$host] = array();
				$d = 0;
				$len = count($array);

				for($i = 0; $i < $len; ++$i)
				{
					if(! in_array($array[$i], $del[$host]) && file_exists($array[$i]))
					{
						$del[$host][$d++] = $array[$i];
						++$t;
						
						if(is_dir($array[$i]))
						{
							++$td;
						}
					}
				}
			}

			$delete = $del;
			$cacheChange = 0;
			$changes = 0;
			$cacheValues = array();
			$cvcnt = 0;

			foreach($count as $host => $real)
			{
				if(!array_key_exists($host, $delete))
				{
					$delete[$host] = array();
				}
				
				if($real > 0)
				{
					$cache = 0;
					$path = \kekse\joinPath(getState('path'), COUNTER_FILE_CHAR . $host);
					
					if(is_file($path))
					{
						$cache = readInt($path);
						
						if($cache === false)
						{
							$cache = 0;
						}
					}
					else if(file_exists($path) && !in_array($path, $delete[$host]))
					{
						$delete[$host][] = $path;
						++$t;
						
						if(is_dir($path))
						{
							++$td;
						}
					}
					
					if($cache !== $real)
					{
						$cacheValues[$host] = [ $cache, $real ];
						++$cvcnt;
					}
				}
				else
				{
					$path = \kekse\joinPath(getState('path'), COUNTER_DIR_CHAR . $host);
					
					if(file_exists($path) && !in_array($path, $delete[$host]))
					{
						$delete[$host][] = $path;
						++$t;
						
						if(is_dir($path))
						{
							++$td;
						}
					}
					
					$path = \kekse\joinPath(getState('path'), COUNTER_FILE_CHAR . $host);
					
					if(file_exists($path) && !in_array($path, $delete[$host]))
					{
						$delete[$host][] = $path;
						++$t;
						
						if(is_dir($path))
						{
							++$td;
						}
					}
				}
			}

			//
			if($t === 0 && $cvcnt === 0)
			{
				\kekse\info('Everything\'s already UP TO DATE! Nothing to change or delete.. :-)');
				exit(0);
			}
			
			if($t > 0)
			{
				\kekse\info('Acquired %d item' . ($t === 1 ? '' : 's') . ' for deletetion, ...', $t);

				if($td > 0)
				{
					\kekse\debug('As there are %d directories selected, total file count may grow.', $td, $t);
				}
			}
			
			if($cvcnt > 0)
			{
				\kekse\info('... and %d cache file' . ($cvcnt === 1 ? '' : 's') . ' found for re-sync!', $cvcnt);
			}
						
			if(! \kekse\prompt('Do you want to start cleaning now [yes/no]? ', $t))
			{
				\kekse\warn('Aborted by request.. exiting.');
				exit(3);
			}

			//
			$totalChanges = 0;
			$totalHostChanges = array();
			$cacheSum = 0;		
			$outDatedSum = 0;

			//
			foreach($cacheValues as $host => $state)
			{
				$cacheSum += $state[1];
				$outDatedSum += $state[0];

				$p = \kekse\joinPath(getState('path'), COUNTER_FILE_CHAR . $host);
				$res = \kekse\readInt($p);
				
				if($res !== $state[1])
				{
					$res = \kekse\writeInt($p, $state[1]);
				
					if($res !== false)
					{
						++$totalChanges;
						$totalHostChanges[$host] = $state[1];
					}
				}
			}
			
			$totalFiles = 0;
			$deletedFiles = 0;
			$failedDeletions = 0;
			$deleteState = array();
			
			foreach($delete as $host => $list)
			{
				$deleteState[$host] = array(0, 0, 0);
				$len = count($list);
				
				for($i = 0; $i < $len; ++$i)
				{
					$r = \kekse\delete($list[$i], true, true);

					$deleteState[$host][0] += $r[0];
					$deleteState[$host][1] += $r[1];
					$deleteState[$host][2] += $r[2];
					
					$totalFiles += $r[0];
					$deletedFiles += $r[1];
					$failedDeletions += $r[2];
				}
			}

			//
			if($totalChanges > 0)
			{
				\kekse\info(\kekse\console\ansi\bold('Changed', true, 'info') . ' %d host caches.', $totalChanges);
			}
			
			//
			$hostCount = count($count);
			
			if($hostCount > 0)
			{
				$totalCacheFiles = 0;
				$maxLen = 0;
				$currLen = 0;
				
				foreach($count as $host => $real)
				{
					$totalCacheFiles += $real;
					
					if(($currLen = \kekse\strlen($host, true)) > $maxLen)
					{
						$maxLen = $currLen;
					}
				}

				\kekse\info('Totally counting %d cache file' . ($totalCacheFiles === 1 ? '' : 's') . ', within %d host' . ($hostCount === 1 ? '' : 's') . '.', $totalCacheFiles, $hostCount);
				\kekse\info('State for %d host' . ($hostCount === 1 ? '' : 's') . ' after cleaning up a bit:', $hostCount);
				printf(PHP_EOL);
				$format = ' ' . \kekse\console\ansi\hard(' %' . $maxLen . 's ', true, 1) . '      ' . \kekse\console\ansi\bold('%-10s', true, 1) . '  ' . \kekse\console\ansi\italic('%18s  %18s  ', true, 1) . ' %-14s' . PHP_EOL;
				$limit = false;
				$i = 0;

				$header = \kekse\console\ansi\underlineFaint('%-' . $maxLen . 's       %-10s%18s%18s      %-14s ' . PHP_EOL, true, 1);
				printf($header, ($maxLen === 0 ? '' : ' Host'), ' Real count', 'Changes', 'Deletions', ' Percent');

				foreach($count as $host => $real)
				{
					$r = (string)$real;
					$c = (isset($totalHostChanges[$host]) ? (string)$totalHostChanges[$host] . ' changes' : '');
					$d = '';
					$p = '';
					$c = (string)rand();
					if(isset($deleteState[$host]) && $deleteState[$host][0] > 0)
					{
						$d = $deleteState[$host][1] . ' / ' . $deleteState[$host][0] . ' deleted';
						$p = \kekse\console\ansi\bold((string)number_format($deleteState[$host][1] / $deleteState[$host][0] * 100, 2), true, 1) . '%';
					}

					printf($format, $host, $r, $c, $d, $p);
				}
				
				printf(PHP_EOL);
			}
			
			//
			$totalChanges; $totalFiles; $deletedFiles;

			//
			if($totalChanges > 0)
			{
				\kekse\info('Totally changed %d caches.', $totalChanges);
			}
			
			if($totalFiles > 0)
			{
				if($deletedFiles === $totalFiles)
				{
					\kekse\info('All %d files successfully deleted!', $totalFiles);
					exit(0);
				}

				\kekse\info('Successfully deleted %d of %d files.', $deletedFiles, $totalFiles);

				if($failedDeletions > 0)
				{
					\kekse\warn('But deletion failed for %d items!', $failedDeletions);
					
					if($totalFiles === $deletedFiles)
					{
						exit(4);
					}
				}

				\kekse\debug('So we deleted around ' . \kekse\console\ansi\bold('%0.2f%%', true, 'debug') . '!', ($deletedFiles / $totalFiles * 100));
			}
			
			//
			exit(4);
		}

		//
		function purge($_index)
		{
			//
			$list = getHosts($_index);

			if($list === null)
			{
				\kekse\warn('No host(s) found.');
				exit(1);
			}
			else
			{
				\kekse\warn('This will *delete* the whole cache (only)!');
				\kekse\debug('Maybe you\'d rather like to `' . \kekse\console\ansi\bold('--clean', true, 'debug') . ' / ' . \kekse\console\ansi\bold('-c', true, 'debug') . '` instead!?');
			}
			
			//
			$path = getState('path');
			$delete = array();
			$maxLen = 0;
			$total = 0;
			$len = 0;
			$h = 0;
			$d = 0;
			
			//
			foreach($list as $host => $state)
			{
				$delete[$host] = array();
				$a = 0;
				
				if($state & COUNTER_DIR)
				{
					$delete[$host][$a++] = \kekse\joinPath($path, COUNTER_DIR_CHAR . $host);
					++$total;
					++$d;
				}
				
				if($state & COUNTER_FILE)
				{
					$delete[$host][$a++] = \kekse\joinPath($path, COUNTER_FILE_CHAR . $host);
					++$total;
				}
				
				if($a === 0)
				{
					unset($delete[$host]);
				}
				else
				{
					++$h;
					
					if(($len = \kekse\strlen($host, true)) > $maxLen)
					{
						$maxLen = $len;
					}
				}
			}
			
			//
			if($h === 0)
			{
				\kekse\info('Nothing to remove. That\'s good! :-)');
				exit(0);
			}
			else if($d > 0)
			{
				\kekse\warn(($d === 1 ? 'One' : 'The %d') . ' director' . ($d === 1 ? 'y' : 'ies') . ' can contain more files, so recursive deletion could affect more than %d file' . ($total === 1 ? '' : 's'), $d, $total);
			}
			
			//
			if(!\kekse\prompt('Do you really want to purge %d file' . ($total === 1 ? '' : 's') . ' of %d host' . ($h === 1 ? '' : 's') . ' [yes/no]? ', $total, $h))
			{
				\kekse\warn('Aborted by request!');
				exit(2);
			}
			
			//
			$dTotal = 0;
			$dDeleted = 0;
			$dFailed = 0;
			$h = 0;
			
			//
			foreach($delete as $host => $files)
			{
				$delete[$host] = array(0, 0, 0);
				$all = count($files);
				$del = 0;
				for($i = 0; $i < $all; ++$i)
				{
					$dr = delete($files[$i], true, true);
					
					$del += $dr[1];
					
					$delete[$host][0] += $dr[0];
					$delete[$host][1] += $dr[1];
					$delete[$host][2] += $dr[2];
					
					$dTotal += $dr[0];
					$dDeleted += $dr[1];
					$dFailed += $dr[2];
				}
				
				if($del === 0)
				{
					unset($delete[$host]);
				}
				else
				{
					++$h;
				}
			}

			//
			if($dFailed === 0)
			{
				if($dTotal === 1)
				{
					\kekse\info('This file could be purged:');
				}
				else
				{
					\kekse\info(\kekse\console\ansi\bold('Every', true, 'info') . ' file of %d in total could be purged:', $dTotal);
				}
			}
			else
			{
				\kekse\info('We purged %d file' . ($dDeleted === 1 ? '' : 's') . ', with %d error' . ($dFailed === 1 ? '' : 's') . ' (so we deleted around ' . \kekse\console\ansi\bold('%0.2f%%', true, 'info') . '):', $dDeleted, $dFailed, ($dDeleted / $dTotal * 100));
			}
			
			printf(PHP_EOL);
			$format = ' ' . \kekse\console\ansi\hard(' %' . $maxLen . 's ') . '  %4d / %-4d    ' . \kekse\console\ansi\bold('%0.2f%%', true, true) . PHP_EOL;
			$limit = false;
			$i = 0;

			$header = \kekse\console\ansi\underlineFaint('%-' . $maxLen . 's     %-4s / %-4s %8s  ' . PHP_EOL, true, true);
			printf($header, ($maxLen === 0 ? '' : ' Host'), ' del', 'total', ' Percent');
			
			foreach($delete as $host => $state)
			{
				if($limit === false && ($limit = limitListCheck(++$i, $h)))
				{
					break;
				}
				
				if($state[2] = 0)
				{
					printf($format, $host, $state[1], $state[0], ($state[1] / $state[0] * 100));
				}
				else
				{
					fprintf(STDERR, $format, $host, $state[1], $state[0], ($state[1] / $state[0] * 100));
				}

			}
			
			printf(PHP_EOL);

			if($dFailed === 0)
			{
				\kekse\debug('Great, everything\'s fine! :-)');
				exit(0);
			}

			\kekse\debug('So we purged ' . \kekse\console\ansi\bold('%0.2f%%', true, 'debug') . ' in total!', ($dDeleted / $dTotal * 100));
			exit(3);
		}

		//
		function remove($_index)
		{
			//
			$config = checkArguments(['-g','--config']);
			$list = getHosts($_index);
			
			if($list === null)
			{
				\kekse\warn('No host(s) found.');
				exit(1);
			}
			else if($config)
			{
				\kekse\warn('Warning: you\'ve also selected to delete configuration files!');
			}
			
			//
			$path = getState('path');
			$hosts = array();
			$types = array();
			$totalHosts = 0;
			$totalFiles = 0;
			$maxLen = 0;
			$len = 0;
			$dirs = 0;

			foreach($list as $host => $state)
			{
				if(($len = \kekse\strlen($host, true)) > $maxLen)
				{
					$maxLen = $len;
				}
				
				$h = 0;
				++$totalHosts;
				$hosts[$host] = array();
				$types[$host] = '';
				$abc = 0;
				
				if($state & COUNTER_VALUE)
				{
					$hosts[$host][$h++] = \kekse\joinPath($path, COUNTER_VALUE_CHAR . $host);
					$types[$host] .= COUNTER_VALUE_CHAR . ' ';
					++$totalFiles;
					++$abc;
				}
				else
				{
					$types[$host] .= '  ';
				}
				
				if($state & COUNTER_DIR)
				{
					$hosts[$host][$h++] = \kekse\joinPath($path, COUNTER_DIR_CHAR . $host);
					$types[$host] .= COUNTER_DIR_CHAR . ' ';
					++$totalFiles;
					++$dirs;
					++$abc;
				}
				else
				{
					$types[$host] .= '  ';
				}
				
				if($state & COUNTER_FILE)
				{
					$hosts[$host][$h++] = \kekse\joinPath($path, COUNTER_FILE_CHAR . $host);
					$types[$host] .= COUNTER_FILE_CHAR . ' ';
					++$totalFiles;
					++$abc;
				}
				else
				{
					$types[$host] .= '  ';
				}
				
				if($config && ($state & COUNTER_CONFIG))
				{
					$hosts[$host][$h++] = \kekse\joinPath($path, COUNTER_CONFIG_CHAR . $host);
					$types[$host] .= COUNTER_CONFIG_CHAR . ' ';
					++$totalFiles;
					++$abc;
				}
				else
				{
					$types[$host] .= '  ';
				}

				if($abc === 0)
				{
					unset($types[$host]);
					unset($hosts[$host]);
					--$totalHosts;
				}
				else
				{
					$types[$host] = substr($types[$host], 0, -1);
				}
			}
			
			//
			if($totalHosts === 0)
			{
				\kekse\info('No host(s) affected, not files available.');
				exit(0);
			}
			else
			{
				\kekse\info(2, 'There ' . ($totalHosts === 1 ? 'is totally one host' : 'are totally %d hosts') . ' affected (with totally %d file' . ($totalFiles === 1 ? '' : 's') . '):', $totalHosts, $totalFiles);
			}
			
			//
			$format = ' ' . \kekse\console\ansi\hard(' %' . $maxLen . 's ', true, 1) . '    %s' . PHP_EOL;
			$limit = false;
			$i = 0;

			$header = \kekse\console\ansi\underlineFaint('%-' . $maxLen . 's     %s ' . PHP_EOL, true, 1);
			printf($header, ($maxLen === 0 ? '' : ' Host'), ' Selected types');
			
			foreach($types as $host => $selection)
			{
				if($limit === false && ($limit = limitListCheck(++$i, $totalHosts)))
				{
					break;
				}
				
				printf($format, $host, \kekse\console\ansi\bold($selection, true, STDOUT));
			}
			
			printf(PHP_EOL);
			
			//
			if($dirs)
			{
				$tmpSub = 'There ' . ($dirs === 1 ? 'is %d directory' : 'are %d directories') . ' selected, so the total amount of deletions could increase.';
				\kekse\warn($tmpSub, $dirs);
			}
			
			if(! \kekse\prompt('Do you really want to delete ' . ($totalFiles === 1 ? '%d counter file' : 'all %d counter files') . ' of %d host' . ($totalHosts === 1 ? '' : 's') . ' [yes/no]? ', $totalFiles, $totalHosts))
			{
				\kekse\warn('Aborted by request.');
				exit(2);
			}
			else
			{
				printf(PHP_EOL);
			}
			
			//
			$dTotal = 0;
			$dDeleted = 0;
			$dFailed = 0;
			
			$regular = ' %' . $maxLen . 's:  %d total, %d deleted, %d failed: ' . \kekse\console\ansi\bold('%0.2f%%', true, 'warn');
			$none = ' %' . $maxLen . 's:  ' . \kekse\console\ansi\bold('no', true, 'warn') . ' files of %d deleted! :-(';
			$all = ' %' . $maxLen . 's:  ' . \kekse\console\ansi\bold('all', true, 'info') . ' %d files deleted! :-D';
			$limit = false;
			$j = 0;
			
			foreach($hosts as $host => $files)
			{
				++$j;
				
				if($limit === false)
				{
					$limit = limitListCheck($j, $totalHosts);
				}
				
				$len = count($files);
				$total = 0;
				$deleted = 0;
				$failed = 0;
				
				for($i = 0; $i < $len; ++$i)
				{
					$dr = delete($files[$i], true, true);
					
					$dTotal += $dr[0];
					$total += $dr[0];
					
					$dDeleted += $dr[1];
					$deleted += $dr[1];
					
					$dFailed += $dr[2];
					$failed += $dr[2];
				}
				
				if(!$limit)
				{
					if($total === 0)
					{
						\kekse\warn($none, $host, $total);
					}
					else if($total === $deleted)
					{
						\kekse\info($all, $host, $deleted);
					}
					else
					{
						\kekse\warn($regular, $host, $total, $deleted, $failed, ($deleted / $total * 100));
					}
				}
			}
			
			printf(PHP_EOL);

			if($dFailed === 0)
			{
				if($dTotal === 1)
				{
					\kekse\info('The ' . \kekse\console\ansi\bold('only one', true, 'info') . ' file was successfully deleted! :-D');
				}
				else
				{
					\kekse\info(\kekse\console\ansi\bold('All', true, 'info') . ' %d files successfully deleted! :-D', $dTotal);
				}
			}
			else if($dTotal === $dFailed)
			{
				if($dTotal === 1)
				{
					\kekse\error('The only ' . \kekse\console\ansi\bold('one', true, 'error') . ' file couldn\'t be deleted! :-(');
				}
				else
				{
					\kekse\error(\kekse\console\ansi\bold('No', true, 'error') . ' single file of all %d could be deleted! :-(', $dTotal);
				}

				exit(3);
			}
			else
			{
				\kekse\info('Only %d file' . ($dDeleted === 1 ? '' : 's') . ' could be deleted!', $dDeleted);
				\kekse\warn('Errors occured with %d file' . ($dFailed === 1 ? '' : 's') . ', so you deleted only ' . \kekse\console\ansi\bold('%0.2f%%', true, 'warn') . '!', $dFailed, ($dDeleted / $dTotal * 100));
				exit(4);
			}
			
			//
			exit(0);
		}

		//
		function sanitize($_index)
		{
			//
			$withoutValues = checkArguments(['-w','--without-values']);
			$path = getState('path');

			//
			if(! checkPath($path, false, 'sanitize', false))
			{
				\kekse\error('Can\'t access counter directory `%s`!', getState('path'));
				exit(1);
			}
			
			$handle = opendir($path);
			
			if($handle === false)
			{
				\kekse\error('Couldn\'t opendir() counter directory `%s`!', $path);
				exit(2);
			}
			else if($withoutValues)
			{
				\kekse\warn('All host files without corresponding value file will also get deleted!');
				\kekse\debug('But this doesn\'t affect per-host configuration files (`' . COUNTER_CONFIG_CHAR . '` prefix).');
			}
			
			//
			$valueFiles = ($withoutValues ? array() : null);
			$v = 0;
			$deletion = array();
			$d = 0;
			$dirs = 0;

			if($withoutValues)
			{
				while($sub = readdir($handle))
				{
					if($sub === '.' || $sub === '..')
					{
						continue;
					}
					else if(KEKSE_KEEP_HIDDEN && $sub[0] === '.')
					{
						continue;
					}
					else if(KEKSE_KEEP && $sub === '.keep')
					{
						continue;
					}
					else if(KEKSE_KEEP_HTACCESS && $sub === '.htaccess')
					{
						continue;
					}
					else if($sub[0] !== COUNTER_VALUE_CHAR)
					{
						continue;
					}
					
					$pp = \kekse\joinPath($path, $sub);
					
					if(! is_file($pp))
					{
						continue;
					}
					else
					{
						$valueFiles[$v++] = substr($sub, 1);
					}
				}
				
				rewinddir($handle);
			}
						
			while($sub = readdir($handle))
			{
				if($sub === '.' || $sub === '..')
				{
					continue;
				}
				else if(KEKSE_KEEP_HIDDEN && $sub[0] === '.')
				{
					continue;
				}
				else if(KEKSE_KEEP && $sub === '.keep')
				{
					continue;
				}
				else if(KEKSE_KEEP_HTACCESS && $sub === '.htaccess')
				{
					continue;
				}
				
				$len = strlen($sub);
				$p = \kekse\joinPath($path, $sub);

				if($len === 1)
				{
					$deletion[$d++] = $p;
					$del = true;
					
					if(is_dir($p))
					{
						++$dirs;
					}
				}
				else
				{
					$type = $sub[0];
					$host = substr($sub, 1);
					
					switch($type)
					{
						case COUNTER_VALUE_CHAR:
							if(! is_file($p))
							{
								$deletion[$d++] = $p;
								
								if(is_dir($p))
								{
									++$dirs;
								}
							}
							break;
						case COUNTER_DIR_CHAR:
							if(! is_dir($p))
							{
								$deletion[$d++] = $p;
							}
							else
							{
								$sub_handle;
								
								if($v > 0 && !in_array($host, $valueFiles))
								{
									$deletion[$d++] = $p;
									++$dirs;
								}
								else if(is_dir($p))
								{
									$sub_handle = opendir($p);
									
									if($sub_handle === false)
									{
										continue 2;
									}
									else while($ss = readdir($sub_handle))
									{
										if($ss === '.' || $ss === '..')
										{
											continue;
										}
										else if(KEKSE_KEEP_HIDDEN && $ss[0] === '.')
										{
											continue;
										}
										else if(KEKSE_KEEP && $ss === '.keep')
										{
											continue;
										}
										else if(KEKSE_KEEP_HTACCESS && $ss === '.htaccess')
										{
											continue;
										}
										
										$pp = \kekse\joinPath($p, $ss);
										
										if(!is_file($pp))
										{
											$deletion[$d++] = $pp;
											
											if(is_dir($pp))
											{
												++$dirs;
											}
										}
									}
								}
							}
							break;
						case COUNTER_FILE_CHAR:
							if(! is_file($p))
							{
								$deletion[$d++] = $p;
								
								if(is_dir($p))
								{
									++$dirs;
								}
							}
							else if($v > 0 && !in_array($host, $valueFiles))
							{
								$deletion[$d++] = $p;
								++$dirs;
							}
							break;
						case COUNTER_CONFIG_CHAR:
							if(! is_file($p))
							{
								$deletion[$d++] = $p;
								
								if(is_dir($p))
								{
									++$dirs;
								}
							}
							break;
						default:
							$deletion[$d++] = $p;
							
							if(is_dir($p))
							{
								++$dirs;
							}
							break;
					}
				}
			}
			
			//
			if($d === 0)
			{
				\kekse\info('No invalid file(s) found. :-)');
				exit(0);
			}
			else
			{
				if($dirs > 0)
				{
					\kekse\warn('Selected %d director' . ($dirs === 1 ? 'y' : 'ies') . ', so recursive deletion could result in more deleted files than these:', $dirs);
				}
				else
				{
					\kekse\info('No directories selected, so the following list is the real amount of deleted files:');
				}
				
				printf(PHP_EOL);
				$limit = false;
				
				for($i = 0; $i < $d; ++$i)
				{
					if($limit === false && ($limit = limitListCheck($i + 1, $d)))
					{
						break;
					}
					
					printf('    %s' . PHP_EOL, $deletion[$i]);
				}
				
				printf(PHP_EOL);
				
				if(! \kekse\prompt('Allow deletion of ' . ($d === 1 ? 'one' : '%d') . ' file' . ($d === 1 ? '' : 's') . ' [yes/no]? ', $d))
				{
					\kekse\warn('Aborting here, as requested.');
					exit(3);
				}
			}
			
			//
			$dTotal = 0;
			$dDeleted = 0;
			$dFailed = 0;
			
			for($i = 0; $i < $d; ++$i)
			{
				$dr = delete($deletion[$i], true, true);
				
				$dTotal += $dr[0];
				$dDeleted += $dr[1];
				$dFailed += $dr[2];
			}
			
			//
			$avg = 1.0;
			
			if($dFailed > 0)
			{
				$avg = ($dDeleted / $dTotal);
			}
			
			if($dDeleted > 0)
			{
				\kekse\info('Sucessfully deleted %d file' . ($dDeleted === 1 ? '' : 's') . ' in total.', $dDeleted);
			}
			
			if($avg != 1)
			{
				if($avg == 0)
				{
					\kekse\error('Not a single file could be deleted!');
				}
				else
				{
					\kekse\warn(($dDeleted === 0 ? 'D' : 'But d') . 'eletion of %d files failed, so only ' . \kekse\console\ansi\bold('%0.2f%%', true, 'warn') . ' deleted!', $dFailed, ($avg * 100));
				}
			}
			
			//
			exit(0);
		}

		//
		function unlog($_index)
		{
			//error_reporting(0);
			$log = getState('log');

			if(! file_exists($log))
			{
				\kekse\info('There is no log file which could be deleted. .. good for you!', basename($log));
				exit(0);
			}
			else if(!is_file($log))
			{
				if(delete($log, true, false))
				{
					\kekse\warn('The log file `%s` was no regular file, but deletion was successful!', $log);
					exit(1);
				}

				\kekse\error('The log file `%s` is not a regular file, and couldn\'t be deleted!', $log);
				exit(2);
			}
			else if(!\kekse\prompt('Do you really want to delete the log file `' . basename($log) . '` [yes/no]? '))
			{
				\kekse\warn('Log file deletion aborted (by request).');
				exit(3);
			}
			else if(! delete($log, false, false))
			{
				\kekse\error('The log path `%s` couldn\'t be deleted!!', basename($log));
				exit(4);
			}

			\kekse\info('The log file `%s` is no longer! :-)', $log);
			exit(0);
		}

		function errors($_index)
		{
			$log = getState('log');
			
			if(! file_exists($log))
			{
				\kekse\info('No errors logged (in `%s`)! :-D', $log);
				exit(0);
			}
			else if(!is_file($log))
			{
				if(delete($log, true, false))
				{
					\kekse\warn('The log file `%s` was no regular file, but deletion was successful!', $log);
					exit(1);
				}
				
				\kekse\error('The log path `%s` is no regular file, and couldn\'t be deleted!', $log);
				exit(2);
			}
			
			if(! checkPath($log, true, 'errors', true))
			{
				\kekse\error('Couldn\'t access log file `%s`!', $log);
				exit(3);
			}

			function countLines($_file, $_chunks = 4096)
			{
				$handle = fopen($_file, 'r');
				
				if($handle === false)
				{
					\kekse\error('Couldn\'t open file handle for log file `%s`!', $log);
					exit(4);
				}

				$result = 0;
				$last = '';
				$char;

				while(($char = fgetc($handle)) !== false)
				{
					if($char === "\n" || $char === "\r")
					{
						if($last === '')
						{
							++$result;
						}
						else if($last === $char)
						{
							++$result;
						}

						$last = $char;
					}
					else
					{
						$last = '';
					}
				}

				fclose($handle);
				return $result;
			}

			$result = countLines($log);
			
			if($result === 0)
			{
				delete($log, false, false);
				\kekse\info(\kekse\console\ansi\bold('No', true, 'info') . ' entries in log file (`%s`).', $log);
				exit(0);
			}
			else
			{
				\kekse\warn('There are %d log lines (in `%s`)', $result, $log);
			}

			exit(4);
		}

		//
		if($GLOBALS['KEKSE_ARGC'] > 1)
		{
			//
			$index = -1;
			$func = null;
			$params = array();
			
			//
			for($i = 0; $i < $GLOBALS['KEKSE_ARGC']; ++$i)
			{
				$item = $GLOBALS['KEKSE_ARGV'][$i];

				if($item === '' || $item[0] !== '-')
				{
					continue;
				}
				else if($item === '--')
				{
					break;
				}
				
				$len = strlen($item);

				if($len < 2 || $len > KEKSE_LIMIT_STRING)			
				{
					continue;
				}
				else if($len === 2) switch($item)
				{
					case '-?':
						$func = 'help';
						$index = $i;
						break;
					case '-V':
						$func = 'info';
						$index = $i;
						$params[0] = true;
						$params[1] = false;
						$params[2] = false;
						break;
					case '-C':
						$func = 'info';
						$index = $i;
						$params[0] = false;
						$params[1] = true;
						$params[2] = false;
						break;
					case '-W':
						$func = 'info';
						$index = $i;
						$params[0] = false;
						$params[1] = false;
						$params[2] = true;
						break;
					case '-I':
						$func = 'info';
						$index = $i;
						$params[0] = true;
						$params[1] = true;
						$params[2] = true;
						break;
					case '-c':
						$func = 'config';
						$index = $i;
						break;
					case '-v':
						$func = 'values';
						$index = $i;
						break;
					case '-y':
						$func = 'sync';
						$index = $i;
						break;
					case '-l':
						$func = 'clean';
						$index = $i;
						break;
					case '-p':
						$func = 'purge';
						$index = $i;
						break;
					case '-z':
						$func = 'sanitize';
						$index = $i;
						break;
					case '-r':
						$func = 'remove';
						$index = $i;
						break;
					case '-s':
						$func = 'set';
						$index = $i;
						break;
					case '-f':
						$func = 'fonts';
						$index = $i;
						break;
					case '-y':
						$func = 'types';
						$index = $i;
						break;
					case '-h':
						$func = 'hashes';
						$index = $i;
						break;
					case '-e':
						$func = 'errors';
						$index = $i;
						break;
					case '-u':
						$func = 'unlog';
						$index = $i;
						break;
				}
				else switch($item)
				{
					case '--help':
						$func = 'help';
						$index = $i;
						break;
					case '--version':
						$func = 'info';
						$index = $i;
						$params[0] = true;
						$params[1] = false;
						$params[2] = false;
						break;
					case '--copyright':
						$func = 'info';
						$index = $i;
						$params[0] = false;
						$params[1] = true;
						$params[2] = false;
						break;
					case '--website':
						$func = 'info';
						$index = $i;
						$params[0] = false;
						$params[1] = false;
						$params[2] = true;
						break;
					case '--info':
						$func = 'info';
						$index = $i;
						$params[0] = true;
						$params[1] = true;
						$params[2] = true;
						break;
					case '--config':
						$func = 'config';
						$index = $i;
						break;
					case '--values':
						$func = 'values';
						$index = $i;
						break;
					case '--sync':
						$func = 'sync';
						$index = $i;
						break;
					case '--clean':
						$func = 'clean';
						$index = $i;
						break;
					case '--purge':
						$func = 'purge';
						$index = $i;
						break;
					case '--sanitize':
						$func = 'sanitize';
						$index = $i;
						break;
					case '--remove':
						$func = 'remove';
						$index = $i;
						break;
					case '--set':
						$func = 'set';
						$index = $i;
						break;
					case '--fonts':
						$func = 'fonts';
						$index = $i;
						break;
					case '--types':
						$func = 'types';
						$index = $i;
						break;
					case '--hashes':
						$func = 'hashes';
						$index = $i;
						break;
					case '--errors':
						$func = 'errors';
						$index = $i;
						break;
					case '--unlog':
						$func = 'unlog';
						$index = $i;
						break;
				}
				
				if($func !== null)
				{
					break;
				}
			}

			if($index === -1 || !$func)
			{
				\kekse\error(2, \kekse\console\ansi\bold('Unknown parameter' . ($GLOBALS['KEKSE_ARGC'] === 2 ? '' : 's'), true, 'error') . '!');
				help(-1, null);
				printf(PHP_EOL);
				\kekse\error(\kekse\console\ansi\bold('Unknown parameter' . ($GLOBALS['KEKSE_ARGC'] === 2 ? '' : 's'), true, 'error') . '!');
				exit(1);
			}
			else
			{
				array_splice($GLOBALS['KEKSE_ARGV'], $index--, 1);
				--$GLOBALS['KEKSE_ARGC'];
				
				$func = '\kekse\counter\\' . $func;
				$func($index, ... $params);
				
				exit(0);
			}
		}
		
		//
		\kekse\debug('Call with `' . \kekse\console\ansi\bold('--help', true, 'debug') . ' / ' . \kekse\console\ansi\bold('-?', true, 'debug') . '` to see a list of available parameters.');
		values(0);
		
		//
		exit(0);
	}
	else if(KEKSE_CLI && !is_string($_host))
	{
		error('In CLI mode, you\'ve to define the $_host argument');
	}
	else
	{
		$_host = \kekse\secureHost($_host);
	}

	//
	function withServer($_threshold_test = true)
	{
		if(!getState('address'))
		{
			return false;
		}
		else if($_threshold_test)
		{
			$conf = getConfig('threshold');
		
			if($conf === null || $conf < 1)
			{
				return false;
			}
		}

		return !!getConfig('server');
	}

	function withClient($_threshold_test = true)
	{
		if(!getState('address'))
		{
			return false;
		}
		else if($_threshold_test)
		{
			$conf = getConfig('threshold');
		
			if($conf === null || $conf < 1)
			{
				return false;
			}
		}

		return !!getConfig('client');
	}

	//
	function setup($_host = null)
	{
		//
		function removePort($_host)
		{
			if(!is_string($_host) || $_host === '')
			{
				return null;
			}
			
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
			
			if($port !== null && \kekse\endsWith($_host, (':' . $port)))
			{
				$result = substr($_host, 0, -(strlen($port) + 1));
			}
			else
			{
				$result = $_host;
			}
			
			return $result;
		}

		function getHost($_host = null, $_die = true)
		{
			//
			$result = null;
			$overridden = false;

			//
			if(is_string($result = $_host))
			{
				$overridden = true;
			}
			else if(is_string(($result = getConfig('override'))) && $result !== '')
			{
				$overridden = true;
			}
			else if(is_string($result = \kekse\getParam('override', false, false, true)) && $result !== '')
			{
				if(! getConfig('override'))
				{
					$result = null;

					if($_die)
					{
						error('You can\'t define `?override` without `override` setting enabled');
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
			setState('overridden', $overridden);

			//
			if($result !== null)
			{
				$result = \kekse\secureHost(removePort($result, $_die));

				if($result === null && $_die)
				{
					error('Invalid host');
				}
			}
			
			return $result;
		}

		//
		$_host = getHost($_host);
		setState('host', $_host);
		makeConfig($_host);

		//
		if(getState('ro'))
		{
			setState('cookie', null);
			setState('dir', null);
			setState('file', null);
		}
		else
		{
			//
			setState('cookie', (withClient() ? \kekse\limit(hash(getConfig('hash'), getState('host'))) : null));

			//
			if(withServer())
			{
				$path = getState('path');
				$host = getState('host');
				
				setState('dir', \kekse\joinPath($path, COUNTER_DIR_CHAR . $host));
				setState('file', \kekse\joinPath($path, COUNTER_FILE_CHAR . $host));
				
				if(! checkPath(getState('dir'), false, 'setup', true, true))
				{
					setState('dir', null);
				}
				
				if(! checkPath(getState('file'), true, 'setup', true, true))
				{
					setState('file', null);
				}
			}
			else
			{
				setState('dir', null);
				setState('file', null);
			}
		}

		//
		if(getState('address') === null)
		{
			setState('ip', null);
			setState('remote', null);
		}
		else
		{
			if(getConfig('privacy'))
			{
				setState('remote', \kekse\limit(hash(getConfig('hash'), getState('address'))));
			}
			else
			{
				setState('remote', getState('address'));
			}

			if(getState('dir'))
			{
				setState('ip', \kekse\joinPath(getState('dir'), \kekse\securePath(getState('remote'))));
			
				if(! checkPath(getState('ip'), true, 'setup', true, true))
				{
					setState('ip', null);
				}
			}
			else
			{
				setState('ip', null);
			}
		}

		//
		$path = getState('path');

		//
		if(!getState('test'))
		{
			//
			setState('value', \kekse\joinPath($path, COUNTER_VALUE_CHAR . getState('host')));
			
			if(! checkPath(getState('value'), true, 'setup', true, false))
			{
				error('Value file not accessable');
			}

			//
			function checkAuto()
			{
				//
				$path = getState('path');
				
				//
				if(! checkPath(getState('path'), false, 'checkAuto', false))
				{
					return false;
				}

				$auto = getConfig('auto');

				if($auto === null)
				{
					return false;
				}

				$exists = file_exists(getState('value'));

				if($exists)
				{
					if(checkPath(getState('value'), true, 'checkAuto', false))
					{
						return true;
					}
					else if(!is_file(getState('value')))
					{
						if(delete(getState('value'), true, false))
						{
							$exists = false;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}
				}

				$values = 0;
				$total = 0;
				$handle = opendir($path);
				
				if($handle === false)
				{
					logError('Couldn\'t opendir()', 'checkAuto', $path, false);
					return false;
				}
				else
				{
					while($sub = readdir($handle))
					{
						if($sub[0] === '.')
						{
							continue;
						}
						else
						{
							++$total;
						}
						
						$pp = \kekse\joinPath($path, $sub);

						if($sub[0] === COUNTER_VALUE_CHAR && is_file($pp))
						{
							if(checkPath($pp, true, 'checkAuto', true))
							{
								++$values;
							}
						}
					}

					closedir($handle);
				}

				$limit = getConfig('limit');

				if($total >= $limit)
				{
					logError('Limit exceeded (' . (string)$limit . ')', 'checkAuto', $path, false);
					return false;
				}
				else if(is_string(getConfig('override')) && getConfig('override') !== '')
				{
					return true;
				}
				else if(getState('overridden'))
				{
					return false;
				}
				
				$auto = getConfig('auto');
				
				if(is_bool($auto))
				{
					if(!$auto)
					{
						return false;
					}
				}
				else if(is_int($auto))
				{
					if($values >= $auto)
					{
						return false;
					}
				}
				else
				{
					logError('Invalid `auto` configuration', 'checkAuto', '', false);
					return false;
				}

				return true;
			}

			//
			if(! checkAuto())
			{
				error(getConfig('none'), true);
				exit(127);
			}
		}

		//
		if(! getState('ro'))
		{
			function test($_threshold_test = true)
			{
				$result = true;

				if(withClient($_threshold_test))
				{
					$result = testCookie(false, false);
				}

				if($result && withServer($_threshold_test))
				{
					$result = testFile(false);
				}

				return $result;
			}

			function testFile($_threshold_test = true)
			{
				//
				if(getState('ip') === null)
				{
					return true;
				}
				
				$ts = getConfig('threshold');
				
				if($_threshold_test && (!is_int($ts) || $ts < 1))
				{
					return true;
				}
				else if(checkPath(getState('ip'), true, 'testFile', false))
				{
					if(is_int($ts) && \kekse\timestamp(readTimestamp()) <= $ts)
					{
						return false;
					}
				}

				return true;
			}

			function testCookie($_threshold_test = true)
			{
				if(!getState('cookie'))
				{
					return true;
				}
				
				$ts = getConfig('threshold');
				
				if($_threshold_test && (!is_int($ts) || $ts < 1))
				{
					return true;
				}
				else if(empty($_COOKIE[getState('cookie')]))
				{
					makeCookie();
				}
				else if(\kekse\timestamp((int)$_COOKIE[getState('cookie')]) <= $ts)
				{
					return false;
				}

				return true;
			}

			function makeCookie()
			{
				if(!getState('cookie'))
				{
					return null;
				}
				
				$ts = getConfig('threshold');
				
				if(!is_int($ts) || $ts < 1)
				{
					return null;
				}
				
				return setcookie(getState('cookie'), (string)\kekse\timestamp(), array(
					'expires' => (time() + $ts),
					'path' => '/',
					'samesite' => 'Strict',
					'secure' => false, //!empty($_SERVER['HTTPS']);
					'httponly' => true
				));
			}

			function cleanFiles()
			{
				//
				if(getConfig('clean') === null)
				{
					logError('Called function, but `clean` is configured as (null), so it\'s forbidden!', 'cleanFiles', getState('dir'), false);
					return readCount();
				}
				else if(! checkPath(getState('dir'), false, 'cleanFiles', true))
				{
					return initCount(false);
				}

				$dir = getState('dir');
				$handle = loaddir($dir);
				
				if($handle === false)
				{
					logError('Can\'t loaddir()', 'cleanFiles', $dir, false);
					return readCount();
				}
				
				$threshold = getConfig('threshold');

				if(is_int($threshold))
				{
					if($threshold < 1)
					{
						$threshold = 0;
					}
				}
				else
				{
					$threshold = 0;
				}

				if($threshold === 0)
				{
					if(delete($dir, true, false))
					{
						if(! delete(getState('file'), true, false))
						{
							logError('Couldn\'t delete cache file', 'cleanFiles', getState('file'), false);
						}
						
						return 0;
					}
					else
					{
						logError('Couldn\'t delete whole cache directory', 'cleanFiles', $dir, false);
					}
				}
				
				$result = 0;

				while($sub = readdir($handle))
				{
					if($sub[0] === '.')
					{
						continue;
					}

					$p = \kekse\joinPath($dir, $sub);

					if(! is_file($p))
					{
						if(! delete($p, true, false))
						{
							logError('Invalid cache file couldn\'t be deleted', 'cleanFiles', $p, false);
						}
					}
					else
					{
						$time = readInt($p);

						if($time === false)
						{
							logError('Couldn\'t read cache file', 'cleanFiles', $p, false);
							++$result;
						}
						else if(\kekse\timestamp($time) <= $threshold)
						{
							++$result;
						}
						else if(! delete($p, false, false))
						{
							logError('Valid cache file (with outdated timestamp) couldn\'t be deleted', 'cleanFiles', $p, false);
							++$result;
						}
					}
				}

				closedir($handle);

				if($result === 0)
				{
					if(! delete($dir, true, false))
					{
						logError('Couldn\'t delete empty cache directory', 'cleanFiles', $dir, false);
					}

					if(! delete(getState('file'), true, false))
					{
						logError('Couldn\'t delete cache file', 'cleanFiles', getState('file'), false);
						writeCount(0, false);
					}

					return 0;
				}

				return writeCount($result, false);
			}

			function initCount()
			{
				//
				$value = 0;
				$dir = getState('dir');

				if(! checkPath($dir, false, 'initCount', true))
				{
					if(checkPath(getState('file'), true, 'initCount', true))
					{
						if(! delete(getState('file'), true, false))
						{
							logError('Couldn\'t delete cache file, maybe invalid', 'initCount', getState('file'), false);
						}
					}
					
					return false;
				}
				else if(! checkPath(getState('file'), true, 'initCount', true))
				{
					return false;
				}
				
				$handle = loaddir($dir);
				
				if($handle === false)
				{
					logError('Couldn\'t loaddir() cache directory', 'initCount', $dir, false);
					return false;
				}
				else while($sub = readdir($handle))
				{
					if($sub[0] === '.')
					{
						continue;
					}
					else if(! checkPath(\kekse\joinPath($dir, $sub), true, 'initCount', false))
					{
						continue;
					}
					else
					{
						++$value;
					}
				}
				
				closedir($handle);

				//
				if($value <= 0)
				{
					if(file_exists($dir) && !delete($dir, true, false))
					{
						logError('Unable to delete cache directory', 'initCount', $dir, false);
					}

					if(file_exists(getState('file')) && !delete(getState('file'), true, false))
					{
						logError('Unable to delete cache file', 'initCount', getState('file'), false);
					}

					return 0;
				}
				
				//
				$result = writeInt(getState('file'), $value);
				
				if($result === false)
				{
					logError('Couldn\'t initialize cache file (with value ' . $value . ')', 'initCount', getState('file'), false);
					return false;
				}
				
				//
				return $value;
			}

			function readCount()
			{
				//
				if(! checkPath(getState('file'), true, 'readCount', true))
				{
					return 0;
				}
				
				$result = readInt(getState('file'));
				
				if($result === false)
				{
					logError('Couldn\'t read cache file', 'readCount', getState('file'), false);
					return false;
				}
				
				return $result;
			}

			function writeCount($_value, $_get = true, $_purge = true)
			{
				//
				$file = getState('file');
				
				//
				if($_value <= 0)
				{
					if($_purge)
					{
						if(file_exists(getState('dir')) && !delete(getState('dir'), true, false))
						{
							logError('Couldn\'t delete cache directory (due to value 0)', 'writeCount', getState('dir'), false);
						}

						if(file_exists($file) && !delete($file, true, false))
						{
							logError('Couldn\'t delete cache file (due to value 0)', 'writeCount', $file, false);
						}

						return false;
					}
					
					$_value = 0;
				}
				
				//
				if(! checkPath($file, true, 'writeCount', true, true))
				{
					return false;
				}

				//
				$result = null;

				if($_get)
				{
					if(!($result = readCount()))
					{
						$result = null;
					}
				}
				
				$r = writeInt($file, $_value);
				
				if($r === false)
				{
					logError('Unable to write cache file (with value ' . $_value . ')', 'writeCount', $file, false);
					return readCount();
				}
				
				return $result;
			}

			function increaseCount($_by = 1)
			{
				$result = (readCount() + $_by);
				writeCount($result, false);
				return $result;
			}

			function decreaseCount($_by = 1)
			{
				$result = (readCount() - $_by);

				if($result < 0)
				{
					$result = 0;
				}

				writeCount($result, false);
				return $result;
			}
			
			function writeTimestamp($_clean = null)
			{
				//
				if(!is_bool($_clean))
				{
					$_clean = (getConfig('clean') !== null);
				}

				//
				$existed = is_file(getState('ip'));

				//
				if(! checkPath(getState('dir'), false, 'writeTimestamp', true))
				{
					return 0;
				}
				else if(! checkPath(getState('ip'), true, 'writeTimestamp', true))
				{
					return 0;
				}

				//
				if(! $existed && readCount() > getConfig('limit'))
				{
					if($_clean)
					{
						if(cleanFiles() > getConfig('limit'))
						{
							logError('Cache exceeded limit, even after cleaning up', 'writeTimestamp', getConfig('dir'), false);
							return 0;
						}
					}
					else
					{
						logError('Cache exceeded limit, and cleaning is disabled/forbidden', 'writeTimestamp', getConfig('dir'), false);
						return 0;
					}
				}
				
				//
				$timestamp = \kekse\timestamp();
				$res = writeInt(getState('ip'), $timestamp);
				
				if($res === false)
				{
					logError('Couldn\'t write timestamp to file', 'writeTimestamp', getState('ip'), false);
					return 0;
				}
				else if(!$existed)
				{
					increaseCount();
				}
				
				//
				return $timestamp;
			}

			function writeValue($_value)
			{
				//
				if(!is_int($_value))
				{
					logError('Value was no integer', 'writeValue', '', false);
					return false;
				}
				else if($_value < 0)
				{
					$_value = 0;
				}
				
				if(! checkPath(getState('value'), true, 'writeValue', true))
				{
					return false;
				}

				$res = writeInt(getState('value'), $_value);
				
				if($res === false)
				{
					logError('Couldn\'t write value (' . $_value . ') to file', 'writeValue', getState('value'), false);
					return false;
				}
				
				return $_value;
			}
		}

		function readTimestamp()
		{
			//
			if(! checkPath(getState('dir'), false, 'readTimestamp', true))
			{
				return 0;
			}
			else if(! checkPath(getState('ip'), true, 'readTimestamp', true))
			{
				return 0;
			}
			
			$result = readInt(getState('ip'));
			
			if($result === false)
			{
				logError('Couldn\'t read timestamp', 'readTimestamp', getState('ip'), false);
				return 0;
			}
			
			return $result;
		}

		function readValue()
		{
			if(! checkPath(getState('value'), true, 'readValue', true))
			{
				error('Unable to access value file');
			}

			$result = readInt(getState('value'));
			
			if($result === false)
			{
				logError('Can\'t read value file', 'readValue', getState('value'), true);
				error('Can\'t read value file');
			}
			
			return $result;
		}
	}

	//
	setup($_host);

	//
	if(getConfig('drawing'))
	{
		//
		function draw($_text, $_zero = null)
		{
			//
			if(!is_bool($_zero))
			{
				$_zero = !!getState('zero');
			}
			
			//
			function drawingError($_reason)
			{
				return error($_reason);
			}

			function getDrawingType()
			{
				//
				$result = \kekse\getParam('type', false, false, true);
				$byParam = true;

				if($result === null)
				{
					$result = getConfig('type');
					$byParam = false;
				}

				$result = strtolower($result);
				$types = imagetypes();

				switch($result)
				{
					case 'png':
						if(! ($types & IMG_PNG))
						{
							drawingError('`' . ($byParam ? '?' : '') . 'type` is not supported');
							return null;
						}
						break;
					case 'jpg':
						if(! ($types & IMG_JPG))
						{
							drawingError('`' . ($byParam ? '?' : '') . 'type` is not supported');
							return null;
						}
						break;
					default:
						drawingError('`' . ($byParam ? '?' : '') . 'type` is not supported');
						return null;
				}

				return $result;
			}

			//
			if($_zero)
			{
				//
				function drawZero($_type)
				{
					//
					if(getState('sent'))
					{
						drawingError('Header already sent (unexpected here)');
						return null;
					}

					//
					$image = imagecreatetruecolor(1, 1);
					imagesavealpha($image, true);
					imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 255, 255, 127));
					
					//
					$sent = null;
					
					switch($_type)
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
						drawingError('Header couldn\'t be sent');
						return false;
					}
					
					return true;
				}
				
				//
				return drawZero(getDrawingType());
			}

			//
			function getFont($_name)
			{
				if(!is_string($_name) || $_name === '')
				{
					return null;
				}
				else if(substr($_name, -4) !== '.ttf')
				{
					$_name .= '.ttf';
				}
				else if(! getState('fonts'))
				{
					return null;
				}
				
				$result = \kekse\joinPath(getState('fonts'), $_name);

				if(is_file($result))
				{
					return $result;
				}
				
				return null;
			}

			function checkAngle($_value)
			{
				$result = null;

				if(is_string($_value))
				{
					$unit = \kekse\unit($_value, true);

					switch($unit[1])
					{
						case 'rad':
							$result = rad2deg($unit[0]);
							break;
						case 'deg':
						case '':
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
					$result = fmod($result, 360);
				}

				if($result !== null && $result < 0)
				{
					$result = abs(360 + $result);
				}
				
				if($result !== null)
				{
					$result = (float)round($result, 2);
				}

				return $result;
			}
			
			function getDrawingOptions($_die = true)
			{
				//
				$result = array();

				//
				if(($result['type'] = getDrawingType()) === null)
				{
					return null;
				}
				else
				{
					$result['unit'] = \kekse\getParam('unit', false);
					$result['font'] = \kekse\getParam('font', false);
					$result['fg'] = \kekse\getParam('fg', false);
					$result['bg'] = \kekse\getParam('bg', false);
					$result['size'] = \kekse\getParam('size', true, true, false);
					$result['h'] = \kekse\getParam('h', true, true, false);
					$result['v'] = \kekse\getParam('v', true, true, false);
					$result['x'] = \kekse\getParam('x', true, true, false);
					$result['y'] = \kekse\getParam('y', true, true, false);
					$result['angle'] = \kekse\getParam('angle', true, true, false);
					$result['min'] = \kekse\getParam('min', null);
				}

				//
				$byParam = array();
				
				//
				$byParam['min'] = true;

				if($result['min'] === null)
				{
					$result['min'] = getConfig('min');
					$byParam['min'] = false;
				}

				//
				$byParam['unit'] = true;
				
				if($result['unit'] === null)
				{
					$result['unit'] = getConfig('unit');
					$byParam['unit'] = false;
				}

				switch($result['unit'] = strtolower($result['unit']))
				{
					case 'px':
					case 'pt':
						break;
					default:
						if($_die)
						{
							drawingError('`' . ($byParam['unit'] ? '?' : '') . 'unit` is invalid; none of [ `px`, `pt` ]');
							return null;
						}
						
						$result['unit'] = getConfig('unit');
						break;
				}

				//
				$byParam['font'] = true;
				
				if(!$result['font'])
				{
					$result['font'] = getConfig('font');
					$byParam['font'] = false;
				}

				if(!($result['font'] = getFont($result['font'])))
				{
					if($_die)
					{
						drawingError('`' . ($byParam['font'] ? '?' : '') . 'font` is not available');
						return null;
					}
					
					if(!($result['font'] = getFont(getConfig('font'))))
					{
						drawingError('Default font is not available (used as fallback)');
						return null;
					}
				}

				//
				$byParam['fg'] = true;
				$byParam['bg'] = true;
				
				if(!$result['fg'])
				{
					$result['fg'] = getConfig('fg');
					$byParam['fg'] = false;
				}

				if(!$result['bg'])
				{
					$result['bg'] = getConfig('bg');
					$byParam['bg'] = false;
				}

				$result['fg'] = \kekse\color($result['fg'], true);
				$result['bg'] = \kekse\color($result['bg'], true);

				if(!$result['fg'])
				{
					if($_die)
					{
						drawingError('`' . ($byParam['fg'] ? '?' : '') . 'fg` is no valid color');
						return null;
					}
					
					if(!($result['fg'] = \kekse\color(getConfig('fg'), true)))
					{
						drawingError('Default FG color is not valid (used as fallback)');
						return null;
					}
				}

				if(!$result['bg'])
				{
					if($_die)
					{
						drawingError('`' . ($byParam['bg'] ? '?' : '') . 'bg` is no valid color');
						return null;
					}
					
					if(!($result['bg'] = \kekse\color(getConfig('bg'), true)))
					{
						drawingError('Default BG color is not valid (used as fallback)');
						return null;
					}
				}

				//
				$byParam['size'] = true;
				
				//
				if(!$result['size'])
				{
					$result['size'] = getConfig('size');
					$byParam['size'] = false;
				}
				
				if(is_string($result['size']))
				{
					$size = \kekse\unit($result['size'], true);
					
					switch($size[1])
					{
						case 'px':
							$result['px'] = $size[0];
							$result['pt'] = \kekse\px2pt($size[0]);
							break;
						case 'pt':
							$result['pt'] = $size[0];
							$result['px'] = \kekse\pt2px($size[0]);
							break;
						case '':
							switch($result['unit'])
							{
								case 'px':
									$result['px'] = $size[0];
									$result['pt'] = \kekse\px2pt($size[0]);
									break;
								case 'pt':
									$result['pt'] = $size[0];
									$result['px'] = \kekse\pt2px($size[0]);
									break;
								default:
									return null;
							}
							break;
						default:
							drawingError('Invalid unit in `' . ($byParam['size'] ? '?' : '') . 'size`');
							return null;
					}
				}
				else if(\kekse\is_number($result['size']))
				{
					switch($result['unit'])
					{
						case 'px':
							$result['px'] = $result['size'];
							$result['pt'] = \kekse\px2pt($result['size']);
							break;
						case 'pt':
							$result['pt'] = $result['size'];
							$result['px'] = \kekse\pt2px($result['size']);
							break;
						default:
							return null;
					}
				}
				else
				{
					drawingError('Invalid `?size`');
					return null;
				}

				//
				unset($result['size']);
				
				//
				$byParam['h'] = true;
				$byParam['v'] = true;
				$byParam['x'] = true;
				$byParam['y'] = true;

				//
				if(!$result['h'])
				{
					$result['h'] = getConfig('h');
					$byParam['h'] = false;
				}
				
				if(is_string($result['h']))
				{
					$h = \kekse\unit($result['h'], true);
					
					switch($h[1])
					{
						case 'px':
							$result['h'] = $h[0];
							break;
						case 'pt':
							$result['h'] = \kekse\pt2px($h[0]);
							break;
						case '':
							switch($result['unit'])
							{
								case 'px':
									$result['h'] = $h[0];
									break;
								case 'pt':
									$result['h'] = \kekse\pt2px($h[0]);
									break;
								default:
									return null;
							}
							break;
						default:
							drawingError('Invalid unit in `' . ($byParam['h'] ? '?' : '') . 'h`');
							return null;
					}
				}
				else if(\kekse\is_number($result['h']))
				{
					switch($result['unit'])
					{
						case 'px':
							break;
						case 'pt':
							$result['h'] = \kekse\pt2px($result['h']);
							break;
						default:
							return null;
					}
				}
				else
				{
					drawingError('Invalid `?h`');
					return null;
				}
				
				if(!$result['v'])
				{
					$result['v'] = getConfig('v');
					$byParam['v'] = false;
				}
				
				if(is_string($result['v']))
				{
					$v = \kekse\unit($result['v'], true);
					
					switch($v[1])
					{
						case 'px':
							$result['v'] = $v[0];
							break;
						case 'pt':
							$result['v'] = \kekse\pt2px($v[0]);
							break;
						case '':
							switch($result['unit'])
							{
								case 'px':
									$result['v'] = $v[0];
									break;
								case 'pt':
									$result['v'] = \kekse\pt2px($v[0]);
									break;
								default:
									return null;
							}
							break;
						default:
							drawingError('Invalid unit in `' . ($byParam['v'] ? '?' : '') . 'v`');
							return null;
					}
				}
				else if(\kekse\is_number($result['v']))
				{
					switch($result['unit'])
					{
						case 'px':
							break;
						case 'pt':
							$result['v'] = \kekse\pt2px($result['v']);
							break;
						default:
							return null;
					}
				}
				else
				{
					drawingError('Invalid `?v`');
					return null;
				}
				
				if(!$result['x'])
				{
					$result['x'] = getConfig('x');
					$byParam['x'] = false;
				}
				
				if(is_string($result['x']))
				{
					$x = \kekse\unit($result['x'], true);
					
					switch($x[1])
					{
						case 'px':
							$result['x'] = $x[0];
							break;
						case 'pt':
							$result['x'] = \kekse\pt2px($x[0]);
							break;
						case '':
							switch($result['unit'])
							{
								case 'px':
									$result['x'] = $x[0];
									break;
								case 'pt':
									$result['x'] = \kekse\pt2px($x[0]);
									break;
								default:
									return null;
							}
							break;
						default:
							drawingError('Invalid unit in `' . ($byParam['x'] ? '?' : '') . 'x`');
							return null;
					}
				}
				else if(\kekse\is_number($result['x']))
				{
					switch($result['unit'])
					{
						case 'px':
							break;
						case 'pt':
							$result['x'] = \kekse\pt2px($result['x']);
							break;
						default:
							return null;
					}
				}
				else
				{
					drawingError('Invalid `?x`');
					return null;
				}
				
				if(!$result['y'])
				{
					$result['y'] = getConfig('y');
					$byParam['y'] = false;
				}
				
				if(is_string($result['y']))
				{
					$y = \kekse\unit($result['y'], true);
					
					switch($y[1])
					{
						case 'px':
							$result['y'] = $y[0];
							break;
						case 'pt':
							$result['y'] = \kekse\pt2px($y[0]);
							break;
						case '':
							switch($result['unit'])
							{
								case 'px':
									$result['y'] = $y[0];
									break;
								case 'pt':
									$result['y'] = \kekse\pt2px($y[0]);
									break;
								default:
									return null;
							}
							break;
						default:
							drawingError('Invalid unit in `' . ($byParam['y'] ? '?' : '') . 'y`');
							return null;
					}
				}
				else if(\kekse\is_number($result['y']))
				{
					switch($result['unit'])
					{
						case 'px':
							break;
						case 'pt':
							$result['y'] = \kekse\pt2px($result['y']);
							break;
						default:
							return null;
					}
				}
				else
				{
					drawingError('Invalid `?y`');
					return null;
				}
				
				//
				if($result['px'] < 3 || $result['px'] > 512)
				{
					drawingError('`' . ($byParam['size'] ? '?' : '') . 'size` exceeds limit (3..512)');
					return null;
				}
				
				if($result['h'] < -512 || $result['h'] > 512)
				{
					drawingError('`' . ($byParam['h'] ? '?' : '') . 'h` exceeds limit (-512..512)');
					return null;
				}
				
				if($result['v'] < -512 || $result['v'] > 512)
				{
					drawingError('`' . ($byParam['v'] ? '?' : '') . 'v` exceeds limit (-512..512)');
					return null;
				}
				
				if($result['x'] < -512 || $result['x'] > 512)
				{
					drawingError('`' . ($byParam['x'] ? '?' : '') . 'x` exceeds limit (-512..512)');
					return null;
				}
				
				if($result['y'] < -512 || $result['y'] > 512)
				{
					drawingError('`' . ($byParam['y'] ? '?' : '') . 'y` exceeds limit (-512..512)');
					return null;
				}

				//
				$byParam['angle'] = true;

				if(!$result['angle'])
				{
					$result['angle'] = getConfig('angle');
					$byParam['angle'] = false;
				}
				
				if(($result['angle'] = checkAngle($result['angle'])) === null)
				{
					if($_die)
					{
						drawingError('`?angle` is invalid');
						return null;
					}
					else if(($result['angle'] = checkAngle(getConfig('angle'))) === null)
					{
						drawingError('Invalid `angle` setting (after `?angle` was invalid or not specified');
						return null;
					}
				}
			
				//
				$result['px'] = (float)round($result['px'], 2);
				$result['pt'] = (float)round($result['pt'], 2);
				$result['h'] = (float)round($result['h'], 2);
				$result['v'] = (float)round($result['v'], 2);
				$result['x'] = (float)round($result['x'], 2);
				$result['y'] = (float)round($result['y'], 2);

				//
				return $result;
			}

			//
			function drawText(&$_text, $_options)
			{
				//
				if(getState('sent'))
				{
					drawingError('Header already sent (unexpected here)');
					return null;
				}

				//
				$measure = function() use(&$_text, &$_options)
				{
					$m = imagettfbbox($_options['pt'], 0, $_options['font'], $_text);

					$minX = (float)round(min($m[0], $m[2], $m[4], $m[6]), 2);
					$maxX = (float)round(max($m[0], $m[2], $m[4], $m[6]), 2);
					$minY = (float)round(min($m[1], $m[3], $m[5], $m[7]), 2);
					$maxY = (float)round(max($m[1], $m[3], $m[5], $m[7]), 2);
					$calculatedHeight = ($maxY - $minY);

					if($_options['min'])
					{
						$height = $calculatedHeight;
					}
					else
					{
						$height = $_options['px'];
					}
					
					$diffHeight = $height - $calculatedHeight;
					$top = $height - $maxY - $diffHeight / 2;//$top = (($diffHeight - $minY) - ($diffHeight) / 2);
					$width = ($maxX - $minX);
					$left = -$minX / 2;
					
					if($height < $_options['px'])
					{
						$height += 4.0;
						$top += 2.0;
					}

					if($height > $_options['px'])
					{
						$height = $_options['px'];
					}

					$_options['width'] = (float)round($width, 2);
					$_options['height'] = (float)round($height, 2);
					$_options['left'] = (float)round($left, 2);
					$_options['top'] = (float)round($top, 2);
					
					$_options['measure'] = array(
						'minX' => $minX,
						'minY' => $minY,
						'maxX' => $maxX,
						'maxY' => $maxY
					);

					return array(
						'width' => &$_options['width'],
						'height' => &$_options['height'],
						'left' => &$_options['left'],
						'top' => &$_options['top'],
						'measure' => &$_options['measure']
					);
				};

				//
				$measure();
				$image = null;

				//
				$_options['width'] += ($_options['h'] * 2);
				$_options['height'] += ($_options['v'] * 2);
				
				if($_options['width'] < 1)
				{
					$_options['width'] = 1;
					$_options['left'] = 0;
				}
				else
				{
					$_options['left'] += $_options['h'];
					$_options['left'] += $_options['x'];
				}
				
				if($_options['height'] < 1)
				{
					$_options['height'] = 1;
					$_options['top'] = 0;
				}
				else
				{
					$_options['top'] += $_options['v'];
					$_options['top'] += $_options['y'];
				}

				//
				$createImage = function() use (&$image, &$_options, &$_text)
				{
					//
					$drawImage = function() use(&$image, &$_options)
					{
						$result = null;
						
						switch($_options['type'])
						{
							case 'png':
								if($result = sendHeader('image/png'))
								{
									imagepng($image);
								}
								break;
							case 'jpg':
								if($result = sendHeader('image/jpeg'))
								{
									imagejpeg($image);
								}
								break;
						}
						
						imagedestroy($image);
						return $result;
					};
					
					$rotateImage = (!$_options['angle'] ? null : function() use(&$image, &$_options, &$color)
					{
						$fixAngle = true;

						if(($_options['angle'] >= 89.99 && $_options['angle'] <= 90.01)
							|| ($_options['angle'] >= 269.99 && $_options['angle'] <= 270.01))
						{
							[ $_options['width'], $_options['height'] ] = [ $_options['height'], $_options['width'] ];
						}
						else if(! ($_options['angle'] >= 179.99 && $_options['angle'] <= 180.01))
						{
							$fixAngle = false;
						}

						$rotated = imagerotate($image, $_options['angle'], $color($image, $_options['bg']));
						imagedestroy($image);
						$result = $rotated;
						
						if(!$result)
						{
							return null;
						}
						else if($fixAngle)
						{
							$result = $initializeImage();
							imagecopy($result, $rotated, 0, 0, 0, 0, $m[0], $m[1]);
							imagedestroy($rotated);

							if(!$result)
							{
								$result = null;
							}
						}

						$_options['width'] = imagesx($result);
						$_options['height'] = imagesy($result);

						return ($image = $result);
					});
					
					$color = function(&$_image, &$_color)
					{
						return imagecolorallocatealpha($_image, $_color[0], $_color[1], $_color[2], $_color[3]);
					};

					$initializeImage = function() use(&$color, &$_options)
					{
						$result = imagecreatetruecolor((int)round($_options['width']), (int)round($_options['height']));
						
						if(!$result)
						{
							return null;
						}

						imagesavealpha($result, true);
						imagealphablending($result, true);
						imageantialias($result, true);
						
						imagefill($result, 0, 0, $color($result, $_options['bg']));
						
						return $result;
					};
					
					$drawText = function() use(&$image, &$color, &$_options, &$_text)
					{
						return imagettftext($image, $_options['pt'], 0, (int)round($_options['left']), (int)round($_options['top']), $color($image, $_options['fg']), $_options['font'], $_text);
					};
					
					//
					if(!($image = $initializeImage()))
					{
						drawingError('Image couldn\'t be initialized');
						return null;
					}
					else
					{
						$drawText();
					}
					
					//
					if($rotateImage !== null)
					{
						if(($image = $rotateImage()) === null)
						{
							drawingError('Unable to rotate image');
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
					drawingError('Couldn\'t send header');
					return null;
				}
				
				return $result;
			}

			return drawText($_text, getDrawingOptions());
		}
	}
	
	function getRadix($_value = null)
	{
		if(is_int($_value) && $_value >= 2 && $_value <= 36)
		{
			return $_value;
		}
		
		$result = \kekse\getParam('radix', true, false);

		if(is_int($result) && $result >= 2 && $result <= 36)
		{
			return $result;
		}
		else if(is_int($result = getConfig('radix')) && $result >= 2 && $result <= 36)
		{
			return $result;
		}
		
		return 10;
	}

	//
	$real = (getState('test') ? rand() : readValue());

	if(getState('done'))
	{
		return $real;
	}

	$value = $real;

	//
	if(!(getState('ro') || getState('test') || getState('done')))
	{
		if(test(true))
		{
			writeValue(++$value);
		}

		if(withClient())
		{
			makeCookie();
		}
	}

	//
	$hide = getConfig('hide');
	
	if(is_string($hide) && !getState('test'))
	{
		$value = $hide;
	}
	else
	{
		if($hide === true && !getState('test'))
		{
			$value = (string)rand();
		}
		else
		{
			$value = (string)$value;
		}
		
		$radix = getRadix();
		
		if($radix !== 10)
		{
			$value = base_convert($value, 10, $radix);
		}
	}

	$l = strlen($value);
	
	if($l > 64)
	{
		logError('$value length exceeds limit (' . (string)$l . ' / 64 chars)', '', '', false);
		$value = 'e';
		$l = 1;
	}

	//
	if(getState('draw') || getState('zero'))
	{
		draw($value, getState('zero'));
	}
	else
	{
		sendHeader();
		header('Content-Length: ' . (string)$l);
		echo $value;
	}

	//
	setState('fin', true);

	//
	if(! (getState('ro') || getState('test')))
	{
		if(withServer())
		{
			writeTimestamp();
		}

		if(withServer(false))
		{
			$clean = getConfig('clean');
			
			if($clean === true)
			{
				cleanFiles();
			}
			else if(is_int($clean))
			{
				$count = readCount();

				if($count >= $clean)
				{
					cleanFiles();
				}
			}
		}
	}

	//
	setState('done', true);
	
	//
	return $real;
}

//
if(! KEKSE_RAW)
{
	counter();
}

//
?>
