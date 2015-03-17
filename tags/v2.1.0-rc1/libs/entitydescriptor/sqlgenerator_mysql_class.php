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
				text('Issue with '.$fName);
				text($field->args);
			}
			$dc			= strlen((int) $field->args->max);
			$unsigned	= $field->args->min >= 0 ? 1 : 0;
			if( !$field->args->decimals ) {
				$max		= (int) $field->args->max;
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
			throw new UserException('Type of '.$fName.' ('.$TYPE->getName().') not found');
		}
		$r = array('name'=>$field->name, 'type'=>$cType, 'nullable'=>!!$field->nullable);
		$r['autoIncrement'] = $r['primaryKey'] = ($field->name=='id');
		return $r;
	}
	
	public function getColumnDefinition($field, $withPK=true) {
		// 	text('mysqlColumnDefinition()');
		// 	text($field);
		$field = (object) $field;
		return SQLAdapter::doEscapeIdentifier($field->name).' '.$field->type.
			($field->nullable ? ' NULL' : ' NOT NULL').
			(!empty($field->autoIncrement) ? ' AUTO_INCREMENT' : '').(($withPK && !empty($field->primaryKey)) ? ' PRIMARY KEY' : '');
	}
	
	public function getIndexDefinition($index) {
		return $index->type.(!empty($index->name) ? ' '.SQLAdapter::doEscapeIdentifier($index->name) : '').' (`'.implode('`, `', $index->fields).'`)';
	}
	
	public function matchEntity($ed) {
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
						$alter .= (!empty($alter) ? ", \n" : '')."\t CHANGE COLUMN ".SQLAdapter::doEscapeIdentifier($cf['name']).' '.$this->getColumnDefinition($f, !$cf['primaryKey']);
					}
				} else {
					// Remove column
					$alter .= (!empty($alter) ? ", \n" : '')."\t DROP COLUMN ".SQLAdapter::doEscapeIdentifier($cf['name']);
				}
			}
			foreach( $fields as $f ) {
				$alter .= (!empty($alter) ? ", \n" : '')."\t ADD COLUMN ".$this->getColumnDefinition($this->getColumnInfosFromField($f));
			}
			unset($fields, $f, $cc, $cf, $columns);
			// Indexes
			try {
				$rawIndexes	= pdo_query('SHOW INDEX FROM '.SQLAdapter::doEscapeIdentifier($ed->getName()), PDOFETCHALL);//|PDOERROR_MINOR
				// 			text('Indexes of '.$ed->getName());
				// 			text($rawIndexes);
				$indexes	= $ed->getIndexes();
				$cIndexes	= array();
				foreach( $rawIndexes as $ci ) {
					$ci = (object) $ci;
					if( $ci->Key_name=='PRIMARY' ) { continue; }
					if( !isset($cIndexes[$ci->Key_name]) ) {
						$type		= 'INDEX';
						if( !$ci->Non_unique ) {
							$type	= 'UNIQUE';
						} else
						if( $ci->Index_type=='FULLTEXT' ) {
							$type	= 'FULLTEXT';
						}
						$cIndexes[$ci->Key_name] = (object) array('name'=>$ci->Key_name, 'type'=>$type, 'fields'=>array());
					}
					$cIndexes[$ci->Key_name]->fields[] = $ci->Column_name;
				}
				foreach($cIndexes as $ci) {
					$found = 0;
					foreach( $indexes as $ii => $i ) {
						if( $i->type==$ci->type && $i->fields==$ci->fields ) {
							unset($indexes[$ii]);
							$found = 1;
							break;
						}
					}
					if( !$found ) {
						// Remove index
						$alter .= (!empty($alter) ? ", \n" : '')."\t DROP INDEX ".SQLAdapter::doEscapeIdentifier($ci->name);
					}
				}
				foreach( $indexes as $i ) {
					$alter .= (!empty($alter) ? ", \n" : '')."\t ADD ".$this->getIndexDefinition($i);
				}
			} catch( SQLException $e ) {
				return null;
			}
			if( empty($alter) ) { return null; }
			return '<div class="table-operation table-alter">ALTER TABLE <div class="table-name">'.SQLAdapter::doEscapeIdentifier($ed->getName())."</div>\n{$alter};</div>";
		} catch( SQLException $e ) {
			return $this->getCreate($ed);
		}
	}
	
	public function getCreate($ed) {
		// 	text('mysqlCreate()');
		// 	text($ed);
		$columns = '';
		foreach( $ed->getFields() as $field ) {
			$columns .= (!empty($columns) ? ", \n" : '')."\t".$this->getColumnDefinition($this->getColumnInfosFromField($field));
		}
		if( empty($columns) ) {
			throw new UserException('No columns');
			return null;
		}
		return '
<div class="table-operation table-create">CREATE TABLE IF NOT EXISTS <div class="table-name">'.SQLAdapter::doEscapeIdentifier($ed->getName()).'</div> (
'.$columns.'
) ENGINE=MYISAM CHARACTER SET utf8;</div>';
	}
}