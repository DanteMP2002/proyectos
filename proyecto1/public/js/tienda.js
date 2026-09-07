/**
 * Interacciones de la tienda: carrito y formularios de acceso.
 * Cada función conserva una tarea concreta para facilitar futuras modificaciones.
 */
const buscarElemento = (selector) => document.querySelector(selector);

function escaparHtml(texto) {
    return String(texto).replace(/[&<>"']/g, (caracter) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[caracter]);
}

function formatearDinero(valor) {
    return `S/ ${Number(valor).toFixed(2)}`;
}

// Centraliza las peticiones JSON para no repetir el manejo de errores.
async function solicitar(ruta, opciones = {}) {
    const respuesta = await fetch(`${window.URL_BASE}${ruta}`, opciones);
    const datos = await respuesta.json();

    if (!respuesta.ok) {
        throw datos;
    }

    return datos;
}

function abrirCapa(idCapa) {
    buscarElemento(`#${idCapa}`)?.classList.add('visible');
    buscarElemento('#fondo-modal')?.classList.add('visible');
}

function cerrarCapa(idCapa) {
    buscarElemento(`#${idCapa}`)?.classList.remove('visible');

    if (!document.querySelector('.modal-acceso.visible, .panel-carrito.visible, #modal-producto.visible')) {
        buscarElemento('#fondo-modal')?.classList.remove('visible');
    }
}

// Dibuja el carrito usando únicamente datos recibidos del servidor.
function pintarCarrito(carrito) {
    const contador = buscarElemento('#contador-carrito');
    const total = buscarElemento('#total-carrito');
    const contenido = buscarElemento('#contenido-carrito');

    if (!contador || !total || !contenido) return;

    contador.textContent = carrito.cantidad;
    total.textContent = formatearDinero(carrito.total);
    contenido.innerHTML = carrito.items.length
        ? carrito.items.map((item) => `
            <article class="linea-carrito">
                <div><strong>${escaparHtml(item.nombre)}</strong><span>${formatearDinero(item.precio)} c/u</span></div>
                <div class="controles-cantidad">
                    <button type="button" data-cambiar="${item.id}" data-cantidad="${item.cantidad - 1}" aria-label="Reducir cantidad">−</button>
                    <b>${item.cantidad}</b>
                    <button type="button" data-cambiar="${item.id}" data-cantidad="${item.cantidad + 1}" aria-label="Aumentar cantidad">+</button>
                    <button type="button" class="enlace-eliminar" data-quitar="${item.id}">Quitar</button>
                </div>
                <strong>${formatearDinero(item.subtotal)}</strong>
            </article>`).join('')
        : '<p class="carrito-vacio">Tu carrito está vacío.</p>';
}

async function actualizarResumenCarrito() {
    try {
        pintarCarrito((await solicitar('/carrito/resumen')).carrito);
    } catch (error) {
        console.error('No se pudo cargar el carrito.', error);
    }
}

async function enviarFormulario(formulario, ruta, mensaje) {
    try {
        const datos = await solicitar(ruta, { method: 'POST', body: new FormData(formulario) });
        window.location.href = datos.redirigir || `${window.URL_BASE}/checkout/formulario`;
    } catch (error) {
        mensaje.textContent = error.mensaje || 'No se pudo completar la acción.';
    }
}

document.addEventListener('click', async (evento) => {
    const botonCerrar = evento.target.closest('[data-cerrar]');
    const botonAgregar = evento.target.closest('[data-agregar]');
    const botonCambiar = evento.target.closest('[data-cambiar]');
    const botonQuitar = evento.target.closest('[data-quitar]');

    if (evento.target.closest('#boton-carrito')) return abrirCapa('panel-carrito');
    if (botonCerrar) return cerrarCapa(botonCerrar.dataset.cerrar);

    // El enlace del encabezado abre el modal en la vista de inicio de sesión.
    if (evento.target.closest('#enlace-login-nav')) {
        buscarElemento('#vista-ingreso').hidden = false;
        buscarElemento('#vista-registro').hidden = true;
        return abrirCapa('modal-acceso');
    }

    if (evento.target.closest('[data-mostrar-registro]')) {
        buscarElemento('#vista-ingreso').hidden = true;
        buscarElemento('#vista-registro').hidden = false;
        return;
    }

    if (evento.target.closest('[data-mostrar-ingreso]')) {
        buscarElemento('#vista-ingreso').hidden = false;
        buscarElemento('#vista-registro').hidden = true;
        return;
    }

    try {
        if (botonAgregar) {
            const datos = await solicitar(`/carrito/agregar/${botonAgregar.dataset.agregar}`, { method: 'POST' });
            pintarCarrito(datos.carrito);
            abrirCapa('panel-carrito');
        }

        if (botonCambiar) {
            const datosFormulario = new FormData();
            datosFormulario.append('cantidad', botonCambiar.dataset.cantidad);
            const datos = await solicitar(`/carrito/cambiar/${botonCambiar.dataset.cambiar}`, { method: 'POST', body: datosFormulario });
            pintarCarrito(datos.carrito);
        }

        if (botonQuitar) {
            const datos = await solicitar(`/carrito/quitar/${botonQuitar.dataset.quitar}`, { method: 'POST' });
            pintarCarrito(datos.carrito);
        }
    } catch (error) {
        alert(error.mensaje || 'No se pudo actualizar el carrito.');
    }
});

buscarElemento('#boton-comprar')?.addEventListener('click', async () => {
    try {
        const datos = await solicitar('/checkout/iniciar', { method: 'POST' });
        window.location.href = datos.redirigir;
    } catch (error) {
        if (error.requiere_acceso) abrirCapa('modal-acceso');
    }
});

buscarElemento('#formulario-ingreso')?.addEventListener('submit', (evento) => {
    evento.preventDefault();
    enviarFormulario(evento.currentTarget, '/login/autenticar', buscarElemento('#mensaje-acceso'));
});

buscarElemento('#formulario-registro')?.addEventListener('submit', (evento) => {
    evento.preventDefault();
    enviarFormulario(evento.currentTarget, '/login/registrar', buscarElemento('#mensaje-acceso'));
});

buscarElemento('#formulario-administrador')?.addEventListener('submit', (evento) => {
    evento.preventDefault();
    enviarFormulario(evento.currentTarget, '/login/administrador', buscarElemento('#mensaje-administrador'));
});

document.addEventListener('keydown', (evento) => {
    if (evento.ctrlKey && evento.shiftKey && evento.key.toLowerCase() === 'a') abrirCapa('modal-administrador');
    if (evento.key === 'Escape') document.querySelectorAll('.modal-acceso.visible, .panel-carrito.visible').forEach((capa) => cerrarCapa(capa.id));
});

buscarElemento('#fondo-modal')?.addEventListener('click', () => {
    document.querySelectorAll('.modal-acceso.visible, .panel-carrito.visible').forEach((capa) => cerrarCapa(capa.id));
});

actualizarResumenCarrito();
