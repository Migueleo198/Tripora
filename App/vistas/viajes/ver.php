<?php
require RUTA_APP . '/vistas/inc/header.php';

$viaje = $datos['viaje'];
$imagenes = imagenesViaje($viaje->id_viaje);
$dias = (int) $viaje->dias;

// Actividades del viaje agrupadas por día para el itinerario
$actividadesPorDia = [];

foreach ($datos['actividades'] ?? [] as $actividad) {
    $actividadesPorDia[(int) $actividad->dia][] = $actividad;
}

$numActividades = count($datos['actividades'] ?? []);

// Actividades opcionales: el cliente las elige al reservar y se suman al precio
$opcionales = array_filter($datos['actividades'] ?? [], fn($actividad) => $actividad->opcional);

// Duración HH:MM:SS de la base de datos a texto: "2 h 30 min"
$textoDuracion = function ($duracion) {
    [$horas, $minutos] = array_map('intval', explode(':', $duracion));
    $partes = [];

    if ($horas > 0) {
        $partes[] = $horas . ' h';
    }

    if ($minutos > 0) {
        $partes[] = $minutos . ' min';
    }

    return implode(' ', $partes);
};

// El carrusel usa la proporción que más se repite entre las imágenes del viaje
// y las que tienen otra forma se ajustan a ella (si hay empate, gana la primera)
$proporcion = '3 / 2';
$votos = [];

foreach ($imagenes as $imagen) {
    $tamano = @getimagesize(dirImagenesViaje($viaje->id_viaje) . '/' . $imagen['archivo']);

    if (!$tamano || $tamano[0] <= 0 || $tamano[1] <= 0) {
        continue;
    }

    // Se agrupan por proporción redondeada para que 1200x800 y 1500x1000 cuenten igual
    $clave = (string) round($tamano[0] / $tamano[1], 2);

    if (!isset($votos[$clave])) {
        $votos[$clave] = ['total' => 0, 'proporcion' => $tamano[0] . ' / ' . $tamano[1]];
    }

    $votos[$clave]['total']++;
}

foreach ($votos as $voto) {
    if (!isset($masRepetida) || $voto['total'] > $masRepetida['total']) {
        $masRepetida = $voto;
    }
}

if (isset($masRepetida)) {
    $proporcion = $masRepetida['proporcion'];
}
?>

