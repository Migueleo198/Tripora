<?php require RUTA_APP . '/vistas/inc/header.php'; ?>

<main class="container py-5" style="max-width: 860px;">
    <h1 class="mb-4">Imágenes de un viaje</h1>

    <div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-trip-error>
        <?php echo e($datos['error'] ?? ''); ?>
    </div>

    <?php if (empty($datos['viajes'])): ?>
        <div class="alert alert-info">
            No hay viajes. <a href="<?php echo RUTA_URL; ?>/viajes/crear">Crea uno primero</a>.
        </div>
    <?php else: ?>
        <form action="<?php echo RUTA_URL; ?>/viajes/subirImagen" method="post" enctype="multipart/form-data" data-trip-image-form>
            <div class="mb-3">
                <label for="id_viaje" class="form-label">Seleccionar viaje</label>
                <select name="id_viaje" id="id_viaje" class="form-select" required data-trip-image-select>
                    <option value="">Selecciona un viaje</option>
                    <?php foreach ($datos['viajes'] as $viaje): ?>
                        <option value="<?php echo (int) $viaje->id_viaje; ?>"
                            <?php echo (int) $viaje->id_viaje === $datos['id_viaje'] ? 'selected' : ''; ?>>
                            <?php echo e($viaje->nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="imagenes" class="form-label">Añadir imágenes</label>
                <input type="file" name="imagenes[]" id="imagenes" class="form-control"
                       accept="image/jpeg,image/png,image/webp" multiple required>
                <div class="form-text">JPG, PNG o WEBP. Puedes seleccionar varias; se añaden a las que ya tiene el viaje.</div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3" data-trip-image-preview></div>

            <button type="submit" class="btn btn-primary">Subir imágenes</button>
        </form>

        <?php if ($datos['id_viaje']): ?>
            <h2 class="h4 mt-5 mb-3">Imágenes actuales</h2>

            <p class="text-muted <?php echo empty($datos['imagenes']) ? '' : 'd-none'; ?>" data-trip-images-empty>
                Este viaje todavía no tiene imágenes.
            </p>

            <div class="row row-cols-2 row-cols-md-4 g-3">
                <?php foreach ($datos['imagenes'] as $i => $imagen): ?>
                    <div class="col" data-trip-image-item>
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo e($imagen['url']); ?>" class="card-img-top"
                                 alt="Imagen <?php echo $i + 1; ?>" style="object-fit: cover; height: 130px;">
                            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?php echo $i === 0 ? 'Portada' : '#' . ($i + 1); ?></small>
                                <form method="post" data-trip-image-delete
                                      action="<?php echo RUTA_URL; ?>/viajes/borrarImagen/<?php echo (int) $datos['id_viaje']; ?>"
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

            <a class="btn btn-link px-0 mt-3" href="<?php echo RUTA_URL; ?>/viajes/ver/<?php echo (int) $datos['id_viaje']; ?>">Ver el viaje &rarr;</a>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
$scripts = ['viajes/ui/tripPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
