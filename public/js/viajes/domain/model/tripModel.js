export class Trip{

    // activities: [{ activityId, day, time, duration, price, optional }]
    // null = no se envían (el viaje conserva las que tenga)
    constructor (tripId,location,price,name,description = "",image = null,images = [],days = 1,activities = null){
        this.tripId = tripId;
        this.location = location;
        this.price = price;
        this.name = name;
        this.description = description;
        this.image = image;
        this.images = images;
        this.days = days;
        this.activities = activities;
    }

    getTripId(){
        return this.tripId;
    }

    getLocation(){
        return this.location;
    }

    getPrice(){
        return this.price;
    }

    getName(){
        return this.name;
    }

    getDescription(){
        return this.description;
    }

    getImage(){
        return this.image;
    }

    getImages(){
        return this.images;
    }

    getDays(){
        return this.days;
    }

    getActivities(){
        return this.activities;
    }

    setTripId(tripId){
        this.tripId = tripId;
    }

    setLocation(location){
        this.location = location;
    }

    setPrice(price){
        this.price = price;
    }

    setName(name){
        this.name = name;
    }

    setDescription(description){
        this.description = description;
    }

    setImage(image){
        this.image = image;
    }

    setImages(images){
        this.images = images;
    }

    setDays(days){
        this.days = days;
    }

    setActivities(activities){
        this.activities = activities;
    }
}
