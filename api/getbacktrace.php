<?php
/**************************************************************
GET BACKTRACE
Estrae lo storico degli allarmi (entrata, cessato) di un dispositivo in un periodo fissato
Chiamata dalla pagina dashboard per generare uno storico degli allarmi
richiede i seguenti parametri di GET
"btmac": bt mac del dispositivo richiesto
"strtime": inizio periodo in formato 'yyy-mm-dd' (optionale, se assente dall'inizio)
"endtime": fine periodo in formato 'yyy-mm-dd' (optionale, se assente fino alla fine)
Restituisce tutti gli eventi di allarme come array JSON:
{"response":"ok","data":[{"myid":"id_code","mymac":"##:##:##:##:##:##","othid":"id_code","othmac":"##:##:##:##:##:##","st":"0|1","ts":"yyyy-mm-dd hh:mm:ss" ,"dur":"######"} ,...]}
18/95/2021 commit github
***************************************************************/
//estrae i parametri di GET
$btmac = $_REQUEST['btmac'];
//se strtime/endtime assenti forza inizio/fine
if(isset($_REQUEST['strtime'])) $strtime = $_REQUEST['strtime'];
else							$strtime = '';
if(isset($_REQUEST['endtime'])) $endtime = $_REQUEST['endtime'];
else 							$endtime = '';
if($strtime=='') $strtime='1970-01-01';
if($endtime=='') $endtime='2286-11-20';
//connette al database
$mysqli = new mysqli('localhost','standbymeuser','qwerty','standbyme');
//estrae info storiche
$result = $mysqli -> query("SELECT
                              t1.id myid,
							  t2.mybtmac mymac,
							  (SELECT DISTINCT t3.id 
							   FROM device t3,proxy t4 
							   WHERE t3.btmac=t4.otherbtmac 
							   AND t4.otherbtmac=t2.otherbtmac) othid,
							  t2.otherbtmac othmac,
							  t2.status st,
							  t2.timestamp ts,
							  t2.duration dur
                            FROM proxy t2,device t1 
                            WHERE  t2.mybtmac=t1.btmac
                            AND t2.mybtmac='$btmac'
                            AND t2.timestamp>='$strtime'
                            AND t2.timestamp<='$endtime'
							ORDER BY t2.timestamp");
//determina il numero di elementi
$numrow=$result->num_rows;
//trasforma in formato JSON
$response['response']='ok';
for($i=0;$i<$numrow;$i++){
	$row = $result -> fetch_assoc();
	$response['data'][$i]['myid']=$row['myid'];
	$response['data'][$i]['mymac']=$row['mymac'];
	$response['data'][$i]['othid']=$row['othid'];
	$response['data'][$i]['othmac']=$row['othmac'];
	$response['data'][$i]['st']=$row['st'];
	$response['data'][$i]['ts']=$row['ts'];
	$response['data'][$i]['dur']=$row['dur'];
}
//invia risposta al client in formato JSON
header('Content-type: application/json');
echo json_encode($response);
?>