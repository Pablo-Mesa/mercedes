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
        map = L.map('map', {
            dragging: false,
            zoomControl: false
        }).setView([window.puntoControl.latitud, window.puntoControl.longitud], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'            
        }).addTo(map);

        // Marker fijo del punto de control
        L.marker([window.puntoControl.latitud, window.puntoControl.longitud]).addTo(map)
            .bindPopup(window.puntoControl.titulo);

        // Círculo alrededor del punto de control
        L.circle([window.puntoControl.latitud, window.puntoControl.longitud], {
            color: 'blue',
            fillColor: '#3f83f8',
            fillOpacity: 0.2,
            radius: window.puntoControl.radio_metros // radio en metros
        }).addTo(map);
    }

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

document.addEventListener("DOMContentLoaded", () => {
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

    // Manejo de envío AJAX
    document.querySelectorAll(".inline-form").forEach(form => {
        form.addEventListener("submit", async e => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });
                const data = await response.json();

                if (data.success) {
                    showToast(data.message, "success");
                    const tbody = document.querySelector('#tablaMarcaciones tbody');
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${data.data.hora}</td>
                        <td>${data.data.tipo}</td>
                        <td>${data.data.dispositivo}</td>
                    `;
                    tbody.appendChild(row);
                } else {
                    showToast(data.message, "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Error de conexión", "error");
            }
        });
    });
});
