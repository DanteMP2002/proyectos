/**
 * catalogo.js
 * Controla los filtros del catálogo. No modifica datos en el servidor.
 */
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador-texto');
    const categoria = document.getElementById('filtro-categoria');
    const orden = document.getElementById('orden-precio');
    const contenedor = document.getElementById('contenedor-productos');

    // Si esta página no tiene catálogo, no hay nada que inicializar.
    if (!buscador || !categoria || !orden || !contenedor) return;

    const tarjetasOriginales = [...contenedor.querySelectorAll('.tarjeta-producto')];

    function aplicarFiltros() {
        const texto = buscador.value.toLocaleLowerCase().trim();
        const categoriaElegida = categoria.value;

        tarjetasOriginales.forEach((tarjeta) => {
            const nombre = (tarjeta.dataset.nombre || '').toLocaleLowerCase();
            const coincide = nombre.includes(texto)
                && (!categoriaElegida || tarjeta.dataset.categoria === categoriaElegida);
            tarjeta.hidden = !coincide;
        });
    }

    function ordenarPorPrecio() {
        const tarjetas = orden.value
            ? [...tarjetasOriginales].sort((a, b) => {
                const diferencia = Number(a.dataset.precio) - Number(b.dataset.precio);
                return orden.value === 'menor-mayor' ? diferencia : -diferencia;
            })
            : tarjetasOriginales;
        tarjetas.forEach((tarjeta) => contenedor.append(tarjeta));
    }

    buscador.addEventListener('input', aplicarFiltros);
    categoria.addEventListener('change', aplicarFiltros);
    orden.addEventListener('change', ordenarPorPrecio);
});
