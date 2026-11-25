<?php require RUTA_APP . '/vistas/inc/header.php'; ?>

<?php
// Resumen: cuántas reservas se ven y cuánto suman las confirmadas
$totalConfirmado = 0;

foreach ($datos['reservas'] as $reserva) {
    if ($reserva->estado === 'CONFIRMADA') {
        $totalConfirmado += $reserva->precio_total;
    }
}

// Botones del filtro: valor del estado => texto
$filtros = ['' => 'Todas', 'PENDIENTE' => 'Pendientes', 'CONFIRMADA' => 'Confirmadas', 'CANCELADA' => 'Canceladas'];
?>

<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="mb-0">Reservas de hoteles</h1>
        <a class="btn btn-outline-primary" href="<?php echo RUTA_URL; ?>/reservas/admin">Ver reservas de viajes</a>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($filtros as $valor => $texto): ?>
            <a class="btn btn-sm <?php echo $datos['estado'] === $valor ? 'btn-primary' : 'btn-outline-secondary'; ?>"
               href="<?php echo RUTA_URL; ?>/reservasHoteles/admin<?php echo $valor === '' ? '' : '?estado=' . $valor; ?>">
                <?php echo $texto; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <p class="text-muted">
        <?php echo count($datos['reservas']); ?> reservas ·
        total confirmado: <strong><?php echo number_format($totalConfirmado, 2, ',', '.'); ?> €</strong>
    </p>

    <?php if (empty($datos['reservas'])): ?>
        <div class="alert alert-info">No hay reservas.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Reservado</th>
                        <th>Cliente</th>
                        <th>Hotel</th>
                        <th>Estancia</th>
                        <th class="text-end">Precio</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos['reservas'] as $reserva): ?>
                        <tr>
                            <td><?php echo formatoFecha($reserva->fecha_reserva); ?></td>
                            <td>
                                <?php echo e($reserva->nombre . ' ' . $reserva->apellidos); ?>
                                <div class="small text-muted"><?php echo e($reserva->email); ?></div>
                            </td>
                            <td>
                                <a href="<?php echo RUTA_URL; ?>/hoteles/ver/<?php echo (int) $reserva->id_hotel; ?>">
                                    <?php echo e($reserva->hotel); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo formatoFecha($reserva->fecha_entrada); ?>
                                <div class="small text-muted"><?php echo (int) $reserva->noches; ?> <?php echo (int) $reserva->noches === 1 ? 'noche' : 'noches'; ?></div>
                                <div class="small text-muted"><?php echo e(nombresRegimen()[$reserva->regimen] ?? $reserva->regimen); ?></div>
                            </td>
                            <td class="text-end"><?php echo number_format((float) $reserva->precio_total, 2, ',', '.'); ?> €</td>
                            <td>
                                <?php if ($reserva->estado === 'CONFIRMADA'): ?>
                                    <span class="badge text-bg-success">Confirmada</span>
                                <?php elseif ($reserva->estado === 'CANCELADA'): ?>
                                    <span class="badge text-bg-secondary">Cancelada</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php // Solo se muestran los botones que cambian algo ?>
                                <?php if ($reserva->estado !== 'CONFIRMADA'): ?>
                                    <form class="d-inline" method="post"
                                          action="<?php echo RUTA_URL; ?>/reservasHoteles/cambiarEstado/<?php echo (int) $reserva->id_reserva_hotel; ?>">
                                        <input type="hidden" name="estado" value="CONFIRMADA">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Confirmar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($reserva->estado !== 'CANCELADA'): ?>
                                    <form class="d-inline" method="post"
                                          action="<?php echo RUTA_URL; ?>/reservasHoteles/cambiarEstado/<?php echo (int) $reserva->id_reserva_hotel; ?>"
                                          data-confirmar="¿Cancelar la reserva de <?php echo e($reserva->nombre); ?> en «<?php echo e($reserva->hotel); ?>»?"
                                          data-confirmar-boton="Cancelar reserva">
                                        <input type="hidden" name="estado" value="CANCELADA">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require RUTA_APP . '/vistas/inc/footer.php'; ?>
