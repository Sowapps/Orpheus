<?php
/*!
 * \page libraries Libraries
 * 
 * \section intro_sec Introduction
 *
 * The framework manages libraries to seperate common sources from other features.\n
 * You can use it to import plugins and extensions for your application.\n
 * 
 * \section import_sec Importing
 * 
 * There is several ways to import libraries, depending on the needs and the way you develop your application.\n
 * 
 * \subsection boot_sub At boot loading
 * 
 * During the boot loading, it includes files from configs and libs directories.\n
 * If a file start with the "_" character, then the file is directly imported.\n
 * If the file is a folder, it tries to import he folder, the inclusion is called recursively.\n
 * In a package with some required classes, we conventionnaly use a _loader.php file to add customed libraries to the autoloads mapping list.\n
 * 
 * 
 * \subsection autoloads_sub With autoloads
 * 
 * The framework uses PHP spl_autoload_register() function to load on request the required libraries.\n
 * It searches the file containing your class from all possibilities, in the priority order:\n
 * \li Paths specified with addAutoload() function.
 * \li Paths loaded from the "autoload" config file (configs/autoload.ini).
 * \li For MyClass, path as libs/myclass_class.php.
 * \li For MyClass, path as libs/myclass/myclass_class.php.
 * \li For Package_MyClass, path as libs/package/myclass_class.php.
 * 
 * A library can declare its own autoload function, it can't affect the behavior of this function but the order of the different functions' calls can.
 * 
 * 
 * \subsection using_sub The "using" way
 * 
 * The using() function is done to import classes as packages, as you can do with Java.\n
 * e.g using('orpheus.examples.myclass') (Import "orpheus/examples/myclass_class.php")\n
 * or using('orpheus.examples.*') (Import all classes from "orpheus/examples/")\n
 * 
 * \section location_sec Location
 * 
 * All libraries are located in the "libs" folder.\n
 * You can change the name and the locaiton of this folder at your own risk.\n
 * To do it, you only should to edit the pathOf(LIBSDIR constant in the constant file (default location "configs/constants.php").\n
 */