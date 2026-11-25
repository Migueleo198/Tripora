<?php

require_once __DIR__ . '/../librerias/Sesion.php';

// Administración de hoteles (solo admin). La vista de hoteles para clientes irá aparte
class Hoteles extends Controlador
{
    private $hotelesModelo;
    private $viajesModelo;
    private $usuario = null;

    public function __construct()
    {
        $this->hotelesModelo = $this->modelo('HotelesModelo');
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

    // Cargar la vista añadiendo el usuario de la sesión y la sección del menú
    private function vistaHoteles($vista, $datos = [])
    {
        $datos['usuarioSesion'] = $this->usuario;
        $datos['menuActivo'] = $datos['menuActivo'] ?? 'Hoteles';
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
        $this->vistaHoteles($vista, $datos);
    }

    // Recoger los datos del formulario de hotel
    private function datosFormulario()
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'ciudad' => trim($_POST['ciudad'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estrellas' => trim($_POST['estrellas'] ?? ''),
            'precio_noche' => trim($_POST['precio_noche'] ?? '')
        ];
    }

    // Validar los datos del formulario de hotel (estrellas opcional de 1 a 5, precio por noche opcional >= 0)
    private function datosValidos($datos)
    {
        return $datos['nombre'] !== '' &&
            $datos['direccion'] !== '' &&
            $datos['ciudad'] !== '' &&
            (
                $datos['estrellas'] === '' ||
                filter_var($datos['estrellas'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) !== false
            ) &&
            (
                $datos['precio_noche'] === '' ||
                (is_numeric($datos['precio_noche']) && (float) $datos['precio_noche'] >= 0)
            );
    }

    // Listar hoteles: /hoteles?pagina=0
    public function index()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $pagina = max(0, (int) ($_GET['pagina'] ?? 0));

        $this->vistaHoteles('Hoteles/index', [
            'hoteles' => $this->hotelesModelo->obtenerHoteles($pagina, 10),
            'error' => $_GET['error'] ?? null
        ]);
    }

    // Listar hoteles en json: /hoteles/listar?pagina=0
    public function listar()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $pagina = max(0, (int) ($_GET['pagina'] ?? 0));
        $hoteles = $this->hotelesModelo->obtenerHoteles($pagina, 10);

        $this->vistaApi([
            'ok' => true,
            'hoteles' => $hoteles->datos,
            'numPaginas' => $hoteles->numPaginas,
            'paginaActual' => $hoteles->paginaActual
        ]);
    }

    // Obtener un hotel en json: /hoteles/obtener/1
    public function obtener($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            $this->vistaApi(['ok' => false, 'error' => 'El hotel no existe.'], 404);
        }

