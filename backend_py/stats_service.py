#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
==============================================================================
PYTELEMETRY: SERVICIO DE ANÁLISIS Y TELEMETRÍA DE CÓDIGO (PYTHON 3)
==============================================================================
Este script realiza una inspección profunda y en tiempo real del proyecto
en el directorio de trabajo (c:\\src\\Portafolio):
  - Recorre el árbol de directorios de forma segura (omitiendo temporales).
  - Cuenta archivos, líneas efectivas de código y bytes por tecnología.
  - Calcula la distribución porcentual del stack solicitado:
    HTML5, CSS3, JavaScript, PHP, Bootstrap y Python.
  - Entrega los resultados en un diccionario estructurado listo para JSON.
==============================================================================
"""

import os
import sys
import json
import time

# Mapa de tecnologías y las extensiones de archivo asociadas
EXTENSIONES_TECNOLOGICAS = {
    "HTML5": [".html", ".htm"],
    "CSS3": [".css"],
    "JavaScript": [".js", ".mjs"],
    "PHP": [".php"],
    "Python": [".py"],
    "JSON/Data": [".json"]
}

# Marca temporal de inicio para calcular el tiempo activo (uptime)
TIEMPO_INICIO = time.time()

def analizar_workspace(directorio_base=None):
    """
    Escanea el directorio del proyecto y recopila métricas cuantitativas
    sobre las líneas de código y el peso de cada lenguaje.

    :param directorio_base: Ruta absoluta del proyecto a inspeccionar.
    :return: Diccionario con estadísticas, porcentajes y metadatos de ejecución.
    """
    # Si no se especifica directorio, asumimos la raíz del proyecto
    if directorio_base is None:
        directorio_base = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
    
    # Inicialización de contadores por lenguaje
    conteo_archivos = {tecnologia: 0 for tecnologia in EXTENSIONES_TECNOLOGICAS}
    conteo_lineas = {tecnologia: 0 for tecnologia in EXTENSIONES_TECNOLOGICAS}
    total_bytes = {tecnologia: 0 for tecnologia in EXTENSIONES_TECNOLOGICAS}

    # Carpetas que se deben ignorar durante el recorrido
    carpetas_ignoradas = {
        ".git", ".github", ".system_generated", "node_modules", "__pycache__",
        ".vscode", "storage", ".venv", "venv", "env", "build", "dist"
    }

    # Recorrido recursivo del árbol de archivos
    for raiz, directorios, archivos in os.walk(directorio_base):
        # Filtramos en el lugar las carpetas ignoradas
        directorios[:] = [d for d in directorios if d not in carpetas_ignoradas]

        for archivo in archivos:
            extension = os.path.splitext(archivo)[1].lower()
            ruta_completa = os.path.join(raiz, archivo)

            # Verificamos a qué categoría pertenece la extensión
            for tech, extensiones in EXTENSIONES_TECNOLOGICAS.items():
                if extension in extensiones:
                    conteo_archivos[tech] += 1
                    try:
                        # Tamaño en bytes
                        tamano = os.path.getsize(ruta_completa)
                        total_bytes[tech] += tamano

                        # Conteo de líneas no vacías para evitar sesgo
                        with open(ruta_completa, "r", encoding="utf-8", errors="ignore") as f:
                            lineas = sum(1 for linea in f if linea.strip())
                            conteo_lineas[tech] += lineas
                    except Exception:
                        # Si un archivo temporal no puede leerse, se continúa sin interrumpir
                        pass

    # Suma total de líneas contabilizadas
    total_lineas_global = sum(conteo_lineas.values()) or 1

    # Cálculo de la distribución porcentual
    distribucion = {}
    for tech, lineas in conteo_lineas.items():
        porcentaje = round((lineas / total_lineas_global) * 100, 1)
        distribucion[tech] = {
            "lines": lineas,
            "files": conteo_archivos[tech],
            "bytes": total_bytes[tech],
            "percentage": porcentaje
        }

    # Matriz cualitativa de experiencia y nivel en cada tecnología
    matriz_habilidades = {
        "HTML5": {"score": 96, "experiencia": "5+ años", "nivel": "Avanzado / Semántica estricta & Canvas"},
        "CSS3": {"score": 98, "experiencia": "5+ años", "nivel": "Experto / 3D Transforms, Animaciones & SynthWave"},
        "JavaScript": {"score": 95, "experiencia": "5+ años", "nivel": "Avanzado / ES6+, Web Audio & Viewport Tornis"},
        "PHP": {"score": 90, "experiencia": "4+ años", "nivel": "Avanzado / PHP 8+, APIs REST & Seguridad"},
        "Bootstrap": {"score": 94, "experiencia": "5+ años", "level": "Experto / Bootstrap 5, Grid & Tokens Adaptativos"},
        "Python": {"score": 92, "experiencia": "4+ años", "nivel": "Avanzado / Microservicios, AST & Telemetría"}
    }

    # Cálculo del tiempo activo del servicio
    segundos_activo = round(time.time() - TIEMPO_INICIO, 1)

    # Respuesta estructurada final
    return {
        "status": "success",
        "source": "live",
        "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
        "uptimeSeconds": segundos_activo,
        "totalLines": total_lineas_global,
        "distribution": distribucion,
        "skillsProficiency": matriz_habilidades,
        "runtime": {
            "pythonVersion": sys.version.split()[0],
            "platform": sys.platform,
            "workspacePath": directorio_base
        }
    }

# Prueba de ejecución directa desde consola
if __name__ == "__main__":
    resultado = analizar_workspace()
    print(json.dumps(resultado, indent=2, ensure_ascii=False))
