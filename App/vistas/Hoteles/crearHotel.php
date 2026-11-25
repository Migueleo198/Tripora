<?php
require RUTA_APP . '/vistas/inc/header.php';

$hotelForm = $datos['hotel'] ?? null;
$accion = RUTA_URL . '/hoteles/crear';
$textoBoton = 'Crear hotel';
?>

<main class="container py-5" style="max-width: 820px;">
    <h1 class="mb-4">Añadir Hotel</h1>

    <?php require RUTA_APP . '/vistas/Hoteles/formulario.php'; ?>
</main>

<?php
$scripts = ['hoteles/ui/hotelPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
