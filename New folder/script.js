// Set the current date in the welcome banner
document.addEventListener('DOMContentLoaded', function() {
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        const today = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        // Using a sample date as in the image for consistency
        const sampleDate = new Date('2023-09-04T00:00:00');
        dateElement.textContent = sampleDate.toLocaleDateString('en-US', options);
    }
});