<?php
require RUTA_APP . '/vistas/inc/header.php';

$hotel = $datos['hotel'];
$idHotel = (int) $hotel->id_hotel;
?>

<main class="container py-4" style="max-width: 720px;">
    <a href="<?php echo RUTA_URL; ?>/hoteles" class="btn btn-link text-decoration-none px-0 mb-3">
        <i class="bi bi-arrow-left"></i> Volver a hoteles
    </a>

    <h1 class="mb-1">Comidas de <?php echo e($hotel->nombre); ?></h1>
    <p class="text-muted mb-4">
        Marca los regímenes que ofrece el hotel y cuánto se suma por noche al precio
        <?php if ($hotel->precio_noche !== null): ?>
            (<?php echo number_format((float) $hotel->precio_noche, 2, ',', '.'); ?> €)
        <?php endif; ?>.
    </p>

    <?php if (!empty($datos['guardado'])): ?>
        <div class="alert alert-success">Regímenes guardados.</div>
    <?php endif; ?>

    <?php if (!empty($datos['error'])): ?>
        <div class="alert alert-danger"><?php echo e($datos['error']); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo RUTA_URL; ?>/hoteles/regimenes/<?php echo $idHotel; ?>">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Ofrece</th>
                    <th>Régimen</th>
                    <th style="width: 180px;">Suplemento por noche</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (nombresRegimen() as $sigla => $nombre): ?>
                    <?php $ofrece = array_key_exists($sigla, $datos['actuales']); ?>
                    <tr>
                        <td>
                            <input class="form-check-input" type="checkbox" name="ofrece[]" value="<?php echo $sigla; ?>"
                                   id="ofrece<?php echo $sigla; ?>" <?php echo $ofrece ? 'checked' : ''; ?>>
                        </td>
                        <td>
                            <label for="ofrece<?php echo $sigla; ?>">
                                <span class="badge text-bg-light me-1"><?php echo $sigla; ?></span> <?php echo e($nombre); ?>
                            </label>
                        </td>
                        <td>
                            <?php if ($sigla === 'SA'): ?>
                                <span class="text-muted">0 € (precio base)</span>
                            <?php else: ?>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" name="suplemento[<?php echo $sigla; ?>]"
                                           min="0" step="0.01" value="<?php echo $ofrece ? e($datos['actuales'][$sigla]) : ''; ?>">
                                    <span class="input-group-text">€</span>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Guardar
        </button>
    </form>
</main>

<?php require RUTA_APP . '/vistas/inc/footer.php'; ?>
