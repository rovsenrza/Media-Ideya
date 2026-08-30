<?php
/**
 * Stable public contact endpoint for web servers that do not process DLE's
 * .htaccess rules (for example the local MAMP Nginx profile).
 */

$siteBase = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$frontController = ($siteBase ?: '') . '/index.php';

$_SERVER['SCRIPT_NAME'] = $frontController;
$_SERVER['PHP_SELF'] = $frontController;
$_SERVER['REQUEST_URI'] = $frontController . '?do=feedback';
$_SERVER['QUERY_STRING'] = 'do=feedback';
$_GET['do'] = 'feedback';
$_REQUEST['do'] = 'feedback';

require dirname(__DIR__) . '/index.php';
