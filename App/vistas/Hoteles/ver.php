<?php
require RUTA_APP . '/vistas/inc/header.php';

$hotel = $datos['hotel'];
$idHotel = (int) $hotel->id_hotel;
$imagenes = imagenesHotel($idHotel);
$nombresRegimen = nombresRegimen();
?>

<style>
    /* Carrusel 3:2 con alto máximo de 570px; las imágenes se recortan para llenar el marco */
    #carruselHotel {
        aspect-ratio: 3 / 2;
        width: 100%;
        max-width: calc(570px * 3 / 2);
        border-radius: 1rem;
        overflow: hidden;
    }

    #carruselHotel .carousel-inner,
    #carruselHotel .carousel-item {
        height: 100%;
    }

    #carruselHotel .carousel-item img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Flechas con fondo para que se vean sobre cualquier imagen */
    #carruselHotel .carousel-control-prev-icon,
    #carruselHotel .carousel-control-next-icon {
        width: 2.75rem;
        height: 2.75rem;
        background-size: 50%;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
    }

    /* Hueco cuando el hotel no tiene imágenes */
    .hotel-sin-imagen {
        aspect-ratio: 3 / 2;
        border-radius: 1rem;
        background: linear-gradient(135deg, #304fa5, #8065f5);
    }

    /* Tarjeta de resumen: se queda fija al bajar en pantallas grandes */
    .hotel-resumen {
        border-radius: 1rem;
    }

    @media (min-width: 992px) {
        .hotel-resumen {
            position: sticky;
            top: 1.5rem;
        }
    }

    .hotel-precio {
        font-size: 2.25rem;
        font-weight: 750;
        letter-spacing: -1px;
        color: #304fa5;
    }

    .hotel-dato {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .hotel-dato i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        background: rgba(128, 101, 245, 0.12);
        color: #6a50e0;
    }
</style>

<main class="container py-4 py-lg-5">
    <a href="<?php echo RUTA_URL; ?>/hoteles<?php echo $esAdmin ? '' : '/vistaCliente'; ?>" class="btn btn-link text-decoration-none px-0 mb-3">
        <i class="bi bi-arrow-left"></i> Volver a hoteles
    </a>

    <div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-hotel-error>
        <?php echo e($datos['error'] ?? ''); ?>
    </div>

    <div class="row g-4 g-lg-5">
        <!-- Columna izquierda: imagen, descripción y detalles -->
        <div class="col-lg-8">
            <?php if ($imagenes): ?>
                <div id="carruselHotel" class="carousel slide mx-auto shadow-sm"
                     data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false">
                    <?php if (count($imagenes) > 1): ?>
                        <div class="carousel-indicators">
                            <?php foreach ($imagenes as $i => $imagen): ?>
                                <button type="button" data-bs-target="#carruselHotel" data-bs-slide-to="<?php echo $i; ?>"
                                        class="<?php echo $i === 0 ? 'active' : ''; ?>"
                                        <?php echo $i === 0 ? 'aria-current="true"' : ''; ?>
                                        aria-label="Imagen <?php echo $i + 1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="carousel-inner">
                        <?php foreach ($imagenes as $i => $imagen): ?>
                            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo e($imagen['url']); ?>"
                                     alt="<?php echo e($hotel->nombre); ?> (<?php echo $i + 1; ?>)">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($imagenes) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carruselHotel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carruselHotel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="hotel-sin-imagen d-flex align-items-center justify-content-center text-white shadow-sm">
                    <i class="bi bi-building fs-1 opacity-75"></i>
                </div>
            <?php endif; ?>

            <?php if (!empty($hotel->descripcion)): ?>
                <section class="mt-5">
                    <h2 class="h4 mb-3">Sobre el hotel</h2>
                    <p class="text-secondary lh-lg mb-0"><?php echo nl2br(e($hotel->descripcion)); ?></p>
                </section>
            <?php endif; ?>

            <section class="mt-5">
                <h2 class="h4 mb-3">Detalles</h2>

                <dl class="row mb-0">
                    <dt class="col-sm-4">Dirección</dt>
                    <dd class="col-sm-8"><?php echo e($hotel->direccion); ?></dd>

                    <dt class="col-sm-4">Ciudad</dt>
                    <dd class="col-sm-8"><?php echo e($hotel->ciudad); ?></dd>

                    <dt class="col-sm-4">Estrellas</dt>
                    <dd class="col-sm-8 mb-0">
                        <?php if ($hotel->estrellas !== null): ?>
                            <span class="text-warning"><?php echo str_repeat('★', (int) $hotel->estrellas); ?></span>
                            (<?php echo (int) $hotel->estrellas; ?>)
                        <?php else: ?>
                            <span class="text-muted">Sin clasificar</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </section>

            <section class="mt-5">
                <h2 class="h4 mb-3">Comidas</h2>

                <ul class="list-group">
                    <?php foreach ($datos['regimenes'] as $fila): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-cup-hot text-secondary me-1"></i>
                                <?php echo e($nombresRegimen[$fila->regimen] ?? $fila->regimen); ?>
                            </span>
                            <span class="text-secondary small">
                                <?php if ((float) $fila->suplemento_noche > 0): ?>
                                    +<?php echo number_format((float) $fila->suplemento_noche, 2, ',', '.'); ?> € / noche
                                <?php else: ?>
                                    Sin suplemento
                                <?php endif; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>

        <!-- Columna derecha: resumen del hotel y acciones (en móvil va la primera) -->
        <div class="col-lg-4 order-first order-lg-last">
            <aside class="hotel-resumen card border-0 shadow-sm p-4">
                <span class="badge rounded-pill text-bg-light align-self-start mb-2">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <?php echo e($hotel->ciudad); ?>
                </span>

                <h1 class="h2 fw-bold mb-3"><?php echo e($hotel->nombre); ?></h1>

                <?php if ($hotel->precio_noche !== null): ?>
                    <div class="text-secondary small">Precio por noche</div>
                    <div class="hotel-precio mb-4"><?php echo number_format((float) $hotel->precio_noche, 2, ',', '.'); ?> €</div>
                <?php else: ?>
                    <p class="text-secondary mb-4">Solo se ofrece dentro de viajes</p>
                <?php endif; ?>

                <div class="d-grid gap-3 mb-4">
                    <div class="hotel-dato">
                        <i class="bi bi-star-fill"></i>
                        <span>
                            <?php if ($hotel->estrellas !== null): ?>
                                <?php echo (int) $hotel->estrellas; ?> <?php echo (int) $hotel->estrellas === 1 ? 'estrella' : 'estrellas'; ?>
                            <?php else: ?>
                                Sin clasificar
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="hotel-dato">
                        <i class="bi bi-signpost"></i>
                        <span><?php echo e($hotel->direccion); ?></span>
                    </div>
                </div>
                <?php // Solo se puede reservar suelto si el hotel tiene precio por noche ?>
                <?php if ($hotel->precio_noche !== null): ?>
                    <hr>
                    <?php // data-precio y data-nombre los usa reservaHotel.js para calcular el total ?>
                    <form method="post" class="d-grid gap-2"
                          action="<?php echo RUTA_URL; ?>/reservasHoteles/reservar/<?php echo $idHotel; ?>"
                          data-reserva-hotel data-precio="<?php echo (float) $hotel->precio_noche; ?>" data-nombre="<?php echo e($hotel->nombre); ?>"
                          data-confirmar="¿Quieres reservar en «<?php echo e($hotel->nombre); ?>»?"
                          data-confirmar-boton="Reservar">
                        <div class="row g-2">
                            <div class="col-7">
                                <label for="fecha_entrada" class="form-label small mb-1">Entrada</label>
                                <input type="date" name="fecha_entrada" id="fecha_entrada" class="form-control"
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-5">
                                <label for="noches" class="form-label small mb-1">Noches</label>
                                <input type="number" name="noches" id="noches" class="form-control"
                                       min="1" max="30" value="1" required>
                            </div>
                        </div>

                        <div>
                            <label for="regimen" class="form-label small mb-1">Comidas</label>
                            <select name="regimen" id="regimen" class="form-select" required>
                                <?php foreach ($datos['regimenes'] as $fila): ?>
                                    <option value="<?php echo e($fila->regimen); ?>" data-suplemento="<?php echo (float) $fila->suplemento_noche; ?>">
                                        <?php echo e($nombresRegimen[$fila->regimen] ?? $fila->regimen); ?>
                                        <?php if ((float) $fila->suplemento_noche > 0): ?>
                                            (+<?php echo number_format((float) $fila->suplemento_noche, 2, ',', '.'); ?> €/noche)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span data-total><?php echo number_format((float) $hotel->precio_noche, 2, ',', '.'); ?> €</span>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cart-fill"></i> Reservar
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($esAdmin): ?>
                    <hr>
                    <div class="d-grid gap-2">
                        <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/hoteles/editar/<?php echo $idHotel; ?>">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a class="btn btn-outline-primary" href="<?php echo RUTA_URL; ?>/hoteles/regimenes/<?php echo $idHotel; ?>">
                            <i class="bi bi-cup-hot"></i> Comidas
                        </a>
                        <form method="post" class="d-grid" data-hotel-delete="<?php echo $idHotel; ?>"
                              action="<?php echo RUTA_URL; ?>/hoteles/borrar/<?php echo $idHotel; ?>"
                              data-confirmar="¿Seguro que quieres borrar el hotel «<?php echo e($hotel->nombre); ?>»?"
                              data-confirmar-boton="Borrar">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> Borrar
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</main>

<?php
$scripts = ['hoteles/ui/hotelPage.js', 'hoteles/ui/reservaHotel.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
