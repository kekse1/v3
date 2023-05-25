<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 * v2.6.2
 */

//
define('VERSION', [ 2, 6, 2 ]);
define('COPYRIGHT', 'Sebastian Kucharczyk <kuchen@kekse.biz>');

//
define('AUTO', 32);
define('THRESHOLD', 10800);//3 hours (60 * 60 * 3 seconds)
define('PATH', 'count');
define('CLIENT', true);
define('SERVER', true);
define('HASH', 'sha3-256');
define('HASH_IP', false);
define('CONTENT', 'text/plain;charset=UTF-8');
define('CLEAN', false);
define('LIMIT', 32768);
define('LOG', 'ERROR.log');
define('ERROR', '/');
define('NONE', '/');
define('DRAW', false);
define('DRAW_PARAMS', true);
define('SIZE', 24);
define('SIZE_LIMIT', 96);
define('FONT', 'Source Code Pro');
define('FONT_LIMIT', [ 'Candara', 'Open Sans', 'Source Code Pro' ]);

//
define('COOKIE_PATH', '/');
define('COOKIE_SAME_SITE', 'Strict');
define('COOKIE_SECURE', false);//(!empty($_SERVER['HTTPS']));
define('COOKIE_HTTP_ONLY', true);

//
function secureHost($_host, $_die = true)
{
	$length = min(strlen($_host), 255);
	$result = '';
	$byte = null;
	$put = '';

	for($i = 0; $i < $length; ++$i)
	{
		if($_host[$i] === '.')
		{
			if(strlen($result) === 0)
			{
				$put = '';
			}
			else if($result[strlen($result) - 1] === '.')
			{
				$put = '';
			}
			else
			{
				$put = '.';
			}
		}
		else if($_host[$i] === ':')
		{
			$put = ':';
		}
		else if($_host[$i] === '-')
		{
			$put = '-';
		}
		else if(($byte = ord($_host[$i])) >= 48 && $byte <= 57)
		{
			$put = chr($byte);
		}
		else if($byte >= 65 && $byte <= 90)
		{
			$put = chr($byte + 32);
		}
		else if($byte >= 97 && $byte <= 122)
		{
			$put = chr($byte);
		}
		else
		{
			$put = '';
		}

		$result .= $put;
	}

	if(empty($result))
	{
		if($_die)
		{
			die('Secured host got no length');
		}

		$result = null;
	}

	return $result;
}

