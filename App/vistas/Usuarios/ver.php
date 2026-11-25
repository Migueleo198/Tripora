<?php
require RUTA_APP . '/vistas/inc/header.php';

// $usuario ya lo usa el header para el usuario de la sesión
$usuarioVer = $datos['usuario'];
$idUsuario = (int) $usuarioVer->id_usuario;
$esPropio = $idUsuario === (int) ($usuario['id_usuario'] ?? 0);
?>

<main class="container py-5">
    <?php if ($esAdmin): ?>
        <a href="<?php echo RUTA_URL; ?>/usuarios" class="btn btn-link px-0 mb-3">&larr; Volver a usuarios</a>
    <?php else: ?>
        <a href="<?php echo RUTA_URL; ?>/inicio" class="btn btn-link px-0 mb-3">&larr; Volver al inicio</a>
    <?php endif; ?>

    <div class="alert alert-danger d-none" data-user-error></div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h2 mb-1"><?php echo e($usuarioVer->nombre . ' ' . $usuarioVer->apellidos); ?></h1>
                    <span class="badge <?php echo $usuarioVer->rol === 'ADMIN' ? 'text-bg-primary' : 'text-bg-secondary'; ?>">
                        <?php echo e($usuarioVer->rol); ?>
                    </span>
                </div>

                <?php if ($esAdmin): ?>
                    <div class="d-flex gap-2">
                        <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/usuarios/editar/<?php echo $idUsuario; ?>">Editar</a>
                        <?php // No se permite borrar el usuario con el que se ha iniciado sesión ?>
                        <?php if (!$esPropio): ?>
                            <form method="post" data-user-delete="<?php echo $idUsuario; ?>"
                                  action="<?php echo RUTA_URL; ?>/usuarios/borrar/<?php echo $idUsuario; ?>"
                                  data-confirmar="¿Seguro que quieres borrar a <?php echo e($usuarioVer->nombre . ' ' . $usuarioVer->apellidos); ?>?"
                                  data-confirmar-boton="Borrar">
                                <button type="submit" class="btn btn-outline-danger">Borrar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-4 col-lg-3">DNI / NIE</dt>
                <dd class="col-sm-8 col-lg-9"><?php echo e($usuarioVer->DNI); ?></dd>

                <dt class="col-sm-4 col-lg-3">Email</dt>
                <dd class="col-sm-8 col-lg-9">
                    <a href="mailto:<?php echo e($usuarioVer->email); ?>"><?php echo e($usuarioVer->email); ?></a>
                </dd>

                <dt class="col-sm-4 col-lg-3">Teléfono</dt>
                <dd class="col-sm-8 col-lg-9"><?php echo e($usuarioVer->telefono); ?></dd>

                <dt class="col-sm-4 col-lg-3">Fecha de nacimiento</dt>
                <dd class="col-sm-8 col-lg-9"><?php echo e(formatoFecha($usuarioVer->fecha_nac)); ?></dd>

                <dt class="col-sm-4 col-lg-3">Fecha de registro</dt>
                <dd class="col-sm-8 col-lg-9"><?php echo e(formatoFecha($usuarioVer->fecha_registro)); ?></dd>
            </dl>
        </div>
    </div>
</main>

<?php
$scripts = ['clients/ui/clientPage.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
