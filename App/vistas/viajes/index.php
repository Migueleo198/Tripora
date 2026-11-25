<?php
require RUTA_APP . '/vistas/inc/header.php';

$viajes = $datos['viajes'];
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Viajes</h1>
        <?php if ($esAdmin): ?>
            <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/viajes/crear">Añadir viaje</a>
        <?php endif; ?>
    </div>

    <div class="alert alert-danger d-none" data-trip-error></div>

    <?php $placeholderBusqueda = 'Buscar viajes, lugares...'; require RUTA_APP . '/vistas/inc/buscador.php'; ?>

    <?php if (empty($viajes->datos)): ?>
        <div class="alert alert-info">Todavía no hay viajes disponibles.</div>
    <?php elseif ($esAdmin): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($viajes->datos as $viaje): ?>
                        <tr data-trip-row data-search-item
                            data-search="<?php echo e($viaje->nombre . ' ' . $viaje->ubicacion . ' ' . $viaje->descripcion); ?>">
                            <td><?php echo e($viaje->nombre); ?></td>
                            <td><?php echo e($viaje->ubicacion); ?></td>
                            <td class="text-end"><?php echo number_format((float) $viaje->precio, 2, ',', '.'); ?> €</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?php echo RUTA_URL; ?>/viajes/ver/<?php echo (int) $viaje->id_viaje; ?>"
                                   title="Ver" aria-label="Ver viaje">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-primary"
                                   href="<?php echo RUTA_URL; ?>/viajes/editar/<?php echo (int) $viaje->id_viaje; ?>"
                                   title="Editar" aria-label="Editar viaje">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-primary"
                                   href="<?php echo RUTA_URL; ?>/viajes/subirImagen?id_viaje=<?php echo (int) $viaje->id_viaje; ?>"
                                   title="Imágenes" aria-label="Imágenes del viaje">
                                    <i class="bi bi-image"></i>
                                </a>
                                <form class="d-inline" method="post" data-trip-delete="<?php echo (int) $viaje->id_viaje; ?>"
                                      action="<?php echo RUTA_URL; ?>/viajes/borrar/<?php echo (int) $viaje->id_viaje; ?>"
                                      data-confirmar="¿Seguro que quieres borrar el viaje «<?php echo e($viaje->nombre); ?>»?"
                                      data-confirmar-boton="Borrar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Borrar" aria-label="Borrar viaje">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($viajes->datos as $viaje): ?>
                <?php require RUTA_APP . '/vistas/viajes/tarjeta.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info d-none" data-search-empty="No hay viajes que coincidan con"></div>

    <?php if ($viajes->numPaginas > 1): ?>
        <nav class="mt-4" aria-label="Paginación de viajes">
            <ul class="pagination justify-content-center">
                <?php for ($i = 0; $i < $viajes->numPaginas; $i++): ?>
                    <li class="page-item <?php echo $i === $viajes->paginaActual ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo RUTA_URL; ?>/viajes?pagina=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php
$scripts = ['viajes/ui/tripPage.js', 'viajes/ui/searchBar.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
