<?php require RUTA_APP . '/vistas/inc/header.php'; ?>

<main class="container py-4" style="max-width: 900px;">
    <h1 class="mb-4">Mis hoteles</h1>

    <?php if (empty($datos['reservas'])): ?>
        <div class="alert alert-info">
            Todavía no has reservado ningún hotel.
            <a href="<?php echo RUTA_URL; ?>/hoteles/vistaCliente">Mira los hoteles disponibles</a>.
        </div>
    <?php else: ?>
        <div class="d-grid gap-3">
            <?php foreach ($datos['reservas'] as $reserva): ?>
                <?php $imagen = imagenHotel($reserva->id_hotel); ?>

                <?php // La salida es la fecha de entrada más las noches ?>
                <?php $fecha_salida = date('Y-m-d', strtotime($reserva->fecha_entrada . ' +' . (int) $reserva->noches . ' days')); ?>

                <div class="card shadow-sm border-0">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <?php if ($imagen): ?>
                                <img src="<?php echo e($imagen); ?>" class="img-fluid rounded-start h-100"
                                     alt="<?php echo e($reserva->nombre); ?>" style="object-fit: cover; min-height: 140px;">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-9">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h2 class="h5 mb-1">
                                        <a href="<?php echo RUTA_URL; ?>/hoteles/ver/<?php echo (int) $reserva->id_hotel; ?>">
                                            <?php echo e($reserva->nombre); ?>
                                        </a>
                                        <?php if ($reserva->estrellas !== null): ?>
                                            <span class="text-warning small"><?php echo str_repeat('★', (int) $reserva->estrellas); ?></span>
                                        <?php endif; ?>
                                    </h2>

                                    <?php // Color de la etiqueta según el estado de la reserva ?>
                                    <?php if ($reserva->estado === 'CONFIRMADA'): ?>
                                        <span class="badge text-bg-success">Confirmada</span>
                                    <?php elseif ($reserva->estado === 'CANCELADA'): ?>
                                        <span class="badge text-bg-secondary">Cancelada</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning">Pendiente</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?php echo e($reserva->ciudad); ?>
                                </p>

                                <p class="mb-1">
                                    <i class="bi bi-calendar3"></i>
                                    Del <?php echo formatoFecha($reserva->fecha_entrada); ?>
                                    al <?php echo formatoFecha($fecha_salida); ?>
                                    (<?php echo (int) $reserva->noches; ?> <?php echo (int) $reserva->noches === 1 ? 'noche' : 'noches'; ?>)
                                </p>

                                <p class="mb-1">
                                    <i class="bi bi-cup-hot"></i> <?php echo e(nombresRegimen()[$reserva->regimen] ?? $reserva->regimen); ?>
                                </p>

                                <p class="text-muted small mb-1">
                                    Reservado el <?php echo formatoFecha($reserva->fecha_reserva); ?>
                                </p>

                                <p class="fw-bold mb-3">
                                    <?php echo number_format((float) $reserva->precio_total, 2, ',', '.'); ?> €
                                </p>

                                <?php if ($reserva->estado !== 'CANCELADA'): ?>
                                    <form method="post"
                                          action="<?php echo RUTA_URL; ?>/reservasHoteles/cancelar/<?php echo (int) $reserva->id_reserva_hotel; ?>"
                                          data-confirmar="¿Seguro que quieres cancelar la reserva en «<?php echo e($reserva->nombre); ?>»?"
                                          data-confirmar-boton="Cancelar reserva">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require RUTA_APP . '/vistas/inc/footer.php'; ?>
