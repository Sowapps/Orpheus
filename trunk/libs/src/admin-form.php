<?php

/**
 * @param AbstractProject $project
 * @param int $current
 */
function _adm_htmlFormTimeline($project, $current) {
	// 	if( $project->step <= PROJECT_STEP_DOCUMENTS ) { return; }
	// 	text("_adm_htmlFormTimeline($project, $current)");
	$steps	= $project->getSteps();
	$menu	= array();
	// 	text($steps);
	foreach( $steps as $step ) {
		$menu[$step]	= $step === PROJECT_STEP_PLAN ? t('step_plan_'.$project->type, Project::getDomain()) : t(Project::$stepsModules[$step]);
	}
	// 	text($menu);
	_htmlFormTimeline($menu, $current, $project->step);
	// 	_htmlFormTimeline(array('Mes sociétés', 'Mon projet', 'Les associés', 'Les partenaires', t('step_plan_'.$project->type, Project::getDomain()), 'Mes documents'), $current-1, $project->step);
}

function _adm_htmlCheckbox($fieldPath, $default=false, $class='', $addAttr='') {
	echo adm_htmlCheckbox($fieldPath, $default, $class, $addAttr);
}
function adm_htmlCheckbox($fieldPath, $default=false, $class='', $addAttr='') {
	if( !is_null($field = getField($fieldPath)) ) {
		$addAttr .= $field->getType()->htmlInputAttr($field->args);
	}
	return htmlCheckBox($fieldPath, $default, $addAttr.' class="checkbox radiobtn_button '.$class.'"');
}

function _adm_htmlCheckboxSwitch($fieldPath, $class='', $addAttr='', $default=false) {
	echo adm_htmlCheckboxSwitch($fieldPath, $class, $addAttr, $default);
}
function adm_htmlCheckboxSwitch($fieldPath, $class='', $addAttr='', $default=false) {
	// 	text('adm_htmlCheckboxSwitch($default) ? '.b($default));
	if( !is_null($field = getField($fieldPath)) ) {
		$addAttr .= $field->getType()->htmlInputAttr($field->args);
	}
	return htmlCheckBox($fieldPath, $default, $addAttr.' class="checkboxswitch '.$class.'"');
}

function _adm_htmlDateInput($fieldPath, $class='', $addAttr='', $default='') {
	echo adm_htmlDateInput($fieldPath, $class, $addAttr, $default);
}
function adm_htmlDateInput($fieldPath, $class='', $addAttr='', $default='') {
	//, 'date'
	return adm_htmlTextInput($fieldPath, $class.' date', 'placeholder="JJ/MM/AAAA" '.$addAttr, $default, 'd');
}

function _adm_htmlMoneyInput($fieldPath, $money='€', $class='', $addAttr='', $default='') {
	echo adm_htmlMoneyInput($fieldPath, $money, $class, $addAttr, $default);
}
function adm_htmlMoneyInput($fieldPath, $money='€', $class='', $addAttr='', $default='') {
	return adm_htmlTextInput($fieldPath, $class, 'data-after="'.$money.'" '.$addAttr, $default);
	// 	echo adm_htmlTextInput($fieldPath, $class, 'placeholder="€" '.$addAttr, $default);
}

function _adm_htmlPassword($fieldPath, $addAttr='') {
	echo htmlPassword($fieldPath, $addAttr);
}

function _adm_htmlTextInput($fieldPath, $class='', $addAttr='', $default='', $type='text') {
	echo adm_htmlTextInput($fieldPath, $class, $addAttr, $default, null, $type);
}
function adm_htmlTextInput($fieldPath, $class='', $addAttr='', $default='', $formatter=null, $type='text') {
	if( !is_null($field = getField($fieldPath)) ) {
		$addAttr .= $field->getType()->htmlInputAttr($field->args);
	}
	return htmlText($fieldPath, $default, $addAttr.' class="form-control '.$class.'"', $formatter, $type);
}

function _adm_htmlInput($fieldPath, $class='', $attr=array(), $default=null, $refClass=null) {
	echo adm_htmlInput($fieldPath, $class, $attr, $default, $refClass);
}
function adm_htmlInput($fieldPath, $class='', $attr=array(), $default=null, $refClass=null) {
	// 	text("$fieldPath, $class, $addAttr, $default");
	$field	= getField($fieldPath, $refClass);
	if( $field === NULL ) {
		throw new Exception('Unable to extract field from field path');
	}
	// 	text("$fieldPath, $class, $addAttr, $default");
	return fieldInput($field, $fieldPath, $class, $attr, $default);
}
/**
 * @param FieldDescriptor $field
 * @param string $fieldPath
 * @param string $class
 * @param string $attr
 * @param string $default
 * @return string
 */
// function fieldInput($field, $fieldPath, $class='', $attr=array(), $default=null) {
// 	$Attr	= array('type'=>'text');
// 	$Attr	= array_merge($Attr, $field->getHTMLInputAttr());
// 	if( is_array($attr) ) {
// 		$Attr	= array_merge($Attr, $attr);
// 	}
// 	$Attr['name']	= apath_html($fieldPath);
// 	fillInputValue($value, $fieldPath, $default);
// 	if( $value !== NULL && $value !== '' ) {
// 		$Attr['value']	= $value;
// 	}
// 	if( !empty($class) ) {
// 		$Attr['class']	= $class;
// 	}
// 	$attrList	= is_string($attr) ? $attr : '';
// 	foreach( $Attr as $k => $v ) {
// 		$attrList	.= ' '.$k.'="'.$v.'"';
// 	}
// 	return '<input '.$attrList.'/>';
// }

function _adm_htmlTextArea($fieldPath, $class='', $addAttr='', $default='') {
	echo adm_htmlTextArea($fieldPath, $class, $addAttr, $default);
}
function adm_htmlTextArea($fieldPath, $class='', $addAttr='', $default='') {
	if( ($field = getField($fieldPath)) !== NULL ) {
		$addAttr .= $field->getType()->htmlInputAttr($field->args);
	}
	return htmlTextArea($fieldPath, $default, $addAttr.' class="form-control '.$class.'"');
}

function _adm_htmlRadioBtn($fieldPath, $elValue, $default='', $addAttr='') {
	echo adm_htmlRadioBtn($fieldPath, $elValue, $default, $addAttr);
}
function adm_htmlRadioBtn($fieldPath, $elValue, $default='', $addAttr='') {
	return htmlRadio($fieldPath, $elValue, $default, $addAttr);
}