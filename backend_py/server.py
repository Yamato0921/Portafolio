#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
==============================================================================
SERVIDOR WEB Y MICROSERVICIO DE BACKEND (PYTHON 3.14+)
==============================================================================
Este script implementa un servidor HTTP multifuncional y ligero utilizando
exclusivamente la librería estándar de Python (sin necesidad de pip install):
  1. Servidor de archivos estáticos: Sirve HTML, CSS, JS, imágenes y JSON.
  2. Endpoint REST de Telemetría: /api/py/stats (ejecuta stats_service.py).
  3. Endpoint REST de Proyectos: /api/projects (entrega projects.json).
  4. Endpoint de Terminal Interactiva: /api/py/terminal (interpreta comandos en español).
  5. Endpoint de Contacto de Respaldo: /api/py/contact (cuando no corre PHP).
==============================================================================
"""

import os
import sys
import json
import time
import urllib.parse
from http.server import HTTPServer, SimpleHTTPRequestHandler

# Forzar codificación UTF-8 en salida estándar para compatibilidad con Windows cp1252
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8")

# Inclusión del directorio local para importar el servicio de estadísticas
sys.path.insert(0, os.path.dirname(__file__))
from stats_service import analizar_workspace

# Directorio raíz del portafolio (c:\src\Portafolio)
BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
PUERTO_SERVIDOR = 8000

class ManejadorPortafolio(SimpleHTTPRequestHandler):
    """
    Controlador HTTP personalizado que combina el servicio de archivos estáticos
    con el procesamiento de peticiones API REST en formato JSON.
    """

    def __init__(self, *args, **kwargs):
        # Configuramos el directorio base del servidor
        super().__init__(*args, directory=BASE_DIR, **kwargs)

    def _establecer_cabeceras(self, codigo_estado=200, tipo_contenido="application/json"):
        """
        Envía cabeceras HTTP estándar incluyendo soporte para CORS y UTF-8.
        """
        self.send_response(codigo_estado)
        self.send_header("Content-Type", f"{tipo_contenido}; charset=utf-8")
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type, X-Requested-With")
        self.send_header("Cache-Control", "no-cache, no-store, must-revalidate")
        self.end_headers()

    def do_OPTIONS(self):
        """
        Maneja solicitudes preliminares de CORS (Pre-flight).
        """
        self._establecer_cabeceras(200, "text/plain")

    def do_GET(self):
        """
        Procesa solicitudes GET: intercepta endpoints API o delega a archivos estáticos.
        """
        ruta_parseada = urllib.parse.urlparse(self.path)
        ruta = ruta_parseada.path

        # 1. Endpoint de telemetría de código en vivo
        if ruta.rstrip("/").endswith("/api/py/stats"):
            metricas = analizar_workspace(BASE_DIR)
            self._establecer_cabeceras(200, "application/json")
            self.wfile.write(json.dumps(metricas, ensure_ascii=False).encode("utf-8"))
            return

        # 2. Endpoint de entrega del catálogo de proyectos en formato JSON
        if ruta.rstrip("/").endswith(("/api/projects", "/api/projects.php")):
            ruta_proyectos = os.path.join(BASE_DIR, "assets", "data", "projects.json")
            if os.path.exists(ruta_proyectos):
                with open(ruta_proyectos, "r", encoding="utf-8") as f:
                    contenido_json = f.read()
                self._establecer_cabeceras(200, "application/json")
                self.wfile.write(contenido_json.encode("utf-8"))
            else:
                self._establecer_cabeceras(404, "application/json")
                self.wfile.write(json.dumps({"error": "Catálogo no encontrado"}, ensure_ascii=False).encode("utf-8"))
            return

        # 3. Comportamiento por defecto: Servir archivos estáticos (index.html, style.css, etc.)
        return super().do_GET()

    def do_POST(self):
        """
        Procesa solicitudes POST: comandos de terminal interactiva y formularios.
        """
        ruta_parseada = urllib.parse.urlparse(self.path)
        ruta = ruta_parseada.path

        # Lectura de los datos del cuerpo de la petición
        longitud_contenido = int(self.headers.get("Content-Length", 0))
        cuerpo_crudo = self.rfile.read(longitud_contenido).decode("utf-8")

        # Decodificación de datos JSON o Formularios tradicionales
        datos = {}
        if "application/json" in self.headers.get("Content-Type", ""):
            try:
                datos = json.loads(cuerpo_crudo)
            except Exception:
                datos = {}
        else:
            datos_formulario = urllib.parse.parse_qs(cuerpo_crudo)
            datos = {k: v[0] for k, v in datos_formulario.items()}

        # 1. Endpoint para ejecutar comandos desde la terminal web
        if ruta.rstrip("/").endswith("/api/py/terminal"):
            comando = datos.get("command", "").strip().lower()
            respuesta = self.procesar_comando_terminal(comando)
            self._establecer_cabeceras(200, "application/json")
            self.wfile.write(json.dumps(respuesta, ensure_ascii=False).encode("utf-8"))
            return

        # 2. Endpoint de procesamiento del formulario de contacto
        if ruta.rstrip("/").endswith(("/api/py/contact", "/api/contact.php")):
            nombre = datos.get("name", "").strip()
            email = datos.get("email", "").strip()
            mensaje = datos.get("message", "").strip()
            asunto = datos.get("subject", "Nuevo mensaje del Portafolio").strip()

            if datos.get("website_url", "").strip():
                self._establecer_cabeceras(200, "application/json")
                self.wfile.write(json.dumps({
                    "status": "success",
                    "message": "Mensaje recibido correctamente."
                }, ensure_ascii=False).encode("utf-8"))
                return

            if len(nombre) < 2 or "@" not in email or len(mensaje) < 10:
                self._establecer_cabeceras(400, "application/json")
                self.wfile.write(json.dumps({
                    "status": "error",
                    "message": "Ingresa un nombre, un correo válido y un mensaje de al menos 10 caracteres."
                }, ensure_ascii=False).encode("utf-8"))
                return

            registro = {
                "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
                "nombre": nombre,
                "email": email,
                "asunto": asunto,
                "mensaje": mensaje
            }
            ruta_storage = os.path.join(BASE_DIR, "storage")
            try:
                os.makedirs(ruta_storage, exist_ok=True)
                with open(os.path.join(ruta_storage, "mensajes_contacto.jsonl"), "a", encoding="utf-8") as archivo:
                    archivo.write(json.dumps(registro, ensure_ascii=False) + "\n")
            except OSError:
                self._establecer_cabeceras(500, "application/json")
                self.wfile.write(json.dumps({
                    "status": "error",
                    "message": "El servidor no pudo guardar el mensaje. Inténtalo de nuevo más tarde."
                }, ensure_ascii=False).encode("utf-8"))
                return

            self._establecer_cabeceras(200, "application/json")
            self.wfile.write(json.dumps({
                "status": "success",
                "message": f"¡Gracias {nombre}! El mensaje quedó guardado en el servidor.",
                "received": {
                    "nombre": nombre,
                    "email": email,
                    "fecha": registro["timestamp"]
                }
            }, ensure_ascii=False).encode("utf-8"))
            return

        # Ruta no encontrada
        self._establecer_cabeceras(404, "application/json")
        self.wfile.write(json.dumps({"error": "Endpoint no encontrado"}, ensure_ascii=False).encode("utf-8"))

    def procesar_comando_terminal(self, cmd):
        """
        Interpreta comandos ingresados en la terminal CLI interactiva.
        """
        partes = cmd.split()
        comando_raiz = partes[0] if partes else ""

        if comando_raiz in ["ayuda", "help"]:
            return {
                "output": (
                    "Comandos Disponibles en la Terminal Python:\n"
                    "  ayuda / help      - Lista todos los comandos disponibles\n"
                    "  quiensoy / whoami - Muestra la identidad y filosofía del desarrollador\n"
                    "  habilidades       - Detalla el nivel de HTML5, CSS3, JS, PHP, Bootstrap y Python\n"
                    "  proyectos         - Resumen de proyectos destacados de código abierto\n"
                    "  telemetria        - Ejecuta análisis estático PyTelemetry en tiempo real\n"
                    "  tema <modo>       - Cambia el tema visual ('tema synthwave' o 'tema limpio')\n"
                    "  audio <on|off>    - Activa o desactiva la síntesis de sonido analógico\n"
                    "  limpiar / clear   - Limpia la pantalla de la terminal"
                )
            }
        elif comando_raiz in ["quiensoy", "whoami"]:
            return {
                "output": (
                    "Identidad: Desarrollador Creativo Independiente y Arquitecto Frontend.\n"
                    "Portafolio personal de Sebastian Giraldo (SynthWave '84, Tornis, Rekishi).\n"
                    "Filosofía: 'Construir proyectos web ambiciosos pero accesibles. Cuanto más salvajes, mejor.'\n"
                    "Ubicación: Remoto / Global."
                )
            }
        elif comando_raiz in ["habilidades", "skills"]:
            return {
                "output": (
                    "Matriz de Tecnologías y Nivel de Dominio:\n"
                    "  [HTML5]      - 96% | Semántica estricta, Layered SVGs, Canvas, ARIA Accesible\n"
                    "  [CSS3]       - 98% | 3D Transforms, Animaciones fluidas, Resplandor SynthWave '84\n"
                    "  [JavaScript] - 95% | ES6+, Web Audio API, Motor reactivo Tornis de Viewport\n"
                    "  [Bootstrap]  - 94% | Bootstrap 5, Grid adaptativo de 12 columnas, Offcanvas\n"
                    "  [PHP]        - 90% | PHP 8+, APIs REST seguras, Sanitización y Tokens CSRF\n"
                    "  [Python]     - 92% | Python 3, Análisis AST, Microservicios REST y CLI Tools"
                )
            }
        elif comando_raiz in ["proyectos", "projects"]:
            return {
                "output": (
                    "Proyectos Destacados:\n"
                    "  1. SynthWave '84 IDE Playground (HTML5/CSS3/JS/Bootstrap)\n"
                    "  2. Tornis Sensor & Viewport Engine (JS/Canvas/CSS3)\n"
                    "  3. OmniStack PHP & Python Backend Hub (PHP/Python/JS/Bootstrap)\n"
                    "  4. Rekishi History & Transition Controller (HTML5/JS)\n"
                    "  5. PyTelemetry Code Matrix (Python/Bootstrap)\n"
                    "Escribe 'tema synthwave' para encender la estética retro de los 80s."
                )
            }
        elif comando_raiz in ["telemetria", "stats"]:
            stats = analizar_workspace(BASE_DIR)
            dist = stats.get("distribution", {})
            lineas_dist = "\n".join([f"    {k:12} : {v['lines']} líneas ({v['percentage']}%)" for k, v in dist.items()])
            return {
                "output": (
                    f"Reporte de Telemetría PyTelemetry en Vivo:\n"
                    f"  Líneas Totales de Código : {stats.get('totalLines')}\n"
                    f"  Versión de Python        : {stats.get('runtime', {}).get('pythonVersion')}\n"
                    f"  Tiempo Activo (Uptime)   : {stats.get('uptimeSeconds')} segundos\n"
                    f"  Distribución de Lenguajes:\n{lineas_dist}"
                )
            }
        elif comando_raiz in ["tema", "theme"]:
            modo = partes[1] if len(partes) > 1 else ""
            if modo in ["synthwave", "limpio", "clean", "midnight"]:
                tema_final = "clean" if modo == "limpio" else modo
                return {
                    "action": "set_theme",
                    "theme": tema_final,
                    "output": f"Cambiando el tema visual a [{modo.upper()}]..."
                }
            return {"output": "Uso: tema <synthwave | limpio>"}
        elif comando_raiz in ["audio"]:
            estado = partes[1] if len(partes) > 1 else ""
            if estado in ["on", "off"]:
                return {
                    "action": "toggle_audio",
                    "state": estado,
                    "output": f"Sintetizador de audio configurado en [{estado.upper()}]."
                }
            return {"output": "Uso: audio <on | off>"}
        elif comando_raiz == "":
            return {"output": ""}
        else:
            return {"output": f"Comando no reconocido: '{cmd}'. Escribe 'ayuda' para ver la lista de comandos."}

class ServidorSeguro(HTTPServer):
    """
    Subclase de HTTPServer que permite reutilizar el puerto inmediatamente
    para evitar errores de socket en Windows.
    """
    allow_reuse_address = True

def iniciar_servidor():
    """
    Inicia el bucle de escucha del servidor HTTP local en el puerto designado.
    Si el puerto 8000 está ocupado, prueba automáticamente puertos alternativos (8001, 8080, etc.).
    """
    servidor_http = None
    puerto_activo = PUERTO_SERVIDOR
    puertos_a_probar = [8000, 8001, 8080, 8888]

    for p in puertos_a_probar:
        try:
            direccion_servidor = ("127.0.0.1", p)
            servidor_http = ServidorSeguro(direccion_servidor, ManejadorPortafolio)
            puerto_activo = p
            break
        except OSError:
            continue

    if servidor_http is None:
        raise OSError(
            "No se pudo iniciar el servidor en los puertos 8000, 8001, 8080 o 8888. "
            "Comprueba si están ocupados e inténtalo de nuevo."
        )

    print("\n" + "=" * 60)
    print(f"🚀 Servidor del Portafolio Activo en: http://127.0.0.1:{puerto_activo}/index.html")
    print(f"📁 Directorio servido: {BASE_DIR}")
    print(f"📡 API Telemetría Python: http://127.0.0.1:{puerto_activo}/api/py/stats")
    print(f"💡 Presiona Ctrl+C para detener el servidor.")
    print("=" * 60 + "\n")

    # Abrir navegador automáticamente cuando el socket esté 100% listo
    import threading
    import webbrowser
    def _abrir_navegador():
        time.sleep(0.7)
        try:
            webbrowser.open(f"http://127.0.0.1:{puerto_activo}/index.html")
        except Exception:
            pass
    threading.Thread(target=_abrir_navegador, daemon=True).start()

    try:
        servidor_http.serve_forever()
    except KeyboardInterrupt:
        print("\nDeteniendo servidor...")
        servidor_http.server_close()

if __name__ == "__main__":
    try:
        iniciar_servidor()
    except OSError as error:
        print(f"\n[ERROR] {error}\n", file=sys.stderr)
        raise SystemExit(1)
