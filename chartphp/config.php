<?php
/**
 * Charts 4 PHP
 *
 * @author Shani <support@chartphp.com> - http://www.chartphp.com
 * @version 1.2.0
 * @license: see license.txt included in package
 */
 
// PHP Grid database connection settings
// define("CHARTPHP_DBTYPE","mysqli"); // or mysqli
// define("CHARTPHP_DBHOST","localhost");
// define("CHARTPHP_DBUSER","root");
// define("CHARTPHP_DBPASS","");
// define("CHARTPHP_DBNAME","northwind");

define("CHARTPHP_DBTYPE","pdo");
define("CHARTPHP_DBHOST","sqlite:../../sampledb/Northwind.db");
define("CHARTPHP_DBUSER","");
define("CHARTPHP_DBPASS","");
define("CHARTPHP_DBNAME","");

// Basepath for lib
define("CHARTPHP_LIBPATH",dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR);
