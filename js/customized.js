/* AUTO CHANGING IMAGE EVERY LOAD OF PAGE */
document.addEventListener("DOMContentLoaded", function() {
    // Array containing URLs of images
    var images = [
        "img/banner-images/undraw_engineering_team_a7n2.svg",
        "img/banner-images/undraw_delivery_truck_vt6p.svg",
        "img/banner-images/undraw_electric_car_b-7-hl.svg",
        "img/banner-images/undraw_logistics_x-4-dc.svg"
        // Add more image URLs as needed
    ];

    // Select a random index from the images array
    var randomIndex = Math.floor(Math.random() * images.length);

    // Get the img element by its id
    var imgElement = document.getElementById("randomBannerImage");

    // Set the src attribute of the img element to the randomly selected image URL
    imgElement.src = images[randomIndex];
});


/* AUTO CHANGING IMAGE EVERY LOAD OF LOGIN PAGE */
document.addEventListener("DOMContentLoaded", function() {
    // Array containing URLs of images
    var images = [
        "https://source.unsplash.com/1Ah8CAwk3vM/600x800",
        "https://source.unsplash.com/U4w7y0lowL8/600x800",
        "https://source.unsplash.com/thtUUYPdxWY/600x800"
        // Add more image URLs as needed
    ];

    // Select a random index from the images array
    var randomIndex = Math.floor(Math.random() * images.length);

    // Get the img element by its id
    var imgElement = document.getElementById("randomLogInImage");

    // Set the src attribute of the img element to the randomly selected image URL
    imgElement.src = images[randomIndex];
});



/* AUTO CHANGING IMAGE EVERY LOAD OF REGISTER PAGE */
document.addEventListener("DOMContentLoaded", function() {
    // Array containing URLs of images
    var images = [
        "https://source.unsplash.com/1Ah8CAwk3vM/600x800",
        "https://source.unsplash.com/U4w7y0lowL8/600x800",
        "https://source.unsplash.com/thtUUYPdxWY/600x800"
        // Add more image URLs as needed
    ];

    // Select a random index from the images array
    var randomIndex = Math.floor(Math.random() * images.length);

    // Get the img element by its id
    var imgElement = document.getElementById("randomRegisterImage");

    // Set the src attribute of the img element to the randomly selected image URL
    imgElement.src = images[randomIndex];
});