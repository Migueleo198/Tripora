<?php $usuario = $datos['usuario'] ?? null; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tripora - Crear cuenta</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo RUTA_URL; ?>/images/icons/favicon-32.png">
    <link rel='stylesheet' href='<?php echo RUTA_URL; ?>/css/header.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="tripora-login">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Crear cuenta</h2>

                        <?php if (!empty($datos['error'])): ?>
                            <div class="alert alert-danger"><?php echo e($datos['error']); ?></div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo RUTA_URL; ?>/login/registro">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50" required autofocus
                                           value="<?php echo e($usuario->nombre ?? ''); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="apellidos" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" maxlength="100" required
                                           value="<?php echo e($usuario->apellidos ?? ''); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="DNI" class="form-label">DNI o NIE</label>
                                    <input type="text" class="form-control" id="DNI" name="DNI" maxlength="9" required placeholder="12345678Z"
                                           value="<?php echo e($usuario->DNI ?? ''); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="fecha_nac" class="form-label">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nac" name="fecha_nac" required
                                           max="<?php echo date('Y-m-d'); ?>"
                                           value="<?php echo e($usuario->fecha_nac ?? ''); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" maxlength="100" required
                                           value="<?php echo e($usuario->email ?? ''); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="20" required
                                           value="<?php echo e($usuario->telefono ?? ''); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="contrasena" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena"
                                           minlength="6" autocomplete="new-password" required>
                                    <div class="form-text">Mínimo 6 caracteres.</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="repetir" class="form-label">Repite la contraseña</label>
                                    <input type="password" class="form-control" id="repetir" name="repetir"
                                           minlength="6" autocomplete="new-password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-4">Crear cuenta</button>
                        </form>

                        <div class="text-center mt-3">
                            ¿Ya tienes cuenta? <a href="<?php echo RUTA_URL; ?>/login">Inicia sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
