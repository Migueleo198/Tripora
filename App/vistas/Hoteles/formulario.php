<?php
// Formulario compartido por crear y editar hotel
// Necesita: $hotelForm (objeto o null), $accion (url del form), $textoBoton
$estrellasActual = (string) ($hotelForm->estrellas ?? '');
?>
<div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-hotel-error>
    <?php echo e($datos['error'] ?? ''); ?>
</div>

<form method="post" action="<?php echo $accion; ?>"
      data-hotel-form data-hotel-id="<?php echo isset($hotelForm->id_hotel) ? (int) $hotelForm->id_hotel : ''; ?>">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
                   value="<?php echo e($hotelForm->nombre ?? ''); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="ciudad" class="form-label">Ciudad</label>
            <input type="text" class="form-control" id="ciudad" name="ciudad" maxlength="100" required
                   value="<?php echo e($hotelForm->ciudad ?? ''); ?>">
        </div>

        <div class="col-12 col-md-8">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" maxlength="150" required
                   value="<?php echo e($hotelForm->direccion ?? ''); ?>">
        </div>

        <div class="col-12">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?php echo e($hotelForm->descripcion ?? ''); ?></textarea>
            <div class="form-text">Opcional. Se muestra a los clientes.</div>
        </div>

        <div class="col-12 col-md-4">
            <label for="estrellas" class="form-label">Estrellas</label>
            <select class="form-select" id="estrellas" name="estrellas">
                <option value="">Sin clasificar</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"
                        <?php echo $estrellasActual === (string) $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?> <?php echo $i === 1 ? 'estrella' : 'estrellas'; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label for="precio_noche" class="form-label">Precio por noche (€)</label>
            <input type="number" class="form-control" id="precio_noche" name="precio_noche" min="0" step="0.01"
                   value="<?php echo e($hotelForm->precio_noche ?? ''); ?>">
            <div class="form-text">Opcional. Déjalo vacío si el hotel solo se ofrece dentro de un viaje.</div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><?php echo e($textoBoton); ?></button>
        <a class="btn btn-outline-secondary" href="<?php echo RUTA_URL; ?>/hoteles">Cancelar</a>
    </div>
</form>
