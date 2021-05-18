<?php
/**************************************************************
Device send events API
Invia segnalazioni di eventi dal device
Chiamata dal dispositivo ESP32 per aggiornare gli allarmi 
http://hostname.domain/standbyme/api/sendevts.php?parms={"mymac:"##:...:##","events":[{"evt":"0|1","adr":"##:...:##"},...]}
richiede i seguenti parametri di GET
"parms": stringa JSON nel formato {"mymac:"##:...:##","events":[{"evt":"0|1","adr":"##:...:##"},...]}
dove:
mymac è il btmac del device che sta inviando il messaggio
events è un array di eventi di cessato/entrata allarme nei confronti di altri device (max 25 attualmente limitato a 10)
ogni elemento di events contiene:
evt: 0|1 cessato/entrata allarme
adr: btmac del device che ha provocato la variazione
Restituisce:
se parms è una stringa JSON valida restituisce {"status":"ok"}
altrimenti( parms mancante, stringa JSON non valida, valori mancanti nella stringaJSON) restituisce un messaggio di errore:
{"status":"fail","data":"error_description"}

***************************************************************/
//estrae il parametro di GET
if(isset($_GET['parms'])) { //parms esiste
    $parms=urldecode($_GET['parms']);
	//decodifica JSON
	$jparms=json_decode($parms,true);
	//verifica se JSON è ben formattato
	if(!is_null($parms)){ //stringa JSON valida
		$err=0;
		//verifica se JSON contiene mymac ed almeno un elemento nell'array events
		if(isset($jparms['mymac']))  $mymac=$jparms['mymac'];		//esiste ed estrae mymac 
		else			    	     $err=1;						//non esiste mymac
		//verifica se esiste l'array events
		if(isset($jparms['events'])) $events=$jparms['events'];		//esiste ed estrae array events
		else	            		 $err=1;						//non esiste array events
		//se events esiste verifica se i suoi elementi contengono evt e adr
		if(isset($events)) {										//esiste array events
			foreach ($events as $event){
				if(!isset($event['evt']))$err=1;					//l'elemento non ha evt
				if(!isset($event['adr']))$err=1;					//l'elemento non ha adr
			}	
		}	
		else $err=1;												//non esiste array evt
		//verifica se string JSON contiene tutti i dati
		if($err==0){ 	//la string JSON contiene tutti i dati
			//connessione al database
			$mysqli = new mysqli('62.149.150.225','Sql803879','0lh773f46r','Sql803879_4');
			//ripete per ogni elemento di events
			foreach ($events as $event){ 	//per ogni elemento di events
				$evt=$event['evt'];			//estrae evt (0=cessato allarme, 1=entrata in allarme)	
				$adr=$event['adr'];			//estrae adr (indirizzo dell'altro dispositivo)
				//verifica se cessato allarme o entrata in allarme
				if($evt=="1") { 	//entrata in allarme
					//prepara query per aggiungere relazione in running
					$q="INSERT INTO running VALUES ('$mymac', '$adr')";
					$result = $mysqli -> query($q);
					//la duration di una entrata in allarme è sempre 0
					$dur=0;
				}
				else {				//cessato allarme
					//prepara query per togliere relazione in running
					$q="DELETE FROM running WHERE btmac1='$mymac' AND btmac2='$adr'";
					$result = $mysqli -> query($q);
					//cerca il timestamp della precedente entrata in allarme
					$q="SELECT TIME_TO_SEC(TIMEDIFF(current_timestamp(),timestamp)) diff FROM proxy WHERE mybtmac='$mymac' AND otherbtmac='$adr' AND status=1 ORDER BY  timestamp DESC LIMIT 1";
 				    $result = $mysqli -> query($q);
				    $numrow=$result->num_rows;
					//verifica se la precedente entrata in allarme esiste
					if($numrow==1){	//la precedente entra in allarme esiste: memorizza il tempo passato in Sec
						$row = $result->fetch_assoc();	
						$dur=$row['diff'];	
					}
					else {			//precedente entrata non trovata: lascia a 0
						$dur=0;		
					}	
				}
				//aggiunge riga in proxy
				$q="INSERT INTO proxy VALUES ('$mymac','$adr',current_timestamp(),$evt,$dur)";
				$result = $mysqli -> query($q);
				//determina se mymac è in allarme (almeno un elemento di running contiene mymac)
				$q="SELECT * FROM running WHERE btmac1='$mymac' OR btmac2='$mymac'";
				$result = $mysqli -> query($q);
				$numrow=$result->num_rows;
				//verifica se mymac è in allarme (almeno una riga in running)
				if($numrow==0)	$status=0;	//non in allarme
				else 			$status=1;	//in allarme
				//aggiorna stato di device
				$q="UPDATE device SET status=$status WHERE btmac='$mymac'";
				$result = $mysqli -> query($q);
				//determina se othermac è in allarme (almeno un elemento di running contiene other)
				$q="SELECT * FROM running WHERE btmac1='$adr' OR btmac2='$adr'";
				$result = $mysqli -> query($q);
				$numrow=$result->num_rows;
				//verifica se mymac è in allarme (almeno una riga in running)
				if($numrow==0)	$status=0;	//non in allarme
				else 			$status=1;	//in allarme
				//aggiorna stato di device
				$q="UPDATE device SET status=$status WHERE btmac='$adr'";
				$result = $mysqli -> query($q);
			}	
			$response['status']='ok';
		}
		else { //mancano valori nella stringa JSON
			$response['status']='fault';
			$response['data']="json string values missing";			
		}	
	}
	else { //stringa JSON non valida
		$response['status']='fault';
		$response['data']="json string not valid";			
	}	
}
else { //fault: il parametro di GET non esiste
    $response['status']='fault';
    $response['data']="device parms missing";	
}	
//send response to client in JSON format
header('Content-type: application/json');
echo json_encode($response);
?>