//
function countFiles($_path = PATH, $_dir = false, $_exclude = true, $_list = false)
{
	$list = scandir($_path);

	if($list === false)
	{
		errorLog('Unable to scandir()', 'countFiles', $_path);
		die('Unable to scandir()');
	}

	$len = count($list);
	$result = ($_list ? array() : 0);

	for($i = 0, $j = 0; $i < $len; ++$i)
	{
		if($list[$i] === '.' || $list[$i] === '..')
		{
			continue;
		}
		else if($list[$i][0] === '.')
		{
			continue;
		}
		else if($_exclude)
		{
			if($list[$i][0] === '-')
			{
				continue;
			}
			else if($list[$i][0] === '+')
			{
				continue;
			}
		}

		if($_dir === false)
		{
			if(!is_file($_path . '/' . $list[$i]))
			{
				continue;
			}
		}
		else if($_dir === true)
		{
			if(!is_dir($_path . '/' . $list[$i]))
			{
				continue;
			}
		}

		if($_list)
		{
			$result[++$j] = $list[$i];
		}
		else
		{
			++$result;
		}
	}

	return $result;
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

	//
	function stats($_index = null)
	{
		//
		function getHosts($_path = PATH)
		{
			return countFiles($_path, false, true, true);
		}
		
		function getHostValue($_path = PATH, $_host)
		{
			if(is_file($_path . '/' . $_host))
			{
				return (int)file_get_contents($_path . '/' . $_host);
			}
			
			return null;
		}
		
		function getRealHostCount($_path = PATH, $_host)
		{
			return countFiles($_path . '/+' . $_host, false, true, false);
		}
		
		function getHostCount($_path = PATH, $_host)
		{
			if(is_file($_path . '/-' . $_host))
			{
				return (int)file_get_contents($_path . '/-' . $_host);
			}
			
			return null;
		}
		
		function writeHostCount($_value, $_path = PATH, $_host)
		{
			return file_put_contents($_path . '/-' . $_host, (string)$_value);
		}
		
		function compareCount($_path = PATH, $_host)
		{
			$currentCount = getHostCount($_path, $_host);
			
			if($currentCount === null)
			{
				return null;
			}
			
			$realCount = getRealHostCount($_path, $_host);
			
			if($realCount === $currentCount)
			{
				return true;
			}
			else
			{
				writeHostCount($realCount, $_path, $_host);
			}
			
			return false;
		}
		
		//
		var_dump(getHosts());

		//
		die(PHP_EOL . PHP_EOL . 'TODO: stats()' . PHP_EOL);
		
		//
		exit();
	}
	
	function clean($_index = -1)
	{
		//
		die('TODO: clean()' . PHP_EOL);
		
		//
		exit();
	}

	//
	function info($_index = -1, $_version = true, $_copyright = true)
	{
		if($_version)
		{
			printf('v' . join('.', VERSION) . PHP_EOL);
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
		printf('    -? / --help      (TODO)' . PHP_EOL);
		printf('    -v / --version' . PHP_EOL);
		printf('    -C / --copyright' . PHP_EOL);
		printf('    -h / --hashes' . PHP_EOL);
		printf('    -c / --config' . PHP_EOL);
		printf('    -s / --stats     (TODO)' . PHP_EOL);
		printf('    -l / --clean     (TODO)' . PHP_EOL);
		printf('    -p / --purge     (TODO)' . PHP_EOL);
		printf('    -i / --init      (TODO)' . PHP_EOL);
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
			printf(START.'Non-empty String (without further testing)' . PHP_EOL, 'PATH', 'OK', 'string');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'PATH', 'BAD');
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
			printf(START.'Boolean type' . PHP_EOL, 'DRAW', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'DRAW', 'BAD');
			++$errors;
		}

		if(gettype(DRAW_PARAMS) === 'boolean')
		{
			printf(START.'Boolean type' . PHP_EOL, 'DRAW_PARAMS', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Boolean type' . PHP_EOL, 'DRAW_PARAMS', 'BAD');
			++$errors;
		}

		if(gettype(SIZE) === 'integer' && SIZE > 0)
		{
			$limit = SIZE_LIMIT;

			if(gettype($limit) !== 'integer')
			{
				$limit = null;
			}

			if($limit === null)
			{
				printf(START.'Integer above 0 (WARNING: can\'t test against invalid SIZE_LIMIT)' . PHP_EOL, 'SIZE', 'WARN');
				++$warnings;
			}
			else if(SIZE > $limit)
			{
				fprintf(STDERR, START.'Integer exceeds SIZE_LIMIT (%d)' . PHP_EOL, 'SIZE', 'BAD', $limit);
				++$errors;
			}
			else
			{
				printf(START.'Integer above 0 and below or equal to SIZE_LIMIT (%d)' . PHP_EOL, 'SIZE', 'OK', $limit);
				++$ok;
			}
		}
		else
		{
			fprintf(STDERR, START.'No Integer (above 0 and below or equal to SIZE_LIMIT)' . PHP_EOL, 'SIZE', 'BAD');
			++$errors;
		}

		if(gettype(SIZE_LIMIT) === 'integer' && SIZE_LIMIT > 0)
		{
			printf(START.'Integer above 0' . PHP_EOL, 'SIZE_LIMIT', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No Integer above 0' . PHP_EOL, 'SIZE_LIMIT', 'BAD');
			++$errors;
		}

		if(gettype(FONT) === 'string' && !empty(FONT))
		{
			printf(START.'Non-empty String (without further tests)' . PHP_EOL, 'FONT', 'OK');
			++$ok;//w/o further tests!!!
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'FONT', 'BAD');
			++$errors;
		}

		if(gettype(FONT_LIMIT) === 'array' && !empty(FONT_LIMIT))
		{
			$valid = true;
			$len = count(FONT_LIMIT);

			for($i = 0; $i < $len; ++$i)
			{
				if(gettype(FONT_LIMIT[$i]) !== 'string' || empty(FONT_LIMIT[$i]))
				{
					$valid = false;
					break;
				}
			}

			if($valid)
			{
				printf(START.'Non-empty Array with %d non-empty Strings in it' . PHP_EOL, 'FONT_LIMIT', 'OK', $len);
				++$ok;
			}
			else
			{
				fprintf(STDERR, START.'No non-empty Array with only non-empty Strings in it' . PHP_EOL, 'FONT_LIMIT', 'BAD');
				++$errors;
			}
		}
		else
		{
			fprintf(STDERR, START.'Not even a non-empty Array..' . PHP_EOL, 'FONT_LIMIT', 'BAD');
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
	
	//TODO/reset whole 'PATH' count-directory (show also if already existed, etc.)
	function init($_index)
	{
		//
		die('TODO: init()' . PHP_EOL);
		
		//
		exit();
	}
	
	//TODO/delete any '+'-directory with any '-'-count-file!
	function purge($_index)
	{
		//
		die('TODO: purge()' . PHP_EOL);
		
		//
		exit();
	}

	//
	if(isset($argv)) for($i = 1; $i < $argc; ++$i)
	{
		if($argv[$i] === '-?' || $argv[$i] === '--help')
		{
			help($i);
		}
		else if($argv[$i] === '-v' || $argv[$i] === '--version')
		{
			info($i, true, false);
		}
		else if($argv[$i] === '-C' || $argv[$i] === '--copyright')
		{
			info($i, false, true);
		}
		else if($argv[$i] === '-s' || $argv[$i] === '--stats')
		{
			stats($i);
		}
		else if($argv[$i] === '-c' || $argv[$i] === '--config')
		{
			config($i);
		}
		else if($argv[$i] === '-h' || $argv[$i] === '--hashes')
		{
			hashes($i);
		}
		else if($argv[$i] === '-l' || $argv[$i] === '--clean')
		{
			clean($i);
		}
		else if($argv[$i] === '-p' || $argv[$i] === '--purge')
		{
			purge($i);
		}
		else if($argv[$i] === '-i' || $argv[$i] === '--init')
		{
			init($i);
		}
	}
	
	//
	printf(' >> Running in CLI mode now (so outside any HTTPD).' . PHP_EOL);
	syntax($argc);
	exit();
}

//
function getHost($_die = true)
{
	$result = null;

	//
	if(! empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'][0] !== ':')
	{
		$result = $_SERVER['HTTP_HOST'];
	}
	else if(! empty($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'][0] !== ':')
	{
		$result = $_SERVER['SERVER_NAME'];
	}
	else if($_die)
	{
		die('No server host/name applicable');
	}
	
	//
	return $result;
}

function endsWith($_haystack, $_needle)
{
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}

	return (substr($_haystack, -strlen($_needle)) === $_needle);
}

function removePortIfAny($_host, $_die = false)
{
	$result = null;
	$port = null;
	
	if(empty($_SERVER['SERVER_PORT']))
	{
		if($_die)
		{
			die('No $_SERVER[\'SERVER_PORT\'] defined');
		}
		
		$result = $_host;
	}
	else
	{
		$port = (string)$_SERVER['SERVER_PORT'];
	}
	
	if($port !== null && endsWith($_host, (':' . $port)))
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
$host = getHost(true);
$host = removePortIfAny($host);
$host = secureHost($host, true);
define('HOST', $host);
define('COOKIE', hash(HASH, $host));
unset($host);

//
define('PATH_FILE', (PATH . '/' . HOST));
define('PATH_DIR', (PATH . '/+' . HOST));
define('PATH_COUNT', (PATH . '/-' . HOST));
define('PATH_IP', (PATH_DIR . '/' . (HASH_IP ? hash(HASH, $_SERVER['REMOTE_ADDR']) : secureHost($_SERVER['REMOTE_ADDR'], true))));
define('PATH_LOG', (PATH . '/' . LOG));

//
header('Content-Type: ' . CONTENT);

//
if(AUTO === null)
{
	die(NONE);
}

//
function errorLog($_reason, $_source = '', $_path = '', $_die = true)
{
	$data = '[' . (string)time() . ']';

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
	$result = file_put_contents(PATH_LOG, $data, FILE_APPEND);

	if($result === false)
	{
		if(gettype(ERROR) === 'string')
		{
			die(ERROR);
		}

		die('Logging error: ' . substr($data, 0, -1));
	}
	else if($_die && gettype(ERROR) === 'string')
	{
		die(ERROR);
	}

	return $result;
}

//
function checkPath($_path = PATH, $_file = PATH_FILE, $_die = true)
{
	//
	if(!file_exists($_path))
	{
		if(! mkdir($_path, 1777, false))
		{
			if($_die)
			{
				errorLog('Directory doesn\'t exist and couldn\'t be created', 'checkPath', $_path);
				die('Directory doesn\'t exist and couldn\'t be created');
			}
			
			return false;
		}
	}
	else if(!is_dir($_path))
	{
		if($_die)
		{
			errorLog('Path doesn\'t point to a directory', 'checkPath', $_path);
			die('Path doesn\'t point to a directory');
		}
		
		return false;
	}

	if(!is_writable($_path))
	{
		if($_die)
		{
			errorLog('Directory isn\'t writable (please `chmod 1777`)', 'checkPath', $_path);
			die('Directory isn\'t writable');
		}
		
		return false;
	}
	else if(AUTO !== true && !is_file($_file))
	{
		if(AUTO === false)
		{
			if($_die)
			{
				errorLog('AUTO is false', 'checkPath', $_file, false);
				die(NONE);
			}
			
			return false;
		}
		else if(gettype(AUTO) === 'integer')
		{
			if(countFiles($_path, false, true, false) >= AUTO)
			{
				if($_die)
				{
					errorLog('AUTO is too low', 'checkPath', $_file, false);
					die(NONE);
				}
				
				return false;
			}
		}
		else if($_die)
		{
			errorLog('Invalid \'AUTO\' constant', 'checkPath', $_file);
			die('Invalid \'AUTO\' constant');
		}
		else
		{
			return false;
		}
	}
	else if(!is_writable($_file))
	{
		if($_die)
		{
			errorLog('File is not writable', 'checkPath', $_file);
			die('File is not writable');
		}
		
		return false;
	}
	
	//
	return true;
}

checkPath(PATH, PATH_FILE, true);

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

	if(CLIENT)
	{
		$result = testCookie();
	}

	if($result && SERVER)
	{
		$result = testFile();
	}

	return $result;
}

function testFile($_path = PATH_IP)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			errorLog('Is not a regular file', 'testFile', $_path);
			die('Is not a regular file');
		}
		else if(!is_readable($_path))
		{
			errorLog('File can\'t be read', 'testFile', $_path);
			die('File can\'t be read');
		}
		else if(timestamp(readTimestamp($_path)) < THRESHOLD)
		{
			return false;
		}
	}

	return true;
}

function testCookie()
{
	if(empty($_COOKIE[COOKIE]))
	{
		makeCookie();
	}
	else if(timestamp((int)$_COOKIE[COOKIE]) < THRESHOLD)
	{
		return false;
	}

	return true;
}

function makeCookie()
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

function cleanFiles($_path = PATH_DIR)
{
	die('TODO: cleanFiles(' . (string)$_clean . ')');
	return getCount();//!
}

function getTime($_path = PATH_IP)
{
	die('TODO: getTime()');
}

function initCount($_path = PATH_COUNT, $_directory = PATH_DIR)
{
	$result = countFiles($_directory, false, false, false);
	$written = file_put_contents($_path, (string)$result);

	if($written === false)
	{
		errorLog('Couldn\'t initialize count', 'initCount', $_path);
		die('Couldn\'t initialize count');
	}

	return $result;
}

function getCount($_path = PATH_COUNT)
{
	if(!file_exists($_path))
	{
		return initCount($_path);
	}
	else if(!is_file($_path))
	{
		errorLog('Count file is not a file', 'getCount', $_path);
		die('Count file is not a file');
	}

	$result = file_get_contents($_path);

	if($result === false)
	{
		errorLog('Couldn\'t read count value', 'getCount', $_path);
		die('Couldn\'t read count value');
	}

	return (int)$result;
}

function setCount($_value, $_path = PATH_COUNT, $_get = true)
{
	$result = null;

	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			errorLog('Count file is not a regular file', 'setCount', $_path);
			die('Count file is not a regular file');
		}
		else if($_get)
		{
			$result = getCount($_path);
		}
	}
	else
	{
		$result = initCount($_path);
	}

	$written = file_put_contents($_path, (string)$_value);

	if($written === false)
	{
		errorLog('Unable to write count', 'setCount', $_path);
		die('Unable to write count');
	}

	return $result;
}

