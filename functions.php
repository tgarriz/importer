<?php
include 'config.php';

if(isset($_POST["Import"])){
	echo	($_POST["Import"]);
	$con = getdb();
    $result = pg_query($con,'truncate asiento01');
	if(!isset($result)) {
		echo 'Error truncando table';
	}
    $filename=$_FILES["file"]["tmp_name"];    
     if($_FILES["file"]["size"] > 0)
       {
          $file = fopen($filename, "r");
            while (($getData = fgetcsv($file, 10000, ";")) !== FALSE)
             {
               $sql = "INSERT into asiento01 (nro_asiento,fecha,concepto,tipo,cod_cta,leyenda,debe,haber) 
	      values ('".$getData[0]."','".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."')";
              $result = pg_query($con, $sql);
          	if(!isset($result)) {
            		echo "<script type=\"text/javascript\">
              	alert(\"Invalid File:Please Upload CSV File.\");
	              window.location = \"importer.php\"
      	        </script>";    
	      } else {
      	        echo "<script type=\"text/javascript\">
              	alert(\"CSV File has been successfully Imported.\");
	            window.location = \"importer.php\"
      	        </script>";
           	}
         }
          
	    fclose($file);
	 }
	 pg_close($con);
}

if(isset($_POST["Generar"])){
	vaciar_dir();
	$con = getdb();
	$result = pg_query($con,'select distinct(nro_asiento) from asiento01');
	/*echo var_dump($aux_asientos);
	$asientos = pg_fetch_array($aux_asientos);
	echo var_dump($asientos);*/
	while($row = pg_fetch_assoc($result)) {
			crea_encabezado($con,$row['nro_asiento']);
			crea_datos($con,$row['nro_asiento']);
	}
	crea_zip();
	enviar();
	pg_close($con);
}

function crea_encabezado($con,$asiento){
	$sql = "select  rpad(nro_asiento,6,' ') || 
				regexp_replace(fecha, '/', '', 'g') ||
				rpad(substring(concepto from 1 for 30),30,' ') ||
				substring(upper(tipo) from 1 for 1) ||
				rpad('1',3,' ') ||
				'   ' as campo	
			from asiento01
			where nro_asiento = '$asiento' limit 1";
	//echo $sql;
	$result = pg_query($con,$sql);
	$file = fopen("files/asiento-".$asiento."1.imp","w");
	while($row = pg_fetch_assoc($result)) {
		fputs($file,$row['campo']."\r\n");
	}
	fclose($file);
}

function crea_datos($con,$asiento){
	$sql = "select  rpad(nro_asiento,6,' ') ||
				rpad(replace(replace(cod_cta,'.',''),'/',''),15,' ') ||
				regexp_replace(fecha, '/', '', 'g') ||
				rpad(substring(leyenda from 1 for 30),30,' ') ||
				'D' ||
				rpad(creaNumero(debe),15,' ') ||
				rpad(' ',17,' ') ||
				rpad(creaNumero(debe),15,' ') ||
				regexp_replace(fecha, '/', '', 'g') as campo
			from asiento01
			where haber = '' AND nro_asiento = '$asiento' 
			union
			select  rpad(nro_asiento,6,' ') ||
				rpad(replace(replace(cod_cta,'.',''),'/',''),15,' ') ||
				regexp_replace(fecha, '/', '', 'g') ||
				rpad(substring(leyenda from 1 for 30),30,' ') ||
				'H' ||
				rpad(creaNumero(haber),15,' ') ||
				rpad(' ',17,' ') ||
				rpad(creaNumero(haber),15,' ') ||
				regexp_replace(fecha, '/', '', 'g') as campo
			from asiento01
			where debe = '' AND nro_asiento = '$asiento'";

	$result = pg_query($con,$sql);
	$file = fopen("files/asiento-".$asiento."2.imp","w");
	while($row = pg_fetch_assoc($result)) {
		fputs($file,$row['campo']."\r\n");
	}
	fclose($file);
}

function vaciar_dir(){
	$files = glob('files/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file){
    	if(is_file($file))
	    unlink($file); //elimino el fichero
	}
}

function crea_zip(){
	$zip = new ZipArchive();
	$ret = $zip->open('files/asientos.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	if ($ret !== TRUE) {
    	printf('Erróneo con el código %d', $ret);
	} else {
    	$zip->addGlob('files/*.{imp}', GLOB_BRACE);
	    $zip->close();
	}
}

function enviar(){
	$zipname = "asientos.zip";
	$zippath = "files/asientos.zip";
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename="'.$zipname.'"');
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($zippath));
	ob_end_flush();
	@readfile($zippath);
}
?>
