<?php

$debug = 0;

if (isset($_GET['tri']) && $_GET['tri'] == 'm')
{
  $tri = 'm';
}
else if (isset($_GET['tri']) && $_GET['tri'] == 'c')
{
  $tri = 'c';
}
else if (isset($_GET['tri']) && $_GET['tri'] == 'd')
{
  $tri = 'd';
}
else
{
  $tri = 't';
}

$select_userIDs = $db->sql_query('SELECT DISTINCT user_id AS user_id FROM '. TABLE_USER_BUILDING);

while ($userIDs = $db->sql_fetch_assoc($select_userIDs))
{
  /* Reinitialisation de la production */
  
  $prodMetal = 0;
  $prodCristal = 0;
  $prodDeut = 0;
  
  $select_users = $db->sql_query(
    'SELECT
      user_id,
      planet_id,
      temperature_min, temperature_max,
      Sat,
      M, C, D, CES, CEF,
      Sat_percentage,
      M_percentage, C_percentage, D_percentage, CES_percentage, CEF_percentage
     FROM
     '. TABLE_USER_BUILDING .'
     WHERE
      user_id = \''. $userIDs['user_id'] .'\'');
  
  /* Recuperation du pseudo du joueur */
  
  $select_pseudo = $db->sql_query('SELECT user_name FROM '. TABLE_USER .' WHERE user_id=\''. $userIDs['user_id'] .'\'');
  $pseudo = $db->sql_fetch_assoc($select_pseudo);
  $pseudo = $pseudo['user_name'];
  
  /* R�cup�ration de la technologie energie du joueur */
  
  $select_NRJ = $db->sql_query('SELECT NRJ FROM '. TABLE_USER_TECHNOLOGY .' WHERE user_id=\''. $userIDs['user_id'] .'\'');
  $NRJ = $db->sql_fetch_assoc($select_NRJ);
  $NRJ = $NRJ['NRJ'];

  /* Techno plasma pour éviter des valeurs à l'ouest */
  $select_PLASMA = $db->sql_query('SELECT Plasma FROM '. TABLE_USER_TECHNOLOGY .' WHERE user_id=\''. $userIDs['user_id'] .'\'');
  $PLASMA = $db->sql_fetch_assoc($select_PLASMA);
  $PLASMA = $PLASMA['Plasma'];
  
  // Debug ...
  if ($debug)
  {
    echo '<pre style=\'text-align : left; font-size : 12px; border : 3px ridge silver;\'>';
    echo $pseudo .'<br />';
  }
  
  while ($users = $db->sql_fetch_assoc($select_users))
  {
    /* On verifie que ce n'est pas une lune */
    
    if ($users['planet_id'] >= 101 AND $users['planet_id'] <= 200)
    {
      /* ** Facteur de production = Energie produite / Energie n�cessaire ** */
      
      /* Energie produite = CES + CEF + Sat */
      
      $cesProd = ($users['CES_percentage'] / 100) * 20 * $users['CES'] * pow(1.1, $users['CES']);
      $cefProd = ($users['CEF_percentage'] / 100) * 30 * $users['CEF'] * pow((1.05 + $NRJ * 0.01), $users['CEF']);
      $satProd = ($users['Sat_percentage'] / 100) * ceil(($users['temperature_max'] + $users['temperature_min']) / 12 + 80 / 3) * $users['Sat'];

      $prodEnergie = floor($cesProd + $cefProd + $satProd);
      
      /* Energie n�cessaire = Metal + Cristal + Deut */
      
      $metalConso = ceil(($users['M_percentage'] / 100) * 10 * $users['M'] * pow (1.1, $users['M']));
      $cristalConso = ceil(($users['C_percentage'] / 100) * 10 * $users['C'] * pow (1.1, $users['C']));
      $deutConso = ceil(($users['D_percentage'] / 100) * 20 * $users['D'] * pow (1.1, $users['D']));
      
      $consoEnergie = floor($metalConso + $cristalConso + $deutConso);
      
      // Facteur de production
      
      $prodFacteur = $consoEnergie == 0 ? 1 : ($prodEnergie / $consoEnergie);
      
      if ($prodFacteur > 1)
      {
        $prodFacteur = 1;
      }
      
      /* ** Calcul des production horaire ** */
      
      // Consomation de deut par la CEF        
      $cefConso = ($users['CEF_percentage'] / 100) * 10 * $users['CEF'] * pow (1.1, $users['CEF']);
      
      $prodMetal += 30 + $prodFacteur * floor ( ($users['M_percentage'] / 100) * (30 * $users['M'] * pow (1.1, $users['M'])) );
      $prodCristal += 15 + $prodFacteur * floor (($users['C_percentage'] / 100) * (20 * $users['C'] * pow (1.1, $users['C'])));
      $prodDeut += $prodFacteur * floor(($users['D_percentage'] / 100) * (10 * $users['D'] * pow (1.1, $users['D']) * (1.44 - 0.004 * $users['temperature_max']))) - $cefConso;

      //Ajout du bonus plasma
      $prodMetal += ($PLASMA * 1) * floor ( (30 * $users['M'] * pow (1.1, $users['M'])) ) / 100;
      $prodCristal += ($PLASMA * 0.66) * floor ( (20 * $users['C'] * pow (1.1, $users['C'])) ) / 100;
      $prodDeut += ($PLASMA * 0.33) * floor ( (10 * $users['D'] * pow (1.1, $users['D']) * (1.44 - 0.004 * $users['temperature_max'])) ) / 100;
    

      // Debug ...
      if ($debug)
      {
        echo '<br />'. $users['planet_id'] .' :';
        echo "\t".'Energie affichee : '. ($prodEnergie - $consoEnergie) .' / '. $prodEnergie .'<br />';
        echo "\t".'Facteur de prod : '. $prodFacteur .'<br />';
        echo "\t".'Metal : '. $prodMetal .' - '. $metalConso .'<br />';
        echo "\t".'Cristal : '. $prodCristal .' - '. $cristalConso .'<br />';
        echo "\t".'Deut : '. $prodDeut .' - '. $deutConso .'<br />';
      }
    }
  }
  
  // Debug ...
  if ($debug)
  {
    echo '</pre>';
  }
    
  /* On verifie si le joueur existe dans la table TABLE_HOF_PROD */
  
  $select_testPseudo = $db->sql_query('SELECT * FROM '. TABLE_HOF_PROD .' WHERE pseudo=\''. $pseudo .'\'');
  $testPseudo = $db->sql_fetch_assoc($select_testPseudo);

  $totalProd = floor ($prodMetal + $prodCristal + $prodDeut);

  if (!empty($testPseudo['pseudo'])) // Si le joueur existe
  {
    /* On verifie que sa production est superieure a celle deja presente, si oui on met a jour */
    if ($prodMetal > $testPseudo['m'])
      $db->sql_query('UPDATE '. TABLE_HOF_PROD .' SET m=\''. $prodMetal .'\' WHERE pseudo=\''. $pseudo .'\'');
    
    if ($prodCristal > $testPseudo['c'])
      $db->sql_query('UPDATE '. TABLE_HOF_PROD .' SET c=\''. $prodCristal .'\' WHERE pseudo=\''. $pseudo .'\'');
    
    if ($prodDeut > $testPseudo['d'])
      $db->sql_query('UPDATE '. TABLE_HOF_PROD .' SET d=\''. $prodDeut .'\' WHERE pseudo=\''. $pseudo .'\'');

    if ($totalProd > $testPseudo['t'])
      $db->sql_query('UPDATE '. TABLE_HOF_PROD .' SET t=\''. $totalProd .'\' WHERE pseudo=\''. $pseudo .'\'');
  }
  else // Le joueur n'existe pas
  {
    $db->sql_query('INSERT INTO '. TABLE_HOF_PROD .' VALUES (\''. $pseudo .'\', \''. $prodMetal .'\', \''. $prodCristal .'\', \''. $prodDeut .'\', \''. $totalProd .'\')');
  }
}

/* Recuperation de la config du mod */

$select_config = $db->sql_query('SELECT * FROM '. TABLE_HOF_CONFIG);
$settings = array();

while ($config = $db->sql_fetch_assoc($select_config))
{
  $settings[$config['parameter']] = $config['value'];
}

/* Affichage de la production */

echo '<p class=\'warningProd\'>Si votre production vous semble incorrecte soyez sûr que la température, le nombre de satellites solaires et le niveau de vos centrales sont correct.<br>
Aussi, la production de deut d\'infocompte est FAUSSE, elle oublie de retrancher la consommation de vos CEF.</p>';

$facteur = $settings['uni50'] ? 2 : 1;

if ($settings['prod_heure'])
{
  afficherProd('heure', $facteur, $tri, $settings['nb_recordsMen']);
}

if ($settings['prod_jour'])
{
  afficherProd('jour', $facteur * 24, $tri, $settings['nb_recordsMen']);
}

if ($settings['prod_semaine'])
{
  afficherProd('semaine', $facteur * 168, $tri, $settings['nb_recordsMen']);
}

?>
