const Loader = {
    _element: null,
    _startTime: 0,

    // 1. Método para fabricar el HTML dinámicamente si no existe
    _createHTMLElement() {
        const html = `
            <div class="loader">
                <div class="bar1"></div><div class="bar2"></div><div class="bar3"></div>
                <div class="bar4"></div><div class="bar5"></div><div class="bar6"></div>
                <div class="bar7"></div><div class="bar8"></div><div class="bar9"></div>
                <div class="bar10"></div><div class="bar11"></div><div class="bar12"></div>
            </div>`;
        
        const container = document.createElement('div');
        container.id = 'loader';
        container.className = 'element-hidden';
        container.innerHTML = html;
        
        // Lo inyectamos directo al final del body de la página actual
        document.body.appendChild(container);
        return container;
    },

    _getValidElement() {
        if (!this._element) {
            // Intenta buscarlo, si no existe en el HTML actual (como en login.php), lo crea
            this._element = document.querySelector('#loader') || this._createHTMLElement();
        }
        return this._element;
    },

    show() {
        const el = this._getValidElement();
        if (el) {
            this._startTime = Date.now();
            el.classList.remove('element-hidden');
        }
    },

    hide(minDuration = 600) {
        const el = this._getValidElement();
        if (!el) return;

        const timeElapsed = Date.now() - this._startTime;
        const remainingTime = minDuration - timeElapsed;

        if (remainingTime > 0) {
            setTimeout(() => el.classList.add('element-hidden'), remainingTime);
        } else {
            el.classList.add('element-hidden');
        }
    }
};
