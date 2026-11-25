-- Régimen de comidas de los hoteles
-- SA = solo alojamiento, AD = alojamiento y desayuno, MP = media pensión,
-- PC = pensión completa, TI = todo incluido
-- Cada hotel indica qué regímenes ofrece y cuánto se suma por noche a precio_noche
SET NAMES utf8mb4;

CREATE TABLE Hotel_Regimen (
    id_hotel INT NOT NULL,
    regimen ENUM('SA', 'AD', 'MP', 'PC', 'TI') NOT NULL,
    suplemento_noche DECIMAL(10,2) NOT NULL,

    CONSTRAINT PK_Hotel_Regimen
        PRIMARY KEY (id_hotel, regimen),

    -- Si se borra el hotel se borran sus regímenes
    CONSTRAINT FK_Hotel_Regimen_Hotel
        FOREIGN KEY (id_hotel)
        REFERENCES Hotel(id_hotel)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT CK_Hotel_Regimen_Suplemento
        CHECK (suplemento_noche >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Régimen elegido al reservar un hotel suelto (precio_total ya incluye el suplemento)
ALTER TABLE Reserva_Hotel
    ADD COLUMN regimen ENUM('SA', 'AD', 'MP', 'PC', 'TI') NOT NULL DEFAULT 'SA' AFTER noches;

-- Régimen incluido en el hotel de un viaje (lo fija el administrador, el cliente no lo elige)
ALTER TABLE Hotel_Viaje
    ADD COLUMN regimen ENUM('SA', 'AD', 'MP', 'PC', 'TI') NOT NULL DEFAULT 'SA' AFTER noches;

-- Datos de ejemplo: todos tienen SA y AD; MP según el hotel; PC y TI solo los de más categoría
INSERT INTO Hotel_Regimen (id_hotel, regimen, suplemento_noche) VALUES
    (1, 'SA', 0), (1, 'AD', 18), (1, 'MP', 45),
    (2, 'SA', 0), (2, 'AD', 10), (2, 'MP', 28),
    (3, 'SA', 0), (3, 'AD', 12), (3, 'MP', 30), (3, 'PC', 50), (3, 'TI', 75),
    (4, 'SA', 0), (4, 'AD', 20), (4, 'MP', 50),
    (5, 'SA', 0), (5, 'AD', 8),
    (6, 'SA', 0), (6, 'AD', 30), (6, 'MP', 70), (6, 'PC', 110),
    (7, 'SA', 0), (7, 'AD', 9), (7, 'MP', 25),
    (8, 'SA', 0), (8, 'AD', 40), (8, 'MP', 90), (8, 'PC', 140), (8, 'TI', 200);

-- Régimen de los hoteles que ya están en viajes
UPDATE Hotel_Viaje SET regimen = 'AD' WHERE id_viaje = 1 AND id_hotel = 1;
UPDATE Hotel_Viaje SET regimen = 'AD' WHERE id_viaje = 2 AND id_hotel = 2;
UPDATE Hotel_Viaje SET regimen = 'TI' WHERE id_viaje = 3 AND id_hotel = 3;
UPDATE Hotel_Viaje SET regimen = 'MP' WHERE id_viaje = 4 AND id_hotel = 4;
UPDATE Hotel_Viaje SET regimen = 'AD' WHERE id_viaje = 5 AND id_hotel = 5;
UPDATE Hotel_Viaje SET regimen = 'PC' WHERE id_viaje = 6 AND id_hotel = 6;
UPDATE Hotel_Viaje SET regimen = 'MP' WHERE id_viaje = 6 AND id_hotel = 7;
