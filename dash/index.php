<!DOCTYPE html>
<html lang="it-IT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SBM Dashboard</title>
<script type='text/javascript' src='../js/jquery/jquery.js?ver=1.12.4-wp'></script>
<script type='text/javascript' src='../js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
<script type='text/javascript' src="../js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<link href="../css/site.css" rel="stylesheet">
<link href="../css/bootstrap.css" rel="stylesheet">
<link href="../js/jquery-ui-1.12.1.custom/jquery-ui.css"rel="stylesheet" >
</head>
<body>
<div id="main">
<h1 class="intest">Device Dashboard</h1>
<hr id="hd1">
<div style="float:left; width:40%; padding:10px">
<h4>Proximity Devices</h4>
<div id="devices">
</div>
</div>
<div id="proxy2" >
<h4>Alert Backtracing</h4>
<b>STUDENT&nbsp;#&nbsp;<select name="myth" id="myth">
</select>&nbsp;START&nbsp;DATE&nbsp;<input size="10" value="2020-09-01" type="text" name="strdt" id="strdt">&nbsp;END&nbsp;DATE&nbsp;<input  size="10" value="2021-06-30" type="text" name="enddt" id="enddt">&nbsp;<input type="button" value="Backtrace" onClick="backtrace()"></b>
<div id="alert">
</div>
</div>
<div style="clear:left"></div>
<div style="float:clear">
<hr >
<h5 ><i>REQUEST:</i><span id="rq">?</span></h5>
<h5><i>RESPONSE:</i><span id="rs">?</span></h5>
<hr>
<div id="contenitore"><p class="doc">This web page simulates Standbyme Dashboard.<br>
It shows the status of the of bt devices (ok or alert) and allow extraction of backtracing info related to a single device<br>
These info are view only.</p></div>
</div>
</body>
</html>
<script type="text/javascript">
//main
//variabili per la generazione delle tag
var scheda;
var str;
var strtime=jQuery('#strdt').val();
var endtime=jQuery('#enddt').val();
var head1='<tr><td style="text-align:center;"><i>STUDENT #</i></td><td style="text-align:center;"><i>Device</i></td><td style="text-align:center;"><i>STATUS</i></td></tr>';
var row1='<tr><td style="text-align:center;">%00</td><td style="text-align:center;">%01</td><td style="text-align:center;"><span class="%02" id="rrsta%03">%04</span></td></tr>';
//carica la tabella dei devices
getall();
//attiva l'aggiornamento dello stato
var interval = setInterval(getsta, 500);
//FUNZIONI
//inizializza date picker
jQuery( function() {
    jQuery( "#strdt" ).datepicker({showOn: "button",buttonImage: "../js/jquery-ui-1.12.1.custom/images/calendar.gif",buttonImageOnly: true,buttonText:"Select date"});
	jQuery( "#strdt" ).datepicker( "option", "dateFormat", "yy-mm-dd");
    jQuery( "#enddt" ).datepicker({showOn: "button",buttonImage: "../js/jquery-ui-1.12.1.custom/images/calendar.gif",buttonImageOnly: true,buttonText:"Select date"});
	jQuery( "#enddt" ).datepicker( "option", "dateFormat", "yy-mm-dd");	
  } );
