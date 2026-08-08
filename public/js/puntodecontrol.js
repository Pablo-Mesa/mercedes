let map, marker;

function initMap() {
    // Valores iniciales desde los inputs ocultos
    const lat = parseFloat(document.getElementById("latitud").value) || -25.2637;
    const lng = parseFloat(document.getElementById("longitud").value) || -57.5759;

    const position = { lat: lat, lng: lng };

    // Inicializar mapa
    map = new google.maps.Map(document.getElementById("map"), {
        center: position,
        zoom: 15
    });

    // Crear marcador
    marker = new google.maps.Marker({
        position: position,
        map: map,
        draggable: false // por defecto no se puede mover
    });

    // Evento: actualizar lat/long al mover el marcador
    marker.addListener("dragend", function (event) {
        document.getElementById("latitud").value = event.latLng.lat();
        document.getElementById("longitud").value = event.latLng.lng();
    });
}

// Botón Editar → habilita campos y marcador
document.addEventListener("DOMContentLoaded", function () {
    const btnEditar = document.getElementById("btnEditar");
    const btnGuardar = document.getElementById("btnGuardar");
    const form = document.getElementById("formPuntoControl");

    if (btnEditar) {
        btnEditar.addEventListener("click", function () {
            // Habilitar inputs
            form.querySelectorAll("input[type=text]").forEach(el => el.disabled = false);
            // Habilitar marcador arrastrable
            marker.setDraggable(true);
            // Habilitar botón Guardar
            btnGuardar.disabled = false;
        });
    }

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            // Aquí puedes hacer el fetch POST al backend
            const data = new FormData(form);

            fetch("/mercedes/configuraciones/guardar_punto_control", {
                method: "POST",
                body: data
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    showToast("Punto de control actualizado correctamente.", "success");
                    // Volver a modo lectura
                    form.querySelectorAll("input[type=text]").forEach(el => el.disabled = true);
                    marker.setDraggable(false);
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

    // Inicializar mapa
    initMap();
});
