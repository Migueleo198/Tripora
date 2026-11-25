-- Precio por noche opcional, para contratar el hotel sin viaje (NULL = solo se ofrece dentro de viajes)
SET NAMES utf8mb4;

ALTER TABLE Hotel
    ADD COLUMN precio_noche DECIMAL(10,2) NULL AFTER estrellas,
    ADD CONSTRAINT CK_Hotel_Precio_Noche CHECK (precio_noche >= 0);