function increaseCount($_path = PATH_COUNT)
{
	$count = getCount($_path);
	setCount(++$count, $_path, false);
	return $count;
}

function decreaseCount($_path = PATH_COUNT)
{
	$count = getCount($_path);

	if($count > 0)
	{
		setCount(--$count, $_path, false);
	}

	return $count;
}

function deleteTimestamp($_path = PATH_IP, $_die = true)
{
	if(CLEAN === null)
	{
		if($_die)
		{
			errorLog('Deletion is not allowed', 'deleteTimestamp', $_path);
			die('Deletion is not allowed');
		}

		return null;
	}

	$result = unlink($_path);

	if($result)
	{
		decreaseCount();
	}
	else if($_die)
	{
		errorLog('Unable to delete timestamp file', 'deleteTimestamp', $_path);
		die('Unable to delete timestamp file');
	}

	return $result;
}

function readTimestamp($_path = PATH_IP)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			errorLog('Is not a file', 'readTimestamp', $_path);
			die('Is not a file');
		}
		else if(!is_readable($_path))
		{
			errorLog('File not readable', 'readTimestamp', $_path);
			die('File not readable');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
			errorLog('Unable to read timestamp', 'readTimestamp', $_path);
			die('Unable to read timestamp');
		}

		return (int)$result;
	}

	return 0;
}

