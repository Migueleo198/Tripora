<?php

require_once __DIR__ . '/../librerias/Sesion.php';

// Reservar hoteles y ver "Mis hoteles"
class ReservasHoteles extends Controlador
{
    private $reservasHotelesModelo;
    private $hotelesModelo;

    public function __construct()
    {
        $this->reservasHotelesModelo = $this->modelo('ReservasHotelesModelo');
        $this->hotelesModelo = $this->modelo('HotelesModelo');
    }

    // Mis hoteles: /reservasHoteles
    public function index()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        $id_usuario = $datos['usuarioSesion']['id_usuario'];

        $datos['menuActivo'] = 'MisHoteles';
        $datos['reservas'] = $this->reservasHotelesModelo->obtenerReservasUsuario($id_usuario);

        $this->vista('reservasHoteles/index', $datos);
    }

    // Reservar un hotel: POST /reservasHoteles/reservar/1 con fecha_entrada, noches y regimen
    public function reservar($id_hotel = null)
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        // Solo se reserva desde el formulario (POST), no escribiendo la url
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redireccionar('/hoteles/ver/' . (int) $id_hotel);
            return;
        }

        $hotel = $this->hotelesModelo->obtenerHotel($id_hotel);

        if (!$hotel) {
            redireccionar('/hoteles/vistaCliente');
            return;
        }

        $ruta = '/hoteles/ver/' . (int) $hotel->id_hotel;

        // Un hotel sin precio por noche solo se vende dentro de un viaje
        if ($hotel->precio_noche === null) {
            redireccionar($ruta . '?error=' . urlencode('Este hotel solo se puede contratar dentro de un viaje.'));
            return;
        }

        $fecha_entrada = $_POST['fecha_entrada'] ?? '';
        $noches = (int) ($_POST['noches'] ?? 0);

        // La fecha tiene que ser real (AAAA-MM-DD) y de hoy en adelante
        $fecha = DateTime::createFromFormat('Y-m-d', $fecha_entrada);

        if (!$fecha || $fecha->format('Y-m-d') !== $fecha_entrada || $fecha_entrada < date('Y-m-d')) {
            redireccionar($ruta . '?error=' . urlencode('Elige una fecha de entrada de hoy en adelante.'));
            return;
        }

        if ($noches < 1 || $noches > 30) {
            redireccionar($ruta . '?error=' . urlencode('Las noches tienen que ser de 1 a 30.'));
            return;
        }

        // El régimen de comidas tiene que ser uno de los que ofrece el hotel; de ahí sale su suplemento
        $regimen = $_POST['regimen'] ?? '';
        $suplemento = null;

        foreach ($this->hotelesModelo->obtenerRegimenes($hotel->id_hotel) as $fila) {
            if ($fila->regimen === $regimen) {
                $suplemento = $fila->suplemento_noche;
            }
        }

        if ($suplemento === null) {
            redireccionar($ruta . '?error=' . urlencode('Elige una de las opciones de comidas del hotel.'));
            return;
        }

        // El precio se calcula aquí con el de la base de datos, no se coge del formulario
        $precio_total = $noches * ($hotel->precio_noche + $suplemento);
        $id_usuario = $datos['usuarioSesion']['id_usuario'];

        $this->reservasHotelesModelo->crearReserva($id_usuario, $hotel->id_hotel, $fecha_entrada, $noches, $regimen, $precio_total);

        redireccionar('/reservasHoteles');
    }

    // Cancelar una reserva de hotel: POST /reservasHoteles/cancelar/1
    public function cancelar($id_reserva_hotel = null)
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = $datos['usuarioSesion']['id_usuario'];
            $this->reservasHotelesModelo->cancelarReserva($id_reserva_hotel, $id_usuario);
        }

        redireccionar('/reservasHoteles');
    }

    // Todas las reservas de hoteles (solo administrador): /reservasHoteles/admin?estado=CONFIRMADA
    public function admin()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        if ($datos['usuarioSesion']['rol'] !== 'ADMIN') {
            redireccionar('/inicio');
            return;
        }

        // Filtro por estado; si llega uno que no existe se muestran todas
        $estado = $_GET['estado'] ?? '';

        if (!in_array($estado, ['PENDIENTE', 'CONFIRMADA', 'CANCELADA'], true)) {
            $estado = '';
        }

        $datos['menuActivo'] = 'Reservas';
        $datos['estado'] = $estado;
        $datos['reservas'] = $this->reservasHotelesModelo->obtenerTodas($estado);

        $this->vista('reservasHoteles/admin', $datos);
    }

    // Cambiar el estado de una reserva (solo administrador): POST /reservasHoteles/cambiarEstado/1 con estado
    public function cambiarEstado($id_reserva_hotel = null)
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        if ($datos['usuarioSesion']['rol'] !== 'ADMIN') {
            redireccionar('/inicio');
            return;
        }

        $estado = $_POST['estado'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($estado, ['PENDIENTE', 'CONFIRMADA', 'CANCELADA'], true)) {
            $this->reservasHotelesModelo->cambiarEstado($id_reserva_hotel, $estado);
        }

        redireccionar('/reservasHoteles/admin');
    }
}
