// Reglas de negocio de usuarios (las mismas que valida Usuarios.php)
const ROLES = ["ADMIN","CLIENTE"];
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// DNI: 8 números + letra. NIE: X, Y o Z + 7 números + letra
const DNI_REGEX = /^([0-9]{8}|[XYZ][0-9]{7})[A-Z]$/;
const DNI_LETTERS = "TRWAGMYFPDXBNJZSQVHLCKE";

export class ClientService{

    // Comprueba formato y letra de control de un DNI o NIE
    isValidDni(dni){
        dni = dni.toUpperCase();

        if(!DNI_REGEX.test(dni)){
            return false;
        }

        // En el NIE la letra inicial cuenta como número: X=0, Y=1, Z=2
        const NUMBER = Number(
            dni.slice(0,-1)
                .replace("X","0")
                .replace("Y","1")
                .replace("Z","2")
        );

        return DNI_LETTERS[NUMBER % 23] === dni.slice(-1);
    }

    // Devuelve el mensaje de error o null si el usuario es válido
    validate(client,passwordRequired = true){
        const REQUIRED = [
            client.getName(),
            client.getSurname(),
            client.getDni(),
            client.getEmail(),
            client.getPhone(),
            client.getBirthDate()
        ];

        if(REQUIRED.some((value) => value.trim() === "")){
            return "Rellena todos los campos.";
        }

        if(!this.isValidDni(client.getDni())){
            return "El DNI o NIE no es válido.";
        }

        if(!EMAIL_REGEX.test(client.getEmail())){
            return "El email no es válido.";
        }

        if(!ROLES.includes(client.getRole())){
            return "El rol no es válido.";
        }

        const BIRTH_DATE = new Date(client.getBirthDate());

        if(Number.isNaN(BIRTH_DATE.getTime()) || BIRTH_DATE > new Date()){
            return "La fecha de nacimiento no es válida.";
        }

        if(passwordRequired && client.getPassword().length < 6){
            return "La contraseña debe tener al menos 6 caracteres.";
        }

        return null;
    }
}
