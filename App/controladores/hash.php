
<?php

class Hash extends Controlador
{
    public function index()
    {
        echo '<h2>Generador de hash</h2>';
        echo '<pre>' . htmlspecialchars(
            password_hash('Admin123!', PASSWORD_DEFAULT)
        ) . '</pre>';
    }
}
