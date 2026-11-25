// Error de dominio de hoteles: validación o respuesta fallida del servidor
export class HotelError extends Error{

    constructor (message,status = null){
        super(message);
        this.name = "HotelError";
        this.status = status;
    }
}
