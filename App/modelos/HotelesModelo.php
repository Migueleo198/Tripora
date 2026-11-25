<?php

class HotelesModelo
{
    private $db;

    public function __construct()
    {
        $this->db = new Base();
    }

    // Obtener todos los hoteles o una página de resultados
    public function obtenerHoteles($pagina = 0, $tam_pagina = 0)
    {
        if ($tam_pagina <= 0) {
            $this->db->query(
                "SELECT * FROM hotel
                 ORDER BY id_hotel DESC"
            );

            return $this->db->registros();
        }

        $pagina = max(0, (int) $pagina);
        $tam_pagina = max(1, (int) $tam_pagina);
        $registro_inicial = $pagina * $tam_pagina;

        $this->db->query(
            "SELECT * FROM hotel
             ORDER BY id_hotel DESC
             LIMIT :inicio, :cantidad"
        );

        $this->db->bind(':inicio', $registro_inicial);
        $this->db->bind(':cantidad', $tam_pagina);

        $hoteles = $this->db->registros();

        return (object) [
            'datos' => $hoteles,
            'numPaginas' => (int) ceil(
                $this->obtenerNumHoteles() / $tam_pagina
            ),
            'paginaActual' => $pagina
        ];
    }

    // Identificadores de los hoteles que coinciden con la búsqueda (nombre, dirección, ciudad o descripción)
    // Se escapan % y _ para buscarlos literalmente
    public function buscarIdsHoteles($busqueda)
    {
        $this->db->query(
            "SELECT id_hotel FROM hotel
             WHERE nombre LIKE :busqueda1
                OR direccion LIKE :busqueda2
                OR ciudad LIKE :busqueda3
                OR descripcion LIKE :busqueda4"
        );

        $patron = '%' . addcslashes(trim($busqueda), '%_\\') . '%';

        $this->db->bind(':busqueda1', $patron);
        $this->db->bind(':busqueda2', $patron);
        $this->db->bind(':busqueda3', $patron);
        $this->db->bind(':busqueda4', $patron);

        return array_column($this->db->registros(), 'id_hotel');
    }

    // Obtener un hotel por su identificador
    public function obtenerHotel($id_hotel)
    {
        $this->db->query(
            "SELECT * FROM hotel
             WHERE id_hotel = :id_hotel
             LIMIT 1"
        );

        $this->db->bind(':id_hotel', (int) $id_hotel);

        return $this->db->registro();
    }

    // Contar el número total de hoteles
    public function obtenerNumHoteles()
    {
        $this->db->query(
            "SELECT COUNT(*) AS total FROM hotel"
        );

        $resultado = $this->db->registro();

        return (int) $resultado->total;
    }

    // Crear un hotel, devuelve su identificador o false
    public function crearHotel($datos)
    {
        $this->db->query(
            "INSERT INTO hotel
                (nombre, direccion, ciudad, descripcion, estrellas, precio_noche)
             VALUES
                (:nombre, :direccion, :ciudad, :descripcion, :estrellas, :precio_noche)"
        );

        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':direccion', $datos['direccion']);
        $this->db->bind(':ciudad', $datos['ciudad']);
        $this->db->bind(':descripcion', $this->descripcion($datos['descripcion'] ?? null));
        $this->db->bind(':estrellas', $this->estrellas($datos['estrellas'] ?? null));
        $this->db->bind(':precio_noche', $this->precioNoche($datos['precio_noche'] ?? null));

        if (!$this->db->execute()) {
            return false;
        }

