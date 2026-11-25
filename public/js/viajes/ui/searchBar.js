import { TripService } from "../domain/services/tripService.js";

// Barra de búsqueda de las páginas con listados (vistas/inc/buscador.php): al escribir filtra
// los elementos de la página (tarjetas de viajes, filas de viajes, hoteles o usuarios).
// Cada elemento lleva data-search-item y su texto en data-search.
// El aviso de sin resultados es <div data-search-empty="No hay ... que coincidan con">.
const SERVICE = new TripService();

const FORM = document.querySelector("form[data-trip-search]");
const INPUT = FORM?.querySelector("[data-trip-search-input]");
const ITEMS = [...document.querySelectorAll("[data-search-item]")];
const EMPTY = document.querySelector("[data-search-empty]");

function filter(term){
    let visibles = 0;

    ITEMS.forEach((item) => {
        const MATCH = SERVICE.matchesSearch(item.dataset.search,term);

        item.classList.toggle("d-none",!MATCH);

        if(MATCH){
            visibles++;
        }
    });

    if(EMPTY){
        EMPTY.textContent = `${EMPTY.dataset.searchEmpty || "No hay resultados para"} «${term.trim()}».`;
        EMPTY.classList.toggle("d-none",visibles > 0);
    }
}

// Mantener ?q= en la url para que al recargar siga filtrada
function updateUrl(term){
    const URL_ACTUAL = new URL(window.location.href);

    if(URL_ACTUAL.pathname !== new URL(FORM.action).pathname){
        return;
    }

    if(term.trim() === ""){
        URL_ACTUAL.searchParams.delete("q");
    } else {
        URL_ACTUAL.searchParams.set("q",term.trim());
    }

    history.replaceState(null,"",URL_ACTUAL);
}

function init(){
    if(!FORM || !INPUT || ITEMS.length === 0){
        return;
    }

    INPUT.addEventListener("input",() => {
        filter(INPUT.value);
        updateUrl(INPUT.value);
    });

    // En una página con elementos filtrables no hace falta enviar: ya está filtrado
    FORM.addEventListener("submit",(event) => {
        event.preventDefault();
        filter(INPUT.value);
    });

    // Aplicar el texto que ya tenga el input (p. ej. al volver atrás en el navegador)
    if(INPUT.value.trim() !== ""){
        filter(INPUT.value);
    }
}

init();
