import { Trip } from "../domain/model/tripModel.js";
import { TripError } from "../domain/model/tripError.js";
import { TripUseCases } from "../application/tripUseCases.js";
import { TripRepository } from "../infrastructure/tripInfrastructure.js";
import { RUTA_URL } from "../constants/globalUrl.js";

// Mejora los formularios de viajes: si no hay JS, siguen funcionando con POST normal
const TRIP_USE_CASES = new TripUseCases(new TripRepository());

const ERROR_BOX = document.querySelector("[data-trip-error]");

function showError(message){
    if(!ERROR_BOX){
        console.error(message);
        return;
    }

    ERROR_BOX.textContent = message;
    ERROR_BOX.classList.remove("d-none");
    ERROR_BOX.scrollIntoView({ behavior: "smooth", block: "center" });
}

function hideError(){
    ERROR_BOX?.classList.add("d-none");
}

function handleError(error){
    if(error instanceof TripError && error.status === 401){
        window.location.href = `${RUTA_URL}/login`;
        return;
    }

    if(error instanceof TripError){
        showError(error.message);
        return;
    }

    console.error(error);
    showError("No se pudo conectar con el servidor.");
}

function setLoading(form,loading){
    form.querySelectorAll("button[type=submit]").forEach((button) => {
        button.disabled = loading;
    });
}

// Actividades marcadas en la tabla <tr data-trip-activity="id"> del formulario
// null si el formulario no tiene tabla de actividades
function getActivitiesFromDOM(form){
    const ROWS = [...form.querySelectorAll("[data-trip-activity]")];

    if(ROWS.length === 0){
        return null;
    }

    return ROWS
        .filter((row) => row.querySelector("[name$='[incluir]']")?.checked)
        .map((row) => ({
            activityId: Number(row.dataset.tripActivity),
            name: row.querySelector("label")?.textContent.trim(),
            day: row.querySelector("[name$='[dia]']").value,
            time: row.querySelector("[name$='[hora]']").value,
            duration: row.querySelector("[name$='[duracion]']").value,
            price: row.querySelector("[name$='[precio]']").value,
            optional: row.querySelector("[name$='[opcional]']").checked
        }));
}

function getTripFromDOM(form){
    const FORM_DATA = new FormData(form);
    const TRIP_ID = form.dataset.tripId ? Number(form.dataset.tripId) : null;

    return new Trip(
        TRIP_ID,
        FORM_DATA.get("ubicacion") ?? "",
        FORM_DATA.get("precio") ?? "",
        FORM_DATA.get("nombre") ?? "",
        FORM_DATA.get("descripcion") ?? "",
        null,
        [],
        FORM_DATA.get("dias") ?? "",
        getActivitiesFromDOM(form)
    );
}

// Crear / editar: <form data-trip-form data-trip-id="...">
function initTripForms(){
    document.querySelectorAll("form[data-trip-form]").forEach((form) => {
        form.addEventListener("submit",async (event) => {
            event.preventDefault();
            hideError();
            setLoading(form,true);

            try{
                const TRIP = getTripFromDOM(form);
                const RESULT = TRIP.getTripId()
                    ? await TRIP_USE_CASES.updateTrip(TRIP)
                    : await TRIP_USE_CASES.addTrip(TRIP);

                window.location.href = RESULT.redirect;
            } catch(error){
                handleError(error);
                setLoading(form,false);
            }
        });
    });
}

// Borrar: <form data-trip-delete="id">, dentro de <tr data-trip-row> si está en la tabla
function initDeleteForms(){
    document.querySelectorAll("form[data-trip-delete]").forEach((form) => {
        form.addEventListener("submit",async (event) => {
            // La confirmación con modal (comun/confirmar.js) ya gestiona el primer envío
            if(event.defaultPrevented){
                return;
            }

            event.preventDefault();
            hideError();
            setLoading(form,true);

            try{
                const RESULT = await TRIP_USE_CASES.deleteTrip(Number(form.dataset.tripDelete));
                const ROW = form.closest("[data-trip-row]");

                if(!ROW){
                    window.location.href = RESULT.redirect;
                    return;
                }

                const TBODY = ROW.parentElement;
                ROW.remove();

                // Página vacía: recargar para que el servidor pinte la paginación correcta
                if(TBODY.children.length === 0){
                    window.location.reload();
                }
            } catch(error){
                handleError(error);
                setLoading(form,false);
            }
        });
    });
}

// Subir imágenes: <form data-trip-image-form> con miniaturas en <div data-trip-image-preview>
function initImageForm(){
    const FORM = document.querySelector("form[data-trip-image-form]");

    if(!FORM){
        return;
    }

    const INPUT = FORM.querySelector("input[type=file]");
    const PREVIEW = document.querySelector("[data-trip-image-preview]");
    const SELECT = FORM.querySelector("[data-trip-image-select]");

    // Al cambiar de viaje se recarga para mostrar sus imágenes actuales
    SELECT?.addEventListener("change",() => {
        const URL_DESTINO = new URL(window.location.href);

        URL_DESTINO.searchParams.set("id_viaje",SELECT.value);
        window.location.href = URL_DESTINO.toString();
    });

    INPUT.addEventListener("change",() => {
        hideError();

        if(!PREVIEW){
            return;
        }

        PREVIEW.querySelectorAll("img").forEach((img) => URL.revokeObjectURL(img.src));
        PREVIEW.replaceChildren();

        const FILES = [...INPUT.files];
        const ERROR = FILES.length ? TRIP_USE_CASES.service.validateImages(FILES) : null;

        if(ERROR){
            showError(ERROR);
            return;
        }

        FILES.forEach((file) => {
            const IMG = document.createElement("img");

            IMG.src = URL.createObjectURL(file);
            IMG.alt = file.name;
            IMG.className = "rounded border";
            IMG.style.cssText = "width: 120px; height: 90px; object-fit: cover;";
            PREVIEW.append(IMG);
        });
    });

    FORM.addEventListener("submit",async (event) => {
        event.preventDefault();
        hideError();
        setLoading(FORM,true);

        try{
            const TRIP_ID = Number(new FormData(FORM).get("id_viaje"));
            const RESULT = await TRIP_USE_CASES.uploadImages(TRIP_ID,[...INPUT.files]);

            window.location.href = RESULT.redirect;
        } catch(error){
            handleError(error);
            setLoading(FORM,false);
        }
    });
}

// Borrar una imagen: <form data-trip-image-delete> dentro de <div data-trip-image-item>
function initImageDeleteForms(){
    document.querySelectorAll("form[data-trip-image-delete]").forEach((form) => {
        form.addEventListener("submit",async (event) => {
            // La confirmación con modal (comun/confirmar.js) ya gestiona el primer envío
            if(event.defaultPrevented){
                return;
            }

            event.preventDefault();
            hideError();
            setLoading(form,true);

            try{
                const TRIP_ID = Number(form.action.split("/").pop());
                const RESULT = await TRIP_USE_CASES.deleteImage(TRIP_ID,new FormData(form).get("imagen"));

                // Recargar para que la galería y la portada queden actualizadas
                window.location.href = RESULT.redirect;
            } catch(error){
                handleError(error);
                setLoading(form,false);
            }
        });
    });
}

initTripForms();
initDeleteForms();
initImageForm();
initImageDeleteForms();
