<?php
// Formulario compartido por crear y editar
// Necesita: $viaje (objeto o null), $accion (url del form), $textoBoton
// Opcional: $actividades (catálogo) para elegir las actividades del viaje
$elegidas = array_column($viaje->actividades ?? [], null, 'id_actividad');
?>
<div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-trip-error>
    <?php echo e($datos['error'] ?? ''); ?>
</div>

<form method="post" action="<?php echo $accion; ?>"
      data-trip-form data-trip-id="<?php echo isset($viaje->id_viaje) ? (int) $viaje->id_viaje : ''; ?>">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
               value="<?php echo e($viaje->nombre ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label for="ubicacion" class="form-label">Ubicación</label>
        <input type="text" class="form-control" id="ubicacion" name="ubicacion" maxlength="150" required
               value="<?php echo e($viaje->ubicacion ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label for="precio" class="form-label">Precio (€)</label>
        <input type="number" class="form-control" id="precio" name="precio" min="0" step="0.01" required
               value="<?php echo e($viaje->precio ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label for="dias" class="form-label">Días</label>
        <input type="number" class="form-control" id="dias" name="dias" min="1" max="255" step="1" required
               value="<?php echo e($viaje->dias ?? ''); ?>">
    </div>

    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="5"><?php echo e($viaje->descripcion ?? ''); ?></textarea>
    </div>

    <?php if (!empty($actividades)): ?>
        <fieldset class="mb-4">
            <legend class="fs-5">Actividades</legend>
            <?php // Marca para el controlador: este formulario trae la lista de actividades ?>
            <input type="hidden" name="actividades_enviadas" value="1">
            <p class="form-text mt-0">Marca las actividades del viaje. Día entre 1 y los días del viaje; la hora es opcional.</p>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Actividad</th>
                            <th style="width: 6rem;">Día</th>
                            <th style="width: 8rem;">Hora</th>
                            <th style="width: 8rem;">Duración</th>
                            <th style="width: 8rem;">Precio (€)</th>
                            <th class="text-center">Opcional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades as $actividad): ?>
                            <?php
                            $idActividad = (int) $actividad->id_actividad;
                            $elegida = $elegidas[$idActividad] ?? null;
                            $campo = 'actividades[' . $idActividad . ']';
                            ?>
                            <tr data-trip-activity="<?php echo $idActividad; ?>">
                                <td>
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="actividad<?php echo $idActividad; ?>"
                                           name="<?php echo $campo; ?>[incluir]" <?php echo $elegida ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <label class="form-check-label" for="actividad<?php echo $idActividad; ?>"
                                           title="<?php echo e($actividad->descripcion); ?>">
                                        <?php echo e($actividad->nombre); ?>
                                    </label>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" min="1" max="255" step="1"
                                           name="<?php echo $campo; ?>[dia]" value="<?php echo e($elegida['dia'] ?? '1'); ?>">
                                </td>
                                <td>
                                    <input type="time" class="form-control form-control-sm"
                                           name="<?php echo $campo; ?>[hora]" value="<?php echo e($elegida['hora'] ?? ''); ?>">
                                </td>
                                <td>
                                    <input type="time" class="form-control form-control-sm"
                                           name="<?php echo $campo; ?>[duracion]" value="<?php echo e($elegida['duracion'] ?? '02:00'); ?>">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" min="0" step="0.01"
                                           name="<?php echo $campo; ?>[precio]" value="<?php echo e($elegida['precio'] ?? '0'); ?>">
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           name="<?php echo $campo; ?>[opcional]" <?php echo !empty($elegida['opcional']) ? 'checked' : ''; ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </fieldset>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?php echo e($textoBoton); ?></button>
        <a class="btn btn-outline-secondary" href="<?php echo RUTA_URL; ?>/viajes">Cancelar</a>
    </div>
</form>
