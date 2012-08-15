<?php
/*! \file
 * All the basic hooks for the framework.
 * 
 * Some predefined hooks are specified in this file, it serves for the orpheus' core.\n
 * Don't delete existing hooks or your website won't work correctly.\n
 * You can add your own hooks here but we advise you to use your own library to do it.\n
 */

Hook::create('startSession');
Hook::create('checkModule');
Hook::create('runModule');
Hook::create('showRendering');