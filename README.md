# Consulta de Cedula Venezuela 
## cedulavenezuela

Es un paquete desarrollado en PHP, desarrollada para poder consultas datos personales de los Habitantes de Venezuela registrados en el CNE y en el IVSS.

### Requiere de
* [guzzlehttp/guzzle: ^7.4](https://packagist.org/packages/guzzlehttp/guzzle)

### Instalación 
```php
composer require kaizerenrique/cedulavenezuela
```

### :scroll: Ejemplos 

#### consultar
```
/** 
* Esta función realiza una consulta a la Pagina del CNE
* @param string   $nac 	Valores permitidos [V|E]
* @param string   $ci 	Número de Cédula de Identidad
*
* @return Retorna un array.
*/

public function consultar($nac, $ci)
```

#### ivssPension
```
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
```

#### cuentaIndividual
```
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
```

#### ✒️ Autores 
* [kaizerenrique](https://github.com/kaizerenrique)

#### 📝 Licencia