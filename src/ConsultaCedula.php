<?php 

namespace Kaizerenrique\Cedulavenezuela;

use Illuminate\Support\Facades\Http;

class ConsultaCedula{

	/**
	* Esta función realiza una consulta a la Pagina del CNE para comprobar la nacionalidad
	* y el numero de cedula .
	* @param string   $nac 	Valores permitidos [V|E]
	* @param string   $ci 	Número de Cédula de Identidad
	*
	* @return Retorna un array.
	*/	

	public function consultar($nac, $ci)
	{
		try {
			$url = 'http://www.cne.gov.ve/web/registro_electoral/ce.php?nacionalidad=';
			$response = Http::get($url.$nac.'&cedula='.$ci);

			$respuesta = $response->getBody()->getContents();// accedemos a el contenido
			$text = strip_tags($respuesta); //limpiamos

			// Identifica si es población Votante
			$findme = 'DATOS DEL ELECTOR'; 
			$pos = strpos($text, $findme);
			$findme2 = 'ADVERTENCIA';
        	$pos2 = strpos($text, $findme2);

	        if ($pos == TRUE AND $pos2 == FALSE) {
	            // Codigo buscar votante
	            $rempl = array('Cédula:', 'Nombre:', 'Estado:', 'Municipio:', 'Parroquia:', 'Centro:',
	            	'Dirección:', 'SERVICIO ELECTORAL', 'Mesa:');

	            $r = trim(str_replace($rempl, '|', self::limpiarCampo($text)));
	            $resource = explode("|", $r);
	            $datos = explode(" ", self::limpiarCampo($resource[2]));

	            // definir nombre y apellidos
	            switch (count($datos)) {
	            	case '2':
	            		$nombres = $datos[0];
	            		$apellidos = $datos[1];
	            	break;
	            	
	            	case '3':
	            		$nombres = $datos[0] . ' ' . $datos[1];
	            		$apellidos = $datos[2];
	            	break;

	            	case '4':
	            		$nombres = $datos[0] . ' ' . $datos[1];
	            		$apellidos = $datos[2]. ' ' . $datos[3];
	            	break;

	            	default:
	            		$count = count($datos);
		                $mitad = round($count / 2);
		                $nombres = $apellidos = '';
		                for ($i = 0; $i < $mitad; $i++) {
		                    $nombres .= $datos[$i].' ';
		                }
		                for ($i = $mitad; $i < $count; $i++) {
		                    $apellidos .= $datos[$i].' ';
		                }
	            	break;
	            }

	            $datoJson = array('error' => 0, 'nacionalidad' => $nac, 'cedula' => $ci, 
	            	'nombres' => $nombres,
	            	'apellidos' => $apellidos,
	            	'inscrito' => 'SI', 'cvestado' => self::limpiarCampo($resource[3]), 
	            	'cvmunicipio' => self::limpiarCampo($resource[4]), 
	            	'cvparroquia' => self::limpiarCampo($resource[5]), 
	            	'centro' => self::limpiarCampo($resource[6]), 
	            	'direccion' => self::limpiarCampo($resource[7]));

	        } elseif ($pos == FALSE AND $pos2 == FALSE) {
	            // Codigo buscar votante
	            $rempl = array('Cédula:', 'Primer Nombre:', 'Segundo Nombre:', 'Primer Apellido:', 
	            	'Segundo Apellido:', 'ESTATUS');
	            $r = trim(str_replace($rempl, '|', $text));
	            $resource = explode("|", $r);

	            $datoJson = array('error' => 2, 'nacionalidad' => NULL, 'cedula' => $ci, 
	            	'nombres' => NULL, 'apellidos' => NULL, 'inscrito' => 'NO -(de la linea 31)');

	        } elseif ($pos == FALSE AND $pos2 == TRUE) {
	            $datoJson = array('error' => 1, 'nacionalidad' => $nac, 'cedula' => $ci, 
	            	'nombres' => NULL, 'apellidos' => NULL, 'inscrito' => 'NO -(de la linea 33)');
	        }

			return $datoJson;
        
	    } catch (\Illuminate\Http\Client\ConnectionException $e) {
	        report($e);	 
	        return false;
	    }

		
	}

	public static function limpiarCampo($valor) {//Con esto limpiamos los errores de la pagina
        $rempl = array('\n', '\t');
        $r = trim(str_replace($rempl, ' ', $valor));
        return str_replace("\r", "", str_replace("\n", "", str_replace("\t", "", $r)));
    }

