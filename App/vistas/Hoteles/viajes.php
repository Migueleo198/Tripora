<?php
require RUTA_APP . '/vistas/inc/header.php';

$hotel = $datos['hotel'];
$idHotel = (int) $hotel->id_hotel;
?>

<main class="container py-4" style="max-width: 860px;">
    <a href="<?php echo RUTA_URL; ?>/hoteles" class="btn btn-link text-decoration-none px-0 mb-3">
        <i class="bi bi-arrow-left"></i> Volver a hoteles
    </a>

    <h1 class="mb-1">Viajes de <?php echo e($hotel->nombre); ?></h1>
    <p class="text-muted mb-4"><?php echo e($hotel->ciudad); ?></p>

    <div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>">
        <?php echo e($datos['error'] ?? ''); ?>
    </div>

    <?php if (empty($datos['viajesHotel'])): ?>
        <div class="alert alert-info">Este hotel todavía no está en ningún viaje.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Viaje</th>
                        <th>Ubicación</th>
                        <th class="text-end">Noches</th>
                        <th>Comidas</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos['viajesHotel'] as $viaje): ?>
                        <tr>
                            <td>
                                <a href="<?php echo RUTA_URL; ?>/viajes/ver/<?php echo (int) $viaje->id_viaje; ?>">
                                    <?php echo e($viaje->nombre); ?>
                                </a>
                            </td>
                            <td><?php echo e($viaje->ubicacion); ?></td>
                            <td class="text-end"><?php echo (int) $viaje->noches; ?></td>
                            <td><?php echo e(nombresRegimen()[$viaje->regimen] ?? $viaje->regimen); ?></td>
                            <td class="text-end">
                                <form class="d-inline" method="post"
                                      action="<?php echo RUTA_URL; ?>/hoteles/quitarViaje/<?php echo $idHotel; ?>"
                                      data-confirmar="¿Quitar «<?php echo e($hotel->nombre); ?>» del viaje «<?php echo e($viaje->nombre); ?>»?"
                                      data-confirmar-boton="Quitar">
                                    <input type="hidden" name="id_viaje" value="<?php echo (int) $viaje->id_viaje; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Quitar" aria-label="Quitar del viaje">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h2 class="h4 mt-5 mb-3">Añadir a un viaje</h2>

    <?php if (empty($datos['viajesDisponibles'])): ?>
        <p class="text-muted">El hotel ya está en todos los viajes.</p>
    <?php else: ?>
        <form method="post" action="<?php echo RUTA_URL; ?>/hoteles/viajes/<?php echo $idHotel; ?>" class="row g-3 align-items-end">
            <div class="col-sm-5">
                <label for="id_viaje" class="form-label">Viaje</label>
                <select name="id_viaje" id="id_viaje" class="form-select" required>
                    <option value="">Selecciona un viaje</option>
                    <?php foreach ($datos['viajesDisponibles'] as $viaje): ?>
                        <option value="<?php echo (int) $viaje->id_viaje; ?>">
                            <?php echo e($viaje->nombre); ?> (<?php echo (int) $viaje->dias; ?> días)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="noches" class="form-label">Noches</label>
                <input type="number" name="noches" id="noches" class="form-control" min="1" max="255" value="1" required>
            </div>

            <?php // Solo los regímenes que ofrece el hotel (se configuran en el botón de comidas) ?>
            <div class="col-sm-3">
                <label for="regimen" class="form-label">Comidas incluidas</label>
                <select name="regimen" id="regimen" class="form-select" required>
                    <?php foreach ($datos['regimenes'] as $fila): ?>
                        <option value="<?php echo e($fila->regimen); ?>">
                            <?php echo e(nombresRegimen()[$fila->regimen] ?? $fila->regimen); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-sm-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Añadir
                </button>
            </div>
        </form>
        <div class="form-text">El hotel se añade al final de la estancia del viaje.</div>
    <?php endif; ?>
</main>

<?php require RUTA_APP . '/vistas/inc/footer.php'; ?>
