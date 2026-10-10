
<?php
/**
 * views/aboutdev/index.php
 *
 * Página informativa del desarrollador de Solver App.
 * Se renderiza dentro de views/layouts/main.php.
 *
 * Los estilos se encuentran en:
 * public/css/aboutdev.css
 */
?>
<link rel="stylesheet" href="/mercedes/public/css/aboutdev.css">
<div class="aboutdev">

    <!-- 1. PRESENTACIÓN -->
    <section class="aboutdev-hero" aria-labelledby="aboutdev-title">

        <div class="aboutdev-hero-content">

            <span class="aboutdev-eyebrow">
                ABOUT DEV · SOLVER APP
            </span>

            <h1 id="aboutdev-title">
                De la experiencia operativa
                <span>al desarrollo de software.</span>
            </h1>

            <p class="aboutdev-hero-description">
                Soy el desarrollador de <strong>Solver App</strong>,
                una aplicación web que nace de mi experiencia
                directa trabajando como repartidor.
            </p>

            <p>
                Conocer desde dentro el trabajo de delivery me permitió
                identificar necesidades reales relacionadas con el
                registro de servicios, los turnos, las tarifas,
                las rendiciones y el control de asistencias.
            </p>

            <p>
                Solver App representa la unión de dos aspectos
                de mi trabajo: comprender una necesidad operativa
                y utilizar la programación para construir una solución.
            </p>

            <div class="aboutdev-hero-actions">

                <a href="#aboutdev-origen"
                   class="aboutdev-btn aboutdev-btn-primary">
                    Conocer el proyecto
                </a>

                <a href="#aboutdev-github"
                   class="aboutdev-btn aboutdev-btn-outline">
                    Código y documentación
                </a>

            </div>

        </div>

        <div class="aboutdev-hero-visual" aria-hidden="true">

            <div class="aboutdev-visual-card">

                <span class="aboutdev-visual-label">
                    SOLVER APP
                </span>

                <div class="aboutdev-visual-symbol">
                    &lt;/&gt;
                </div>

                <p>
                    Experiencia real.<br>
                    Solución digital.<br>
                    Evolución continua.
                </p>

            </div>

        </div>

    </section>


    <!-- 2. EL ORIGEN -->
    <section
        id="aboutdev-origen"
        class="aboutdev-section"
        aria-labelledby="aboutdev-origen-title"
    >

        <div class="aboutdev-section-heading">

            <span class="aboutdev-section-number">
                01 / EL ORIGEN
            </span>

            <h2 id="aboutdev-origen-title">
                Una necesidad observada desde dentro
            </h2>

            <p class="aboutdev-section-intro">
                Antes de convertirse en una aplicación,
                Solver App comenzó como una idea nacida
                de una situación cotidiana.
            </p>

        </div>

        <div class="aboutdev-text-content">

            <p>
                En la operación que dio origen al proyecto,
                varios repartidores prestan servicios a dos
                negocios gastronómicos ubicados en un mismo predio.
            </p>

            <p>
                Durante cada jornada es necesario registrar
                quién realizó un servicio, para qué empresa,
                qué tarifa corresponde y si existe alguna
                rendición pendiente.
            </p>

            <p>
                Parte de ese seguimiento se realizaba mediante
                un cuaderno físico, cuyas páginas posteriormente
                se compartían por WhatsApp. Las llegadas de los
                repartidores también se comunicaban mediante mensajes.
            </p>

            <div class="aboutdev-highlight">

                <p>
                    <strong>La oportunidad:</strong>
                    centralizar la información y facilitar su consulta,
                    reduciendo la dependencia del papel y de mensajes
                    dispersos.
                </p>

            </div>

            <p>
                Así comenzó Solver App: no como un ejercicio académico,
                sino como un proyecto inspirado en una operación
                que conozco personalmente.
            </p>

        </div>

    </section>


    <!-- 3. LA SOLUCIÓN -->
    <section
        id="aboutdev-solucion"
        class="aboutdev-section"
        aria-labelledby="aboutdev-solucion-title"
    >

        <div class="aboutdev-section-heading">

            <span class="aboutdev-section-number">
                02 / LA SOLUCIÓN
            </span>

            <h2 id="aboutdev-solucion-title">
                Dos herramientas, una aplicación
            </h2>

            <p class="aboutdev-section-intro">
                Solver App reúne dos herramientas complementarias,
                diseñadas para atender diferentes necesidades
                de la operación de reparto.
            </p>

        </div>

        <div class="aboutdev-tools-grid">

            <!-- CUADERNO -->
            <article class="aboutdev-tool-card">

                <div class="aboutdev-tool-icon" aria-hidden="true">
                    &#128221;
                </div>

                <span class="aboutdev-tool-category">
                    GESTIÓN DE PRODUCCIÓN
                </span>

                <h3>Cuaderno</h3>
                
                <figure class="aboutdev-screenshot">
                    <img
                        src="/mercedes/public/images/aboutdev/cuaderno.webp"
                        alt="Vista de la herramienta Cuaderno de Solver App"
                        loading="lazy"
                        decoding="async"
                    >
                    <figcaption>
                        Registro y seguimiento de producción en Cuaderno.
                    </figcaption>
                </figure>

                <p>
                    Permite registrar los servicios realizados
                    por los repartidores y relacionarlos con
                    empresas, turnos, tarifas y acciones.
                </p>

                <p>
                    Contempla operaciones con efectivo, POS
                    y entregas que no requieren rendición
                    de dinero.
                </p>

                <p>
                    Ofrece resúmenes por Rider, vistas agrupadas
                    por empresa y seguimiento de tarifas,
                    totales y rendiciones pendientes.
                </p>

                <div class="aboutdev-tool-footer">
                    Producción · Tarifas · Rendiciones
                </div>

            </article>


            <!-- ASISTENCIAS -->
            <article class="aboutdev-tool-card">

                <div class="aboutdev-tool-icon" aria-hidden="true">
                    &#128205;
                </div>

                <span class="aboutdev-tool-category">
                    CONTROL DE ASISTENCIAS
                </span>

                <h3>Marcaciones y ubicación</h3>

                <p>
                    Permite configurar un punto de control
                    geográfico con su ubicación y radio
                    de cobertura.
                </p>

                <p>
                    Ofrece a los repartidores una interfaz
                    para realizar marcaciones de asistencia
                    y consultar sus registros.
                </p>

                <p>
                    Integra mapas y geolocalización como
                    parte de la gestión de llegadas
                    y salidas.
                </p>

                <div class="aboutdev-tool-footer">
                    Asistencias · Mapas · Punto de control
                </div>

            </article>

        </div>

        <p class="aboutdev-section-note">
            Ambas herramientas forman parte de Solver App
            y pueden habilitarse de manera independiente.
        </p>

    </section>


    <!-- 4. DESARROLLO -->
    <section
        id="aboutdev-desarrollo"
        class="aboutdev-section"
        aria-labelledby="aboutdev-desarrollo-title"
    >

        <div class="aboutdev-section-heading">

            <span class="aboutdev-section-number">
                03 / DESARROLLO
            </span>

            <h2 id="aboutdev-desarrollo-title">
                Cómo está construido Solver App
            </h2>

            <p class="aboutdev-section-intro">
                Detrás de cada pantalla hay decisiones
                relacionadas con la organización de datos,
                las reglas de negocio y la experiencia de uso.
            </p>

        </div>

        <div class="aboutdev-text-content">

            <p>
                Solver App está desarrollado utilizando
                <strong>PHP, SQL, HTML, CSS y JavaScript</strong>,
                con una arquitectura MVC en evolución.
            </p>

            <p>
                El desarrollo busca separar responsabilidades,
                reutilizar funcionalidades y facilitar mejoras
                progresivas sin afectar los procesos existentes.
            </p>

        </div>

        <div class="aboutdev-features-grid">

            <article class="aboutdev-feature">

                <h3>Acceso por roles</h3>

                <p>
                    Administrador, Operaciones, Rider e Invitado,
                    con funciones y vistas diferenciadas.
                </p>

            </article>

            <article class="aboutdev-feature">

                <h3>Organización operativa</h3>

                <p>
                    Producción vinculada con repartidores,
                    empresas, turnos, tarifas y acciones.
                </p>

            </article>

            <article class="aboutdev-feature">

                <h3>Consultas adaptadas</h3>

                <p>
                    Información presentada según las necesidades
                    de administración, operaciones, repartidores
                    y usuarios de consulta.
                </p>

            </article>

            <article class="aboutdev-feature">

                <h3>Herramientas independientes</h3>

                <p>
                    Cuaderno y Control de Asistencias pueden
                    habilitarse según las necesidades operativas.
                </p>

            </article>

            <article class="aboutdev-feature">

                <h3>Geolocalización</h3>

                <p>
                    Integración de mapas, puntos de control
                    y radios de cobertura.
                </p>

            </article>

            <article class="aboutdev-feature">

                <h3>Mejora continua</h3>

                <p>
                    Revisión de código, ajustes funcionales
                    y evolución progresiva de la interfaz
                    y la arquitectura.
                </p>

            </article>

        </div>

        <div class="aboutdev-tech">

            <h3>Tecnologías y conceptos</h3>

            <div class="aboutdev-tech-list"
                 aria-label="Tecnologías utilizadas">

                <span>PHP</span>
                <span>SQL</span>
                <span>HTML</span>
                <span>CSS</span>
                <span>JavaScript</span>
                <span>MVC</span>
                <span>Roles y permisos</span>
                <span>Geolocalización</span>

            </div>

        </div>

    </section>


    <!-- 5. EVOLUCIÓN -->
    <section
        id="aboutdev-evolucion"
        class="aboutdev-section"
        aria-labelledby="aboutdev-evolucion-title"
    >

        <div class="aboutdev-section-heading">

            <span class="aboutdev-section-number">
                04 / EVOLUCIÓN
            </span>

            <h2 id="aboutdev-evolucion-title">
                Construido para seguir creciendo
            </h2>

            <p class="aboutdev-section-intro">
                Solver App parte de una operación concreta,
                pero su evolución contempla escenarios
                de mayor alcance.
            </p>

        </div>

        <div class="aboutdev-evolution-grid">

            <article class="aboutdev-evolution-card">

                <span class="aboutdev-status aboutdev-status-current">
                    CONTEXTO ACTUAL
                </span>

                <h3>Una operación compartida</h3>

                <p>
                    El proyecto se desarrolla tomando como referencia
                    una operación de reparto que presta servicios
                    a dos negocios ubicados en un mismo punto físico.
                </p>

                <p>
                    Solver App permite organizar información
                    de producción y asistencias dentro de
                    ese contexto operativo.
                </p>

            </article>

            <article class="aboutdev-evolution-card">

                <span class="aboutdev-status aboutdev-status-future">
                    VISIÓN FUTURA
                </span>

                <h3>Múltiples puntos de control</h3>

                <p>
                    La evolución prevista contempla que
                    el administrador general pueda incorporar
                    nuevos puntos de control y gestionar
                    diferentes ubicaciones.
                </p>

                <p>
                    Cada operación podría organizar sus empresas,
                    turnos, tarifas y repartidores, manteniendo
                    una administración central.
                </p>

            </article>

        </div>

        <p class="aboutdev-section-note">
            La gestión de múltiples puntos independientes
            es una funcionalidad proyectada.
            Solver App continúa en desarrollo.
        </p>

    </section>


    <!-- 6. GITHUB Y DOCUMENTACIÓN -->
    <section
        id="aboutdev-github"
        class="aboutdev-section aboutdev-github"
        aria-labelledby="aboutdev-github-title"
    >

        <div class="aboutdev-section-heading">

            <span class="aboutdev-section-number">
                05 / CÓDIGO Y DOCUMENTACIÓN
            </span>

            <h2 id="aboutdev-github-title">
                Explorar el proyecto
            </h2>

            <p class="aboutdev-section-intro">
                El código también forma parte de la historia
                de Solver App.
            </p>

        </div>

        <div class="aboutdev-github-content">

            <p>
                Este proyecto refleja un proceso continuo
                de aprendizaje, desarrollo y resolución
                de problemas mediante software.
            </p>

            <p>
                Su repositorio en GitHub permite explorar
                el código fuente, la organización del sistema,
                su documentación y los cambios realizados
                durante su evolución.
            </p>

            <p>
                Compartir el proyecto no significa presentarlo
                como un producto perfecto o terminado,
                sino mostrar el trabajo que existe detrás
                de una aplicación inspirada en una necesidad real.
            </p>

            <!-- PENDIENTE: Sustituir el enlace cuando confirmemos la URL pública del repositorio. -->
            <div class="aboutdev-github-action">
                <a href="https://github.com/Pablo-Mesa/mercedes.git" class="aboutdev-github-link" target="_blank" rel="noopener noreferrer">
                    <span class="github-icon-wrapper">
                        <?= render_icon('github', 'nav-icon') ?>
                    </span> 
                    Ver repositorio en GitHub
                </a>
            </div>

        </div>

    </section>


    <!-- PIE DE ABOUT DEV -->
    <footer class="aboutdev-footer">

        <p>
            <strong>Solver App</strong>
            · Proyecto funcional en desarrollo
            y evolución continua.
        </p>

        <a href="#aboutdev-title" class="aboutdev-back-top">
            Volver al inicio &uarr;
        </a>

    </footer>

</div>
