<?php
/**
 * SQL Adapter Library
 * 
 * SQL Adapter library brings sql adapters for DBMS
 */

addAutoload('SQLAdapter',					'sqladapter/SQLAdapter');
addAutoload('SQLAdapter_MySQL',				'sqladapter/SQLAdapter_MySQL');
addAutoload('SQLAdapter_MSSQL',				'sqladapter/SQLAdapter_MSSQL');
addAutoload('SQLAdapter_PgSQL',				'sqladapter/SQLAdapter_PgSQL');

addAutoload('SQLRequest',					'sqladapter/SQLRequest');
addAutoload('SQLSelectRequest',				'sqladapter/SQLSelectRequest');

addAutoload('TransactionOperationSet',		'sqladapter/TransactionOperationSet');
addAutoload('TransactionOperation',			'sqladapter/TransactionOperationSet');
addAutoload('CreateTransactionOperation',	'sqladapter/TransactionOperationSet');
addAutoload('UpdateTransactionOperation',	'sqladapter/TransactionOperationSet');
addAutoload('DeleteTransactionOperation',	'sqladapter/TransactionOperationSet');

require_once '_pdo.php';

SQLAdapter::registerAdapter('mysql', 'SQLAdapter_MySQL');
