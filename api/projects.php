<?php
/**
 * ==============================================================================
 * API DE PROYECTOS - PORTAFOLIO INTERACTIVO (PHP 8+)
 * ==============================================================================
 * Este endpoint provee la lista de proyectos en formato JSON.
 * Permite:
 *  - Filtrar proyectos por categoría (ej. ?category=creative, ?category=fullstack)
 *  - Filtrar solo proyectos destacados (ej. ?featured=1)
 *  - Obtener un proyecto específico por ID (ej. ?id=synthwave-84)
 * ==============================================================================
 */

// Cabeceras para respuesta JSON y habilitación de CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

// Manejo de petición pre-flight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ruta al archivo de base de datos JSON centralizado
$jsonPath = __DIR__ . '/../assets/data/projects.json';

// Verificación de existencia del archivo
if (!file_exists($jsonPath)) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "El catálogo de proyectos no fue encontrado en el servidor."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lectura y decodificación de datos JSON
$jsonData = file_get_contents($jsonPath);
$projects = json_decode($jsonData, true);

if (!is_array($projects)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al decodificar la información de los proyectos."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Captura de parámetros GET opcionales para filtrado
$categoriaDeseada = isset($_GET['category']) ? trim($_GET['category']) : null;
$soloDestacados   = isset($_GET['featured']) && $_GET['featured'] === '1';
$idBuscado        = isset($_GET['id']) ? trim($_GET['id']) : null;

// Si se solicita un proyecto en particular por su ID único
if ($idBuscado !== null) {
    $encontrado = null;
    foreach ($projects as $item) {
        if ($item['id'] === $idBuscado) {
            $encontrado = $item;
            break;
        }
    }

    if ($encontrado) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => $encontrado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Proyecto con ID '{$idBuscado}' no encontrado."
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Filtrado de la lista según los parámetros suministrados
$resultado = array_filter($projects, function ($proyecto) use ($categoriaDeseada, $soloDestacados) {
    // Si se pidió filtro de categoría y no coincide, se descarta
    if ($categoriaDeseada && strtolower($proyecto['category']) !== strtolower($categoriaDeseada)) {
        return false;
    }
    // Si se pidieron solo destacados y este no lo es, se descarta
    if ($soloDestacados && empty($proyecto['featured'])) {
        return false;
    }
    return true;
});

// Reindexamos el arreglo para asegurar un array JSON continuo en la salida
$resultado = array_values($resultado);

// Retornamos la respuesta final en JSON
http_response_code(200);
echo json_encode([
    "status" => "success",
    "total"  => count($resultado),
    "data"   => $resultado
], JSON_UNESCAPED_UNICODE);
