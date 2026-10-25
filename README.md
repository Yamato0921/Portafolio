# 🌟 Portafolio Interactivo Sebastian Giraldo

Un portafolio web interactivo de alto impacto con una estética editorial y retro-futurista.

Desarrollado de forma integral y armónica utilizando los 6 lenguajes y herramientas solicitados:
- **HTML5**: Semántica estricta (WCAG AAA), gráficos vectoriales SVG multicapa, Canvas procedural y etiquetas semánticas.
- **CSS3**: Variables CSS3 (tokens de diseño), transformaciones 3D con perspectiva, modo dual (Editorial Limpio vs. SynthWave '84 Neón) y líneas de barrido CRT.
- **JavaScript (ES6+)**: Parallax con interpolación matemática (Lerp) a 60 FPS, motor de telemetría de viewport Tornis, sintetizador de audio analógico con Web Audio API y renderizado asíncrono.
- **Bootstrap 5**: Cuadrículas adaptativas de 12 columnas, modales de casos de estudio y cajón de terminal Offcanvas.
- **PHP 8+**: Renderizado del lado del servidor (SSR), generación de tokens CSRF, validación honeypot y endpoint REST seguro para el formulario de contacto.
- **Python 3.14**: Microservicio HTTP ultraligero sin dependencias externas, analizador estático de código mediante AST (`PyTelemetry`) y visor interactivo de portafolio para la consola CLI.

---

## 🚀 Características Principales

### 1. Avatar Vectorial Interactivo (Sello Sebastian Giraldo)
- Ilustración SVG personalizada con rizos tipo twists que caen sobre la frente, tono de piel cálido, rasgos faciales, arete y camiseta oscura.
- La mirada sigue la posición del cursor en tiempo real con efecto de profundidad y amortiguación (*damping*).
- Al hacer clic sobre el avatar, guiña un ojo, emite un sonido arcade sintetizado y muestra bocadillos de diálogo rotativos en español.

### 2. HUD de Telemetría Reactiva (Inspirado en Tornis)
- Barra de estado en vivo que monitoriza en tiempo real:
  - Coordenadas `(X, Y)` del cursor.
  - Velocidad instantánea en `px/s`.
  - Desplazamiento de scroll.
  - Tasa de refresco del navegador en `FPS`.

### 3. Modo Dual: Editorial Limpio & SynthWave '84
- **Modo Editorial Limpio**: Fondo crema cálido (`#fbfaf6`), tipografía negra de alto contraste (`Outfit` y `Space Grotesk`) y generoso espacio en blanco.
- **Modo SynthWave '84**: Fondo violeta oscuro (`#171520`), resplandores neón rosa (`#ff7edb`) y cian (`#36f9f6`), suelo con rejilla 3D en perspectiva y efecto de escaneo CRT.

### 4. Sintetizador de Audio Analógico (Web Audio API)
- Sonidos generados puramente por software mediante osciladores y nodos de ganancia en JavaScript (sin necesidad de archivos MP3 externos).
- Clics táctiles tipo teclado mecánico, pitidos arcade y acordes armónicos de confirmación.

### 5. Terminal CLI Interactiva de Desarrollador
- Panel lateral accesible con la tecla `/` o desde el botón de la barra superior.
- Admite comandos en español como `ayuda`, `quiensoy`, `habilidades`, `proyectos`, `telemetria`, `tema synthwave` y `limpiar`.
- Conectada con el microservicio en Python para consultas dinámicas.

---

## 📁 Estructura del Proyecto

```
c:\src\Portafolio\
├── index.html                  # Versión cliente interactiva (SPA)
├── index.php                   # Versión renderizada en servidor (PHP 8 SSR + CSRF)
├── README.md                   # Documentación completa del proyecto
├── api/
│   ├── contact.php             # Endpoint PHP para procesamiento seguro de contacto
│   └── projects.php            # Endpoint PHP para entrega y filtrado de proyectos en JSON
├── assets/
│   ├── favicon.svg             # Favicon con el logotipo triangular del portafolio
│   └── data/
│       └── projects.json       # Base de datos centralizada de proyectos y casos de estudio
├── backend_py/
│   ├── server.py               # Servidor HTTP y microservicio REST nativo en Python 3
│   ├── stats_service.py        # Motor de análisis estático y telemetría de código
│   └── cli_portfolio.py        # Visor interactivo del portafolio en consola de comandos
├── css/
│   ├── style.css               # Hoja de estilos principal y tokens de diseño editorial
│   └── synthwave.css           # Sobrescrituras y efectos de neón para SynthWave '84
└── js/
    ├── app.js                  # Orquestador principal de eventos, temas y AJAX
    ├── audio.js                # Sintetizador de audio con Web Audio API
    ├── avatar.js               # Lógica de seguimiento y parallax del avatar SVG
    ├── projects.js             # Gestor dinámico de proyectos y ventanas modales
    ├── terminal.js             # Controlador de la terminal interactiva en navegador
    └── viewport-tracker.js     # Motor reactivo de telemetría Tornis
```

---

## 🛠️ Cómo Ejecutar el Proyecto

Tienes múltiples formas de ejecutar y disfrutar del portafolio:

### Opción 1: Servidor Web Local en Python (Recomendado)
Con Python 3.8 o superior, inicia el servidor local con el script de tu sistema:

```text
Windows: doble clic en iniciar.bat o ejecutar .\iniciar.ps1
Otros sistemas: python3 backend_py/server.py
```
Abre tu navegador en: **`http://127.0.0.1:8000/index.html`**

El backend local sirve el portafolio, calcula telemetría en vivo y guarda los mensajes recibidos en `storage/mensajes_contacto.jsonl`.

---

### Opción 2: Visor Interactivo en Consola CLI (Python)
Puedes explorar todo el portafolio directamente en la terminal de Windows:

```powershell
python backend_py/cli_portfolio.py
```
Permite consultar proyectos, revisar habilidades con barras gráficas y medir las líneas de código del proyecto.

---

### Opción 3: Servidor PHP (Para la versión SSR)
Si tienes PHP instalado (o mediante XAMPP / Laragon):

```powershell
php -S 127.0.0.1:8080
```
Abre tu navegador en: **`http://127.0.0.1:8080/index.php`**

---

## Publicar en GitHub Pages

El proyecto incluye un flujo de GitHub Actions que construye y publica automáticamente la versión estática. Para activarlo:

1. Sube el proyecto a un repositorio de GitHub en la rama `main` o `master`.
2. En **Settings → Pages**, selecciona **GitHub Actions** como fuente de publicación.
3. Espera a que termine el flujo **Deploy portfolio to GitHub Pages** en la pestaña **Actions**.

El proceso publica solo `index.html`, `assets/`, `css/` y `js/`; no sube `.venv`, los archivos de almacenamiento ni el código PHP/Python del servidor. No requiere instalar paquetes para construirlo. También genera una instantánea de telemetría que mantiene disponible el comando `telemetria` en el alojamiento estático.

### Funciones y límites por tipo de alojamiento

- **GitHub Pages u otro hosting estático:** funcionan la interfaz, los proyectos, temas, avatar, audio, terminal y telemetría de compilación. El formulario prepara un correo con los datos ingresados; el visitante debe abrir su aplicación de correo y pulsar **Enviar**.
- **Servidor con Python:** la telemetría es en vivo y el formulario guarda mensajes en `storage/mensajes_contacto.jsonl`.
- **Servidor con PHP:** utiliza `index.php`, `api/stats.php` y `api/contact.php`; el formulario requiere que el directorio `storage/` permita escritura. `index.html` es la entrada estática y no genera el token de sesión que necesita el endpoint PHP.

GitHub Pages no ejecuta PHP ni Python. Por eso el sitio ofrece una alternativa explícita en la versión estática en lugar de simular que un formulario fue enviado. Los backends actuales guardan mensajes en el servidor; para recibir notificaciones por correo automáticamente se necesita configurar un proveedor de correo en el hosting.

Para otro hosting estático, genera la misma versión que se publica en Pages con `python scripts/build_pages.py` y sube el contenido de `dist/`.

El archivo `.gitignore` excluye entornos virtuales, datos privados de `storage/` y la carpeta generada `dist/`.

---

## 📝 Comentarios del Código

Cada archivo (`.html`, `.css`, `.js`, `.php`, `.py`, `.json`) ha sido extensamente comentado en **español** para explicar:
1. El propósito de cada módulo y función.
2. Los cálculos matemáticos de interpolación y perspectiva 3D.
3. Las medidas de seguridad aplicadas (Honeypot, sanitización y protección CSRF).
4. El funcionamiento de la síntesis de audio mediante osciladores Web Audio.
