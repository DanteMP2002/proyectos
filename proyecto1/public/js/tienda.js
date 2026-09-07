/**
 * tienda.js — Vínculo Bodas
 * ============================================================
 * Maneja toda la interactividad de la página principal:
 *   - Carrito de compras (abrir, cerrar, añadir, quitar, cambiar cantidad)
 *   - Modales de login y registro (abrir/cerrar)
 *   - Modal de administrador (atajo Ctrl+Shift+A)
 *   - Filtros de productos (por texto, categoría y precio)
 *   - Botón flotante de WhatsApp (arrastrable)
 *   - Botón de "Iniciar sesión" en el header
 *
 * DEPENDENCIAS:
 *   - modal.js  (debe cargarse antes que este archivo)
 *   - La variable global window.URL_BASE definida en el PHP
 * ============================================================
 */
 
 
// ─── UTILIDADES GENERALES ─────────────────────────────────────────────────
 
/**
 * Atajo para document.querySelector (como el $ de jQuery, pero nativo).
 * Ejemplo: $('#boton-carrito') en vez de document.querySelector('#boton-carrito')
 */

/* Interacciones públicas: carrito de invitado, modales y acceso protegido. */
const $ = (selector) => document.querySelector(selector);
/**
 * Formatea un número como precio en soles peruanos.
 * Ejemplo: dinero(12.5) → "S/ 12.50"
 */
const dinero = (valor) => `S/ ${Number(valor).toFixed(2)}`;
/**
 * Escapa caracteres especiales de HTML para evitar inyección de código.
 * IMPORTANTE: nunca pongas texto del usuario directo en innerHTML sin escapar.
 * Ejemplo: escaparHtml('<script>') → '&lt;script&gt;'
 */
const escaparHtml = (texto) =>
    String(texto).replace(/[&<>"']/g, (simbolo) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;',
        '"': '&quot;', "'": '&#039;'
    })[simbolo]);
 
// ─── COMUNICACIÓN CON EL SERVIDOR (AJAX / FETCH) ──────────────────────────
 
/**
 * Función reutilizable para hacer peticiones al servidor y recibir JSON.
 * Es async porque espera la respuesta antes de continuar (no bloquea la UI).
 *
 * @param {string} ruta    - La ruta relativa, ej: '/carrito/agregar/3'
 * @param {object} opciones - Opciones de fetch (method, body, etc.)
 * @returns {Promise<object>} - Los datos JSON que devuelve el servidor
 * @throws {object} - Si el servidor devuelve error (status ≥ 400), lanza el JSON
 */
async function solicitar(ruta, opciones = {}) {
    const respuesta = await fetch(`${window.URL_BASE}${ruta}`, opciones);
    const datos = await respuesta.json();
    // Si el servidor respondió con error HTTP, lo lanzamos como excepción
    if (!respuesta.ok) throw datos;
    return datos;
}
// ─── CONTROL DE CAPAS Y MODALES DE ACCESO ─────────────────────────────────
 
/**
 * Abre una capa (panel o modal) añadiendo la clase CSS "visible".
 * También muestra el fondo oscuro semitransparente.
 * @ param {string} idCapa - El id del elemento HTML, sin el #
 */
function abrir(idCapa) {
    $(`#${idCapa}`).classList.add('visible');
    $('#fondo-modal').classList.add('visible');
}
/**
 * Cierra una capa (panel o modal) quitando la clase CSS "visible".
 * Si no quedan otras capas abiertas, oculta el fondo oscuro también.
 * @ param {string} idCapa - El id del elemento HTML, sin el #
 */
function cerrar(idCapa) {
    $(`#${idCapa}`).classList.remove('visible');
    // Solo ocultamos el fondo si no hay otras capas abiertas
    if (!document.querySelector('.modal-acceso.visible, .panel-carrito.visible')) {
        $('#fondo-modal').classList.remove('visible');
    }
}

// ─── RENDERIZADO DEL CARRITO ───────────────────────────────────────────────
 
