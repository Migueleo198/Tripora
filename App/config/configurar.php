<?php

    // Ruta de la aplicacion
    define('RUTA_APP', dirname(dirname(__FILE__)));

    // Ruta url, Ejemplo: http://localhost/daw2_mvc
    define('RUTA_URL', '');

    define('NOMBRE_SITIO', 'CRUD MVC - DAW2 Alcañiz 2024-2025');


    // Configuracion de la Base de Datos
    // En Docker llegan por variables de entorno (docker-compose.yml); en XAMPP se usan los valores de siempre
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USUARIO', getenv('DB_USUARIO') ?: 'root');
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
    define('DB_NOMBRE', getenv('DB_NOMBRE') ?: 'tripora');

    // Configuracion Tamaño de pagina en la paginacion
    define('TAM_PAGINA', 3);

    // Carpeta donde se guardan las imagenes de los viajes (dentro de public)
    define('DIR_IMAGENES_VIAJES', dirname(RUTA_APP) . '/public/img/viajes');
      
    //Lo mismo pero para hoteles
    define('DIR_IMAGENES_HOTELES', dirname(RUTA_APP) . '/public/img/hoteles');

    // Duracion de la sesion
    define('TMP_SESION', 365 * 24 * 60 * 60);   // un año

    // Direccion completa de la web, para los enlaces que van en los correos
    define('URL_SITIO', getenv('URL_SITIO') ?: 'http://tripora.local');

    // Correo (SMTP). Con Gmail: CORREO_PASSWORD es una "contraseña de aplicación" de la cuenta
    // Si CORREO_USUARIO esta vacio no se envia nada: el correo se guarda en App/logs/correos.log para pruebas
    define('CORREO_HOST', 'smtp.gmail.com');
    define('CORREO_PUERTO', 587);
    // El usuario y la contraseña van en correo_credenciales.php, que no se sube a git (.gitignore)
    if (file_exists(__DIR__ . '/correo_credenciales.php')) {
        require __DIR__ . '/correo_credenciales.php';
    }

    // Si no hay archivo (por ejemplo en Docker), se cogen de las variables de entorno
    if (!defined('CORREO_USUARIO')) {
        define('CORREO_USUARIO', getenv('CORREO_USUARIO') ?: '');
        define('CORREO_PASSWORD', getenv('CORREO_PASSWORD') ?: '');
    }
    define('CORREO_NOMBRE', 'Tripora');

    // Horas que valen el enlace y el código para cambiar la contraseña
    define('HORAS_TOKEN', 3);

    // Veces que se puede escribir mal el código antes de que deje de valer
    define('INTENTOS_CODIGO', 5);
