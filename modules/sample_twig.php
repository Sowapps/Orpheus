<?php
//TwigRendering::display($Module);
Config::set('default_rendering', 'TwigRendering');
log_debug('default_rendering config setted to TwigRendering');
log_debug('checking...'.Config::get('default_rendering'));