@echo off
setlocal
cd /d "%~dp0"
chcp 65001 > nul
title Portafolio Interactivo - Servidor local
color 0b

echo ==============================================================================
echo    PORTAFOLIO INTERACTIVO DE SEBASTIAN GIRALDO
echo    Vista local con telemetria Python
echo ==============================================================================
echo.

set "PYTHON_EXE="
if exist "%~dp0.venv\Scripts\python.exe" set "PYTHON_EXE=%~dp0.venv\Scripts\python.exe"

if not defined PYTHON_EXE (
    where py >nul 2>nul
    if not errorlevel 1 set "PYTHON_EXE=py"
)

if not defined PYTHON_EXE (
    where python >nul 2>nul
    if not errorlevel 1 set "PYTHON_EXE=python"
)

if not defined PYTHON_EXE (
    echo [ERROR] No se encontro Python. Instala Python 3.8 o superior.
    exit /b 1
)

echo [OK] Python: %PYTHON_EXE%
echo [*] Iniciando el servidor local. Presiona Ctrl+C para detenerlo.
echo.
"%PYTHON_EXE%" "%~dp0backend_py\server.py"
set "EXIT_CODE=%ERRORLEVEL%"

if not "%EXIT_CODE%"=="0" (
    echo.
    echo [ERROR] El servidor termino con el codigo %EXIT_CODE%.
    pause
)

exit /b %EXIT_CODE%
