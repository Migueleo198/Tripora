<?php

require_once __DIR__ . '/../librerias/Sesion.php';

class Login extends Controlador
{
    private $loginModelo;
    private $usuariosModelo;

    public function __construct()
    {
        $this->loginModelo = $this->modelo('LoginModelo');
        $this->usuariosModelo = $this->modelo('Usuario');
    }

    // Mostrar el formulario
    public function index()
    {
        $datos = [];

        // Vuelve aquí después de cambiar la contraseña con el enlace del correo
        if (isset($_GET['cambiada'])) {
            $datos['mensaje'] = 'Contraseña cambiada. Ya puedes iniciar sesión.';
        }

        $this->vista('login', $datos);
    }

    // Iniciar sesión con email y contraseña
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redireccionar('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $contraseña = $_POST['contraseña'] ?? '';

        if ($email === '' || $contraseña === '') {
            $datos['error'] = 'Introduce el email y la contraseña.';
            $this->vista('login', $datos);
            return;
        }

        // Buscar el usuario mediante el modelo Login
        $usuario = $this->loginModelo->obtenerPorEmail($email);

        // registro() parece devolver un objeto
        if (
            !$usuario ||
            !password_verify($contraseña, $usuario->contraseña)
        ) {
            $datos['error'] = 'Email o contraseña incorrectos.';
            $this->vista('login', $datos);
            return;
        }

        // Convertir a array y eliminar la contraseña
        $usuario = (array) $usuario;
        unset($usuario['contraseña']);

        // Crear sesión
        Sesion::crearSesion($usuario);

        // Redirigir según el rol
        if ($usuario['rol'] === 'ADMIN') {
            redireccionar('/inicio');
        } else {
            redireccionar('/inicio');
        }
    }

    // Crear una cuenta nueva: /login/registro
    // Siempre se crea como CLIENTE y al terminar se inicia sesión directamente
    public function registro()
    {
        $datos = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = [
                'DNI' => strtoupper(trim($_POST['DNI'] ?? '')),
                'email' => trim($_POST['email'] ?? ''),
                'rol' => 'CLIENTE',
                'contraseña' => $_POST['contrasena'] ?? '',
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellidos' => trim($_POST['apellidos'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'fecha_nac' => $_POST['fecha_nac'] ?? ''
            ];

            // Misma validación que al crear un usuario desde administración (helpers/funciones.php)
            $error = validarUsuario($usuario);

            if ($error === null && $usuario['contraseña'] !== ($_POST['repetir'] ?? '')) {
                $error = 'Las contraseñas no coinciden.';
            }

            if ($error === null && $this->usuariosModelo->existeUsuario($usuario['email'], $usuario['DNI'])) {
                $error = 'Ya existe una cuenta con ese email o DNI.';
            }

            if ($error === null) {
                $id_usuario = $this->usuariosModelo->crearUsuario($usuario);

                if ($id_usuario) {
                    // Iniciar sesión con el usuario recién creado, igual que en login() (sin la contraseña)
                    $sesion = (array) $this->usuariosModelo->obtenerUsuario($id_usuario);
                    unset($sesion['contraseña']);

                    Sesion::crearSesion($sesion);
                    redireccionar('/inicio');
                    return;
                }

                $error = 'No se ha podido crear la cuenta. Inténtalo de nuevo.';
            }

            // Si hay error se vuelve a pintar el formulario con lo escrito (menos la contraseña)
            $datos['error'] = $error;
            $datos['usuario'] = (object) array_diff_key($usuario, ['contraseña' => true]);
        }

