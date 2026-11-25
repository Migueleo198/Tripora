import { ClientError } from "../domain/model/clientError.js";
import { RUTA_URL } from "../../viajes/constants/globalUrl.js";

const URL_USUARIOS = `${RUTA_URL}/usuarios`;

// Convierte un Client en los campos que espera Usuarios.php
function clientToFormData(client){
    let formData = new FormData();

    formData.append("nombre",client.getName());
    formData.append("apellidos",client.getSurname());
    formData.append("DNI",client.getDni());
    formData.append("email",client.getEmail());
    formData.append("telefono",client.getPhone());
    formData.append("fecha_nac",client.getBirthDate());
    formData.append("rol",client.getRole());
    formData.append("contrasena",client.getPassword());

    return formData;
}

// Repositorio de usuarios contra el controlador Usuarios.php
export class ClientRepository{

    async create(client){
        const DATA = await this.#request(`${URL_USUARIOS}/crear`,{
            method: "POST",
            body: clientToFormData(client)
        });

        return {
            clientId: DATA.id_usuario,
            redirect: DATA.redirect
        };
    }

    // Hace la petición pidiendo json y lanza ClientError si falla
    async #request(url,options = {}){
        const RESPONSE = await fetch(url,{
            ...options,
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        });

        let data = null;

        try{
            data = await RESPONSE.json();
        } catch {
            throw new ClientError(`Respuesta no válida del servidor (${RESPONSE.status}).`,RESPONSE.status);
        }

        if(!RESPONSE.ok || !data.ok){
            throw new ClientError(data.error ?? `Error ${RESPONSE.status}`,RESPONSE.status);
        }

        return data;
    }
}
