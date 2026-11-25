// Al cambiar las noches o las comidas en ver hotel se recalcula el total
// y el mensaje del modal de confirmación. El precio real lo calcula el servidor
const FORM = document.querySelector("[data-reserva-hotel]");

if(FORM){
    const TOTAL = FORM.querySelector("[data-total]");
    const NOCHES = FORM.querySelector("[name='noches']");
    const REGIMEN = FORM.querySelector("[name='regimen']");

    const formatoPrecio = (precio) => precio.toLocaleString("es-ES",{minimumFractionDigits: 2,maximumFractionDigits: 2}) + " €";

    const recalcular = () => {
        const noches = Math.max(1,Number(NOCHES.value) || 1);
        const suplemento = Number(REGIMEN.selectedOptions[0]?.dataset.suplemento || 0);
        const total = noches * (Number(FORM.dataset.precio) + suplemento);

        TOTAL.textContent = formatoPrecio(total);
        FORM.dataset.confirmar = "¿Quieres reservar " + noches + (noches === 1 ? " noche" : " noches") + " en «" + FORM.dataset.nombre + "» por " + formatoPrecio(total) + "?";
    };

    NOCHES.addEventListener("input",recalcular);
    REGIMEN.addEventListener("change",recalcular);
    recalcular();
}
