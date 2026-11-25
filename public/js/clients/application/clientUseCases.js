import { ClientService } from "../domain/services/clientService.js";
import { ClientError } from "../domain/model/clientError.js";

// Casos de uso de usuarios: validan con el servicio y delegan en el repositorio
export class ClientUseCases{

    constructor(repository,service = new ClientService()){
        this.repository = repository;
        this.service = service;
    }

    addClient(client){
        const error = this.service.validate(client);

        if(error){
            throw new ClientError(error);
        }

        return this.repository.create(client);
    }
}
