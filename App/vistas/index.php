<?php require RUTA_APP . '/vistas/inc/header.php'; ?>

<main class="container py-5">
    <h1>Bienvenido a Tripora, <?php echo $datos['usuarioSesion']['nombre'] ?></h1>
    <p class="lead">Planifica, comparte y explora tus viajes.</p>

    <?php $placeholderBusqueda = 'Buscar viajes, lugares...'; require RUTA_APP . '/vistas/inc/buscador.php'; ?>

    <?php if (empty($datos['viajes'])): ?>
        <div class="alert alert-info">Todavía no hay viajes disponibles.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($datos['viajes'] as $viaje): ?>
                <?php $oculto = $datos['idsEncontrados'] !== null && !in_array($viaje->id_viaje, $datos['idsEncontrados']); ?>
                <?php require RUTA_APP . '/vistas/viajes/tarjeta.php'; ?>
            <?php endforeach; ?>
        </div>

        <div class="alert alert-info mt-4 <?php echo $datos['idsEncontrados'] === [] ? '' : 'd-none'; ?>" data-search-empty="No hay viajes que coincidan con">
            <?php if ($datos['idsEncontrados'] === []): ?>
                No hay viajes que coincidan con «<?php echo e($datos['busqueda']); ?>».
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<?php
$scripts = ['viajes/ui/searchBar.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
