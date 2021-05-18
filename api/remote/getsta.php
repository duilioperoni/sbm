<?php
/**************************************************************
GET STA
Estrae il solo stato di tutti i dispositivi configurati
Chiamata dalla pagina Dashboard per l'aggionamento dinamico
Nessun parametro
Restuisce lo stato di tutti i dispositivi con un array JSON:
{"response":"ok","data":[{"id":"id_code","status":"0|1"},...}
***************************************************************/
//connessione al database
$mysqli = new mysqli('62.149.150.225','Sql803879','0lh773f46r','Sql803879_4');
//estrae informazioni di stato
$result = $mysqli -> query("SELECT id,status FROM device");
//determina il numero di elementi
$numrow=$result->num_rows;
//traforma il risultato in formato JSON
$response['response']='ok';
for($i=0;$i<$numrow;$i++){
	$row = $result -> fetch_assoc();
	$response['data'][$i]['id']=$row['id'];
	$response['data'][$i]['status']=$row['status'];
}
//invia al cliente in formato JSON
header('Content-type: application/json');
echo json_encode($response);
?>