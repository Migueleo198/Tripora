<?php
require RUTA_APP . '/vistas/inc/header.php';

$hoteles = $datos['hoteles'];
?>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Hoteles</h1>
        <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/hoteles/crear">Añadir hotel</a>
    </div>

    <div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-hotel-error>
        <?php echo e($datos['error'] ?? ''); ?>
    </div>

    <?php $placeholderBusqueda = 'Buscar hoteles, ciudades...'; require RUTA_APP . '/vistas/inc/buscador.php'; ?>

    <?php if (empty($hoteles->datos)): ?>
        <div class="alert alert-info">Todavía no hay hoteles.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Estrellas</th>
                        <th class="text-end">Precio / noche</th>
                        <th class='text-end'>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hoteles->datos as $hotel): ?>
                        <?php $idHotel = (int) $hotel->id_hotel; ?>
                        <tr data-hotel-row data-search-item
                            data-search="<?php echo e($hotel->nombre . ' ' . $hotel->direccion . ' ' . $hotel->ciudad); ?>">
                            <td><?php echo e($hotel->nombre); ?></td>
                            <td><?php echo e($hotel->direccion); ?></td>
                            <td><?php echo e($hotel->ciudad); ?></td>
                            <td>
                                <?php if ($hotel->estrellas !== null): ?>
                                    <span class="text-warning" title="<?php echo (int) $hotel->estrellas; ?> estrellas">
                                        <?php echo str_repeat('★', (int) $hotel->estrellas); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($hotel->precio_noche !== null): ?>
                                    <?php echo number_format((float) $hotel->precio_noche, 2, ',', '.'); ?> €
                                <?php else: ?>
                                    <span class="text-muted" title="Solo se ofrece dentro de viajes">Solo en viajes</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-secondary" href="<?php echo RUTA_URL; ?>/hoteles/ver/<?php echo $idHotel; ?>"
                                   title="Ver" aria-label="Ver hotel">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-primary" href="<?php echo RUTA_URL; ?>/hoteles/editar/<?php echo $idHotel; ?>"
                                   title="Editar" aria-label="Editar hotel">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-success" href="<?php echo RUTA_URL; ?>/hoteles/viajes/<?php echo $idHotel; ?>"
                                   title="Viajes" aria-label="Viajes del hotel">
                                    <i class="bi bi-airplane"></i>
                                </a>
                                <a class="btn btn-sm btn-outline-warning" href="<?php echo RUTA_URL; ?>/hoteles/regimenes/<?php echo $idHotel; ?>"
                                   title="Comidas" aria-label="Regímenes de comidas del hotel">
                                    <i class="bi bi-cup-hot"></i>
                                </a>
                                <?php // Borrar va por POST: el controlador no borra con un enlace (GET) ?>
                                <form class="d-inline" method="post" data-hotel-delete="<?php echo $idHotel; ?>"
                                      action="<?php echo RUTA_URL; ?>/hoteles/borrar/<?php echo $idHotel; ?>"
                                      data-confirmar="¿Seguro que quieres borrar el hotel «<?php echo e($hotel->nombre); ?>»?"
                                      data-confirmar-boton="Borrar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Borrar" aria-label="Borrar hotel">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="alert alert-info d-none" data-search-empty="No hay hoteles que coincidan con"></div>

    <?php if ($hoteles->numPaginas > 1): ?>
        <nav class="mt-4" aria-label="Paginación de hoteles">
            <ul class="pagination justify-content-center">
                <?php for ($i = 0; $i < $hoteles->numPaginas; $i++): ?>
                    <li class="page-item <?php echo $i === $hoteles->paginaActual ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo RUTA_URL; ?>/hoteles?pagina=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<?php
$scripts = ['hoteles/ui/hotelPage.js', 'viajes/ui/searchBar.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
