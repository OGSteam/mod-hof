<?php
	/*
		- Fonction qui genere la page production
	*/
	
	function afficherProd($type, $facteur, $tri, $nbRecordsMen)
	{
	global $db;
?>

<table style='width : 60%; text-align : center; font-weight : bold; margin-bottom : 10px;'>
	<tr style='line-height : 20px; vertical-align : center;'>
		<td class='c' style='color : #00F0F0; width : 20%; padding : 3px;'>Production par <?php echo $type; ?></td>
		<td class='c' style='width : 20%; padding : 3px;'><a style='color : <?php if ($tri == 'm') { echo 'lime;'; } else { echo '#00F0F0;'; } ?>' href='index.php?action=hof&amp;page=prod&amp;tri=m'>Métal</td>
		<td class='c' style='width : 20%; padding : 3px;'><a style='color : <?php if ($tri == 'c') { echo 'lime;'; } else { echo '#00F0F0;'; } ?>' href='index.php?action=hof&amp;page=prod&amp;tri=c'>Cristal</td>
		<td class='c' style='width : 20%; padding : 3px;'><a style='color : <?php if ($tri == 'd') { echo 'lime;'; } else { echo '#00F0F0;'; } ?>' href='index.php?action=hof&amp;page=prod&amp;tri=d'>Deutérium</td>
		<td class='c' style='width : 20%; padding : 3px;'><a style='color : <?php if ($tri == 't') { echo 'lime;'; } else { echo '#00F0F0;'; } ?>' href='index.php?action=hof&amp;page=prod&amp;tri=t'>Total</td>
	</tr>
	
	<?php
		/* On affiche les records de production */
		
		$select_records	= $db->sql_query('SELECT * FROM '. TABLE_HOF_PROD .' ORDER BY '. $tri .' DESC LIMIT 0, '. $nbRecordsMen .'') or die(mysql_error());
		
		while ($records	= mysqli_fetch_assoc($select_records))
		{
			$total = $facteur * ($records['m'] + $records['c'] + $records['d']);
	?>
	
	<tr>
		<td style='color : white; background-color : #273234;'><?php echo $records['pseudo']; ?></td>
		<td style='color : red; background-color : #273234;'><?php echo number_format($facteur * $records['m'], 0, ',', ' '); ?></td>
		<td style='color : lightblue; background-color : #273234;'><?php echo number_format($facteur * $records['c'], 0, ',', ' '); ?></td>
		<td style='color : green; background-color : #273234;'><?php echo number_format($facteur * $records['d'], 0, ',', ' '); ?></td>
		<td style='color : green; background-color : #273234;'><?php echo number_format($total, 0, ',', ' '); ?></td>
	</tr>
	
	<?php
		}
	?>

</table>

<?php
	}
	
	
	/*
		- Fonction qui genere les autres page de records
	*/
	
	function createHoF ($category, $label, $name, $icon, $user_id)
	{
	    global $db;

		/* On recupere les valeures existante */
		$select_config	= $db->sql_query('SELECT * FROM ' . TABLE_HOF_CONFIG);
		$settings		= array ();
		
		while ($config	= mysqli_fetch_array($select_config))
		{
			$settings[$config['parameter']] = $config['value'];
		}
		
		/* On prepare le champ de bataille */
		
		$mysqlTable	= array(
			'1' => TABLE_USER_BUILDING,
			'2' => TABLE_USER_TECHNOLOGY,
			'3' => TABLE_FLOTTES,
			'4' => TABLE_USER_DEFENCE,
			'5' => TABLE_USER_BUILDING,
			'6' => TABLE_USER_DEFENCE);
		
		$catTable	= array(
			'1' => 'Bâtiments',
			'2' => 'Laboratoire',
			'3' => 'Flottes',
			'4' => 'Défense',
			'5' => 'Bâtiments',
			'6' => 'Défense');
		
		$items = count($label); // Nombre de batiments, labo, ...
		
		if ($settings['vous'])
		{
			$ext	= 'style=\'width : 35%;\'';
			$milieu	= 'style=\'width : 15%;\'';
		}
		else
		{
			$ext	= 'style=\'width : 42%;\'';
			$milieu	= 'style=\'width : 16%;\'';
		}
?>

<table class='table_hof'>
	<tr class='tr_hof'>
		<th <?php echo $ext . '>' . $catTable[$category]; ?></th>
		<?php
			if ($settings['vous'])
				echo '<th style=\'width : 15%\'>Vous</th>';
		?>
		<th <?php echo $milieu; ?>>Max</th>
		<th>Joueurs</th>
	</tr>
	
	<?php
		for ($i = 0 ; $i < $items ; $i++)
		{
			/* On selectionne la valeur maximum actuel */

			if ($category == 3 OR $category == 5 OR $category == 6)
			{
				$select_max	= $db->sql_query (
					'SELECT SUM(' . $label[$i] . ') AS ' . $label[$i] . ', user_id
					FROM ' . $mysqlTable[$category] . '
					GROUP BY user_id
					ORDER BY ' . $label[$i] . ' DESC') or die (mysqli_error());
				
				if ($settings['vous'])
				{
					$select_nivUser	= $db->sql_query (
						'SELECT SUM(' . $label[$i] . ') AS ' . $label[$i] . '
						FROM ' . $mysqlTable[$category] . '
						WHERE user_id=\'' . $user_id . '\'') or die (mysqli_error());
				}
			}
			else
			{
				$select_max	= $db->sql_query (
					'SELECT ' . $label[$i] . '
					FROM ' . $mysqlTable[$category] . '
					ORDER BY ' . $label[$i] . ' DESC') or die (mysqli_error());
				
				if ($settings['vous'])
				{
					$select_nivUser	= $db->sql_query (
						'SELECT ' . $label[$i] . '
						FROM ' . $mysqlTable[$category] . '
						WHERE user_id=\'' . $user_id . '\'
						ORDER BY ' . $label[$i] . ' DESC') or die (mysqli_error());
				}
			}
			
			if ($settings['vous'])
			{
				$nivUser	= mysqli_fetch_array($select_nivUser);
				$nivUser	= $nivUser[0];
			}
			
			$max		= mysqli_fetch_array($select_max);
			$IdFlottes	= $max; // Besoin pour apres, pseudo des recordsmen de flotte
			
			$valMax		= $max[0]; // Valeur maximum
			
			/* On selectionne la valeur maximum deja enregistrer dans la table */
			
			$select_max	= $db->sql_query (
				'SELECT valeur
				FROM ' . TABLE_HOF_RECORDS . '
				WHERE nom=\'' . $name[$i] . '\'');
			
			$max		= mysqli_fetch_array ($select_max);
			$currentMax = $max[0];
			
			/* On compare ces deux valeurs :
			  * Si $valMax est supperieur a $currentMax => On met a jour la table
			  * Sinon => On fait rien ;)
			*/
			
			if ($valMax >= $currentMax)
			{
				/* Mais d'abord il faut recuperer les pseudos des nouveaux gagnants :P */
				
				if ($category == 3 OR $category == 5 OR $category == 6)
				{
					$select_userName	= $db->sql_query (
						'SELECT user_name
						FROM ' . TABLE_USER . '
						WHERE user_id=\'' . $IdFlottes[1] . '\'') or die (mysqli_error ());
				}
				else
				{
					$select_userName	= $db->sql_query (
						'SELECT DISTINCT ' . TABLE_USER . '.user_name
						FROM ' . TABLE_USER . ', ' . $mysqlTable[$category] . '
						WHERE ' . TABLE_USER . '.user_id=' . $mysqlTable[$category] . '.user_id
						AND ' . $label[$i] . '=\'' . $valMax . '\'') or die (mysqli_error ());
				}
				
				$userNames = ''; // Initialisation obligatoire car on est dans la boucle for !
				
				while ($userName = mysqli_fetch_array ($select_userName))
				{
					if ($userNames == '')
						$userNames = $userName[0];
					else
						$userNames .= ', ' . $userName[0];
				}
				
				if ($valMax == 0)
					$userNames = '-';
				
				/* On met a jour */
				
				$db->sql_query (
					'UPDATE ' . TABLE_HOF_RECORDS . '
					SET valeur=\'' . $valMax . '\', pseudos=\'' . $userNames . '\'
					WHERE id_cat=\'' . $category . '\' AND nom=\'' . $name[$i] . '\'') or die (mysqli_error ());
			}
			
			/* On affiche nos records, a partir de la table =) */
			
			$select_records	= $db->sql_query ('SELECT valeur, pseudos FROM ' . TABLE_HOF_RECORDS . ' WHERE id_cat=\'' . $category . '\' AND  nom=\'' . $name[$i] . '\'');
			$records		= mysqli_fetch_array ($select_records);
			
			/* Separation des milliers si Flottes OU Defense */
			
			if ($category == 3 OR $category == 4 OR $category == 6)
				$valMax = number_format ($records[0], 0, ',', ' ');
			else
				$valMax = $records[0];
	?>
	
	<tr>
		<td class='gauche'><?php echo $name[$i]; ?></td>		
		<?php
			if ($settings['vous'])
			{
				if ($settings['diff'])
				{
					$difference = $records[0] - $nivUser;
					$difference = ' (' . $difference . ')';
				}
				
				echo '<td class=\'milieu\'>' . $nivUser . $difference . '</td>';
			}
		?>
		<td class='milieu'><?php echo $valMax; ?></td>
		<td class='droite'><?php echo $records[1]; ?></td>
	</tr>
		
	<?php
		}
		
		echo '</table>';
	}
	
	/*
		- Fonctions pour les formulaires
	*/
	
	function checked ($var)
	{
		if ($var)
			echo 'checked=\'checked\'';
	}
	
	function selected ($name, $colorBDD)
	{
		if ($name == $colorBDD)
			echo ' selected=\'selected\'';
	}
	
	/*
		- Remplit la table de configuration
	*/
	
	function fillTableConfig ()
	{
	    global $db;
		$db->sql_query('TRUNCATE TABLE '. TABLE_HOF_CONFIG .'');
		
		$db->sql_query('INSERT INTO '. TABLE_HOF_CONFIG .' VALUES
			(\'vous\', \'1\'),
			(\'diff\', \'1\'),
			(\'img\', \'0\'),
			(\'imgLink\', \'http://uni3.ogame.fr/evolution/gebaeude/\'),
			
			(\'uni50\', \'0\'),
			(\'prod_heure\', \'0\'),
			(\'prod_jour\', \'1\'),
			(\'prod_semaine\', \'1\'),
			(\'nb_recordsMen\', \'3\'),
			
			(\'center\', \'1\'),
			(\'couleurCat\', \'fuchsia\'),
			(\'tailleCat\', \'16\'),
			(\'gras\', \'1\'),
			(\'souligne\', \'1\'),
			(\'italic\', \'0\'),
			(\'couleurLabel\', \'black\'),
			(\'tailleLabel\', \'14\'),
			(\'couleurNiv\', \'silver\'),
			(\'tailleNiv\', \'16\'),
			(\'couleurPseudos\', \'yellow\'),
			(\'taillePseudos\', \'14\')');
	}
	
	/*
		- Remplit la table des records
	*/
	
	function fillTableRecords ($batiments, $labo, $flottes, $defense)
	{
	    global $db;
		$db->sql_query('TRUNCATE TABLE '. TABLE_HOF_RECORDS .'');
		
		// Batiments
		foreach ($batiments as $name)
			$db->sql_query('INSERT INTO '. TABLE_HOF_RECORDS .' VALUES (\'\', \'1\', \''. $name .'\', \'\', \'\')');
		
		// Laboratoire
		foreach ($labo as $name)
			$db->sql_query('INSERT INTO '. TABLE_HOF_RECORDS .' VALUES (\'\', \'2\', \''. $name .'\', \'\', \'\')');
		
		// Flottes
		foreach ($flottes as $name)
			$db->sql_query('INSERT INTO '. TABLE_HOF_RECORDS .' VALUES (\'\', \'3\', \''. $name .'\', \'\', \'\')');
		
		// Defense
		foreach ($defense as $name)
			$db->sql_query('INSERT INTO '. TABLE_HOF_RECORDS .' VALUES (\'\', \'4\', \''. $name .'\', \'\', \'\')');
		
		// Batiments cumules
		foreach ($batiments as $name)
			$db->sql_query('INSERT INTO '. TABLE_HOF_RECORDS .' VALUES (\'\', \'5\', \''. $name .'\', \'\', \'\')');
		
		// Defense cumules
		foreach ($defense as $name)
			$db->sql_query('INSERT INTO '. TABLE_HOF_RECORDS .' VALUES (\'\', \'6\', \''. $name .'\', \'\', \'\')');
	}
?>