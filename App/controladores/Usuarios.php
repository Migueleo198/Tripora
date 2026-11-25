<?php

require_once __DIR__ . '/../librerias/Sesion.php';

class Usuarios extends Controlador
{
    private $usuariosModelo;
    private $usuario = null;
    
    public function __construct(){
        $this->usuariosModelo = $this->modelo("Usuario"); 
    }

     // Comprobar que existe una sesión
    private function comprobarSesion()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Debes iniciar sesión.'], 401);
            }

            redireccionar('/login');
            return false;
        }

        $this->usuario = $datos['usuarioSesion'];

        return $this->usuario;
    }

    // Comprobar que el usuario es administrador
    private function comprobarAdmin()
    {
        $usuario = $this->comprobarSesion();

        if ($usuario === false) {
            return false;
        }

        if (($usuario['rol'] ?? '') !== 'ADMIN') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No tienes permisos.'], 403);
            }

            redireccionar('/inicio');
            return false;
        }

        return true;
    }

    // Recoger los datos del formulario de usuario
    private function datosFormulario()
    {
        return [
            'DNI' => strtoupper(trim($_POST['DNI'] ?? '')),
            'email' => trim($_POST['email'] ?? ''),
            'rol' => $_POST['rol'] ?? '',
            'contraseña' => $_POST['contrasena'] ?? '',
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'fecha_nac' => $_POST['fecha_nac'] ?? ''
        ];
    }

     // Cargar la vista añadiendo el usuario de la sesión para el header
    private function vistaUsuarios($vista, $datos = [])
    {
        $datos['usuarioSesion'] = $this->usuario;
        $datos['menuActivo'] = 'Usuarios';
        $this->vista($vista, $datos);
    }

    // Formulario y actualización: /usuarios/editar/1
    public function editar($id_usuario = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $usuarioActual = $this->usuariosModelo->obtenerUsuario($id_usuario);

        if (!$usuarioActual) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El usuario no existe.'], 404);
            }

            redireccionar('/usuarios');
            return;
        }

        // La contraseña cifrada nunca se manda a la vista
        unset($usuarioActual->contraseña);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $this->datosFormulario();

            // Datos para volver a pintar el formulario si hay error (con id y sin contraseña)
            $usuarioVista = (object) array_merge(
                (array) $usuarioActual,
                array_diff_key($usuario, ['contraseña' => true])
            );

            // validarUsuario() está en helpers/funciones.php (también la usa el registro)
            $error = validarUsuario($usuario, false);

            if ($error === null && $this->usuariosModelo->existeUsuario($usuario['email'], $usuario['DNI'], $id_usuario)) {
                $error = 'Ya existe otro usuario con ese email o DNI.';
            }

            if ($error !== null) {
                if (esPeticionApi()) {
                    $this->vistaApi(['ok' => false, 'error' => $error], 422);
                }

                $this->vistaUsuarios('Usuarios/editarUsuario', [
                    'usuario' => $usuarioVista,
                    'error' => $error
                ]);
                return;
            }

            if ($this->usuariosModelo->editarUsuario($id_usuario, $usuario)) {
                if (esPeticionApi()) {
                    $this->vistaApi([
                        'ok' => true,
                        'id_usuario' => (int) $id_usuario,
                        'redirect' => RUTA_URL . '/usuarios'
                    ]);
                }

                redireccionar('/usuarios');
                return;
            }

            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No se pudo actualizar el usuario.'], 500);
            }

            $this->vistaUsuarios('Usuarios/editarUsuario', [
                'usuario' => $usuarioVista,
                'error' => 'No se pudo actualizar el usuario.'
            ]);
            return;
        }

        $this->vistaUsuarios('Usuarios/editarUsuario', [
            'usuario' => $usuarioActual
        ]);
    }

    // Formulario y creación: /usuarios/crear
    public function crear()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $this->datosFormulario();

            // La contraseña no se devuelve a la vista si hay error
            $usuarioVista = (object) array_diff_key($usuario, ['contraseña' => true]);

            $error = validarUsuario($usuario);

            if ($error === null && $this->usuariosModelo->existeUsuario($usuario['email'], $usuario['DNI'])) {
                $error = 'Ya existe un usuario con ese email o DNI.';
            }

            if ($error !== null) {
                if (esPeticionApi()) {
                    $this->vistaApi(['ok' => false, 'error' => $error], 422);
                }

                $this->vistaUsuarios('Usuarios/crearUsuario', [
                    'usuario' => $usuarioVista,
                    'error' => $error
                ]);
                return;
            }

            $id_usuario = $this->usuariosModelo->crearUsuario($usuario);

            if ($id_usuario) {
                if (esPeticionApi()) {
                    $this->vistaApi([
                        'ok' => true,
                        'id_usuario' => $id_usuario,
                        'redirect' => RUTA_URL . '/usuarios'
                    ]);
                }

                redireccionar('/usuarios');
                return;
            }

            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No se pudo crear el usuario.'], 500);
            }

            $this->vistaUsuarios('Usuarios/crearUsuario', [
                'usuario' => $usuarioVista,
                'error' => 'No se pudo crear el usuario.'
            ]);
            return;
        }

        $this->vistaUsuarios('Usuarios/crearUsuario');
    }

    // Ficha de un usuario: /usuarios/ver/1
    public function ver($id_usuario = null)
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        // Un cliente solo puede ver su propia ficha, el administrador cualquiera
        $esAdmin = ($this->usuario['rol'] ?? '') === 'ADMIN';
        $esPropio = (int) $id_usuario === (int) ($this->usuario['id_usuario'] ?? 0);

        if (!$esAdmin && !$esPropio) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No tienes permisos.'], 403);
            }

            redireccionar('/inicio');
            return;
        }

        $usuario = $this->usuariosModelo->verUsuario($id_usuario);

        if (!$usuario) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El usuario no existe.'], 404);
            }

            redireccionar('/usuarios');
            return;
        }

        // La contraseña cifrada nunca se manda a la vista
        unset($usuario->contraseña);

        $this->vistaUsuarios('Usuarios/ver', [
            'usuario' => $usuario
        ]);
    }

    // Eliminar: /usuarios/borrar/1
    public function borrar($id_usuario = null)
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'Método no permitido.'], 405);
            }

            redireccionar('/usuarios');
            return;
        }

        // No se permite borrar el usuario con el que se ha iniciado sesión
        if ((int) $id_usuario === (int) ($this->usuario['id_usuario'] ?? 0)) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No puedes borrar tu propio usuario.'], 403);
            }

            redireccionar('/usuarios');
            return;
        }

        if (!$this->usuariosModelo->obtenerUsuario($id_usuario)) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'El usuario no existe.'], 404);
            }

            redireccionar('/usuarios');
            return;
        }

        if (!$this->usuariosModelo->borrarUsuario($id_usuario)) {
            if (esPeticionApi()) {
                $this->vistaApi(['ok' => false, 'error' => 'No se pudo borrar el usuario.'], 500);
            }

            redireccionar('/usuarios');
            return;
        }

        if (esPeticionApi()) {
            $this->vistaApi([
                'ok' => true,
                'id_usuario' => (int) $id_usuario,
                'redirect' => RUTA_URL . '/usuarios'
            ]);
        }

        redireccionar('/usuarios');
    }

    // Mi perfil: /usuarios/perfil
    // Cualquier usuario con sesión ve y cambia sus propios datos (el rol no se puede cambiar desde aquí)
    public function perfil()
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        $id_usuario = (int) $this->usuario['id_usuario'];
        $actual = $this->usuariosModelo->obtenerUsuario($id_usuario);

        if (!$actual) {
            redireccionar('/login/logout');
            return;
        }

        $error = null;
        $usuarioForm = $actual;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $this->datosFormulario();

            // El rol y la contraseña no se tocan en este formulario
            $usuario['rol'] = $actual->rol;
            $usuario['contraseña'] = '';

            $error = validarUsuario($usuario, false);

            if ($error === null && $this->usuariosModelo->existeUsuario($usuario['email'], $usuario['DNI'], $id_usuario)) {
                $error = 'Ya existe otro usuario con ese email o DNI.';
            }

            if ($error === null && $this->usuariosModelo->editarUsuario($id_usuario, $usuario)) {
                // Actualizar los datos guardados en la sesión (nombre del menú, email...)
                $_SESSION['usuarioSesion'] = array_merge($_SESSION['usuarioSesion'], array_diff_key($usuario, ['contraseña' => true]));

                redireccionar('/usuarios/perfil?ok=datos');
                return;
            }

            $error = $error ?? 'No se han podido guardar los datos.';

            // Volver a pintar el formulario con lo escrito
            $usuarioForm = (object) array_merge((array) $actual, array_diff_key($usuario, ['contraseña' => true]));
        }

        $this->vistaPerfil([
            'usuarioForm' => $usuarioForm,
            'error' => $error
        ]);
    }

    // Cambiar la contraseña desde el perfil: POST /usuarios/contrasena
    // Hay que escribir la contraseña actual, así nadie la cambia con la sesión abierta de otro
    public function contrasena()
    {
        if ($this->comprobarSesion() === false) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redireccionar('/usuarios/perfil');
            return;
        }

        $id_usuario = (int) $this->usuario['id_usuario'];
        $actual = $this->usuariosModelo->obtenerUsuario($id_usuario);

        $antigua = $_POST['actual'] ?? '';
        $nueva = $_POST['nueva'] ?? '';
        $repetir = $_POST['repetir'] ?? '';
        $error = null;

        if (!$actual || !password_verify($antigua, $actual->contraseña)) {
            $error = 'La contraseña actual no es correcta.';
        } elseif (strlen($nueva) < 6) {
            $error = 'La contraseña nueva debe tener al menos 6 caracteres.';
        } elseif ($nueva !== $repetir) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } elseif (!$this->usuariosModelo->cambiarContrasena($id_usuario, $nueva)) {
            $error = 'No se ha podido cambiar la contraseña.';
        }

        if ($error === null) {
            redireccionar('/usuarios/perfil?ok=contrasena');
            return;
        }

        $this->vistaPerfil([
            'usuarioForm' => $actual,
            'errorContrasena' => $error
        ]);
    }

    // Cargar la vista del perfil con el resumen de reservas del usuario
    private function vistaPerfil($datos)
    {
        $id_usuario = (int) $this->usuario['id_usuario'];

        // La contraseña cifrada nunca se manda a la vista
        unset($datos['usuarioForm']->contraseña);

        // Reservas que no están canceladas
        $activas = fn($reserva) => $reserva->estado !== 'CANCELADA';
        $datos['numViajes'] = count(array_filter($this->modelo('ReservasModelo')->obtenerReservasUsuario($id_usuario), $activas));
        $datos['numHoteles'] = count(array_filter($this->modelo('ReservasHotelesModelo')->obtenerReservasUsuario($id_usuario), $activas));

        $datos['ok'] = $_GET['ok'] ?? null;
        $datos['usuarioSesion'] = $this->usuario;
        $datos['menuActivo'] = 'Perfil';

        $this->vista('Usuarios/perfil', $datos);
    }

    // Listar usuarios: /usuarios
    public function index()
    {
        if (!$this->comprobarAdmin()) {
            return;
        }

        $pagina = max(0, (int) ($_GET['pagina'] ?? 0));

        $this->vistaUsuarios('Usuarios/index', [
            'usuarios' => $this->usuariosModelo->obtenerUsuarios($pagina, 10)
        ]);
    }
}