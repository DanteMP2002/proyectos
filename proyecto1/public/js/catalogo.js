/**
 * Filtros del catálogo. Solo cambia las tarjetas visibles en el navegador;
 * no modifica productos ni consulta la base de datos.
 */
function iniciarFiltrosCatalogo() {
    const buscador = document.getElementById('buscador-texto');
    const filtroCategoria = document.getElementById('filtro-categoria');
    const ordenPrecio = document.getElementById('orden-precio');
    const contenedor = document.getElementById('contenedor-productos');

    // Esta validación permite reutilizar el archivo en páginas sin catálogo.
    if (!buscador || !filtroCategoria || !ordenPrecio || !contenedor) return;

    const tarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-producto'));
    const mensajeVacio = document.createElement('p');
    mensajeVacio.className = 'mensaje-sin-resultados';
    mensajeVacio.textContent = 'No encontramos productos con esos filtros.';
    mensajeVacio.hidden = true;
    contenedor.after(mensajeVacio);

    // Oculta o muestra las tarjetas según texto y categoría seleccionada.
    function aplicarFiltros() {
        const textoBuscado = buscador.value.trim().toLocaleLowerCase();
        const categoriaElegida = filtroCategoria.value;
        let resultadosVisibles = 0;

        tarjetas.forEach((tarjeta) => {
            const nombre = (tarjeta.dataset.nombre || '').toLocaleLowerCase();
            const categoria = tarjeta.dataset.categoria || '';
            const coincide = nombre.includes(textoBuscado)
                && (!categoriaElegida || categoria === categoriaElegida);

            tarjeta.hidden = !coincide;
            if (coincide) resultadosVisibles += 1;
        });

        mensajeVacio.hidden = resultadosVisibles !== 0;
    }

    // Reordena las mismas tarjetas, sin perder el filtro ya aplicado.
    function ordenarTarjetas() {
        const orden = ordenPrecio.value;
        const tarjetasOrdenadas = [...tarjetas].sort((primera, segunda) => {
            if (!orden) return tarjetas.indexOf(primera) - tarjetas.indexOf(segunda);

            const diferencia = Number(primera.dataset.precio) - Number(segunda.dataset.precio);
            return orden === 'menor-mayor' ? diferencia : -diferencia;
        });

        tarjetasOrdenadas.forEach((tarjeta) => contenedor.append(tarjeta));
    }

    buscador.addEventListener('input', aplicarFiltros);
    filtroCategoria.addEventListener('change', aplicarFiltros);
    ordenPrecio.addEventListener('change', ordenarTarjetas);
}

// Funciona tanto si el script carga antes como después de que el DOM esté listo.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarFiltrosCatalogo);
} else {
    iniciarFiltrosCatalogo();
}
