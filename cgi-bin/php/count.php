<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 * v2.8.5
 */

//
define('VERSION', [ 2, 8, 5 ]);
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
define('CLEAN', true);
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
define('COLOR_FG', 'rgba(0, 0, 0, 1)');
define('COLOR_BG', 'rgba(255, 255, 255, 0)');

//
define('COOKIE_PATH', '/');
define('COOKIE_SAME_SITE', 'Strict');
define('COOKIE_SECURE', false);//(!empty($_SERVER['HTTPS']));
define('COOKIE_HTTP_ONLY', true);

//
function secureHost($_host, $_die = true)
{
	if($_host[0] === '/' || $_host[0] === '\\')
	{
		if($_die)
		{
			die('Invalid $_host');
		}

		return null;
	}

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

function securePath($_string, $_die = true)
{
	if(gettype($_string) !== 'string')
	{
		errorLog('Invalid path $_string', 'securePath', '', $_die);

		if($_die)
		{
			die('Invalid path $_string');
		}
		
		return null;
	}
	else if($_string[0] === '.')
	{
		if($_die)
		{
			die('No hidden/dot files allowed here');
		}

		return null;
	}
	else if($_string[0] === '/' || $_string[0] === '\\')
	{
		if($_die)
		{
			die('Absolute paths are not allowed here');
		}

		return null;
	}

	$len = strlen($_string);
	
	if($len > 255)
	{
		errorLog('Secured path string is too long (above 255 chars)', 'securePath', $_string, $_die);

		if($_die)
		{
			die('Secured path string is too long');
		}
		
		return null;
	}
	
	$result = '';
	
	for($i = 0; $i < $len; ++$i)
	{
		if($_string[$i] === '.')
		{
			if(strlen($result) === 0)
			{
				continue;
			}
			else if($result[strlen($result) - 1] === '.')
			{
				continue;
			}
		}
		else if($_string[$i] === '*' || $_string[$i] === '?')
		{
			continue;
		}
		else if($_string[$i] === ':')
		{
			continue;
		}
		else if(ord($_string[$i]) < 32)
		{
			continue;
		}
		
		$result .= $_string[$i];
	}
	
	if(empty($result))
	{
		errorLog('Secured path string is/was empty', 'securePath', $_string, $_die);

		if($_die)
		{
			die('Secured path string is/became empty');
		}
		
		return null;
	}

	return $result;
}

function errorLog($_reason, $_source = '', $_path = '', $_die = true)
{
	$noLog = false;
	$data = null;
	
	if(!defined('PATH_LOG'))
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
		fprintf(STDERR, ' >> ' . $data);
		$result = $data;
	}
	else
	{
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
	}

	return $result;
}

//
function checkPath($_path = PATH, $_file = PATH_FILE)
{
	//
	$_path = securePath($_path, true);

	//
	if(!file_exists($_path))
	{
		die('Directory doesn\'t exist!');
	}
	else if(!is_dir($_path))
	{
		die('Path doesn\'t point to a directory');
	}
	else if(!is_writable($_path))
	{
		die('Directory isn\'t writable');
	}
	else if(AUTO !== true && !is_file($_file))
	{
		if(AUTO === false)
		{
			errorLog('AUTO is false', 'checkPath', $_file, false);
			die(NONE);
		}
		else if(gettype(AUTO) === 'integer')
		{
			if(countFiles($_path, false, true, false) >= AUTO)
			{
				errorLog('AUTO is too low', 'checkPath', $_file, false);
				die(NONE);
			}
		}
		else
		{
			errorLog('Invalid \'AUTO\' constant', 'checkPath', $_file);
			die('Invalid \'AUTO\' constant');
		}
	}
	else if(!is_writable($_file))
	{
		errorLog('File is not writable', 'checkPath', $_file);
		die('File is not writable');
	}
	
	//
	return true;
}

//
function countFiles($_path = PATH, $_dir = false, $_exclude = true, $_list = false)
{
	if(gettype($_path) !== 'string')
	{
		errorLog('Path is not a string', 'countFiles', '', true);
		die('Path is not a string');
	}
	else
	{
		$_path = securePath($_path, true);
	}
	
	if(! is_dir($_path))
	{
		errorLog('This is not a directory', 'countFiles', $_path);
		die('This is not a directory');
	}

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
		else if($list[$i] === LOG)
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
			$result[$j++] = $list[$i];
		}
		else
		{
			++$result;
		}
	}

	return $result;
}

