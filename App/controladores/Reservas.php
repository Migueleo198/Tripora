<?php

require_once __DIR__ . '/../librerias/Sesion.php';

// Contratar viajes y ver "Mis viajes"
class Reservas extends Controlador
{
    private $reservasModelo;
    private $viajesModelo;

    public function __construct()
    {
        $this->reservasModelo = $this->modelo('ReservasModelo');
        $this->viajesModelo = $this->modelo('ViajesModelo');
    }

    // Mis viajes: /reservas
    public function index()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        $id_usuario = $datos['usuarioSesion']['id_usuario'];

        $datos['menuActivo'] = 'MisViajes';
        $datos['reservas'] = $this->reservasModelo->obtenerReservasUsuario($id_usuario);

        $this->vista('reservas/index', $datos);
    }

    // Contratar un viaje: POST /reservas/contratar/1
    public function contratar($id_viaje = null)
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        // Solo se contrata desde el botón (POST), no escribiendo la url
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redireccionar('/viajes/ver/' . (int) $id_viaje);
            return;
        }

        $viaje = $this->viajesModelo->obtenerViaje($id_viaje);

        if (!$viaje) {
            redireccionar('/inicio');
            return;
        }

        // Actividades marcadas en el formulario (checkbox actividades[])
        $elegidas = array_map('intval', (array) ($_POST['actividades'] ?? []));

        // El precio se calcula con la base de datos, no con el formulario, para que no se pueda cambiar:
        // precio del viaje + precio de cada actividad opcional elegida que sea de este viaje
        $precio_total = $viaje->precio;
        $actividades = [];

        foreach ($this->viajesModelo->obtenerActividadesViaje($viaje->id_viaje) as $actividad) {
            if ($actividad->opcional && in_array((int) $actividad->id_actividad, $elegidas, true)) {
                $precio_total += $actividad->precio;
                $actividades[] = $actividad->id_actividad;
            }
        }

        $id_usuario = $datos['usuarioSesion']['id_usuario'];
        $this->reservasModelo->crearReserva($id_usuario, $viaje->id_viaje, $precio_total, $actividades);

        redireccionar('/reservas');
    }

    // Cancelar una reserva: POST /reservas/cancelar/1
    public function cancelar($id_reserva = null)
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = $datos['usuarioSesion']['id_usuario'];
            $this->reservasModelo->cancelarReserva($id_reserva, $id_usuario);
        }

        redireccionar('/reservas');
    }

    // Todas las reservas de viajes (solo administrador): /reservas/admin?estado=CONFIRMADA
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
        $datos['reservas'] = $this->reservasModelo->obtenerTodas($estado);

        $this->vista('reservas/admin', $datos);
    }

    // Cambiar el estado de una reserva (solo administrador): POST /reservas/cambiarEstado/1 con estado
    public function cambiarEstado($id_reserva = null)
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
            $this->reservasModelo->cambiarEstado($id_reserva, $estado);
        }

        redireccionar('/reservas/admin');
    }
}
