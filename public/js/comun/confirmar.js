// Confirmación con modal para cualquier <form data-confirmar="mensaje">
// Se registra en fase de captura para frenar el envío antes que otros listeners del form
(function(){
    const MODAL_EL = document.getElementById("modalConfirmar");

    if(!MODAL_EL){
        return;
    }

    const MODAL = new bootstrap.Modal(MODAL_EL);
    const MENSAJE = MODAL_EL.querySelector("[data-modal-mensaje]");
    const ACEPTAR = MODAL_EL.querySelector("[data-modal-aceptar]");

    let formPendiente = null;

    document.addEventListener("submit",(event) => {
        const FORM = event.target.closest("form[data-confirmar]");

        if(!FORM){
            return;
        }

        // Segundo envío tras aceptar: dejarlo pasar
        if(FORM.dataset.confirmado === "1"){
            delete FORM.dataset.confirmado;
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        formPendiente = FORM;
        MENSAJE.textContent = FORM.dataset.confirmar;
        ACEPTAR.textContent = FORM.dataset.confirmarBoton || "Aceptar";
        MODAL.show();
    },true);

    ACEPTAR.addEventListener("click",() => {
        if(!formPendiente){
            return;
        }

        const FORM = formPendiente;
        formPendiente = null;

        FORM.dataset.confirmado = "1";
        MODAL.hide();
        FORM.requestSubmit();
    });

    MODAL_EL.addEventListener("hidden.bs.modal",() => {
        formPendiente = null;
    });
})();
