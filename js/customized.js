/* AUTO CHANGING IMAGE EVERY LOAD OF PAGE */
document.addEventListener("DOMContentLoaded", function() {
    // Array containing URLs of images
    var images = [
        "transparent/aes-logo.png",
        "transparent/bucs-logo.png",
        "transparent/bu-logo.png"
        // Add more image URLs as needed
    ];

    // Select a random index from the images array
    var randomIndex = Math.floor(Math.random() * images.length);

    // Get the img element by its id
    var imgElement = document.getElementById("randomImage");

    // Set the src attribute of the img element to the randomly selected image URL
    imgElement.src = images[randomIndex];
    });





    

/* AUTO CHANGING ICON EVERY LOAD OF PAGE */
document.addEventListener("DOMContentLoaded", function() {
    // Array containing URLs of Lordicon JSON files
    var lordiconURLs = [
        "https://cdn.lordicon.com/cqofjexf.json",
        "https://cdn.lordicon.com/qzhzhqfw.json",
        "https://cdn.lordicon.com/lzlcrlfm.json"
    ];
  
    // Select a random index from the lordiconURLs array
    var randomIndex = Math.floor(Math.random() * lordiconURLs.length);
  
    // Get the lord-icon element by its id
    var lordiconElement = document.getElementById("randomIcon");
  
    // Set the src attribute of the lord-icon element to the randomly selected Lordicon URL
    lordiconElement.setAttribute("src", lordiconURLs[randomIndex]);
  });