export class Hotel{

    constructor(hotelId,nombre,direccion,ciudad,estrellas,precioNoche = null,descripcion = ""){
        this.hotelId = hotelId;
        this.nombre = nombre;
        this.direccion = direccion;
        this.ciudad = ciudad;
        this.estrellas = estrellas;
        this.precioNoche = precioNoche;
        this.descripcion = descripcion;
    }

    getHotelId(){
        return this.hotelId;
    }

    getNombre(){
        return this.nombre;
    }

    getDireccion(){
        return this.direccion;
    }

    getCiudad(){
        return this.ciudad;
    }

    getEstrellas(){
        return this.estrellas;
    }

    getPrecioNoche(){
        return this.precioNoche;
    }

    getDescripcion(){
        return this.descripcion;
    }

    setHotelId(hotelId){
        this.hotelId = hotelId;
    }

    setNombre(nombre){
        this.nombre = nombre;
    }

    setDireccion(direccion){
        this.direccion = direccion;
    }

    setCiudad(ciudad){
        this.ciudad = ciudad;
    }

    setEstrellas(estrellas){
        this.estrellas = estrellas;
    }

    setPrecioNoche(precioNoche){
        this.precioNoche = precioNoche;
    }

    setDescripcion(descripcion){
        this.descripcion = descripcion;
    }

}