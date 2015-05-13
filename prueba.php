<?php
include('config/config.php');
include('config/connection.php');

function preprocesamiento($dbhandler,$destino,$origen){
	$result = mysqli_query($dbhandler,"SELECT contenthash,filename,filesize,mimetype,id FROM mdl_files WHERE mimetype in ('application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','text/html','text/plain','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.oasis.opendocument.text','application/vnd.oasis.opendocument.spreadsheet','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel','text/rtf','application/vnd.openxmlformats-officedocument.presentationml.slideshow','application/vnd.oasis.opendocument.presentation')");
	$records = mysqli_num_rows($result);
 
	$start = array_sum(explode(' ', microtime())); 
	error_log(strftime("%F %T"));

	while ($row = mysqli_fetch_row($result)){
	   foreach ($row as $field){
		$dorigen=$origen."/".substr($row[0],0,2)."/".substr($row[0],2,2)."/".$row[0];
		$ddestino=$destino."/".$row[0];
		copy($dorigen,$ddestino);
		$renombrado=$destino."/".$row[1];
		rename($ddestino,$renombrado);
		error_log($renombrado);
	  }
	}

	$end = array_sum(explode(' ', microtime())); 
	error_log(strftime("%F %T"));
	error_log("Tiempo total: ".round($end - $start)." segs."); 
}

function poblarBD($dbhandler,$destinotxt){
	$id=1;
	
	$dir = opendir($destinotxt);
	$start = array_sum(explode(' ', microtime()));
        while ($elemento = readdir($dir)){
                if($elemento != "." && $elemento != ".."){
			$query="INSERT INTO nlp_archivos_txt(id,nombreArchivo) VALUES ('$id','$elemento')";
			mysqli_query($dbhandler,$query);
			$resultado=shell_exec('java -jar lib/langdetect.jar --detectlang -d profiles/ '.$destinotxt."/".$elemento);
			$query="INSERT INTO nlp_archivos_idiomas(idarchivo,idioma) VALUES ('$id','$resultado')";
			mysqli_query($dbhandler,$query);
			error_log("Archivo:".$elemento." ".$id." ".$resultado);
			$id++;
                }
        }
	$end = array_sum(explode(' ', microtime()));
	error_log("Tiempo total: ".round($end - $start)." segs."); 
}


ini_set("log_errors",1);
ini_set("error_log","/tmp/nlp_log");

//preprocesamiento($dbhandler,$destino,$origen);
//error_log("Proceso finalizado");

//conversion de pdf en bash
//for file in *.pdf;do sudo pdftotext "$file" "../txt/$file.txt";done
poblarBD($dbhandler,$destinotxt);

//echo "<pre>"; print_r($results);
?> 
