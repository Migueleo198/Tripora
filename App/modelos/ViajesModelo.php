<?php

class ViajesModelo
{
    private $db;

    public function __construct()
    {
        $this->db = new Base();
    }

    // Condición WHERE para buscar por nombre, ubicación o descripción
    private function filtroBusqueda($busqueda)
    {
        if ($busqueda === '') {
            return '';
        }

        return "WHERE nombre LIKE :busqueda1
                OR ubicacion LIKE :busqueda2
                OR descripcion LIKE :busqueda3";
    }

    // Vincular el texto de búsqueda (se escapan % y _ para buscarlos literalmente)
    private function bindBusqueda($busqueda)
    {
        if ($busqueda === '') {
            return;
        }

        $patron = '%' . addcslashes($busqueda, '%_\\') . '%';

        $this->db->bind(':busqueda1', $patron);
        $this->db->bind(':busqueda2', $patron);
        $this->db->bind(':busqueda3', $patron);
    }

    // Obtener todos los viajes o una página de resultados, opcionalmente filtrados
    public function obtenerViajes($pagina = 0, $tam_pagina = 0, $busqueda = '')
    {
        $busqueda = trim($busqueda);

        if ($tam_pagina <= 0) {
            $this->db->query(
                "SELECT * FROM viaje
                 {$this->filtroBusqueda($busqueda)}
                 ORDER BY id_viaje DESC"
            );
            $this->bindBusqueda($busqueda);

            return $this->db->registros();
        }

        $pagina = max(0, (int) $pagina);
        $tam_pagina = max(1, (int) $tam_pagina);
        $registro_inicial = $pagina * $tam_pagina;

        $this->db->query(
            "SELECT * FROM viaje
             {$this->filtroBusqueda($busqueda)}
             ORDER BY id_viaje DESC
             LIMIT :inicio, :cantidad"
        );

        $this->bindBusqueda($busqueda);
        $this->db->bind(':inicio', $registro_inicial);
        $this->db->bind(':cantidad', $tam_pagina);

        $viajes = $this->db->registros();

        return (object) [
            'datos' => $viajes,
            'numPaginas' => (int) ceil(
                $this->obtenerNumViajes($busqueda) / $tam_pagina
            ),
            'paginaActual' => $pagina
        ];
    }

    // Obtener un viaje por su identificador
    public function obtenerViaje($id_viaje)
    {
        $this->db->query(
            "SELECT * FROM viaje
             WHERE id_viaje = :id_viaje
             LIMIT 1"
        );

        $this->db->bind(':id_viaje', (int) $id_viaje);

        return $this->db->registro();
    }

    // Contar el número total de viajes, opcionalmente filtrados
    public function obtenerNumViajes($busqueda = '')
    {
        $busqueda = trim($busqueda);

        $this->db->query(
            "SELECT COUNT(*) AS total FROM viaje
             {$this->filtroBusqueda($busqueda)}"
        );

        $this->bindBusqueda($busqueda);

        $resultado = $this->db->registro();

        return (int) $resultado->total;
    }

    // Catálogo de actividades para elegir al crear un viaje
    public function obtenerActividades()
    {
        $this->db->query(
            "SELECT * FROM actividad
             ORDER BY nombre"
        );

        return $this->db->registros();
    }

    // Actividades de un viaje con su día, hora, duración, precio y si es opcional
    public function obtenerActividadesViaje($id_viaje)
    {
        $this->db->query(
            "SELECT a.id_actividad, a.nombre, a.descripcion,
                    av.dia, av.hora, av.duracion, av.precio, av.opcional
             FROM actividad_viaje av
             JOIN actividad a ON a.id_actividad = av.id_actividad
             WHERE av.id_viaje = :id_viaje
             ORDER BY av.dia, av.hora"
        );

        $this->db->bind(':id_viaje', (int) $id_viaje);

        return $this->db->registros();
    }

    // Hoteles de un viaje en el orden de la estancia, con las noches en cada uno
    public function obtenerHotelesViaje($id_viaje)
    {
        $this->db->query(
            "SELECT h.id_hotel, h.nombre, h.direccion, h.ciudad, h.estrellas,
                  hv.orden, hv.noches, hv.regimen
                    FROM hotel_viaje hv
                    JOIN hotel h ON h.id_hotel = hv.id_hotel
                    WHERE hv.id_viaje = :id_viaje
                    ORDER BY hv.orden"
        );


        $this->db->bind(':id_viaje', (int) $id_viaje);

        return $this->db->registros();
    }

