<?php

if (!defined('IN_SPYOGAME'))
{
  exit('Hacking attempt');
}

$mod_folder = 'hof';
$mod_name = 'hof';
define('TABLE_HOF_PROD', $table_prefix . 'hof_prod');

//Update Table
global $db;

$query = "ALTER TABLE `" . TABLE_HOF_PROD . "` ADD `t` DECIMAL(15,6) UNSIGNED NOT NULL AFTER `d`";

$db->sql_query($query);

update_mod($mod_folder, $mod_name);

