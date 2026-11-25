-- Añade la descripción de los viajes (ya aplicado en la base local tripora)
ALTER TABLE viaje
    ADD COLUMN descripcion TEXT NULL AFTER nombre;
