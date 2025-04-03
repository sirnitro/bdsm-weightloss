<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calorie Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 400px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Calorie Calculator</h2>
        <form id="calorieForm">
            <label for="age">Age:</label>
            <input type="number" id="age" name="age" required>
            
            <label for="gender">Gender:</label>
            <select id="gender" name="gender">
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            
            <label for="weight">Weight:</label>
            <input type="number" id="weight" name="weight" required>
            <select id="weightUnit" name="weightUnit">
                <option value="kg">kg</option>
                <option value="lbs">lbs</option>
            </select>
            
            <label for="height">Height:</label>
            <input type="number" id="height" name="height" required>
            <select id="heightUnit" name="heightUnit">
                <option value="cm">cm</option>
                <option value="inches">inches</option>
            </select>
            
            <label for="activity">Activity Level:</label>
            <select id="activity" name="activity">
                <option value="1.2">Sedentary (little or no exercise)</option>
                <option value="1.375">Lightly active (light exercise/sports 1-3 days/week)</option>
                <option value="1.55">Moderately active (moderate exercise/sports 3-5 days/week)</option>
                <option value="1.725">Very active (hard exercise/sports 6-7 days a week)</option>
                <option value="1.9">Extra active (very hard exercise/physical job)</option>
            </select>
            
            <button type="button" onclick="calculateCalories()">Calculate</button>
        </form>
        <div class="result" id="result"></div>
    </div>

    <script>
        function calculateCalories() {
            const age = parseInt(document.getElementById("age").value);
            const gender = document.getElementById("gender").value;
            let weight = parseFloat(document.getElementById("weight").value);
            let height = parseFloat(document.getElementById("height").value);
            const weightUnit = document.getElementById("weightUnit").value;
            const heightUnit = document.getElementById("heightUnit").value;
            const activity = parseFloat(document.getElementById("activity").value);

            if (!age || !weight || !height || !activity) {
                document.getElementById("result").innerText = "Please fill out all fields.";
                return;
            }

            // Convert weight to kilograms if entered in pounds
            if (weightUnit === "lbs") {
                weight = weight / 2.20462;
            }

            // Convert height to centimeters if entered in inches
            if (heightUnit === "inches") {
                height = height * 2.54;
            }

            let bmr; // Basal Metabolic Rate

            // Harris-Benedict Equation
            if (gender === "male") {
                bmr = 10 * weight + 6.25 * height - 5 * age + 5;
            } else {
                bmr = 10 * weight + 6.25 * height - 5 * age - 161;
            }

            const calories = bmr * activity;

            // Display the result
            document.getElementById("result").innerText = `Your estimated daily caloric need is ${Math.round(calories)} calories.`;
        }
    </script>
</body>
</html>

