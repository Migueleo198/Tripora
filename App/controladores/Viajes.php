<?php

require_once __DIR__ . '/../librerias/Sesion.php';

class Viajes extends Controlador
{
    private $viajesModelo;
    private $usuario = null;

    public function __construct()
    {
        $this->viajesModelo = $this->modelo('ViajesModelo');
    }

    // Comprobar que existe una sesión
    private function comprobarSesion()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Debes iniciar sesión.'], 401);
            }

            redireccionar('/login');
            return false;
        }

        $this->usuario = $datos['usuarioSesion'];

        return $this->usuario;
    }

    // Comprobar que el usuario es administrador
    private function comprobarAdmin()
    {
        $usuario = $this->comprobarSesion();

        if ($usuario === false) {
            return false;
        }

        if (($usuario['rol'] ?? '') !== 'ADMIN') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No tienes permisos.'], 403);
            }

            redireccionar('/inicio');
            return false;
        }

        return true;
    }

    // Cargar la vista añadiendo el usuario de la sesión para el header
    private function vistaViajes($vista, $datos = [])
    {
        $datos['usuarioSesion'] = $this->usuario;
        $datos['menuActivo'] = 'Viajes';

        // Nombres de los regímenes de comidas (SA => Solo alojamiento...), están en helpers/funciones.php
        $datos['regimenes_tipo'] = nombresRegimen();

        $this->vista($vista, $datos);
    }

    // Responder con éxito: json si viene de fetch, redirección si no
    private function responderExito($ruta, $datos = [])
    {
        if (esPeticionApi()) {
            $this->vistaApi(array_merge(
                ['ok' => true, 'redirect' => RUTA_URL . $ruta],
                $datos
            ));
        }

        redireccionar($ruta);
    }

    // Responder con error: json si viene de fetch, la vista con el error si no
    private function responderError($vista, $datos, $error, $codigo = 422)
    {
        if (esPeticionApi()) {
            $this->vistaApi(['ok' => false, 'error' => $error], $codigo);
        }

        $datos['error'] = $error;
        $this->vistaViajes($vista, $datos);
    }

    // Preparar un viaje para devolverlo en json
    private function viajeApi($viaje)
    {
        $viaje = (array) $viaje;
        $viaje['imagenes'] = array_column(imagenesViaje($viaje['id_viaje']), 'url');
        $viaje['imagen'] = $viaje['imagenes'][0] ?? null;

        return $viaje;
    }

    // Recoger los datos del formulario de viaje
    private function datosFormulario()
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'ubicacion' => trim($_POST['ubicacion'] ?? ''),
            'precio' => $_POST['precio'] ?? '',
            'dias' => $_POST['dias'] ?? '',
            'actividades' => $this->actividadesFormulario(),
            // Solo si el formulario incluye la tabla de actividades se tocan las del viaje
            'actividades_enviadas' => !empty($_POST['actividades_enviadas'])
        ];
    }

    // Actividades guardadas de un viaje en el formato del formulario (hora y duración HH:MM)
    private function actividadesParaFormulario($id_viaje)
    {
        $actividades = [];

        foreach ($this->viajesModelo->obtenerActividadesViaje($id_viaje) as $actividad) {
            $actividades[] = [
                'id_actividad' => (int) $actividad->id_actividad,
                'dia' => (string) $actividad->dia,
                'hora' => $actividad->hora === null ? '' : substr($actividad->hora, 0, 5),
                'duracion' => substr($actividad->duracion, 0, 5),
                'precio' => $actividad->precio,
                'opcional' => (bool) $actividad->opcional
            ];
        }

        return $actividades;
    }

    // Actividades marcadas en el formulario: actividades[id][incluir|dia|hora|duracion|precio|opcional]
    private function actividadesFormulario()
    {
        $actividades = [];

        foreach ($_POST['actividades'] ?? [] as $id_actividad => $actividad) {
            if (!is_array($actividad) || empty($actividad['incluir'])) {
                continue;
            }

            $actividades[] = [
                'id_actividad' => (int) $id_actividad,
                'dia' => trim($actividad['dia'] ?? ''),
                'hora' => trim($actividad['hora'] ?? ''),
                'duracion' => trim($actividad['duracion'] ?? ''),
                'precio' => trim($actividad['precio'] ?? ''),
                'opcional' => !empty($actividad['opcional'])
            ];
        }

        return $actividades;
    }

    // Validar las actividades elegidas: devuelve el mensaje de error o null
    // Día entre 1 y los días del viaje, duración obligatoria (> 0), hora opcional, precio >= 0
    private function errorActividades($actividades, $dias, $catalogo)
    {
        $nombres = array_column($catalogo, 'nombre', 'id_actividad');
        $hora = '/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/';

        foreach ($actividades as $actividad) {
            if (!isset($nombres[$actividad['id_actividad']])) {
                return 'Una de las actividades elegidas no existe.';
            }

            $nombre = $nombres[$actividad['id_actividad']];
            $diaValido = filter_var($actividad['dia'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => (int) $dias]]) !== false;

            if (
                !$diaValido ||
                !preg_match($hora, $actividad['duracion']) ||
                preg_match('/^00:00(:00)?$/', $actividad['duracion']) ||
                ($actividad['hora'] !== '' && !preg_match($hora, $actividad['hora'])) ||
                !is_numeric($actividad['precio']) ||
                (float) $actividad['precio'] < 0
            ) {
                return 'Revisa «' . $nombre . '»: día de 1 a ' . (int) $dias . ', duración, hora (opcional) y precio válidos.';
            }
        }

        return null;
    }

    // Validar los datos del formulario de viaje
    private function datosValidos($datos)
    {
        return $datos['nombre'] !== '' &&
            $datos['ubicacion'] !== '' &&
            is_numeric($datos['precio']) &&
            (float) $datos['precio'] >= 0 &&
            filter_var($datos['dias'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 255]]) !== false;
    }

    // Listar viajes: /viajes
    public function index()
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        $pagina = max(0, (int) ($_GET['pagina'] ?? 0));

        $this->vistaViajes('viajes/index', [
            'viajes' => $this->viajesModelo->obtenerViajes($pagina, 10)
            
        ]);
    }

    // Listar viajes en json: /viajes/listar?pagina=0&q=texto
    public function listar()
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        $pagina = max(0, (int) ($_GET['pagina'] ?? 0));
        $busqueda = trim($_GET['q'] ?? '');
        $viajes = $this->viajesModelo->obtenerViajes($pagina, 10, $busqueda);

        $this->vistaApi([
            'ok' => true,
            'viajes' => array_map([$this, 'viajeApi'], $viajes->datos),
            'numPaginas' => $viajes->numPaginas,
            'paginaActual' => $viajes->paginaActual
        ]);
    }

    // Obtener un viaje en json: /viajes/obtener/1
    public function obtener($id_viaje = null)
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        $viaje = $this->viajesModelo->obtenerViaje($id_viaje);

        if (!$viaje) {
            $this->vistaApi(['ok' => false, 'error' => 'El viaje no existe.'], 404);
        }

        $this->vistaApi([
            'ok' => true,
            'viaje' => $this->viajeApi($viaje)
        ]);
    }

    // Mostrar un viaje: /viajes/ver/1
    public function ver($id_viaje = null)
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        $viaje = $this->viajesModelo->obtenerViaje($id_viaje);

        if (!$viaje) {
            redireccionar('/viajes');
            return;
        }

        $this->vistaViajes('viajes/ver', [
            'viaje' => $viaje,
            'actividades' => $this->viajesModelo->obtenerActividadesViaje($id_viaje),
            'hoteles' => $this->viajesModelo->obtenerHotelesViaje($id_viaje)
        ]);
    }

    // Formulario y creación: /viajes/crear
    public function crear()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $catalogo = $this->viajesModelo->obtenerActividades();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $viaje = $this->datosFormulario();
            $datosVista = ['viaje' => (object) $viaje, 'actividades' => $catalogo];

            if (!$this->datosValidos($viaje)) {
                $this->responderError(
                    'viajes/crear',
                    $datosVista,
                    'Introduce un nombre, ubicación, precio y días válidos.'
                );
                return;
            }

            $errorActividades = $this->errorActividades($viaje['actividades'], $viaje['dias'], $catalogo);

            if ($errorActividades !== null) {
                $this->responderError('viajes/crear', $datosVista, $errorActividades);
                return;
            }

            $id_viaje = $this->viajesModelo->crearViaje($viaje);

            if ($id_viaje) {
                $this->responderExito('/viajes', [
                    'viaje' => $this->viajeApi($this->viajesModelo->obtenerViaje($id_viaje))
                ]);
                return;
            }

            $this->responderError(
                'viajes/crear',
                $datosVista,
                'No se pudo crear el viaje.',
                500
            );
            return;
        }

        $this->vistaViajes('viajes/crear', [
            'actividades' => $catalogo
        ]);
    }

    // Formulario y actualización: /viajes/editar/1
    public function editar($id_viaje = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $viaje = $this->viajesModelo->obtenerViaje($id_viaje);

        if (!$viaje) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El viaje no existe.'], 404);
            }

            redireccionar('/viajes');
            return;
        }

        $catalogo = $this->viajesModelo->obtenerActividades();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->datosFormulario();
            $viajeFormulario = (object) array_merge((array) $viaje, $datos);
            $datosVista = ['viaje' => $viajeFormulario, 'actividades' => $catalogo];

            if (!$this->datosValidos($datos)) {
                $this->responderError(
                    'viajes/editar',
                    $datosVista,
                    'Introduce un nombre, ubicación, precio y días válidos.'
                );
                return;
            }

            if ($datos['actividades_enviadas']) {
                $errorActividades = $this->errorActividades($datos['actividades'], $datos['dias'], $catalogo);

                if ($errorActividades !== null) {
                    $this->responderError('viajes/editar', $datosVista, $errorActividades);
                    return;
                }
            }

            try {
                $actualizado = $this->viajesModelo->actualizarViaje($id_viaje, $datos);
                $error = $actualizado ? null : 'No se pudo actualizar el viaje.';
            } catch (PDOException $e) {
                $error = 'No se puede quitar una actividad que ya han elegido clientes en sus reservas.';
            }

            if ($error === null) {
                $this->responderExito('/viajes', [
                    'viaje' => $this->viajeApi($viajeFormulario)
                ]);
                return;
            }

            $this->responderError('viajes/editar', $datosVista, $error, 409);
            return;
        }

        $viaje->actividades = $this->actividadesParaFormulario($id_viaje);

        $this->vistaViajes('viajes/editar', [
            'viaje' => $viaje,
            'actividades' => $catalogo
        ]);
    }

    // Eliminar: /viajes/borrar/1
    public function borrar($id_viaje = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Método no permitido.'], 405);
            }

            redireccionar('/viajes');
            return;
        }

        $viaje = $this->viajesModelo->obtenerViaje($id_viaje);

        if (!$viaje) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El viaje no existe.'], 404);
            }

            redireccionar('/viajes');
            return;
        }

        // Si el viaje tiene reservas, la FK lo impide y el modelo lanza PDOException
        try {
            $borrado = $this->viajesModelo->borrarViaje($id_viaje);
            $error = $borrado ? null : 'No se pudo borrar el viaje.';
        } catch (PDOException $e) {
            $error = 'No se puede borrar «' . $viaje->nombre . '»: tiene reservas.';
        }

        if ($error !== null) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => $error], 409);
            }

            redireccionar('/viajes');
            return;
        }

        borrarImagenesViaje($id_viaje);

        $this->responderExito('/viajes', [
            'id_viaje' => (int) $id_viaje
        ]);
    }

    // Normalizar $_FILES['imagenes'] (input multiple) a una lista de archivos
    private function archivosSubidos($campo)
    {
        $subida = $_FILES[$campo] ?? null;

        if (!$subida || !is_array($subida['name'])) {
            return [];
        }

        $archivos = [];

        foreach ($subida['name'] as $i => $nombre) {
            if ($subida['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $archivos[] = [
                'name' => $nombre,
                'tmp_name' => $subida['tmp_name'][$i],
                'error' => $subida['error'][$i]
            ];
        }

        return $archivos;
    }

    // Formulario y subida de imágenes (una o varias): /viajes/subirImagen?id_viaje=1
    public function subirImagen()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $id_viaje = (int) ($_SERVER['REQUEST_METHOD'] === 'POST'
            ? ($_POST['id_viaje'] ?? 0)
            : ($_GET['id_viaje'] ?? 0));

        $datos = [
            'viajes' => $this->viajesModelo->obtenerViajes(),
            'id_viaje' => $id_viaje,
            'imagenes' => $id_viaje ? imagenesViaje($id_viaje) : []
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->vistaViajes('viajes/subirImagen', $datos);
            return;
        }

        if (!$this->viajesModelo->obtenerViaje($id_viaje)) {
            $this->responderError('viajes/subirImagen', $datos, 'Selecciona un viaje válido.');
            return;
        }

        $archivos = $this->archivosSubidos('imagenes');

        if (empty($archivos)) {
            $this->responderError('viajes/subirImagen', $datos, 'No se ha recibido ninguna imagen.');
            return;
        }

        // Comprobar el tipo real de cada archivo, no el que envía el navegador
        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        // Se validan todas antes de guardar ninguna
        foreach ($archivos as $i => $archivo) {
            if ($archivo['error'] !== UPLOAD_ERR_OK) {
                $this->responderError('viajes/subirImagen', $datos, 'No se pudo subir «' . $archivo['name'] . '».');
                return;
            }

            $tipo = $finfo->file($archivo['tmp_name']);

            if (!isset($extensiones[$tipo])) {
                $this->responderError('viajes/subirImagen', $datos, '«' . $archivo['name'] . '» debe ser JPG, PNG o WEBP.');
                return;
            }

            $archivos[$i]['extension'] = $extensiones[$tipo];
        }

        $dir = dirImagenesViaje($id_viaje);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($archivos as $archivo) {
            // El nombre empieza por la fecha para que el orden alfabético sea el de subida
            $nombre = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $archivo['extension'];

            if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombre)) {
                $datos['imagenes'] = imagenesViaje($id_viaje);
                $this->responderError('viajes/subirImagen', $datos, 'No se pudo guardar «' . $archivo['name'] . '».', 500);
                return;
            }
        }

        $this->responderExito('/viajes/ver/' . $id_viaje, [
            'imagenes' => array_column(imagenesViaje($id_viaje), 'url')
        ]);
    }

    // Eliminar una imagen de un viaje: POST /viajes/borrarImagen/1 con imagen=archivo
    public function borrarImagen($id_viaje = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $ruta = '/viajes/subirImagen?id_viaje=' . (int) $id_viaje;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Método no permitido.'], 405);
            }

            redireccionar($ruta);
            return;
        }

        if (!borrarImagenViaje($id_viaje, $_POST['imagen'] ?? '')) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'La imagen no existe.'], 404);
            }

            redireccionar($ruta);
            return;
        }

        $this->responderExito($ruta, [
            'imagenes' => array_column(imagenesViaje($id_viaje), 'url')
        ]);
    }
}
