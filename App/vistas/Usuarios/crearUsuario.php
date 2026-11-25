<?php
require RUTA_APP . '/vistas/inc/header.php';

$usuarioForm = $datos['usuario'] ?? null;
$accion = RUTA_URL . '/usuarios/crear';
$textoBoton = 'Crear usuario';
?>

<main class="container py-5" style="max-width: 820px;">
    <h1 class="mb-4">Añadir usuario</h1>

    <?php require RUTA_APP . '/vistas/Usuarios/formulario.php'; ?>
</main>

<?php
$scripts = ['clients/ui/clientPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
