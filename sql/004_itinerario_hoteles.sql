-- Itinerario y hoteles: días del viaje, día y hora de cada actividad,
-- hoteles por viaje (Alojar) y actividades elegidas en cada reserva (Seleccionar)
SET NAMES utf8mb4;

-- ============================================
-- VIAJE: duración en días
-- ============================================

ALTER TABLE Viaje
    ADD COLUMN dias TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER descripcion,
    ADD CONSTRAINT CK_Viaje_Dias CHECK (dias >= 1);

UPDATE Viaje SET dias = 3 WHERE id_viaje = 1;
UPDATE Viaje SET dias = 4 WHERE id_viaje = 2;
UPDATE Viaje SET dias = 5 WHERE id_viaje = 3;
UPDATE Viaje SET dias = 7 WHERE id_viaje = 4;
UPDATE Viaje SET dias = 2 WHERE id_viaje = 5;
UPDATE Viaje SET dias = 5 WHERE id_viaje = 6;

ALTER TABLE Viaje
    ALTER COLUMN dias DROP DEFAULT;

-- ============================================
-- ACTIVIDAD_VIAJE: día y hora dentro del viaje
-- ============================================

ALTER TABLE Actividad_Viaje
    ADD COLUMN dia TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER id_actividad,
    ADD COLUMN hora TIME NULL AFTER dia,
    ADD CONSTRAINT CK_Actividad_Viaje_Dia CHECK (dia >= 1);

UPDATE Actividad_Viaje SET dia = 1, hora = '10:00' WHERE id_viaje = 1 AND id_actividad = 1;
UPDATE Actividad_Viaje SET dia = 1, hora = '16:00' WHERE id_viaje = 1 AND id_actividad = 7;
UPDATE Actividad_Viaje SET dia = 2, hora = '10:00' WHERE id_viaje = 1 AND id_actividad = 5;
UPDATE Actividad_Viaje SET dia = 2, hora = '20:00' WHERE id_viaje = 1 AND id_actividad = 2;

UPDATE Actividad_Viaje SET dia = 1, hora = '10:00' WHERE id_viaje = 2 AND id_actividad = 1;
UPDATE Actividad_Viaje SET dia = 2, hora = '10:00' WHERE id_viaje = 2 AND id_actividad = 5;
UPDATE Actividad_Viaje SET dia = 3, hora = '11:00' WHERE id_viaje = 2 AND id_actividad = 10;
UPDATE Actividad_Viaje SET dia = 3, hora = '20:00' WHERE id_viaje = 2 AND id_actividad = 2;

UPDATE Actividad_Viaje SET dia = 2, hora = '09:00' WHERE id_viaje = 3 AND id_actividad = 9;
UPDATE Actividad_Viaje SET dia = 3, hora = '09:00' WHERE id_viaje = 3 AND id_actividad = 3;
UPDATE Actividad_Viaje SET dia = 4, hora = '11:00' WHERE id_viaje = 3 AND id_actividad = 4;
UPDATE Actividad_Viaje SET dia = 4, hora = '17:00' WHERE id_viaje = 3 AND id_actividad = 6;

UPDATE Actividad_Viaje SET dia = 1, hora = '16:00' WHERE id_viaje = 4 AND id_actividad = 7;
UPDATE Actividad_Viaje SET dia = 2, hora = '10:00' WHERE id_viaje = 4 AND id_actividad = 1;
UPDATE Actividad_Viaje SET dia = 3, hora = '19:00' WHERE id_viaje = 4 AND id_actividad = 2;
UPDATE Actividad_Viaje SET dia = 5, hora = '11:00' WHERE id_viaje = 4 AND id_actividad = 10;

UPDATE Actividad_Viaje SET dia = 1, hora = '10:00' WHERE id_viaje = 5 AND id_actividad = 1;
UPDATE Actividad_Viaje SET dia = 1, hora = '20:30' WHERE id_viaje = 5 AND id_actividad = 2;
UPDATE Actividad_Viaje SET dia = 2, hora = '10:00' WHERE id_viaje = 5 AND id_actividad = 5;
UPDATE Actividad_Viaje SET dia = 2, hora = '12:00' WHERE id_viaje = 5 AND id_actividad = 7;

UPDATE Actividad_Viaje SET dia = 2, hora = '09:00' WHERE id_viaje = 6 AND id_actividad = 9;
UPDATE Actividad_Viaje SET dia = 3, hora = '08:30' WHERE id_viaje = 6 AND id_actividad = 3;
UPDATE Actividad_Viaje SET dia = 4, hora = '09:00' WHERE id_viaje = 6 AND id_actividad = 8;
UPDATE Actividad_Viaje SET dia = 4, hora = '17:00' WHERE id_viaje = 6 AND id_actividad = 6;

ALTER TABLE Actividad_Viaje
    ALTER COLUMN dia DROP DEFAULT;

-- ============================================
-- TABLA: HOTEL
-- ============================================

CREATE TABLE Hotel (
    id_hotel INT AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    estrellas TINYINT UNSIGNED NULL,

    CONSTRAINT PK_Hotel
        PRIMARY KEY (id_hotel),

    CONSTRAINT CK_Hotel_Estrellas
        CHECK (estrellas BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: HOTEL_VIAJE (relación Alojar)
-- ============================================

CREATE TABLE Hotel_Viaje (
    id_viaje INT NOT NULL,
    id_hotel INT NOT NULL,
    orden TINYINT UNSIGNED NOT NULL,
    noches TINYINT UNSIGNED NOT NULL,

    CONSTRAINT PK_Hotel_Viaje
        PRIMARY KEY (id_viaje, id_hotel),

    CONSTRAINT UQ_Hotel_Viaje_Orden
        UNIQUE (id_viaje, orden),

    CONSTRAINT FK_Hotel_Viaje_Viaje
        FOREIGN KEY (id_viaje)
        REFERENCES Viaje(id_viaje)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT FK_Hotel_Viaje_Hotel
        FOREIGN KEY (id_hotel)
        REFERENCES Hotel(id_hotel)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT CK_Hotel_Viaje_Orden
        CHECK (orden >= 1),

    CONSTRAINT CK_Hotel_Viaje_Noches
        CHECK (noches >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: RESERVA_ACTIVIDAD (relación Seleccionar)
-- ============================================

CREATE TABLE Reserva_Actividad (
    id_reserva INT NOT NULL,
    id_viaje INT NOT NULL,
    id_actividad INT NOT NULL,

    CONSTRAINT PK_Reserva_Actividad
        PRIMARY KEY (id_reserva, id_viaje, id_actividad),

    CONSTRAINT FK_Reserva_Actividad_Reserva
        FOREIGN KEY (id_reserva)
        REFERENCES Reserva(id_reserva)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT FK_Reserva_Actividad_Actividad_Viaje
        FOREIGN KEY (id_viaje, id_actividad)
        REFERENCES Actividad_Viaje(id_viaje, id_actividad)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
