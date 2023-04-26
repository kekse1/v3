<?php

/*
 * BE SURE TO `chmod 1777 ./counter/`.. (so that PHP can access there, with write permissions, too)!
 */
//
define('DIRECTORY', 'counter');
define('THRESHOLD', 600);

//
function secureHost($_hostname)
{
	return str_replace('/', '', str_replace('\\', '', $_hostname));
}

//
define('HOSTNAME', $_SERVER['HTTP_HOST']);
define('PATH', (DIRECTORY . '/' . secureHost(HOSTNAME)));

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
	if(! isset($_COOKIE['timestamp']))
	{
		makeCookie();
		return true;
	}
	else if(timestamp((int)$_COOKIE['timestamp']) < THRESHOLD)
	{
		return false;
	}

	return true;
}

function makeCookie($_domain = HOSTNAME, $_hours = 2, $_days = 0, $_path = '/', $_same_site = 'Strict', $_http_only = true)
{
	return setcookie('timestamp', timestamp(), array(
		'expires' => (time() + (($_hours * 60 * 60) + ($_days * 60 * 60 * 24))),
		'domain' => $_domain,
		//'secure' => !!$_SERVER['HTTPS'],
		'path' => $_path,
		'samesite' => $_same_site,
		'httponly' => $_http_only));
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
header('Content-Type: text/plain; charset=UTF-8');
echo $count;
exit();

?>
