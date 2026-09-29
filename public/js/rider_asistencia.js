// Reloj en tiempo real
function updateClock() {
    const now = new Date();
    const timeEl = document.getElementById("clock-time");
    if (timeEl) {
        timeEl.textContent = now.toLocaleTimeString();
    }
}
setInterval(updateClock, 1000);
updateClock();

let map;               // referencia global al mapa
let riderMarker = null; // referencia al marker dinámico del rider

// Función de distancia (Haversine) - útil si luego validamos ubicación
function calcularDistancia(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // radio de la tierra en metros
    const toRad = (deg) => deg * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

document.addEventListener("DOMContentLoaded", () => {
    // Inicializar mapa con punto de control
    if (window.puntoControl && window.puntoControl.latitud && window.puntoControl.longitud && typeof L !== "undefined") {
        map = L.map('map', {
            dragging: false,
            zoomControl: false
        }).setView([window.puntoControl.latitud, window.puntoControl.longitud], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([window.puntoControl.latitud, window.puntoControl.longitud]).addTo(map)
            .bindPopup(window.puntoControl.titulo);

        L.circle([window.puntoControl.latitud, window.puntoControl.longitud], {
            color: 'blue',
            fillColor: '#3f83f8',
            fillOpacity: 0.2,
            radius: window.puntoControl.radio_metros
        }).addTo(map);
    }

    // Habilitar/deshabilitar turnos según hora local
    const now = new Date();
    const currentTime = now.toTimeString().slice(0,5); // HH:MM

    document.querySelectorAll(".turno-card").forEach(card => {
        const horario = card.querySelector("div:nth-child(2) span").textContent;
        const [horaInicio, horaFin] = horario.split(" - ");

        if (currentTime < horaInicio || currentTime > horaFin) {
            card.style.opacity = "0.5";
            card.querySelector("button[type=submit]").disabled = true;
        }
    });

    // Manejo de envío AJAX con Loader
    document.querySelectorAll(".inline-form").forEach(form => {
        form.addEventListener("submit", async e => {
            e.preventDefault();
            const formData = new FormData(form);

            // Buscar radio seleccionado dentro del card
            const radioSeleccionado = form.closest(".turno-card").querySelector('input[name="tipo"]:checked');
            if (!radioSeleccionado) {
                showToast("Debes seleccionar Entrada o Salida antes de marcar.", "warning");
                return; // 👈 cancelamos envío
            }
            formData.set("tipo", radioSeleccionado.value);

            Loader.show(); // 👈 mostrar loader al iniciar

            // Obtener ubicación
            if (navigator.geolocation) {
                await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            formData.set("lat", pos.coords.latitude);
                            formData.set("lon", pos.coords.longitude);

                            // 👇 Log en consola para verificar coordenadas
                            console.log("Ubicación capturada:", pos.coords.latitude, pos.coords.longitude);

                            // 🚀 Agregar/actualizar marcador del rider en el mapa
                            if (map) {
                                if (riderMarker) {
                                    // Si ya existe, actualizamos posición
                                    riderMarker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                                } else {
                                    // Si no existe, lo creamos
                                    riderMarker = L.marker([pos.coords.latitude, pos.coords.longitude], {
                                        icon: L.icon({
                                            iconUrl: "https://maps.gstatic.com/mapfiles/ms2/micons/green-dot.png",
                                            iconSize: [32, 32],
                                            iconAnchor: [16, 32],
                                            popupAnchor: [0, -32]
                                        })
                                    }).addTo(map).bindPopup("Tu ubicación actual").openPopup();
                                }

                                // Centrar el mapa en el rider
                                map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                            }

                            resolve();
                        },
                        err => {
                            console.error(err);
                            showToast("No se pudo obtener tu ubicación. Activa el GPS.", "warning");
                            Loader.hide(); // 👈 ocultar loader en error
                            reject(err);
                        }
                    );
                });
            }

            try {
                // 👇 Log en consola antes de enviar
                console.log("Datos enviados:", Object.fromEntries(formData));

                const response = await fetch(form.action, {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                let data;
                try {
                    data = await response.json();
                } catch (err) {
                    showToast("Error en la respuesta del servidor.", "error");
                    Loader.hide();
                    return;
                }

                if (response.ok) {
                    if (data.success) {
                        showToast(data.message, "success");
                        const tbody = document.querySelector('#tablaMarcaciones tbody');
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${data.data.empresa ?? '-'}</td>
                            <td>${data.data.hora}</td>
                            <td>${data.data.tipo}</td>
                            <td>${data.data.dispositivo}</td>
                        `;
                        tbody.appendChild(row);
                    } else {
                        showToast(data.message, "error");
                    }
                } else {
                    showToast(data.message || "No se pudo registrar la marcación.", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Error de conexión", "error");
            } finally {
                Loader.hide(); // 👈 ocultar loader siempre al terminar
            }
        });
    });

});
