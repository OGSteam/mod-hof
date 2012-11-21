<?php
  // Besoin pour plus tard
  $couleur  = array('aqua', 'black', 'blue', 'fuchsia', 'green', 'grey', 'lime', 'maroon', 'navy', 'olive', 'purple', 'red', 'silver', 'teal', 'white', 'yellow');
  
  /* On sauvegarde les modifications */  
  
  if (!empty($_POST))
  {    
    /* Securisation (et conversion) des donnees */
    /* General */
    
    $vous = (isset($_POST['vous']) && $_POST['vous'] == 'on') ? 1 : 0;
    $diff = (isset($_POST['diff']) && $_POST['diff'] == 'on') ? 1 : 0;
    
    /* Production miniere */
    
    $uni50   = (isset($_POST['uni50']) && $_POST['uni50'] == 'on') ? 1 : 0;
    $heure   = (isset($_POST['heure']) && $_POST['heure'] == 'on') ? 1 : 0;
    $jour    = (isset($_POST['jour']) && $_POST['jour'] == 'on') ? 1 : 0;
    $semaine = (isset($_POST['semaine']) && $_POST['semaine'] == 'on') ? 1 : 0;
    
    /* Export BBCode */
    
    $center         = (isset($_POST['center']) && $_POST['center'] == 'on') ? 1 : 0;
    $gras           = (isset($_POST['gras']) && $_POST['gras'] == 'on') ? 1 : 0;
    $souligne       = (isset($_POST['souligne']) && $_POST['souligne'] == 'on') ? 1 : 0;
    $italic         = (isset($_POST['italic']) && $_POST['italic'] == 'on') ? 1 : 0;
    
    $couleurCat     = mysql_real_escape_string (htmlentities ($_POST['couleurCat'], ENT_QUOTES));
    $tailleCat      = (isset($_POST['tailleCat']) && intval($_POST['tailleCat']) > 7) ? intval($_POST['tailleCat']) : 16;
    
    $couleurLabel   = mysql_real_escape_string (htmlentities ($_POST['couleurLabel'], ENT_QUOTES));
    $tailleLabel    = (isset($_POST['tailleLabel']) && intval($_POST['tailleLabel']) > 7) ? intval($_POST['tailleLabel']) : 14;
    
    $couleurNiv     = mysql_real_escape_string (htmlentities ($_POST['couleurNiv'], ENT_QUOTES));
    $tailleNiv      = (isset($_POST['tailleNiv']) && intval($_POST['tailleNiv']) > 7) ? intval($_POST['tailleNiv']) : 16;
    
    $couleurPseudos = mysql_real_escape_string (htmlentities ($_POST['couleurPseudos'], ENT_QUOTES));
    $taillePseudos  = (isset($_POST['taillePseudos']) && intval($_POST['taillePseudos']) > 7) ? intval($_POST['taillePseudos']) : 14;
    
    // Si la colonne "Vous" n'est pas affichee, il n'y a pas de difference de niveau a faire
    if (!$vous)
      $diff = 0;
    
    /* Insertion dans la BDD */
    
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $vous .'\' WHERE parameter=\'vous\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $diff .'\' WHERE parameter=\'diff\'');
    
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $uni50 .'\' WHERE parameter=\'uni50\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $heure .'\' WHERE parameter=\'prod_heure\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $jour .'\' WHERE parameter=\'prod_jour\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $semaine .'\' WHERE parameter=\'prod_semaine\'');
    
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $center .'\' WHERE parameter=\'center\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $couleurCat .'\' WHERE parameter=\'couleurCat\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $tailleCat .'\' WHERE parameter=\'tailleCat\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $gras .'\' WHERE parameter=\'gras\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $souligne .'\' WHERE parameter=\'souligne\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $italic .'\' WHERE parameter=\'italic\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $couleurLabel .'\' WHERE parameter=\'couleurLabel\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $tailleLabel .'\' WHERE parameter=\'tailleLabel\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $couleurNiv .'\' WHERE parameter=\'couleurNiv\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $tailleNiv .'\' WHERE parameter=\'tailleNiv\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $couleurPseudos .'\' WHERE parameter=\'couleurPseudos\'');
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $taillePseudos .'\' WHERE parameter=\'taillePseudos\'');
    
    // On verifie que le nombre de personne affichees entre par l'admin n'est pas supperieur au nombre de personne presente dans la BDD
    
    $select_nbEntrees = mysql_query('SELECT COUNT(DISTINCT user_id) AS nbRM FROM '. TABLE_USER_BUILDING .'');
    $nbEntrees = mysql_fetch_array($select_nbEntrees);
    
    if (isset($_POST['nbRM']))
    {
      $nbRM = intval($_POST['nbRM']);
      
      if ($nbRM > $nbEntrees['nbRM'] OR $nbRM == 0)
        $nbRM = $nbEntrees['nbRM'];
    }
    else
      $nbRM = $nbEntrees['nbRM'];
    
    mysql_query('UPDATE '. TABLE_HOF_CONFIG .' SET value=\''. $nbRM .'\' WHERE parameter=\'nb_recordsMen\'');
    
    /* Reset */
    
    $defaultOption = (isset($_POST['defaultOption']) && $_POST['defaultOption'] == 'on') ? 1 : 0;
    $resetRecords = (isset($_POST['resetRecords']) && $_POST['resetRecords'] == 'on') ? 1 : 0;
    
    if ($defaultOption)
      fillTableConfig();
    
    if ($resetRecords)
    {
      // Vidage des tables
      fillTableRecords($nameBatiment, $nameLabo, $nameFlotte, $nameDefense);
      mysql_query('TRUNCATE TABLE '. TABLE_HOF_PROD .'');
    }
    
    echo '<p class=\'msgOK\'>Changement effectués !</p>';
  }
  
  /* On recupere les valeures existante */
  
  $select_config = mysql_query('SELECT * FROM '. TABLE_HOF_CONFIG .'');
  $settings = array();
  
  while ($config = mysql_fetch_array($select_config))
  {
    $settings[$config['parameter']] = $config['value'];
  }
