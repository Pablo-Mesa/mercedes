# Refactorización: Vistas y Estilos (prompt para AI / desarrollador)

Objetivo
- Revisar las vistas (`views/**/*.php`) y la hoja de estilos principal (`public/css/style.css`).
- Eliminar estilos inline y redundancias, corregir typos, y extraer reglas repetidas a utilidades CSS centralizadas.
- Mantener compatibilidad visual y accesibilidad.

Instrucciones generales
1. Buscar y reemplazar todos los atributos `style="..."` en `views/` por clases utilitarias. Evitar cambios funcionales en PHP, solo reemplazos HTML/CSS.
2. Corregir typos en estilos inline (ej. `align-item` → `align-items`) si se conservan temporalmente.
3. Añadir utilidades en `public/css/style.css` (ejemplos más abajo) y agrupar reglas duplicadas en secciones: botones, badges, layout, formularios, utilidades.
4. Reemplazar uso directo de `width:100%` y `display:none` por clases `.w-full` y `.hidden` respectivamente.
5. Añadir clases semánticas para componentes reutilizados: `.panel`, `.form-panel`, `.grid-form`, `.img-preview`, `.toast-modal`, `.cards-grid`.
6. Mantener comentarios claros en cada archivo modificado y crear un commit por conjunto lógico de cambios.

Archivos a tocar (sugeridos)
- `views/layouts/main.php`
- `views/produccion/*.php`
- `views/riders/*.php`
- `views/tarifas/*.php`
- `views/usuarios/*.php`
- `views/configuraciones/puntodecontrol.php`
- `public/css/style.css`

Cambios concretos (ejemplos antes → después)

- Caso 1: `views/tarifas/form.php`
  - Antes:
    <div style="display:flex; flex-direction: column; justify-content: center, align-item: center">
  - Después:
    <div class="d-flex flex-column justify-center align-center">

- Caso 2: `views/riders/form.php` (imagen preview)
  - Antes:
    <img id="preview" src="..." style="max-width:120px; margin-top:10px; display:block;" />
  - Después:
    <img id="preview" src="..." class="img-preview" />

Utilidades CSS sugeridas (añadir a `public/css/style.css`)
.d-flex { display:flex; }
.flex-column { flex-direction:column; }
.justify-center { justify-content:center; }
.align-center { align-items:center; }
.w-full { width:100%; }
.hidden { display:none !important; }
.text-center { text-align:center; }
.img-preview { max-width:120px; margin-top:10px; display:block; border-radius:6px; }
.btn-warning { background:#f6c24b; color:#1c1c1c; }
.badge { display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; }
.badge-success { background: #e8f8f0; color: var(--color-success); }
.badge-muted { background: #f4f6fa; color: #9aa3bf; }
.badge-role { background:#eef3ff; color:var(--color-primary-dark); }
.cards-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:16px; }
.toast-modal-overlay { position:fixed; inset:0; display:flex; align-items:flex-end; justify-content:center; pointer-events:none; }
.toast-modal { pointer-events:auto; background:#111827; color:#fff; padding:10px 14px; border-radius:8px; margin:24px; }

Notas y verificación
- Ejecutar una revisión manual por página clave (Dashboard, Riders, Tarifas, Usuarios, Punto de control).
- Abrir la app localmente y revisar visualmente: validar formularios, tablas, botones y toasts.
- Si usas un sistema de control de versiones: crear ramas por área (`refactor/styles`, `refactor/views`) y PR con cambios.

Comandos útiles (Windows / PowerShell)
```powershell
# Buscar estilos inline (para revisar antes de aplicar)
Select-String -Path .\views\**\*.php -Pattern 'style="' -SimpleMatch

# Reemplazo rápido (ejemplo, usar con precaución y revisar cambios)
(Get-Content views\tarifas\form.php) -replace 'style="[^"]+"', 'class="d-flex"' | Set-Content views\tarifas\form.php
```

Checklist rápida antes de cerrar
- [ ] No quedan `style="` en `views/` (o quedan solo casos justificables).
- [ ] `public/css/style.css` contiene las utilidades añadidas.
- [ ] Correcciones tipográficas aplicadas (`align-items`, etc.).
- [ ] Pruebas visuales realizadas en páginas clave.

Si quieres, puedo ejecutar los cambios automáticamente: dime si prefieres que

- A) Aplique solo las utilidades CSS y genere un diff para revisar.
- B) Aplique cambios automáticos en todas las vistas (reemplazos safe), luego revisas.
- C) Solo genere los commits sugeridos y el README con instrucciones para aplicar manualmente.
