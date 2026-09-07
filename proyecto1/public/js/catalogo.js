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

    const mensajeVacio = document.createElement('p');
    mensajeVacio.className = 'mensaje-sin-resultados';
    mensajeVacio.textContent = 'No encontramos productos con esos filtros.';
    mensajeVacio.hidden = true;
    contenedor.after(mensajeVacio);

    function aplicarFiltros() {
        const textoBuscado = buscador.value.trim().toLocaleLowerCase();
        const categoriaElegida = filtroCategoria.value;
        let resultadosVisibles = 0;

        tarjetasOriginales.forEach((tarjeta) => {
            const nombre = (tarjeta.dataset.nombre || '').toLocaleLowerCase();
            const categoria = tarjeta.dataset.categoria || '';
            const coincide = nombre.includes(textoBuscado)
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

        if (criterio === 'menor-mayor' || criterio === 'mayor-menor') {
            resultado = Number(primera.dataset.precio) - Number(segunda.dataset.precio);
            if (criterio === 'mayor-menor') resultado *= -1;
        } else if (criterio === 'nombre') {
            resultado = (primera.dataset.nombre || '').localeCompare(segunda.dataset.nombre || '', 'es');
        } else {
            resultado = prioridadRecomendada(primera) - prioridadRecomendada(segunda);
            // Mantiene el orden original dentro de cada grupo recomendado.
            if (resultado === 0) result = tarjetasOriginales.indexOf(primera) - tarjetasOriginales.indexOf(segunda);
        }

        return ordenInvertido ? resultado * -1 : resultado;
    }

    function ordenarTarjetas() {
        [...tarjetasOriginales].sort(compararTarjetas).forEach((tarjeta) => contenedor.append(tarjeta));
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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarFiltrosCatalogo);
} else {
    iniciarFiltrosCatalogo();
}
