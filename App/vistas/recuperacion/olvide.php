<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tripora - Recuperar contraseña</title>
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
                        <h2 class="card-title text-center mb-3">Recuperar contraseña</h2>

                        <?php if (!empty($datos['mensaje'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($datos['mensaje']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($datos['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($datos['error']); ?></div>
                        <?php endif; ?>

                        <?php // Antes de enviar el correo: pedir el email ?>
                        <?php if (empty($datos['email'])): ?>
                            <p class="text-muted small">Escribe tu correo y te enviaremos un enlace y un código para cambiar la contraseña.</p>

                            <form method="post" action="<?php echo RUTA_URL; ?>/login/olvide">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Enviar</button>
                            </form>
                        <?php else: ?>
                            <?php // Después de enviarlo: pulsar el enlace del correo o escribir aquí el código ?>
                            <p class="text-muted small">Pulsa el enlace del correo o escribe aquí el código de 6 dígitos.</p>

                            <form method="post" action="<?php echo RUTA_URL; ?>/login/codigo">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($datos['email']); ?>">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código</label>
                                    <input type="text" class="form-control form-control-lg text-center" id="codigo" name="codigo"
                                           inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                                           placeholder="000000" required autofocus>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Comprobar código</button>
                            </form>

                            <div class="text-center mt-3 small">
                                ¿No te ha llegado? <a href="<?php echo RUTA_URL; ?>/login/olvide">Pedir otro</a>
                            </div>
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
