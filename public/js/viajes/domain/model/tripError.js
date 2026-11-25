// Error de dominio de viajes: validación o respuesta fallida del servidor
export class TripError extends Error{

    constructor (message,status = null){
        super(message);
        this.name = "TripError";
        this.status = status;
    }
}
