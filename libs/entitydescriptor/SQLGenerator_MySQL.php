<?php

// MySQL Generator

class SQLGenerator_MySQL {
	public function getColumnInfosFromField($field) {
		$TYPE = EntityDescriptor::getType($field->type);
		$cType = '';
		if( $TYPE instanceof TypeString ) {
			$max = $TYPE instanceof TypePassword ? 128 : $field->args->max;
			if( $max < 256 ) {
				$cType = "VARCHAR({$field->args->max})";
			} else
			if( $max < 65536 ) {
				$cType = "TEXT";
			} else
			if( $max < 16777216 ) {
				$cType = "MEDIUMTEXT";
			} else {
				$cType = "LONGTEXT";
			}
		} else
		if( $TYPE instanceof TypeNumber ) {
			if( !isset($field->args->max) ) {
				text('Issue with '.$field->name.', missing max argument');
				text($field->args);
			}
			$dc			= strlen((int) $field->args->max);
			$unsigned	= $field->args->min >= 0 ? 1 : 0;
			if( !$field->args->decimals ) {
// 				$max	= (int) $field->args->max;// Int max on 32 bits systems is incompatible with SQL
				$max	= $field->args->max;// Treat it as in
// 				debug('$field - '.$field->name.', type='.$field->type.' => '.$max);
				$f			= 1+1*$unsigned;
				if( $max < 128*$f ) {
					$cType	= "TINYINT";
				} else
				if( $max < 32768*$f ) {
					$cType	= "SMALLINT";
				} else
				if( $max < 8388608*$f ) {
					$cType	= "MEDIUMINT";
				} else
				if( $max < 2147483648*$f ) {
					$cType	= "INT";
				} else {
					$cType	= "BIGINT";
				}
				$cType .= '('.strlen($max).')';
					
			} else {
				$dc += $field->args->decimals+1;
				// http://code.rohitink.com/2013/06/12/mysql-integer-float-decimal-data-types-differences/
				if( $dc < 23 && $field->args->decimals < 8 ) {// Approx accurate to 7 decimals
// 				if( $dc < 7 ) {// Approx accurate to 7 decimals
					$cType = "FLOAT";
				} else {// Approx accurate to 15 decimals
					$cType = "DOUBLE";
				}
				$cType .= "({$dc},{$field->args->decimals})";
			}
			if( $unsigned ) {
				$cType	.= ' UNSIGNED';
			}
		} else
		if( $TYPE instanceof TypeDate ) {
			$cType = 'DATE';
		} else
		if( $TYPE instanceof TypeDatetime ) {
			$cType = 'DATETIME';
		} else {
			throw new UserException('Type of '.$field->name.' ('.$TYPE->getName().') not found');
		}
		$r = array('name'=>$field->name, 'type'=>$cType, 'nullable'=>!!$field->nullable);
		$r['autoIncrement'] = $r['primaryKey'] = ($field->name=='id');
		return $r;
	}
	
	public function getColumnDefinition($field, $withPK=true) {
		// 	text('mysqlColumnDefinition()');
		// 	text($field);
		$field = (object) $field;
		return $this->formatHTML_Identifier($field->name).' '.$this->formatHTML_ColumnType($field->type).
			' '.$this->formatHTML_ReservedWord($field->nullable ? 'NULL' : 'NOT NULL').
			(!empty($field->autoIncrement) ? ' '.$this->formatHTML_ReservedWord('AUTO_INCREMENT') : '').(($withPK && !empty($field->primaryKey)) ? ' '.$this->formatHTML_ReservedWord('PRIMARY KEY') : '');
	}
	
	public function getIndexDefinition($index) {
		$fields = '';
		foreach( $index->fields as $field ) {
			$fields .= ($fields ? ', ' : '').$this->formatHTML_Identifier($field);
		}
		return $this->formatHTML_ReservedWord($index->type).(!empty($index->name) ? ' '.$this->formatHTML_Identifier($index->name) : '').' ('.$fields.')';
	}
	
