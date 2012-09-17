<?php
//TwigRendering::display($Module);
Config::set('default_rendering', 'TwigRendering');

log_debug('default_rendering config setted to TwigRendering, now: '.Config::get('default_rendering'));

require LIBSPATH.'_twigrenderer/_loader.php';