//getall: chiede la tabella completa dei devices e la carica
//e crea la lista a discesa del backtrace
function getall() {
	//invia richiesta ed attende la risposta
	jQuery.ajax({
		url: "../api/getall.php",
		dataType: 'html',
		success: function (data) {
					var things = jQuery.parseJSON( data ).data;
					//costruisce la tabella
					str='<table width="100%">'+head1;
					//per ogni device ricevuto
					things.forEach(function(t){
						//compone riga di tabella devices
						scheda=row1;
						scheda=scheda.replace("%00",t.id);
						scheda=scheda.replace("%03",t.id);
						scheda=scheda.replace("%01",t.btmac);
						var sta=t.status;
						//modifica l'elemento in base a status
						if(sta==0){
							scheda=scheda.replace("%02","vac");
							scheda=scheda.replace("%04","OK");
							
						}
						else if (sta==1){
							scheda=scheda.replace("%02","eng");
							scheda=scheda.replace("%04","ALERT");
						}
						else {
							scheda=scheda.replace("%02","unk");
							scheda=scheda.replace("%04","UNKNOWN");							
						}
						//aggiunge riga alla tabella	
						str=str+scheda;
						//aggiunge option alla lista
						jQuery('#myth').append(jQuery('<option>', {value:t.btmac, text:t.id+'('+t.btmac+')'}));
					});
					//chiudi tabella
                    str=str+"</table>"
					//inserisce tabella nella pagina
					jQuery("#devices").html(str);
				},
        error: function(){
					//error trap
                    jQuery("#contenitore").html("AJAX error");
                },
		});
}
//getsta: chiede stato e ed aggiorna colonna in tabella
function getsta() {
	//invia richiesta ed attende risposta
	jQuery.ajax({
		url: "../api/getsta.php",
		dataType: 'html',
		success: function (data) {
					var things = jQuery.parseJSON( data ).data;
					//per ogni device ricevuto
					things.forEach(function(t){
						//aggiorna stato del dispositivo
						var id=t.id;
						var sta=t.status;
						var msg="";
						if(sta==0){	//stato ok
							msg="OK";
							jQuery('#rrsta'+id).removeClass().addClass('vac');
							
						}
						else if (sta==1){ //stato allarme
							msg="ALERT";
							jQuery('#rrsta'+id).removeClass().addClass('eng');
						}
						else { //stato sconosciuto
							msg="UNKNOWN";
							jQuery('#rrsta'+id).removeClass().addClass('unk');
						}	
						jQuery('#rrsta'+id).html(msg);
					});
				},
        error: function(){
					//error trap
                    jQuery("#contenitore").html("AJAX error");
                },
		});
}

function backtrace() {
	//Costruisce l'URL di richiesta
	var mybtmac=jQuery('#myth option:selected').val();
	var strtime=jQuery('#strdt').val();
	var endtime=jQuery('#enddt').val();
	var req="../api/getbacktrace.php?btmac="+mybtmac+"&strtime="+strtime+"&endtime="+endtime;
	//Diagnostica locale
	jQuery('#rq').html(req);
	//invia richiesta ed attende la risposta
	jQuery.ajax({
		url: req,
		dataType: 'html',
		success: function (data) {
						//Diagnostica remota
						jQuery('#rs').html(data);
						var events = jQuery.parseJSON( data ).data;
						var ptab='<table class="table-bordered" width="100%"><tr><td class="legenda" width="5%">ID</td><td class="legenda" width="25%">MAC</td><td class="legenda" width="20%">STATUS</td><td class="legenda" width="25%">TIMESTAMP</td><td class="legenda" width="25%">DURATION</td></tr>';
						if (typeof events === "undefined") {
							ptab+='</table>';
						}
						else {
							events.forEach(function(t){
								//estrae stato
								var st=t.st;
								var sstr;
								//calcola durata evento
								var s=t.dur;
								var h=Math.floor(s/3600);
								var m=Math.floor((s%3600)/60);
								var s=(s%3600)%60;
								var sdur=h+":"+m+":"+s;
								if (st==0) sstr="END&nbsp;ALERT";
								else       sstr="START&nbsp;ALERT";
								ptab+='<tr><td class="dato">'+t.othid+'</td><td class="dato">'+t.othmac+'</td><td class="dato">'+sstr+'</td><td class="dato">'+t.ts+'</td><td class="dato">'+sdur+'</td></tr>';						
							});
							ptab+='</table>';
						}
						jQuery('#alert').html(ptab);
		},
        error: function(){
					//error trap
                    jQuery("#contenitore").html("AJAX error");
        },	
	});

}
</script>