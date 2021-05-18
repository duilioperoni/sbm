<?php
/**************************************************************
GET Histhogram for day
Estrae la serie storica degli allarmi aggregati per giorno per un range di date
Chiamata dalla pagina istogrammi per la inizializzazione della pagina
Parametri:
strdate: data di inizio della serie storica
enddate: data di fine della serie storica
Restuisce il conteggio di allarmi per giorno nel formato
{"response":"ok","data":[{"date":"count",...]} | "fail_desc" }
dove date è lo unix time della data
count è il numero di allarmi in quella data
***************************************************************/
error_reporting(0);
//se strtime/endtime assenti forza inizio/fine
if(isset($_REQUEST['strtime'])) $strtime = $_REQUEST['strtime'];
else							$strtime = '';
if(isset($_REQUEST['endtime'])) $endtime = $_REQUEST['endtime'];
else 							$endtime = '';
if($strtime=='') $strtime='2020-09-01';
if($endtime=='') $endtime='2021-06-30';
//connette al database
$mysqli = new mysqli('localhost','standbymeuser','qwerty','standbyme');
if($mysqli->connect_errno==0) {
	//estrae la serie storica
	$error=0;
	$q="SELECT 		UNIX_TIMESTAMP(DATE(c.datefield)) uxt,
					DATE(c.datefield) date,
					COALESCE(px.u,0) units 
		FROM 		calendar c 
		LEFT JOIN 	(SELECT DATE(p.timestamp)ts,
							COUNT(*) u 
					 FROM 	proxy p 
					 WHERE p.status=1 
					 GROUP BY DATE(p.timestamp) 
					 ORDER BY ts) px 
		ON 			(DATE(c.datefield)=px.ts)
		WHERE 		DATE(c.datefield)>='$strtime' 
		AND 		DATE(c.datefield)<='$endtime'";
	$result = $mysqli -> query($q);
	if($result!=false) {
		//determina numero elementi
		$numrow=$result->num_rows;
		//trasforma in formato JSON
		//$response['status']='ok';
		for($i=0;$i<$numrow;$i++){
			$row = $result -> fetch_assoc();
			$response['dps'][$i]['date']=$row['uxt']*1000;
//			$response['data'][$i]['cdate']=$row['date'];
			$response['dps'][$i]['units']=$row['units'];
		}
	}
	else { //errore di query
		$response['status']='fault';
		$response['data']="MySQL error n.".$mysqli->errno;		
	}	
}
else { //errore di connessione
	$response['status']='fault';
	$response['data']="MySQL connection error";			
}	
//invia risposta al client in formato JSON
header('Content-type: application/json');
echo json_encode($response);
?>