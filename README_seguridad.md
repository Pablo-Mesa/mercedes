Eres un auditor de seguridad de software. 
Analiza el proyecto Mercedes (PHP MVC) con el siguiente enfoque:

1. **Autenticación y sesiones**
   - Evalúa cómo se maneja login, logout y persistencia de sesión.
   - Identifica riesgos de session hijacking, session fixation o falta de expiración.

2. **Roles y permisos**
   - Revisa la implementación de roles (admin, operaciones, rider, invitado).
   - Señala si hay riesgo de escalamiento de privilegios o acceso manual por URL.

3. **Validaciones y sanitización**
   - Evalúa si los inputs están validados y sanitizados.
   - Identifica riesgos de SQL injection, XSS, CSRF.
   - Sugiere medidas de protección (prepared statements, tokens CSRF, escaping de salida).

4. **Configuración y entorno**
   - Revisa credenciales en `Database.php` y configuración local.
   - Señala riesgos de contraseñas vacías, exposición de archivos sensibles, uploads inseguros.

5. **Recomendaciones**
   - Propón medidas inmediatas para endurecer seguridad antes de poner en producción.
   - Diferencia entre lo que es crítico (no puede salir a campo sin esto) y lo que es recomendable a mediano plazo.

Tu salida debe ser clara, estructurada y práctica, con bullets o secciones. 
No generes código nuevo salvo que sea necesario para ilustrar un ajuste. 
El objetivo es tener un diagnóstico de seguridad y recomendaciones concretas.
