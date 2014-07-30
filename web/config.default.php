<?php
	$INSTANCE_NAME   = 'TemPIC';
	$URL_BASE        = 'http://example.com/tempic';
	$PATH_UPLOAD     = 'upload';
	$DISALLOWED_EXTS = array('php', 'html', 'htm', 'htaccess', 'htpasswd');
	$SIZE_LIMIT      = 20e6;
	$LIFETIMES       = array(
		'30m' => array('name' => '30 Minutes', 'time' => 30),
		'1h'  => array('name' => '1 Hour',     'time' => 60),
		'2h'  => array('name' => '2 Hours',    'time' => 2*60),
		'4h'  => array('name' => '4 Hours',    'time' => 4*60),
		'10h' => array('name' => '10 Hours',   'time' => 10*60),
		'1d'  => array('name' => '1 Day',      'time' => 24*60),
		'3d'  => array('name' => '3 Days',     'time' => 3*24*60),
	);
?>