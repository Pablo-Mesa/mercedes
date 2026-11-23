// public/js/notificaciones-audio.js
const NotificadorAudio = (() => {
    let audioCtx = null;

    const inicializar = (alertaId) => {
        const alerta = document.getElementById(alertaId);

        const actualizarEstadoVisual = () => {
            if (!alerta) return;
            const estaListo = audioCtx && audioCtx.state === 'running';
            alerta.style.display = estaListo ? 'none' : 'block';
        };

        const intentarActivar = () => {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().then(actualizarEstadoVisual).catch(() => {});
            } else {
                actualizarEstadoVisual();
            }

            if (audioCtx.state === 'running') {
                document.removeEventListener('click', intentarActivar);
                document.removeEventListener('keydown', intentarActivar);
            }
        };

        // 1. Intentar activar automáticamente apenas abre la vista
        intentarActivar();

        // 2. Si el navegador lo bloqueó por falta de clic, desbloquear con la primera interacción
        document.addEventListener('click', intentarActivar);
        document.addEventListener('keydown', intentarActivar);
    };

    const bip = () => {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }

        const ahora = audioCtx.currentTime;
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ahora);

        gain.gain.setValueAtTime(0.2, ahora);
        gain.gain.exponentialRampToValueAtTime(0.001, ahora + 0.25);

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.start(ahora);
        osc.stop(ahora + 0.25);
    };

    return { inicializar, bip };
})();
