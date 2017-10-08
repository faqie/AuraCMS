<?php
/*
 * @version		3.4.0
 * @package		AuraCMS
 * @copyright	Copyright (C) 2013 AuraCMS.
 * @license		GNU/GPL, see LICENSE.txt
 * @description 	containing database information
 *
 * October 22, 2013 , 10:40:23 AM  
 * Iwan Susyanto, S.Si - admin@auracms.org      - 081 327 575 145
 
	
	/*  Pengaturan Error Warning
		0 = false
		E_ALL = true
	*/
	error_reporting(0);
	
	//define('FUNCTION', true);
	if (!defined('FUNCTION')) define('FUNCTION', true);


    $mysql_database	= 'auracms';
    $mysql_host		= 'localhost';
    $mysql_user		= 'root';
    $mysql_password	= 'mysql';
	$link = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_database);

	
	if( ! ini_get('date.timezone') )
	{
	   date_default_timezone_set("Asia/Jakarta");
	}

?>