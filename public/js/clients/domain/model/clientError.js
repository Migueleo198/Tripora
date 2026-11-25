// Error de dominio de usuarios: validación o respuesta fallida del servidor
export class ClientError extends Error{

    constructor (message,status = null){
        super(message);
        this.name = "ClientError";
        this.status = status;
    }
}