    // Crear un viaje con sus actividades ($datos['actividades']), devuelve su identificador o false
    // Se guarda todo en una transacción: si falla una actividad no queda el viaje a medias
    public function crearViaje($datos)
    {
        $this->db->iniciarTransaccion();

        try {
            $this->db->query(
                "INSERT INTO viaje
                    (nombre, descripcion, ubicacion, precio, dias)
                 VALUES
                    (:nombre, :descripcion, :ubicacion, :precio, :dias)"
            );

            $this->db->bind(':nombre', $datos['nombre']);
            $this->db->bind(':descripcion', $datos['descripcion']);
            $this->db->bind(':ubicacion', $datos['ubicacion']);
            $this->db->bind(':precio', $datos['precio']);
            $this->db->bind(':dias', (int) $datos['dias']);
            $this->db->execute();

            $id_viaje = (int) $this->db->ultimoIndice();

            foreach ($datos['actividades'] ?? [] as $actividad) {
                $this->anadirActividad($id_viaje, $actividad);
            }

            $this->db->confirmarTransaccion();

            return $id_viaje;
        } catch (PDOException $e) {
            $this->db->deshacerTransaccion();
            return false;
        }
    }

    // Asociar una actividad a un viaje (tabla actividad_viaje); si ya estaba, actualiza sus datos
    private function anadirActividad($id_viaje, $actividad)
    {
        $this->db->query(
            "INSERT INTO actividad_viaje
                (id_viaje, id_actividad, dia, hora, duracion, precio, opcional)
             VALUES
                (:id_viaje, :id_actividad, :dia, :hora, :duracion, :precio, :opcional)
             ON DUPLICATE KEY UPDATE
                dia = VALUES(dia),
                hora = VALUES(hora),
                duracion = VALUES(duracion),
                precio = VALUES(precio),
                opcional = VALUES(opcional)"
        );

        $this->db->bind(':id_viaje', (int) $id_viaje);
        $this->db->bind(':id_actividad', (int) $actividad['id_actividad']);
        $this->db->bind(':dia', (int) $actividad['dia']);
        $this->db->bind(':hora', $actividad['hora'] === '' ? null : $actividad['hora']);
        $this->db->bind(':duracion', $actividad['duracion']);
        $this->db->bind(':precio', $actividad['precio']);
        $this->db->bind(':opcional', (int) $actividad['opcional']);

        $this->db->execute();
    }

    // Actualizar un viaje existente y, si vienen ($datos['actividades_enviadas']), sus actividades
    // Si se quita una actividad que alguna reserva ha elegido, la FK de reserva_actividad lo impide:
    // se deshace todo y se relanza la PDOException
    public function actualizarViaje($id_viaje, $datos)
    {
        $this->db->iniciarTransaccion();

        try {
            $this->db->query(
                "UPDATE viaje
                 SET nombre = :nombre,
                     descripcion = :descripcion,
                     ubicacion = :ubicacion,
                     precio = :precio,
                     dias = :dias
                 WHERE id_viaje = :id_viaje"
            );

            $this->db->bind(':id_viaje', (int) $id_viaje);
            $this->db->bind(':nombre', $datos['nombre']);
            $this->db->bind(':descripcion', $datos['descripcion']);
            $this->db->bind(':ubicacion', $datos['ubicacion']);
            $this->db->bind(':precio', $datos['precio']);
            $this->db->bind(':dias', (int) $datos['dias']);
            $this->db->execute();

            if (!empty($datos['actividades_enviadas'])) {
                $this->sincronizarActividades($id_viaje, $datos['actividades'] ?? []);
            }

            $this->db->confirmarTransaccion();

            return true;
        } catch (PDOException $e) {
            $this->db->deshacerTransaccion();
            throw $e;
        }
    }

    // Deja en actividad_viaje exactamente las actividades recibidas:
    // borra las desmarcadas, añade las nuevas y actualiza las que ya estaban
    private function sincronizarActividades($id_viaje, $actividades)
    {
        $ids = array_map('intval', array_column($actividades, 'id_actividad'));
        $marcadores = [];

        foreach ($ids as $i => $id) {
            $marcadores[] = ':id' . $i;
        }

        $this->db->query(
            "DELETE FROM actividad_viaje
             WHERE id_viaje = :id_viaje" .
            ($ids ? ' AND id_actividad NOT IN (' . implode(', ', $marcadores) . ')' : '')
        );

        $this->db->bind(':id_viaje', (int) $id_viaje);

        foreach ($ids as $i => $id) {
            $this->db->bind(':id' . $i, $id);
        }

        $this->db->execute();

        foreach ($actividades as $actividad) {
            $this->anadirActividad($id_viaje, $actividad);
        }
    }

    // Eliminar un viaje con sus actividades y hoteles asociados
    // Si tiene reservas, la FK de reserva lo impide: se deshace todo y se relanza la PDOException
    public function borrarViaje($id_viaje)
    {
        $this->db->iniciarTransaccion();

        try {
            foreach (['actividad_viaje', 'hotel_viaje', 'viaje'] as $tabla) {
                $this->db->query(
                    "DELETE FROM $tabla WHERE id_viaje = :id_viaje"
                );

                $this->db->bind(':id_viaje', (int) $id_viaje);
                $this->db->execute();
            }

            $this->db->confirmarTransaccion();

            return true;
        } catch (PDOException $e) {
            $this->db->deshacerTransaccion();
            throw $e;
        }
    }
}
