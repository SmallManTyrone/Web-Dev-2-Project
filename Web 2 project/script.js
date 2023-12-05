document.addEventListener("DOMContentLoaded", function() {
    var successMessageContainer = document.querySelector(".success-message-container");

    // Check if the success message container exists
    if (successMessageContainer) {
        // Add a click event listener to the document
        document.addEventListener("click", function() {
            // Hide the success message container
            successMessageContainer.style.display = "none";
        });

        // Prevent the click event from propagating from the success message container
        successMessageContainer.addEventListener("click", function(event) {
            event.stopPropagation();
        });

    }
});


