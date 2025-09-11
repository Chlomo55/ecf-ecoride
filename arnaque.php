<?php 


if ($_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit();
}


