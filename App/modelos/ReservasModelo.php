<?php

class ReservasModelo
{
    private $db;

    public function __construct()
    {
        $this->db = new Base();
    }

    // Guardar una reserva nueva de un viaje con las actividades opcionales elegidas ($actividades = ids)
    // Se guarda todo en una transacción: si falla una actividad no queda la reserva a medias
    public function crearReserva($id_usuario, $id_viaje, $precio, $actividades = [])
    {
        $this->db->iniciarTransaccion();

        try {
            $this->db->query(
                "INSERT INTO reserva (id_viaje, id_usuario, estado, fecha_reserva, precio_total)
                 VALUES (:id_viaje, :id_usuario, 'CONFIRMADA', NOW(), :precio)"
            );

            $this->db->bind(':id_viaje', (int) $id_viaje);
            $this->db->bind(':id_usuario', (int) $id_usuario);
            $this->db->bind(':precio', $precio);
            $this->db->execute();

            $id_reserva = (int) $this->db->ultimoIndice();

            foreach ($actividades as $id_actividad) {
                $this->db->query(
                    "INSERT INTO reserva_actividad (id_reserva, id_viaje, id_actividad)
                     VALUES (:id_reserva, :id_viaje, :id_actividad)"
                );

                $this->db->bind(':id_reserva', $id_reserva);
                $this->db->bind(':id_viaje', (int) $id_viaje);
                $this->db->bind(':id_actividad', (int) $id_actividad);
                $this->db->execute();
            }

            $this->db->confirmarTransaccion();

            return $id_reserva;
        } catch (PDOException $e) {
            $this->db->deshacerTransaccion();
            return false;
        }
    }

    // Reservas de un usuario con los datos del viaje y sus actividades opcionales, las más nuevas primero
    // GROUP_CONCAT junta los nombres de las actividades en un texto: "Cena típica, Kayak"
    public function obtenerReservasUsuario($id_usuario)
    {
        $this->db->query(
            "SELECT r.id_reserva, r.estado, r.fecha_reserva, r.precio_total,
                    v.id_viaje, v.nombre, v.ubicacion, v.dias,
                    GROUP_CONCAT(a.nombre ORDER BY a.nombre SEPARATOR ', ') AS actividades
             FROM reserva r
             JOIN viaje v ON v.id_viaje = r.id_viaje
             LEFT JOIN reserva_actividad ra ON ra.id_reserva = r.id_reserva
             LEFT JOIN actividad a ON a.id_actividad = ra.id_actividad
             WHERE r.id_usuario = :id_usuario
             GROUP BY r.id_reserva
             ORDER BY r.fecha_reserva DESC"
        );

        $this->db->bind(':id_usuario', (int) $id_usuario);

        return $this->db->registros();
    }

    // Todas las reservas de viajes con el cliente y el viaje (para el administrador)
    // Si llega un estado, solo las de ese estado
    public function obtenerTodas($estado = '')
    {
        $this->db->query(
            "SELECT r.id_reserva, r.estado, r.fecha_reserva, r.precio_total,
                    v.id_viaje, v.nombre AS viaje,
                    u.nombre, u.apellidos, u.email,
                    GROUP_CONCAT(a.nombre ORDER BY a.nombre SEPARATOR ', ') AS actividades
             FROM reserva r
             JOIN viaje v ON v.id_viaje = r.id_viaje
             JOIN usuario u ON u.id_usuario = r.id_usuario
             LEFT JOIN reserva_actividad ra ON ra.id_reserva = r.id_reserva
             LEFT JOIN actividad a ON a.id_actividad = ra.id_actividad
             WHERE :estado = '' OR r.estado = :estado_filtro
             GROUP BY r.id_reserva
             ORDER BY r.fecha_reserva DESC"
        );

        $this->db->bind(':estado', $estado);
        $this->db->bind(':estado_filtro', $estado);

        return $this->db->registros();
    }

    // Cambiar el estado de cualquier reserva (lo usa el administrador)
    public function cambiarEstado($id_reserva, $estado)
    {
        $this->db->query(
            "UPDATE reserva SET estado = :estado WHERE id_reserva = :id_reserva"
        );

        $this->db->bind(':estado', $estado);
        $this->db->bind(':id_reserva', (int) $id_reserva);

        return $this->db->execute();
    }

    // Cancelar una reserva (solo si es del usuario, para que nadie cancele las de otro)
    public function cancelarReserva($id_reserva, $id_usuario)
    {
        $this->db->query(
            "UPDATE reserva SET estado = 'CANCELADA'
             WHERE id_reserva = :id_reserva AND id_usuario = :id_usuario"
        );

        $this->db->bind(':id_reserva', (int) $id_reserva);
        $this->db->bind(':id_usuario', (int) $id_usuario);

        return $this->db->execute();
    }
}
