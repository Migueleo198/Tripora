import { Hotel } from "../domain/model/hotelModel.js";
import { HotelError } from "../domain/model/hotelError.js";
import { HotelUseCases } from "../application/hotelesUseCases.js";
import { HotelRepository } from "../infrastructure/hotelesInfrastructure.js";
import { RUTA_URL } from "../../viajes/constants/globalUrl.js";

// Mejora los formularios de hoteles: si no hay JS, siguen funcionando con POST normal
const HOTEL_USE_CASES = new HotelUseCases(new HotelRepository());

const ERROR_BOX = document.querySelector("[data-hotel-error]");

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
    if(error instanceof HotelError && error.status === 401){
        window.location.href = `${RUTA_URL}/login`;
        return;
    }

    if(error instanceof HotelError){
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

// Estrellas y precio vacíos = null (campos opcionales)
function getHotelFromDOM(form){
    const FORM_DATA = new FormData(form);
    const HOTEL_ID = form.dataset.hotelId ? Number(form.dataset.hotelId) : null;
    const STARS = FORM_DATA.get("estrellas") ?? "";
    const PRICE = FORM_DATA.get("precio_noche") ?? "";

    return new Hotel(
        HOTEL_ID,
        FORM_DATA.get("nombre") ?? "",
        FORM_DATA.get("direccion") ?? "",
        FORM_DATA.get("ciudad") ?? "",
        STARS === "" ? null : Number(STARS),
        PRICE === "" ? null : Number(PRICE),
        FORM_DATA.get("descripcion") ?? ""
    );
}

// Crear / editar: <form data-hotel-form data-hotel-id="..."> (sin id crea, con id edita)
function initHotelForms(){
    document.querySelectorAll("form[data-hotel-form]").forEach((form) => {
        form.addEventListener("submit",async (event) => {
            event.preventDefault();
            hideError();
            setLoading(form,true);

            try{
                const HOTEL = getHotelFromDOM(form);
                const RESULT = HOTEL.getHotelId()
                    ? await HOTEL_USE_CASES.updateHotel(HOTEL)
                    : await HOTEL_USE_CASES.addHotel(HOTEL);

                window.location.href = RESULT.redirect;
            } catch(error){
                handleError(error);
                setLoading(form,false);
            }
        });
    });
}

// Borrar: <form data-hotel-delete="id">, dentro de <tr data-hotel-row> si está en la tabla
function initDeleteForms(){
    document.querySelectorAll("form[data-hotel-delete]").forEach((form) => {
        form.addEventListener("submit",async (event) => {
            // La confirmación con modal (comun/confirmar.js) ya gestiona el primer envío
            if(event.defaultPrevented){
                return;
            }

            event.preventDefault();
            hideError();
            setLoading(form,true);

            try{
                const RESULT = await HOTEL_USE_CASES.deleteHotel(Number(form.dataset.hotelDelete));
                const ROW = form.closest("[data-hotel-row]");

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

// Subir imágenes: <form data-hotel-image-form> con miniaturas en <div data-hotel-image-preview>
function initImageForm(){
    const FORM = document.querySelector("form[data-hotel-image-form]");

    if(!FORM){
        return;
    }

    const INPUT = FORM.querySelector("input[type=file]");
    const PREVIEW = document.querySelector("[data-hotel-image-preview]");
    const SELECT = FORM.querySelector("[data-hotel-image-select]");

    // Al cambiar de hotel se recarga para mostrar sus imágenes actuales
    SELECT?.addEventListener("change",() => {
        const URL_DESTINO = new URL(window.location.href);

        URL_DESTINO.searchParams.set("id_hotel",SELECT.value);
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
        const ERROR = FILES.length ? HOTEL_USE_CASES.service.validateImages(FILES) : null;

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
            const HOTEL_ID = Number(new FormData(FORM).get("id_hotel"));
            const RESULT = await HOTEL_USE_CASES.uploadImages(HOTEL_ID,[...INPUT.files]);

            window.location.href = RESULT.redirect;
        } catch(error){
            handleError(error);
            setLoading(FORM,false);
        }
    });
}

// Borrar una imagen: <form data-hotel-image-delete="id del hotel">
function initImageDeleteForms(){
    document.querySelectorAll("form[data-hotel-image-delete]").forEach((form) => {
        form.addEventListener("submit",async (event) => {
            // La confirmación con modal (comun/confirmar.js) ya gestiona el primer envío
            if(event.defaultPrevented){
                return;
            }

            event.preventDefault();
            hideError();
            setLoading(form,true);

            try{
                const HOTEL_ID = Number(form.dataset.hotelImageDelete);
                const RESULT = await HOTEL_USE_CASES.deleteImage(HOTEL_ID,new FormData(form).get("imagen"));

                // Recargar para que la galería y la portada queden actualizadas
                window.location.href = RESULT.redirect;
            } catch(error){
                handleError(error);
                setLoading(form,false);
            }
        });
    });
}

initHotelForms();
initDeleteForms();
initImageForm();
initImageDeleteForms();
