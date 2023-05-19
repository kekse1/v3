<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 * v2.0.0
 */

//
define('AUTO', 255);
define('DIRECTORY', 'counter');
define('THRESHOLD', 7200);
define('CLIENT', true);
define('SERVER', false);
define('BOTH', true);
define('TYPE_CONTENT', 'text/plain;charset=UTF-8');
define('CLEAN', 255);
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
function endsWith($_haystack, $_needle)
{
	if(strlen($_needle) > strlen($_haystack))
	{
		return false;
	}

	return (substr($_haystack, -strlen($_needle)) === $_needle);
}

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
unset($host);

//
define('PATH_FILE', (DIRECTORY . '/' . HOST));
define('PATH_DIR', (DIRECTORY . '/+' . HOST));
define('PATH_TIME', (PATH_DIR . '/' . HOST));

//
function countFiles($_path = DIRECTORY)
{
	$list = scandir($_path);
	$len = count($list);
	$result = 0;

	for($i = 0; $i < $len; ++$i)
	{
		if(is_file($_path . $result[$i]))
		{
			++$result;
		}
	}

	return $result;
}

//
if(!file_exists(DIRECTORY))
{
	die('Directory \'' . DIRECTORY . '\' doesn\'t exist - create with `chmod 1777`.');
}
else if(!is_dir(DIRECTORY))
{
	die('The path \'' + DIRECTORY + '\' is not a directory!');
}
else if(!is_writable(DIRECTORY))
{
	die('Your directory \'' . DIRECTORY . '\' is not writable (please `chmod 1777`)');
}
else if(AUTO !== true && !file_exists(PATH_FILE))
{
	if(AUTO === false)
	{
		die('/');
	}
	else if(gettype(AUTO) === 'integer')
	{
		if(countFiles(DIRECTORY) >= AUTO)
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

function test($_both = BOTH)
{
	if(! (CLIENT || SERVER))
	{
		return true;
	}

	$result = true;

	if(CLIENT && !testCookie())
	{
		$result = false;
	}
	else if(! $_both)
	{
		return true;
	}

	if(SERVER && !testFiles())
	{
		$result = false;
	}
	else if(! $_both)
	{
		return true;
	}

	return $result;
}

function testFiles()
{
	return true;
	//cleanFiles();
}

function testCookie()
{
	if(!isset($_COOKIE[HOST]))
	{
		makeCookie();
	}
	else if(timestamp((int)$_COOKIE[HOST]) < THRESHOLD)
	{
		return false;
	}

	return true;
}

function makeCookie()
{
	return setcookie(HOST, timestamp(), array(
		'expires' => (time() + THRESHOLD),
		//'domain' => str_replace(' ', ':', HOST),
		'secure' => COOKIE_SECURE,
		'path' => COOKIE_PATH,
		'samesite' => COOKIE_SAME_SITE,
		'httponly' => COOKIE_HTTP_ONLY
	));
}

function cleanFiles($_min = CLEAN)
{
	die('TODO: cleanFiles(' . $_min . ')');
}

function getFileModificationTime($_path)
{
}

function readTimestamp($_path = PATH_TIME)
{
}

function writeTimestamp($_path = PATH_TIME)
{
}

function readCounter($_path = PATH_FILE)
{
	if(!file_exists($_path))
	{
		touch($_path);
		return 0;
	}

	return (int)file_get_contents($_path);
}

function writeCounter($_value = 0, $_path = PATH_FILE)
{
	return file_put_contents($_path, (string)$_value);
}

//
$count = readCounter();

if(test())//if(testCookie())
{
	writeCounter(++$count);
}

if(CLIENT) makeCookie();
if(SERVER) writeTimestamp();

//
$count = (string)$count;
header('Content-Length: ' . strlen($count));
echo $count;

//
exit();

?>

