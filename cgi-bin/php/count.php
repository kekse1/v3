<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 * v2.3.1
 */

// 
// TODO: *CLEAN*!!
//

//
define('AUTO', 255);
define('THRESHOLD', 7200);
define('DIRECTORY', 'count');
define('ERROR', 'error.log');
define('CLIENT', true);
define('SERVER', true);
define('HASH', 'sha3-256');
define('HASH_IP', false);
define('TYPE_CONTENT', 'text/plain;charset=UTF-8');
define('CLEAN', false);
define('LIMIT', 65535);
define('COOKIE_PATH', '/');
define('COOKIE_SAME_SITE', 'Strict');
define('COOKIE_HTTP_ONLY', true);
define('COOKIE_SECURE', !empty($_SERVER['HTTPS']));

//
header('Content-Type: ' . TYPE_CONTENT);

//
if(AUTO === null)
{
	die('/');
}

//
function secureHost($_host)
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
		else if($_host[$i] === ' ')
		{
			$put = ' ';
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

	if(strlen($result) === 0)
	{
		die('Secured host got no length');
	}

	return $result;
}

//
$host = '';

if(! empty($_SERVER['HTTP_HOST']))
{
	$host = $_SERVER['HTTP_HOST'];
}
else if(! empty($_SERVER['SERVER_NAME']))
{
	$host = $_SERVER['SERVER_NAME'];
}
else
{
	die('No server host/name applicable');
}

function endsWith($_haystack, $_needle)
{
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}

	return (substr($_haystack, -strlen($_needle)) === $_needle);
}

if(!empty($_SERVER['SERVER_PORT']))
{
	if(! empty($_SERVER['HTTPS']))
	{
		if($_SERVER['SERVER_PORT'] === '443')
		{
			if(endsWith($host, ':443'))
			{
				$host = substr($host, -4);
			}
		}
		else
		{
			if(endsWith($host, (':' . $_SERVER['SERVER_PORT'])))
			{
				$host = substr($host, 0, -strlen(':' . $_SERVER['SERVER_PORT']));
			}

			$host .= ' ' . $_SERVER['SERVER_PORT'];
		}
	}
	else if($_SERVER['SERVER_PORT'] === '80')
	{
		if(endsWith($host, ':80'))
		{
			$host = substr($host, -3);
		}
	}
	else
	{
		if(endsWith($host, (':' . $_SERVER['SERVER_PORT'])))
		{
			$host = substr($host, 0, -strlen(':' . $_SERVER['SERVER_PORT']));
		}

		$host .= ' ' . $_SERVER['SERVER_PORT'];
	}
}

//
define('HOST', secureHost($host));
define('COOKIE', hash(HASH, HOST));
unset($host);

//
define('PATH_FILE', (DIRECTORY . '/' . HOST));
define('PATH_DIR', (DIRECTORY . '/+' . HOST));
define('PATH_COUNT', (DIRECTORY . '/-' . HOST));
$addr = (HASH_IP ? hash(HASH, $_SERVER['REMOTE_ADDR']) : secureHost($_SERVER['REMOTE_ADDR']));
define('PATH_IP', (PATH_DIR . '/' . $addr));
define('PATH_ERROR', (DIRECTORY . '/' . ERROR));
unset($addr);

//
function countFiles($_path = DIRECTORY, $_dir = false, $_list = false, $_exclude_count = true, $_exclude_ip = false)
{
	$list = scandir($_path);
	$len = count($list);
	$result = ($_list ? array() : 0);

	for($i = 0, $j = 0; $i < $len; ++$i)
	{
		if($list[$i] === '.' || $list[$i] === '..')
		{
			continue;
		}
		else if($_dir === false)
		{
			if($_exclude_count && $list[$i][0] === '-')
			{
				continue;
			}
			else if(!is_file($_path . '/' . $list[$i]))
			{
				continue;
			}
		}
		else if($_dir === true)
		{
			if($_exclude_ip && $list[$i][0] === '+')
			{
				continue;
			}
			else if(!is_dir($_path . '/' . $list[$i]))
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
if(!file_exists(DIRECTORY))
{
	if(! mkdir(DIRECTORY, 1777, false))
	{
		die('Directory \'' . DIRECTORY . '\' doesn\'t exist, and couldn\'t be created');
	}
}
else if(!is_dir(DIRECTORY))
{
	die('The path \'' + DIRECTORY + '\' is not a directory');
}

if(!is_writable(DIRECTORY))
{
	die('Your directory \'' . DIRECTORY . '\' is not writable (please `chmod 1777`)');
}
else if(AUTO !== true && !is_file(PATH_FILE))
{
	if(AUTO === false)
	{
		die('/');
	}
	else if(gettype(AUTO) === 'integer')
	{
		if(countFiles(DIRECTORY, false, false, true, false) >= AUTO)
		{
			die('/');
		}
	}
	else
	{
		die('Invalid \'AUTO\' constant');
	}
}
else if(!is_writable(PATH_FILE))
{
	die('File \'' . HOST . '\' is not writable');
}

//TODO/check for directory..

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
			die('Is not a regular file');
		}
		else if(!is_readable($_path))
		{
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
		'httponly' => COOKIE_HTTP_ONLY
	));
}

function error($_path, $_source, $_die = true)
{
	$data = $_source . '(' . $_path . ') ' . (string)time() . '\n';
	$result = file_put_contents(PATH_ERROR, $data);

	if($result === false && $_die)
	{
		die('Couldn\'t log error: ' . substr($data, 0, -1));
	}

	return $result;
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

function getCount($_path = PATH_COUNT)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			die('Count file is not a file');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
			die('Couldn\'t read count value');
		}

		return (int)$result;
	}

	setCount(0, $_path, false);
	return 0;
}

function setCount($_value, $_path = PATH_COUNT, $_get = true)
{
	if(file_exists($_path))
	{
		if(!is_file($_path))
		{
			die('Count file is not a regular file');
		}
	}

	$result = null;

	if($_get)
	{
		$result = getCount($_path);
	}
	
	$written = file_put_contents($_path, (string)$_value);

	if($written === false)
	{
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
	$result = unlink($_path);

	if($result)
	{
		decreaseCount();
	}
	else if($_die)
	{
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
			die('Is not a file');
		}
		else if(!is_readable($_path))
		{
			die('File not readable');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
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
			die('It\'s no regular file');
		}
		else if(!is_writable($_path))
		{
			die('Not a writable file');
		}
	}
	else if(getCount() > LIMIT)
	{
		if($_clean)
		{
			if(cleanFiles() > LIMIT)
			{
				error($_path, 'writeTimestamp');
				return null;
			}
		}
		else
		{
			error($_path, 'writeTimestamp');
			return null;
		}
	}
	
	$result = file_put_contents($_path, (string)timestamp());

	if($result === false)
	{
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
			die('It\'s not a regular file');
		}
		else if(!is_readable($_path))
		{
			die('File is not readable');
		}

		$result = file_get_contents($_path);

		if($result === false)
		{
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
			die('Not a regular file');
		}
		else if(!is_writable($_path))
		{
			die('File is not writable');
		}
	}

	$result = file_put_contents($_path, (string)$_value);

	if($result === false)
	{
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
