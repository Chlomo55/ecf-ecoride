<?php
require_once 'mongo_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Conversion en ObjectId si nécessaire
    if (preg_match('/^[a-f\d]{24}$/i', $id)) {
        $mongoId = new MongoDB\BSON\ObjectId($id);
    } else {
        $mongoId = $id;
    }
    try {
        $collection = $db->employees;
        $result = $collection->updateOne(
            ['_id' => $mongoId],
            ['$unset' => ['suspended' => ""]]
        );
    } catch (Exception $e) {
        die("Erreur lors de la réactivation : " . $e->getMessage());
    }
    if ($result->getModifiedCount() === 0) {
        die("Aucune modification effectuée. L'ID est-il correct ?");
    }
}
header('Location: employees.php');
exit;