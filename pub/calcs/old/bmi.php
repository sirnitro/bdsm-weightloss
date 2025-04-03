<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator</title>
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
        <h2>BMI Calculator</h2>
        <form id="bmiForm">
            <label for="unitType">Unit Type:</label>
            <select id="unitType" name="unitType" onchange="toggleUnits()">
                <option value="metric">Metric (kg, cm)</option>
                <option value="imperial">Imperial (lbs, inches)</option>
            </select>
            
            <div id="metricInputs">
                <label for="weightMetric">Weight (kg):</label>
                <input type="number" id="weightMetric" name="weightMetric" step="0.1" required>
                
                <label for="heightMetric">Height (cm):</label>
                <input type="number" id="heightMetric" name="heightMetric" step="0.1" required>
            </div>
            
            <div id="imperialInputs" style="display: none;">
                <label for="weightImperial">Weight (lbs):</label>
                <input type="number" id="weightImperial" name="weightImperial" step="0.1">
                
                <label for="heightImperial">Height (inches):</label>
                <input type="number" id="heightImperial" name="heightImperial" step="0.1">
            </div>
            
            <button type="button" onclick="calculateBMI()">Calculate BMI</button>
        </form>
        <div class="result" id="result"></div>
    </div>

    <script>
        function toggleUnits() {
            const unitType = document.getElementById("unitType").value;
            if (unitType === "metric") {
                document.getElementById("metricInputs").style.display = "block";
                document.getElementById("imperialInputs").style.display = "none";
            } else {
                document.getElementById("metricInputs").style.display = "none";
                document.getElementById("imperialInputs").style.display = "block";
            }
        }

        function calculateBMI() {
            const unitType = document.getElementById("unitType").value;
            let weight, height, bmi;

            if (unitType === "metric") {
                // Get Metric Inputs
                weight = parseFloat(document.getElementById("weightMetric").value);
                height = parseFloat(document.getElementById("heightMetric").value) / 100; // Convert cm to meters
            } else {
                // Get Imperial Inputs
                weight = parseFloat(document.getElementById("weightImperial").value);
                height = parseFloat(document.getElementById("heightImperial").value);
                // Convert lbs and inches to kg and meters
                weight = weight / 2.20462;
                height = height * 0.0254;
            }

            if (!weight || !height) {
                document.getElementById("result").innerText = "Please fill out all fields.";
                return;
            }

            // Calculate BMI
            bmi = weight / (height * height);

            // Determine BMI Category
            let category;
            if (bmi < 18.5) {
                category = "Underweight";
            } else if (bmi >= 18.5 && bmi < 24.9) {
                category = "Normal weight";
            } else if (bmi >= 25 && bmi < 29.9) {
                category = "Overweight";
            } else {
                category = "Obese";
            }

            // Display the result
            document.getElementById("result").innerHTML = `
                Your BMI is <strong>${bmi.toFixed(2)}</strong>.<br>
                You are classified as <strong>${category}</strong>.
            `;
        }
    </script>
</body>
</html>

