(function () {
    'use strict';

    const MODAL_Z_INDEX = '4100';
    const BACKDROP_Z_INDEX = '4090';

    function activeModalExists() {
        return document.querySelector('.modal.show, .modal.showing') !== null;
    }

    function promoteModalToBody(modalElement) {
        if (!(modalElement instanceof HTMLElement) || !modalElement.classList.contains('modal')) {
            return;
        }

        if (modalElement.parentElement !== document.body) {
            document.body.appendChild(modalElement);
        }

        modalElement.style.zIndex = MODAL_Z_INDEX;
    }

    function syncBackdropLayers() {
        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.style.zIndex = BACKDROP_Z_INDEX;
        });
    }

    function clearOrphanedBackdrop() {
        if (activeModalExists()) {
            syncBackdropLayers();
            return;
        }

        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    }

    document.addEventListener('show.bs.modal', function (event) {
        promoteModalToBody(event.target);
        clearOrphanedBackdrop();
        syncBackdropLayers();
    });

    document.addEventListener('shown.bs.modal', function (event) {
        promoteModalToBody(event.target);
        window.requestAnimationFrame(syncBackdropLayers);
    });

    document.addEventListener('hidden.bs.modal', function () {
        window.requestAnimationFrame(clearOrphanedBackdrop);
    });

    window.addEventListener('pageshow', clearOrphanedBackdrop);
})();
