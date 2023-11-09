document.addEventListener('DOMContentLoaded', function () {
    // Get references to the input fields and elements where you want to display the preview
    const titleInput = document.getElementById('title');
    const releaseDateInput = document.getElementById('releaseDate');
    const ageRatingInput = document.getElementById('ageRating');
    const descriptionInput = document.getElementById('description');
    const languageInput = document.getElementById('language');
    const runtimeInput = document.getElementById('runtime');
    const directorInput = document.getElementById('director');
    const actorsInput = document.getElementById('actors');
    const moviePosterInput = document.getElementById('movie_poster'); // Input for movie poster
    const previewTitle = document.getElementById('previewTitle');
    const previewReleaseDate = document.getElementById('previewReleaseDate');
    const previewAgeRating = document.getElementById('previewAgeRating');
    const previewDescription = document.getElementById('previewDescription');
    const previewLanguage = document.getElementById('previewLanguage');
    const previewRuntime = document.getElementById('previewRuntime');
    const previewDirector = document.getElementById('previewDirector');
    const previewActors = document.getElementById('previewActors');
    const previewMoviePoster = document.getElementById('previewMoviePoster'); // Image element for movie poster

    // Function to update the preview
    function updatePreview() {
        // Update the preview elements with the input values
        previewTitle.textContent = titleInput.value;
        previewReleaseDate.textContent = releaseDateInput.value;
        previewAgeRating.textContent = ageRatingInput.value;
        previewDescription.textContent = descriptionInput.value;
        previewLanguage.textContent = languageInput.value;
        previewRuntime.textContent = runtimeInput.value;
        previewDirector.textContent = directorInput.value;
        previewActors.textContent = actorsInput.value;

        // Display the movie poster if a file is selected
        if (moviePosterInput.files.length > 0) {
            const file = moviePosterInput.files[0];
            const objectURL = URL.createObjectURL(file);
            previewMoviePoster.src = objectURL;
        } else {
            // Hide the movie poster if no file is selected
            previewMoviePoster.src = "";
        }
    }

    // Add event listeners to the input fields to update the preview
    titleInput.addEventListener('input', updatePreview);
    releaseDateInput.addEventListener('input', updatePreview);
    ageRatingInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    languageInput.addEventListener('input', updatePreview);
    runtimeInput.addEventListener('input', updatePreview);
    directorInput.addEventListener('input', updatePreview);
    actorsInput.addEventListener('input', updatePreview);
    moviePosterInput.addEventListener('change', updatePreview); // Listen for file selection

    // Initial call to populate the preview with the default values
    updatePreview();

    // Debug statements
    console.log("Document is ready.");
    console.log("Initial preview updated.");
});
