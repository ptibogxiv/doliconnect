<?php

$res = @include ("../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

$action	= GETPOST('action', 'alpha');
$bid	= GETPOST('bid', 'int');


$socid='';
if ($user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}

if ($user->rights->exportcomptable->comptable) {
	$Comptable = true;
}

llxHeader("",$langs->trans("Export comptable")); 

if ($Comptable) {
echo '<input type="button" value="Déconnexion" class="button" style="float: right;" onclick="window.location=\'../user/logout.php\'">';
}
print load_fiche_titre($langs->trans("Export factures"),'','title_accountancy.png');


/*
SELECT DISTINCT s.nom as s_nom, s.code_compta as s_code_compta, s.tva_intra as s_tva_intra, f.facnumber as f_facnumber, f.type as f_type, f.datef as f_datef, f.date_lim_reglement as f_date_lim_reglement, f.total as f_total, f.total_ttc as f_total_ttc, f.tva as f_tva FROM ".MAIN_DB_PREFIX."societe as s LEFT JOIN ".MAIN_DB_PREFIX."c_country as c on s.fk_pays = c.rowid, ".MAIN_DB_PREFIX."facture as f LEFT JOIN ".MAIN_DB_PREFIX."projet as pj ON f.fk_projet = pj.rowid LEFT JOIN ".MAIN_DB_PREFIX."user as uc ON f.fk_user_author = uc.rowid LEFT JOIN ".MAIN_DB_PREFIX."user as uv ON f.fk_user_valid = uv.rowid LEFT JOIN ".MAIN_DB_PREFIX."facture_extrafields as extra ON f.rowid = extra.fk_object LEFT JOIN ".MAIN_DB_PREFIX."paiement_facture as pf ON pf.fk_facture = f.rowid LEFT JOIN ".MAIN_DB_PREFIX."paiement as p ON pf.fk_paiement = p.rowid LEFT JOIN ".MAIN_DB_PREFIX."c_paiement as pt ON pt.id = p.fk_paiement LEFT JOIN ".MAIN_DB_PREFIX."bank as b ON b.rowid = p.fk_bank LEFT JOIN ".MAIN_DB_PREFIX."bank_account as ba ON ba.rowid = b.fk_account WHERE f.fk_soc = s.rowid AND f.entity IN (1) and ( date_format(f.datef,'%Y%m%d') >= 20170801 AND date_format(f.datef,'%Y%m%d') <= 20170831)
*/

$serveur	= 'gl1mj.myd.infomaniak.com';
$user		= 'gl1mj_app_doliba';
$password	= 'vO8GcOWSin';
$base		= 'gl1mj_app_dolibarr';

$Connexion = mysqli_connect("$serveur", "$user", "$password");
mysqli_select_db($Connexion, $base);

mysqli_query($Connexion,"SET NAMES UTF8");
mysqli_query($Connexion,"SET CHARACTER  UTF8");

$chemin 	= 'export_'.date('Ymd').'-'.date('His').'.csv';

$fichier_csv = fopen('export/'.$chemin, 'w+');

fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));


fputcsv($fichier_csv, array('CLIENT','IBAN','TVA INTRA','REF FACTURE','DATE FACTURATION','DATE ECHEANCE','HT','TTC','HT 6%','TVA 6%','HT 21%','TVA 21%','HT 0%','TVA 0%'));


if ($_POST['DateDebut'] <> '' && $_POST['DateFin'] <> '') {	
	$DateDebutSQL 	= implode('-', array_reverse(explode('/', $_POST['DateDebut'])));
	$DateFinSQL 	= implode('-', array_reverse(explode('/', $_POST['DateFin'])));
} else {
	$DateDebutSQL 	= date('Y').'-'.date('m').'-01';
	$DateFinSQL 	= date('Y').'-'.date('m').'-'.date('t');
}
//echo $DateDebutSQL.' - '.$DateFinSQL.' | '.$_POST['DateDebut'].' - '.$_POST['DateFin'];
$Sql = "
SELECT DISTINCT 

