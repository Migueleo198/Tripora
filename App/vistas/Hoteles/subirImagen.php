<?php require RUTA_APP . '/vistas/inc/header.php'; ?>

<main class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">Imágenes de un Hotel</h1>

    <div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-hotel-error>
        <?php echo e($datos['error'] ?? ''); ?>
    </div>

    <?php if (empty($datos['hoteles'])): ?>
        <div class="alert alert-info">
            No hay hoteles. <a href="<?php echo RUTA_URL; ?>/hoteles/crear">Crea uno primero</a>.
        </div>
    <?php else: ?>
        <form action="<?php echo RUTA_URL; ?>/hoteles/subirImagen" method="post" enctype="multipart/form-data" data-hotel-image-form>
            <div class="mb-3">
                <label for="id_hotel" class="form-label">Seleccionar hotel</label>
                <select name="id_hotel" id="id_hotel" class="form-select" required data-hotel-image-select>
                    <option value="">Selecciona un hotel</option>
                    <?php foreach ($datos['hoteles'] as $hotel): ?>
                        <option value="<?php echo (int) $hotel->id_hotel; ?>"
                            <?php echo (int) $hotel->id_hotel === $datos['id_hotel'] ? 'selected' : ''; ?>>
                            <?php echo e($hotel->nombre); ?> (<?php echo e($hotel->ciudad); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="imagenes" class="form-label">Añadir imágenes</label>
                <input type="file" name="imagenes[]" id="imagenes" class="form-control"
                       accept="image/jpeg,image/png,image/webp" multiple required>
                <div class="form-text">JPG, PNG o WEBP. Puedes seleccionar varias; se añaden a las que ya tiene el hotel.</div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3" data-hotel-image-preview></div>

            <button type="submit" class="btn btn-primary">Subir imágenes</button>
        </form>

        <?php if ($datos['id_hotel']): ?>
            <h2 class="h4 mt-5 mb-3">Imágenes actuales</h2>

            <?php if (empty($datos['imagenes'])): ?>
                <p class="text-muted">Este hotel todavía no tiene imágenes.</p>
            <?php endif; ?>

            <div class="row row-cols-2 row-cols-md-4 g-3">
                <?php foreach ($datos['imagenes'] as $i => $imagen): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo e($imagen['url']); ?>" class="card-img-top"
                                 alt="Imagen <?php echo $i + 1; ?>" style="object-fit: cover; height: 130px;">
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?php echo $i === 0 ? 'Portada' : '#' . ($i + 1); ?></small>
                                <form method="post" data-hotel-image-delete="<?php echo (int) $datos['id_hotel']; ?>"
                                      action="<?php echo RUTA_URL; ?>/hoteles/borrarImagen/<?php echo (int) $datos['id_hotel']; ?>"
                                      data-confirmar="¿Seguro que quieres borrar esta imagen?"
                                      data-confirmar-boton="Borrar">
                                    <input type="hidden" name="imagen" value="<?php echo e($imagen['archivo']); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Borrar" aria-label="Borrar imagen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a class="btn btn-link px-0 mt-3" href="<?php echo RUTA_URL; ?>/hoteles/ver/<?php echo (int) $datos['id_hotel']; ?>">Ver el hotel &rarr;</a>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
$scripts = ['hoteles/ui/hotelPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
