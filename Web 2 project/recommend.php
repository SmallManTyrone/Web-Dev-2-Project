<?php

$apiKey = "15d2ea6d0dc1d476efbca3eba2b9bbfb";
$apiUrl = "https://api.themoviedb.org/3/discover/movie";
$defaultQuery = "goonies"; // Default query if the user's search is empty

$apiUrl .= "?api_key={$apiKey}&sort_by=popularity.desc&page=" . rand(1, 100); // Random page

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Movie</title>
    <link rel="stylesheet" href="recommendstyles.css">
</head>

<body>
    <header>
        <h2>Random Movie</h2>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>
    </header>

    <section class="random-movie-container">
        <?php
        if ($err) {
            echo "<p class='error'>cURL Error #:" . $err . "</p>";
        } else {
            $json = json_decode($response, true);

            if (isset($json['total_results']) && $json['total_results'] > 0) {
                // Movie found
                $randomIndex = array_rand($json['results']);
                $title = $json['results'][$randomIndex]['title'];
                $posterPath = $json['results'][$randomIndex]['poster_path'];
                $posterUrl = "http://image.tmdb.org/t/p/w500/{$posterPath}";
                
                echo "<div class='random-movie-info'>";
                echo "<p class='movie-title'>Your random movie: <strong>{$title}</strong></p>";
                echo "<button class='refresh-button' onclick='location.reload()'>Refresh</button>";
                echo "</div>";
                echo "<img src=\"{$posterUrl}\" alt=\"Movie Poster\" class=\"movie-poster\">";
    
            } else {
                echo '<div class="alert"><p>We\'re afraid nothing was found for that search.</p></div>';
                echo "<p>Perhaps you were looking for The Goonies?</p>";
            }
        }
        ?>
    </section>
</body>

</html>