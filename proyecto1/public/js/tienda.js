/* Interacciones públicas: carrito de invitado, modales y acceso protegido. */
const $ = (selector) => document.querySelector(selector);
const dinero = (valor) => `S/ ${Number(valor).toFixed(2)}`;
// Evita que un nombre guardado en la base se interprete como HTML en el carrito.
const escaparHtml = (texto) => String(texto).replace(/[&<>"']/g, (simbolo) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[simbolo]);

// Solicitud reutilizable para los endpoints que responden JSON.
async function solicitar(ruta, opciones = {}) {
    const respuesta = await fetch(`${window.URL_BASE}${ruta}`, opciones);
    const datos = await respuesta.json();
    if (!respuesta.ok) throw datos;
    return datos;
}

function abrir(capa) { $(`#${capa}`).classList.add('visible'); $('#fondo-modal').classList.add('visible'); }
function cerrar(capa) {
    $(`#${capa}`).classList.remove('visible');
    if (!document.querySelector('.modal-acceso.visible, .panel-carrito.visible')) $('#fondo-modal').classList.remove('visible');
}

// Dibuja el contenido del menú lateral cada vez que cambia el carrito.
function pintarCarrito(carrito) {
    $('#contador-carrito').textContent = carrito.cantidad;
    $('#total-carrito').textContent = dinero(carrito.total);
    $('#contenido-carrito').innerHTML = carrito.items.length ? carrito.items.map((item) => `
        <article class="linea-carrito"><div><strong>${escaparHtml(item.nombre)}</strong><span>${dinero(item.precio)} c/u</span></div>
        <div class="controles-cantidad"><button data-cambiar="${item.id}" data-cantidad="${item.cantidad - 1}">−</button><b>${item.cantidad}</b><button data-cambiar="${item.id}" data-cantidad="${item.cantidad + 1}">+</button><button class="enlace-eliminar" data-quitar="${item.id}">Quitar</button></div><strong>${dinero(item.subtotal)}</strong></article>`).join('') : '<p class="carrito-vacio">Tu carrito está vacío.</p>';
}

async function actualizarResumen() { pintarCarrito((await solicitar('/carrito/resumen')).carrito); }

document.addEventListener('click', async (evento) => {
    const agregar = evento.target.closest('[data-agregar]');
    const cambiar = evento.target.closest('[data-cambiar]');
    const quitar = evento.target.closest('[data-quitar]');
    if (evento.target.closest('#boton-carrito')) return abrir('panel-carrito');
    if (evento.target.closest('[data-cerrar]')) return cerrar(evento.target.closest('[data-cerrar]').dataset.cerrar);
    if (agregar) {
        try { pintarCarrito((await solicitar(`/carrito/agregar/${agregar.dataset.agregar}`, { method: 'POST' })).carrito); abrir('panel-carrito'); }
        catch (error) { alert(error.mensaje); }
    }
    if (cambiar) {
        const datos = new FormData(); datos.append('cantidad', cambiar.dataset.cantidad);
        pintarCarrito((await solicitar(`/carrito/cambiar/${cambiar.dataset.cambiar}`, { method: 'POST', body: datos })).carrito);
    }
    if (quitar) pintarCarrito((await solicitar(`/carrito/quitar/${quitar.dataset.quitar}`, { method: 'POST' })).carrito);
    if (evento.target.matches('[data-mostrar-registro]')) { $('#vista-ingreso').hidden = true; $('#vista-registro').hidden = false; }
    if (evento.target.matches('[data-mostrar-ingreso]')) { $('#vista-ingreso').hidden = false; $('#vista-registro').hidden = true; }
});

$('#boton-comprar').addEventListener('click', async () => {
    try { window.location.href = (await solicitar('/checkout/iniciar', { method: 'POST' })).redirigir; }
    catch (error) { if (error.requiere_acceso) abrir('modal-acceso'); }
});

async function enviarFormulario(formulario, ruta, mensaje) {
    try {
        const datos = await solicitar(ruta, { method: 'POST', body: new FormData(formulario) });
        mensaje.textContent = datos.mensaje || 'Acceso validado.';
        window.location.href = datos.redirigir || `${window.URL_BASE}/checkout/formulario`;
    } catch (error) { mensaje.textContent = error.mensaje || 'No se pudo completar la acción.'; }
}

$('#formulario-ingreso').addEventListener('submit', (e) => { e.preventDefault(); enviarFormulario(e.currentTarget, '/login/autenticar', $('#mensaje-acceso')); });
$('#formulario-registro').addEventListener('submit', (e) => { e.preventDefault(); enviarFormulario(e.currentTarget, '/login/registrar', $('#mensaje-acceso')); });
$('#formulario-administrador').addEventListener('submit', (e) => { e.preventDefault(); enviarFormulario(e.currentTarget, '/login/administrador', $('#mensaje-administrador')); });

// Atajo visual: no reemplaza la validación del código y rol en PHP.
document.addEventListener('keydown', (evento) => { if (evento.ctrlKey && evento.shiftKey && evento.key.toLowerCase() === 'a') abrir('modal-administrador'); });
$('#fondo-modal').addEventListener('click', () => document.querySelectorAll('.visible').forEach((elemento) => elemento.classList.remove('visible')));
actualizarResumen();

// ==========================================================================
// SISTEMA DE FILTRADO Y ORDENAMIENTO EN TIEMPO REAL (SIN RECARGAR PAGINA)
// ==========================================================================
document.addEventListener("DOMContentLoaded", () => {
    const buscadorTexto = document.getElementById("buscador-texto");
    const filtroCategoria = document.getElementById("filtro-categoria");
    const ordenPrecio = document.getElementById("orden-precio");
    const contenedorProductos = document.getElementById("contenedor-productos");
    
    // Obtenemos una lista estática original de todas las tarjetas de productos
    const productosTarjetas = Array.from(contenedorProductos.querySelectorAll(".tarjeta-producto"));

    // Función principal para filtrar por texto y categoría simultáneamente
    function filtrarProductos() {
        const textoBusqueda = buscadorTexto.value.toLowerCase().trim();
        const categoriaSeleccionada = filtroCategoria.value;

        productosTarjetas.forEach(tarjeta => {
            const nombre = tarjeta.getAttribute("data-nombre");
            const categoria = tarjeta.getAttribute("data-categoria");

            // Validaciones combinadas (Buscador + Select)
            const coincideTexto = nombre.includes(textoBusqueda);
            const coincideCategoria = categoriaSeleccionada === "" || categoria === categoriaSeleccionada;

            // Si pasa ambos filtros se muestra, si no, se oculta con CSS dinámico
            if (coincideTexto && coincideCategoria) {
                tarjeta.style.display = ""; 
            } else {
                tarjeta.style.display = "none";
            }
        });
    }

    // Función para reordenar las tarjetas por precio (Menor/Mayor)
    function ordenarProductos() {
        const tipoOrden = ordenPrecio.value;
        
        // Si no hay orden seleccionado, usamos el orden original de la base de datos
        if (tipoOrden === "") {
            productosTarjetas.forEach(tarjeta => contenedorProductos.appendChild(tarjeta));
            return;
        }

        // Clonamos y ordenamos el array basado en el precio guardado en el data-attribute
        const tarjetasOrdenadas = [...productosTarjetas].sort((a, b) => {
            const precioA = parseFloat(a.getAttribute("data-precio"));
            const precioB = parseFloat(b.getAttribute("data-precio"));

            return tipoOrden === "menor-mayor" ? precioA - precioB : precioB - precioA;
        });

        // Reinyectamos los elementos ordenados en el contenedor sin recargar la página
        tarjetasOrdenadas.forEach(tarjeta => contenedorProductos.appendChild(tarjeta));
    }

    // Escuchadores de eventos en tiempo real (Input y Selects)
    buscadorTexto.addEventListener("input", filtrarProductos);
    filtroCategoria.addEventListener("change", filtrarProductos);
    ordenPrecio.addEventListener("change", ordenarProductos);
});
// ==========================================================================
// BOTÓN FLOTANTE DE WHATSAPP COMPLETAMENTE ARRASTRABLE (DRAG & DROP)
// ==========================================================================
document.addEventListener("DOMContentLoaded", () => {
    const el = document.getElementById("whatsapp-flotante");
    if (!el) return;

    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;
    let hasMoved = false;

    // Asegurar que el elemento tenga posición inicial calculada por coordenadas físicas fijas
    const rect = el.getBoundingClientRect();
    el.style.right = 'auto';
    el.style.bottom = 'auto';
    el.style.left = rect.left + 'px';
    el.style.top = rect.top + 'px';

    // Función de inicio de arrastre (Mouse y Touch)
    function startDrag(e) {
        // Ignorar si se hace clic con el botón derecho del mouse
        if (e.button && e.button !== 0) return;

        isDragging = true;
        hasMoved = false;

        const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;

        // Calcular la distancia entre el clic y la esquina superior izquierda del botón
        offsetX = clientX - el.offsetLeft;
        offsetY = clientY - el.offsetTop;

        el.style.cursor = 'grabbing';
    }

    // Función de movimiento continuo
    function doDrag(e) {
        if (!isDragging) return;

        // Prevenir scroll de fondo en móviles y selección de texto en PC
        if (e.cancelable) e.preventDefault();

        const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

        // Nuevas posiciones deseadas
        let newX = clientX - offsetX;
        let newY = clientY - offsetY;

        // Controlar límites para que el botón no se salga de la pantalla
        const maxUnX = window.innerWidth - el.offsetWidth;
        const maxUnY = window.innerHeight - el.offsetHeight;

        newX = Math.max(0, Math.min(newX, maxUnX));
        newY = Math.max(0, Math.min(newY, maxUnY));

        el.style.left = newX + 'px';
        el.style.top = newY + 'px';
        hasMoved = true;
    }

    // Función de liberación del botón
    function stopDrag(e) {
        if (!isDragging) return;
        isDragging = false;
        el.style.cursor = 'pointer';

        // Si se desplazó de su lugar, anulamos la acción del enlace nativo temporalmente
        if (hasMoved) {
            e.preventDefault();
            const stopClick = (event) => {
                event.preventDefault();
                el.removeEventListener('click', stopClick, true);
            };
            el.addEventListener('click', stopClick, true);
        }
    }

    // Eventos para Computadoras (Mouse)
    el.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', doDrag, { passive: false });
    document.addEventListener('mouseup', stopDrag);

    // Eventos para Celulares (Pantallas Táctiles)
    el.addEventListener('touchstart', startDrag, { passive: true });
    document.addEventListener('touchmove', doDrag, { passive: false });
    document.addEventListener('touchend', stopDrag);
});


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
