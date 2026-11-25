-- Código de 6 dígitos que va en el correo de recuperación, además del enlace
-- codigo: el código cifrado con password_hash (nunca el código en sí)
-- intentos: veces que se ha escrito mal; a los 5 deja de valer y hay que pedir otro
SET NAMES utf8mb4;

-- Los tokens pendientes no tienen código, se borran (el usuario puede pedir otro enlace)
DELETE FROM Token_Recuperacion;

ALTER TABLE Token_Recuperacion
    ADD COLUMN codigo VARCHAR(255) NOT NULL AFTER token,
    ADD COLUMN intentos TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER codigo;
