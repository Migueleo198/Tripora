import { TripService } from "../domain/services/tripService.js";
import { TripError } from "../domain/model/tripError.js";

// Casos de uso de viajes: validan con el servicio y delegan en el repositorio
export class TripUseCases{

    constructor(repository,service = new TripService()){
        this.repository = repository;
        this.service = service;
    }

    loadTrips(page = 0){
        return this.repository.findAll(page);
    }

    loadTrip(id){
        return this.repository.findById(id);
    }

    addTrip(trip){
        this.#checkTrip(trip);
        return this.repository.create(trip);
    }

    updateTrip(trip){
        this.#checkTrip(trip);
        return this.repository.update(trip);
    }

    deleteTrip(id){
        return this.repository.delete(id);
    }

    uploadImages(id,files){
        if(!id){
            throw new TripError("Selecciona un viaje válido.");
        }

        const error = this.service.validateImages(files);

        if(error){
            throw new TripError(error);
        }

        return this.repository.uploadImages(id,files);
    }

    deleteImage(id,fileName){
        return this.repository.deleteImage(id,fileName);
    }

    #checkTrip(trip){
        const error = this.service.validate(trip);

        if(error){
            throw new TripError(error);
        }
    }
}
