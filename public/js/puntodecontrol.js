let map, marker, circle;

function initMap() {
    const lat = parseFloat(document.getElementById("latitud").value) || -25.2637;
    const lng = parseFloat(document.getElementById("longitud").value) || -57.5759;
    const radio = parseInt(document.getElementById("radio").value) || 300;

    // Inicializar mapa
    map = L.map('map').setView([lat, lng], 15);

    // Tiles de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Crear marcador (no arrastrable por defecto)
    marker = L.marker([lat, lng], { draggable: false }).addTo(map);

    // Crear círculo alrededor del marcador
    circle = L.circle([lat, lng], {
        color: 'blue',
        fillColor: '#3f83f8',
        fillOpacity: 0.2,
        radius: radio
    }).addTo(map);

    // Evento: actualizar lat/long al mover el marcador
    marker.on("dragend", function () {
        const pos = marker.getLatLng();
        document.getElementById("latitud").value = pos.lat;
        document.getElementById("longitud").value = pos.lng;
        circle.setLatLng(pos); // mover círculo junto al marcador
    });

    // Evento: actualizar radio dinámicamente
    const radioSelect = document.getElementById("radio");
    if (radioSelect) {
        radioSelect.addEventListener("change", function () {
            circle.setRadius(parseInt(this.value));
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const btnEditar = document.getElementById("btnEditar");
    const btnGuardar = document.getElementById("btnGuardar");
    const form = document.getElementById("formPuntoControl");

    if (btnEditar) {
        btnEditar.addEventListener("click", function () {
            form.querySelectorAll("input[type=text], select").forEach(el => el.disabled = false);
            marker.dragging.enable(); // habilitar arrastre
            btnGuardar.disabled = false;
        });
    }

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const data = new FormData(form);

            fetch("/mercedes/configuraciones/guardar_punto_control", {
                method: "POST",
                body: data
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    showToast("Punto de control actualizado correctamente.", "success");
                    form.querySelectorAll("input[type=text], select").forEach(el => el.disabled = true);
                    marker.dragging.disable(); // volver a modo lectura
                    btnGuardar.disabled = true;
                } else {
                    alert("Error al guardar: " + response.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error inesperado al guardar.");
            });
        });
    }

    initMap();
});
