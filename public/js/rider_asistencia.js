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

document.addEventListener("DOMContentLoaded", () => {
    // Inicializar mapa con punto de control
    if (window.puntoControl && window.puntoControl.latitud && window.puntoControl.longitud && typeof L !== "undefined") {
        map = L.map('map').setView([window.puntoControl.latitud, window.puntoControl.longitud], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap', dragging: false
        }).addTo(map);

        // Marker fijo del punto de control
        L.marker([window.puntoControl.latitud, window.puntoControl.longitud]).addTo(map)
            .bindPopup(window.puntoControl.titulo);

        // Círculo alrededor del punto de control
        L.circle([window.puntoControl.latitud, window.puntoControl.longitud], {
            color: 'blue',
            fillColor: '#3f83f8',
            fillOpacity: 0.2,
            radius: 300 // radio en metros
        }).addTo(map);
    }

    const btnComprobar = document.getElementById("btnComprobar");
    const accionesUbicacion = document.getElementById("accionesUbicacion");
    const radioPermitido = 300; // metros

    // Comprobar ubicación
    btnComprobar.addEventListener("click", () => {
        if (!navigator.geolocation) {
            showToast("Tu navegador no soporta geolocalización.", "error");
            return;
        }

        Loader.show();

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                Loader.hide();
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;

                // Guardar en campos ocultos
                document.getElementById("lat").value = lat;
                document.getElementById("lon").value = lon;

                const distancia = calcularDistancia(lat, lon, window.puntoControl.latitud, window.puntoControl.longitud);

                if (distancia <= radioPermitido) {
                    accionesUbicacion.style.display = "block";
                    btnComprobar.style.display = "none";

                    // 🚀 Nuevo marker con la ubicación del rider
                    if (riderMarker) {
                        riderMarker.setLatLng([lat, lon]); // actualizar si ya existe
                    } else {
                        riderMarker = L.marker([lat, lon]).addTo(map)
                            .bindPopup("Tu ubicación actual").openPopup();
                    }

                    map.setView([lat, lon], 15); // centrar en el rider
                } else {
                    showToast('Acércate más a la ubicación deseada.', 'warning');
                }
            },
            (err) => {
                Loader.hide();
                showToast("No se pudo obtener tu ubicación. Activa el GPS.", 'warning');
                console.error(err);
            }
        );
    });

    // Enviar llegada y procesar respuesta JSON
    document.getElementById("asistenciaForm").addEventListener("submit", async (e) => {
        e.preventDefault(); // evitar submit clásico
        Loader.show();

        try {
            const form = e.target;
            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, "success");
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast(data.message || "Error al registrar llegada", "error");
            }
        } catch (err) {
            console.error(err);
            showToast("Error de conexión", "error");
        } finally {
            Loader.hide();
        }
    });
});

// Función de distancia (Haversine)
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
