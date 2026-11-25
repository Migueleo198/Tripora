<?php
require RUTA_APP . '/vistas/inc/header.php';

$hotelForm = $datos['hotel'];
$accion = RUTA_URL . '/hoteles/editar/' . (int) $hotelForm->id_hotel;
$textoBoton = 'Guardar cambios';
?>

<main class="container py-5" style="max-width: 820px;">
    <h1 class="mb-4">Editar Hotel</h1>

    <?php require RUTA_APP . '/vistas/Hoteles/formulario.php'; ?>
</main>

<?php
$scripts = ['hoteles/ui/hotelPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
