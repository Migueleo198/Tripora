<?php

    class Usuario {
        private $db;

        public function __construct(){
            $this->db = new Base;
        }


        public function obtenerUsuarios($pagina = 0, $tam_pagina = 0){
            $registro_inicial = $pagina * $tam_pagina;
            if ($tam_pagina == 0){
                $this->db->query("SELECT * FROM usuario");
                return $this->db->registros();
            } else {
                $this->db->query("SELECT * FROM usuario LIMIT $registro_inicial, $tam_pagina");
                return (object) [
                    'datos' => $this->db->registros(),
                    'numPaginas' => ceil($this->obtenerNumUsuarios()/$tam_pagina),
                    'paginaActual' => $pagina
                ];
            }
        }

        public function getRolUsuario(){
            $this->db->query("SELECT * FROM USUARIO WHERE USUARIO");
        }

        // Obtener un usuario por su identificador
        public function obtenerUsuario($id_usuario){
            $this->db->query("SELECT * FROM usuario WHERE id_usuario = :id_usuario LIMIT 1");

            $this->db->bind(":id_usuario",(int) $id_usuario);

            return $this->db->registro();
        }

        // Comprobar si ya hay otro usuario con ese email o DNI (son columnas UNIQUE)
        // $excluirId sirve al editar, para no chocar con el propio usuario
        public function existeUsuario($email, $dni, $excluirId = 0){
            $this->db->query("SELECT id_usuario FROM usuario
                WHERE (email = :email OR DNI = :dni) AND id_usuario <> :excluir
                LIMIT 1");

            $this->db->bind(":email",$email);
            $this->db->bind(":dni",$dni);
            $this->db->bind(":excluir",(int) $excluirId);

            return (bool) $this->db->registro();
        }

        // Crear un usuario, devuelve su identificador o false
        // id_usuario es AUTO_INCREMENT y fecha_registro la pone la base de datos
        public function crearUsuario($datos){
            $this->db->query("INSERT INTO usuario (DNI,email,fecha_registro,
            rol,contraseña,nombre,apellidos,telefono,fecha_nac) VALUES

            (:DNI,:email,NOW(),
            :rol,:contrasena,:nombre,:apellidos,:telefono,:fecha_nac)");

            $this->db->bind(":DNI",$datos['DNI']);
            $this->db->bind(":email",$datos['email']);
            $this->db->bind(":rol",$datos['rol']);
            // El login la comprueba con password_verify, así que se guarda cifrada
            $this->db->bind(":contrasena",password_hash($datos['contraseña'], PASSWORD_DEFAULT));
            $this->db->bind(":nombre",$datos['nombre']);
            $this->db->bind(":apellidos",$datos['apellidos']);
            $this->db->bind(":telefono",$datos['telefono']);
            $this->db->bind(":fecha_nac",$datos['fecha_nac']);
            
            if (!$this->db->execute()) {
                return false;
            }

            return (int) $this->db->ultimoIndice();
        }

        // Actualizar un usuario existente
        // fecha_registro no se toca y la contraseña solo cambia si se ha escrito una nueva
        public function editarUsuario($id_usuario, $datos){
            $cambiarContraseña = $datos['contraseña'] !== '';

            $this->db->query("UPDATE usuario
                SET DNI = :DNI,
                    email = :email,
                    rol = :rol,
                    nombre = :nombre,
                    apellidos = :apellidos,
                    telefono = :telefono,
                    fecha_nac = :fecha_nac"
                    . ($cambiarContraseña ? ", contraseña = :contrasena" : "") . "
                WHERE id_usuario = :id_usuario");

            $this->db->bind(":id_usuario",(int) $id_usuario);
            $this->db->bind(":DNI",$datos['DNI']);
            $this->db->bind(":email",$datos['email']);
            $this->db->bind(":rol",$datos['rol']);
            $this->db->bind(":nombre",$datos['nombre']);
            $this->db->bind(":apellidos",$datos['apellidos']);
            $this->db->bind(":telefono",$datos['telefono']);
            $this->db->bind(":fecha_nac",$datos['fecha_nac']);

            if ($cambiarContraseña) {
                // El login la comprueba con password_verify, así que se guarda cifrada
                $this->db->bind(":contrasena",password_hash($datos['contraseña'], PASSWORD_DEFAULT));
            }

            return $this->db->execute();
        }

        // Cambiar solo la contraseña de un usuario (desde su perfil)
        public function cambiarContrasena($id_usuario, $contraseña){
            $this->db->query("UPDATE usuario SET contraseña = :contrasena WHERE id_usuario = :id_usuario");

            // El login la comprueba con password_verify, así que se guarda cifrada
            $this->db->bind(":contrasena",password_hash($contraseña, PASSWORD_DEFAULT));
            $this->db->bind(":id_usuario",(int) $id_usuario);

            return $this->db->execute();
        }

        public function borrarUsuario($id_usuario){
            $this->db->query("DELETE FROM usuario WHERE id_usuario = :id_usuario");

            $this->db->bind(':id_usuario',(int) $id_usuario);

            return $this->db->execute();
        }

        public function verUsuario($idUsuario){
            if(!$idUsuario){
                return;
            }
            $this->db->query("SELECT * FROM usuario WHERE id_usuario = :id_usuario LIMIT 1");

            $this->db->bind(':id_usuario', (int) $idUsuario);

            return $this->db->registro();
        }


        public function obtenerNumUsuarios(){
            $this->db->query("SELECT * FROM usuario");

            return $this->db->rowCount();
        }


    }
