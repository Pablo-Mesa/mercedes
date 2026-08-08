/**
 * public/js/main.js
 * Interacciones ligeras del frontend (sin dependencias externas).
 */
document.addEventListener('DOMContentLoaded', function () {

    const input_foto_perfil = document.getElementById('foto_perfil');
    const modal = document.getElementById("modalProduccion");
    const turno_label = document.getElementById('turnoLabel');
    const accion = document.getElementById('frmProduccion_accion');
    const closeModal = document.getElementById('closeModal');

    const hamburger = document.getElementById('hamburger-btn');
    const sidebar = document.querySelector('.sidebar');

    const overlay = document.getElementById('sidebar-overlay');
    const fechaInput = document.getElementById('fechaHoraViaje');

    const select = document.getElementById("mesesSelect");
    const mesActual = new Date().getMonth(); // 0 = Enero, 11 = Diciembre

    // Array de nombres de meses en español
    const meses = [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ];

    const diasSemana = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

    const diasContainer = document.getElementById("diasContainer");

    const hoy = new Date();
    const anioActual = hoy.getFullYear();

    const formProduccion = document.getElementById('formProduccion');

    const turnoSelect = document.getElementById('turno');
    const turnoIconPreview = document.getElementById('turnoIconPreview');
    
    //obtener fecha de paraguay    
    function getDatePy(){
        // Obtener fecha actual en Paraguay
        const fechaParaguay = new Date().toLocaleDateString("es-PY", {
            timeZone: "America/Asuncion",
            year: "numeric",
            month: "2-digit",
            day: "2-digit"
        });

        // fechaParaguay viene como "03/08/2026"
        const partes = fechaParaguay.split("/");
        const fechaStr = `${partes[2]}-${partes[1]}-${partes[0]}`; // YYYY-MM-DD

        return fechaStr;
    }

    // Confirmación antes de activar/desactivar un usuario (acción reversible)
    document.querySelectorAll('a[href*="/usuarios/estado"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const isDeactivating = link.classList.contains('btn-danger-outline');
            const mensaje = isDeactivating
                ? '¿Deseas desactivar este usuario? Podrás reactivarlo cuando quieras.'
                : '¿Deseas activar este usuario nuevamente?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });

    // Pequeño feedback visual en el botón de envío del formulario de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function () {
            const btn = loginForm.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Ingresando...';
            }
        });
    }

    // Auto-cierre de alertas tras unos segundos
    document.querySelectorAll('.alert').forEach(function (alertBox) {
        setTimeout(function () {
            alertBox.style.transition = 'opacity 0.4s ease';
            alertBox.style.opacity = '0';
            setTimeout(function () {
                alertBox.remove();
            }, 400);
        }, 5000);
    });

    // 1. Busca el primer input que sea de tipo texto, email, url, etc.
    // Excluye inputs ocultos (hidden), botones, checkboxes o radios.
    const primerInput = document.querySelector(
        'form input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"])'
    );

    // 2. Si encuentra un input válido en la página actual, le da el foco
    if (primerInput) {
        primerInput.focus();
    }

    const items = document.querySelectorAll('.sidebar-nav .nav-item');
    let currentIndex = 0; // índice del enlace actualmente "seleccionado"

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentIndex = (currentIndex + 1) % items.length;
            items[currentIndex].focus();
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentIndex = (currentIndex - 1 + items.length) % items.length;
            items[currentIndex].focus();
        }
    });

    if(input_foto_perfil){        
        document.getElementById('foto_perfil').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });        
    }

    /*si modal existe*/
    if(modal){
        const btnAbrir = document.getElementById("btnAbrirModal");
        const btnCerrar = document.getElementById("btnCerrarModal");

        btnAbrir.addEventListener("click", () => modal.style.display = "flex");
        btnCerrar.addEventListener("click", () => modal.style.display = "none");

        // Cerrar si se hace clic fuera del contenido
        window.addEventListener("click", (e) => {
            if (e.target === modal) modal.style.display = "none";
        });
    }

    if(turno_label){

        const hidden = document.getElementById('turnoHidden'); 
        const turnoSwitch = document.getElementById('turnoSwitch');
        const turnoIcon = document.getElementById('turnoIcon');

        turnoSwitch.addEventListener('change', function() {        
            
            if (this.checked) {
                hidden.value = 2; // Nocturno
                turno_label.textContent = 'Nocturno';
                turnoIcon.textContent = '🌙'; // icono nocturno
            } else {
                hidden.value = 1; // Diurno
                turno_label.textContent = 'Diurno';
                turnoIcon.textContent = '☀️'; // icono diurno
            }
        });
    }

    // Detectar teclas globales
    document.addEventListener('keydown', function(event) {
        // F2 abre modal de nuevo registro
        if (event.key === "F2") {
            event.preventDefault(); // evita que el navegador use F2
            const btnNuevo = document.querySelector('.btn-nuevo'); 
            if (btnNuevo) {
            btnNuevo.click(); // simula click en el botón "Nuevo"
            }
        }

        // Escape cierra modal de producción
        if (event.key === "Escape") {
            //cierra modal produccion
            const modal = document.getElementById('modalProduccion');
            if (modal && modal.style.display === "flex") {
                modal.style.display = "none";
            }
            //cierra modal detalle 
            const detalleModal = document.getElementById('detalleModal');
            if (detalleModal && detalleModal.style.display === "flex") {
                detalleModal.style.display = "none";
            }            
        }

    });

    // agregar listener al select Acciones de Riders "onchange"
    if(accion){        
        accion.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const requiereFactura = selected.getAttribute('data-requiere-factura');
            const efectivoFields = document.getElementById('efectivoFields');
            console.log('value: '+requiereFactura);
            if (requiereFactura === "1") {
                efectivoFields.style.display = 'block';
            } else {
                efectivoFields.style.display = 'none';
            }
        });
    }
    
    // function mostrar mensaje tipo toast
    window.showToast = function(message, type = 'success') {
        const overlay = document.getElementById('toast-modal');
        const modal = overlay.querySelector('.toast-modal');
        const iconEl = document.getElementById('toast-icon');
        const msgEl = document.getElementById('toast-message');

        let icon = '';
        switch(type) {
            case 'success': icon = '✔️'; break;
            case 'error':   icon = '❌'; break;
            case 'info':    icon = 'ℹ️'; break;
            case 'warning': icon = '⚠️'; break;
        }

        iconEl.textContent = icon;
        msgEl.textContent = message;
        modal.className = `toast-modal ${type}`;

        overlay.style.display = 'flex';

        setTimeout(() => {
            overlay.style.display = 'none';
        }, 3000);
    };

    //abre y carga el modal con los detalles de produccion del rider seleccionado
    function openDetalleModal(riderId, fecha) {
        fetch('/mercedes/produccion/detalle?rider=' + riderId + '&fecha=' + fecha)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#detalleTable tbody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                // No hay registros: mostrar mensaje y no abrir modal
                showToast("No se encontraron registros para esa fecha.");
                return;
            }

            let totalTarifas = 0; // acumulador

            data.forEach(p => {
                const fecha = new Date(p.fecha_creacion);
                const horaStr = fecha.toLocaleTimeString('es-PY', {hour:'2-digit', minute:'2-digit'});
                const suma = Number(p.total_factura || 0) + Number(p.vuelto || 0);

                // acumular tarifa
                totalTarifas += Number(p.tarifa_detalle || 0);

                const turnoBadge = p.turno === 'Diurno'
                    ? `<span class="badge-turno badge-diurno"><img src="/mercedes/public/images/sun.png" alt="diurno"> ${p.turno}</span>`
                    : `<span class="badge-turno badge-nocturno"><img src="/mercedes/public/images/moon.png" alt="nocturno"> ${p.turno}</span>`;

                const rendicionCell = (p.accion_nombre === 'Solo entrega')
                    ? `<td><span class="badge-check">✔️</span></td>`
                    : `<td>
                        <label class="switch">
                            <input type="checkbox" 
                                class="toggle-rendicion" 
                                data-id="${p.id}" 
                                ${p.rendicion ? 'checked' : ''}>
                            <span class="slider"></span>
                        </label>
                    </td>`;    

                tbody.innerHTML += `
                <tr>                    
                    <td>
                        <span class="badge-hora">
                        <img src="/mercedes/public/images/hora_envio.png" alt="hora de envio">
                        ${horaStr}</span>
                    </td>                    
                    <td>${turnoBadge}</td>
                    <td>Gs. ${parseInt(p.tarifa_detalle).toLocaleString('es-PY')}</td>
                    <td>
                        ${p.accion_nombre}
                        ${suma > 0 ? `<br><span class="badge-pendiente">Gs. ${suma.toLocaleString('es-PY')}</span>` : ''}
                    </td>
                    ${rendicionCell}                            
                    <td>
                        <a href="/mercedes/produccion/delete?id=${p.id}" class="btn btn-danger">&#128465; Eliminar</a>
                    </td>
                </tr>`;
            });

            // fila de total al final
            const trTotal = document.createElement('tr');
            trTotal.innerHTML = `
                <td colspan="2" style="text-align:right; font-weight:bold;">TOTAL TARIFAS</td>
                <td colspan="0">
                    <span class="badge-total">Gs. ${totalTarifas.toLocaleString('es-PY')}</span>
                </td>
            `;
            tbody.appendChild(trTotal);


            document.getElementById('detalleModal').style.display = 'flex';
        });
    }

    // Botón de cerrar modal
    if(closeModal){
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('detalleModal').style.display = 'none';
        });
    }    

    // Asignar evento a todos los botones de detalle
    document.querySelectorAll('.btn-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            const fechaInput = document.getElementById("fecha").value; // formato YYYY-MM-DD
            this.setAttribute("data-fecha", fechaInput);
            const riderId = this.dataset.rider;
            const riderName = this.dataset.riderName;
            // Mostrar nombre en el modal
            document.getElementById('rider_name').textContent = riderName + ' # ' + girarFecha(fechaInput);            
            openDetalleModal(riderId, fechaInput);
        });
    });

    // Botones de agregar producción
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        btn.addEventListener('click', function() {
            const riderId = this.dataset.rider;
            const select = document.getElementById('rider');
            const slctTarifa = document.getElementById('tarifa');

            // Buscar y seleccionar la opción correcta
            Array.from(select.options).forEach(opt => {
                opt.selected = (opt.value === riderId);
                slctTarifa.focus();
            });
            // Mostrar el modal
            document.getElementById('modalProduccion').style.display = 'flex';
        });
    });

    //boton menu hamburguesa
    if(hamburger){
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });        
    }    

    // * utilizado para cerrar btn menu hamburguesa al dar click fuera del menu
    if(overlay){
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.style.display = 'none';
        });
    }

    // inicializa el campo fecha
    if (fechaInput) {
        // Crear objeto con la hora local de Asunción
        const ahora = new Date();

        // Ajustar a zona horaria de Asunción (GMT-4)
        // Intl.DateTimeFormat con timeZone asegura la conversión correcta
        const opciones = { timeZone: 'America/Asuncion', hour12: false };
        const partes = new Intl.DateTimeFormat('en-CA', {
        ...opciones,
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
        }).formatToParts(ahora);

        const valores = {};
        partes.forEach(p => { valores[p.type] = p.value; });

        // Formato compatible con datetime-local: YYYY-MM-DDTHH:MM
        const valor = `${valores.year}-${valores.month}-${valores.day}T${valores.hour}:${valores.minute}`;
        fechaInput.value = valor;
    }

    // funcion cargar detalle produccion
    function cargarDetalleProducciones(riderId, riderName) {
        fetch('/mercedes/produccion/byRider?id=' + riderId)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#detalleTable tbody');
            tbody.innerHTML = ''; // limpiar filas anteriores
            document.getElementById('rider_name').textContent = riderName;

            let totalTarifas = 0; // acumulador

            data.forEach(detalle => {
                const tr = document.createElement('tr');

                // Fecha y hora separadas
                const fecha = new Date(detalle.fecha_creacion);
                const fechaStr = fecha.toLocaleDateString('es-PY');
                const horaStr = fecha.toLocaleTimeString('es-PY', {hour: '2-digit', minute: '2-digit'});

                // acumular tarifa
                totalTarifas += Number(detalle.tarifa_detalle || 0);

                tr.innerHTML = `
                <td>${fechaStr}</td>
                <td>${horaStr}</td>
                <td>${detalle.accion_nombre}</td>
                <td>${detalle.turno}</td>
                <td>Gs. ${Number(detalle.tarifa_detalle).toLocaleString('es-PY')}</td>
                <td>
                    <label class="switch">
                        <input type="checkbox" 
                            class="toggle-rendicion" 
                            data-id="${detalle.id}" 
                            ${detalle.rendicion ? 'checked' : ''}>
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    <button class="btn btn-info">Editar</button>
                </td>
                `;
                tbody.appendChild(tr);
            });

            // agregar fila de total al final
            if (data.length > 0) {
                const trTotal = document.createElement('tr');
                trTotal.innerHTML = `
                    <td colspan="4" style="text-align:right; font-weight:bold;">TOTAL</td>
                    <td style="font-weight:bold;">Gs. ${totalTarifas.toLocaleString('es-PY')}</td>
                    <td colspan="2"></td>
                `;
                tbody.appendChild(trTotal);
            }
        });
    }


    // agregar listener a los elementos que contiene la clase toggle-rencion "onchange" 
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('toggle-rendicion')) {
            const idProduccion = e.target.dataset.id;
            const estado = e.target.checked ? 1 : 0;
            fetch('/mercedes/produccion/rendicion_update?id=' + idProduccion + '&estado=' + estado)
            .then(res => {
                if (!res.ok) {
                throw new Error('Respuesta HTTP inválida');
                }
                return res.text(); // siempre lo tratamos como texto primero
            })
            .then(txt => {
                console.log('Respuesta cruda:', txt); // útil para depuración
                const data = JSON.parse(txt.trim()); // parse seguro

                if (!data.success) {
                alert('Error al actualizar rendición');
                e.target.checked = !estado; // revertir si falla
                } else {
                // actualizar badge en la tarjeta del rider
                const badge = document.querySelector(`.badge-rendicion[data-rider="${data.id_rider}"]`);
                if (badge) {
                    badge.textContent = estado ? 'Rendido' : 'Pendiente';
                    badge.className = 'badge-rendicion ' + (estado ? 'rendido' : 'pendiente');
                }
                }
            })
            .catch(err => {
                console.error('Error en fetch:', err);
                alert('Error de conexión');
                e.target.checked = !estado;
            });
        }
    });

    // Función para dibujar días
    function dibujarDias(mesSeleccionado) {
        diasContainer.innerHTML = ""; // limpiar
        // Cantidad de días del mes seleccionado
        const ultimoDia = new Date(anioActual, mesSeleccionado + 1, 0).getDate();
        // Si es el mes actual, solo hasta hoy
        const limite = (mesSeleccionado === mesActual) ? hoy.getDate() : ultimoDia;
        for (let d = 1; d <= limite; d++) {
            const fecha = new Date(anioActual, mesSeleccionado, d);
            //const nombreDia = diasSemana[fecha.getDay()]; // abreviado
            const div = document.createElement("div");
            div.textContent = `${d} `; //${nombreDia}
            div.classList.add("dias");
            diasContainer.appendChild(div);
        }
        // Verificamos si NO tiene la clase 'activo'
        if (!diasContainer.classList.contains("diasContainer")) {
            // Si no la tiene, la agregamos
            diasContainer.classList.add("diasContainer");
        }
    }

    if(select){

        // Redibujar al cambiar selección
        select.addEventListener("change", function() {
            dibujarDias(parseInt(this.value));
        });

        // Llenar el select con meses transcurridos
        for (let i = 0; i <= mesActual; i++) {
            const option = document.createElement("option");
            option.value = i; // valor = índice del mes
            option.textContent = meses[i];
            if (i === mesActual) option.selected = true; // seleccionar mes actual
            select.appendChild(option);
        }
    }

    // Dibujar inicialmente el mes actual
    //dibujarDias(mesActual);

    function girarFecha(fecha){
        // Obtener el valor del input (YYYY-MM-DD)
        const valor = fecha; // ejemplo: "2026-08-03"
        // Dividir en partes
        const partes = valor.split("-"); // ["2026","08","03"]
        // Reordenar a dd:mm:yyyy
        const fechaFormateada = `${partes[2]}-${partes[1]}-${partes[0]}`;
        //retornar
        return fechaFormateada;
    }

    function guardarProduccion(form) {
        fetch('/mercedes/produccion/store', {
            method: 'POST',
            body: new FormData(document.getElementById('formProduccion'))
        })
        .then(res => {
            if (!res.ok) {
                throw new Error("Respuesta HTTP no OK: " + res.status);
            }
            return res.json();
        })
        .then(resp => {
            if (resp.success) {
                showToast(resp.message);
                const riderId = document.querySelector('[name="id_rider"]').value;
                const fecha = document.querySelector('[name="fecha_creacion"]').value;
                const soloFecha = fecha.split("T")[0]; 
                console.log(soloFecha); // "2026-08-05"


                // refrescar vista produccion con la fecha actual del formulario
                window.location.href = '/mercedes/produccion?fecha=' + soloFecha;
            }
            else{
                showToast(resp.message);
            }
        })
        .catch(err => {
            console.error("Error en fetch:", err);
            showToast("Error de conexión con el servidor");
        });

    }

    if(formProduccion){
        document.getElementById('formProduccion').addEventListener('submit', function(e) {
            e.preventDefault(); // evita el envío clásico
            guardarProduccion(this); // pasa el formulario a la función
            document.getElementById('modalProduccion').style.display = 'none';
        });
    }

     function actualizarIcono() {
        const iconUrl = turnoSelect.selectedOptions[0]?.dataset.icon;
        if (iconUrl) {
        turnoIconPreview.style.backgroundImage = `url(${iconUrl})`;
        } else {
        turnoIconPreview.style.backgroundImage = '';
        }
    }

    if (turnoSelect) {
        turnoSelect.addEventListener('change', actualizarIcono);
        actualizarIcono(); // inicializar al cargar
    }

});