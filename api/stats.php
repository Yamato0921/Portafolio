<?php
/**
 * Telemetría de archivos del portafolio para alojamientos PHP.
 */

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Solo se aceptan peticiones GET."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$baseDir = dirname(__DIR__);
$extensions = [
    "HTML5" => ["html", "htm"],
    "CSS3" => ["css"],
    "JavaScript" => ["js", "mjs"],
    "PHP" => ["php"],
    "Python" => ["py"],
    "JSON/Data" => ["json"]
];
$ignoredDirectories = [
    ".git", ".github", ".venv", "venv", "env", "node_modules",
    "__pycache__", "storage", "dist", "build"
];
$distribution = [];
foreach ($extensions as $technology => $_) {
    $distribution[$technology] = [
        "lines" => 0,
        "files" => 0,
        "bytes" => 0,
        "percentage" => 0
    ];
}

$scanDirectory = function (string $directory) use (&$scanDirectory, &$distribution, $extensions, $ignoredDirectories): void {
    $entries = scandir($directory);
    if ($entries === false) {
        throw new RuntimeException("No se pudo leer un directorio del proyecto.");
    }

    foreach ($entries as $entry) {
        if ($entry === "." || $entry === "..") {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $entry;
        if (is_link($path)) {
            continue;
        }
        if (is_dir($path)) {
            if (!in_array($entry, $ignoredDirectories, true)) {
                $scanDirectory($path);
            }
            continue;
        }
        if (!is_file($path)) {
            continue;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        foreach ($extensions as $technology => $validExtensions) {
            if (!in_array($extension, $validExtensions, true)) {
                continue;
            }

            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new RuntimeException("No se pudo leer un archivo del proyecto.");
            }

            $nonEmptyLines = preg_match_all('/^\s*\S.*$/m', $contents);
            if ($nonEmptyLines === false) {
                throw new RuntimeException("No se pudieron calcular las métricas del proyecto.");
            }

            $distribution[$technology]["files"]++;
            $distribution[$technology]["lines"] += $nonEmptyLines;
            $distribution[$technology]["bytes"] += strlen($contents);
            break;
        }
    }
};

try {
    $scanDirectory($baseDir);
} catch (RuntimeException $error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$totalLines = array_sum(array_column($distribution, "lines"));
foreach ($distribution as &$metrics) {
    $metrics["percentage"] = $totalLines > 0
        ? round(($metrics["lines"] / $totalLines) * 100, 1)
        : 0;
}
unset($metrics);

echo json_encode([
    "status" => "success",
    "source" => "live",
    "timestamp" => date("Y-m-d H:i:s"),
    "totalLines" => $totalLines,
    "distribution" => $distribution,
    "runtime" => [
        "engine" => "PHP " . PHP_VERSION
    ]
], JSON_UNESCAPED_UNICODE);
