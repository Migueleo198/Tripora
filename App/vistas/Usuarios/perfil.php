<?php
require RUTA_APP . '/vistas/inc/header.php';

// Se usa $usuarioForm y no $usuario porque el header ya usa $usuario para la sesión
$usuarioForm = $datos['usuarioForm'];
$iniciales = mb_strtoupper(mb_substr($usuarioForm->nombre, 0, 1) . mb_substr($usuarioForm->apellidos, 0, 1));
?>

<style>
    .perfil-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 5rem;
        height: 5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #304fa5, #8065f5);
        color: #fff;
        font-size: 1.75rem;
        font-weight: 700;
    }
</style>

<main class="container py-4 py-lg-5">
    <h1 class="mb-4">Mi perfil</h1>

    <?php if ($datos['ok'] === 'datos'): ?>
        <div class="alert alert-success">Datos guardados.</div>
    <?php elseif ($datos['ok'] === 'contrasena'): ?>
        <div class="alert alert-success">Contraseña cambiada.</div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Columna izquierda: resumen de la cuenta -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-3"><span class="perfil-avatar"><?php echo e($iniciales); ?></span></div>
                <h2 class="h4 mb-1"><?php echo e($usuarioForm->nombre . ' ' . $usuarioForm->apellidos); ?></h2>
                <p class="text-muted mb-2"><?php echo e($usuarioForm->email); ?></p>
                <div>
                    <span class="badge <?php echo $usuarioForm->rol === 'ADMIN' ? 'text-bg-primary' : 'text-bg-secondary'; ?>">
                        <?php echo $usuarioForm->rol === 'ADMIN' ? 'Administrador' : 'Cliente'; ?>
                    </span>
                </div>
                <p class="text-muted small mt-3 mb-0">Miembro desde <?php echo formatoFecha($usuarioForm->fecha_registro); ?></p>
            </div>

            <div class="list-group shadow-sm mt-4">
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                   href="<?php echo RUTA_URL; ?>/reservas">
                    <span><i class="bi bi-airplane me-2"></i> Mis viajes</span>
                    <span class="badge rounded-pill text-bg-primary"><?php echo (int) $datos['numViajes']; ?></span>
                </a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                   href="<?php echo RUTA_URL; ?>/reservasHoteles">
                    <span><i class="bi bi-building me-2"></i> Mis hoteles</span>
                    <span class="badge rounded-pill text-bg-primary"><?php echo (int) $datos['numHoteles']; ?></span>
                </a>
                <a class="list-group-item list-group-item-action text-danger" href="<?php echo RUTA_URL; ?>/login/logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                </a>
            </div>
        </div>

        <!-- Columna derecha: editar datos y cambiar contraseña -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <h2 class="h5 mb-3">Mis datos</h2>

                <?php if (!empty($datos['error'])): ?>
                    <div class="alert alert-danger"><?php echo e($datos['error']); ?></div>
                <?php endif; ?>

                <form method="post" action="<?php echo RUTA_URL; ?>/usuarios/perfil">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50" required
                                   value="<?php echo e($usuarioForm->nombre); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" maxlength="100" required
                                   value="<?php echo e($usuarioForm->apellidos); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="DNI" class="form-label">DNI o NIE</label>
                            <input type="text" class="form-control" id="DNI" name="DNI" maxlength="9" required
                                   value="<?php echo e($usuarioForm->DNI); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nac" class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nac" name="fecha_nac" required
                                   max="<?php echo date('Y-m-d'); ?>" value="<?php echo e($usuarioForm->fecha_nac); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100" required
                                   value="<?php echo e($usuarioForm->email); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="20" required
                                   value="<?php echo e($usuarioForm->telefono); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">
                        <i class="bi bi-check-lg"></i> Guardar cambios
                    </button>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4 mt-4">
                <h2 class="h5 mb-3">Cambiar contraseña</h2>

                <?php if (!empty($datos['errorContrasena'])): ?>
                    <div class="alert alert-danger"><?php echo e($datos['errorContrasena']); ?></div>
                <?php endif; ?>

                <form method="post" action="<?php echo RUTA_URL; ?>/usuarios/contrasena">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="actual" class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" id="actual" name="actual" autocomplete="current-password" required>
                        </div>
                        <div class="col-md-4">
                            <label for="nueva" class="form-label">Nueva</label>
                            <input type="password" class="form-control" id="nueva" name="nueva" minlength="6" autocomplete="new-password" required>
                        </div>
                        <div class="col-md-4">
                            <label for="repetir" class="form-label">Repite la nueva</label>
                            <input type="password" class="form-control" id="repetir" name="repetir" minlength="6" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-primary mt-4">
                        <i class="bi bi-key"></i> Cambiar contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require RUTA_APP . '/vistas/inc/footer.php'; ?>
