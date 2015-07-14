<?php
/**
 * @page lib_rendering Rendering Library
 * 
 * @section intro_sec Introduction
 *
 * The rendering library is designed to allow the rendering of any types.\n
 * Each type should have a class as HTMLRendering.\n
 * HTMLRendering is for the rendering of HTML, using a theme.\n
 * RawRendering is for the rendering of an unspecified type, it just displays the module's result without treatment.\n
 * @n
 * This package is required.\n
 * 
 * @section install_sec Installation
 * 
 * Copy all files from the archive into your website root directory.\n
 * Edit the configs/engine.ini file with your rendering setting, we recommend:\n
 * default_rendering = "HTMLRendering"\n
 * Modify default_rendering only if you know what you are doing.
 */