?>
  
<form action='index.php?action=hof&page=admin' method='post' class='admin'>

  <!-- General -->
  
  
  <table class='table_hof' style='width : 500px;'>
    <tr class='tr_admin'>
      <th colspan='2'>General</th>
    </tr>
    
    <tr>
      <td style='width : 50%;'>Ajout de la colonne "Vous"</td>
      <td><input type='checkbox' name='vous' <?php checked ($settings['vous']); ?> /></td>
    </tr>
    <tr>
      <td>Afficher la différence entre "Max" et "Vous"</td>
      <td><input type='checkbox' name='diff' <?php checked ($settings['diff']); ?> /></td>
    </tr>
  </table>
  
  <!-- Production Miniere -->
  
  <table class='table_hof' style='width : 500px;'>
    <tr class='tr_admin'>
      <th colspan='2'>Production Minière</th>
    </tr>
    
    <tr>
      <td style='width : 50%;'>Univers 50 (Vitesse * 2)</td>
      <td><input type='checkbox' name='uni50' <?php checked ($settings['uni50']); ?> /></td>
    </tr>
    <tr>
      <td>Production</td>
      <td>
        <input type='checkbox' name='heure' <?php checked ($settings['prod_heure']); ?> /><label for='heure'> Par heure</label>
        <input type='checkbox' name='jour' <?php checked ($settings['prod_jour']); ?> /><label for='jour'> Par jour</label>
        <input type='checkbox' name='semaine' <?php checked ($settings['prod_semaine']); ?> /><label for='semaine'> Par semaine</label>
      </td>
    </tr>
    <tr>
      <td>Nombre de personne affichées<br />(0 => tout le monde)</td>
      <td><input type='text' name='nbRM' size='3' value="<?php echo $settings['nb_recordsMen']; ?>" /></td>
    </tr>
  </table>
  
  <!-- Export BBCode -->
  
  <table class='table_hof' style='width : 500px;'>
    <tr class='tr_admin'>
      <th colspan='2'>Export BBCode</th>
    </tr>
    
    <!-- Categories -->
    
    <tr>
      <td style='width : 50%;'>Centrer le tout</td>
      <td><input type='checkbox' name='center' <?php checked ($settings['center']); ?> /></td>
    </tr>
    <tr>
      <td>Decoration des catégories</td>
      <td>
        <input type='checkbox' name='gras' <?php checked ($settings['gras']); ?> /><label for='gras'> Gras</label>
        <input type='checkbox' name='souligne' <?php checked ($settings['souligne']); ?> /><label for='jour'> Souligne</label>
        <input type='checkbox' name='italic' <?php checked ($settings['italic']); ?> /><label for='semaine'> Italic</label>
      </td>
    </tr>
    <tr>
      <td>Couleur des catégories</td>
      <td>
        <select name='couleurCat'>
          <?php
            foreach ($couleur as $name)
            {
              echo '<option value=\'' . $name . '\' style=\'color : ' . $name . '; text-transform : capitalize;\'';
              selected ($name, $settings['couleurCat']);
              echo '>' . $name . '</option>' . "\n\t\t\t\t\t";
            }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Taille des catégories (en pixel)</td>
      <td><input type='text' name='tailleCat' size='2' value="<?php echo $settings['tailleCat']; ?>" /></td>
    </tr>
    
    <!-- Label -->
    
    <tr>
      <td>Couleur des labels</td>
      <td>
        <select name='couleurLabel'>
          <?php
            foreach ($couleur as $name)
            {
              echo '<option value=\'' . $name . '\' style=\'color : ' . $name . '; text-transform : capitalize;\'';
              selected ($name, $settings['couleurLabel']);
              echo '>' . $name . '</option>' . "\n\t\t\t\t\t";
            }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Taille des labels (en pixel)</td>
      <td><input type='text' name='tailleLabel' size='2' value="<?php echo $settings['tailleLabel']; ?>" /></td>
    </tr>
    
    <!-- Niveau Max -->
    
    <tr>
      <td>Couleur des niveaux</td>
      <td>
        <select name='couleurNiv'>
          <?php
            foreach ($couleur as $name)
            {
              echo '<option value=\'' . $name . '\' style=\'color : ' . $name . '; text-transform : capitalize;\'';
              selected ($name, $settings['couleurNiv']);
              echo '>' . $name . '</option>' . "\n\t\t\t\t\t";
            }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Taille des niveaux (en pixel)</td>
      <td><input type='text' name='tailleNiv' size='2' value="<?php echo $settings['tailleNiv']; ?>" /></td>
    </tr>
    
    <!-- Pseudos -->
    
    <tr>
      <td>Couleur des pseudos</td>
      <td>
        <select name='couleurPseudos'>
          <?php
            foreach ($couleur as $name)
            {
              echo '<option value=\'' . $name . '\' style=\'color : ' . $name . '; text-transform : capitalize;\'';
              selected ($name, $settings['couleurPseudos']);
              echo '>' . $name . '</option>' . "\n\t\t\t\t\t";
            }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Taille des pseudos (en pixel)</td>
      <td><input type='text' name='taillePseudos' size='2' value="<?php echo $settings['taillePseudos']; ?>" /></td>
    </tr>    
  </table>
  
  <!-- Reset -->
  
  <table class='table_hof' style='width : 500px;'>
    <tr class='tr_admin'>
      <th colspan='2'>Reset</th>
    </tr>
    
    <tr>
      <td style='width : 50%;'>Remettre les options par défaut</td>
      <td><input type='checkbox' name='defaultOption' /></td>
    </tr>
    <tr>
      <td>Raffraichir les records</td>
      <td><input type='checkbox' name='resetRecords' /></td>
    </tr>
  </table>
  
  <p>
    <input type='submit' value='Sauvegarder' />
    <input type='hidden' value='1' name='ouvert' />
  </p>
</form>
