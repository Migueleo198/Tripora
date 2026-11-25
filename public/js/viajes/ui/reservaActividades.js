// Al marcar actividades opcionales en ver viaje se recalcula el total
// y el mensaje del modal de confirmación. El precio real lo calcula el servidor
const FORM = document.querySelector("[data-reserva-viaje]");

if(FORM){
    const TOTAL = FORM.querySelector("[data-total]");
    const CHECKS = FORM.querySelectorAll("input[name='actividades[]']");

    const formatoPrecio = (precio) => precio.toLocaleString("es-ES",{minimumFractionDigits: 2,maximumFractionDigits: 2}) + " €";

    const recalcular = () => {
        let total = Number(FORM.dataset.precio);

        CHECKS.forEach((check) => {
            if(check.checked){
                total += Number(check.dataset.precio);
            }
        });

        if(TOTAL){
            TOTAL.textContent = formatoPrecio(total);
        }

        FORM.dataset.confirmar = "¿Quieres contratar «" + FORM.dataset.nombre + "» por " + formatoPrecio(total) + "?";
    };

    CHECKS.forEach((check) => check.addEventListener("change",recalcular));
}
