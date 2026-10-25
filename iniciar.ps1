# Inicia el servidor local con los endpoints Python de telemetria y contacto.
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Set-Location $PSScriptRoot

$pythonExe = Join-Path $PSScriptRoot ".venv\Scripts\python.exe"
if (-not (Test-Path $pythonExe)) {
    $pythonCommand = Get-Command py -ErrorAction SilentlyContinue
    if (-not $pythonCommand) {
        $pythonCommand = Get-Command python -ErrorAction SilentlyContinue
    }
    if ($pythonCommand) {
        $pythonExe = $pythonCommand.Source
    }
}

if (-not (Test-Path $pythonExe) -and -not (Get-Command $pythonExe -ErrorAction SilentlyContinue)) {
    Write-Error "No se encontró Python. Instala Python 3.8 o superior."
    exit 1
}

Write-Host "Iniciando el servidor local del portafolio..." -ForegroundColor Cyan
Write-Host "Presiona Ctrl+C para detenerlo." -ForegroundColor Gray
& $pythonExe "$PSScriptRoot\backend_py\server.py"
exit $LASTEXITCODE
