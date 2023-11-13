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
    const categoryInput = document.getElementById('category'); // Input for movie categories
    const genresInput = document.getElementById('genres'); // Input for movie genres
    const previewTitle = document.getElementById('previewTitle');
    const previewReleaseDate = document.getElementById('previewReleaseDate');
    const previewAgeRating = document.getElementById('previewAgeRating');
    const previewDescription = document.getElementById('previewDescription');
    const previewLanguage = document.getElementById('previewLanguage');
    const previewRuntime = document.getElementById('previewRuntime');
    const previewDirector = document.getElementById('previewDirector');
    const previewActors = document.getElementById('previewActors');
    const previewMoviePoster = document.getElementById('previewMoviePoster'); // Image element for movie poster
    const previewCategories = document.getElementById('previewCategories'); // Element for displaying categories
    const previewGenres = document.getElementById('previewGenres'); // Element for displaying genres

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
        previewCategories.textContent = categoryInput.options[categoryInput.selectedIndex].text; // Display selected category
        previewGenres.textContent = genresInput.value; // Display entered genres

        // Display the movie poster if a file is selected
        if (moviePosterInput.files.length > 0) {
            const file = moviePosterInput.files[0];
            const objectURL = URL.createObjectURL(file);
            previewMoviePoster.src = objectURL;
        } else {
            previewMoviePoster.src = "";
        }
    }

    // Add event listeners to the input fields to update the preview
    titleInput.addEventListener('input', updatePreview);
    titleInput.addEventListener('change', updatePreview);
    releaseDateInput.addEventListener('input', updatePreview);
    releaseDateInput.addEventListener('change', updatePreview);
    ageRatingInput.addEventListener('input', updatePreview);
    ageRatingInput.addEventListener('change', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('change', updatePreview);
    languageInput.addEventListener('input', updatePreview);
    languageInput.addEventListener('change', updatePreview);
    runtimeInput.addEventListener('input', updatePreview);
    runtimeInput.addEventListener('change', updatePreview);
    directorInput.addEventListener('input', updatePreview);
    directorInput.addEventListener('change', updatePreview);
    actorsInput.addEventListener('input', updatePreview);
    actorsInput.addEventListener('change', updatePreview);
    moviePosterInput.addEventListener('input', updatePreview);
    moviePosterInput.addEventListener('change', updatePreview);
    categoryInput.addEventListener('input', updatePreview);
    categoryInput.addEventListener('change', updatePreview);
    genresInput.addEventListener('input', updatePreview);
    genresInput.addEventListener('change', updatePreview);

    // Initial call to populate the preview with the default values
    updatePreview();

    // Debug statements
    console.log("Document is ready.");
    console.log("Initial preview updated.");
});
