<?php

if (!defined('IN_SPYOGAME')) {
  exit('Hacking attempt');
}

global $db, $table_prefix;

define('TABLE_HOF_CONFIG', $table_prefix . 'hof_config');
define('TABLE_HOF_RECORDS', $table_prefix . 'hof_records');
define('TABLE_HOF_PROD', $table_prefix . 'hof_prod');
define('TABLE_FLOTTES', $table_prefix . 'mod_flottes');

if (!install_mod('hof')) {
  echo '<script>alert(\'Une erreur est survenue pendant l\'installation du module "HoF".\')</script>';
}

/* Suppression des table si elles existent deja */
$db->sql_query('DROP TABLE IF EXISTS '. TABLE_HOF_CONFIG);
$db->sql_query('DROP TABLE IF EXISTS '. TABLE_HOF_RECORDS);
$db->sql_query('DROP TABLE IF EXISTS '. TABLE_HOF_PROD);

/* Creation des tables */
$db->sql_query(
  'CREATE TABLE '. TABLE_HOF_PROD .' (
    pseudo VARCHAR(30) NOT NULL,
    m DECIMAL(15,6) UNSIGNED NOT NULL,
    c DECIMAL(15,6) UNSIGNED NOT NULL,
    d DECIMAL(15,6) UNSIGNED NOT NULL,
    t DECIMAL(15,6) UNSIGNED NOT NULL,
    PRIMARY KEY (pseudo)
  ) ENGINE = MYISAM');

$db->sql_query(
  'CREATE TABLE '. TABLE_HOF_CONFIG .' (
    parameter VARCHAR(20) NOT NULL,
    value VARCHAR(40) NOT NULL
  ) ENGINE = MYISAM');

$db->sql_query(
  'CREATE TABLE '. TABLE_HOF_RECORDS .' (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_cat TINYINT UNSIGNED NOT NULL,
    nom TINYTEXT NOT NULL,
    valeur INT UNSIGNED NOT NULL,
    pseudos TEXT NOT NULL,
    PRIMARY KEY (id)
  ) ENGINE = MYISAM');

/* Remplissage des tables */

require_once('mod/hof/pages/functions.php');
require_once('mod/hof/pages/arrays.php');

// Configuration
fillTableConfig();

// Records
fillTableRecords($nameBatiment, $nameLabo, $nameFlotte, $nameDefense);

/* Suppression des defenses et de la flotte d'un utilisateur qui n'existe pas */

// Defense
$select_defense = $db->sql_query('SELECT DISTINCT user_id FROM '. TABLE_USER_DEFENCE);

while ($defense = $db->sql_fetch_assoc($select_defense))
{
  $select_userID = $db->sql_query('SELECT user_id FROM '. TABLE_USER);
  $user_exist = false;

  while ($userID = $db->sql_fetch_assoc($select_userID))
  {
    if ($userID['user_id'] == $defense['user_id'])
    {
      $user_exist = true;
    }
  }

  if (!$user_exist)
  {
    $db->sql_query('DELETE FROM '. TABLE_USER_DEFENCE .' WHERE user_id = \''. $defense['user_id'] .'\'');
  }
}

// Flotte, si le mod flotte existe
$select_mod = $db->sql_query('SELECT title FROM '. TABLE_MOD .' WHERE title = \'Flottes\'');
$mod = $db->sql_fetch_assoc($select_mod);

if (!empty ($mod))
{
  $select_flotte = $db->sql_query('SELECT DISTINCT user_id FROM '. TABLE_FLOTTES) or exit($db->sql_error());

  while ($flotte = $db->sql_fetch_assoc($select_flotte))
  {
    $select_userID= $db->sql_query('SELECT user_id FROM '. TABLE_USER) or exit($db->sql_error());
    $user_exist= false;

    while ($userID = $db->sql_fetch_assoc($select_userID))
    {
      if ($userID['user_id'] == $flotte['user_id'])
      {
        $user_exist = true;
      }
    }

    if (!$user_exist)
    {
      $db->sql_query('DELETE FROM '. TABLE_FLOTTES .' WHERE user_id = \''. $flotte['user_id'] .'\'') or exit($db->sql_error());
    }
  }
}

?>
