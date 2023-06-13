<?php

//
namespace kekse\counter;

//
define('COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');
define('HELP', 'https://github.com/kekse1/count.php/');
define('VERSION', '3.0.2');

//
define('DIR', 'count/');
define('LOG', 'count.log');
define('THRESHOLD', 7200);
define('AUTO', 32);
define('HIDE', false);
define('CLIENT', true);
define('SERVER', true);
define('DRAWING', false);
define('OVERRIDE', false);
define('CONTENT', 'text/plain;charset=UTF-8');
define('CLEAN', true);
define('LIMIT', 32768);
define('FONTS', 'fonts/');
define('FONT', 'IntelOneMono');
define('SIZE', 24);
define('SIZE_LIMIT', 512);
define('FG', '0, 0, 0, 1');
define('BG', '255, 255, 255, 0');
define('H', 0);
define('V', 0);
define('H_LIMIT', 256);
define('V_LIMIT', 256);
define('AA', true);
define('TYPE', 'png');
define('PRIVACY', false);
define('HASH', 'sha3-256');
define('ERROR', '/');
define('NONE', '/');
define('RAW', false);

//
define('MAX', 224);

//
function normalize($_string)
{
	if(gettype($_string) !== 'string')
	{
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

function timestamp($_diff = null)
{
	if(gettype($_diff) !== 'integer')
	{
		return time();
	}
	
	return (time() - $_diff);
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
		if(gettype($_args[$i]) !== 'string')
		{
			throw new Error('Invalid argument[' . $i . ']');
		}

		$result .= $_args[$i] . '/';
	}
	
	if(strlen($result) > 0)
	{
		$result = substr($result, 0, -1);
	}
	
	return normalize($result);
}

function limit($_string, $_length = MAX)
{
	return substr($_string, 0, $_length);
}

function secure($_string)
{
	if(gettype($_string) !== 'string')
	{
		return null;
	}
	
	$len = strlen($_string);
	
	if($len > MAX)
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

		while(($len - 1 - $rem) >= 0 && $result[$len - 1 - $rem] === '.')
		{
			++$rem;
		}

		if($rem > 0)
		{
			$result = substr($result, 0, -$rem);
			$rem = 0;
			$len = strlen($result);
		}

		while($rem < ($len - 1) && ($result[$rem] === '.' || $result[$rem] === '~' || $result[$rem] === '+' || $result[$rem] === '-'))
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
//TODO/$_depth argument!!
//
function remove($_path, $_recursive = false, $_depth_current = 0)
{
	if(is_dir($_path))
	{
		if(! $_recursive)
		{
			if(rmdir($_path) === false)
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
		
		while($sub = readdir($handle))
		{
			if($sub === '.' || $sub === '..')
			{
				continue;
			}
			else if(is_dir(join_path($_path, $sub)))
			{
				remove(join_path($_path, $sub), $_recursive, $_depth_current + 1);
			}
			else if(unlink(join_path($_path, $sub)) === false)
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
	
	return true;
}

function get_param($_key, $_numeric = false, $_float = true, $_secure = true)
{
	//
	//TODO/maybe relay from $_GET[] => $_SERVER[] then!?! ^_^
	//
	if(CLI)
	{
		return null;
	}
	else if(gettype($_key) !== 'string')
	{
		return null;
	}
	else if(empty($_key))
	{
		return null;
	}
	else if(!isset($_GET[$_key]))
	{
		return null;
	}

	$value = $_GET[$_key];
	$len = strlen($value);

	if($len > MAX || $len === 0)
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
	else if($result !== null && $_secure)
	{
		$result = secure($result);
	}

	return $result;
}

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

//
if(isset($argc) && isset($argv))
{
	define('ARGC', $argc);
	define('ARGV', $argv);
}

//
define('CLI', (php_sapi_name() === 'cli'));

//
function counter($_host = null, $_read_only = RAW)
{
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

	function error($_reason, $_exit_code = 224)
	{
		if(RAW)
		{
			throw new Exception($_reason);
		}
		else if(defined('FIN') && FIN)
		{
			return null;
		}
		else if(CLI)
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

			exit(224);
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
		
	function log_error($_reason, $_source = '', $_path = '', $_die = true)
	{
		if(CLI)
		{
			if($_die)
			{
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

		if(defined('CAN_LOG') && CAN_LOG)
		{
			$result = file_put_contents(PATH_LOG, $data, FILE_APPEND);

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
	define('COOKIE_PATH', '/');
	define('COOKIE_SAME_SITE', 'Strict');
	define('COOKIE_SECURE', false);//(!empty($_SERVER['HTTPS']));
	define('COOKIE_HTTP_ONLY', true);

	//
	define('TEST', (CLI ? null : (isset($_GET['test']))));
	define('RO', (CLI ? null : (TEST || (isset($_GET['readonly']) || isset($_GET['ro'])))));
	define('ZERO', (CLI ? null : (DRAWING && isset($_GET['zero']) && extension_loaded('gd'))));
	define('DRAW', (CLI ? null : (ZERO || (DRAWING && isset($_GET['draw']) && extension_loaded('gd')))));

	//
	function check_path_char($_path, $_basename = true)
	{
		if($_basename)
		{
			$_path = basename($_path);
		}
		
		switch($_path[0])
		{
			case '.':
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
			error('Path needs to be (non-empty) String');
		}
		else if(empty($_path))
		{
			error('Path may not be empty');
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
			else
			{
				error('The \'getcwd()\' function doesn\'t work');
			}
		}
		else
		{
			$result = __DIR__ . ($_path[0] === '/' ? '' : '/') . $_path;
		}
		
		if(gettype($result) === 'string')
		{
			$result = normalize($result);
		}
		else
		{
			return null;
		}
		
		if($result === '/')
		{
			error('Root directory reached, which is not allowed here');
		}
		else if(!check_path_char($result, true))
		{
			error('Path may not start with one of [ \'.\', \'~\', \'+\', \'-\' ]');
		}

		if($_check)
		{
			if($_file)
			{
				if(!is_dir(dirname($result)))
				{
					error('Directory of path \'' . $_path . '\' doesn\'t exist');
				}
			}
			else if(!is_dir($result))
			{
				error('Directory \'' . $_path . '\' doesn\'t exist');
			}
		}

		return $result;
	}

	//
	define('PATH', get_path(DIR, true, false));
	define('PATH_LOG', get_path(LOG, true, true));
	define('CAN_LOG', true);

	if(DRAWING || CLI)
	{
		define('PATH_FONTS', get_path(FONTS, (CLI ? false : true), false));
	}
	else
	{
		define('PATH_FONTS', null);
	}

	//
	if(! (is_readable(PATH)))// && is_writable(PATH)))
	{
		error('Your \'DIR\' path is not readable');
	}
	else if(DRAWING && !is_readable(PATH_FONTS))
	{
		error('Your \'FONTS\' path is not readable');
	}
	else if(!is_dir(dirname(PATH_LOG)))
	{
		error('Your \'LOG\' directory is not a directory');
	}
	else if(is_file(PATH_LOG) && !is_writable(PATH_LOG))
	{
		error('Your existing \'LOG\' file is not writable');
	}

	//
	if(CLI && !RAW)
	{
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

		function get_arguments($_index, $_secure = false, $_null = true, $_unique = true)
		{
			if(gettype($_index) !== 'integer' || $_index < 0)
			{
				if($_null)
				{
					return null;
				}
				
				return array();
			}
			
			if(ARGC <= $_index)
			{
				if($_null)
				{
					return null;
				}
				
				return array();
			}
			
			$result = array();

			for($i = $_index + 1, $j = 0; $i < ARGC; ++$i)
			{
				if(strlen(ARGV[$i]) === 0)
				{
					continue;
				}
				else if(ARGV[$i][0] === '-')
				{
					break;
				}

				if($_unique && in_array(ARGV[$i], $result))
				{
					continue;
				}

				$result[$j++] = ARGV[$i];
			}

			if($_secure) for($i = 0; $i < count($result); ++$i)
			{
				if(($result[$i] = secure($result[$i])) === null)
				{
					array_splice($result, $i--, 1);
				}
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

		function get_list($_index = null, $_sort = true)
		{
			function item($_host, &$_result)
			{
				if($_host[0] === '.' || $_host === '..' || strlen($_host) === 1)
				{
					return 0;
				}
				
				$type = $_host[0];
				
				switch($type)
				{
					case '~':
						$type = TYPE_VALUE;
						break;
					case '+':
						$type = TYPE_DIR;
						break;
					case '-':
						$type = TYPE_FILE;
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
			
			$list = null;
			
			if(gettype($_index) === 'integer' && $_index > -1)
			{
				$list = get_arguments($_index, false, true, true);
			}

			$result = array();
			$found = 0;

			if($list === null)
			{
				$handle = opendir(PATH);
				
				if($handle === false)
				{
					fprintf(STDERR, ' >> Couldn\'t opendir()' . PHP_EOL);
					exit(1);
				}
				
				while($host = readdir($handle))
				{
					if(item($host, $result) > 0)
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
					$sub = join_path(PATH, '{~,+,-}' . strtolower($list[$i]));
					$sub = glob($sub, GLOB_BRACE);
					$subLen = count($sub);

					if($subLen === 0)
					{
						continue;
					}
					else for($j = 0; $j < $subLen; ++$j)
					{
						if(item(basename($sub[$j]), $result) > 0)
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
			//printf('    -t / --set (...TODO)' . PHP_EOL);
			printf('    -v / --values (*)' . PHP_EOL);
			printf('    -s / --sync (*)' . PHP_EOL);
			printf('    -l / --clean (*)' . PHP_EOL);
			printf('    -p / --purge (*)' . PHP_EOL);
			printf('    -c / --check' . PHP_EOL);
			printf('    -h / --hashes' . PHP_EOL);
			printf('    -f / --fonts (*)' . PHP_EOL);
			printf('    -t / --types' . PHP_EOL);
			printf('    -e / --errors' . PHP_EOL);
			printf('    -u / --unlog' . PHP_EOL);
			printf(PHP_EOL);

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

			$fonts = get_arguments($_index, false, true, true);
			$result = array();
			$defined;

			if($fonts === null)
			{
				$defined = -1;
				$result = glob(join_path(PATH_FONTS, '*.ttf'), GLOB_BRACE);
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

					$sub = glob(join_path(PATH_FONTS, basename($fonts[$i], '.ttf') . '.ttf'));
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
				fprintf(STDERR, ' >> No fonts found' . ($defined === -1 ? '' : ' (with your ' . $defined . ' globs)') . PHP_EOL);
				exit(3);
			}
			
			$len = count($result);
			printf(' >> Found %d fonts' . ($defined === -1 ? '' : ' (by %d globs)') . PHP_EOL . PHP_EOL, $len, $defined);
			
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
			printf(' >> We\'re testing your configuration right now.' . PHP_EOL);
			fprintf(STDERR, ' >> Beware: the DRAWING options are not finished in this --check/-c function! JFYI..' . PHP_EOL);
			printf(PHP_EOL);

			//
			$ok = 0;
			$errors = 0;
			$warnings = 0;

			//
			define('START', '%12s: %-7s');
			
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

			if(gettype(OVERRIDE) === 'boolean')
			{
				printf(START.'Boolean type, great (could also be a non-empty String)' . PHP_EOL, 'OVERRIDE', 'OK');
				++$ok;
			}
			else if(gettype(OVERRIDE) === 'string' && !empty(OVERRIDE))
			{
				printf(START.'A non-empty String (and could also be a Boolean)' . PHP_EOL, 'OVERRIDE', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'Not a boolean type' . PHP_EOL, 'OVERRIDE', 'BAD');
				++$errors;
			}

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

			if(gettype(PRIVACY) === 'boolean')
			{
				printf(START.'Boolean type' . PHP_EOL, 'PRIVACY', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'PRIVACY', 'ERROR');
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

			if(gettype(RAW) === 'boolean')
			{
				printf(START.'A boolean value' . PHP_EOL, 'RAW', 'OK');
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'Not a boolean value' . PHP_EOL, 'RAW', 'BAD');
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
		
		function set($_index = null)
		{
			//by default set (0)
			//either for a specified host, or to all which match a *glob*..
			//also to init a host, if !AUTO!
			//and use 'prompt()' if file was not already existing!
	die('TODO: set()');
		}

		//
		function purge($_index = null)
		{
			//
			$list = get_list($_index);

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
				
				if($value & TYPE_DIR)
				{
					$dirs[$d++] = $key;
					++$c;
				}
				
				if($value & TYPE_FILE)
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

			if(!prompt('Do you really want to delete them [yes/no]? '))
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
				if(remove(join_path(PATH, '+' . $dirs[$i]), true))
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
				if(remove(join_path(PATH, '-' . $files[$i]), false))
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
				fprintf(STDERR, ' >> BUT %d files could *not* be removed. :-/' . PHP_EOL, $errors);
				exit(3);
			}
			
			fprintf(STDERR, ' >> NONE of the selected %d files deleted! :-(' . PHP_EOL, $total);
			exit(4);
		}
		
		function clean($_index = null)
		{
			//
			$list = get_list($_index);

			if($list === null)
			{
				fprintf(STDERR, ' >> No hosts found.' . PHP_EOL);
				exit(1);
			}

			$orig = count($list);

			foreach($list as $host => $type)
			{
				if(! ($type & TYPE_DIR))
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

			if(!prompt('Do you want to continue [yes/no]? '))
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

				$handle = opendir(join_path(PATH, '+' . $host));

				if($handle === false)
				{
					$errors[$host] = 1;
					continue;
				}
				else while($sub = readdir($handle))
				{
					if($sub[0] === '.' || $sub === '..')
					{
						continue;
					}

					$p = join_path(PATH, '+' . $host, $sub);

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
					else if(timestamp($val = (int)$val) >= THRESHOLD)
					{
						if(remove($p, false))
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
					remove(join_path(PATH, '+' . $host), true);
					remove(join_path(PATH, '-' . $host), false);
				}
				else if(! file_put_contents(join_path(PATH, '-' . $host), (string)$count))
				{
					++$errors[$host];
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

			if($e === 0)
			{
				printf(' >> Great, not a single error! :-)' . PHP_EOL);
			}
			else
			{
				$total = 0;

				foreach($errors as $h => $c)
				{
					$total += $c;
				}

				fprintf(STDERR, ' >> Hm, %d host' . ($e === 0 ? '' : 's') . ' caused %d errors..' . PHP_EOL, $e, $total);
			}

			if($d === 0)
			{
				printf(' >> ' . ($e === 0 ? '' : 'But ') . ' no deletions, so nothing changed at all.' . PHP_EOL);
				exit(0);
			}
			else
			{
				printf(' >> Amount of deleted files per host:' . PHP_EOL . PHP_EOL);
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

			$s = ' %' . $maxLen . 's    %d' . PHP_EOL;

			foreach($delete as $host => $del)
			{
				printf($s, $host, $del);
			}

			//
			printf(PHP_EOL . ' >> Deleted %d files totally.' . PHP_EOL, $sum);
			exit(0);
		}

		//
		function sync($_index = null)
		{
			return values($_index, true);
		}

		function values($_index = null, $_sync = false)
		{
			//
			$list = get_list($_index);

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

				if($value & TYPE_VALUE)			
				{
					$val = null;
					$real = null;
					$cache = null;
					
					if($value & TYPE_FILE)
					{
						$cache = file_get_contents(join_path(PATH, '-' . $key));
						
						if($cache === false)
						{
							$cache = null;
						}
						else
						{
							$cache = (int)$cache;
						}
					}
					
					if($value & TYPE_DIR)
					{
						$handle = opendir(join_path(PATH, '+' . $key));
						
						if($handle === false)
						{
							$real = null;
						}
						else
						{
							$real = 0;
							
							while($sub = readdir($handle))
							{
								if($sub[0] === '.' || $sub === '..')
								{
									continue;
								}
								else if(join_path(PATH, '+' . $key, $sub))
								{
									++$real;
								}
							}
							
							closedir($handle);
						}
					}

					$val = file_get_contents(join_path(PATH, '~' . $key));
					
					if($val === false)
					{
						$result[$key] = [ null, $cache, $real ];
					}
					else
					{
						$result[$key] = [ (int)$val, $cache, $real ];
					}
				}
				else
				{
					$result[$key] = null;
				}
			}

			printf(PHP_EOL);
			$a = ' %' . $maxLen . "s    ";
			$b = "%-10s %6s / %-6s" . PHP_EOL;
			$sync = array();
			
			foreach($result as $h => $v)
			{
				if($v === null)
				{
					fprintf(STDERR, $a . '%s' . PHP_EOL, $h, '/');
					$sync[$h] = 0;
				}
				else
				{
					printf($a . $b, $h, ($v[0] === null ? '-' : (string)$v[0]), ($v[1] === null ? '-' : (string)$v[1]), ($v[2] === null ? '-' : (string)$v[2]));
					
					if($v[2] === 0 && (($list[$h] & TYPE_DIR) || ($list[$h] & TYPE_FILE)))
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
			}
			
			printf(PHP_EOL);
			$s = count($sync);

			if(!$_sync || $s === 0)
			{
				exit(0);
			}

			if(!prompt('Do you want to synchronize ' . count($sync) . ' hosts now [yes/no]? '))
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
					if(is_dir($p = join_path(PATH, '+' . $host)))
					{
						if(remove($p, true))
						{
							++$del;
						}
						else
						{
							++$err;
						}
					}

					if(is_file($p = join_path(PATH, '-' . $host)))
					{
						if(remove($p, false))
						{
							++$del;
						}
						else
						{
							++$err;
						}
					}
				}
				else if(file_put_contents(join_path(PATH, '-' . $host), (string)$val))
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

			if(! file_exists(PATH_LOG))
			{
				fprintf(STDERR, ' >> There is no \'%s\' which could be deleted. .. that\'s good for you. :)~' . PHP_EOL, basename(PATH_LOG));
				exit(1);
			}
			else if(!is_file(PATH_LOG))
			{
				fprintf(STDERR, ' >> The \'%s\' is not a regular file. Please replace/remove it asap!' . PHP_EOL, PATH_LOG);
			}

			if(!prompt('Do you really want to delete the file \'' . basename(PATH_LOG) . '\' [yes/no]? '))
			{
				fprintf(STDERR, ' >> Log file deletion aborted (by request).' . PHP_EOL);
				exit(2);
			}
			else if(remove(PATH_LOG, false) === false)
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

		function errors($_index = null)
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
		function help($_index = null)
		{
			printf(HELP . PHP_EOL);
			exit(0);
		}

		//
		for($i = 1; $i < ARGC; ++$i)
		{
			if(strlen(ARGV[$i]) < 2 || ARGV[$i][0] !== '-')
			{
				continue;
			}
			else if(ARGV[$i] === '-?' || ARGV[$i] === '--help')
			{
				help($i);
			}
			else if(ARGV[$i] === '-V' || ARGV[$i] === '--version')
			{
				info($i, true, false);
			}
			else if(ARGV[$i] === '-C' || ARGV[$i] === '--copyright')
			{
				info($i, false, true);
			}
			/*else if(ARGV[$i] === '-t' || ARGV[$i] === '--set')
			{
				set($i);
			}*/
			else if(ARGV[$i] === '-v' || ARGV[$i] === '--values')
			{
				values($i);
			}
			else if(ARGV[$i] === '-s' || ARGV[$i] === '--sync')
			{
				sync($i);
			}
			else if(ARGV[$i] === '-l' || ARGV[$i] === '--clean')
			{
				clean($i);
			}
			else if(ARGV[$i] === '-p' || ARGV[$i] === '--purge')
			{
				purge($i);
			}
			else if(ARGV[$i] === '-c' || ARGV[$i] === '--check')
			{
				check($i);
			}
			else if(ARGV[$i] === '-h' || ARGV[$i] === '--hashes')
			{
				hashes($i);
			}
			else if(ARGV[$i] === '-f' || ARGV[$i] === '--fonts')
			{
				fonts($i);
			}
			else if(ARGV[$i] === '-t' || ARGV[$i] === '--types')
			{
				types($i);
			}
			else if(ARGV[$i] === '-e' || ARGV[$i] === '--errors')
			{
				errors($i);
			}
			else if(ARGV[$i] === '-u' || ARGV[$i] === '--unlog')
			{
				unlog($i);
			}
		}
		
		//
		printf(' >> Running in CLI mode now (outside any HTTPD).' . PHP_EOL);
		syntax(ARGC);
		exit();
	}
	else if(CLI && (gettype($_host) !== 'string' || empty($_host)) && (gettype(OVERRIDE) !== 'string' || empty(OVERRIDE)))
	{
		error('Invalid $_host (needs to be defined in CLI mode)');
	}

	//
	function get_host($_host = null, $_die = !RAW)
	{
		$result = null;

		//
		if(gettype($_host) === 'string' && !empty($_host))
		{
			$result = $_host;
			define('OVERRIDDEN', true);
		}
		else if(gettype(OVERRIDE) === 'string' && !empty(OVERRIDE))
		{
			$result = OVERRIDE;
			define('OVERRIDDEN', true);
		}
		else if(gettype($result = get_param('override', false)) === 'string' && !empty($result))
		{
			if(! OVERRIDE)
			{
				$result = null;

				if($_die)
				{
					error('You can\'t define \'?override\' without OVERRIDE enabled');
				}
			}
			else
			{
				define('OVERRIDDEN', true);
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
		if(!defined('OVERRIDDEN'))
		{
			define('OVERRIDDEN', false);
		}

		//
		if($result !== null)
		{
			$result = secure_host(remove_port($result, $_die));

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
		$host = get_host($_host);
		define('HOST', $host);
		define('COOKIE', limit(hash(HASH, $host)));
		unset($host);

		//
		define('PATH_FILE', join_path(PATH, '~' . secure_path(HOST)));
		define('PATH_DIR', join_path(PATH, '+' . secure_path(HOST)));
		define('PATH_COUNT', join_path(PATH, '-' . secure_path(HOST)));
		
		if(empty($_SERVER['REMOTE_ADDR']))
		{
			define('PATH_IP', null);
		}
		else
		{
			define('PATH_IP', join_path(PATH_DIR, secure_path(PRIVACY ? limit(hash(HASH, $_SERVER['REMOTE_ADDR'])) : secure_host($_SERVER['REMOTE_ADDR']))));
		}

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
			if(PATH_IP === null)
			{
				return true;
			}
			else if(file_exists(PATH_IP))
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
				log_error('Can\'t opendir()', 'clean_files', PATH_DIR, false);
				return -1;
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
				else if(timestamp((int)file_get_contents($sub)) < THRESHOLD)
				{
					++$result;
				}
				else if(! remove($sub, false))
				{
					log_error('Unable to remove() outdated file', 'clean_files', $sub, false);
					++$result;
				}
			}
			
			closedir($handle);
			return write_count($result, false);
		}

		function init_count()
		{
			$result = 0;

			if(is_dir(PATH_DIR))
			{
				$handle = opendir(PATH_DIR);

				if($handle === false)
				{
					log_error('Can\'t opendir()', 'init_count', PATH_DIR, false);
					return null;
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
				log_error('Couldn\'t initialize count', 'init_count', PATH_COUNT, false);
				return null;
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
				log_error('Count file is not a file, or it\'s not writable', 'read_count', PATH_COUNT, false);
				return 0;
			}

			$result = file_get_contents(PATH_COUNT);

			if($result === false)
			{
				log_error('Couldn\'t read count value', 'read_count', PATH_COUNT, false);
				return null;
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
					log_error('Count file is not a regular file', 'write_count', PATH_COUNT, false);
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

			$written = file_put_contents(PATH_COUNT, (string)$_value);

			if($written === false)
			{
				log_error('Unable to write count', 'write_count', PATH_COUNT, false);
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
			if(PATH_IP === null)
			{
				return 0;
			}
			else if(file_exists(PATH_IP))
			{
				if(!is_file(PATH_IP))
				{
					log_error('Is not a file', 'read_timestamp', PATH_IP, false);
					return 0;
				}
				else if(!is_readable(PATH_IP))
				{
					log_error('File not readable', 'read_timestamp', PATH_IP, false);
					return 0;
				}

				$result = file_get_contents(PATH_IP);

				if($result === false)
				{
					log_error('Unable to read timestamp', 'read_timestamp');
					return 0;
				}

				return (int)$result;
			}

			return 0;
		}

		function write_timestamp($_clean = (CLEAN !== null))
		{
			if(PATH_IP === null)
			{
				return 0;
			}
			else if(!file_exists(PATH_DIR))
			{
				if(!mkdir(PATH_DIR))
				{
					log_error('Can\'t mkdir()', 'write_timestamp', PATH_DIR, false);
					return 0;
				}
			}
			else if(!is_dir(PATH_DIR))
			{
				log_error('Path isn\'t a directory', 'write_timestamp', PATH_DIR, false);
				return 0;
			}
			
			$existed = file_exists(PATH_IP);

			if($existed)
			{
				if(!is_file(PATH_IP))
				{
					log_error('It\'s no regular file', 'write_timestamp', PATH_IP, false);
					return 0;
				}
				else if(!is_writable(PATH_IP))
				{
					log_error('Not a writable file', 'write_timestamp', PATH_IP, false);
					return 0;
				}
			}
			else if(read_count() > LIMIT)
			{
				if($_clean)
				{
					if(clean_files() > LIMIT)
					{
						log_error('LIMIT exceeded, even after clean_files()', 'write_timestamp', PATH_IP, false);
						return 0;
					}
				}
				else
				{
					log_error('LIMIT exceeded (and no clean_files() called)', 'write_timestamp', PATH_IP, false);
					return 0;
				}
			}

			$result = file_put_contents(PATH_IP, (string)timestamp());

			if($result === false)
			{
				log_error('Unable to write timestamp', 'write_timestamp', PATH_IP, false);
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

		function write_value($_value)
		{
			if(gettype($_value) !== 'integer' || $_value < 0)
			{
				log_error('Value was no integer, or it was below zero', 'write_value', '', true);
				error('Value was no integer, or it was below zero');
			}

			if(file_exists(PATH_FILE))
			{
				if(!is_file(PATH_FILE))
				{
					log_error('Not a regular file', 'write_value', PATH_FILE, true);
					error('Not a regular file');
				}
				else if(!is_writable(PATH_FILE))
				{
					log_error('File is not writable', 'write_value', PATH_FILE, true);
					error('File is not writable');
				}
			}

			$result = file_put_contents(PATH_FILE, (string)$_value);

			if($result === false)
			{
				log_error('Unable to write value', 'write_value', PATH_FILE, true);
				error('Unable to write value');
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
	if(DRAWING && !RAW)
	{
		function draw($_text, $_zero = ZERO)
		{
			//
			function draw_error($_reason)
			{
				return error($_reason);
			}

			function get_drawing_type()
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
			
			function get_drawing_options()
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
					draw_error('\'?size\' exceeds limit (0 / ' . SIZE_LIMIT . ')');
					return null;
				}

				if(! is_numeric($result['h']))
				{
					$result['h'] = H;
				}
				else if($result['h'] > H_LIMIT || $result['h'] < -H_LIMIT)
				{
					draw_error('\'?h\' exceeds limit (' . H_LIMIT . ')');
					return null;
				}

				if(! is_numeric($result['v']))
				{
					$result['v'] = V;
				}
				else if($result['v'] > V_LIMIT || $result['v'] < -V_LIMIT)
				{
					draw_error('\'?v\' exceeds limit (' . V_LIMIT . ')');
					return null;
				}

				if($result['font'] === null)
				{
					$result['font'] = FONT;
				}

				if(($result['font'] = get_font($result['font'])) === null)
				{
					draw_error('\'?font\' is not available');
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
					draw_error('\'?fg\' is no valid rgb/rgba color');
					return null;
				}

				$result['bg'] = get_color($result['bg'], true);

				if($result['bg'] === null)
				{
					draw_error('\'?bg\' is no valid rgb/rgba color');
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
				if($width < 1)
				{
					$width = 1;
				}

				if($height < 1)
				{
					$height = 1;
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
	}
	
	//
	$real = read_value();

	if((defined('DONE') && DONE) || ($_read_only && RAW))
	{
		return $real;
	}

	$value = (TEST ? rand() : $real);
	
	//
	if(! (RO || TEST || $_read_only) && !(defined('DONE') && DONE))
	{
		if(test())
		{
			write_value(++$value);
		}

		if(CLIENT && !OVERRIDDEN && !CLI)
		{
			make_cookie();
		}
	}

	//
	if(!RAW)
	{
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
	}

	//
	if(SERVER && !(RO || TEST || $_read_only) && !(defined('DONE') && DONE))
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
	define('DONE', true);
	
	//
	return $real;
}

//
if(!RAW)
{
	counter();
}

?>
