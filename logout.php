<?php
require "includes/sesion.php";
session_destroy();   // borra la sesión entera
header("Location: login.php");
exit;
