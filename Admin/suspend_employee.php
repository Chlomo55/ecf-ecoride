<?php
require_once 'mongo_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $collection = $db->employees;
        $result = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => ['suspended' => true]]
        );
    } catch (Exception $e) {
        die("Erreur lors de la suspension : " . $e->getMessage());
    }
}
header('Location: employees.php');
exit;