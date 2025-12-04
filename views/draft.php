<div class="dashboard-layout">

    <!-- Sidebar -->
    <section class="sidebar">
        <a data-target="sec1">Sección 1</a>
        <a data-target="sec2">Sección 2</a>
        <a data-target="sec3">Sección 3</a>
    </section>

    <!-- Main del dashboard -->
    <main id="dashboard-main">
        <h2>Selecciona una sección</h2>
    </main>

</div>
https://github.com/QCristian/MVC

<!-- Contenido oculto -->
<div id="sec1" class="content" hidden>
    <h2>Contenido de la Sección 1</h2>
    <p>Estructura base</p>
    <pre>
    /mi-proyecto
    │
    ├─ /controllers
    │   ├─ HomeController.php
    │   ├─ SobreController.php
    │   └─ ServiciosController.php
    │
    ├─ /models
    │   └─ ModeloEjemplo.php
    │
    ├─ /views
    │   ├─ home.php
    │   ├─ sobre.php
    │   ├─ servicios.php
    │   └─ contacto.php
    │
    └─ /public
        ├─ index.php
        ├─ estilos.css
        ├─ app.js
        └─ .htaccess
    </pre>
</div>

<div id="sec2" class="content" hidden>
    <h2>Contenido de la Sección 2</h2>
    <p>IPv4 & IPv6 - ✅ ¿Para qué sirve cada una?</p>
        <p>
            IPv4
            La IP tradicional de toda la vida.
            Se usa para identificar dispositivos en la red.
            Formato corto: 192.168.1.10
            Es la que usarás para tu servidor local, tu app MVC y accesos en LAN.
        </p>
        <p>
            IPv6
            Nueva versión por falta de IPv4.
            Mucho más larga: fe80::1a2b:4c5d:...
            Se usa en redes modernas y en internet.
            NO la necesitas para tu proyecto local.
        </p>
    <pre>

        🧠 RESUMEN TOTAL (de todo lo que hablamos sobre IP)
        IPv4 = la dirección que usarás para que otros dispositivos accedan a tu app (192.168.x.x).
        IPv6 = no te sirve para tu proyecto local actual.
        Identificación rápida:
        Con puntos → IPv4
        Con dos puntos → IPv6
        Para verlas:
        Windows: ipconfig
        Linux/Mac: ifconfig o ip addr
        Para que otros dispositivos entren a tu app sin DNS:
        Deben usar tu IPv4 local o editar su archivo hosts.
        Para que entren sin configurar nada:
        Se necesita DNS en el router.
    </pre>
</div>

<div id="sec3" class="content" hidden>
    <h2>Contenido de la Sección 3</h2>
    <p>Texto de ejemplo 3...</p>
    <pre>
        <h3>Segunda sección</h3>
        <h4>CSS</h4>
        <p>La forma más correcta y eficaz en MVC con .htaccess es: <br>
        ✅ 1. Guardar los estilos en /public/css/
            <pre>
                public/css/estilos.css
            </pre>
        </p>
        <p>
        ✅ 2. En las vistas, cargar el CSS con ruta absoluta desde public:
            <pre>
                link rel="stylesheet" href="/css/estilos.css"
            </pre>
        </p>
        <p>
        ✅ 3. No usar rutas relativas (../) porque con rutas amigables fallan.
            <pre>
                👉 Todo recurso estático (CSS, JS, imágenes) se guarda en /public/ y se referencia con rutas absolutas.
            </pre>
        </p>
    </pre>
</div>

<div id="sec4" class="content" hidden>



</div>
























<?php $extraJs = "/js/script.js"; ?>