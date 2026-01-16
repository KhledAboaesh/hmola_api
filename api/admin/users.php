<?php
// backend/api/admin/users.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include '../../db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method == 'GET') {
        $query = "SELECT id, name, phone, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll();
        echo json_encode(["status" => "success", "data" => $users]);
        
    } elseif ($method == 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id']) && !empty($data['role'])) {
            $query = "UPDATE users SET role = :role WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":role", $data['role']);
            $stmt->bindParam(":id", $data['id']);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "User updated successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update user"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Incomplete data"]);
        }
    } elseif ($method == 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        if ($id) {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":id", $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "User deleted"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to delete"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "ID required"]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
