<?php

if (!defined('IN_SPYOGAME'))
{
  exit('Hacking Attempt');
}

global $db, $table_prefix;

$mod_uninstall_name = 'hof';
$mod_uninstall_table = $table_prefix .'hof_config, '. $table_prefix .'hof_records, '. $table_prefix .'hof_prod';

uninstall_mod($mod_uninstall_name, $mod_uninstall_table);

?>
