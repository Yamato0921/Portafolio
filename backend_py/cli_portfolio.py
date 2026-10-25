#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
==============================================================================
VISOR DE PORTAFOLIO EN CONSOLA (CLI PORTFOLIO INTERACTIVE) - PYTHON 3
==============================================================================
Herramienta interactiva para explorar el portafolio directamente desde
la terminal de comandos de Windows (PowerShell / CMD / Bash).
Permite:
  1. Explorar proyectos con metadatos técnicos, estrellas y stack.
  2. Visualizar la matriz de habilidades con barras de progreso ANSI.
  3. Ejecutar PyTelemetry para análisis de líneas y porcentajes en vivo.
  4. Leer la filosofía creativa de desarrollo de Sebastian Giraldo.
  5. Iniciar el servidor web local con un solo comando.
==============================================================================
"""

import os
import sys
import json
import time

# Forzar codificación UTF-8 en salida estándar para compatibilidad con Windows cp1252
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8")

# Códigos de escape ANSI para colores vibrantes en la consola
CYAN = "\033[96m"
ROSA = "\033[95m"
AMARILLO = "\033[93m"
VERDE = "\033[92m"
ROJO = "\033[91m"
NEGRITA = "\033[1m"
RESET = "\033[0m"

# Logotipo en arte ASCII con estilo retro SynthWave '84
BANNER = f"""{ROSA}{NEGRITA}
   ██████╗  ██████╗ ██████╗ ██████╗  ██████╗ ██╗    ██╗███████╗███╗   ██╗
   ██╔══██╗██╔═══██╗██╔══██╗██╔══██╗██╔═══██╗██║    ██║██╔════╝████╗  ██║
   ██████╔╝██║   ██║██████╔╝██████╔╝██║   ██║██║ █╗ ██║█████╗  ██╔██╗ ██║
   ██╔══██╗██║   ██║██╔══██╗██╔══██╗██║   ██║██║███╗██║██╔══╝  ██║╚██╗██║
   ██║  ██║╚██████╔╝██████╔╝██████╔╝╚██████╔╝╚███╔███╔╝███████╗██║ ╚████║
   ╚═╝  ╚═╝ ╚═════╝ ╚═════╝ ╚═════╝  ╚═════╝  ╚══╝╚══╝ ╚══════╝╚═╝  ╚═══╝{RESET}
{CYAN}    ★ DESARROLLADOR CREATIVO & ARQUITECTO WEB | EDICIÓN SYNTHWAVE '84 ★{RESET}
"""

def mostrar_banner():
    """Imprime el banner principal con información del stack tecnológico."""
    print(BANNER)
    print(f"{AMARILLO}Portafolio personal de Sebastian Giraldo | Tema SynthWave '84{RESET}")
    print(f"Tecnologías: {NEGRITA}HTML5 • CSS3 • JavaScript • PHP 8 • Bootstrap 5 • Python 3{RESET}\n")

def cargar_proyectos():
    """Lee el catálogo de proyectos centralizado desde assets/data/projects.json."""
    directorio_raiz = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
    ruta_datos = os.path.join(directorio_raiz, "assets", "data", "projects.json")
    if os.path.exists(ruta_datos):
        with open(ruta_datos, "r", encoding="utf-8") as f:
            return json.load(f)
    return []

def mostrar_menu():
    """Despliega el menú de navegación para la terminal."""
    print(f"{CYAN}{NEGRITA}[1]{RESET} Ver Proyectos Destacados (Work)")
    print(f"{CYAN}{NEGRITA}[2]{RESET} Matriz de Tecnologías & Habilidades")
    print(f"{CYAN}{NEGRITA}[3]{RESET} Ejecutar PyTelemetry (Análisis en vivo del código)")
    print(f"{CYAN}{NEGRITA}[4]{RESET} Sobre el Desarrollador (Filosofía Creativa)")
    print(f"{CYAN}{NEGRITA}[5]{RESET} Iniciar Servidor Web Local (Puerto 8000)")
    print(f"{CYAN}{NEGRITA}[0]{RESET} Salir de la Consola\n")

def ver_proyectos(proyectos):
    """Muestra cada proyecto con formato enriquecido en consola."""
    print(f"\n{NEGRITA}══════════════════ CATÁLOGO DE PROYECTOS DESTACADOS ══════════════════{RESET}")
    for idx, p in enumerate(proyectos, 1):
        print(f"\n{ROSA}{NEGRITA}#{idx} {p['title']}{RESET} [{AMARILLO}{p['year']}{RESET}] - {CYAN}{p['stars']}{RESET}")
        print(f"   {p['subtitle']}")
        print(f"   {p['description']}")
        print(f"   {VERDE}Stack:{RESET} {', '.join(p['tags'])}")
    print(f"\n{NEGRITA}══════════════════════════════════════════════════════════════════════{RESET}\n")

def ver_habilidades():
    """Muestra la matriz de tecnologías requeridas con barras de progreso gráficas."""
    print(f"\n{NEGRITA}══════════════════ STACK TECNOLÓGICO Y DOMINIO ══════════════════{RESET}\n")
    habilidades = [
        ("HTML5", 96, "Semántica estricta, Layered SVGs, Canvas, Audio API"),
        ("CSS3", 98, "3D Transforms, SynthWave Glow, Animaciones, Glassmorphism"),
        ("JavaScript", 95, "ES6+, Tornis Viewport Engine, Web Audio, Event Pub/Sub"),
        ("Bootstrap", 94, "Bootstrap 5, Responsive Grid, Offcanvas, Modales"),
        ("PHP", 90, "PHP 8 REST APIs, CSRF Tokens, Templating Dinámico"),
        ("Python", 92, "Python 3 Microservicios, AST Telemetría, CLI Tools")
    ]
    for nombre, puntaje, desc in habilidades:
        longitud_barra = int(puntaje / 5)
        barra = "█" * longitud_barra + "░" * (20 - longitud_barra)
        print(f" {ROSA}{nombre:12}{RESET} [{CYAN}{barra}{RESET}] {puntaje}% | {desc}")
    print(f"\n{NEGRITA}══════════════════════════════════════════════════════════════════════{RESET}\n")

def ver_telemetria():
    """Ejecuta el escaneo de código del workspace en vivo."""
    sys.path.insert(0, os.path.dirname(__file__))
    from stats_service import analizar_workspace

    print(f"\n{AMARILLO}Ejecutando PyTelemetry sobre el workspace en c:\\src\\Portafolio...{RESET}")
    resultado = analizar_workspace()
    print(f"\n{VERDE}✓ Análisis completado con éxito:{RESET}")
    print(f"  • Total líneas de código : {NEGRITA}{resultado['totalLines']}{RESET}")
    print(f"  • Tiempo activo backend  : {resultado['uptimeSeconds']}s")
    print(f"  • Runtime de Python      : {resultado['runtime']['pythonVersion']}")
    print(f"\n{CYAN}Distribución por Tecnología:{RESET}")
    for tech, d in resultado["distribution"].items():
        print(f"    - {tech:12}: {d['lines']:4} líneas ({d['percentage']}%) [{d['files']} archivos]")
    print()

def main():
    """Función principal que ejecuta el bucle interactivo de la CLI."""
    proyectos = cargar_proyectos()
    mostrar_banner()
    while True:
        mostrar_menu()
        eleccion = input(f"{NEGRITA}Selecciona una opción [0-5]: {RESET}").strip()
        if eleccion == "1":
            ver_proyectos(proyectos)
        elif eleccion == "2":
            ver_habilidades()
        elif eleccion == "3":
            ver_telemetria()
        elif eleccion == "4":
            print(f"\n{NEGRITA}FILOSOFÍA CREATIVA (SEBASTIAN GIRALDO):{RESET}")
            print("  'Ayudo a organizaciones y personas a construir proyectos web ambiciosos pero accesibles'")
            print("  'Cuanto más salvajes y creativos, mejor.'")
            print("  Uniendo diseño editorial, microinteracciones y el brillo de SynthWave '84.\n")
        elif eleccion == "5":
            print(f"\n{VERDE}Iniciando servidor web local en http://127.0.0.1:8000 ...{RESET}")
            from server import iniciar_servidor
            iniciar_servidor()
            break
        elif eleccion == "0":
            print(f"\n{ROSA}¡Hasta pronto! Sigue construyendo proyectos web ambiciosos y accesibles.{RESET}\n")
            break
        else:
            print(f"\n{ROJO}Opción no válida. Por favor ingresa un número del 0 al 5.{RESET}\n")

if __name__ == "__main__":
    main()
