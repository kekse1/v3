<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 */

//
define('AUTO', 255);
define('DIRECTORY', 'counter');
define('THRESHOLD', 7200);
define('LENGTH', 255);
define('COOKIE', 'timestamp');
define('COOKIE_SAME_SITE', 'Strict');
define('COOKIE_PATH', '/');
define('COOKIE_HTTP_ONLY', true);
define('COOKIE_SECURE', !empty($_SERVER['HTTPS']));
define('CONTENT_TYPE', 'text/plain;charset=UTF-8');

//
header('Content-Type: ' . CONTENT_TYPE);

//
if(AUTO === null)
{
	die('/');
}

//
function secureHost($_host)
{
	$length = min(strlen($_host), LENGTH);
	$result = '';
	$byte = null;
	$put = '';

	for($i = 0; $i < $length; $i++)
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
		else if($_host[$i] === '_')
		{
			$put = '_';
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

$host = secureHost($host);

if(strlen($_SERVER['SERVER_PORT']) > 0)
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
		else if(!endsWith($host, (':' . $_SERVER['SERVER_PORT'])))
		{
			$host .= ('_' . $_SERVER['SERVER_PORT']);
		}
		else
		{
			$host = substr($host, 0, -strlen(':' . $_SERVER['SERVER_PORT'])) . '_' . $_SERVER['SERVER_PORT'];
		}
	}
	else if($_SERVER['SERVER_PORT'] === '80')
	{
		if(endsWith($host, ':80'))
		{
			$host = substr($host, -3);
		}
	}
	else if(!endsWith($host, (':' . $_SERVER['SERVER_PORT'])))
	{
		$host .= ('_' . $_SERVER['SERVER_PORT']);
	}
	else
	{
		$host = substr($host, 0, -strlen(':' . $_SERVER['SERVER_PORT'])) . '_' . $_SERVER['SERVER_PORT'];
	}
}

define('HOST', $host);
define('PATH', (DIRECTORY . '/' . $host));
unset($host);

//
if(!file_exists(DIRECTORY))
{
	die('Directory \'' . DIRECTORY . '\' doesn\'t exist - create with `chmod 1777`.');
}
else if(AUTO !== true && !file_exists(PATH))
{
	if(AUTO === false)
	{
		die('/');
	}
	else if(gettype(AUTO) === 'integer')
	{
		$existing = (count(scandir(DIRECTORY)) - 2);

		if($existing >= AUTO)
		{
			die('/');
		}
	}
	else
	{
		die('Invalid \'AUTO\' constant');
	}
}
else if(!is_writable(PATH))
{
	die('File \'' . HOST . '\' is not writable');
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

function testCookie()
{
	if(!isset($_COOKIE[COOKIE]))
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
		'domain' => HOST,
		'secure' => COOKIE_SECURE,
		'path' => COOKIE_PATH,
		'samesite' => COOKIE_SAME_SITE,
		'httponly' => COOKIE_HTTP_ONLY
	));
}

function readCounter($_path = PATH)
{
	if(!file_exists($_path))
	{
		touch($_path);
		return 0;
	}

	return (int)file_get_contents($_path);
}

function writeCounter($_value = 0, $_path = PATH)
{
	return file_put_contents($_path, (string)$_value);
}

//
$count = readCounter();

if(testCookie())
{
	writeCounter(++$count);
}

makeCookie();

//
$count = (string)$count;
header('Content-Length: ' . strlen($count));
echo $count;

//
exit();

?>

