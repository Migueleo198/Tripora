import { Trip } from "../domain/model/tripModel.js";
import { TripError } from "../domain/model/tripError.js";
import { URL_VIAJES } from "../constants/globalUrl.js";

// Convierte un viaje del servidor (id_viaje, nombre...) en un Trip
export function tripFromApi(json){
    return new Trip(
        Number(json.id_viaje),
        json.ubicacion,
        Number(json.precio),
        json.nombre,
        json.descripcion ?? "",
        json.imagen ?? null,
        json.imagenes ?? [],
        Number(json.dias)
    );
}

// Convierte un Trip en los campos que espera Viajes.php
function tripToFormData(trip){
    let formData = new FormData();

    formData.append("nombre",trip.getName());
    formData.append("descripcion",trip.getDescription());
    formData.append("ubicacion",trip.getLocation());
    formData.append("precio",trip.getPrice());
    formData.append("dias",trip.getDays());

    // Mismo formato que el formulario sin JS: actividades_enviadas + actividades[id][campo]
    // Sin lista de actividades (null) no se envían y el servidor conserva las del viaje
    if(trip.getActivities() === null){
        return formData;
    }

    formData.append("actividades_enviadas","1");

    trip.getActivities().forEach((activity) => {
        const FIELD = `actividades[${activity.activityId}]`;

        formData.append(`${FIELD}[incluir]`,"1");
        formData.append(`${FIELD}[dia]`,activity.day);
        formData.append(`${FIELD}[hora]`,activity.time);
        formData.append(`${FIELD}[duracion]`,activity.duration);
        formData.append(`${FIELD}[precio]`,activity.price);

        if(activity.optional){
            formData.append(`${FIELD}[opcional]`,"1");
        }
    });

    return formData;
}

// Repositorio de viajes contra el controlador Viajes.php
export class TripRepository{

    async findAll(page = 0,term = ""){
        const PARAMS = new URLSearchParams({ pagina: page, q: term });
        const DATA = await this.#request(`${URL_VIAJES}/listar?${PARAMS}`);

        return {
            trips: DATA.viajes.map(tripFromApi),
            numPages: DATA.numPaginas,
            currentPage: DATA.paginaActual
        };
    }

    async findById(id){
        const DATA = await this.#request(`${URL_VIAJES}/obtener/${id}`);

        return tripFromApi(DATA.viaje);
    }

    async create(trip){
        const DATA = await this.#request(`${URL_VIAJES}/crear`,{
            method: "POST",
            body: tripToFormData(trip)
        });

        return {
            trip: tripFromApi(DATA.viaje),
            redirect: DATA.redirect
        };
    }

    async update(trip){
        const DATA = await this.#request(`${URL_VIAJES}/editar/${trip.getTripId()}`,{
            method: "POST",
            body: tripToFormData(trip)
        });

        return {
            trip: tripFromApi(DATA.viaje),
            redirect: DATA.redirect
        };
    }

    async delete(id){
        const DATA = await this.#request(`${URL_VIAJES}/borrar/${id}`,{
            method: "POST"
        });

        return {
            tripId: DATA.id_viaje,
            redirect: DATA.redirect
        };
    }

    async uploadImages(id,files){
        let formData = new FormData();

        formData.append("id_viaje",id);
        files.forEach((file) => formData.append("imagenes[]",file));

        const DATA = await this.#request(`${URL_VIAJES}/subirImagen`,{
            method: "POST",
            body: formData
        });

        return {
            images: DATA.imagenes,
            redirect: DATA.redirect
        };
    }

    async deleteImage(id,fileName){
        let formData = new FormData();

        formData.append("imagen",fileName);

        const DATA = await this.#request(`${URL_VIAJES}/borrarImagen/${id}`,{
            method: "POST",
            body: formData
        });

        return {
            images: DATA.imagenes,
            redirect: DATA.redirect
        };
    }

    // Hace la petición pidiendo json y lanza TripError si falla
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
            throw new TripError(`Respuesta no válida del servidor (${RESPONSE.status}).`,RESPONSE.status);
        }

        if(!RESPONSE.ok || !data.ok){
            throw new TripError(data.error ?? `Error ${RESPONSE.status}`,RESPONSE.status);
        }

        return data;
    }
}
