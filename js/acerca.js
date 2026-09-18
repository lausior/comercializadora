document.addEventListener('DOMContentLoaded', () => {

    const abrir = document.getElementById('abrirInfoSrg');
    const cerrar = document.getElementById('cerrarInfoSrg');
    const modal = document.getElementById('modalInfoSrg');

    if (!abrir || !cerrar || !modal) {
        return;
    }

    function cerrarModal() {
        modal.style.display = 'none';
        document.body.classList.remove('modal-abierto');
    }

    abrir.addEventListener('click', () => {
        modal.style.display = 'flex';
        document.body.classList.add('modal-abierto');
        cerrar.focus();
    });

    cerrar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.style.display !== 'none') {
            cerrarModal();
        }
    });

});