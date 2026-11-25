<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tripora - Nueva contraseña</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo RUTA_URL; ?>/images/icons/favicon-32.png">
    <link rel='stylesheet' href='<?php echo RUTA_URL; ?>/css/header.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="tripora-login">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-3">Nueva contraseña</h2>

                        <?php if (!empty($datos['enlaceMalo'])): ?>
                            <div class="alert alert-danger">El enlace no es válido o ha caducado.</div>
                            <a class="btn btn-primary w-100" href="<?php echo RUTA_URL; ?>/login/olvide">Pedir otro enlace</a>
                        <?php else: ?>
                            <p class="text-muted small">Cuenta: <?php echo htmlspecialchars($datos['email']); ?></p>

                            <?php if (!empty($datos['error'])): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($datos['error']); ?></div>
                            <?php endif; ?>

                            <?php // Llega desde el enlace (token en la url) o desde el código (email y código en campos ocultos) ?>
                            <form method="post" action="<?php echo htmlspecialchars($datos['accion'] ?? RUTA_URL . '/login/restablecer/' . $datos['token']); ?>">
                                <?php if (isset($datos['codigo'])): ?>
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($datos['email']); ?>">
                                    <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($datos['codigo']); ?>">
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label for="contrasena" class="form-label">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" minlength="6" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label for="repetir" class="form-label">Repite la contraseña</label>
                                    <input type="password" class="form-control" id="repetir" name="repetir" minlength="6" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Cambiar contraseña</button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="<?php echo RUTA_URL; ?>/login">Volver a iniciar sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
