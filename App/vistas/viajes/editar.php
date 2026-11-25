<?php
require RUTA_APP . '/vistas/inc/header.php';

$viaje = $datos['viaje'];
$actividades = $datos['actividades'] ?? [];
$accion = RUTA_URL . '/viajes/editar/' . (int) $viaje->id_viaje;
$textoBoton = 'Guardar cambios';
?>

<main class="container py-5" style="max-width: 960px;">
    <h1 class="mb-4">Editar viaje</h1>

    <?php require RUTA_APP . '/vistas/viajes/formulario.php'; ?>
</main>

<?php
$scripts = ['viajes/ui/tripPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
