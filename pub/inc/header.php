<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDSMWeightLoss</title>
    <link rel="stylesheet" href="css/styles.css">

    <script>
        function showPopup(recipe) {
            const popup = document.getElementById('popup');
            document.getElementById('popup-title').innerText = recipe.Title;
            document.getElementById('popup-ingredients').innerText = recipe.Ingredients;
            document.getElementById('popup-instructions').innerText = recipe.Instructions;
            document.getElementById('popup-healthinfo').innerText = recipe.HealthInfo || 'N/A';
            document.getElementById('popup-source').innerHTML = `<a href="${recipe.Source}" target="_blank">Source</a>`;
            document.querySelector('.overlay').style.display = 'block';
            popup.style.display = 'block';
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }
    </script>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>BDSM Weight Loss</h1>
            <p>Where accountability meets transformation</p>
        </div>
<?php include_once "inc/menu.html"; ?>

    </header>
