<?php
// Formulario compartido por crear y editar usuario
// Necesita: $usuarioForm (objeto o null), $accion (url del form), $textoBoton
// Se usa $usuarioForm y no $usuario porque el header ya usa $usuario para la sesión
$esEdicion = isset($usuarioForm->id_usuario);
?>
<div class="alert alert-danger <?php echo empty($datos['error']) ? 'd-none' : ''; ?>" data-user-error>
    <?php echo e($datos['error'] ?? ''); ?>
</div>

<form method="post" action="<?php echo $accion; ?>"
      data-user-form data-user-id="<?php echo $esEdicion ? (int) $usuarioForm->id_usuario : ''; ?>">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50" required
                   value="<?php echo e($usuarioForm->nombre ?? ''); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" class="form-control" id="apellidos" name="apellidos" maxlength="100" required
                   value="<?php echo e($usuarioForm->apellidos ?? ''); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="DNI" class="form-label">DNI</label>
            <input type="text" class="form-control" id="DNI" name="DNI" maxlength="9" required
                   placeholder="12345678Z"
                   value="<?php echo e($usuarioForm->DNI ?? ''); ?>">
            <div class="form-text">DNI (8 números y letra) o NIE (X/Y/Z, 7 números y letra).</div>
        </div>

        <div class="col-12 col-md-6">
            <label for="fecha_nac" class="form-label">Fecha de nacimiento</label>
            <input type="date" class="form-control" id="fecha_nac" name="fecha_nac" required
                   max="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo e($usuarioForm->fecha_nac ?? ''); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" maxlength="100" required
                   value="<?php echo e($usuarioForm->email ?? ''); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="20" required
                   value="<?php echo e($usuarioForm->telefono ?? ''); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
                <?php foreach (['CLIENTE' => 'Cliente', 'ADMIN' => 'Administrador'] as $valor => $texto): ?>
                    <option value="<?php echo $valor; ?>"
                        <?php echo ($usuarioForm->rol ?? 'CLIENTE') === $valor ? 'selected' : ''; ?>>
                        <?php echo $texto; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label for="contrasena" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="contrasena" name="contrasena"
                   minlength="6" autocomplete="new-password"
                   <?php echo $esEdicion ? '' : 'required'; ?>>
            <div class="form-text">
                <?php echo $esEdicion ? 'Déjala vacía para no cambiarla.' : 'Mínimo 6 caracteres.'; ?>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><?php echo e($textoBoton); ?></button>
        <a class="btn btn-outline-secondary" href="<?php echo RUTA_URL; ?>/usuarios">Cancelar</a>
    </div>
</form>
