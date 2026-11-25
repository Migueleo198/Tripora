<?php

    //Para redireccionar la pagina
    function redireccionar($pagina){
        header('location: '.RUTA_URL.$pagina);
    }

    
    function tienePrivilegios($rol_usuario,$rolesPermitidos){
        // si $rolesPermitidos es vacio, se tendran privilegios
        if (empty($rolesPermitidos) || in_array($rol_usuario, $rolesPermitidos)) {
            return true;
        }
    }


    function formatoFecha($fechaIngles){
        return ($fechaIngles) ? date("d/m/Y", strtotime($fechaIngles)) : '';     // Obetnemos el formato de fecha en español
    }


    // Carpeta con las imagenes de un viaje: public/img/viajes/{id}
    function dirImagenesViaje($id_viaje){
        return DIR_IMAGENES_VIAJES.'/'.(int) $id_viaje;
    }


    // Nombres de archivo de las imagenes de un viaje, en orden de subida
    function archivosImagenesViaje($id_viaje){
        $dir = dirImagenesViaje($id_viaje);

        if (!is_dir($dir)) {
            return [];
        }

        $archivos = array_filter(scandir($dir), function ($archivo) {
            return in_array(strtolower(pathinfo($archivo, PATHINFO_EXTENSION)), ['jpg', 'png', 'webp'], true);
        });

        sort($archivos);

        return $archivos;
    }


    // Imagenes de un viaje: [['archivo' => ..., 'url' => ...], ...]
    function imagenesViaje($id_viaje){
        $imagenes = [];

        foreach (archivosImagenesViaje($id_viaje) as $archivo) {
            $imagenes[] = [
                'archivo' => $archivo,
                'url' => RUTA_URL.'/img/viajes/'.(int) $id_viaje.'/'.rawurlencode($archivo)
            ];
        }

        return $imagenes;
    }

    // Carpeta con las imagenes de un hotel: public/img/hoteles/{id}
    function dirImagenesHotel($id_hotel){
        return DIR_IMAGENES_HOTELES.'/'.(int) $id_hotel;
    }


    // Nombres de archivo de las imagenes de un hotel, en orden de subida
    function archivosImagenesHotel($id_hotel){
        $dir = dirImagenesHotel($id_hotel);

        if (!is_dir($dir)) {
            return [];
        }

        $archivos = array_filter(scandir($dir), function ($archivo) {
            return in_array(strtolower(pathinfo($archivo, PATHINFO_EXTENSION)), ['jpg', 'png', 'webp'], true);
        });

        sort($archivos);

        return $archivos;
    }


    // Imagenes de un hotel: [['archivo' => ..., 'url' => ...], ...]
    function imagenesHotel($id_hotel){
        $imagenes = [];

        foreach (archivosImagenesHotel($id_hotel) as $archivo) {
            $imagenes[] = [
                'archivo' => $archivo,
                'url' => RUTA_URL.'/img/hoteles/'.(int) $id_hotel.'/'.rawurlencode($archivo)
            ];
        }

        return $imagenes;
    }


    // Devuelve la url de la primera imagen de un hotel (portada), o null si no tiene
    function imagenHotel($id_hotel){
        return imagenesHotel($id_hotel)[0]['url'] ?? null;
    }


    // Elimina una imagen de un hotel. Solo se aceptan archivos que existan en su carpeta
    function borrarImagenHotel($id_hotel, $archivo){
        if (!in_array($archivo, archivosImagenesHotel($id_hotel), true)) {
            return false;
        }

        return unlink(dirImagenesHotel($id_hotel).'/'.$archivo);
    }


    // Devuelve la url de la primera imagen de un viaje (portada), o null si no tiene
    function imagenViaje($id_viaje){
        return imagenesViaje($id_viaje)[0]['url'] ?? null;
    }


    // Elimina una imagen de un viaje. Solo se aceptan archivos que existan en su carpeta
    function borrarImagenViaje($id_viaje, $archivo){
        if (!in_array($archivo, archivosImagenesViaje($id_viaje), true)) {
            return false;
        }

        return unlink(dirImagenesViaje($id_viaje).'/'.$archivo);
    }


    // Elimina todas las imagenes de un viaje y su carpeta
    function borrarImagenesViaje($id_viaje){
        foreach (archivosImagenesViaje($id_viaje) as $archivo) {
            unlink(dirImagenesViaje($id_viaje).'/'.$archivo);
        }

        if (is_dir(dirImagenesViaje($id_viaje))) {
            @rmdir(dirImagenesViaje($id_viaje));
        }
    }


    // Comprueba si la peticion llega desde fetch y espera json
    function esPeticionApi(){
        $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        return $ajax || $json;
    }


    // Escapar texto para mostrarlo en las vistas
    function e($texto){
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }

    // Clase 'active' para el enlace del menú de la sección actual ($datos['menuActivo'])
    function menuActivo($datos, $seccion){
        return ($datos['menuActivo'] ?? '') === $seccion ? 'active' : '';
    }

    // Regímenes de comidas de los hoteles: sigla que se guarda en la BD => nombre que se muestra
    function nombresRegimen(){
        return [
            'SA' => 'Solo alojamiento',
            'AD' => 'Alojamiento y desayuno',
            'MP' => 'Media pensión',
            'PC' => 'Pensión completa',
            'TI' => 'Todo incluido',
        ];
    }

    // Comprobar formato y letra de control de un DNI (8 números + letra) o NIE (X/Y/Z + 7 números + letra)
    function dniValido($dni){
        if (!preg_match('/^([0-9]{8}|[XYZ][0-9]{7})[A-Z]$/', $dni)) {
            return false;
        }

        // En el NIE la letra inicial cuenta como número: X=0, Y=1, Z=2
        $numero = (int) strtr(substr($dni, 0, -1), ['X' => '0', 'Y' => '1', 'Z' => '2']);

        return 'TRWAGMYFPDXBNJZSQVHLCKE'[$numero % 23] === substr($dni, -1);
    }

    // Validar los datos de un usuario (formulario del admin y registro), devuelve el mensaje de error o null
    function validarUsuario($datos, $contraseñaObligatoria = true){
        foreach (['nombre', 'apellidos', 'DNI', 'email', 'telefono', 'fecha_nac'] as $campo) {
            if ($datos[$campo] === '') {
                return 'Rellena todos los campos.';
            }
        }

        if (!dniValido($datos['DNI'])) {
            return 'El DNI o NIE no es válido.';
        }

        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            return 'El email no es válido.';
        }

        if (!in_array($datos['rol'], ['ADMIN', 'CLIENTE'], true)) {
            return 'El rol no es válido.';
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha_nac']);

        if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_nac'] || $fecha > new DateTime()) {
            return 'La fecha de nacimiento no es válida.';
        }

        // Al editar es opcional, pero si se escribe una nueva también debe cumplir el mínimo
        if (($contraseñaObligatoria || $datos['contraseña'] !== '') && strlen($datos['contraseña']) < 6) {
            return 'La contraseña debe tener al menos 6 caracteres.';
        }

        return null;
    }
