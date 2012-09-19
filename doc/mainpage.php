<?php
/*!
 * \mainpage Documentation
 * 
 * \warning For the moment, The Orpheus demo website and the Orpheus framework use the same repository but it's unsecure and not convenient.\n
 * This is why it will be changed soon and they will be separated.\n
 * 
 * \section intro_sec Introduction
 *
 * Orpheus is a free, open source, flexible, and smart framework meant to be embedded in web applications.\n
 * The purpose is to provide an easy-to-use library that is powerful, but that isn't weighed down by a large amount of rarely-used features.\n
 * Development of Orpheus begun in January, 2012, with the first public release on july 28th, 2012, with only the most basic of functionalities.\n
 * The author is still dedicated to the continued improvement and growth of this framework.\n
 * \n
 * The framewok is designed in a way to seperate job and technical instructions, this is the Separation of concerns.\n
 * Libraries do the technical part, Objects link Model and Controller, Modules are the Main controller (The job) and the template are the view.\n
 * Only the core package is necessary, you can use the MVC design pattern if you want, all needed tools are provided, but its use is not mandatory.\n
 * \n
 * The official website for the framework is http://orpheus.cartman34.fr/.\n
 *
 * \section install_sec Installation
 *
 * \subsection requirements Step 0: Requirements
 * 
 * This framework may be used for web applications, so it requires a web server with PHP 5.3+
 *
 * \subsection download Step 1: Download
 * 
 * Download The lastest release from our website:\n
 * http://orpheus.cartman34.fr/downloads/latest/\n
 * (Take full package for all files)
 * 
 * \subsection embed Step 2: Embed
 * 
 * Unpack the archive and integrate it into your web application (or create it first).
 *
 * \section start Getting started
 * 
 * Configure constants: \ref constants\n
 * Install the libraries you need and configure it.\n
 * Don't forget to configure your database, menus and user rights.\n
 * To develop your own features, put it in your root folder "libs/_src/".\n
 *
 * \section basic_lib Basic libraries
 * 
 * Some libraries are required or made for easier use of the framework.\n
 * \li \ref lib_config
 * \li \ref lib_core
 * \li \ref lib_hooks
 * \li \ref lib_initernationalization
 * \li \ref lib_publisher
 * \li \ref lib_rendering
 * \li \ref lib_route
 * \li \ref lib_sqlmapper
 */