    /**
    * Esta función consulta si una persona es pensionada del IVSS.
    * @param string   $nac 	Valores permitidos [V|E]
	* @param string   $ci 	Número de Cédula de Identidad
	* @param string   $d1 	Dia de Nacimiento  	
	* @param string   $m1 	Mes de Nacimiento
	* @param string   $y1 	Año de Nacimiento 
    *
    * @return Retorna un array.
    */
    
    public function ivssPension($nac, $ci, $d1, $m1, $y1)
    {
    	try {
	    	$url = 'http://www.ivss.gob.ve:28080/Pensionado/PensionadoCTRL?boton=Consultar&nacionalidad=';

	    	$response = Http::get(
				$url.$nac.'&cedula='.$ci.'&d1='.$d1.'&m1='.$m1.'&y1='.$y1);

			$respuesta = $response->getBody()->getContents();// accedemos a el contenido
			$text = strip_tags($respuesta); //limpiamos

			$findme = 'Datos del Asegurado'; 
			$pos = strpos($text, $findme);
			$findme2 = 'no tiene';
	        $pos2 = strpos($text, $findme2);

	        if ($pos == TRUE AND $pos2 == FALSE) {
	        	$rempl = array('Tipo de Pensión:', 'Cédula:', 'Apellido y nombre:', 'Entidad Financiera:', 'Estatus de la Pensión:');
	        	$r = trim(str_replace($rempl, '|', self::limpiarCampo($text)));
	            $resource = explode("|", $r);
	            $datos = explode(" ", self::limpiarCampo($resource[1]));
	            $datos1 = explode(" ", self::limpiarCampo($resource[2]));

	            // definir nombre y apellidos
	            switch (count($datos)) {
	            	case '2':
	            		$nombres = $datos[0];
	            		$apellidos = $datos[1];
	            	break;
	            	
	            	case '3':
	            		$nombres = $datos[0] . ' ' . $datos[1];
	            		$apellidos = $datos[2];
	            	break;

	            	case '4':
	            		$nombres = $datos[0] . ' ' . $datos[1];
	            		$apellidos = $datos[2]. ' ' . $datos[3];
	            	break;

	            	default:
	            		$count = count($datos);
		                $mitad = round($count / 2);
		                $nombres = $apellidos = '';
		                for ($i = 0; $i < $mitad; $i++) {
		                    $nombres .= $datos[$i].' ';
		                }
		                for ($i = $mitad; $i < $count; $i++) {
		                    $apellidos .= $datos[$i].' ';
		                }
	            	break;
	            }

	            $datoJson = array(
	            	"status" => "1",
		            "pensionado" => "SI",
		            "mensaje" => $nombres.' '.$apellidos,);

	        } elseif ($pos == FALSE AND $pos2 == TRUE){
	        	$datoJson = array(
	        		"status" => "0",
		            "pensionado" => "NO",
		            "mensaje" => $pos2,);        	
	        }
        
        return $datoJson;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
	        report($e);	 
	        return false;
	        
	    }
    }

    /**
    * Esta función consulta si una persona posee cuenta del IVSS.
    * @param string   $nac 	Valores permitidos [V|E]
	* @param string   $ci 	Número de Cédula de Identidad
	* @param string   $d 	Dia de Nacimiento  	
	* @param string   $m 	Mes de Nacimiento
	* @param string   $y 	Año de Nacimiento 
    *
    * @return Retorna un array.
    */

    public function cuentaIndividual($nac, $ci, $d, $m, $y)
    {
    	try {
	    	$url = 'http://www.ivss.gob.ve:28083/CuentaIndividualIntranet/CtaIndividual_PortalCTRL?nacionalidad_aseg=';

	    	$response = Http::get(
				$url.$nac.'&cedula_aseg='.$ci.'&d='.$d.'&m='.$m.'&y='.$y);

	    	$respuesta = $response->getBody()->getContents();// accedemos a el contenido
			$text = strip_tags($respuesta); //limpiamos

			$findme = 'Datos del Asegurado'; 
			$pos = strpos($text, $findme);
			$findme2 = 'no esta registrada como asegurado';
	        $pos2 = strpos($text, $findme2);

	        if ($pos == TRUE AND $pos2 == FALSE) {        	
	        	$datoJson = array(
	        		"status" => "1",
		            "registrado" => "SI",
		        );
	        } elseif ($pos == FALSE AND $pos2 == TRUE){
	        	$datoJson = array(
	        		"status" => "0",
		            "registrado" => "NO",
		        );
	        }

	    	return $datoJson;

		} catch (\Illuminate\Http\Client\ConnectionException $e) {
	        report($e);	 
	        return false;
	        
	    }

    }

}