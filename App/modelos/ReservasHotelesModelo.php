<?php

class ReservasHotelesModelo
{
    private $db;

    public function __construct()
    {
        $this->db = new Base();
    }

    // Guardar una reserva nueva de un hotel para un usuario, con el régimen de comidas elegido
    public function crearReserva($id_usuario, $id_hotel, $fecha_entrada, $noches, $regimen, $precio_total)
    {
        $this->db->query(
            "INSERT INTO reserva_hotel (id_usuario, id_hotel, estado, fecha_reserva, fecha_entrada, noches, regimen, precio_total)
             VALUES (:id_usuario, :id_hotel, 'CONFIRMADA', NOW(), :fecha_entrada, :noches, :regimen, :precio_total)"
        );

        $this->db->bind(':id_usuario', (int) $id_usuario);
        $this->db->bind(':id_hotel', (int) $id_hotel);
        $this->db->bind(':fecha_entrada', $fecha_entrada);
        $this->db->bind(':noches', (int) $noches);
        $this->db->bind(':regimen', $regimen);
        $this->db->bind(':precio_total', $precio_total);

        return $this->db->execute();
    }

    // Reservas de hotel de un usuario con los datos del hotel, las más nuevas primero
    public function obtenerReservasUsuario($id_usuario)
    {
        $this->db->query(
            "SELECT r.id_reserva_hotel, r.estado, r.fecha_reserva, r.fecha_entrada, r.noches, r.regimen, r.precio_total,
                    h.id_hotel, h.nombre, h.ciudad, h.estrellas
             FROM reserva_hotel r
             JOIN hotel h ON h.id_hotel = r.id_hotel
             WHERE r.id_usuario = :id_usuario
             ORDER BY r.fecha_reserva DESC"
        );

        $this->db->bind(':id_usuario', (int) $id_usuario);

        return $this->db->registros();
    }

    // Todas las reservas de hoteles con el cliente y el hotel (para el administrador)
    // Si llega un estado, solo las de ese estado
    public function obtenerTodas($estado = '')
    {
        $this->db->query(
            "SELECT r.id_reserva_hotel, r.estado, r.fecha_reserva, r.fecha_entrada, r.noches, r.regimen, r.precio_total,
                    h.id_hotel, h.nombre AS hotel,
                    u.nombre, u.apellidos, u.email
             FROM reserva_hotel r
             JOIN hotel h ON h.id_hotel = r.id_hotel
             JOIN usuario u ON u.id_usuario = r.id_usuario
             WHERE :estado = '' OR r.estado = :estado_filtro
             ORDER BY r.fecha_reserva DESC"
        );

        $this->db->bind(':estado', $estado);
        $this->db->bind(':estado_filtro', $estado);

        return $this->db->registros();
    }

    // Cambiar el estado de cualquier reserva (lo usa el administrador)
    public function cambiarEstado($id_reserva_hotel, $estado)
    {
        $this->db->query(
            "UPDATE reserva_hotel SET estado = :estado WHERE id_reserva_hotel = :id_reserva_hotel"
        );

        $this->db->bind(':estado', $estado);
        $this->db->bind(':id_reserva_hotel', (int) $id_reserva_hotel);

        return $this->db->execute();
    }

    // Cancelar una reserva (solo si es del usuario, para que nadie cancele las de otro)
    public function cancelarReserva($id_reserva_hotel, $id_usuario)
    {
        $this->db->query(
            "UPDATE reserva_hotel SET estado = 'CANCELADA'
             WHERE id_reserva_hotel = :id_reserva_hotel AND id_usuario = :id_usuario"
        );

        $this->db->bind(':id_reserva_hotel', (int) $id_reserva_hotel);
        $this->db->bind(':id_usuario', (int) $id_usuario);

        return $this->db->execute();
    }
}
