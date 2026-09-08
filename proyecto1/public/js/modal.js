/**
 * modal.js — Vínculo Bodas
 * ============================================================
 * Maneja la apertura y cierre del modal de detalle de producto.
 *
 * CÓMO FUNCIONA:
 * 1. Cada tarjeta de producto en inicio.php tiene atributos data-*
 *    (data-nombre, data-descripcion, data-precio, etc.)
 * 2. Al hacer clic en una tarjeta, abrirModal() lee esos datos
 *    y los pone dentro del HTML del modal.
 * 3. El modal se muestra añadiendo la clase CSS "activo".
 *
 * DEPENDENCIAS:
 * - modal.css  (los estilos del modal)
 * - El HTML de inicio.php debe tener un elemento con id="modal-producto"
 * ============================================================
 */
document.addEventListener('DOMContentLoaded', () => {
    // ─── 1. REFERENCIAS AL DOM ─────────────────────────────────────────────────
// Guardamos referencias a los elementos del modal para no buscarlos
// cada vez que se abre/cierra (es más eficiente).
    const modalOverlay     = document.getElementById('modal-producto');
    const modalNombre      = document.getElementById('modal-nombre');
    const modalDescripcion = document.getElementById('modal-descripcion');
    const modalPrecio      = document.getElementById('modal-precio');
    const modalWhatsapp    = document.getElementById('modal-whatsapp');
    const modalImagen      = document.getElementById('modal-imagen');
    const modalCategoria   = document.getElementById('modal-categoria');
    const modalGaleria     = document.getElementById('modal-galeria');
    const modalBtnCarrito  = document.getElementById('modal-btn-carrito');
    const fondoModal       = document.getElementById('fondo-modal');
if (!modalOverlay) return;

let imagenesModal = [];

function leerImagenesTarjeta(tarjeta) {
    const valor = tarjeta.getAttribute('data-imagenes');
    try {
        const imagenes = JSON.parse(decodeURIComponent(valor || '[]'));
        return Array.isArray(imagenes) ? imagenes.filter(Boolean) : [];
    } catch (error) {
        const portada = tarjeta.getAttribute('data-imagen');
        return portada ? [portada] : [];
    }
}

function mostrarImagenModal(indice) {
    const imagen = imagenesModal[indice];
    if (!imagen) return;

    modalImagen.src = imagen;
    modalGaleria?.querySelectorAll('[data-modal-imagen]').forEach((miniatura, indiceMiniatura) => {
        miniatura.classList.toggle('activa', indiceMiniatura === indice);
    });
}

function cargarGaleriaModal(imagenes, nombre) {
    imagenesModal = imagenes;
    if (!modalGaleria) return;

    modalGaleria.innerHTML = imagenes.length > 1
        ? imagenes.map((imagen, indice) => `
            <button type="button" class="modal-miniatura${indice === 0 ? ' activa' : ''}" data-modal-imagen="${indice}" aria-label="Ver imagen ${indice + 1} de ${nombre}">
                <img src="${imagen}" alt="Miniatura de ${nombre}">
            </button>`).join('')
        : '';
}

// ─── 2. FUNCIÓN PARA ABRIR EL MODAL ────────────────────────────────────────
/**
 * Recibe el elemento <article> de la tarjeta de producto sobre el que
 * se hizo clic, lee sus atributos data-* y rellena el modal con esa info.
 *
 * @ param {HTMLElement} tarjeta - El <article class="tarjeta-producto"> clickeado
 */
function abrirModal(tarjeta) {
    if (!tarjeta) return;
    // Leer todos los datos guardados en los atributos data-* de la tarjeta
        const nombre      = tarjeta.dataset.nombre      || 'Producto';
        const descripcion = tarjeta.dataset.descripcion || '';
        const precio      = parseFloat(tarjeta.dataset.precio) || 0;
        const urlWhatsapp = tarjeta.dataset.whatsapp    || '#';
        const imagen      = tarjeta.dataset.imagen      || '';
        let imagenes      = leerImagenesTarjeta(tarjeta);
        if (!imagenes.length && imagen) imagenes = [imagen];
        const categoria   = tarjeta.dataset.categoria   || '';
        const productoId  = tarjeta.dataset.id          || '';
        const agotado     = tarjeta.classList.contains('producto-agotado');

    // Rellenar el modal con los datos leídos
    modalNombre.textContent      = nombre;
    modalDescripcion.textContent = descripcion;
    modalPrecio.textContent      = `S/ ${precio.toFixed(2)}`;
    modalWhatsapp.href           = urlWhatsapp;
    modalCategoria.textContent   = categoria;

    // Mostrar la imagen si existe, o esconder el contenedor si no hay
    if (imagen) {
        cargarGaleriaModal(imagenes, nombre);
        mostrarImagenModal(0);
        modalImagen.alt = `Foto de ${nombre}`;
        modalImagen.parentElement.style.display = '';
    } else {
        modalImagen.parentElement.style.display = 'none';
    }

    // Configurar el botón de carrito según si hay stock o no
    if (agotado) {
        modalBtnCarrito.textContent        = 'Sin stock disponible';
        modalBtnCarrito.classList.add('agotado');
        modalBtnCarrito.removeAttribute('data-agregar'); // sin data-agregar no dispara nada en tienda.js
    } else {
        modalBtnCarrito.textContent        = 'Añadir al carrito';
        modalBtnCarrito.classList.remove('agotado');
        modalBtnCarrito.dataset.agregar    = productoId; // tienda.js escucha este atributo
    }

    // Mostrar el modal añadiendo la clase CSS "activo"
    modalOverlay.classList.add('visible');
    if (fondoModal) fondoModal.classList.add('visible');
    modalOverlay.setAttribute('aria-hidden', 'false');
    // Bloquear el scroll del body para que no se desplace la página detrás
    document.body.style.overflow = 'hidden';
}


// ─── 3. FUNCIÓN PARA CERRAR EL MODAL ───────────────────────────────────────
/**
 * Oculta el modal y restaura el scroll normal de la página.
 */
function cerrarModal() {
    modalOverlay.classList.remove('visible');
    
    // Ocultar fondo modal si existe
    if (fondoModal) {
        fondoModal.classList.remove('visible');
    }
    
    modalOverlay.setAttribute('aria-hidden', 'true');
    
    // Restaurar el scroll del navegador obligatoriamente
    document.body.style.overflow = '';
    document.body.style.position = '';
}


// ─── 4. EVENTOS DE CIERRE ──────────────────────────────────────────────────

// Cerrar al hacer clic en el fondo oscuro (fuera de la caja blanca)
// Event Delegated Listener
    document.addEventListener('click', (evento) => {
        const botonMiniatura = evento.target.closest('[data-modal-imagen]');
        if (botonMiniatura) {
            mostrarImagenModal(Number(botonMiniatura.dataset.modalImagen));
            return;
        }

        const botonDetalle = evento.target.closest('[data-ver-producto]') || evento.target.closest('.tarjeta-producto');
        if (botonDetalle && !evento.target.closest('[data-agregar], .boton-consulta-agotado')) {
            const tarjeta = evento.target.closest('.tarjeta-producto');
            if (tarjeta) abrirModal(tarjeta);
            return;
        }

        if (evento.target.closest('[data-cerrar-producto]') || evento.target === modalOverlay) {
            cerrarModal();
        }
    });

// Cerrar al presionar la tecla Escape (accesibilidad)
document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && modalOverlay.classList.contains('visible')) {
            cerrarModal();
        }
    });
});
/*
// Una sola escucha para todas las tarjetas: evita usar onclick dentro del HTML.
document.addEventListener('click', (evento) => {
    const botonDetalle = evento.target.closest('[data-ver-producto]');
    if (botonDetalle) {
        abrirModal(botonDetalle.closest('.tarjeta-producto'));
        return;
    }

    if (evento.target.closest('[data-cerrar-producto]')) {
        cerrarModal();
    }
});
*/