	public function matchEntity(EntityDescriptor $ed) {
		try {
			$columns	= pdo_query('SHOW COLUMNS FROM '.SQLAdapter::doEscapeIdentifier($ed->getName()), PDOFETCHALL);//|PDOERROR_MINOR
			// Fields
			$fields	= $ed->getFields();
			$alter	= '';
			foreach( $columns as $cc ) {
				$cc	= (object) $cc;
				$cf = array( 'name'=>$cc->Field, 'type'=>strtoupper($cc->Type), 'nullable'=>$cc->Null=='YES',
					'primaryKey'=>$cc->Key=='PRI', 'autoIncrement'=>strpos($cc->Extra, 'auto_increment')!==false);
				if( isset($fields[$cf['name']]) ) {
					$f = $this->getColumnInfosFromField($fields[$cf['name']]);
					unset($fields[$cf['name']]);
					// Current definition is different from former
					if( $f != $cf ) {
// 						$alter .= (!empty($alter) ? ", \n" : '')."\t CHANGE COLUMN ".SQLAdapter::doEscapeIdentifier($cf['name']).' '.$this->getColumnDefinition($f, !$cf['primaryKey']);
						$alter .= (!empty($alter) ? ", \n" : '').$this->formatHTML_SubCommand('CHANGE COLUMN').' '.$this->formatHTML_Identifier($cf['name']).' '.$this->getColumnDefinition($f, !$cf['primaryKey']);
					}
				} else {
					// Remove column
// 					$alter .= (!empty($alter) ? ", \n" : '')."\t DROP COLUMN ".SQLAdapter::doEscapeIdentifier($cf['name']);
					$alter .= (!empty($alter) ? ", \n" : '').$this->formatHTML_SubCommand('DROP COLUMN').' '.$this->formatHTML_Identifier($cf['name']);
				}
			}
			foreach( $fields as $f ) {
// 				$alter .= (!empty($alter) ? ", \n" : '')."\t ADD COLUMN ".$this->getColumnDefinition($this->getColumnInfosFromField($f));
				$alter .= (!empty($alter) ? ", \n" : '').$this->formatHTML_SubCommand('ADD COLUMN').' '.$this->getColumnDefinition($this->getColumnInfosFromField($f));
			}
			unset($fields, $f, $cc, $cf, $columns);
			// Indexes
			try {
				$rawIndexes	= pdo_query('SHOW INDEX FROM '.SQLAdapter::doEscapeIdentifier($ed->getName()), PDOFETCHALL);//|PDOERROR_MINOR
				// 			text('Indexes of '.$ed->getName());
				// 			text($rawIndexes);
				$indexes	= $ed->getIndexes();
				// Current indexes
				$cIndexes	= array();
				foreach( $rawIndexes as $ci ) {
					$ci = (object) $ci;
					if( $ci->Key_name==='PRIMARY' ) { continue; }
					if( !isset($cIndexes[$ci->Key_name]) ) {
						$type		= 'INDEX';
						if( !$ci->Non_unique ) {
							$type	= 'UNIQUE';
						} else
						if( $ci->Index_type==='FULLTEXT' ) {
							$type	= 'FULLTEXT';
						}
						$cIndexes[$ci->Key_name] = (object) array('name'=>$ci->Key_name, 'type'=>$type, 'fields'=>array());
					}
					$cIndexes[$ci->Key_name]->fields[] = $ci->Column_name;
				}
				// Match new to current ones
				foreach($cIndexes as $ci) {
					$found = 0;
					foreach( $indexes as $ii => $index ) {
						if( $index->type===$ci->type && $index->fields==$ci->fields ) {
							unset($indexes[$ii]);
							$found = 1;
							break;
						}
					}
					if( !$found ) {
						// Remove index
						$alter .= (!empty($alter) ? ", \n" : '').$this->formatHTML_SubCommand('DROP INDEX').' '.$this->formatHTML_Identifier($ci->name);
// 						$alter .= (!empty($alter) ? ", \n" : '')."\t DROP INDEX ".SQLAdapter::doEscapeIdentifier($ci->name);
					}
				}
				foreach( $indexes as $index ) {
					$alter .= (!empty($alter) ? ", \n" : '').$this->formatHTML_SubCommand('ADD').' '.$this->getIndexDefinition($index);
// 					$alter .= (!empty($alter) ? ", \n" : '')."\t ADD ".$this->getIndexDefinition($index);
				}
			} catch( SQLException $e ) {
				return null;
			}
			if( empty($alter) ) { return null; }
			return '<div class="table-operation table-alter">'.$this->formatHTML_Command('ALTER TABLE').' '.$this->formatHTML_Identifier($ed->getName())."\n{$alter};</div>";
		} catch( SQLException $e ) {
// 			throw $e;
			return $this->getCreate($ed);
		}
	}
	
	public function getCreate(EntityDescriptor $ed) {
		// 	text('mysqlCreate()');
		// 	text($ed);
		$createDefinition = '';
		foreach( $ed->getFields() as $field ) {
			$createDefinition .= (!empty($createDefinition) ? ", \n" : '')."\t".$this->getColumnDefinition($this->getColumnInfosFromField($field));
		}
		foreach( $ed->getIndexes() as $index ) {
			$createDefinition .= ", \n\t".$this->getIndexDefinition($index);
		}
		if( empty($createDefinition) ) {
			throw new UserException('No columns');
// 			return null;
		}
		return '
<div class="table-operation table-create">'.$this->formatHTML_Command('CREATE TABLE IF NOT EXISTS').' '.$this->formatHTML_Identifier($ed->getName()).' (
'.$createDefinition.'
) '.$this->formatHTML_ReservedWord('ENGINE=MYISAM').' '.$this->formatHTML_ReservedWord('CHARACTER SET').' '.$this->formatHTML_Identifier('utf8').';</div>';
	}
	
	protected function formatHTML_Command($string) {
		return $this->formatHTML_ReservedWord($string, 'query_command');
	}
	
	protected function formatHTML_SubCommand($string) {
		return $this->formatHTML_ReservedWord("\t ".$string, 'query_subCommand');
	}
	
	protected function formatHTML_ColumnType($string) {
		return $this->formatHTML_ReservedWord($string, 'query_columnType');
	}
	
	protected function formatHTML_ReservedWord($string, $class='') {
		return $this->formatHTML_InlineBlock($string, 'query_reservedWord '.$class);
	}
	
	protected function formatHTML_Identifier($string) {
		return $this->formatHTML_InlineBlock(SQLAdapter::doEscapeIdentifier($string), 'query_identifier');
	}
	
	protected function formatHTML_InlineBlock($string, $class) {
		return '<div class="ib '.$class.'">'.$string.'</div>';
	}
}