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
    <title>Random Movie</title>
    <link rel="stylesheet" href="Styles.css">
</head>

<body>
    <h2>Random Movie</h2>
    <ul>
        <li><a href="index.php">Home</a></li>
    </ul>

    <?php
    if ($err) {
        echo "<p>cURL Error #:" . $err . "</p>";
    } else {
        $json = json_decode($response, true);

        if (isset($json['total_results']) && $json['total_results'] > 0) {
            // Movie found
            $randomIndex = array_rand($json['results']);
            $title = $json['results'][$randomIndex]['title'];
            $posterPath = $json['results'][$randomIndex]['poster_path'];
            $posterUrl = "http://image.tmdb.org/t/p/w500/{$posterPath}";

            echo "<p>Your random movie: <strong>{$title}</strong></p>";
            echo "<img src=\"{$posterUrl}\" class=\"img-responsive\">";
        } else {
            echo '<div class="alert"><p>We\'re afraid nothing was found for that search.</p></div>';
            echo "<p>Perhaps you were looking for The Goonies?</p>";
        }
    }
    ?>

    <br>
    <button onclick="location.reload()">Refresh</button>
</body>

</html>
