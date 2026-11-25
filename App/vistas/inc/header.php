<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Tripora</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo RUTA_URL; ?>/images/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo RUTA_URL; ?>/images/icons/favicon-192.png">
    <link rel="apple-touch-icon" href="<?php echo RUTA_URL; ?>/images/icons/apple-touch-icon.png">
    <link rel='stylesheet' href='<?php echo RUTA_URL; ?>/css/header.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<?php
$usuario = $datos['usuarioSesion'] ?? null;
$esAdmin = ($usuario['rol'] ?? '') === 'ADMIN';
?>

<body data-ruta-url="<?php echo RUTA_URL; ?>">
<header>
    <nav class="navbar navbar-expand-lg navbar-dark tripora-navbar">
        <div class="container-fluid px-4 align-items-center">

            <!-- Logo -->
            <a class="navbar-brand fw-bold fs-3 d-flex align-items-center gap-2" href="<?php echo RUTA_URL; ?>/">
                <img class="tripora-logo" src="<?php echo RUTA_URL; ?>/images/icons/logo-header.png"
                     alt="" width="48" height="40"> Tripora
            </a>

            <!-- Mobile menu button -->
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#triporaNav"
                    aria-controls="triporaNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation links -->
            <div class="collapse navbar-collapse" id="triporaNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo menuActivo($datos, 'Inicio'); ?>" href="<?php echo RUTA_URL; ?>/">Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
    <?php if ($esAdmin): ?>
        <a class="nav-link dropdown-toggle <?php echo menuActivo($datos, 'Viajes'); ?>"
           href="#"
           id="adminViajesDropdown"
           role="button"
           data-bs-toggle="dropdown"
           aria-expanded="false">
            Administrar viajes
        </a>

        <ul class="dropdown-menu" aria-labelledby="adminViajesDropdown">
            <li>
                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/viajes">
                    Ver viajes
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/viajes/crear">
                    Añadir viaje
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/viajes/subirImagen">
                    Imágenes de viajes
                </a>
            </li>
                </ul>
                <?php else: ?>
                    <a class="nav-link <?php echo menuActivo($datos, 'Viajes'); ?>" href="<?php echo RUTA_URL; ?>/viajes">
                         Viajes
                    </a>
                    <?php endif; ?>
                </li>
                    <?php if ($esAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo menuActivo($datos, 'Hoteles'); ?>"
                           href="#"
                           id="adminHotelesDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Administrar hoteles
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="adminHotelesDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/hoteles">
                                    Ver hoteles
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/hoteles/crear">
                                    Añadir hotel
                                </a>
                            </li>

                            <li>
                             <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/hoteles/subirImagen">
                                Imágenes de hoteles
                            </a>
                            </li>
                        </ul>
                    </li>

                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo menuActivo($datos, 'HotelesVistaCliente'); ?>" href="<?php echo RUTA_URL; ?>/hoteles/vistaCliente">Hoteles</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo menuActivo($datos, 'MisViajes'); ?>" href="<?php echo RUTA_URL; ?>/reservas">Mis viajes</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo menuActivo($datos, 'MisHoteles'); ?>" href="<?php echo RUTA_URL; ?>/reservasHoteles">Mis hoteles</a>
                    </li>

                   
                    <li class="nav-item">
                        <a class="nav-link" href="community.html">Comunidad</a>
                    </li>

                    <?php if ($esAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo menuActivo($datos, 'Usuarios'); ?>"
                           href="#"
                           id="adminUsuariosDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Administrar Usuarios
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="adminUsuariosDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/usuarios">
                                    Ver usuarios
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/usuarios/crear">
                                    Añadir usuario
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo menuActivo($datos, 'Reservas'); ?>"
                           href="#"
                           id="adminReservasDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Administrar reservas
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="adminReservasDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/reservas/admin">
                                    Reservas de viajes
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo RUTA_URL; ?>/reservasHoteles/admin">
                                    Reservas de hoteles
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- Account menu -->
                <div class="dropdown ms-lg-3 mt-3 mt-lg-0">
                    <button class="btn btn-outline-light dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        Cuenta
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?php echo menuActivo($datos, 'Perfil'); ?>" href="<?php echo RUTA_URL; ?>/usuarios/perfil">Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="<?php echo RUTA_URL; ?>/reservas">Mis Viajes</a></li>
                        <li><a class="dropdown-item" href="<?php echo RUTA_URL; ?>/reservasHoteles">Mis Hoteles</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if ($usuario): ?>
                            <li><a class="dropdown-item text-danger" href="<?php echo RUTA_URL; ?>/login/logout"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="<?php echo RUTA_URL; ?>/login">Iniciar Sesión</a></li>
                            <li><a class="dropdown-item" href="<?php echo RUTA_URL; ?>/login/registro">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>
