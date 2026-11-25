<?php $imagen = imagenViaje($viaje->id_viaje); ?>
<?php // $oculto lo pone la página principal cuando la tarjeta no coincide con ?q= ?>
<div class="col <?php echo !empty($oculto) ? 'd-none' : ''; ?>" data-search-item
     data-search="<?php echo e($viaje->nombre . ' ' . $viaje->ubicacion . ' ' . ($viaje->descripcion ?? '')); ?>">
    <div class="card h-100 shadow-sm">
        <?php if ($imagen): ?>
            <img src="<?php echo e($imagen); ?>" class="card-img-top" alt="<?php echo e($viaje->nombre); ?>" style="object-fit: cover; height: 200px;">
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title"><?php echo e($viaje->nombre); ?></h5>
            <p class="card-text text-muted mb-1"><?php echo e($viaje->ubicacion); ?></p>
            <p class="card-text fw-bold"><?php echo number_format((float) $viaje->precio, 2, ',', '.'); ?> €</p>
        </div>
        <div class="card-footer bg-transparent border-0">
            <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/viajes/ver/<?php echo (int) $viaje->id_viaje; ?>">Ver más</a>
        </div>
    </div>
</div>
