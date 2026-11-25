-- Corrige la letra de control de los DNI de prueba (validación añadida en Usuarios.php)
-- El id 10 tenía 9 números (123454678B): se usan los 8 primeros
SET NAMES utf8mb4;

UPDATE usuario SET DNI = '00000001R' WHERE id_usuario = 1;
UPDATE usuario SET DNI = '00000002W' WHERE id_usuario = 2;
UPDATE usuario SET DNI = '00000003A' WHERE id_usuario = 3;
UPDATE usuario SET DNI = '00000004G' WHERE id_usuario = 4;
UPDATE usuario SET DNI = '12345675B' WHERE id_usuario = 9;
UPDATE usuario SET DNI = '12345467X' WHERE id_usuario = 10;
