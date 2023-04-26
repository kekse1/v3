<?php
header('Content-Type: text/plain; charset=utf-8');
header('Content-Length: ' . strlen($_SERVER['REMOTE_ADDR']));
echo $_SERVER['REMOTE_ADDR'];
?>
