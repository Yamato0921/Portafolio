<?php
/**
 * ==============================================================================
 * API DE CONTACTO - PORTAFOLIO INTERACTIVO (PHP 8+)
 * ==============================================================================
 * Este archivo procesa los mensajes enviados a través del formulario de contacto.
 * Aplica buenas prácticas de desarrollo web:
 *  - Validación de métodos HTTP (sólo permite POST).
 *  - Configuración de cabeceras CORS y Content-Type JSON.
 *  - Sanitización y filtrado riguroso de entradas para prevenir XSS e inyecciones.
 *  - Validación de formato de correo electrónico.
 *  - Mecanismo anti-spam mediante campo trampa (Honeypot).
 *  - Respuestas estructuradas en formato JSON con códigos de estado HTTP semánticos.
 * ==============================================================================
 */

// 1. SESIÓN Y CONFIGURACIÓN DE CABECERAS HTTP
if (session_status() === PHP_SESSION_NONE && !session_start()) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo iniciar la sesión de seguridad."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Indicamos al cliente que la respuesta siempre será en formato JSON codificado en UTF-8.
header("Content-Type: application/json; charset=UTF-8");

// Permitir solicitudes de orígenes controlados (CORS) para desarrollo local o microservicios.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

// Si el navegador realiza una petición preliminar (pre-flight) con el método OPTIONS, respondemos 200 y salimos.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. VALIDACIÓN DEL MÉTODO HTTP
// Verificamos estrictamente que la petición entrante sea de tipo POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Código 405: Método no permitido
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido. Solo se aceptan peticiones POST."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Las páginas PHP incluyen el token de sesión en el formulario.
$csrfToken = trim($_POST['csrf_token'] ?? '');
if (stripos($_SERVER["CONTENT_TYPE"] ?? '', 'application/json') !== false) {
    $csrfPayload = json_decode(file_get_contents('php://input'), true);
    $csrfToken = trim(is_array($csrfPayload) ? ($csrfPayload['csrf_token'] ?? '') : '');
    $inputData = is_array($csrfPayload) ? $csrfPayload : [];
} else {
    $inputData = $_POST;
}

if (empty($_SESSION['csrf_token']) || $csrfToken === '' || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => "La sesión del formulario venció. Recarga la página e inténtalo de nuevo."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. CAPTURA Y DECODIFICACIÓN DE DATOS
// El formulario puede enviar los datos tanto como 'application/json' o como 'application/x-www-form-urlencoded'.
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';

// 4. VERIFICACIÓN DEL CAMPO HONEYPOT (Técnica Anti-Spam)
// Un campo oculto invisible para humanos que si contiene datos significa que fue llenado por un bot automático.
$honeypot = trim($inputData['website_url'] ?? '');
if (!empty($honeypot)) {
    // Si el bot llenó el campo trampa, simulamos éxito para despistarlo pero no procesamos el mensaje.
    echo json_encode([
        "status" => "success",
        "message" => "Mensaje recibido correctamente."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 5. SANITIZACIÓN Y LIMPIEZA DE LOS CAMPOS RECIBIDOS
// Limpiamos espacios en blanco al inicio y final y neutralizamos caracteres especiales para evitar XSS.
$nombre   = htmlspecialchars(strip_tags(trim($inputData['name'] ?? '')), ENT_QUOTES, 'UTF-8');
$email    = filter_var(trim($inputData['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$asunto   = htmlspecialchars(strip_tags(trim($inputData['subject'] ?? 'Nuevo mensaje del Portafolio')), ENT_QUOTES, 'UTF-8');
$mensaje  = htmlspecialchars(strip_tags(trim($inputData['message'] ?? '')), ENT_QUOTES, 'UTF-8');

// 6. VALIDACIONES DE CAMPOS OBLIGATORIOS
$errores = [];

if (empty($nombre) || mb_strlen($nombre) < 2) {
    $errores[] = "Por favor ingresa un nombre válido (mínimo 2 caracteres).";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Por favor ingresa un correo electrónico con formato válido.";
}

if (empty($mensaje) || mb_strlen($mensaje) < 10) {
    $errores[] = "El mensaje debe contener al menos 10 caracteres explicativos.";
}

// Si existen errores de validación, devolvemos error 400 Bad Request.
if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Datos de formulario inválidos.",
        "errors" => $errores
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 7. REGISTRO LOCAL / LOG DEL MENSAJE RECIBIDO
// Creamos una estructura de datos limpia con la fecha y hora de recepción.
$registro = [
    "timestamp" => date("Y-m-d H:i:s"),
    "nombre"    => $nombre,
    "email"     => $email,
    "asunto"    => $asunto,
    "mensaje"   => $mensaje,
    "ip_origen" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
];

// Opcional: Guardamos el mensaje en un archivo log seguro para persistencia local.
$logDir = __DIR__ . '/../storage';
if (!is_dir($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "El servidor no pudo preparar el almacenamiento del mensaje."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$bytesWritten = file_put_contents(
    $logDir . '/mensajes_contacto.jsonl',
    json_encode($registro, JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND | LOCK_EX
);
if ($bytesWritten === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "El servidor no pudo guardar el mensaje. Inténtalo de nuevo más tarde."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 8. RESPUESTA EXITOSA AL CLIENTE
// Devolvemos confirmación con código 200 OK y mensaje amigable en español.
http_response_code(200);
echo json_encode([
    "status"  => "success",
    "message" => "¡Muchas gracias, {$nombre}! El mensaje quedó guardado en el servidor.",
    "data"    => [
        "nombre"    => $nombre,
        "email"     => $email,
        "asunto"    => $asunto,
        "timestamp" => $registro["timestamp"]
    ]
], JSON_UNESCAPED_UNICODE);