s.nom as s_nom, 
sr.iban_prefix as sr_iban_prefix, 
s.tva_intra as s_tva_intra, 
f.ref as f_facnumber, 
f.type as f_type, 
f.datef as f_datef, 
f.date_lim_reglement as f_date_lim_reglement, 
f.total as f_total, 
f.total_ttc as f_total_ttc, 
f.tva as f_tva, 
f.rowid as f_rowid

FROM 
".MAIN_DB_PREFIX."societe as s LEFT JOIN 
".MAIN_DB_PREFIX."c_country as c on s.fk_pays = c.rowid LEFT JOIN 
".MAIN_DB_PREFIX."societe_rib as sr ON sr.fk_soc = s.rowid, 

".MAIN_DB_PREFIX."facture as f LEFT JOIN 
".MAIN_DB_PREFIX."projet as pj ON f.fk_projet = pj.rowid LEFT JOIN 
".MAIN_DB_PREFIX."user as uc ON f.fk_user_author = uc.rowid LEFT JOIN 
".MAIN_DB_PREFIX."user as uv ON f.fk_user_valid = uv.rowid LEFT JOIN 
".MAIN_DB_PREFIX."facture_extrafields as extra ON f.rowid = extra.fk_object LEFT JOIN 
".MAIN_DB_PREFIX."paiement_facture as pf ON pf.fk_facture = f.rowid LEFT JOIN 
".MAIN_DB_PREFIX."paiement as p ON pf.fk_paiement = p.rowid LEFT JOIN 
".MAIN_DB_PREFIX."c_paiement as pt ON pt.id = p.fk_paiement LEFT JOIN 
".MAIN_DB_PREFIX."bank as b ON b.rowid = p.fk_bank LEFT JOIN 
".MAIN_DB_PREFIX."bank_account as ba ON ba.rowid = b.fk_account

WHERE f.fk_soc = s.rowid AND (f.datef BETWEEN '$DateDebutSQL' and '$DateFinSQL') order by f_rowid asc
";
/*
$ResultatListeFactures		= mysqli_query($Connexion,$RequeteListeFactures) or die ('Erreur : '.mysqli_error($Connexion) ); echo '<p>'.$RequeteListeFactures.'</p>';
while($DataListeFactures	= mysqli_fetch_object($ResultatListeFactures))
{
	$Lignes 		= array();
	
	$IdFacture	= $DataListeFactures->f_rowid;
	
	$Lignes[]		= $DataListeFactures->s_nom;
	$Lignes[]		= $DataListeFactures->sr_iban_prefix;
	$Lignes[]		= $DataListeFactures->s_tva_intra;
	
	$Lignes[]		= $DataListeFactures->f_facnumber;
	//$Lignes[]		= $DataListeFactures->f_type;
	$Lignes[]		= date('d/m/Y',strtotime($DataListeFactures->f_datef));
	$Lignes[]		= date('d/m/Y',strtotime($DataListeFactures->f_date_lim_reglement));
	
	$Lignes[]		= $DataListeFactures->f_total;
	$Lignes[]		= $DataListeFactures->f_total_ttc;
	
	$TotalTVA6	= 0;
	$TotalTVA21	= 0;
	$TotalTVA0	= 0;
	
	if ($DataListeFactures->f_type == 0)
	{
		$RequeteDetailsFactures		= ; //echo '<p>'.$RequeteDetailsFactures.'</p>';
		$ResultatDetailsFactures	= mysqli_query($Connexion,$RequeteDetailsFactures) or die ('Erreur : '.mysqli_error($Connexion) );
		while($DataDetailsFactures 	= mysqli_fetch_object($ResultatDetailsFactures))
		{
			switch($DataDetailsFactures->tva_tx){
				case '6.000':
					$TotalTva6HT[] 		= $DataDetailsFactures->total_ht;
					$TotalTva6TTC[] 	= $DataDetailsFactures->total_ttc;
				break;
				case '21.000':
					$TotalTva21HT[] 	= $DataDetailsFactures->total_ht;
					$TotalTva21TTC[] 	= $DataDetailsFactures->total_ttc;
				break;
				case '0.000':
					$TotalTva0HT[] 		= $DataDetailsFactures->total_ht;
					$TotalTva0TTC[] 	= $DataDetailsFactures->total_ttc;
				break;	
			}
		}

		$TotalTva6HT	= array_sum($TotalTva6HT);
		$TotalTva6TTC	= array_sum($TotalTva6TTC);
		
		$TotalTva21HT 	= array_sum($TotalTva21HT);
		$TotalTva21TTC	= array_sum($TotalTva21TTC);
		
		$TotalTva0HT 	= array_sum($TotalTva0HT);
		$TotalTva0TTC 	= array_sum($TotalTva0TTC);
	}
	
	$Lignes[] 	= number_format($TotalTva6HT, 2, ',', ' ');
	$Lignes[] 	= number_format($TotalTva21HT, 2, ',', ' ');
	$Lignes[] 	= number_format($TotalTva0HT, 2, ',', ' ');
	
	fputcsv($fichier_csv, $Lignes);
}
*/
if ($Comptable) {
	echo '<script>$("#id-top").hide();</script>';
	echo '<script>$(".side-nav").hide();</script>';
}


