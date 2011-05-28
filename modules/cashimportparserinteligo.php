<?php

// 1 - właczone szykania 0 - wylączone szukania
define("SZUKAJDANE", 1);
define("SZUKAJID", 1);

function toPermalink($string) {
	$unPretty = array('/ä/', '/ö/', '/ü/', '/Ä/', '/Ö/', '/Ü/', '/ß/', '/ą/', '/Ą/', '/ć/', '/Ć/', '/ę/', '/Ę/', '/ł/', '/Ł/' ,'/ń/', '/Ń/', '/ó/', '/Ó/', '/ś/', '/Ś/', '/ź/', '/Ź/', '/ż/', '/Ż/','/Š/','/Ž/','/š/','/ž/','/Ÿ/','/Ŕ/','/Á/','/Â/','/Ă/','/Ä/','/Ĺ/','/Ç/','/Č/','/É/','/Ę/','/Ë/','/Ě/','/Í/','/Î/','/Ď/','/Ń/','/Ň/','/Ó/','/Ô/','/Ő/','/Ö/','/Ř/','/Ů/','/Ú/','/Ű/','/Ü/','/Ý/','/ŕ/','/á/','/â/','/ă/','/ä/','/ĺ/','/ç/','/č/','/é/','/ę/','/ë/','/ě/','/í/','/î/','/ď/','/ń/','/ň/','/ó/','/ô/','/ő/','/ö/','/ř/','/ů/','/ú/','/ű/','/ü/','/ý/','/˙/','/Ţ/','/ţ/','/Đ/','/đ/','/ß/','/Œ/','/œ/','/Ć/','/ć/','/ľ/');

	$pretty  = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss', 'a', 'A', 'c', 'C', 'e', 'E', 'l', 'L', 'n', 'N', 'o', 'O', 's', 'S', 'z', 'Z', 'z', 'Z','S','Z','s','z','Y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','O','U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','TH','th','DH','dh','ss','OE','oe','AE','ae','u');

	$permalink = strtolower(preg_replace($unPretty, $pretty, $string));

	return str_replace(" ", " ", preg_replace("/[^a-zA-Z0-9 ]/", "", $permalink) );
 
}

header("Content-type: text/html; charset=utf-8"); 

if(isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name']) && $_FILES['file']['size'])
{
	$file = file($_FILES['file']['tmp_name']);
	$ln = $_POST['number'];
} 
elseif(isset($_FILES['file'])) // upload errors
	switch($_FILES['file']['error'])
	{
		case 1: 
		case 2: $error['file'] = trans('File is too large.'); break;
		case 3: $error['file'] = trans('File upload has finished prematurely.'); break;
		case 4: $error['file'] = trans('Path to file was not specified.'); break;
		default: $error['file'] = trans('Problem during file upload.'); break;
}
//-----------------------------------------------
		// Wyszukiwanie przelewów
		
	$array=array();
	$ln = $_POST['number'];

// Upload pliku	
	move_uploaded_file($_FILES['file']['tmp_name'], "backups/import/".$_FILES['file']['name']); 
	
// 	czytanie pliku

	$plik = "backups/import/".$_FILES['file']['name'];
	$i=0;
	$uchwyt = fopen($plik,rb);
	while(!feof($uchwyt)){
	 	$linia = fgets($uchwyt);
		$array[$i] = strtoupper(iconv("windows-1250","UTF-8",$linia));
		$i++;
	}

// usuwanie poczatku

$g = count($array);
		
	for($i=0; $i<=1; $i++){
 		unset($array[$i]);
	}
	
// usuwanie konca
	for($i=$g-1; $i<=$g; $i++){
 		unset($array[$i]);
	}

// tworzenie tablicy
	$i = 0;
	$arrays=array();
	foreach($array as $a){
		$arrays[$i] = explode('","', $a);
		$i++;
	}
	$array = $arrays;



// SZYKANIE WPŁAT
	$g = count($array);
	for($i=0; $i<=$g; $i++){
 		if($array[$i][4] < 0) unset($array[$i]);
	}
	
	

// tworzenie tablicy poprawnej

	$i = 0;
	$arrays=array();
	foreach($array as $a){
		$arrays[$i] = $a;
		$i++;
	}
	$array = $arrays;
		
// ujednolicenie
	$g = count($array);

	for($i=0; $i<=$g-1; $i++){
 	
	$array[$i]['cash'] = str_replace(',', '.', str_replace(' ', '', $array[$i][4]));
	$array[$i]['date'] = $array[$i][1];
	$array[$i][0] = substr($array[$i][0], 1);
	$array[$i][11] = substr($array[$i][11], 0, -2);
	$array[$i]['name'] = $array[$i][8].' '.$array[$i][11];
	$array[$i]['numer'] = $array[$i][0];
	
	$array[$i]['name'] = strtr(toPermalink($array[$i]['name']), "¦","s");
	
	
	
	for($c=0; $c<12; $c++)
		unset($array[$i][$c]);
		
	}
	
	// WYJSCIE SERWISOWE	
//	echo '<textarea rows="30" cols="100">';
//	print_r($array);
//	echo '</textarea>';
//	exit();
	
//----------------------------------------------------------
	   	// przeszukiwanie po 1245/lms/2009
		
		$errore=array();
		
		$i =0;
		$o=0;
		
		foreach($array as $s) {
			$i++;
			if(strpos($s['name'], '/LMS/')== TRUE || strpos($s['name'], '\LMS')== TRUE)
			{
				$o++;
				$l = $s['name'];
				// numer pozycji
				$sz = strpos($s['name'], '/LMS/'); 
				if($sz == FALSE) $sz = strpos($s['name'], '\LMS');
				
				// rok
					$rok = substr($s['name'], $sz+5, 4);
				  			
				$fak = substr($s['name'], 0, $sz);
				$tok = strtok($fak, ". |-_+;<>,*!@#$%:^&()=/\\//ABCDEFGHIJKLMNOPRSTWYXVZabcdefghijklmnoprstwyzxęąćółśćżźńĄĘĆÓŁŚĆŻŹŃ");
					while ($tok !== false) {
    					$aa=$tok; 
					$tok = strtok(". |-_+;<>,*!@#$%:^&()=/\\//ABCDEFGHIJKLMNOPRSTWYVXZabcdefghijklmnoprstwyzxęąćółśćżźńĄĘĆÓŁŚĆŻŹŃ");
						
					}
				
				$id_fak = $aa;

	$c_id=$DB->GetOne('SELECT customerid FROM documents WHERE number=? AND DATE_FORMAT(FROM_UNIXTIME(cdate), "%Y")=? ',array($id_fak,$rok));

		$c = $DB->GetOne('SELECT name FROM customers WHERE id=?', array($c_id)).' '. $DB->GetOne('SELECT lastname FROM customers WHERE id=?', array($c_id));
		
		$DB->Execute('INSERT INTO cashimport (date, value, customer, 
					customerid, description, hash, closed) VALUES (?,?,?,?,?,?,?)',
					array(strtotime($s['date']),  $s['cash'], $c, $c_id, $s['numer'].'-1 - '.$s['name'], $s['numer'],0));
				
			} else {
				$errors[$i]['id'] = $i;
				$errors[$i]['date'] = $s['date'];
				$errors[$i]['title'] = $s['name'];
				$errors[$i]['cash'] = $s['cash'];
				$errors[$i]['numer'] = $s['numer'];
				
			}
		}
		
	$jeden_a = $o;
		
