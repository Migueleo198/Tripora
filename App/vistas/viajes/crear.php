<?php
require RUTA_APP . '/vistas/inc/header.php';

$viaje = $datos['viaje'] ?? null;
$actividades = $datos['actividades'] ?? [];
$accion = RUTA_URL . '/viajes/crear';
$textoBoton = 'Crear viaje';
?>

<main class="container py-5" style="max-width: 960px;">
    <h1 class="mb-4">Añadir viaje</h1>

    <?php require RUTA_APP . '/vistas/viajes/formulario.php'; ?>
</main>

<?php
$scripts = ['viajes/ui/tripPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