/**
 * Dibuja el contenido del panel lateral del carrito.
 * Se llama cada vez que el carrito cambia (al agregar, quitar o cambiar cantidad).
 *
 * @param {object} carrito - Objeto con { items: [], total: 0, cantidad: 0 }
 *                           que devuelve el servidor
 */
function pintarCarrito(carrito) {
    // Actualizar el contador de ítems en el botón del header
    $('#contador-carrito').textContent = carrito.cantidad;
 
    // Actualizar el total visible en el pie del panel
    $('#total-carrito').textContent = dinero(carrito.total);
 
    // Reconstruir la lista de productos en el panel
    $('#contenido-carrito').innerHTML = carrito.items.length
        ? carrito.items.map((item) => `
            <article class="linea-carrito">
                <div>
                    <strong>${escaparHtml(item.nombre)}</strong>
                    <span>${dinero(item.precio)} c/u</span>
                </div>
                <div class="controles-cantidad">
                    <!-- data-cambiar y data-cantidad son leídos por el listener de clicks abajo -->
                    <button data-cambiar="${item.id}" data-cantidad="${item.cantidad - 1}">−</button>
                    <b>${item.cantidad}</b>
                    <button data-cambiar="${item.id}" data-cantidad="${item.cantidad + 1}">+</button>
                    <button class="enlace-eliminar" data-quitar="${item.id}">Quitar</button>
                </div>
                <strong>${dinero(item.subtotal)}</strong>
            </article>`)
            .join('')
        : '<p class="carrito-vacio">Tu carrito está vacío.</p>';
}
/**
 * Pide al servidor el estado actual del carrito y lo dibuja.
 * Se llama al cargar la página para restaurar el carrito de sesión.
 */
async function actualizarResumen() {
    pintarCarrito((await solicitar('/carrito/resumen')).carrito);
}

// ─── LISTENER PRINCIPAL DE CLICKS ─────────────────────────────────────────
/**
 * En vez de poner un event listener en cada botón individualmente,
 * escuchamos TODOS los clicks en el documento y preguntamos si
 * el elemento clickeado (o su padre) es el que nos interesa.
 * Esto se llama "event delegation" y es más eficiente.
 */
document.addEventListener('click', async (evento) => {
    // Buscamos si el elemento clickeado o algún ancestro coincide con cada caso
    const agregar = evento.target.closest('[data-agregar]');
    const cambiar  = evento.target.closest('[data-cambiar]');
    const quitar   = evento.target.closest('[data-quitar]');
 
    // — Abrir el panel del carrito —
    if (evento.target.closest('#boton-carrito')) {
        return abrir('panel-carrito');
    }
 
    // — Cerrar cualquier capa (el atributo data-cerrar lleva el id a cerrar) —
    if (evento.target.closest('[data-cerrar]')) {
        return cerrar(evento.target.closest('[data-cerrar]').dataset.cerrar);
    }
 
    // — Añadir un producto al carrito —
    if (agregar) {
        try {
            const datos = await solicitar(
                `/carrito/agregar/${agregar.dataset.agregar}`,
                { method: 'POST' }
            );
            pintarCarrito(datos.carrito);
            abrir('panel-carrito'); // abrimos el panel para que el usuario vea lo que añadió
        } catch (error) {
            alert(error.mensaje); // mostramos el mensaje de error del servidor
        }
        return;
    }
 
    // — Cambiar la cantidad de un producto en el carrito —
    if (cambiar) {
        const formData = new FormData();
        formData.append('cantidad', cambiar.dataset.cantidad);
        const datos = await solicitar(
            `/carrito/cambiar/${cambiar.dataset.cambiar}`,
            { method: 'POST', body: formData }
        );
        pintarCarrito(datos.carrito);
        return;
    }
 
    // — Quitar un producto del carrito —
    if (quitar) {
        const datos = await solicitar(
            `/carrito/quitar/${quitar.dataset.quitar}`,
            { method: 'POST' }
        );
        pintarCarrito(datos.carrito);
        return;
    }
 
    // — Cambiar de "login" a "registro" dentro del modal de acceso —
    if (evento.target.matches('[data-mostrar-registro]')) {
        $('#vista-ingreso').hidden = true;
        $('#vista-registro').hidden = false;
    }
 
    // — Cambiar de "registro" a "login" dentro del modal de acceso —
    if (evento.target.matches('[data-mostrar-ingreso]')) {
        $('#vista-ingreso').hidden = false;
        $('#vista-registro').hidden = true;
    }
});

