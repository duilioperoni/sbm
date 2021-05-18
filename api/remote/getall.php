<?php
/**************************************************************
GET ALL 
Estrae tutti i dati dei device configurati
Chiamata dalla pagina Dashboard per la inizializzazione della pagina
Nessun parametro
Restuisce tutti i dispositivi con un array JSON:
{"response":"ok","data":[{"id":"id_code","description":"descr_id","type:"M|W|H|A","status":"0|1|2|3","sensore:"0|1","timestamp":"##########"}, ...]} | "fail_desc" }
***************************************************************/
//connette al database
$mysqli = new mysqli('62.149.150.225','Sql803879','0lh773f46r','Sql803879_4');
//estrae tutti i dispositivi
$result = $mysqli -> query("SELECT * FROM device");
//determina numero elementi
$numrow=$result->num_rows;
//trasforma in formato JSON
$response['response']='ok';
for($i=0;$i<$numrow;$i++){
	$row = $result -> fetch_assoc();
	$response['data'][$i]['id']=$row['id'];
	$response['data'][$i]['btmac']=$row['btmac'];
	$response['data'][$i]['status']=$row['status'];
	$response['data'][$i]['timestamp']=$row['timestamp'];
}
//invia risposta al client in formato JSON
header('Content-type: application/json');
echo json_encode($response);
?>