<style>
    /* Imagen completa a su proporción real: sin recortes ni franjas.
       Se limita el alto para que las fotos verticales no ocupen toda la pantalla */
    .viaje-imagen {
        display: block;
        max-width: 100%;
        max-height: 570px;
        width: auto;
        height: auto;
        margin: 0 auto;
    }

    /* Carrusel con la proporción más repetida (--proporcion): todas las imágenes
       se ajustan a ese marco. Alto máximo 570px */
    #carruselViaje {
        aspect-ratio: var(--proporcion);
        width: 100%;
        max-width: calc(570px * (var(--proporcion)));
        border-radius: 1rem;
        overflow: hidden;
    }

    #carruselViaje .carousel-inner,
    #carruselViaje .carousel-item {
        height: 100%;
    }

    #carruselViaje .carousel-item img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Flechas con fondo para que se vean sobre cualquier imagen */
    #carruselViaje .carousel-control-prev-icon,
    #carruselViaje .carousel-control-next-icon {
        width: 2.75rem;
        height: 2.75rem;
        background-size: 50%;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
    }

    /* Hueco cuando el viaje no tiene imágenes */
    .viaje-sin-imagen {
        aspect-ratio: 3 / 2;
        border-radius: 1rem;
        background: linear-gradient(135deg, #304fa5, #8065f5);
    }

    /* Tarjeta de resumen: se queda fija al bajar en pantallas grandes */
    .viaje-resumen {
        border-radius: 1rem;
    }

    @media (min-width: 992px) {
        .viaje-resumen {
            position: sticky;
            top: 1.5rem;
        }
    }

    .viaje-precio {
        font-size: 2.25rem;
        font-weight: 750;
        letter-spacing: -1px;
        color: #304fa5;
    }

    .viaje-dato {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .viaje-dato i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        background: rgba(128, 101, 245, 0.12);
        color: #6a50e0;
    }

    /* Itinerario: línea de tiempo dentro de cada día */
    #itinerario .accordion-button:not(.collapsed) {
        background: rgba(128, 101, 245, 0.1);
        color: #304fa5;
        box-shadow: none;
    }

    .itinerario-lista {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .itinerario-lista li {
        position: relative;
        padding: 0 0 1.25rem 1.75rem;
        border-left: 2px solid #dcd5ff;
        margin-left: 0.5rem;
    }

    .itinerario-lista li:last-child {
        padding-bottom: 0;
        border-left-color: transparent;
    }

    .itinerario-lista li::before {
        content: "";
        position: absolute;
        left: -7px;
        top: 0.3rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #8065f5;
        box-shadow: 0 0 0 3px #fff;
    }
</style>

<main class="container py-4 py-lg-5">
    <a href="<?php echo RUTA_URL; ?>/viajes" class="btn btn-link text-decoration-none px-0 mb-3">
        <i class="bi bi-arrow-left"></i> Volver a viajes
    </a>

    <div class="alert alert-danger d-none" data-trip-error></div>

    <div class="row g-4 g-lg-5">
        <!-- Columna izquierda: imágenes, descripción e itinerario -->
        <div class="col-lg-8">
            <?php if (count($imagenes) === 1): ?>
                <img src="<?php echo e($imagenes[0]['url']); ?>" class="viaje-imagen rounded-4 shadow-sm" alt="<?php echo e($viaje->nombre); ?>">
            <?php elseif ($imagenes): ?>
                <?php // Pasa solo cada 3 s y no se detiene al poner el ratón encima ?>
                <div id="carruselViaje" class="carousel slide mx-auto shadow-sm" style="--proporcion: <?php echo e($proporcion); ?>;"
                     data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false">
                    <div class="carousel-indicators">
                        <?php foreach ($imagenes as $i => $imagen): ?>
                            <button type="button" data-bs-target="#carruselViaje" data-bs-slide-to="<?php echo $i; ?>"
                                    class="<?php echo $i === 0 ? 'active' : ''; ?>"
                                    <?php echo $i === 0 ? 'aria-current="true"' : ''; ?>
                                    aria-label="Imagen <?php echo $i + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="carousel-inner">
                        <?php foreach ($imagenes as $i => $imagen): ?>
                            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo e($imagen['url']); ?>"
                                     alt="<?php echo e($viaje->nombre); ?> (<?php echo $i + 1; ?>)">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#carruselViaje" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carruselViaje" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            <?php else: ?>
                <div class="viaje-sin-imagen d-flex align-items-center justify-content-center text-white shadow-sm">
                    <i class="bi bi-image fs-1 opacity-75"></i>
                </div>
            <?php endif; ?>

            <?php if (!empty($viaje->descripcion)): ?>
                <section class="mt-5">
                    <h2 class="h4 mb-3">Sobre el viaje</h2>
                    <p class="text-secondary lh-lg mb-0"><?php echo nl2br(e($viaje->descripcion)); ?></p>
                </section>
            <?php endif; ?>

            <?php if (!empty($datos['hoteles'])): ?>
                <section class="mt-5">
                    <h2 class="h4 mb-3">Alojamiento</h2>

                    <div class="d-grid gap-3">
                        <?php foreach ($datos['hoteles'] as $hotel): ?>
                            <a class="card border-0 shadow-sm text-reset text-decoration-none"
                               href="<?php echo RUTA_URL; ?>/hoteles/ver/<?php echo (int) $hotel->id_hotel; ?>">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="viaje-dato">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">
                                            <?php echo e($hotel->nombre); ?>
                                            <?php if ($hotel->estrellas !== null): ?>
                                                <span class="text-warning small ms-1"><?php echo str_repeat('★', (int) $hotel->estrellas); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-secondary small">
                                            <i class="bi bi-geo-alt"></i> <?php echo e($hotel->direccion); ?>, <?php echo e($hotel->ciudad); ?>
                                        </div>
                                        <div class="small mt-1">
                                            <i class="bi bi-cup-hot"></i> Comidas: <?php echo e($datos['regimenes_tipo'][$hotel->regimen] ?? $hotel->regimen); ?>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill text-bg-light">
                                        <?php echo (int) $hotel->noches; ?> <?php echo (int) $hotel->noches === 1 ? 'noche' : 'noches'; ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="mt-5">
                <h2 class="h4 mb-3">Itinerario</h2>

                <div class="accordion shadow-sm" id="itinerario">
                    <?php for ($dia = 1; $dia <= $dias; $dia++): ?>
                        <?php $actividadesDia = $actividadesPorDia[$dia] ?? []; ?>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button <?php echo $dia === 1 ? '' : 'collapsed'; ?>" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#dia<?php echo $dia; ?>"
                                        aria-expanded="<?php echo $dia === 1 ? 'true' : 'false'; ?>" aria-controls="dia<?php echo $dia; ?>">
                                    <span class="fw-semibold me-2">Día <?php echo $dia; ?></span>
                                    <span class="text-secondary small">
                                        <?php if ($actividadesDia): ?>
                                            <?php echo count($actividadesDia); ?> <?php echo count($actividadesDia) === 1 ? 'actividad' : 'actividades'; ?>
                                        <?php else: ?>
                                            Día libre
                                        <?php endif; ?>
                                    </span>
                                </button>
                            </h3>

                            <div id="dia<?php echo $dia; ?>" class="accordion-collapse collapse <?php echo $dia === 1 ? 'show' : ''; ?>"
                                 data-bs-parent="#itinerario">
                                <div class="accordion-body">
                                    <?php if ($actividadesDia): ?>
                                        <ul class="itinerario-lista">
                                            <?php foreach ($actividadesDia as $actividad): ?>
                                                <li>
                                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                                        <div>
                                                            <div class="fw-semibold">
                                                                <?php if ($actividad->hora !== null): ?>
                                                                    <span class="text-primary me-1"><?php echo e(substr($actividad->hora, 0, 5)); ?></span>
                                                                <?php endif; ?>
                                                                <?php echo e($actividad->nombre); ?>
                                                            </div>
                                                            <?php if (!empty($actividad->descripcion)): ?>
                                                                <div class="text-secondary small"><?php echo e($actividad->descripcion); ?></div>
                                                            <?php endif; ?>
                                                            <div class="text-secondary small mt-1">
                                                                <i class="bi bi-clock"></i> <?php echo e($textoDuracion($actividad->duracion)); ?>
                                                            </div>
                                                        </div>

                                                        <div class="text-end">
                                                            <?php if ($actividad->opcional): ?>
                                                                <span class="badge rounded-pill text-bg-warning">Opcional</span>
                                                                <div class="small fw-semibold mt-1">+<?php echo number_format((float) $actividad->precio, 2, ',', '.'); ?> €</div>
                                                            <?php else: ?>
                                                                <span class="badge rounded-pill text-bg-success">Incluida</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-secondary mb-0"><i class="bi bi-sun"></i> Día libre para explorar por tu cuenta.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </section>
        </div>

        <!-- Columna derecha: resumen del viaje y acciones (en móvil va la primera) -->
        <div class="col-lg-4 order-first order-lg-last">
            <aside class="viaje-resumen card border-0 shadow-sm p-4">
                <span class="badge rounded-pill text-bg-light align-self-start mb-2">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <?php echo e($viaje->ubicacion); ?>
                </span>

                <h1 class="h2 fw-bold mb-3"><?php echo e($viaje->nombre); ?></h1>

                <div class="text-secondary small">Precio por persona</div>
                <div class="viaje-precio mb-4"><?php echo number_format((float) $viaje->precio, 2, ',', '.'); ?> €</div>

                <div class="d-grid gap-3 mb-4">
                    <div class="viaje-dato">
                        <i class="bi bi-calendar3"></i>
                        <span><?php echo $dias; ?> <?php echo $dias === 1 ? 'día' : 'días'; ?></span>
                    </div>
                    <div class="viaje-dato">
                        <i class="bi bi-compass"></i>
                        <span><?php echo $numActividades; ?> <?php echo $numActividades === 1 ? 'actividad' : 'actividades'; ?></span>
                    </div>
                </div>

                <hr>
                <?php // data-precio y data-nombre los usa reservaActividades.js para recalcular el total ?>
                <form method="post" class="d-grid gap-3"
                      action="<?php echo RUTA_URL; ?>/reservas/contratar/<?php echo (int) $viaje->id_viaje; ?>"
                      data-reserva-viaje data-precio="<?php echo (float) $viaje->precio; ?>" data-nombre="<?php echo e($viaje->nombre); ?>"
                      data-confirmar="¿Quieres contratar «<?php echo e($viaje->nombre); ?>» por <?php echo number_format((float) $viaje->precio, 2, ',', '.'); ?> €?"
                      data-confirmar-boton="Contratar">

                    <?php if ($opcionales): ?>
                        <div>
                            <div class="fw-semibold mb-2">Actividades opcionales</div>

                            <?php foreach ($opcionales as $actividad): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="actividades[]"
                                           id="actividad<?php echo (int) $actividad->id_actividad; ?>"
                                           value="<?php echo (int) $actividad->id_actividad; ?>"
                                           data-precio="<?php echo (float) $actividad->precio; ?>">
                                    <label class="form-check-label d-flex justify-content-between gap-2" for="actividad<?php echo (int) $actividad->id_actividad; ?>">
                                        <span><?php echo e($actividad->nombre); ?> <span class="text-secondary small">(día <?php echo (int) $actividad->dia; ?>)</span></span>
                                        <span class="text-nowrap">+<?php echo number_format((float) $actividad->precio, 2, ',', '.'); ?> €</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span data-total><?php echo number_format((float) $viaje->precio, 2, ',', '.'); ?> €</span>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cart-fill"></i> Reservar
                    </button>
                </form>

                <?php if ($esAdmin): ?>
                    <hr>
                    <div class="d-grid gap-2">
                        <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/viajes/editar/<?php echo (int) $viaje->id_viaje; ?>">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a class="btn btn-outline-primary" href="<?php echo RUTA_URL; ?>/viajes/subirImagen?id_viaje=<?php echo (int) $viaje->id_viaje; ?>">
                            <i class="bi bi-images"></i> Imágenes
                        </a>
                        <form method="post" class="d-grid" data-trip-delete="<?php echo (int) $viaje->id_viaje; ?>"
                              action="<?php echo RUTA_URL; ?>/viajes/borrar/<?php echo (int) $viaje->id_viaje; ?>"
                              data-confirmar="¿Seguro que quieres borrar el viaje «<?php echo e($viaje->nombre); ?>»?"
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
$scripts = ['viajes/ui/tripPage.js', 'viajes/ui/reservaActividades.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
