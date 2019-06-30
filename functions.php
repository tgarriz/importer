<?php
include 'config.php';

if(isset($_POST["Import"])){
	$con = getdb();
    $result = pg_query($con,'truncate asiento01');
	if(!isset($result)) {
		echo 'Error truncando table';
	}
	$filename=$_FILES["file"]["tmp_name"];
    revisacsv($filename);	
     if($_FILES["file"]["size"] > 0) {
			$file = fopen($filename, "r");
			$reg = 0;
			while (($getData = fgetcsv($file, 10000, ";")) !== FALSE){
               $sql = "INSERT into asiento01 (id,nro_asiento,fecha,concepto,tipo,cod_cta,leyenda,debe,haber) 
		  values ('".++$reg."','".$getData[0]."','".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."')";
              $result = pg_query($con, $sql);
          	  if(!isset($result)) {
            	echo "<script type=\"text/javascript\">
              	alert(\"CSV Invalido.\");
	            window.location = \"index.php\"
      	        </script>";    
			  } else {
				/*	$result2 = pg_query($con,"update asiento01 set debe = '' where debe = '0,00'");
					if(!isset($result2)){
						echo "error en result2";
						exit();
					}
					$result3 = pg_query($con,"update asiento01 set haber = '' where debe = '0,00'");
					if(!isset($result3)){
						echo "error en result3";
						exit();
					}*/
      	        echo "<script type=\"text/javascript\">
              	alert(\"CSV Importado.\");
	            window.location = \"index.php\"
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
			where debe != '' AND debe != '0,00' AND nro_asiento = '$asiento' 
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
			where haber != '' AND haber != '0,00' AND nro_asiento = '$asiento'";

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

function revisacsv($filename){
	$file = fopen($filename, "r") or exit("No subio csv");
	$nro_linea = 1;
	$max = count(file($filename));
	//echo "max es ".$max;
	while(!feof($file) and $nro_linea<$max){
		if(!revisaLinea(fgets($file))){
			echo "Error en CSV, linea ".$nro_linea;
			exit();	
		}
		$nro_linea++;

	}
	fclose($file);
}

function revisaLinea($line){
	return (substr_count($line,';') == 7) ? true : false;
}
?>
