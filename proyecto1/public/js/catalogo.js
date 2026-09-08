/**
 * Filtros y orden visual del catálogo. No modifica datos del servidor.
 */
function iniciarFiltrosCatalogo() {
    const buscador = document.getElementById('buscador-texto');
    const filtroCategoria = document.getElementById('filtro-categoria');
    const selectorOrden = document.getElementById('orden-catalogo');
    const botonInvertir = document.getElementById('invertir-orden');
    const contenedor = document.getElementById('contenedor-productos');

    if (!buscador || !filtroCategoria || !selectorOrden || !botonInvertir || !contenedor) return;

    const tarjetasOriginales = Array.from(contenedor.querySelectorAll('.tarjeta-producto'));
    let ordenInvertido = false;

    function normalizarTexto(valor) {
        return String(valor)
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('es')
            .trim();
    }

    const mensajeVacio = document.createElement('p');
    mensajeVacio.className = 'mensaje-sin-resultados';
    mensajeVacio.textContent = 'No encontramos productos con esos filtros.';
    mensajeVacio.hidden = true;
    contenedor.after(mensajeVacio);

    function aplicarFiltros() {
        const textoBuscado = normalizarTexto(buscador.value);
        const categoriaElegida = normalizarTexto(filtroCategoria.value);
        let resultadosVisibles = 0;

        tarjetasOriginales.forEach((tarjeta) => {
            const nombre = normalizarTexto(tarjeta.dataset.nombre);
            const descripcion = normalizarTexto(tarjeta.dataset.descripcion);
            const categoria = normalizarTexto(tarjeta.dataset.categoria);
            const coincideTexto = !textoBuscado
                || nombre.includes(textoBuscado)
                || descripcion.includes(textoBuscado);
            const coincide = coincideTexto
                && (!categoriaElegida || categoria === categoriaElegida);

            tarjeta.hidden = !coincide;
            if (coincide) resultadosVisibles += 1;
        });

        mensajeVacio.hidden = resultadosVisibles !== 0;
    }

    // Vestidos primero, categorías regulares al centro y pasteles/postres al final.
    function prioridadRecomendada(tarjeta) {
        const categoria = (tarjeta.dataset.categoria || '').toLocaleLowerCase();
        if (categoria.includes('vestido')) return 0;
        if (categoria.includes('pastel') || categoria.includes('postre')) return 2;
        return 1;
    }

    function compararTarjetas(primera, segunda) {
        const criterio = selectorOrden.value;
        let resultado = 0;

        if (criterio === 'menor-mayor') {
            resultado = Number(primera.dataset.precio) - Number(segunda.dataset.precio);
        } else if (criterio === 'nombre') {
            resultado = normalizarTexto(primera.dataset.nombre).localeCompare(normalizarTexto(segunda.dataset.nombre), 'es');
        } else {
            resultado = prioridadRecomendada(primera) - prioridadRecomendada(segunda);
            // Mantiene el orden original dentro de cada grupo recomendado.
            if (resultado === 0) resultado = tarjetasOriginales.indexOf(primera) - tarjetasOriginales.indexOf(segunda);
        }

        return ordenInvertido ? resultado * -1 : resultado;
    }

    function ordenarTarjetas() {
        [...tarjetasOriginales].sort(compararTarjetas).forEach((tarjeta) => contenedor.append(tarjeta));
        aplicarFiltros();
    }

    buscador.addEventListener('input', aplicarFiltros);
    filtroCategoria.addEventListener('change', aplicarFiltros);
    selectorOrden.addEventListener('change', ordenarTarjetas);
    botonInvertir.addEventListener('click', () => {
        ordenInvertido = !ordenInvertido;
        botonInvertir.classList.toggle('invertido', ordenInvertido);
        botonInvertir.setAttribute('aria-label', ordenInvertido ? 'Restaurar orden' : 'Invertir el orden');
        ordenarTarjetas();
    });

    ordenarTarjetas();
    iniciarRotacionImagenes(tarjetasOriginales);
}

function iniciarRotacionImagenes(tarjetas) {
    tarjetas.forEach((tarjeta) => {
        const imagen = tarjeta.querySelector('.contenedor-imagen-producto img');
        if (!imagen) return;

        let imagenes;
        try {
            imagenes = JSON.parse(tarjeta.dataset.imagenes || '[]');
        } catch {
            imagenes = [];
        }

        if (imagenes.length < 2) return;

        let indice = 0;
        let temporizador;
        tarjeta.addEventListener('mouseenter', () => {
            temporizador = window.setInterval(() => {
                indice = (indice + 1) % imagenes.length;
                imagen.src = imagenes[indice];
            }, 3000);
        });
        tarjeta.addEventListener('mouseleave', () => {
            window.clearInterval(temporizador);
            temporizador = undefined;
            indice = 0;
            imagen.src = imagenes[0];
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarFiltrosCatalogo);
} else {
    iniciarFiltrosCatalogo();
}
