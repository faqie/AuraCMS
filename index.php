<?php
// ----------------------------------------------------------------------------------------------------
// - Display Errors
// ----------------------------------------------------------------------------------------------------
ini_set('display_errors', 'On');
ini_set('html_errors', 0);

// ----------------------------------------------------------------------------------------------------
// - Error Reporting
// ----------------------------------------------------------------------------------------------------
error_reporting(-1);

// ----------------------------------------------------------------------------------------------------
// - Shutdown Handler
// ----------------------------------------------------------------------------------------------------
function ShutdownHandler()
{
    if(@is_array($error = @error_get_last()))
    {
        return(@call_user_func_array('ErrorHandler', $error));
    };

    return(TRUE);
};

register_shutdown_function('ShutdownHandler');

// ----------------------------------------------------------------------------------------------------
// - Error Handler
// ----------------------------------------------------------------------------------------------------
function ErrorHandler($type, $message, $file, $line)
{
    $_ERRORS = Array(
        0x0001 => 'E_ERROR',
        0x0002 => 'E_WARNING',
        0x0004 => 'E_PARSE',
        0x0008 => 'E_NOTICE',
        0x0010 => 'E_CORE_ERROR',
        0x0020 => 'E_CORE_WARNING',
        0x0040 => 'E_COMPILE_ERROR',
        0x0080 => 'E_COMPILE_WARNING',
        0x0100 => 'E_USER_ERROR',
        0x0200 => 'E_USER_WARNING',
        0x0400 => 'E_USER_NOTICE',
        0x0800 => 'E_STRICT',
        0x1000 => 'E_RECOVERABLE_ERROR',
        0x2000 => 'E_DEPRECATED',
        0x4000 => 'E_USER_DEPRECATED'
    );

    if(!@is_string($name = @array_search($type, @array_flip($_ERRORS))))
    {
        $name = 'E_UNKNOWN';
    };

    return(print(@sprintf("%s Error in file \xBB%s\xAB at line %d: %s\n", $name, @basename($file), $line, $message)));
};

$old_error_handler = set_error_handler("ErrorHandler");

// =================

	/**
	 *	AuraCMS v.3.0
	 * 	Oktober 1, 2013 05:12:10 AM 
	 *	Iwan Susyanto, S.Si - admin@auracms.org      - 081 327 575 145
	 */
 
	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}

	if(DS == '/'){
		define('WEBROOT_DIR',__DIR__.'/');
	}else{
		define('WEBROOT_DIR',str_replace('\\', '/', __DIR__).'/');
	}
	if (!defined('INC')) {
		define('INC', WEBROOT_DIR . 'includes/');
	}
	if (!defined('JS')) {
		define('JS', WEBROOT_DIR . 'js/');
	}
	if (!defined('CSS')) {
		define('CSS', WEBROOT_DIR . 'css/');
	}
	if (!defined('MOD')) {
		define('MOD', WEBROOT_DIR . 'mod/');
	}
	
	include INC . 'session.php';
	@header("Content-type: text/html; charset=utf-8;");
	ob_start("ob_gzhandler");
	
	define('INDEX', true);
	
	if (!file_exists("includes/connection.php")){
		require_once ('installer/install.php');
		die();
	}else{	
		include INC . 'connection.php';
		include INC . 'mysql.php';
		include INC . 'global.php';
		include INC . 'phpmailerautoload.php';
		include INC . 'fungsi.php';
		include INC . 'template.php';
		include INC . 'admin.lib.php';
		include INC . 'json.php';
	}
	
	if (!defined('THEMES')) {
		define('THEMES', WEBROOT_DIR . 'themes/'.get_setting('themes').'/');
	}
	$timer = new microTimer;
	$timer->start();
	
	$_GET['mod'] 	= !isset($_GET['mod']) ? null : $_GET['mod'];
	$_GET['action'] = !isset($_GET['action']) ? null : $_GET['action'];
	$_GET['view'] 	= !isset($_GET['view']) ? null : $_GET['view'];
	if (isset ($_GET['pg'])) $pg = int_filter ($_GET['pg']); else $pg = NULL;
	if (isset ($_GET['stg'])) $stg = int_filter ($_GET['stg']); else $stg = NULL;
	if (isset ($_GET['offset'])) $offset = int_filter ($_GET['offset']); else $offset = NULL;
	ob_start();
	if (!isset($_GET['mod'])) {
		include THEMES . 'normal.php';
	} else if (isset($_GET['mod']) && !empty($_GET['mod']) && !preg_match("/\.\./",$_GET['mod']) && file_exists(MOD . '/'.$_GET['mod'].'/'.$_GET['mod'].'.php')){
		include MOD . '/'.$_GET['mod'].'/'.$_GET['mod'].'.php';	
	} else {
		header("location:index.php");
		exit;		
	}
	
	$content = ob_get_contents();
	ob_end_clean(); 

	ob_start();
	if(isset($_SESSION['level']) AND $_SESSION['level']!='administrator'){
		echo '<h1 class="bg2 rt">Member Menu</h1>';
		echo '<div class="box"><strong>Welcome '.$_SESSION['name'].'</strong>';	
		echo '<div class="border listmenu"><ul><li><a href="logout.html" title="" />Logout</a></li></ul></div>';	
		echo '</div>';
	}
	modul(0);
	$leftside = ob_get_contents();
	ob_end_clean(); 
	
	ob_start();
	modul(1);
	$rightside = ob_get_contents();
	ob_end_clean(); 
	
	if ($_GET['action'] == 'logout') {
		logout ();
	}
	
	if (isset($_COOKIE['statistik']) != 'AuraCMS'){
		$day 	 = date('w', time() + 0);
		stats();
		setcookie('statistik', 'AuraCMS', time()+ 3600);	
	}
	

	
	$style_include_out 	= !isset($style_include) ? '' : implode("",$style_include);
	$script_include_out = !isset($script_include) ? '' : implode("",$script_include);
	$rightside 			= !isset($rightside) ? '' : $rightside;
	$leftside 			= !isset($leftside) ? '' : $leftside;
	$slider 			= !isset($slider) ? '' : $slider;
	
	$define = array ('leftside'    		=> $leftside,
					 'content'     		=> $content,
					 'rightside'  		=> $rightside, 	
					 'slider'     		=> $slider, 
					 'style_include' 	=> $style_include_out,
					 'script_include' 	=> $script_include_out,
					 'meta_title' 		=> get_setting('title'),
					 'meta_description' => get_setting('description'),
					 'meta_keywords' 	=> get_setting('keyword'),
					 'url'     			=> get_setting('url'),
					 'menuatas'     	=> topmenu(),
					 'timer' 			=> $timer->stop()
	                );
					
	if(!isset($_GET['mod'])){
		$tpl = new template (THEMES.'themes.html');
	}else{
		$tpl = new template (THEMES.'themes-1.html');
	}
	if(isset($index_hal)){
		$tpl = new template (THEMES.'themes-2.html');
	}
	$tpl-> define_tag($define);
	$tpl-> cetak();
?>