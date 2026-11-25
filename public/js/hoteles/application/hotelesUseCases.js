import { HotelService } from "../domain/services/hotelService.js";
import { HotelError } from "../domain/model/hotelError.js";

// Casos de uso de hoteles: validan con el servicio y delegan en el repositorio
export class HotelUseCases{

    constructor(repository,service = new HotelService()){
        this.repository = repository;
        this.service = service;
    }

    loadHotels(page = 0){
        return this.repository.findAll(page);
    }

    loadHotel(id){
        return this.repository.findById(id);
    }

    addHotel(hotel){
        this.#checkHotel(hotel);
        return this.repository.create(hotel);
    }

    updateHotel(hotel){
        if(!hotel.getHotelId()){
            throw new HotelError("Selecciona un hotel válido.");
        }

        this.#checkHotel(hotel);
        return this.repository.update(hotel);
    }

    deleteHotel(id){
        if(!id){
            throw new HotelError("Selecciona un hotel válido.");
        }

        return this.repository.delete(id);
    }

    uploadImages(id,files){
        if(!id){
            throw new HotelError("Selecciona un hotel válido.");
        }

        const error = this.service.validateImages(files);

        if(error){
            throw new HotelError(error);
        }

        return this.repository.uploadImages(id,files);
    }

    deleteImage(id,fileName){
        return this.repository.deleteImage(id,fileName);
    }

    #checkHotel(hotel){
        const error = this.service.validate(hotel);

        if(error){
            throw new HotelError(error);
        }
    }
}