        $this->vista('registro', $datos);
    }

    // ¿Has olvidado tu contraseña?: /login/olvide
    // GET muestra el formulario; POST envía al correo un enlace con un token
    public function olvide()
    {
        $datos = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $usuario = $email === '' ? false : $this->loginModelo->obtenerPorEmail($email);

            if ($usuario) {
                // Token aleatorio de 64 caracteres para el enlace. En la BD se guarda su hash
                $token = bin2hex(random_bytes(32));

                // Código de 6 dígitos para escribirlo a mano (000000 a 999999). En la BD va cifrado con password_hash
                $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $this->loginModelo->guardarToken($usuario->id_usuario, hash('sha256', $token), password_hash($codigo, PASSWORD_DEFAULT));

                $enlace = URL_SITIO . RUTA_URL . '/login/restablecer/' . $token;

                $html = '<p>Hola ' . e($usuario->nombre) . ',</p>'
                      . '<p>Para cambiar tu contraseña de Tripora entra en este enlace:</p>'
                      . '<p><a href="' . e($enlace) . '">' . e($enlace) . '</a></p>'
                      . '<p>O escribe este código en la página de recuperación:</p>'
                      . '<p style="font-size: 28px; font-weight: bold; letter-spacing: 6px;">' . $codigo . '</p>'
                      . '<p>El enlace y el código valen ' . HORAS_TOKEN . ' horas y solo se pueden usar una vez.</p>'
                      . '<p>Si no lo has pedido tú, ignora este correo.</p>';

                Correo::enviar($usuario->email, 'Recuperar contraseña - Tripora', $html);
            }

            // Mismo mensaje exista o no el email, para no dar pistas de qué correos están registrados
            $datos['mensaje'] = 'Si el correo está registrado, te hemos enviado un enlace y un código para cambiar la contraseña.';
            $datos['email'] = $email;
        }

        $this->vista('recuperacion/olvide', $datos);
    }

    // Cambiar la contraseña con el código de 6 dígitos del correo: POST /login/codigo
    // Paso 1: email + código -> si es correcto se muestra el formulario de la contraseña nueva
    // Paso 2: el mismo formulario vuelve con email + código + contraseña nueva -> se cambia
    public function codigo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redireccionar('/login/olvide');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $token = $email === '' ? false : $this->loginModelo->obtenerTokenPorEmail($email);

        // Mismo mensaje si no hay código pendiente, si ha caducado, si se ha pasado de intentos o si está mal
        if (!$token || $token->intentos >= INTENTOS_CODIGO || !password_verify($codigo, $token->codigo)) {
            if ($token && $token->intentos < INTENTOS_CODIGO) {
                $this->loginModelo->sumarIntento($token->id_token);
            }

            $this->vista('recuperacion/olvide', [
                'email' => $email,
                'error' => 'El código no es correcto o ha caducado. Si lo has escrito mal ' . INTENTOS_CODIGO . ' veces, pide otro.'
            ]);
            return;
        }

        // Datos para el formulario de la contraseña nueva: envía otra vez el email y el código a este método
        $datos = [
            'email' => $token->email,
            'accion' => RUTA_URL . '/login/codigo',
            'codigo' => $codigo
        ];

        // Paso 2: ya viene la contraseña nueva
        if (isset($_POST['contrasena'])) {
            $error = $this->cambiarContrasenaFormulario($token->id_usuario);

            if ($error === null) {
                redireccionar('/login?cambiada=1');
                return;
            }

            $datos['error'] = $error;
        }

        $this->vista('recuperacion/restablecer', $datos);
    }

    // Validar la contraseña nueva del formulario y guardarla; devuelve el mensaje de error o null
    // La usan restablecer() (enlace) y codigo() (código de 6 dígitos)
    private function cambiarContrasenaFormulario($id_usuario)
    {
        $contraseña = $_POST['contrasena'] ?? '';
        $repetir = $_POST['repetir'] ?? '';

        if (strlen($contraseña) < 6) {
            return 'La contraseña debe tener al menos 6 caracteres.';
        }

        if ($contraseña !== $repetir) {
            return 'Las contraseñas no coinciden.';
        }

        if (!$this->loginModelo->cambiarContrasena($id_usuario, password_hash($contraseña, PASSWORD_DEFAULT))) {
            return 'No se ha podido cambiar la contraseña. Inténtalo de nuevo.';
        }

        return null;
    }

    // Cambiar la contraseña con el enlace del correo: /login/restablecer/{token}
    public function restablecer($token = '')
    {
        $datos = ['token' => $token];

        // El token son 64 caracteres hexadecimales; se busca por su hash y que no haya caducado
        $usuario = false;

        if (strlen($token) === 64 && ctype_xdigit($token)) {
            $usuario = $this->loginModelo->obtenerUsuarioPorToken(hash('sha256', $token));
        }

        if (!$usuario) {
            $datos['enlaceMalo'] = true;
            $this->vista('recuperacion/restablecer', $datos);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->cambiarContrasenaFormulario($usuario->id_usuario);

            if ($error === null) {
                redireccionar('/login?cambiada=1');
                return;
            }

            $datos['error'] = $error;
        }

        $datos['email'] = $usuario->email;
        $this->vista('recuperacion/restablecer', $datos);
    }

    // Cerrar sesión
    public function logout()
    {
        Sesion::cerrarSesion();
        redireccionar('/login');
    }
}