function writeTimestamp($_path = PATH_IP, $_clean = (CLEAN !== null))
{
	$existed = file_exists($_path);

	if($existed)
	{
		if(!is_file($_path))
		{
			errorLog('It\'s no regular file', 'writeTimestamp', $_path);
			die('It\'s no regular file');
		}
		else if(!is_writable($_path))
		{
			errorLog('Not a writable file', 'writeTimestamp', $_path);
			die('Not a writable file');
		}
	}
	else if(getCount() > LIMIT)
	{
		if($_clean)
		{
			if(cleanFiles() > LIMIT)
			{
				errorLog('LIMIT exceeded, even after cleanFiles()', 'writeTimestamp', $_path);
				return null;
			}
		}
		else
		{
			errorLog('LIMIT exceeded (and no cleanFiles() called)', 'writeTimestamp', $_path);
			return null;
		}
	}
	
	$result = file_put_contents($_path, (string)timestamp());

	if($result === false)
	{
		errorLog('Unable to write timestamp', 'writeTimestamp', $_path);
		die('Unable to write timestamp');
	}
	else if(!$existed)
	{
		increaseCount();
	}

	return $result;
}

function readValue($_path = PATH_FILE)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			errorLog('It\'s not a regular file', 'readValue', $_path);
			die('It\'s not a regular file');
		}
		else if(!is_readable($_path))
		{
			errorLog('File is not readable', 'readValue', $_path);
			die('File is not readable');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
			errorLog('Unable to read value', 'readValue', $_path);
			die('Unable to read value');
		}

		return (int)$result;
	}
	else
	{
		touch($_path);
	}

	return 0;
}

function writeValue($_value = 0, $_path = PATH_FILE)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			errorLog('Not a regular file', 'writeValue', $_path);
			die('Not a regular file');
		}
		else if(!is_writable($_path))
		{
			errorLog('File is not writable', 'writeValue', $_path);
			die('File is not writable');
		}
	}

	$result = file_put_contents($_path, (string)$_value);

	if($result === false)
	{
		errorLog('Unable to write value', 'writeValue', $_path);
		die('Unable to write value');
	}

	return $result;
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
		errorLog('Not a directory', '', PATH_DIR);
		die('Not a directory');
	}
}

//
$value = readValue();

if(test())
{
	writeValue(++$value);
}

if(CLIENT)
{
	makeCookie();
}

//
$value = (string)$value;
header('Content-Length: ' . strlen($value));
echo $value;

//
if(SERVER)
{
	//
	writeTimestamp();

	//
	if(CLEAN === true)
	{
		cleanFiles();
	}
	else if(gettype(CLEAN) === 'integer')
	{
		$count = getCount();

		if($count >= CLEAN)
		{
			cleanFiles();
		}
	}
}

//
exit();

?>
