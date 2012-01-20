<?php
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL = 'page_structure';
	
	public static $theme = 'default';
	
	public static $themesPath = 'themes/';//Static reject DS
	public static $cssPath = 'css/';//Static reject DS
	public static $modelsPath = '';//Static reject DS
	
	public abstract static function render($env, $model=null) {
		if( !isset($model) ) {
			throw new Exception("Invalid Rendering Model");
		}
		extract($env);
		include static::getModelsPath().$model;
	}
	
	public static function getModelsPath() {
		return static::$themesPath.static::$theme.DS.static::$modelsPath;
	}
	
	public static function getCSSPath() {
		return static::$themesPath.static::$theme.DS.static::$cssPath;
	}
}