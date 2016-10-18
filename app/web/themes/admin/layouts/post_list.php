<?php
/* Example of CMS Inlay

*/
/* @var $View Templatable */

$View->includeInlay($identifier, 'post_summary');
$View->includeTemplate($identifier, $model);