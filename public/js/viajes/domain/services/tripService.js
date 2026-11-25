// Reglas de negocio de viajes (las mismas que valida Viajes.php)
const IMAGE_TYPES = ["image/jpeg","image/png","image/webp"];

const PRICE_FORMAT = new Intl.NumberFormat("es-ES",{
    style: "currency",
    currency: "EUR"
});

export class TripService{

    // Devuelve el mensaje de error o null si el viaje es válido
    validate(trip){
        const price = Number(trip.getPrice());
        const days = Number(trip.getDays());

        if(
            trip.getName().trim() === "" ||
            trip.getLocation().trim() === "" ||
            trip.getPrice() === "" ||
            Number.isNaN(price) ||
            price < 0 ||
            !Number.isInteger(days) ||
            days < 1 ||
            days > 255
        ){
            return "Introduce un nombre, ubicación, precio y días válidos.";
        }

        return this.validateActivities(trip.getActivities() ?? [],days);
    }

    // Mismas reglas que Viajes.php: día de 1 a los días del viaje, duración > 0, hora opcional, precio >= 0
    validateActivities(activities = [],days){
        const TIME = /^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/;

        for(const ACTIVITY of activities){
            const DAY = Number(ACTIVITY.day);
            const PRICE = Number(ACTIVITY.price);

            if(
                !Number.isInteger(DAY) || DAY < 1 || DAY > days ||
                !TIME.test(ACTIVITY.duration) || /^00:00(:00)?$/.test(ACTIVITY.duration) ||
                (ACTIVITY.time !== "" && !TIME.test(ACTIVITY.time)) ||
                ACTIVITY.price === "" || Number.isNaN(PRICE) || PRICE < 0
            ){
                return `Revisa «${ACTIVITY.name ?? "la actividad"}»: día de 1 a ${days}, duración, hora (opcional) y precio válidos.`;
            }
        }

        return null;
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

    // Texto en minúsculas y sin tildes, para comparar búsquedas ("Paris" encuentra "París")
    normalizeText(text){
        return (text ?? "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g,"")
            .toLowerCase()
            .trim();
    }

    // Comprueba si un texto contiene la búsqueda (búsqueda vacía: todo coincide)
    matchesSearch(text,term){
        const TERM = this.normalizeText(term);

        return TERM === "" || this.normalizeText(text).includes(TERM);
    }

    formatPrice(price){
        return PRICE_FORMAT.format(Number(price));
    }
}