echo '<form action="" method="POST" id="FormEnvoiDates" name="FormEnvoiDates">';
echo '<input type="hidden" name="token" value="'.newToken().'">';
echo '<table class="tagtable liste">';
echo '<tr>';
echo '<td colspan="2"><b>Recherche par période:</b></td>';
echo '<td rowspan="3"><input type="submit" name="EnvoiDates" id="EnvoiDates" value="Afficher le résultat" class="button"></td>';
echo '<td align="right" width="50%" rowspan="3"><a href="export/dl.php?File=' . $chemin . '">Télécharger le tableau en csv</a></td>';
echo '</tr>';
echo '<tr>';
echo '<td width="25">De:</td>';
echo '<td><input type="text" name="DateDebut" id="DateDebut" class="maxwidth75" maxlength="11" value="'.date('d/m/Y',strtotime($DateDebutSQL)).'" onchange="dpChangeDay(\'DateDebut\',\'dd/MM/yyyy\'); "><button id="DateDebutButton" type="button" class="dpInvisibleButtons" onclick="showDP(\'/core/\',\'DateDebut\',\'dd/MM/yyyy\',\'fr_FR\');"><img src="/theme/eldy/img/object_calendarday.png" alt="" title="Sélectionnez une date" class="datecallink" border="0"></button></td>';
echo '</tr>';
echo '<tr>';
echo '<td width="25">A:</td>';
echo '<td><input type="text" name="DateFin" id="DateFin" class="maxwidth75" maxlength="11" value="'.date('d/m/Y',strtotime($DateFinSQL)).'" onchange="dpChangeDay(\'DateFin\',\'dd/MM/yyyy\'); ><button id="DateFinButton" type="button" class="dpInvisibleButtons" onclick="showDP(\'/core/\',\'DateFin\',\'dd/MM/yyyy\',\'fr_FR\');"><img src="/theme/eldy/img/object_calendarday.png" alt="" title="Sélectionnez une date" class="datecallink" border="0"></button></td>';
echo '</tr>';
echo '</table>';

echo '</form>';


echo '<table class="tagtable liste">';

echo '<tbody>';
echo '<tr class="liste_titre">';
echo '<th class="liste_titre">Nom</th>';
echo '<th class="liste_titre">IBAN</th>';
echo '<th class="liste_titre">TVA INTRA</th>';
echo '<th class="liste_titre">FACTURE</th>';
echo '<th class="liste_titre">DATE FACTURE</th>';
echo '<th class="liste_titre">DATE LIMITE REGLEMENT</th>';
echo '<th class="liste_titre">TOTAL HT</th>';
echo '<th class="liste_titre">TOTAL TTC</th>';
echo '<th class="liste_titre">HT 6%</th>';
echo '<th class="liste_titre">TVA 6%</th>';
echo '<th class="liste_titre">HT 21%</th>';
echo '<th class="liste_titre">TVA 21%</th>';
echo '<th class="liste_titre">HT 0%</th>';
echo '<th class="liste_titre">TVA 0%</th>';
echo '</tr>';
echo '</tbody>';

