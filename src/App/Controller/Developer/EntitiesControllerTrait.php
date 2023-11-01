<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Developer;

use Orpheus\EntityDescriptor\Entity\PermanentEntity;
use Orpheus\EntityDescriptor\Generator\Sql\SqlGenerator;
use Orpheus\EntityDescriptor\Generator\Sql\SqlGeneratorMySql;
use Orpheus\Exception\UserException;
use Orpheus\SqlAdapter\AbstractSqlAdapter;
use Orpheus\SqlAdapter\Exception\SqlException;
use PDO;
use PDOStatement;

trait EntitiesControllerTrait {
	
	const OUTPUT_APPLY = 1;
	const OUTPUT_DISPLAY = 2;
	
	protected AbstractSqlAdapter $adapter;
	
	protected function getEntityListChanges(SqlGenerator $generator, array $entityClasses): array {
		$result = [];
		/** @var class-string<PermanentEntity> $entityClass */
		foreach( $entityClasses as $entityClass ) {
			$query = $generator->getIncrementalChanges($entityClass::getDescriptor(), $entityClass::getSqlAdapter());
			if( $query ) {
				$result[$entityClass] = $query;
			}
		}
		return $result;
	}
	
	protected function getUnknownTables(): array {
		$knownTables = [];
		/** @var class-string<PermanentEntity> $entityClass */
		foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
			$knownTables[$entityClass::getTable()] = 1;
		}
		$unknownTables = [];
		/* @var PDOStatement $statement */
		$statement = $this->adapter->query('SHOW TABLES', AbstractSqlAdapter::QUERY_STATEMENT);
		while( $tableFetch = $statement->fetch(PDO::FETCH_NUM) ) {
			$table = $tableFetch[0];
			if( isset($knownTables[$table]) ) {
				continue;
			}
			$unknownTables[] = $table;
		}
		
		return $unknownTables;
	}
	
	protected function applyChanges(array $queries): void {
		foreach( $queries as $query ) {
			$this->adapter->query(strip_tags($query), AbstractSqlAdapter::PROCESS_EXEC);
		}
	}
	
	protected function applyRemoveUnknownTables(array $unknownTables): void {
		foreach( $unknownTables as $table ) {
			try {
				$this->adapter->query(sprintf('DROP TABLE %s', $this->adapter->escapeIdentifier($table)), AbstractSqlAdapter::PROCESS_EXEC);
			} catch( SqlException $e ) {
				reportError(sprintf('Unable to drop table %s, cause: %s', $table, $e->getMessage()));
			}
		}
	}
	
	protected function calculateRemovableTables(array $unknownTables, ?array $requestedRemove): array {
		return array_intersect($unknownTables, $requestedRemove ?? []);
	}
	
	protected function processGeneration(int $processGeneration, array $queries, array $removableTables): bool {
		$requireEntityValidation = false;
		if( $processGeneration === self::OUTPUT_DISPLAY ) {
			if( !$queries ) {
				reportInfo('errorNoChanges', DOMAIN_SETUP);
			}
			$requireEntityValidation = $removableTables || $queries;
		} else if( $processGeneration === self::OUTPUT_APPLY ) {
			if( !$queries && !$removableTables ) {
				throw new UserException('errorNoChanges', DOMAIN_SETUP);
			}
			if( $queries ) {
				$this->applyChanges($queries);
			}
			if( $removableTables ) {
				$this->applyRemoveUnknownTables($removableTables);
			}
			
			reportSuccess('successSqlApply', DOMAIN_SETUP);
		}
		
		return $requireEntityValidation;
	}
	
	protected function calculateChanges(array $selectedEntities): array {
		$generator = new SqlGeneratorMySql();
		$queries = $this->getEntityListChanges($generator, $selectedEntities);
		$unknownTables = $this->getUnknownTables();
		
		return [$queries, $unknownTables];
	}
	
}
