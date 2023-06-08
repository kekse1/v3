<?php

//
namespace kekse;

//
define('COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');
define('HELP', 'https://github.com/kekse1/count.php/');
define('VERSION', '2.20.0');

//
define('RAW', false);
define('AUTO', 32);
define('THRESHOLD', 7200);
define('DIR', 'count');
define('HIDE', false);
define('CLIENT', true);
define('SERVER', true);
define('OVERRIDE', false);
define('HASH', 'sha3-256');
define('HASH_IP', false);
define('CONTENT', 'text/plain;charset=UTF-8');
define('CLEAN', true);
define('LIMIT', 32768);
define('LOG', 'count.log');
define('ERROR', '/');
define('NONE', '/');
define('DRAWING', false);
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

function secure($_string)
{
	if(gettype($_string) !== 'string')
	{
		return null;
	}
	
	$len = strlen($_string);
	
	if($len > 255)
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

function get_param($_key, $_numeric = false, $_float = true)
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

	$value = secure($_GET[$_key]);

	if($_numeric === null) switch(strtolower($value)[0])
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
	$len = strlen($value);

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

	return $result;
}

function ends_with($_haystack, $_needle, $_case_sensitive = true)
{
	if(!$_case_sensitive)
	{
		$_haystack = strtolower($_haystack);
		$_needle = strtolower($_needle);
	}
	
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}

	return (substr($_haystack, -strlen($_needle)) === $_needle);
}

