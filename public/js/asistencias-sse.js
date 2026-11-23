// public/js/asistencias-sse.js
document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.getElementById('tabla-asistencias');
    const tbody = document.getElementById('tbody-asistencias');
    
    if (!tabla || !tbody) return;

    const fechaTabla = tabla.dataset.fecha || '';

     // Obtener fecha de hoy en formato YYYY-MM-DD (hora local)
    const hoyObj = new Date();
    const anio = hoyObj.getFullYear();
    const mes = String(hoyObj.getMonth() + 1).padStart(2, '0');
    const dia = String(hoyObj.getDate()).padStart(2, '0');
    const fechaHoy = `${anio}-${mes}-${dia}`;

    // Si el admin está viendo un historial de otro día, NO abrimos stream SSE
    if (fechaTabla !== fechaHoy) {
        return;
    }

    // Prepara el audio con el primer clic/tecla en la pantalla
    NotificadorAudio.inicializar('alerta-audio-bloqueado');

    const ultimoId = tabla.dataset.ultimoId || 0;
    const fecha = tabla.dataset.fecha || '';
    const streamUrl = `${tabla.dataset.streamUrl}?ultimo_id=${ultimoId}&fecha=${encodeURIComponent(fecha)}`;
    
    const eventSource = new EventSource(streamUrl);

    // Función auxiliar para evitar inyección HTML
    const escaparHtml = (texto) => {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    };

    // Convierte 'YYYY-MM-DD' a 'DD-MM-YYYY' igual que date('d-m-Y') en PHP
    const formatearFecha = (fechaIso) => {
        if (!fechaIso) return '';
        const partes = fechaIso.split('-');
        return partes.length === 3 ? `${partes[2]}-${partes[1]}-${partes[0]}` : fechaIso;
    };

    eventSource.addEventListener('nueva_llegada', (event) => {
        const a = JSON.parse(event.data);
        
        // Evitar duplicados
        if (document.getElementById(`asistencia-${a.id}`)) return;

        // Si estaba el mensaje de "No se encontraron asistencias", eliminarlo
        const filaVacia = document.getElementById('fila-sin-asistencias');
        if (filaVacia) {
            filaVacia.remove();
        }

        // Construir el badge idéntico al de PHP
        let badgeHtml = '<span class="badge bg-secondary">N/D</span>';
        if (a.tipo === 'entrada') {
            badgeHtml = '<span class="badge badge-success">Entrada</span>';
        } else if (a.tipo === 'salida') {
            badgeHtml = '<span class="badge badge-info">Salida</span>';
        }

        const tr = document.createElement('tr');
        tr.id = `asistencia-${a.id}`;
        tr.classList.add('fila-nueva');
        tr.innerHTML = `
            <td>${escaparHtml(formatearFecha(a.fecha))}</td>
            <td>${escaparHtml(a.rider_name)}</td>
            <td>
                ${badgeHtml}
                <small># ${escaparHtml(a.dispositivo)}</small>
                <br>
                ${escaparHtml(a.hora)}
            </td>
            <td>${escaparHtml(a.observaciones)}</td>
        `;

        tbody.prepend(tr);
        // Disparar el bip justo cuando se actualiza la tabla
        NotificadorAudio.bip();
    });

    window.addEventListener('beforeunload', () => {
        eventSource.close();
    });
});
