/* =========================================================
   EXPORTAR LISTADO A PDF
   =========================================================
   Función compartida por Empresas, Usuarios y Clientes.

   Recoge los ids de las filas que pasan los filtros ACTIVOS
   en ese momento (llamando a la función obtenerFilasFiltradas
   de cada listado, que se le pasa como parámetro), y los envía
   por POST a su script de exportación correspondiente, que
   vuelve a comprobar permisos fila a fila y genera el PDF con
   FPDF.

   Se manda por POST en un <form> oculto en vez de por GET
   para no tener límite de longitud de URL con listados
   largos, y se abre en una pestaña nueva para no perder la
   página del listado.
========================================================= */

function exportarListadoPDF(urlDestino, obtenerFilasFiltradas) {

    const filas = obtenerFilasFiltradas();

    if (!filas || filas.length === 0) {

        alert('No hay resultados que exportar con los filtros actuales.');

        return;

    }

    const ids = filas
        .map(fila => fila.dataset.id)
        .filter(id => id)
        .join(',');

    const formulario = document.createElement('form');

    formulario.method = 'POST';
    formulario.action = urlDestino;
    formulario.target = '_blank';
    formulario.style.display = 'none';

    const inputIds = document.createElement('input');

    inputIds.type = 'hidden';
    inputIds.name = 'ids';
    inputIds.value = ids;

    formulario.appendChild(inputIds);

    document.body.appendChild(formulario);

    formulario.submit();

    document.body.removeChild(formulario);

}
