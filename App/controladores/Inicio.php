<?php

require_once __DIR__ . '/../librerias/Sesion.php';

class Inicio extends Controlador
{
    private $viajesModelo;
    private $usuario = null;
    

    public function __construct()
    {
        $this->viajesModelo = $this->modelo('ViajesModelo');
    }

    public function index()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }
        
        // Se pintan todas las tarjetas; con ?q= las que no coinciden salen ocultas
        // y el buscador (js/viajes/ui/searchBar.js) las filtra mientras se escribe
        $busqueda = trim($_GET['q'] ?? '');
        $datos['menuActivo'] = "Inicio";
        $datos['viajes'] = $this->viajesModelo->obtenerViajes();
        $datos['busqueda'] = $busqueda;
        $datos['idsEncontrados'] = $busqueda === ''
            ? null
            : array_column($this->viajesModelo->obtenerViajes(0, 0, $busqueda), 'id_viaje');
        
        
      
        $this->vista('index', $datos);
    }

     

    public function login()
    {
        $this->vista('login');
    }

    public function loadViajes()
    {
        $datos = [];

        if (!Sesion::sesionCreada($datos)) {
            redireccionar('/login');
            return;
        }

        $viajes = $this->viajesModelo->obtenerViajes();

        return $viajes;
    }
}