        return (int) $this->db->ultimoIndice();
    }

    // Actualizar un hotel existente
    public function actualizarHotel($id_hotel, $datos)
    {
        $this->db->query(
            "UPDATE hotel
             SET nombre = :nombre,
                 direccion = :direccion,
                 ciudad = :ciudad,
                 descripcion = :descripcion,
                 estrellas = :estrellas,
                 precio_noche = :precio_noche
             WHERE id_hotel = :id_hotel"
        );

        $this->db->bind(':id_hotel', (int) $id_hotel);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':direccion', $datos['direccion']);
        $this->db->bind(':ciudad', $datos['ciudad']);
        $this->db->bind(':descripcion', $this->descripcion($datos['descripcion'] ?? null));
        $this->db->bind(':estrellas', $this->estrellas($datos['estrellas'] ?? null));
        $this->db->bind(':precio_noche', $this->precioNoche($datos['precio_noche'] ?? null));

        return $this->db->execute();
    }

    // Eliminar un hotel (falla si algún viaje o reserva lo usa: FK on delete restrict)
    public function borrarHotel($id_hotel)
    {
        $this->db->query(
            "DELETE FROM hotel WHERE id_hotel = :id_hotel"
        );

        $this->db->bind(':id_hotel', (int) $id_hotel);

        return $this->db->execute();
    }

    // Viajes en los que está el hotel, con las noches y el puesto del hotel en cada viaje
    public function obtenerViajesHotel($id_hotel)
    {
        $this->db->query(
            "SELECT v.id_viaje, v.nombre, v.ubicacion, v.dias, hv.orden, hv.noches, hv.regimen
             FROM hotel_viaje hv
             JOIN viaje v ON v.id_viaje = hv.id_viaje
             WHERE hv.id_hotel = :id_hotel
             ORDER BY v.nombre"
        );

        $this->db->bind(':id_hotel', (int) $id_hotel);

        return $this->db->registros();
    }

    // Asociar el hotel a un viaje: va el último en el orden de estancia del viaje
    // Si ya estaba asociado, la clave primaria lo impide y se lanza PDOException
    public function asociarViaje($id_hotel, $id_viaje, $noches, $regimen = 'SA')
    {
        $this->db->query(
            "INSERT INTO hotel_viaje (id_viaje, id_hotel, orden, noches, regimen)
             SELECT :id_viaje, :id_hotel, COALESCE(MAX(orden), 0) + 1, :noches, :regimen
             FROM hotel_viaje
             WHERE id_viaje = :id_viaje_orden"
        );

        $this->db->bind(':id_viaje', (int) $id_viaje);
        $this->db->bind(':id_hotel', (int) $id_hotel);
        $this->db->bind(':noches', (int) $noches);
        $this->db->bind(':regimen', $regimen);
        $this->db->bind(':id_viaje_orden', (int) $id_viaje);

        return $this->db->execute();
    }

    // Regímenes de comidas que ofrece un hotel con su suplemento por noche, del más barato al más caro
    // Si el hotel no tiene ninguno guardado (por ejemplo uno recién creado) se ofrece solo alojamiento
    public function obtenerRegimenes($id_hotel)
    {
        $this->db->query(
            "SELECT regimen, suplemento_noche
             FROM hotel_regimen
             WHERE id_hotel = :id_hotel
             ORDER BY suplemento_noche"
        );

        $this->db->bind(':id_hotel', (int) $id_hotel);

        $regimenes = $this->db->registros();

        if (!$regimenes) {
            $regimenes = [(object) ['regimen' => 'SA', 'suplemento_noche' => 0]];
        }

        return $regimenes;
    }

    // Guardar los regímenes de un hotel ($regimenes = ['AD' => 12.5, 'MP' => 30, ...])
    // Se borran los que tenía y se insertan los nuevos dentro de una transacción
    public function guardarRegimenes($id_hotel, $regimenes)
    {
        $this->db->iniciarTransaccion();

        try {
            $this->db->query("DELETE FROM hotel_regimen WHERE id_hotel = :id_hotel");
            $this->db->bind(':id_hotel', (int) $id_hotel);
            $this->db->execute();

            foreach ($regimenes as $regimen => $suplemento) {
                $this->db->query(
                    "INSERT INTO hotel_regimen (id_hotel, regimen, suplemento_noche)
                     VALUES (:id_hotel, :regimen, :suplemento)"
                );

                $this->db->bind(':id_hotel', (int) $id_hotel);
                $this->db->bind(':regimen', $regimen);
                $this->db->bind(':suplemento', $suplemento);
                $this->db->execute();
            }

            $this->db->confirmarTransaccion();

            return true;
        } catch (PDOException $e) {
            $this->db->deshacerTransaccion();
            return false;
        }
    }

    // Quitar el hotel de un viaje
    public function quitarViaje($id_hotel, $id_viaje)
    {
        $this->db->query(
            "DELETE FROM hotel_viaje WHERE id_hotel = :id_hotel AND id_viaje = :id_viaje"
        );

        $this->db->bind(':id_hotel', (int) $id_hotel);
        $this->db->bind(':id_viaje', (int) $id_viaje);

        return $this->db->execute();
    }

    // La descripción es opcional: vacía se guarda como NULL
    private function descripcion($descripcion)
    {
        return $descripcion === null || $descripcion === '' ? null : $descripcion;
    }

    // Las estrellas son opcionales: vacío se guarda como NULL
    private function estrellas($estrellas)
    {
        return $estrellas === null || $estrellas === '' ? null : (int) $estrellas;
    }

    // El precio por noche es opcional: vacío se guarda como NULL (el hotel solo va dentro de viajes)
    private function precioNoche($precio)
    {
        return $precio === null || $precio === '' ? null : (string) (float) $precio;
    }
}
