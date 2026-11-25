<?php

class LoginModelo
{
    private $db;

    public function __construct()
    {
        $this->db = new Base;
    }

    // Buscar un usuario por su email
    public function obtenerPorEmail($email)
    {
        $this->db->query(
            "SELECT * FROM Usuario WHERE email = :email LIMIT 1"
        );

        $this->db->bind(':email', $email);

        return $this->db->registro();
    }

    // Guardar el token del enlace y el código del correo de un usuario (los dos ya cifrados)
    // Antes se borran los que tuviera, así solo vale el último correo enviado
    public function guardarToken($id_usuario, $token, $codigo)
    {
        $this->db->query("DELETE FROM token_recuperacion WHERE id_usuario = :id_usuario");
        $this->db->bind(':id_usuario', (int) $id_usuario);
        $this->db->execute();

        $this->db->query(
            "INSERT INTO token_recuperacion (id_usuario, token, codigo, fecha_expiracion)
             VALUES (:id_usuario, :token, :codigo, DATE_ADD(NOW(), INTERVAL :horas HOUR))"
        );

        $this->db->bind(':id_usuario', (int) $id_usuario);
        $this->db->bind(':token', $token);
        $this->db->bind(':codigo', $codigo);
        $this->db->bind(':horas', (int) HORAS_TOKEN);

        return $this->db->execute();
    }

    // Token sin caducar del usuario con ese email (para comprobar el código), o false si no hay
    public function obtenerTokenPorEmail($email)
    {
        $this->db->query(
            "SELECT t.id_token, t.codigo, t.intentos, u.id_usuario, u.email
             FROM token_recuperacion t
             JOIN usuario u ON u.id_usuario = t.id_usuario
             WHERE u.email = :email AND t.fecha_expiracion > NOW()"
        );

        $this->db->bind(':email', $email);

        return $this->db->registro();
    }

    // Sumar un intento fallido al escribir el código
    public function sumarIntento($id_token)
    {
        $this->db->query("UPDATE token_recuperacion SET intentos = intentos + 1 WHERE id_token = :id_token");
        $this->db->bind(':id_token', (int) $id_token);

        return $this->db->execute();
    }

    // Usuario dueño de un token que no ha caducado, o false si no hay ninguno
    public function obtenerUsuarioPorToken($token)
    {
        $this->db->query(
            "SELECT u.id_usuario, u.email, u.nombre
             FROM token_recuperacion t
             JOIN usuario u ON u.id_usuario = t.id_usuario
             WHERE t.token = :token AND t.fecha_expiracion > NOW()"
        );

        $this->db->bind(':token', $token);

        return $this->db->registro();
    }

    // Cambiar la contraseña (ya cifrada) y borrar los tokens del usuario para que el enlace no se pueda volver a usar
    // Va en una transacción: o se hacen las dos cosas o ninguna
    public function cambiarContrasena($id_usuario, $contraseña)
    {
        $this->db->iniciarTransaccion();

        try {
            $this->db->query("UPDATE usuario SET contraseña = :contrasena WHERE id_usuario = :id_usuario");
            $this->db->bind(':contrasena', $contraseña);
            $this->db->bind(':id_usuario', (int) $id_usuario);
            $this->db->execute();

            $this->db->query("DELETE FROM token_recuperacion WHERE id_usuario = :id_usuario");
            $this->db->bind(':id_usuario', (int) $id_usuario);
            $this->db->execute();

            $this->db->confirmarTransaccion();

            return true;
        } catch (PDOException $e) {
            $this->db->deshacerTransaccion();
            return false;
        }
    }

    // Obtener el rol de un usuario
    public function getRolUsuario($email)
    {
        $this->db->query(
            "SELECT rol FROM Usuario WHERE email = :email LIMIT 1"
        );

        $this->db->bind(':email', $email);

        $usuario = $this->db->registro();

        return $usuario ? $usuario->rol : null;
    }

    // Obtener usuarios
    public function obtenerUsuarios($pagina = 0, $tam_pagina = 0)
    {
        if ($tam_pagina <= 0) {
            $this->db->query("SELECT * FROM Usuario");
            return $this->db->registros();
        }

        $registroInicial = $pagina * $tam_pagina;

        $this->db->query(
            "SELECT * FROM Usuario
             LIMIT :inicio, :cantidad"
        );

        $this->db->bind(':inicio', (int) $registroInicial);
        $this->db->bind(':cantidad', (int) $tam_pagina);

        return (object) [
            'datos' => $this->db->registros(),
            'numPaginas' => ceil(
                $this->obtenerNumUsuarios() / $tam_pagina
            ),
            'paginaActual' => $pagina
        ];
    }

    // Contar usuarios
    public function obtenerNumUsuarios()
    {
        $this->db->query("SELECT COUNT(*) AS total FROM Usuario");

        $resultado = $this->db->registro();

        return (int) $resultado->total;
    }
}
