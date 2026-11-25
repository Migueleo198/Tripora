<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tripora Login</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo RUTA_URL; ?>/images/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo RUTA_URL; ?>/images/icons/favicon-192.png">
    <link rel="apple-touch-icon" href="<?php echo RUTA_URL; ?>/images/icons/apple-touch-icon.png">
    <link rel='stylesheet' href='<?php echo RUTA_URL; ?>/css/header.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="tripora-login">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Iniciar sesión</h2>

                        <?php if (!empty($datos['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($datos['error']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($datos['mensaje'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($datos['mensaje']); ?></div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo RUTA_URL; ?>/login/login">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="<?php echo RUTA_URL; ?>/login/olvide">¿Has olvidado tu contraseña?</a>
                        </div>

                        <hr>

                        <div class="text-center">
                            ¿No tienes cuenta? <a href="<?php echo RUTA_URL; ?>/login/registro">Regístrate</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
