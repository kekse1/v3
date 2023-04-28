<?php

/*
 * Copyright (c) Sebastian Kucharczyk <kuchen@kekse.biz>
 */

/*
 * BE SURE TO `chmod 1777 ./counter/`.. (so that PHP can access there, with write permissions, too)!
 */
//
define('DIRECTORY', 'counter');
//define('THRESHOLD', 600);
define('THRESHOLD', 7200);
define('LENGTH', 255);
define('CHARS', array_merge(range('a', 'z'), range('0', '9'), ['.','-']));
define('COOKIE', 'timestamp');
define('COOKIE_SAME_SITE', 'Strict');
define('COOKIE_PATH', '/');
define('COOKIE_HTTP_ONLY', true);

//
function secureHost($_hostname)
{
	$_hostname = strtolower($_hostname);
	$length = min(strlen($_hostname), LENGTH);
	$result = '';

	for($i = 0; $i < $length; $i++)
	{
		if(in_array($_hostname[$i], CHARS))
		{
			$result .= $_hostname[$i];
		}
	}

	if(strlen($result) === 0)
	{
		die('Filtered hostname got no length');
	}

	return $result;
}

//
define('HOSTNAME', secureHost($_SERVER['HTTP_HOST']));
define('PATH', (DIRECTORY . '/' . HOSTNAME));

//
if(! file_exists(DIRECTORY))
{
	die('Directory \'' . DIRECTORY . '\' doesn\'t exist - create with `chmod 1777`.');
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
	if(! isset($_COOKIE[COOKIE]))
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
		'domain' => HOSTNAME,
		//'secure' => !!$_SERVER['HTTPS'],
		'path' => COOKIE_PATH,
		'samesite' => COOKIE_SAME_SITE,
		'httponly' => COOKIE_HTTP_ONLY
	));
}

function readCounter($_path = PATH)
{
	if(! file_exists($_path))
	{
		touch($_path);
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
header('Content-Type: text/plain;charset=UTF-8');
echo $count;
exit();

?>
