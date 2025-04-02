import axios from 'axios';
import Swal from 'sweetalert2';
window.axios = axios;
window.Swal = Swal
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

//Metodo para eliminar elemento
document.addEventListener("DOMContentLoaded", function () {
    document.body.addEventListener("click", function (event) {
        if (event.target.closest(".delete-btn")) {
            let button = event.target.closest(".delete-btn");
            let id = button.getAttribute("data-id");

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        }
    });
});
