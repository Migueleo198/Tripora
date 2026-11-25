import { Client } from "../domain/model/clientModel.js";
import { ClientError } from "../domain/model/clientError.js";
import { ClientUseCases } from "../application/clientUseCases.js";
import { ClientRepository } from "../infrastructure/clientsInfrastructure.js";
import { RUTA_URL } from "../../viajes/constants/globalUrl.js";

// Intercepta el formulario de usuarios: si no hay JS, sigue funcionando con POST normal
const CLIENT_USE_CASES = new ClientUseCases(new ClientRepository());

const ERROR_BOX = document.querySelector("[data-user-error]");

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
    if(error instanceof ClientError && error.status === 401){
        window.location.href = `${RUTA_URL}/login`;
        return;
    }

    if(error instanceof ClientError){
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

function getClientFromDOM(form){
    const FORM_DATA = new FormData(form);

    return new Client(
        null,
        (FORM_DATA.get("DNI") ?? "").trim().toUpperCase(),
        (FORM_DATA.get("email") ?? "").trim(),
        FORM_DATA.get("rol") ?? "",
        FORM_DATA.get("contrasena") ?? "",
        (FORM_DATA.get("nombre") ?? "").trim(),
        (FORM_DATA.get("apellidos") ?? "").trim(),
        (FORM_DATA.get("telefono") ?? "").trim(),
        FORM_DATA.get("fecha_nac") ?? ""
    );
}

// Crear: <form data-user-form data-user-id=""> (con id es edición, aún no gestionada por JS)
function initCreateForm(){
    const FORM = document.querySelector("form[data-user-form]");

    if(!FORM || FORM.dataset.userId){
        return;
    }

    FORM.addEventListener("submit",async (event) => {
        event.preventDefault();
        hideError();
        setLoading(FORM,true);

        try{
            const RESULT = await CLIENT_USE_CASES.addClient(getClientFromDOM(FORM));

            window.location.href = RESULT.redirect;
        } catch(error){
            handleError(error);
            setLoading(FORM,false);
        }
    });
}

initCreateForm();
