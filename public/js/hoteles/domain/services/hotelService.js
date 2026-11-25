// Reglas de negocio de hoteles (las mismas que valida Hoteles.php)
const IMAGE_TYPES = ["image/jpeg","image/png","image/webp"];

const PRICE_FORMAT = new Intl.NumberFormat("es-ES",{
    style: "currency",
    currency: "EUR"
});

// Vacío, null o undefined: campo opcional sin rellenar
function isEmpty(value){
    return value === null || value === undefined || String(value).trim() === "";
}

export class HotelService{

    // Devuelve el mensaje de error o null si el hotel es válido
    // Estrellas opcional (entero de 1 a 5) y precio por noche opcional (0 o más)
    validate(hotel){
        const STARS = Number(hotel.getEstrellas());
        const PRICE = Number(hotel.getPrecioNoche());

        if(
            String(hotel.getNombre() ?? "").trim() === "" ||
            String(hotel.getDireccion() ?? "").trim() === "" ||
            String(hotel.getCiudad() ?? "").trim() === "" ||
            (!isEmpty(hotel.getEstrellas()) && (!Number.isInteger(STARS) || STARS < 1 || STARS > 5)) ||
            (!isEmpty(hotel.getPrecioNoche()) && (Number.isNaN(PRICE) || PRICE < 0))
        ){
            return "Introduce un nombre, dirección y ciudad. Si las indicas, estrellas de 1 a 5 y precio por noche de 0 o más.";
        }

        return null;
    }

    // Un hotel solo se puede contratar sin viaje si tiene precio por noche
    canBookAlone(hotel){
        return !isEmpty(hotel.getPrecioNoche());
    }

    // Devuelve el mensaje de error o null si la imagen es válida
    validateImage(file){
        if(!file){
            return "No se ha recibido ninguna imagen.";
        }

        if(!IMAGE_TYPES.includes(file.type)){
            return `«${file.name}» debe ser JPG, PNG o WEBP.`;
        }

        return null;
    }

    // Devuelve el primer error de una lista de imágenes o null si todas son válidas
    validateImages(files){
        if(!files || files.length === 0){
            return "No se ha recibido ninguna imagen.";
        }

        for(const FILE of files){
            const ERROR = this.validateImage(FILE);

            if(ERROR){
                return ERROR;
            }
        }

        return null;
    }

    formatPrice(price){
        return PRICE_FORMAT.format(Number(price));
    }
}