function starts_with($_haystack, $_needle, $_case_sensitive = true)
{
	if(!$_case_sensitive)
	{
		$_haystack = strtolower($_haystack);
		$_needle = strtolower($_needle);
	}

	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
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
function counter($_host = null, $_read_only = RAW, $_die = !RAW)
{
	//
	define('COOKIE_PATH', '/');
	define('COOKIE_SAME_SITE', 'Strict');
	define('COOKIE_SECURE', false);//(!empty($_SERVER['HTTPS']));
	define('COOKIE_HTTP_ONLY', true);

	//
	define('CLI', (php_sapi_name() === 'cli'));

	//
	define('TEST', (CLI ? null : (isset($_GET['test']))));
	define('RO', (CLI ? null : (TEST || (isset($_GET['readonly']) || isset($_GET['ro'])))));
	define('ZERO', (CLI ? null : (DRAWING && isset($_GET['zero']) && extension_loaded('gd'))));
	define('DRAW', (CLI ? null : (ZERO || (DRAWING && isset($_GET['draw']) && extension_loaded('gd')))));

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
			die('Path needs to be (non-empty) String' . (CLI ? PHP_EOL : ''));
		}
		else if(empty($_path))
		{
			die('Path may not be empty' . (CLI ? PHP_EOL : ''));
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
				die('The \'getcwd()\' function doesn\'t work');
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
			die('Root directory reached, which is not allowed here' . (CLI ? PHP_EOL : ''));
		}
		else if(!check_path_char($result, true))
		{
			die('Path may not start with one of [ \'.\', \'~\', \'+\', \'-\' ]');
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

	//
	define('PATH', get_path(DIR, true, false));
	define('PATH_LOG', get_path(LOG, false, true));

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
		if(defined('FIN') && FIN)
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

	function log_error($_reason, $_source = '', $_path = '', $_die = !RAW)
	{
		$noLog = false;
		$data = null;
		
		if(!defined('PATH_LOG') || empty(PATH_LOG))
		{
			$noLog = $_die = !RAW;
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
	if(CLI && !RAW)
	{
		//
		if(! (defined('STDIN') && defined('STDOUT')))
		{
			die(' >> Running in CLI mode, but \'STDIN\' and/or \'STDOUT\' are not set!' . PHP_EOL);
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

		function get_list($_index)
		{
			//
			function get_item($_sub, &$_result)
			{
				$type = $_sub[0];
				$host = substr($_sub, 1);
				
				if(!in_array($host, $_result['host']))
				{
					$_result['host'][] = $host;
				}
				
				if(!isset($_result['type'][$host]))
				{
					$_result['type'][$host] = 0;
				}
				
				switch($type)
				{
					case '~':
						if(!in_array($host, $_result['value']))
						{
							$_result['value'][] = $host;
						}
						
						$_result['type'][$host] |= TYPE_VALUE;
						break;
					case '+':
						if(!in_array($host, $_result['dir']))
						{
							$_result['dir'][] = $host;
						}
						
						$_result['type'][$host] |= TYPE_DIR;
						break;
					case '-':
						if(!in_array($host, $_result['file']))
						{
							$_result['file'][] = $host;
						}
						
						$_result['type'][$host] |= TYPE_FILE;
						break;
				}
				
				return $_result;
			}

			//
			$list = null;

			if(gettype($_index) === 'integer')
			{
				$list = get_arguments($_index, false, true, true);
			}

			$result = array();
			$result['host'] = array();
			$result['dir'] = array();
			$result['file'] = array();
			$result['value'] = array();
			$result['type'] = array();
			$result['rest'] = array();
			
			if($list === null)
			{
				$handle = opendir(PATH);
				
				if($handle === false)
				{
					log_error('Can\'t opendir()', 'get_list', PATH, true);
					error('Can\'t opendir()');
				}
				
				$next = null;
				$found = 0;
				
				while($sub = readdir($handle))
				{
					if($sub[0] === '.' || $sub === '..')
					{
						continue;
					}
					else
					{
						switch($sub[0])
						{
							case '~':
							case '+':
							case '-':
								if(strlen($sub) > 1)
								{
									$next = false;
								}
								else
								{
									$next = true;
								}
								break;
							default:
								$next = true;
								break;
						}

						if($next)
						{
							$result['rest'][] = $sub;
							continue;
						}
					}
					
					get_item($sub, $result);
					++$found;
				}
				
				closedir($handle);

				if($found === 0)
				{
					$result = null;
				}
			}
			else
			{
				$len = count($list);
				$found = 0;
				$sub;
				
				for($i = 0; $i < $len; ++$i)
				{
					$sub = join_path(PATH, '{~,+,-}' . $list[$i]);
					$sub = glob($sub, GLOB_BRACE);
					$subLen = count($sub);
					
					if($subLen === 0)
					{
						continue;
					}
					else for($j = 0; $j < $subLen; ++$j)
					{
						get_item(basename($sub[$j]), $result);
						++$found;
					}
				}

				if($found === 0)
				{
					$result = null;
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
			printf('    -s / --set (...TODO)' . PHP_EOL);
			printf('    -v / --values (*)' . PHP_EOL);
			printf('    -n / --sync (*)' . PHP_EOL);
			printf('    -l / --clean (*)' . PHP_EOL);
			printf('    -p / --purge (*)' . PHP_EOL);
			printf('    -c / --check' . PHP_EOL);
			printf('    -h / --hashes' . PHP_EOL);
			printf('    -f / --fonts' . PHP_EOL);
			printf('    -t / --types' . PHP_EOL);
			printf('    -e / --errors' . PHP_EOL);
			printf('    -u / --unlog' . PHP_EOL);
			printf(PHP_EOL);

			exit(0);
		}

		function hashes($_index = -1)
		{
			$list = hash_algos();
			$len = count($list);
			
			for($i = 0; $i < $len; ++$i)
			{
				printf($list[$i] . PHP_EOL);
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
				fprintf(STDERR, ' >> No fonts found.' . PHP_EOL);
				exit(3);
			}
			
			$len = count($result);
			printf(' >> Found %d fonts' . ($defined === -1 ? '' : ' (by %d globs)') . PHP_EOL . PHP_EOL, $len, $defined);
			
			for($i = 0; $i < $len; ++$i)
			{
				printf('    %s' . PHP_EOL, $result[$i]);
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

	die('TODO: same as in fonts()!');
			/*
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

			exit(0);*/
		}
		
		function config($_index = -1)
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
		
		function set($_index = -1)
		{
			//by default set (0)
			//either for a specified host, or to all which match a *glob*..
			//also to init a host, if !AUTO!
			//and use 'prompt()' if file was not already existing!
	die('TODO: set()');
		}

		function sync($_index = -1)
		{
			return values($_index, true, true);
		}
		
		function purge($_index = -1)
		{
			return values($_index, true, false);
		}
		
		function clean($_index = -1)
		{
			return values($_index, false, true);
		}
		
		// @ $removed[]: [ 1 = +host/file, 2 = +host/dir, 4 = +host/, 8 = -host ];
		function values($_index = -1, $_purge = false, $_clean = false)
		{
	die('TODO');//and don't forget 'prompt()'!!
			//
			$list = get_list($_index);

			if($list === null)
			{
				fprintf(STDERR, ' >> No hosts found!' . PHP_EOL);
				exit(1);
			}

			//
			$result = array(
				'values' => array(),
				'removed' => array(),
				'cleaned' => array(),
				'synced' => array(),
				'count' => array(),
				'rest' => array()
			);
			
			//
			$hosts = $list['host'];
			$h = count($hosts);
			
			//
			for($i = 0; $i < $h; ++$i)
			{
				$type = $list['type'][$hosts[$i]];
				$rem = 0;
				
				if($_clean && ($type & TYPE_DIR))
				{
					$handle = opendir(join_path(PATH, '+' . $hosts[$i]));
					$counting = 0;
					
					if($handle !== false)
					{
						while($sub = readdir($handle))
						{
							if($sub[0] === '.' || $sub === '..')
							{
								continue;
							}
							
							$p = join_path(PATH, '+' . $hosts[$i], $sub);
							
							if(!is_file($p))
							{
								continue;
							}
							else
							{
								++$counting;
							}
							
							if(! (is_readable($p) && is_writable($p)))
							{
								continue;
							}
							
							$time = file_get_contents($p);
							
							if($time === false)
							{
								continue;
							}
							else
							{
								$time = timestamp((int)$time);
							}
							
							if($time <= THRESHOLD)
							{
								continue;
							}
							
							if(remove($p, false))
							{
								if(!isset($result['cleaned'][$hosts[$i]]))
								{
									$result['cleaned'][$hosts[$i]] = 1;
								}
								else
								{
									++$result['cleaned'][$hosts[$i]];
								}
								
								--$counting;
								$rem |= 1;
							}
						}
					}
					
					$orig = null;
					
					if($type & TYPE_FILE)
					{
						$orig = file_get_contents(join_path(PATH, '-' . $hosts[$i]));
						
						if($orig !== false)
						{
							$orig = (int)$orig;
						}
					}
					
					if((file_put_contents(join_path(PATH, '-' . $hosts[$i]), (string)$counting)) !== false)
					{
						if($orig !== $counting)
						{
							$result['synced'][$hosts[$i]] = [ $counting, $orig ];
						}
						
						$result['count'][$hosts[$i]] = $counting;
					}
				}
				
				if($type & TYPE_VALUE)
				{
					$value = file_get_contents(join_path(PATH, '~' . $hosts[$i]));
					
					if($value !== false)
					{
						$result['values'][$hosts[$i]] = (int)$value;
					}

					$orig = null;
					$real = null;
					
					if($type & TYPE_FILE)
					{
						$orig = file_get_contents(join_path(PATH, '-' . $hosts[$i]));
						
						if($orig === false)
						{
							$orig = null;
						}
						else
						{
							$orig = (int)$orig;
						}
					}
					
					if($type & TYPE_DIR)
					{
						$handle = opendir(join_path(PATH, '+' . $hosts[$i]));
						
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
								
								$p = join_path(PATH, '+' . $hosts[$i], $sub);
								
								if(is_file($p))
								{
									++$real;
								}
								else if($_purge && remove($p, true))
								{
									$rem |= 2;
								}
							}
							
							if($real === 0 && $_purge)
							{
								if(remove(join_path(PATH, '+' . $hosts[$i]), true))
								{
									$rem |= 4;
								}
								
								if(remove(join_path(PATH, '-' . $hosts[$i]), false))
								{
									$rem |= 8;
								}
							}
						}
					}
					
					if($orig !== null && $real !== null)
					{
						if($orig !== $real)
						{
							$r = file_put_contents(join_path(PATH, '-' . $hosts[$i]), (string)$real);
							
							if($r !== false)
							{
								$result['synced'][$hosts[$i]] = [ $real, $orig ];
								$result['count'][$hosts[$i]] = $real;
							}
						}
						else
						{
							$result['count'][$hosts[$i]] = $real;
						}
					}
					else if($real !== null)
					{
						$r = file_put_contents(join_path(PATH, '-' . $hosts[$i]), (string)$real);
						
						if($r !== false)
						{
							$result['synced'][$hosts[$i]] = [ $real, $orig ];
							$result['count'][$hosts[$i]] = $real;
						}
						else
						{
							$result['count'][$hosts[$i]] = null;
						}
					}
					else if($orig !== null)
					{
						$result['count'][$hosts[$i]] = -$orig;
					}
					else
					{
						$result['count'][$hosts[$i]] = null;
					}
				}
				else
				{
					$result['values'][$hosts[$i]] = null;
					
					if($_purge && (($type & TYPE_DIR) || ($type & TYPE_FILE)))
					{
						if($type & TYPE_DIR)
						{
							if(remove(join_path(PATH, '+' . $hosts[$i]), true))
							{
								$rem |= 4;
							}
						}
						
						if($type & TYPE_FILE)
						{
							if(remove(join_path(PATH, '-' . $hosts[$i]), false))
							{
								$rem |= 8;
							}
						}
					}

					if($_clean)
					{
						$r = file_put_contents(join_path(DIR, '~' . $hosts[$i]), '0');
					}
				}
				
				if($result['count'][$hosts[$i]] === 0 && ($_clean || $_purge))
				{
					$p = join_path(PATH, '+' . $hosts[$i]);
					
					if(remove($p, true))
					{
						$rem |= 4;
					}
					
					$p = join_path(PATH, '-' . $hosts[$i]);
					
					if(remove($p, false))
					{
						$rem |= 8;
					}
				}

				$result['removed'][$hosts[$i]] = $rem;
			}
			
			if($_purge)
			{
				$len = count($list['rest']);

				if($len > 0)
				{
					for($i = 0, $j = 0; $i < $len; ++$i)
					{
						$r = remove(join_path(PATH, $list['rest'][$i]), true);
						
						if($r)
						{
							$result['rest'][$j++] = $list['rest'][$i];
						}
					}
				}
			}

			// @ $removed[]: [ 1 = +host/file, 2 = +host/dir, 4 = +host/, 8 = -host ];

	//
	var_dump($result);
	die('	//TODO: values(' . $_index . ')');
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
			else if(ARGV[$i] === '-s' || ARGV[$i] === '--set')
			{
				set($i);
			}
			else if(ARGV[$i] === '-v' || ARGV[$i] === '--values')
			{
				values($i);
			}
			else if(ARGV[$i] === '-n' || ARGV[$i] === '--sync')
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
				config($i);
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
		printf(' >> Running in CLI mode now (so outside any HTTPD).' . PHP_EOL);
		syntax(ARGC);
		exit();
	}

	//
	if(RAW && CLI && (gettype($_host) !== 'string' || empty($_host)))
	{
		if($_die)
		{
			die('Invalid $_host (needs to be defined in RAW+CLI mode)' . (CLI ? PHP_EOL : ''));
		}

		return null;
	}

	//
	function get_host($_host = null, $_die = !RAW)
	{
		$result = null;

		//
		if(gettype($_host) === 'string' && !empty($_host))
		{
			$result = $_host;
		}
		else if($result = get_param('override', false))
		{
			if(! OVERRIDE)
			{
				$result = null;

				if($_die)
				{
					error('You can\'t define \'?override\' without OVERRIDE enabled');
				}
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
		define('COOKIE', hash(HASH, $host));
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
			define('PATH_IP', join_path(PATH_DIR, secure_path(HASH_IP ? hash(HASH, $_SERVER['REMOTE_ADDR']) : secure_host($_SERVER['REMOTE_ADDR']))));
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
				else if(timestamp((int)file_get_contents($sub)) <= THRESHOLD)
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
			function get_drawing_type($_die = !RAW)
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
			function draw_error($_reason, $_die = !RAW)
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
			
			function get_drawing_options($_die = !RAW)
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
