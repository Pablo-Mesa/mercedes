// public/js/components/ModalConfirmacion.js

export class ModalConfirmacion extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }

    // Escuchamos todos los atributos configurables + el estado de visibilidad
    static get observedAttributes() {
        return ['abierto', 'titulo', 'mensaje', 'texto-confirmar', 'texto-cancelar'];
    }

    // Si cualquier atributo cambia en caliente desde JavaScript, el componente se actualiza solo
    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'abierto') {
            this.actualizarVisibilidad();
        } else if (this.shadowRoot.innerHTML !== '') {
            // Si el componente ya se renderizó, actualizamos el nodo correspondiente
            this.renderizarAtributos();
        }
    }

    connectedCallback() {
        // Estructura base y estilos aislados
        this.shadowRoot.innerHTML = `
            <style>
                .backdrop {
                    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                    background: rgba(0, 0, 0, 0.6); display: none;
                    justify-content: center; align-items: center; z-index: 2000;
                    backdrop-filter: blur(2px);
                }
                .modal {
                    background: white; padding: 24px; border-radius: 8px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-width: 450px; width: 90%;
                    font-family: system-ui, -apple-system, sans-serif;
                }
                .titulo { margin-top: 0; color: #1a1a1a; font-size: 1.35rem; }
                .mensaje { margin: 15px 0 25px 0; color: #4a4a4a; line-height: 1.5; font-size: 0.95rem; }
                .acciones { display: flex; justify-content: flex-end; gap: 12px; }
                button { 
                    padding: 10px 18px; border: none; border-radius: 6px; 
                    cursor: pointer; font-weight: 600; font-size: 0.9rem;
                    transition: background 0.2s;
                }
                .btn-cancelar { background: #f3f4f6; color: #4b5563; }
                .btn-cancelar:hover { background: #e5e7eb; }
                .btn-confirmar { background: #2563eb; color: white; }
                .btn-confirmar:hover { background: #1d4ed8; }
            </style>

            <div class="backdrop">
                <div class="modal">
                    <h3 class="titulo"></h3>
                    <p class="mensaje"></p>
                    <div class="acciones">
                        <button class="btn-cancelar"></button>
                        <button class="btn-confirmar"></button>
                    </div>
                </div>
            </div>
        `;

        // Vinculamos los eventos a los botones
        this.shadowRoot.querySelector('.btn-cancelar').addEventListener('click', () => this.cerrar(false));
        this.shadowRoot.querySelector('.btn-confirmar').addEventListener('click', () => this.cerrar(true));
        
        // Poblamos los textos por primera vez y revisamos si debe estar abierto
        this.renderizarAtributos();
        this.actualizarVisibilidad();
    }

    // Método centralizado para pintar los textos basados en atributos o valores por defecto
    renderizarAtributos() {
        const titulo = this.getAttribute('titulo') || '¿Confirmar acción?';
        const mensaje = this.getAttribute('mensaje') || '¿Estás seguro de que deseas continuar con esta operación?';
        const txtConfirmar = this.getAttribute('texto-confirmar') || 'Aceptar';
        const txtCancelar = this.getAttribute('texto-cancelar') || 'Cancelar';

        this.shadowRoot.querySelector('.titulo').textContent = titulo;
        this.shadowRoot.querySelector('.mensaje').textContent = mensaje;
        this.shadowRoot.querySelector('.btn-cancelar').textContent = txtCancelar;
        this.shadowRoot.querySelector('.btn-confirmar').textContent = txtConfirmar;
    }

    actualizarVisibilidad() {
        const fondo = this.shadowRoot.querySelector('.backdrop');
        if (!fondo) return;
        fondo.style.display = this.hasAttribute('abierto') ? 'flex' : 'none';
    }

    cerrar(confirmado) {
        this.removeAttribute('abierto');
        
        // Despachamos el evento genérico
        this.dispatchEvent(new CustomEvent('respuesta-modal', {
            detail: { confirmado: confirmado },
            bubbles: true,
            composed: true
        }));
    }
}

customElements.define('modal-confirmacion', ModalConfirmacion);