$RequeteListeFactures = $db->query($Sql);
if ($RequeteListeFactures)
{
	$numRequeteListeFactures = $db->num_rows($RequeteListeFactures);
	$iRequeteListeFactures = 0;

	if ($numRequeteListeFactures)
	{
		while ($iRequeteListeFactures < $numRequeteListeFactures)
		{
			$objRequeteListeFactures = $db->fetch_object($RequeteListeFactures);
			if ($objRequeteListeFactures)
			{
				echo '<tr>';
				
				$Lignes 		= array();
				
				$IdFacture		= $objRequeteListeFactures->f_rowid;
				
				$Lignes[]		= $objRequeteListeFactures->s_nom;
				$Lignes[]		= $objRequeteListeFactures->sr_iban_prefix;
				$Lignes[]		= $objRequeteListeFactures->s_tva_intra;
				
				$Lignes[]		= $objRequeteListeFactures->f_facnumber;
				//$Lignes[]		= $objRequeteListeFactures->f_type;
				$Lignes[]		= date('d/m/Y',strtotime($objRequeteListeFactures->f_datef));
				$Lignes[]		= date('d/m/Y',strtotime($objRequeteListeFactures->f_date_lim_reglement));
				
				$Lignes[]		= number_format($objRequeteListeFactures->f_total, 2, ',', ' ');;
				$Lignes[]		= number_format($objRequeteListeFactures->f_total_ttc, 2, ',', ' ');;
				
				$TotalTVA6		= 0;
				$TotalTVA21		= 0;
				$TotalTVA0		= 0;	
				$TotalTVA6HT	= 0;
				$TotalTVA21HT	= 0;
				$TotalTVA0HT	= 0;		
				
				$RequeteDetailsFacturesTVA6 = $db->query("select SUM(".MAIN_DB_PREFIX."facturedet.total_tva) as TotalTVA from ".MAIN_DB_PREFIX."facturedet where fk_facture='$IdFacture' and tva_tx='6.000'");
				if ($RequeteDetailsFacturesTVA6)
				{				
					$objRequeteDetailsFacturesTVA6 = $db->fetch_object($RequeteDetailsFacturesTVA6);
					$TotalTva6		= $objRequeteDetailsFacturesTVA6->TotalTVA;		
				}
				$RequeteDetailsFacturesTVA21 = $db->query("select SUM(".MAIN_DB_PREFIX."facturedet.total_tva) as TotalTVA from ".MAIN_DB_PREFIX."facturedet where fk_facture='$IdFacture' and tva_tx='21.000'");
				if ($RequeteDetailsFacturesTVA21)
				{				
					$objRequeteDetailsFacturesTVA21 = $db->fetch_object($RequeteDetailsFacturesTVA21);
					$TotalTva21		= $objRequeteDetailsFacturesTVA21->TotalTVA;		
				}
				$RequeteDetailsFacturesTVA0 = $db->query("select SUM(".MAIN_DB_PREFIX."facturedet.total_tva) as TotalTVA from ".MAIN_DB_PREFIX."facturedet where fk_facture='$IdFacture' and tva_tx='0.000'");
				if ($RequeteDetailsFacturesTVA0)
				{				
					$objRequeteDetailsFacturesTVA0 = $db->fetch_object($RequeteDetailsFacturesTVA0);
					$TotalTVA0		= $objRequeteDetailsFacturesTVA0->TotalTVA;	
				}
				
				
				$RequeteDetailsFacturesHT6 = $db->query("select SUM(".MAIN_DB_PREFIX."facturedet.total_ht) as TotalHT from ".MAIN_DB_PREFIX."facturedet where fk_facture='$IdFacture' and tva_tx='6.000'");
				if ($RequeteDetailsFacturesHT6)
				{				
					$objRequeteDetailsFacturesHT6 = $db->fetch_object($RequeteDetailsFacturesHT6);
					$TotalTva6HT	= $objRequeteDetailsFacturesHT6->TotalHT;			
				}
				$RequeteDetailsFacturesHT21 = $db->query("select SUM(".MAIN_DB_PREFIX."facturedet.total_ht) as TotalHT from ".MAIN_DB_PREFIX."facturedet where fk_facture='$IdFacture' and tva_tx='21.000'");
				if ($RequeteDetailsFacturesHT21)
				{				
					$objRequeteDetailsFacturesHT21 = $db->fetch_object($RequeteDetailsFacturesHT21);
					$TotalTVA21HT	= $objRequeteDetailsFacturesHT21->TotalHT;
				}
				$RequeteDetailsFacturesHT0 = $db->query("select SUM(".MAIN_DB_PREFIX."facturedet.total_ht) as TotalHT from ".MAIN_DB_PREFIX."facturedet where fk_facture='$IdFacture' and tva_tx='0.000'");
				if ($RequeteDetailsFacturesHT0)
				{				
					$objRequeteDetailsFacturesHT0 = $db->fetch_object($RequeteDetailsFacturesHT0);
					$TotalTVA0HT	= $objRequeteDetailsFacturesHT0->TotalHT;			
				}
				
				
				$Lignes[] 	= number_format($objRequeteDetailsFacturesHT6->TotalHT, 2, ',', ' ');
				$Lignes[] 	= number_format($objRequeteDetailsFacturesTVA6->TotalTVA, 2, ',', ' ');
				$Lignes[] 	= number_format($objRequeteDetailsFacturesHT21->TotalHT, 2, ',', ' ');
				$Lignes[] 	= number_format($objRequeteDetailsFacturesTVA21->TotalTVA, 2, ',', ' ');
				$Lignes[] 	= number_format($objRequeteDetailsFacturesHT0->TotalHT, 2, ',', ' ');
				$Lignes[] 	= number_format($objRequeteDetailsFacturesTVA0->TotalTVA, 2, ',', ' ');
					
				
				fputcsv($fichier_csv, $Lignes, ',', '"');
				
				echo '<td>'.$objRequeteListeFactures->s_nom.'</td>';
				echo '<td>'.$objRequeteListeFactures->sr_iban_prefix.'</td>';
				echo '<td>'.$objRequeteListeFactures->s_tva_intra.'</td>';
				echo '<td>'.$objRequeteListeFactures->f_facnumber.'</td>';
				echo '<td>'.date('d/m/Y',strtotime($objRequeteListeFactures->f_datef)).'</td>';
				echo '<td>'.date('d/m/Y',strtotime($objRequeteListeFactures->f_date_lim_reglement)).'</td>';
				echo '<td>'.number_format($objRequeteListeFactures->f_total, 2, ',', ' ').'</td>';		
				echo '<td>'.number_format($objRequeteListeFactures->f_total_ttc, 2, ',', ' ').'</td>';	
				echo '<td>'.number_format($objRequeteDetailsFacturesHT6->TotalHT, 2, ',', ' ').'</td>';	
				echo '<td>'.number_format($objRequeteDetailsFacturesTVA6->TotalTVA, 2, ',', ' ').'</td>';	
				echo '<td>'.number_format($objRequeteDetailsFacturesHT21->TotalHT, 2, ',', ' ').'</td>';	
				echo '<td>'.number_format($objRequeteDetailsFacturesTVA21->TotalTVA, 2, ',', ' ').'</td>';	
				echo '<td>'.number_format($objRequeteDetailsFacturesHT0->TotalHT, 2, ',', ' ').'</td>';	
				echo '<td>'.number_format($objRequeteDetailsFacturesTVA0->TotalTVA, 2, ',', ' ').'</td>';	
				
				echo '</tr>';			
			}
			
			$TotalTva6HT	= 0;
			$TotalTva6TTC	= 0;
			
			$TotalTva21HT 	= 0;
			$TotalTva21TTC	= 0;
			
			$TotalTva0HT 	= 0;
			$TotalTva0TTC 	= 0;
							
			$TotalTva6Tempo = 0;
							
			$iRequeteListeFactures++;
		}
	}
}

fclose($fichier_csv);
		
echo '</table>';




/*
header('Content-disposition: attachment; filename="' . $chemin . '"');
header('Content-Type: application/force-download');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '. filesize($chemin));
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
readfile($chemin);*/




llxFooter();

$db->close();