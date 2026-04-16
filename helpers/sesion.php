<?php

session_start();

//VALIDAMOS SI HAY UNA SECCION ACTIVA, se crea solo cuando hay una sesion activa
if (!isset($_SESSION['user'])) {
    header('Location: /colegio/');
    exit();
}