// ─── BOTÓN "CONTINUAR CON LA COMPRA" ──────────────────────────────────────
/**
 * Al hacer clic, le pregunta al servidor si el usuario está logueado.
 * - Si SÍ está logueado → redirige al formulario de checkout
 * - Si NO está logueado → abre el modal de acceso (login/registro)
 */
$('#boton-comprar').addEventListener('click', async () => {
    try {
        const datos = await solicitar('/checkout/iniciar', { method: 'POST' });
        window.location.href = datos.redirigir; // redirige al checkout
    } catch (error) {
        // El servidor devuelve requiere_acceso: true si no hay sesión
        if (error.requiere_acceso) abrir('modal-acceso');
    }
});
// ─── ENVÍO DE FORMULARIOS DE ACCESO ───────────────────────────────────────
 
/**
 * Envía un formulario al servidor de forma asíncrona (sin recargar la página)
 * y muestra el mensaje de respuesta o redirige si el servidor lo indica.
 *
 * @param {HTMLFormElement} formulario - El formulario que se envía
 * @param {string} ruta               - La ruta del endpoint PHP
 * @param {HTMLElement} mensajeEl     - Elemento donde mostrar mensajes de error/éxito
 */
async function enviarFormulario(formulario, ruta, mensajeEl) {
    try {
        const datos = await solicitar(ruta, {
            method: 'POST',
            body: new FormData(formulario) // envía los campos del formulario
        });
        mensajeEl.textContent = datos.mensaje || 'Acceso validado.';
        // Si el servidor indica una URL de redirección, vamos ahí
        window.location.href = datos.redirigir || `${window.URL_BASE}/checkout/formulario`;
    } catch (error) {
        mensajeEl.textContent = error.mensaje || 'No se pudo completar la acción.';
    }
}

// Conectar los 3 formularios de acceso con su endpoint correspondiente
$('#formulario-ingreso').addEventListener('submit', (e) => {
    e.preventDefault(); // evitar el envío tradicional (con recarga)
    enviarFormulario(e.currentTarget, '/login/autenticar', $('#mensaje-acceso'));
});
 
$('#formulario-registro').addEventListener('submit', (e) => {
    e.preventDefault();
    enviarFormulario(e.currentTarget, '/login/registrar', $('#mensaje-acceso'));
});
 
$('#formulario-administrador').addEventListener('submit', (e) => {
    e.preventDefault();
    enviarFormulario(e.currentTarget, '/login/administrador', $('#mensaje-administrador'));
});
// ─── ATAJO DE TECLADO PARA ADMINISTRADOR ──────────────────────────────────
/**
 * Ctrl + Shift + A abre el modal de login de administrador.
 * NOTA: esto es solo un atajo de conveniencia para el desarrollo.
 * La seguridad real está en el servidor (PHP comprueba el rol).
 */
document.addEventListener('keydown', (evento) => {
    if (evento.ctrlKey && evento.shiftKey && evento.key.toLowerCase() === 'a') {
        abrir('modal-administrador');
    }
});
 
// Cerrar cualquier capa visible al hacer clic en el fondo oscuro
$('#fondo-modal').addEventListener('click', () => {
    document.querySelectorAll('.visible').forEach((el) => el.classList.remove('visible'));
});

// ─── BOTÓN "INICIAR SESIÓN" EN EL HEADER ──────────────────────────────────
/**
 * El botón del header que abre el modal de login.
 * Solo existe si el usuario NO está logueado (PHP no lo renderiza si hay sesión).
 */
