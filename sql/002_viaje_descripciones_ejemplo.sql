-- Descripciones de ejemplo para los viajes existentes
SET NAMES utf8mb4;

UPDATE viaje SET descripcion = 'Tres días en la Ciudad de la Luz: paseo por el Sena, subida a la Torre Eiffel y tarde libre en Montmartre. Incluye hotel céntrico con desayuno y entrada al Museo del Louvre.'
WHERE id_viaje = 1;

UPDATE viaje SET descripcion = 'Recorre la Roma clásica con visita guiada al Coliseo, el Foro Romano y el Vaticano. Tiempo libre para perderse por el Trastevere y probar la auténtica pasta carbonara.'
WHERE id_viaje = 2;

UPDATE viaje SET descripcion = 'Sol y naturaleza en las Canarias: excursión al Parque Nacional del Teide, avistamiento de cetáceos en Los Gigantes y días de playa en el sur de la isla.'
WHERE id_viaje = 3;

UPDATE viaje SET descripcion = 'Una semana entre tradición y futuro: templos de Asakusa, el cruce de Shibuya, el mercado de Tsukiji y excursión de un día al monte Fuji. Incluye Japan Rail Pass de 7 días.'
WHERE id_viaje = 4;

UPDATE viaje SET descripcion = 'Escapada de fin de semana con visita guiada a la Alhambra y los jardines del Generalife, paseo por el Albaicín al atardecer y ruta de tapas por el centro.'
WHERE id_viaje = 5;

UPDATE viaje SET descripcion = 'Rutas de senderismo entre glaciares y lagos alpinos, viaje en el tren panorámico Glacier Express y alojamiento en chalet de montaña con media pensión.'
WHERE id_viaje = 6;
