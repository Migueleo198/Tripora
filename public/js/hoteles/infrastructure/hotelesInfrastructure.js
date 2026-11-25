import { Hotel } from "../domain/model/hotelModel.js";
import { HotelError } from "../domain/model/hotelError.js";
import { RUTA_URL } from "../../viajes/constants/globalUrl.js";

const URL_HOTELES = `${RUTA_URL}/hoteles`;

// Convierte un hotel del servidor (id_hotel, nombre...) en un Hotel
export function hotelFromAPI(json){
    return new Hotel(
        Number(json.id_hotel),
        json.nombre,
        json.direccion,
        json.ciudad,
        json.estrellas === null || json.estrellas === "" ? null : Number(json.estrellas),
        json.precio_noche === null || json.precio_noche === "" ? null : Number(json.precio_noche),
        json.descripcion ?? ""
    );
}

// Convierte un Hotel en los campos que espera Hoteles.php (opcionales vacíos = NULL)
function hotelToFormData(hotel){
    let formData = new FormData();

    formData.append("nombre",hotel.getNombre());
    formData.append("direccion",hotel.getDireccion());
    formData.append("ciudad",hotel.getCiudad());
    formData.append("descripcion",hotel.getDescripcion() ?? "");
    formData.append("estrellas",hotel.getEstrellas() ?? "");
    formData.append("precio_noche",hotel.getPrecioNoche() ?? "");

    return formData;
}

// Repositorio de hoteles contra el controlador Hoteles.php
export class HotelRepository{

    async findAll(page = 0){
        const PARAMS = new URLSearchParams({ pagina: page });
        const DATA = await this.#request(`${URL_HOTELES}/listar?${PARAMS}`);

        return {
            hotels: DATA.hoteles.map(hotelFromAPI),
            numPages: DATA.numPaginas,
            currentPage: DATA.paginaActual
        };
    }

    async findById(id){
        const DATA = await this.#request(`${URL_HOTELES}/obtener/${id}`);

        return hotelFromAPI(DATA.hotel);
    }

    async create(hotel){
        const DATA = await this.#request(`${URL_HOTELES}/crear`,{
            method: "POST",
            body: hotelToFormData(hotel)
        });

        return {
            hotel: hotelFromAPI(DATA.hotel),
            redirect: DATA.redirect
        };
    }

    async update(hotel){
        const DATA = await this.#request(`${URL_HOTELES}/editar/${hotel.getHotelId()}`,{
            method: "POST",
            body: hotelToFormData(hotel)
        });

        return {
            hotel: hotelFromAPI(DATA.hotel),
            redirect: DATA.redirect
        };
    }

    async delete(id){
        const DATA = await this.#request(`${URL_HOTELES}/borrar/${id}`,{
            method: "POST"
        });

        return {
            hotelId: DATA.id_hotel,
            redirect: DATA.redirect
        };
    }

    async uploadImages(id,files){
        let formData = new FormData();

        formData.append("id_hotel",id);
        files.forEach((file) => formData.append("imagenes[]",file));

        const DATA = await this.#request(`${URL_HOTELES}/subirImagen`,{
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

        const DATA = await this.#request(`${URL_HOTELES}/borrarImagen/${id}`,{
            method: "POST",
            body: formData
        });

        return {
            images: DATA.imagenes,
            redirect: DATA.redirect
        };
    }

    // Hace la petición pidiendo json y lanza HotelError si falla
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
            throw new HotelError(`Respuesta no válida del servidor (${RESPONSE.status}).`,RESPONSE.status);
        }

        if(!RESPONSE.ok || !data.ok){
            throw new HotelError(data.error ?? `Error ${RESPONSE.status}`,RESPONSE.status);
        }

        return data;
    }
}