//-------------------------------------------------------
// przeszukiwanie po ID

	$error = array();
	$i=0;
	$d=0;
	if(SZUKAJID)
	foreach($errors as $a){
	$i++;
	$zap = strpos($a['title'], 'ID:');
		if($zap !== FALSE)
		{
			$d++;
			$c_id = substr($a['title'], $zap+3, 4);
			$c = $DB->GetOne('SELECT name FROM customers WHERE id=?', array($c_id)).' '. $DB->GetOne('SELECT lastname FROM customers WHERE id=?', array($c_id));
			
			$DB->Execute('INSERT INTO cashimport (date, value, customer, customerid, description, hash,closed) VALUES (?,?,?,?,?,?,?)', array(strtotime($a['date']), $a['cash'], $c, $c_id, $a['numer'].'-4 - '.$a['title'], $a['numer'],0));
			
		} else {
				$errore[$i]['id'] = $i;
				$errore[$i]['date'] = $a['date'];
				$errore[$i]['title'] = $a['title'];
				$errore[$i]['cash'] = $a['cash'];
				$errore[$i]['numer'] = $a['numer'];				
		}
	}

	$cztery_a = $d;
	
//-------------------------------------------------------
		// przeszukiwanie po imieniu i nazwisku
	

		$customers = $DB->GetAll('SELECT lastname,name,id FROM customers WHERE lastname !="" AND name != "" ');
		$ln=0;
		$f=0;
		$errors=array();
		
		foreach($errore as $a){
		$ln++;
		$g=0;
		
			if(SZUKAJDANE)
			foreach($customers as $b){
				
				$zap = strpos(strtoupper($a['title']), strtoupper(toPermalink($b['lastname'])));
				$zapp = strpos(strtoupper($a['title']), strtoupper(toPermalink($b['name'])));
				
				
				if($zap !== FALSE && $zapp !== FALSE) {	
					$f++;
					$g++;				
					$DB->Execute('INSERT INTO cashimport (date, value, customer, customerid, description, hash, closed) VALUES (?,?,?,?,?,?,?)',			array(strtotime($a['date']), $a['cash'], $b['name'].' '.$b['lastname'], $b['id'], $a['numer'].'-2 - '.$a['title'], $a['id'], 0));
				}
			}	
			
			if($g==0) {
				$DB->Execute('INSERT INTO cashimport (date, value, customer, customerid, description, hash, closed) VALUES (?,?,?,?,?,?,?)',	array(strtotime($a['date']), $a['cash'], ' ', 0, $a['numer'].'-3 - '.$a['title'], $a['numer'], 0));
					
			}
			
		}
		
	$dwa_a = $f;

	

	
//----------------------------------------------		
// Syatystyki

	$layout['stat']['add'] = $jeden_a+$dwa_a+$cztery_a;
	$layout['stat']['all'] = count($array);
	$layout['stat']['del'] = count($array)-($jeden_a+$dwa_a+$cztery_a);
	$layout['stat']['1a'] = $jeden_a;
	$layout['stat']['2a'] = $dwa_a;
	$layout['stat']['4a'] = $cztery_a;

	
		
	include(MODULES_DIR.'/cashimportinteligo.php');
	die;


$layout['pagetitle'] = trans('Cash Operations Import');
$layout['file'] = $file;

$SESSION->save('backto', $_SERVER['QUERY_STRING']);
$SMARTY->assign('file', $file);
$SMARTY->assign('error', $error);
$SMARTY->assign('customerlist', $LMS->GetCustomerNames());
$SMARTY->assign('sourcelist', 'Synergia Polska');
$SMARTY->display('cashimportinteligo.html');



?>