function removeDirectory($_path, $_recursive = true, $_die = true, $_depth_current = 0)
{
	//
	if(($_path = securePath($_path, $_die)) === null)
	{
		return null;
	}
	
	//
	if(is_dir($_path))
	{
		if(! $_recursive)
		{
			return !!(rmdir($_path));
		}
		
		$handle = opendir($_path);
		
		while($sub = readdir($handle))
		{
			if($sub === '.' || $sub === '..')
			{
				continue;
			}
			else if(is_dir($_path . '/' . $sub))
			{
				removeDirectory($_path . '/' . $sub, true, $_die, $_depth_current + 1);
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
	
	function getArgument($_index, $_argv = ARGV)
	{
		if(empty($_argv[$_index]))
		{
			return null;
		}
		else if($_argv[$_index][0] === '-')
		{
			return null;
		}
		
		return explode(',', $_argv[$_index]);
	}

	//
	function getHosts($_path = PATH, $_counts = false, $_die = true)
	{
		$result = countFiles(securePath($_path, $_die), ($_counts ? null : false), ($_counts ? false : true), true);

		if($_counts) for($i = 0; $i < count($result); ++$i)
		{
			if($result[$i][0] === '+' || $result[$i][0] === '-')
			{
				$result[$i] = substr($result[$i], 1);
			}
			else
			{
				array_splice($result, $i--, 1);
			}
		}

		return $result;
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
		printf('    -V / --version' . PHP_EOL);
		printf('    -C / --copyright' . PHP_EOL);
		printf('    -h / --hashes' . PHP_EOL);
		printf('    -c / --config' . PHP_EOL);
		printf('    -v / --values [host,..]' . PHP_EOL);
		printf('    -y / --sync [host,..]' . PHP_EOL);
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

		if(gettype(COLOR_FG) === 'string' && !empty(COLOR_FG))
		{
			printf(START.'Non-empty String (without further tests)' . PHP_EOL, 'COLOR_FG', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'COLOR_FG', 'BAD');
			++$errors;
		}

		if(gettype(COLOR_BG) === 'string' && !empty(COLOR_BG))
		{
			printf(START.'Non-empty String (without further tests)' . PHP_EOL, 'COLOR_BG', 'OK');
			++$ok;
		}
		else
		{
			fprintf(STDERR, START.'No non-empty String' . PHP_EOL, 'COLOR_BG', 'BAD');
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
	
	function getCache($_path = PATH, $_host = null, $_stop = true)
	{
		//
		$_path = securePath($_path, true);
		$hosts = null;
		$sub = null;
		
		if(gettype($_host) === 'string')
		{
			$hosts = [ $_host ];
		}
		else
		{
			$hosts = getHosts($_path, false, true);
		}
		
		for($i = 0; $i < count($hosts); ++$i)
		{
			//
			$hosts[$i] = secureHost($hosts[$i], true);
			$sub = securePath($_path . '/+' . $hosts[$i], true);
			
			//
			if(file_exists($sub))
			{
				if(!is_dir($sub))
				{
					fprintf(STDERR, ' >> Cache directory for host \'%s\' is not a directory! Please replace/remove it asap!' . PHP_EOL, $_host);
					exit(1);
				}
				else if(!is_readable($sub))
				{
					fprintf(STDERR, ' >> Cache directory for host \'%s\' is not readable! Please replace/remove it asap!' . PHP_EOL, $_host);
					exit(2);
				}
			}
			else
			{
				array_splice($hosts, $i--, 1);
			}
		}
		
		if(count($hosts) === 0)
		{
			return array();
		}

		$hostsCount = count($hosts);
		$result = array();

		for($i = 0, $j = 0; $i < $hostsCount; ++$i)
		{
			$list = countFiles($_path . '/+' . $hosts[$i], false, false, true);
			$filesCount = count($list);

			for($k = 0; $k < $filesCount; ++$k)
			{
				$sub = $_path . '/+' . $hosts[$i] . '/' . $list[$k];
				
				if(is_file($sub))
				{
					$result[$j++] = $_path . '/+' . $hosts[$i] . '/' . $list[$k];
				}
			}
		}
		
		return $result;
	}

	function values($_index = -1, $_path = PATH)
	{
		//
		$_path = securePath($_path, true);
		$hosts = getArgument($_index + 1);

		//
		if($hosts === null)
		{
			$hosts = getHosts($_path, false);
		}

		if(count($hosts) === 0)
		{
			fprintf(STDERR, ' >> No hosts found' . PHP_EOL);
			exit(1);
		}
		
		$files = array();
		$file = null;

		for($i = 0, $j = 0; $i < count($hosts); ++$i)
		{
			$hosts[$i] = secureHost($hosts[$i]);
			$file = securePath($_path . '/' . $hosts[$i]);
			
			if(is_file($file))
			{
				$files[$j++] = $file;
			}
			else
			{
				fprintf(STDERR, ' >> No host \'%s\'' . PHP_EOL, $hosts[$i]);
				array_splice($hosts, $i--, 1);
			}
		}
		
		$len = count($files);
		
		if($len === 0)
		{
			fprintf(STDERR, ' >> No host files left' . PHP_EOL);
			exit(2);
		}

		$value = -1;
		$maxLen = 0;
		$len = 0;
		
		for($i = 0; $i < $len; ++$i)
		{
			if(($len = strlen($hosts[$i])) > $maxLen)
			{
				$maxLen = $len;
			}
		}
		
		$len = count($files);
		$maxLen += 13;
		$START = '%' . $maxLen . 's: ';
		
		for($i = 0; $i < $len; ++$i)
		{
			if(($value = file_get_contents($files[$i])) === false)
			{
				fprintf(STDERR, $START . 'Unable to read host file' . PHP_EOL, $$hosts[$i]);
			}
			else
			{
				printf($START . $value . PHP_EOL, $hosts[$i]);
			}
		}

		//
		exit(0);
	}

	function sync($_index = null, $_path = PATH)
	{
		//
		$_path = securePath($_path, true);
		$hosts = getArgument($_index + 1);
		
		if($hosts === null)
		{
			$hosts = getHosts($_path, true);
		}
		
		for($i = 0; $i < count($hosts); ++$i)
		{
			if(!is_dir($_path . '/+' . $hosts[$i]))
			{
				if(is_file($_path . '/-' . $hosts[$i]) && is_writable($_path . '/-' . $hosts[$i]))
				{
					unlink($_path . '/-' . $hosts[$i]);
				}

				array_splice($hosts, $i--, 1);
			}
		}

		$len = count($hosts);

		if($len === 0)
		{
			fprintf(STDERR, ' >> No cache directories found.' . PHP_EOL);
			exit(1);
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
			
			$new = countFiles($_path . '/+' . $hosts[$i], false, false, false);
			
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
		$result = array();
		$cached = getCache($_path, $_host);
		$len = count($cached);
		
		if($len === 0)
		{
			printf('No cache files available to clean.' . PHP_EOL);
			exit(0);
		}
		
		$delete = array();

		//
		for($i = 0, $j = 0; $i < $len; ++$i)
		{
			$time = (int)file_get_contents($cached[$i]);
			$diff = timestamp($time);
			
			if($diff >= THRESHOLD)
			{
				$delete[$j++] = $cached[$i];
			}
		}
		
		//
		$len = count($delete);
		$success = array();
		$errors = 0;
		$dirs = array();
		
		if($len === 0)
		{
			fprintf(STDERR, ' >> No files for cleaning left');
			
			if(count($cached) === 0)
			{
				fprintf(STDERR, ' (had no real files)');
			}
			else
			{
				fprintf(STDERR, ' (all files are too young)');
			}
			
			fprintf(STDERR, PHP_EOL);
			exit(1);
		}
		else for($i = 0, $j = 0, $k = 0; $i < $len; ++$i)
		{
			if(unlink($delete[$i]) === true)
			{
				$sub = dirname($delete[$i]);
				
				if(!in_array($sub, $dirs))
				{
					$dirs[$k++] = dirname($delete[$i]);
				}
				
				$success[$j++] = $delete[$i];
			}
			else
			{
				++$errors;
			}
		}
		
		$len = count($dirs);
		
		//
		printf(' >> %d successfully cleaned' . PHP_EOL, count($success));

		if($errors > 0)
		{
			fprintf(STDERR, ' >> %d cache files couldn\'t be deleted..' . PHP_EOL, $errors);
			$errors = 0;
		}
		
		$target = null;

		for($i = 0, $j = 0; $i < $len; ++$i)
		{
			$sub = countFiles($dirs[$i], false, false, false);
			$target = str_replace('+', '-', $dirs[$i]);

			if(file_put_contents($target, (string)$sub))
			{
				printf(' >> File \'%s\' sync\'ed (counting %d files)' . PHP_EOL, $target, $sub);
			}
			else
			{
				fprintf(STDERR, ' >> File \'%s\' couldn\'t be written (for sync, with %d files)..' . PHP_EOL, $target, $sub);
			}
		}
		
		//
		exit();
	}

	function purge($_index = -1, $_path = PATH, $_host = null)
	{
		//
		$hosts = getArgument($_index + 1);

		if($hosts === null)
		{
			$hosts = getHosts($_path, true);
		}

		$len = count($hosts);

		if($len === 0)
		{
			printf('No hosts available to purge their cache files..' . PHP_EOL);
			exit(0);
		}
		else for($i = 0; $i < count($hosts); ++$i)
		{
			if(!is_dir($_path . '/+' . $hosts[$i]) && !is_file($_path . '/-' . $hosts[$i]))
			{
				array_splice($hosts, $i--, 1);
			}
		}

		if(count($hosts) === 0)
		{
			fprintf(STDERR, ' >> No hosts found' . PHP_EOL);
			exit(1);
		}

		$input = prompt('Do you really want to purge all cache files for ' . $len . ' hosts [yes/no]? ');
		
		if(!$input)
		{
			fprintf(STDERR, ' >> Aborting (as requested)!' . PHP_EOL);
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
				if(unlink($sub))
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
				if(removeDirectory($sub, true, false))
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

	function unlog($_index = -1, $_path = (PATH . '/' . LOG))
	{
		$_path = securePath($_path, true);
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
		else if(unlink($_path) === false)
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

	function errors($_index = -1, $_path = (PATH . '/' . LOG))
	{
		$_path = securePath($_path, true);
		
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

		function countLines($_file, $_chunks = 4096)
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

		$result = countLines($_path);

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
		if($argv[$i] === '-?' || $argv[$i] === '--help')
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
		else if($argv[$i] === '-y' || $argv[$i] === '--sync')
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
define('PATH_FILE', securePath(PATH . '/' . HOST, true));
define('PATH_DIR', securePath(PATH . '/+' . HOST, true));
define('PATH_COUNT', securePath(PATH . '/-' . HOST, true));
define('PATH_IP', securePath(PATH_DIR . '/' . (HASH_IP ? hash(HASH, $_SERVER['REMOTE_ADDR']) : secureHost($_SERVER['REMOTE_ADDR'])), true));
define('PATH_LOG', securePath(PATH . '/' . LOG, true));

//
header('Content-Type: ' . CONTENT);

//
if(AUTO === null)
{
	die(NONE);
}
else
{
	checkPath(PATH, PATH_FILE);
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

function cleanFiles($_dir = PATH_DIR, $_file = PATH_FILE)
{
	if(CLEAN === null)
	{
		errorLog('Called function, but CLEAN === null', 'cleanFiles', '', false);
		return -1;
	}
	else if(!is_dir($_dir))
	{
		return 0;
	}

	$dirs = countFiles($_dir, false, false, true);
	$len = count($dirs);
	$deleted = 0;

	for($i = 0; $i < $len; ++$i)
	{
		if(!is_file($_dir . '/' . $dirs[$i]) || !is_readable($_dir . '/' . $dirs[$i]) || !is_writable($_dir . '/' . $dirs[$i]))
		{
			errorLog('File is not a regular file, or it\'s not readable or writable..', 'cleanFiles', $_dir . '/' . $dirs[$i], false);
		}
		else
		{
			$time = (int)file_get_contents($_dir . '/' . $dirs[$i]);
			$diff = timestamp($time);

			if($diff >= THRESHOLD)
			{
				if(unlink($_dir . '/' . $dirs[$i]) === false)
				{
					errorLog('File couldn\'t be deleted..', 'cleanFiles', $_dir . '/' . $dirs[$i], false);
				}
				else
				{
					++$deleted;
				}
			}
		}
	}

	if($deleted > 0)
	{
		$total = ($len - $deleted);

		if($total < 0)
		{
			errorLog('Total count is below zero, as originally ' . $len . ' files, and ' . $deleted . ' deleted?', 'cleanFiles', $_dir, false);
			return -1;
		}
		else if(file_put_contents($_file, (string)$total) === false)
		{
			errorLog('Couldn\'t write new total count (' . $total . ') to file', 'cleanFiles', $_file, false);
			return -1;
		}
	}

	return $deleted;
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
				die('LIMIT exceeded, even after cleanFiles()');
				return null;
			}
		}
		else
		{
			errorLog('LIMIT exceeded (and no cleanFiles() called)', 'writeTimestamp', $_path);
			die('LIMIT exceeded; w/o cleanFiles() call');
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
