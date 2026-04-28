<?php
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$dataFile = "data/animaux.json";
$uploadDir = "uploads/";

// créer fichiers si inexistants
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function getData($file) {
    return json_decode(file_get_contents($file), true);
}

function saveData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// ================= CREATE (POST) =================
if ($method === "POST") {

    $animaux = getData($dataFile);

    $animal = [
        "id" => uniqid(),
        "nom" => $_POST["nom"] ?? "",
        "espece" => $_POST["espece"] ?? "",
        "race" => $_POST["race"] ?? "",
        "age" => $_POST["age"] ?? "",
        "poids" => $_POST["poids"] ?? "",
        "besoins" => $_POST["besoins"] ?? "",
        "emoji" => $_POST["emoji"] ?? "🐶",
        "photo" => null
    ];

    // upload photo
    if (!empty($_FILES["photo"]["name"])) {
        $ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $fileName = uniqid() . "." . $ext;
        $target = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target)) {
            $animal["photo"] = $target;
        }
    }

    $animaux[] = $animal;
    saveData($dataFile, $animaux);

    echo json_encode(["status" => "success", "animal" => $animal]);
    exit;
}

// ================= UPDATE (PUT) =================
if ($method === "PUT") {

    parse_str(file_get_contents("php://input"), $_PUT);

    $animaux = getData($dataFile);

    foreach ($animaux as &$a) {
        if ($a["id"] == $_PUT["id"]) {

            $a["nom"] = $_PUT["nom"] ?? $a["nom"];
            $a["espece"] = $_PUT["espece"] ?? $a["espece"];
            $a["race"] = $_PUT["race"] ?? $a["race"];
            $a["age"] = $_PUT["age"] ?? $a["age"];
            $a["poids"] = $_PUT["poids"] ?? $a["poids"];
            $a["besoins"] = $_PUT["besoins"] ?? $a["besoins"];
            $a["emoji"] = $_PUT["emoji"] ?? $a["emoji"];
        }
    }

    saveData($dataFile, $animaux);

    echo json_encode(["status" => "updated"]);
    exit;
}

// ================= DELETE =================
if ($method === "DELETE") {

    parse_str(file_get_contents("php://input"), $_DELETE);

    $animaux = getData($dataFile);
    $new = [];

    foreach ($animaux as $a) {
        if ($a["id"] != $_DELETE["id"]) {
            $new[] = $a;
        } else {
            // supprimer photo si existe
            if (!empty($a["photo"]) && file_exists($a["photo"])) {
                unlink($a["photo"]);
            }
        }
    }

    saveData($dataFile, $new);

    echo json_encode(["status" => "deleted"]);
    exit;
}

// ================= READ (GET) =================
if ($method === "GET") {
    echo json_encode(getData($dataFile));
    exit;
}

echo json_encode(["error" => "Méthode non supportée"]);
?>