document.addEventListener('DOMContentLoaded', () => {
    const botonLoginNav = document.getElementById('enlace-login-nav');
    if (botonLoginNav) {
        botonLoginNav.addEventListener('click', (e) => {
            e.preventDefault();
            abrir('modal-acceso');
        });
    }
});

//actualizarResumen();

// ─── FILTROS EN TIEMPO REAL ────────────────────────────────────────────────
/**
 * Permite buscar productos por nombre, filtrar por categoría y ordenar por precio
 * sin recargar la página. Todo ocurre en el navegador con los datos ya cargados.
 *
 * Los datos de cada producto están guardados como atributos data-* en cada
 * <article class="tarjeta-producto">, así que no necesitamos ir al servidor.
 */
document.addEventListener('DOMContentLoaded', () => {
    const buscadorTexto     = document.getElementById('buscador-texto');
    const filtroCategoria   = document.getElementById('filtro-categoria');
    const ordenPrecio       = document.getElementById('orden-precio');
    const contenedor        = document.getElementById('contenedor-productos');
 
    // Guardamos la lista original de tarjetas (en el orden que vino de PHP)
    const todasLasTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-producto'));
 
    /**
     * Oculta o muestra cada tarjeta según los filtros activos.
     * Se llama cada vez que el usuario escribe en el buscador o cambia el select.
     */
    function filtrarProductos() {
        const textoBusqueda       = buscadorTexto.value.toLowerCase().trim();
        const categoriaSeleccionada = filtroCategoria.value;
 
        todasLasTarjetas.forEach((tarjeta) => {
            const nombre    = tarjeta.dataset.nombre    || ''; // ya viene en minúsculas desde PHP
            const categoria = tarjeta.dataset.categoria || '';
 
            const coincideTexto     = nombre.includes(textoBusqueda);
            const coincideCategoria = categoriaSeleccionada === '' || categoria === categoriaSeleccionada;
 
            // Mostrar u ocultar con CSS (display:'') significa "volver al valor por defecto"
            tarjeta.style.display = (coincideTexto && coincideCategoria) ? '' : 'none';
        });
    }
 
    /**
     * Reordena las tarjetas en el DOM según el precio.
     * Si no hay orden seleccionado, restaura el orden original de la BD.
     */
    function ordenarProductos() {
        const tipoOrden = ordenPrecio.value;
 
        if (tipoOrden === '') {
            // Restaurar orden original: reaños en el mismo orden que estaban
            todasLasTarjetas.forEach((tarjeta) => contenedor.appendChild(tarjeta));
            return;
        }
 
        // Ordenar una copia del array (no el original) por precio
        const tarjetasOrdenadas = [...todasLasTarjetas].sort((a, b) => {
            const precioA = parseFloat(a.dataset.precio);
            const precioB = parseFloat(b.dataset.precio);
            return tipoOrden === 'menor-mayor' ? precioA - precioB : precioB - precioA;
        });
 
        // Re-insertar las tarjetas en el nuevo orden (appendChild mueve, no duplica)
        tarjetasOrdenadas.forEach((tarjeta) => contenedor.appendChild(tarjeta));
    }
 
    // Escuchar cambios en tiempo real en los controles de filtrado
    buscadorTexto.addEventListener('input', filtrarProductos);
    filtroCategoria.addEventListener('change', filtrarProductos);
    ordenPrecio.addEventListener('change', ordenarProductos);
});
// ─── FILTROS EN TIEMPO REAL ────────────────────────────────────────────────
/**
 * Permite buscar productos por nombre, filtrar por categoría y ordenar por precio
 * sin recargar la página. Todo ocurre en el navegador con los datos ya cargados.
 *
 * Los datos de cada producto están guardados como atributos data-* en cada
 * <article class="tarjeta-producto">, así que no necesitamos ir al servidor.
 */
