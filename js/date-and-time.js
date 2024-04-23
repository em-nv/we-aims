// Function to update the date and time
function updateDateTime() {
    const currentDate = new Date();
    
    // Format the date
    const dateString = currentDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
    
    // Format the time
    const timeString = currentDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    
    // Update the date_now element
    const dateElement = document.getElementById('date_now');
    dateElement.textContent = dateString;
    
    // Update the current-time element
    const timeElement = document.getElementById('current-time');
    timeElement.textContent = timeString;
}

// Update the date and time initially
updateDateTime();

// Update the date and time every second
setInterval(updateDateTime, 1000);