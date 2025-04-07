const ACTION_CONFIG = {
    'active-btn': {
        icon: 'warning',
        confirmText: 'Si, cambiar',
        defaultText: 'Cambiar estado?'
    },
    'restore-btn': {
        icon: 'info',
        confirmText: 'Sí, recuperar',
        defaultText: 'Recuperar elemento.'
    },
    'delete-btn': {
        icon: 'warning',
        confirmText: 'Sí, eliminar',
        defaultText: 'Esta acción no se puede deshacer.'
    }
};

const SWAL_DEFAULT_CONFIG = {
    showCancelButton: true,
    confirmButtonColor: "#0B628D",
    cancelButtonColor: "#3085d6",
    cancelButtonText: "Cancelar"
};

document.body.addEventListener("click", function (event) {
    // Buscar el botón más cercano que coincida con alguno de nuestros selectores
    const actionTypes = Object.keys(ACTION_CONFIG);
    const button = event.target.closest(actionTypes.map(type => `.${type}`).join(', '));

    if (!button) return;

    const actionType = Array.from(button.classList).find(cls => cls.endsWith('-btn'));
    const id = button.getAttribute("data-id");
    const { icon, confirmText, defaultText } = ACTION_CONFIG[actionType];
    const formId = `${actionType.replace('-btn', '')}-form-${id}`;

    const title = actionType === 'restore-btn' ? '¿Recuperar elemento?' : '¿Estás seguro?';

    Swal.fire({
        ...SWAL_DEFAULT_CONFIG,
        title,
        text: defaultText,
        icon,
        confirmButtonText: confirmText
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
});
