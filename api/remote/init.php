<?php
/**************************************************************
Device init API
Richiesta di inizializzazione dal device
Chiamata dai dispositivi ESP32
http://hostname.domain/standbyme/api/init.php?btmac=hh:hh:hh:hh:hh:hh
richiede il seguente parametro di GET
"btmac": bt mac address del device richiedente: 'hh:hh:hh:hh:hh:hh'
Restituisce:
se btmac è di un dispositivo configurato nel database restituisce un array JSON contenente i btmac di tutti i disposivi configurati.
{"status":"ok","data":["0":"bt_mac0", ...]}
altrimenti (dispositivo non configurato, parametro mancante, parametro non valido) restituisce un messaggio di errore json
{"response":"fail","data":"error_description"}

***************************************************************/
//estrae il parametrodi GET
if(isset($_GET['btmac'])) { //esiste il paramatro di GET
    $btmac=$_GET['btmac'];
	//varifica se il parametro è valido
	$check=preg_match('/([a-fA-F0-9]{2}:){5}([0-9A-Fa-f]{2})/', $btmac);
	if ($check) { //stringa valida come btmac
		//connette al database e verifica se il richiedente è configurato
		$mysqli = new mysqli('62.149.150.225','Sql803879','0lh773f46r','Sql803879_4');
	    $result = $mysqli -> query("SELECT * FROM device WHERE btmac='$btmac'");
	    $numrows=$result->num_rows;
	    if($numrows>0) { //c'è una riga di risposta: richiedente configurato configurato
			//estrae tutti i dispositivi configurati e genera JSON array
			$result = $mysqli -> query("SELECT * FROM device");	
			$response['status']='ok';
			$i=0;
			while ($row = $result->fetch_assoc()) {
				$response['data'][$i]=$row['btmac'];
				$i++;
			}
	    }
	    else { //errore:dispositivo richiedente non configurato
	       $response['status']='fault';
           $response['data']="not configured btmac device";	
        }			
	}
	else {  //errore:string bt mac non valida
		$response['status']='fault';
		$response['data']="btmac string invalid";	
	}	
}
else { //errore: parametro di GET mancante
    $response['status']='fault';
    $response['data']="device btmac missing";	
}	
//invia risposta al client in formato JSON
header('Content-type: application/json');
echo json_encode($response);
?>