document.addEventListener('DOMContentLoaded', () => {
    const buscadorTexto     = document.getElementById('buscador-texto');
    const filtroCategoria   = document.getElementById('filtro-categoria');
    const ordenPrecio       = document.getElementById('orden-precio');
    const contenedor        = document.getElementById('contenedor-productos');
 
    // Guardamos la lista original de tarjetas (en el orden que vino de PHP)
    const todasLasTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-producto'));
 
    /**
     * Oculta o muestra cada tarjeta según los filtros activos.
     * Se llama cada vez que el usuario escribe en el buscador o cambia el select.
     */
    function filtrarProductos() {
        const textoBusqueda       = buscadorTexto.value.toLowerCase().trim();
        const categoriaSeleccionada = filtroCategoria.value;
 
        todasLasTarjetas.forEach((tarjeta) => {
            const nombre    = tarjeta.dataset.nombre    || ''; // ya viene en minúsculas desde PHP
            const categoria = tarjeta.dataset.categoria || '';
 
            const coincideTexto     = nombre.includes(textoBusqueda);
            const coincideCategoria = categoriaSeleccionada === '' || categoria === categoriaSeleccionada;
 
            // Mostrar u ocultar con CSS (display:'') significa "volver al valor por defecto"
            tarjeta.style.display = (coincideTexto && coincideCategoria) ? '' : 'none';
        });
    }
 
    /**
     * Reordena las tarjetas en el DOM según el precio.
     * Si no hay orden seleccionado, restaura el orden original de la BD.
     */
    function ordenarProductos() {
        const tipoOrden = ordenPrecio.value;
 
        if (tipoOrden === '') {
            // Restaurar orden original: reaños en el mismo orden que estaban
            todasLasTarjetas.forEach((tarjeta) => contenedor.appendChild(tarjeta));
            return;
        }
 
        // Ordenar una copia del array (no el original) por precio
        const tarjetasOrdenadas = [...todasLasTarjetas].sort((a, b) => {
            const precioA = parseFloat(a.dataset.precio);
            const precioB = parseFloat(b.dataset.precio);
            return tipoOrden === 'menor-mayor' ? precioA - precioB : precioB - precioA;
        });
 
        // Re-insertar las tarjetas en el nuevo orden (appendChild mueve, no duplica)
        tarjetasOrdenadas.forEach((tarjeta) => contenedor.appendChild(tarjeta));
    }
 
    // Escuchar cambios en tiempo real en los controles de filtrado
    buscadorTexto.addEventListener('input', filtrarProductos);
    filtroCategoria.addEventListener('change', filtrarProductos);
    ordenPrecio.addEventListener('change', ordenarProductos);
});

// ─── INICIALIZACIÓN ────────────────────────────────────────────────────────
// Al cargar la página, pedimos el carrito de sesión actual al servidor
// para mostrar el contador y los ítems si ya había algo guardado.
actualizarResumen();

// Enlazador opcional para abrir tu modal de login existente al pulsar el botón del header
document.addEventListener("DOMContentLoaded", () => {
    const botonLoginNav = document.getElementById("enlace-login-nav");
    const modalAcceso = document.getElementById("modal-acceso");
    const fondoModal = document.getElementById("fondo-modal");

    if (botonLoginNav && modalAcceso && fondoModal) {
        botonLoginNav.addEventListener("click", (e) => {
            e.preventDefault();
            modalAcceso.classList.add("visible");
            fondoModal.classList.add("visible");
            modalAcceso.setAttribute("aria-hidden", "false");
        });
    }
});

//MODAL REUTILIZABLE
function abrirModal(element) {
    document.getElementById("modal-nombre").innerText = element.dataset.nombre;
    document.getElementById("modal-descripcion").innerText = element.dataset.descripcion;
    document.getElementById("modal-precio").innerText = element.dataset.precio;
    document.getElementById("modal-whatsapp").href = element.dataset.whatsapp;

    document.getElementById("modal-producto").style.display = "flex";
}

function cerrarModal() {
    document.getElementById("modal-producto").style.display = "none";
}

