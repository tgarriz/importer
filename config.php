<?php
function getdb(){
	$db=pg_connect('host=192.168.122.30 user=postgres password=postgres dbname=asientos connect_timeout=5') or die('connection failed');
	return $db;
}
function get_all_records(){
        $con = getdb();
        $Sql = "SELECT * FROM asiento01";
        $result = pg_query($con, $Sql);  
        if (pg_num_rows($result) > 0) {
         echo "<div class='table-responsive'><table id='myTable' class='table table-striped table-bordered'>
                 <thead><tr><th>NRO_ASIENTO</th>
                              <th>FECHA</th>
                              <th>CONCEPTO</th>
                              <th>TIPO</th>
                              <th>COD_CTA</th>
                              <th>LEYENDA</th>
                              <th>DEBE</th>
                              <th>HABER</th>
                            </tr></thead><tbody>";
         while($row = pg_fetch_assoc($result)) {
             echo "<tr><td>" . $row['nro_asiento']."</td>
                       <td>" . $row['fecha']."</td>
                       <td>" . $row['concepto']."</td>
                       <td>" . $row['tipo']."</td>
                       <td>" . $row['cod_cta']."</td>       
                       <td>" . $row['leyenda']."</td>
                       <td>" . $row['debe']."</td>
                       <td>" . $row['haber']."</td></tr>";        
         }
         echo "</tbody></table></div>";
    	} else {
         echo "no hay registros";
    	}
	pg_close($con);
    }
?>
