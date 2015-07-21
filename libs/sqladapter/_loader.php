<?php
/** SQL Adapter Library
 * 
 * SQL Adapter library brings sql adapters for DBMS
 */

addAutoload('SQLAdapter',					'sqladapter/SQLAdapter');
addAutoload('SQLAdapter_MySQL',				'sqladapter/SQLAdapter_MySQL');
addAutoload('SQLAdapter_MSSQL',				'sqladapter/SQLAdapter_MSSQL');
addAutoload('SQLAdapter_PgSQL',				'sqladapter/SQLAdapter_PgSQL');

require_once '_pdo.php';
// require_once __DIR__.'/_pdo.php';