        $this->vistaApi([
            'ok' => true,
            'hotel' => $hotel
        ]);
    }

    // Mostrar un hotel: /hoteles/ver/1 (cualquier usuario con sesión; las acciones solo las ve el admin)
    public function ver($id_hotel = null)
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            redireccionar(($this->usuario['rol'] ?? '') === 'ADMIN' ? '/hoteles' : '/hoteles/vistaCliente');
            return;
        }

        // El cliente tiene su propia entrada "Hoteles" en el menú
        $this->vistaHoteles('Hoteles/ver', [
            'hotel' => $hotel,
            // Comidas que se pueden elegir al reservar, con lo que suman por noche
            'regimenes' => $this->hotelesModelo->obtenerRegimenes($hotel->id_hotel),
            'menuActivo' => ($this->usuario['rol'] ?? '') === 'ADMIN' ? 'Hoteles' : 'HotelesVistaCliente',
            // Error de la reserva (ReservasHoteles::reservar vuelve aquí con ?error=)
            'error' => $_GET['error'] ?? null
        ]);
    }

    // Formulario y creación: /hoteles/crear
    public function crear()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hotel = $this->datosFormulario();

            if (!$this->datosValidos($hotel)) {
                $this->responderError(
                    'Hoteles/crearHotel',
                    ['hotel' => (object) $hotel],
                    'Introduce un nombre, dirección y ciudad. Si las indicas, estrellas de 1 a 5 y precio por noche de 0 o más.'
                );
                return;
            }

            $id_hotel = $this->hotelesModelo->crearHotel($hotel);

            if ($id_hotel) {
                $this->responderExito('/hoteles', [
                    'hotel' => $this->hotelesModelo->obtenerHotel($id_hotel)
                ]);
                return;
            }

            $this->responderError(
                'Hoteles/crearHotel',
                ['hotel' => (object) $hotel],
                'No se pudo crear el hotel.',
                500
            );
            return;
        }

        $this->vistaHoteles('Hoteles/crearHotel');
    }

    // Formulario y actualización: /hoteles/editar/1
    public function editar($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El hotel no existe.'], 404);
            }

            redireccionar('/hoteles');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->datosFormulario();
            $hotelFormulario = (object) array_merge((array) $hotel, $datos);

            if (!$this->datosValidos($datos)) {
                $this->responderError(
                    'Hoteles/editarHotel',
                    ['hotel' => $hotelFormulario],
                    'Introduce un nombre, dirección y ciudad. Si las indicas, estrellas de 1 a 5 y precio por noche de 0 o más.'
                );
                return;
            }

            if ($this->hotelesModelo->actualizarHotel($id_hotel, $datos)) {
                $this->responderExito('/hoteles', [
                    'hotel' => $hotelFormulario
                ]);
                return;
            }

            $this->responderError(
                'Hoteles/editarHotel',
                ['hotel' => $hotelFormulario],
                'No se pudo actualizar el hotel.',
                500
            );
            return;
        }

        $this->vistaHoteles('Hoteles/editarHotel', [
            'hotel' => $hotel
        ]);
    }

    // Viajes del hotel y asociación a un viaje nuevo: /hoteles/viajes/1
    public function viajes($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            redireccionar('/hoteles');
            return;
        }

        $ruta = '/hoteles/viajes/' . (int) $id_hotel;
        $error = null;

        // Solo se puede incluir en el viaje un régimen que ofrezca el hotel
        $regimenes = $this->hotelesModelo->obtenerRegimenes($id_hotel);
        $siglasRegimen = array_column($regimenes, 'regimen');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_viaje = (int) ($_POST['id_viaje'] ?? 0);
            $noches = filter_var($_POST['noches'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 255]]);
            $regimen = $_POST['regimen'] ?? '';

            if (!$this->viajesModelo->obtenerViaje($id_viaje) || $noches === false) {
                $error = 'Elige un viaje e indica al menos 1 noche.';
            } elseif (!in_array($regimen, $siglasRegimen, true)) {
                $error = 'Elige uno de los regímenes de comidas del hotel.';
            } else {
                try {
                    $this->hotelesModelo->asociarViaje($id_hotel, $id_viaje, $noches, $regimen);
                    redireccionar($ruta);
                    return;
                } catch (PDOException $e) {
                    $error = 'El hotel ya está en ese viaje.';
                }
            }
        }

        $viajesHotel = $this->hotelesModelo->obtenerViajesHotel($id_hotel);
        $idsAsociados = array_map(fn($viaje) => (int) $viaje->id_viaje, $viajesHotel);

        // En el desplegable solo los viajes en los que todavía no está el hotel
        $viajesDisponibles = array_filter(
            $this->viajesModelo->obtenerViajes(),
            fn($viaje) => !in_array((int) $viaje->id_viaje, $idsAsociados, true)
        );

        $this->vistaHoteles('Hoteles/viajes', [
            'hotel' => $hotel,
            'viajesHotel' => $viajesHotel,
            'viajesDisponibles' => $viajesDisponibles,
            'regimenes' => $regimenes,
            'error' => $error ?? ($_GET['error'] ?? null)
        ]);
    }

    // Regímenes de comidas que ofrece el hotel y su suplemento por noche: /hoteles/regimenes/1
    // GET muestra los 5 regímenes con una casilla y un precio; POST guarda los marcados
    public function regimenes($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            redireccionar('/hoteles');
            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $marcados = (array) ($_POST['ofrece'] ?? []);
            $suplementos = (array) ($_POST['suplemento'] ?? []);
            $guardar = [];

            // Se recorren las siglas válidas, así no se puede colar un régimen que no existe
            foreach (array_keys(nombresRegimen()) as $sigla) {
                if (!in_array($sigla, $marcados, true)) {
                    continue;
                }

                // Solo alojamiento es el precio base: siempre 0
                $suplemento = $sigla === 'SA' ? 0 : filter_var($suplementos[$sigla] ?? '', FILTER_VALIDATE_FLOAT);

                if ($suplemento === false || $suplemento < 0) {
                    $error = 'El suplemento de cada régimen marcado tiene que ser un número de 0 o más.';
                    break;
                }

                $guardar[$sigla] = $suplemento;
            }

            if ($error === null && !$guardar) {
                $error = 'Marca al menos un régimen.';
            }

            if ($error === null) {
                if ($this->hotelesModelo->guardarRegimenes($hotel->id_hotel, $guardar)) {
                    redireccionar('/hoteles/regimenes/' . (int) $hotel->id_hotel . '?guardado=1');
                    return;
                }

                $error = 'No se pudieron guardar los regímenes.';
            }
        }

        // Regímenes guardados como sigla => suplemento, para marcar las casillas del formulario
        $actuales = [];

        foreach ($this->hotelesModelo->obtenerRegimenes($hotel->id_hotel) as $fila) {
            $actuales[$fila->regimen] = $fila->suplemento_noche;
        }

        $this->vistaHoteles('Hoteles/regimenes', [
            'hotel' => $hotel,
            'actuales' => $actuales,
            'error' => $error,
            'guardado' => isset($_GET['guardado'])
        ]);
    }

    // Quitar el hotel de un viaje: POST /hoteles/quitarViaje/1 con id_viaje
    public function quitarViaje($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $ruta = '/hoteles/viajes/' . (int) $id_hotel;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redireccionar($ruta);
            return;
        }

        try {
            $this->hotelesModelo->quitarViaje($id_hotel, (int) ($_POST['id_viaje'] ?? 0));
        } catch (PDOException $e) {
            redireccionar($ruta . '?error=' . urlencode('No se pudo quitar el hotel del viaje.'));
            return;
        }

        redireccionar($ruta);
    }

    public function vistaCliente(){

        if($this->comprobarSesion() === false){
            return;
        }
        $pagina = max(0, (int) ($_GET['pagina'] ?? 0));
        $vista = 'Hoteles/hotelesVistaCliente';
        $datos['usuarioSesion'] = $this->usuario;
        $datos['menuActivo'] = 'HotelesVistaCliente';
        $datos['hoteles'] = $this->hotelesModelo->obtenerHoteles($pagina, 10);

        // Con ?q= las tarjetas que no coinciden salen ocultas (como en la página principal)
        $busqueda = trim($_GET['q'] ?? '');
        $datos['busqueda'] = $busqueda;
        $datos['idsEncontrados'] = $busqueda === ''
            ? null
            : $this->hotelesModelo->buscarIdsHoteles($busqueda);

        $this->vista($vista, $datos);
    }

    // Eliminar: /hoteles/borrar/1
    public function borrar($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Método no permitido.'], 405);
            }

            redireccionar('/hoteles');
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El hotel no existe.'], 404);
            }

            redireccionar('/hoteles');
            return;
        }

        // Las FK de hotel_viaje y reserva_hotel son ON DELETE RESTRICT: si el hotel se usa, PDO lanza excepción
        try {
            $borrado = $this->hotelesModelo->borrarHotel($id_hotel);
            $error = $borrado ? null : 'No se pudo borrar el hotel.';
        } catch (PDOException $e) {
            $error = 'No se puede borrar «' . $hotel->nombre . '»: está en algún viaje o tiene reservas.';
        }

        if ($error !== null) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => $error], 409);
            }

            redireccionar('/hoteles?error=' . urlencode($error));
            return;
        }

        $this->responderExito('/hoteles', ['id_hotel' => (int) $id_hotel]);
    }

    // Viajes en json (para asignar hoteles a viajes): /hoteles/loadViajes
    public function loadViajes()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $this->vistaApi([
            'ok' => true,
            'viajes' => $this->viajesModelo->obtenerViajes()
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

    // Formulario y subida de imágenes (una o varias): /hoteles/subirImagen?id_hotel=1
    public function subirImagen()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $id_hotel = (int) ($_SERVER['REQUEST_METHOD'] === 'POST'
            ? ($_POST['id_hotel'] ?? 0)
            : ($_GET['id_hotel'] ?? 0));

        $datos = [
            'hoteles' => $this->hotelesModelo->obtenerHoteles(),
            'id_hotel' => $id_hotel,
            'imagenes' => $id_hotel ? imagenesHotel($id_hotel) : []
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->vistaHoteles('Hoteles/subirImagen', $datos);
            return;
        }

        if (!$this->hotelesModelo->obtenerHotel($id_hotel)) {
            $this->responderError('Hoteles/subirImagen', $datos, 'Selecciona un hotel válido.');
            return;
        }

        $archivos = $this->archivosSubidos('imagenes');

        if (empty($archivos)) {
            $this->responderError('Hoteles/subirImagen', $datos, 'No se ha recibido ninguna imagen.');
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
                $this->responderError('Hoteles/subirImagen', $datos, 'No se pudo subir «' . $archivo['name'] . '».');
                return;
            }

            $tipo = $finfo->file($archivo['tmp_name']);

            if (!isset($extensiones[$tipo])) {
                $this->responderError('Hoteles/subirImagen', $datos, '«' . $archivo['name'] . '» debe ser JPG, PNG o WEBP.');
                return;
            }

            $archivos[$i]['extension'] = $extensiones[$tipo];
        }

        $dir = dirImagenesHotel($id_hotel);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($archivos as $archivo) {
            // El nombre empieza por la fecha para que el orden alfabético sea el de subida
            $nombre = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $archivo['extension'];

            if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombre)) {
                $datos['imagenes'] = imagenesHotel($id_hotel);
                $this->responderError('Hoteles/subirImagen', $datos, 'No se pudo guardar «' . $archivo['name'] . '».', 500);
                return;
            }
        }

        $this->responderExito('/hoteles/subirImagen?id_hotel=' . $id_hotel, [
            'imagenes' => array_column(imagenesHotel($id_hotel), 'url')
        ]);
    }

    // Eliminar una imagen de un hotel: POST /hoteles/borrarImagen/1 con imagen=archivo
    public function borrarImagen($id_hotel = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $ruta = '/hoteles/subirImagen?id_hotel=' . (int) $id_hotel;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Método no permitido.'], 405);
            }

            redireccionar($ruta);
            return;
        }

        if (!borrarImagenHotel($id_hotel, $_POST['imagen'] ?? '')) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'La imagen no existe.'], 404);
            }

            redireccionar($ruta);
            return;
        }

        $this->responderExito($ruta, [
            'imagenes' => array_column(imagenesHotel($id_hotel), 'url')
        ]);
    }
}
