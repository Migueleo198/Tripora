export class Client{

    constructor (clientId,dni,email,role,password,name,surname,phone,birthDate){
        this.clientId = clientId;
        this.dni = dni;
        this.email = email;
        this.role = role;
        this.password = password;
        this.name = name;
        this.surname = surname;
        this.phone = phone;
        this.birthDate = birthDate;
    }

    getClientId(){
        return this.clientId;
    }

    getDni(){
        return this.dni;
    }

    getEmail(){
        return this.email;
    }

    getRole(){
        return this.role;
    }

    getPassword(){
        return this.password;
    }

    getName(){
        return this.name;
    }

    getSurname(){
        return this.surname;
    }

    getPhone(){
        return this.phone;
    }

    getBirthDate(){
        return this.birthDate;
    }
}
