// RUTA_URL viene del header de PHP (<body data-ruta-url="...">)
export const RUTA_URL = document.body?.dataset.rutaUrl ?? "";

export const URL_VIAJES = `${RUTA_URL}/viajes`;
