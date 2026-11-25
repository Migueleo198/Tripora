<?php
require RUTA_APP . '/vistas/inc/header.php';

$hoteles = $datos['hoteles'];
?>


    

<main class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Hoteles</h1>
       
    </div>
    
    <p class="lead">Busca los mejores hoteles.</p>

    <?php $placeholderBusqueda = 'Buscar hoteles...'; require RUTA_APP . '/vistas/inc/buscador.php'; ?>

    <?php if (empty($datos['hoteles'])): ?>
        <div class="alert alert-info">Todavía no hay hoteles disponibles.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($datos['hoteles']->datos as $hotel): ?>
                <?php $oculto = $datos['idsEncontrados'] !== null && !in_array($hotel->id_hotel, $datos['idsEncontrados']); ?>
                <?php require RUTA_APP . '/vistas/hoteles/hotelesVistaClienteTarjeta.php'; ?>
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

