const fileInput = document.getElementById('event-image-file');
const hintSpan = document.getElementById('event-image-hint');

fileInput.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        // A file was chosen -> show the filename
        hintSpan.textContent = 'Gekozen: ' + this.files[0].name;
    } else {
        // User canceled selection -> restore default text
        hintSpan.textContent = 'JPG of PNG · maximaal 5 MB';
    }
});