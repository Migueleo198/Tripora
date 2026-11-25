<?php
require RUTA_APP . '/vistas/inc/header.php';

$usuarios = $datos['usuarios'];
?>

<main class="container py-5">
    

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-4">Usuarios</h1>
        <?php if ($esAdmin): ?>
            <a class="btn btn-primary" href="<?php echo RUTA_URL; ?>/usuarios/crear">Añadir Usuario</a>
        <?php endif; ?>
    </div>

    <?php $placeholderBusqueda = 'Buscar usuarios, DNI, email...'; require RUTA_APP . '/vistas/inc/buscador.php'; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios->datos as $usuarioFila): ?>
                    <?php $idUsuario = (int) $usuarioFila->id_usuario; ?>
                    <tr data-user-row data-search-item
                        data-search="<?php echo e($usuarioFila->nombre . ' ' . $usuarioFila->apellidos . ' ' . $usuarioFila->email . ' ' . $usuarioFila->telefono . ' ' . $usuarioFila->DNI . ' ' . $usuarioFila->rol); ?>">
                        <td><?php echo e($usuarioFila->nombre . ' ' . $usuarioFila->apellidos); ?></td>
                        <td><?php echo e($usuarioFila->email); ?></td>
                        <td><?php echo e($usuarioFila->telefono); ?></td>
                        <td><?php echo e($usuarioFila->rol); ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="<?php echo RUTA_URL; ?>/usuarios/ver/<?php echo $idUsuario; ?>"
                               title="Ver" aria-label="Ver usuario">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?php echo RUTA_URL; ?>/usuarios/editar/<?php echo $idUsuario; ?>"
                               title="Editar" aria-label="Editar usuario">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php // No se permite borrar el usuario con el que se ha iniciado sesión ?>
                            <?php if ($idUsuario !== (int) ($usuario['id_usuario'] ?? 0)): ?>
                                <form class="d-inline" method="post" data-user-delete="<?php echo $idUsuario; ?>"
                                      action="<?php echo RUTA_URL; ?>/usuarios/borrar/<?php echo $idUsuario; ?>"
                                      data-confirmar="¿Seguro que quieres borrar a <?php echo e($usuarioFila->nombre . ' ' . $usuarioFila->apellidos); ?>?"
                                      data-confirmar-boton="Borrar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Borrar" aria-label="Borrar usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="alert alert-info d-none" data-search-empty="No hay usuarios que coincidan con"></div>
</main>

<?php
$scripts = ['viajes/ui/searchBar.js'];
require RUTA_APP . '/vistas/inc/footer.php';
?>
