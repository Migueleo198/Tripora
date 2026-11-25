-- Descripción opcional del hotel (se muestra a los clientes)
SET NAMES utf8mb4;

ALTER TABLE Hotel
    ADD COLUMN descripcion TEXT NULL AFTER ciudad;

UPDATE Hotel SET descripcion = 'Hotel boutique en la orilla izquierda del Sena, a pocos pasos de Saint-Germain-des-Prés y del Barrio Latino. Habitaciones de estilo parisino, desayuno continental y terraza con vistas a los tejados de la ciudad.' WHERE nombre = 'Hotel Rive Gauche';
UPDATE Hotel SET descripcion = 'Alojamiento familiar en el corazón del Trastevere, entre calles empedradas, trattorias y plazas con encanto. A diez minutos a pie del Vaticano y con desayuno italiano incluido.' WHERE nombre = 'Hotel Trastevere';
UPDATE Hotel SET descripcion = 'Hotel frente al mar en el sur de Tenerife, con piscina exterior climatizada, solárium y acceso directo al paseo marítimo. Ideal para descansar y disfrutar del buen tiempo todo el año.' WHERE nombre = 'Hotel Costa Adeje';
UPDATE Hotel SET descripcion = 'Hotel moderno en Shinjuku, junto a la estación y al parque Shinjuku Gyoen. Habitaciones compactas y silenciosas, baño de estilo japonés y vistas al skyline de Tokio desde las plantas altas.' WHERE nombre = 'Hotel Shinjuku Garden';
UPDATE Hotel SET descripcion = 'Casa granadina tradicional con patio andaluz en el barrio del Albaicín, frente a la Alhambra. Habitaciones decoradas con azulejos y madera, y terraza con vistas al atardecer sobre Sierra Nevada.' WHERE nombre = 'Hotel Albaicín';
UPDATE Hotel SET descripcion = 'Chalet alpino de lujo con vistas directas al Matterhorn. Spa con sauna y piscina interior, chimenea en el salón y acceso a las pistas de esquí en invierno y a las rutas de senderismo en verano.' WHERE nombre = 'Chalet Matterhorn';
UPDATE Hotel SET descripcion = 'Hostal sencillo y acogedor en el centro de Interlaken, entre los lagos de Thun y Brienz. Buena base para excursiones a la Jungfrau, con cocina compartida y habitaciones privadas o compartidas.' WHERE nombre = 'Hostal Jungfrau';
