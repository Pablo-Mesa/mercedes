/**
 * public/js/main.js
 * Interacciones ligeras del frontend (sin dependencias externas).
 */

let contador = 0;
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
    const toggleBtn = document.getElementById('toggleVista');    
    const btnGrupo = document.getElementById('grupo');
    const selectRole = document.getElementById('role_id');
    const btnToggleTabla = document.getElementById('btnToggleTabla');
    const btnToggleTheme = document.getElementById('btnToggleTheme');
    const btnToggleEdit = document.getElementById('toggleEdit');

    const configBtn = document.getElementById("configBtn");
    const configDropdown = document.getElementById("configDropdown");

    const toggleCuaderno = document.getElementById('toggleCuaderno');
    const toggleAsistencias = document.getElementById('toggleAsistencias');

    const herramientasSelector = document.querySelector('.herramientas-selector');
    
    
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
            // Encendemos el loader global para cubrir toda la pantalla 
            // mientras el controlador de backend procesa las credenciales
            if (typeof Loader !== 'undefined') {
                Loader.show();
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

        const selectRider = document.getElementById("rider");
        const fechaModal = document.getElementById("fechaHoraViaje");
        
        btnAbrir.addEventListener("click", () => modal.style.display = "flex");
        btnCerrar.addEventListener("click", () => modal.style.display = "none");

        // Resetear campos
        selectRider.value = "";
        fechaModal.value = "";
        
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
                //reiniciar parcialmente el formulario
                reinicioParcialFormulario('*');
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
            //cierra modal  
            const detalleModal = document.getElementById('detalleModal');
            if (detalleModal && detalleModal.style.display === "flex") {                
                detalleModal.style.display = "none";
            }            
            // refrescar vista produccion con la fecha actual del formulario
            location.reload();
        }

    });

    // agregar listener al select Acciones de Riders "onchange"
    if (accion) {
        accion.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const codigo = selected.getAttribute('data-codigo');
            const efectivoFields = document.getElementById('efectivoFields'); 

            // 0. obtener referencia al campo total de factura
            const totalFacturaFields = document.getElementById('totalFactura');
            // 1. Obtenemos el valor actual del input
            const valorInput = totalFacturaFields.value.trim();
            // 2. Convertimos a número entero base 10
            const montoEntero = parseInt(valorInput, 10);

            const vueltoInput = document.getElementById('vuelto');
            //calcularVueltoMaximaDenominacion(totalFactura)
            if (codigo === 'efectivo') {
                efectivoFields.classList.remove('hidden');
                efectivoFields.classList.add('view');
                //efectivoFields.style.display = 'flex';
                vueltoInput.required = true;

                // 3. Validación estricta
                if (valorInput !== '' && !isNaN(montoEntero) && Number.isInteger(montoEntero) && montoEntero > 0) {
                    // 1. Obtenemos el entero puro desde la función (Ej: 13000)
                    const vueltoEntero = calcularVueltoMaximaDenominacion(montoEntero);                    
                    // 2. Lo guardamos en el input como un string numérico limpio ("13000")
                    vueltoInput.value = vueltoEntero.toString(); 
                }


            } else {
                efectivoFields.classList.remove('view');
                efectivoFields.classList.add('hidden');
                //efectivoFields.style.display = 'none';
                vueltoInput.required = false;
                vueltoInput.value = '';
            }
        });

        accion.dispatchEvent(new Event('change'));
    }

    function validarFormularioProduccion() {
        const totalFacturaInput = document.getElementById('totalFactura');
        const vueltoInput = document.getElementById('vuelto');
        const selected = accion.options[accion.selectedIndex];
        const codigo = selected?.getAttribute('data-codigo');

        const totalFactura = totalFacturaInput.value.trim();
        if (totalFactura === '' || isNaN(totalFactura) || Number(totalFactura) < 0) {
            showToast('Total factura es obligatorio y debe ser mayor o igual a 0.', 'error');
            totalFacturaInput.focus();
            return false;
        }

        if (codigo === 'efectivo') {
            const vuelto = vueltoInput.value.trim();
            if (vuelto === '' || isNaN(vuelto) || Number(vuelto) < 0) {
                showToast('Vuelto es obligatorio para cobro en efectivo y debe ser mayor o igual a 0.', 'error');
                vueltoInput.focus();
                return false;
            }
        }

        return true;
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
        }, 5000);
    };

    //abre y carga el modal con los detalles de produccion del rider seleccionado
    function openDetalleModal(riderId, fecha) {

        Loader.show();
        fetch('/mercedes/produccion/byRider?id=' + riderId + '&fecha=' + fecha)
        .then(res => res.json())
        .then(data => {
            const tablaDetalle = document.getElementById('detalleTable');
            const tbody = tablaDetalle.querySelector('tbody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                showToast("No se encontraron registros para esa fecha.");
                return;
            }

            let totalTarifas = 0;

            data.forEach(p => {
                const fechaObj = new Date(p.fecha_creacion);
                const horaStr = fechaObj.toLocaleTimeString('es-PY', {hour:'2-digit', minute:'2-digit'});
                const suma = Number(p.total_factura || 0) + Number(p.vuelto || 0);
                totalTarifas += Number(p.tarifa_detalle || 0);

                const turnoBadge = `<span class="badge-turno"><img src="${p.url_icono}" alt="turno"> ${p.turno}</span>`;

                const rendicionCell = (p.accion_nombre === 'Solo entrega')
                    ? `<td><span class="badge-check">✔️</span></td>`
                    : `<td>
                        <label class="switch">
                            <input type="checkbox" class="toggle-rendicion" data-id="${p.id}" ${p.rendicion ? 'checked' : ''}>
                            <span class="slider"></span>
                        </label>
                    </td>`;

                const vuelto = Number.isFinite(parseInt(p.vuelto, 10)) 
                    ? Math.trunc(parseInt(p.vuelto, 10)).toLocaleString('es-PY') 
                    : '0';

                tbody.innerHTML += `
                <tr>
                    <td>
                        <img src="/mercedes/public/uploads/grupos/${p.icono}" alt="icono grupo" style="height:20px;vertical-align:middle;margin-right:5px;">
                        <span>${p.grupo_nombre}</span>
                    </td>
                    <td>${turnoBadge}</td>
                    <td>
                        <span class="badge-hora">
                            <img src="/mercedes/public/images/hora_envio.png" alt="hora de envio">
                            ${horaStr}
                        </span>
                    </td>
                    
                    <td>Gs. ${parseInt(p.tarifa_detalle).toLocaleString('es-PY')}</td>
                    
                    <td>
                        ${p.accion_nombre}
                        ${suma > 0 ? `<br><span class="badge-pendiente" title="Total Gs.: ${Math.trunc(parseInt(p.total_factura, 10)).toLocaleString('es-PY')} # Vuelto Gs.: ${vuelto}">
                        Gs. ${suma.toLocaleString('es-PY')}</span>` : ''}
                    </td>
                    ${rendicionCell}
                    <td class="acciones-col hidden">
                        <a href="/mercedes/produccion/delete?id=${p.id}" class="btn btn-danger">&#128465; Eliminar</a>
                    </td>
                </tr>`;
            });

            // Pie con total
            let tfoot = tablaDetalle.querySelector('tfoot');
            if (!tfoot) {
                tfoot = document.createElement('tfoot');                
                tablaDetalle.appendChild(tfoot);
            }
            /*<td colspan="${window.userRole === 'admin' ? 2 : 1}"></td>*/
            tfoot.innerHTML = `
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align:center; font-weight:bold;">TOTAL</td>
                    <td style="text-align:center;">
                        <span class="badge-total">Gs. ${totalTarifas.toLocaleString('es-PY')}</span>
                    </td>
                    <td></td>
                </tr>`;            
            document.getElementById('detalleModal').style.display = 'flex';
        }).catch(error => {
            // 2. Opcional: Manejo básico si hay una caída del backend
            console.error("Error al traer el detalle:", error);
            showToast("Ocurrió un error al procesar la solicitud.");
        })
        .finally(() => {
            // 3. SE OCULTA SIEMPRE: Tanto si terminó el código del modal con éxito,
            // o si entró en el bloque .catch por un error de red.
            Loader.hide();
        }); 
    }
    
    // Botón de cerrar modal
    if(closeModal){
        closeModal.addEventListener('click', () => {
            document.getElementById('detalleModal').style.display = 'none';
            // refrescar vista produccion con la fecha actual del formulario
            location.reload();
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
            document.getElementById('rider_name').textContent = riderName;
            document.getElementById('rider_production_date').textContent = girarFecha(fechaInput);            
            openDetalleModal(riderId, fechaInput);
        });
    });

    // Botones de agregar producción
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        btn.addEventListener('click', function() {
            const riderId = this.dataset.rider;
            const select = document.getElementById('rider');
            
            const fechaVista = document.getElementById("fecha"); // input en la vista
            const fechaModal = document.getElementById("fechaHoraViaje"); 

            // Buscar y seleccionar la opción correcta
            Array.from(select.options).forEach(opt => {
                opt.selected = (opt.value === riderId);                
            });
            
            // Tomar fecha de la vista y pasarla al modal
            if (fechaVista.value) {
                // Convertir la fecha YYYY-MM-DD a formato datetime-local (YYYY-MM-DDTHH:MM)
                // Usamos medianoche como hora por defecto
                fechaModal.value = fechaVista.value + "T00:00";
            }

            //reiniciar parcialmente el formulario
            reinicioParcialFormulario('');

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
            Loader.show();
            fetch('/mercedes/produccion/rendicion_update?id=' + idProduccion + '&estado=' + estado)
            .then(res => {
                if (!res.ok) {
                throw new Error('Respuesta HTTP inválida');
                }
                return res.text(); // siempre lo tratamos como texto primero
            })
            .then(txt => {
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
            }).catch(err => {
                console.error('Error en fetch:', err);
                alert('Error de conexión');
                e.target.checked = !estado;
            }).finally(() => {
                // 3. SE OCULTA SIEMPRE: Tanto si terminó el código del modal con éxito,
                // o si entró en el bloque .catch por un error de red.
                Loader.hide();
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
        Loader.show();
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
        })
        .finally(() => {
            // 3. SE OCULTA SIEMPRE: Tanto si terminó el código del modal con éxito,
            // o si entró en el bloque .catch por un error de red.
            Loader.hide();
        });

    }

    if (formProduccion) {
        document.getElementById('formProduccion').addEventListener('submit', function(e) {
            e.preventDefault(); // evita el envío clásico
            if (!validarFormularioProduccion()) {
                return;
            }
            guardarProduccion(this); // pasa el formulario a la función
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

    /**
     * Calcula el vuelto asumiendo que el cliente siempre paga con billetes de 100.000 Gs.
     * @param {number} totalFactura - El monto total de la factura.
     * @returns {number} El vuelto/cambio estimado para el peor de los casos.
     */
    function calcularVueltoMaximaDenominacion(totalFactura) {
        if (!totalFactura || totalFactura <= 0) return 0;

        const BILLETE_MAXIMO = 100000;

        // Calcula cuántos billetes de 100.000 se necesitan (redondeando hacia arriba)
        const cantidadBilletes = Math.ceil(totalFactura / BILLETE_MAXIMO);
        
        // Monto total con el que pagaría el cliente
        const pagoPresumible = cantidadBilletes * BILLETE_MAXIMO;

        // Retorna el vuelto
        return pagoPresumible - totalFactura;
    }

    // JS para alternar scroll interno ↔ expandido
    /*si toggleBtn existe*/
    if(toggleBtn){

        const gridCards = document.querySelector('.cards-grid');
        const tablaProduccion = document.getElementById('tablaProduccion');

        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isGridVisible = gridCards.style.display !== 'none';

            if (isGridVisible) {
                gridCards.style.display = 'none';
                tablaProduccion.style.display = 'flex';
                toggleBtn.textContent = '🗂️ Ver en cards';
            } else {
                gridCards.style.display = 'flex';
                tablaProduccion.style.display = 'none';
                toggleBtn.textContent = '📊 Ver en tabla';
            }
        });

    }

    function reinicioParcialFormulario(cuanto){
        const slctRider = document.getElementById('rider');
        const slctTarifa = document.getElementById('tarifa');
        const slctAccion = document.getElementById('frmProduccion_accion');
        const txtTotalFactura = document.getElementById('totalFactura');
        const txtVuelto = document.getElementById('vuelto');
        const efeFields = document.getElementById('efectivoFields');

        //reiniciar valores del
        slctTarifa.focus();
        slctTarifa.selectedIndex = 0;
        slctAccion.selectedIndex = 2;
        txtVuelto.value = "";
        txtTotalFactura.value = "";
        efeFields.classList.remove('view');
        efeFields.classList.add('hidden');
        if(cuanto === 'all' || cuanto === '*'){
            slctRider.selectedIndex = 0;
        }
    }
   
    /*si boton grupo exite, entonces*/
    if(btnGrupo){
        btnGrupo.addEventListener('change', function() {
            const grupoId = this.value;
            const tarifaSelect = document.getElementById('tarifa');
            if (!grupoId) return;            
            Loader.show();
            /* promesa ejecuta 3 fetch */
            Promise.all([

                /*fetch riders*/
                fetch('/mercedes/produccion/getRidersByGrupoAjax?grupo=' + grupoId)
                    .then(res => res.json())
                    .then(data => {
                    const riderSelect = document.getElementById('rider');
                    riderSelect.innerHTML = '<option value="">-- Seleccionar Rider --</option>';
                    data.forEach(r => {
                        riderSelect.innerHTML += `<option value="${r.id}">${r.name}</option>`;
                    });
                    }),

                /*fetch tarifas*/    
                fetch('/mercedes/produccion/getTarifasByGrupoAjax?grupo=' + grupoId)
                    .then(res => res.json())
                    .then(data => {
                    tarifaSelect.innerHTML = '<option value="">-- Seleccionar Tarifa --</option>';
                    data.forEach(t => {
                            tarifaSelect.innerHTML += `<option value="${t.id}">${parseInt(t.costo).toLocaleString()}</option>`;
                        });
                    }),

                /*fetch turnos*/    
                fetch('/mercedes/produccion/getTurnosByGrupoAjax?grupo=' + grupoId)
                .then(res => res.json())
                .then(data => {
                    const turnoSelect = document.getElementById('turno');
                    turnoSelect.innerHTML = '<option value="">-- Seleccionar Turno --</option>';

                    // hora actual local (Asunción)
                    const now = new Date();
                    const formatter = new Intl.DateTimeFormat('es-PY', {
                        timeZone: 'America/Asuncion',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                    const parts = formatter.formatToParts(now);
                    const hour = parseInt(parts.find(p => p.type === 'hour').value, 10);
                    const minute = parseInt(parts.find(p => p.type === 'minute').value, 10);
                    const currentMinutes = hour * 60 + minute;

                    let autoSelectedId = null;

                    data.forEach(t => {
                        const inicio = timeToMinutes(t.hora_inicio);
                        const fin    = timeToMinutes(t.hora_fin);

                        const opt = document.createElement('option');
                        opt.value = t.id;
                        opt.textContent = `${t.turno} (${t.hora_inicio} - ${t.hora_fin})`;

                        // usar función que soporta rangos cruzando medianoche
                        if (isWithinRange(currentMinutes, inicio, fin)) {
                            autoSelectedId = t.id;
                        }

                        turnoSelect.appendChild(opt);
                    });

                    // seleccionar automáticamente
                    if (autoSelectedId) {
                        turnoSelect.value = autoSelectedId;
                    }

                }).catch(err => {
                    console.error('Error cargando turnos:', err);
                    const turnoSelect = document.getElementById('turno');
                    turnoSelect.innerHTML = '<option value="">Error al cargar turnos</option>';
                }) 

            ])
            .catch(err => console.error('Error cargando datos:', err))
            .finally(() => Loader.hide());                
        });
    }

    // helpers
    function timeToMinutes(timeStr) {
        const [h, m] = timeStr.split(':').map(Number);
        return h * 60 + m;
    }

    function isWithinRange(currentMinutes, inicio, fin) {
        if (inicio <= fin) {
            return currentMinutes >= inicio && currentMinutes <= fin;
        } else {
            // rango que cruza medianoche
            return currentMinutes >= inicio || currentMinutes <= fin;
        }
    }

    /*si select roles existe*/
    if(selectRole){
        selectRole.addEventListener('change', function() {
            const grupoField = document.getElementById('grupoField');
            const selectedRole = this.options[this.selectedIndex].text.toLowerCase();
            if (selectedRole.includes('operaciones')) {
                grupoField.style.display = 'flex';
            } else {
                grupoField.style.display = 'none';
            }
        });
    }

    /*si btnToggleTabla*/
    if(btnToggleTabla){
        btnToggleTabla.addEventListener('click', function() {
            const completa = document.getElementById('tablaCompleta');
            const compacta = document.getElementById('tablaCompacta');

            if (completa.style.display === 'none') {
                completa.style.display = 'block';
                compacta.style.display = 'none';
            } else {
                completa.style.display = 'none';
                compacta.style.display = 'block';
            }
        });
    }

    /*si btnToggleTheme*/
    if(btnToggleTheme){
        btnToggleTheme.addEventListener('click', function() {
        const resumen = document.querySelector('.resumen-grupos');
        const tabla = document.querySelector('.tabla-resumen');
        const iconos = document.querySelectorAll('.icono-grupo');

        if (resumen.classList.contains('resumen-grupos-dark')) {
            // volver a claro
            resumen.classList.remove('resumen-grupos-dark');
            tabla.classList.remove('tabla-resumen-dark');
            iconos.forEach(i => i.classList.remove('icono-grupo-dark'));
        } else {
            // activar oscuro
            resumen.classList.add('resumen-grupos-dark');
            tabla.classList.add('tabla-resumen-dark');
            iconos.forEach(i => i.classList.add('icono-grupo-dark'));
        }
        });
    }

    /*si btnToggleEdit edita tabla detalles produccion*/
    if(btnToggleEdit){
        btnToggleEdit.addEventListener('click', function() {
            const accionesCols = document.querySelectorAll('.acciones-col');
            const isHidden = accionesCols[0].classList.contains('hidden');

            accionesCols.forEach(col => {
                if (isHidden) {//si columna oculta, entonces
                    col.classList.remove('hidden');//visualizar
                }
                else {//sino, entonces
                    col.classList.add('hidden');//ocultar
                }
            });

            // Cambiar estado del botón
            this.classList.toggle('active');
            this.textContent = isHidden ? '❌ Cerrar edición' : '✏️ Editar tabla';
        });
    }

    /*toggleMenu abre menu configuraciones*/
    function toggleMenu() {
        const isOpen = configDropdown.classList.contains("open");
        configDropdown.classList.toggle("open", !isOpen);
        if(configBtn) {configBtn.setAttribute("aria-expanded", !isOpen);} 
    }

    /*si configBtn*/
    if(configBtn){
        // Click con mouse
        configBtn.addEventListener("click", toggleMenu);

        // Teclado: Enter o barra espaciadora
        configBtn.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                toggleMenu();
            }
        });
    }

    // Opcional: cerrar si se hace click fuera
    document.addEventListener("click", (e) => {
        if (!configBtn.contains(e.target) && !configDropdown.contains(e.target)) {
            configDropdown.classList.remove("open");
            configBtn.setAttribute("aria-expanded", false);
        }
    });
    
    //actualizar estados en la vista herramientas    
    function actualizarVistas() {
        const tabla = document.querySelector('.tabla-herramientas');
        if (!tabla) return; // si no existe, salimos sin error

        const filas = tabla.querySelectorAll('tbody tr');
        const btnGuardar = document.getElementById('btnGuardar');
        if (!btnGuardar) return;;

        // activar/desactivar columnas completas
        const cuadernoActivo = document.getElementById('toggleCuaderno').checked;
        const asistenciasActivo = document.getElementById('toggleAsistencias').checked;

        tabla.classList.toggle('active-cuaderno', cuadernoActivo);
        tabla.classList.toggle('active-asistencias', asistenciasActivo);

        // La selección vacía también es válida: permite desactivar ambas herramientas.
        btnGuardar.disabled = false;

        filas.forEach(fila => {
            const vista = fila.querySelector('.vista-item');
            const cuadernoCheck = fila.querySelector('.col-cuaderno input');
            const asistenciasCheck = fila.querySelector('.col-asistencias input');

            vista.classList.remove('active-cuaderno', 'active-asistencias');

            if (cuadernoActivo && cuadernoCheck.checked) {
                vista.classList.add('active-cuaderno');
            }
            if (asistenciasActivo && asistenciasCheck.checked) {
                vista.classList.add('active-asistencias');
            }
        });
    }

    //si toggle cuaderno, entonces
    if(toggleCuaderno){
        toggleCuaderno.addEventListener('change', actualizarVistas);
    }

    //si toggle asistencias, entonces
    if(toggleAsistencias){
        toggleAsistencias.addEventListener('change', actualizarVistas);
    }   

    // inicializar
    actualizarVistas();

    // si formulario seleccion de herramientas existe, entonces
    if(herramientasSelector){
        // formulario seleccion de herramientas
        document.querySelector('.herramientas-selector').addEventListener('submit', function(e) {
            e.preventDefault(); // Evitamos el submit clásico
            Loader.show();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' } // para que el backend sepa que es AJAX
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success'); // tu función de toast
                    //retardo para recarga de pagina
                    setTimeout(() => {
                        location.reload();
                    }, 1200); // 1,2 segundos para que se vea el toast
                } else {
                    showToast('Error al guardar', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error de conexión');
            })
            .finally(() => {
                Loader.hide();                
            });
                
        });
    }

});