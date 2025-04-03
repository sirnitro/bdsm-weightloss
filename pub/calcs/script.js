document.getElementById("unit-system").addEventListener("change", function () {
  const unitSystem = this.value;
  document.getElementById("metric-fields").style.display =
    unitSystem === "metric" ? "block" : "none";
  document.getElementById("imperial-fields").style.display =
    unitSystem === "imperial" ? "block" : "none";
});

function calculate() {
  const unitSystem = document.getElementById("unit-system").value;
  const weight = unitSystem === "metric"
    ? parseFloat(document.getElementById("weight").value)
    : parseFloat(document.getElementById("weight-imperial").value) * 0.453592;
  const height = unitSystem === "metric"
    ? parseFloat(document.getElementById("height-metric").value) / 100
    : (parseFloat(document.getElementById("height-ft").value) * 12 + parseFloat(document.getElementById("height-in").value)) * 0.0254;
  const waist = parseFloat(document.getElementById("waist").value);
  const hip = parseFloat(document.getElementById("hip").value);
  const neck = parseFloat(document.getElementById("neck").value);
  const steps = parseInt(document.getElementById("steps").value);
  const sleep = parseFloat(document.getElementById("sleep").value);
  const age = parseInt(document.getElementById("age").value);
  const sex = document.getElementById("sex").value;
  const activityLevel = parseFloat(document.getElementById("activity-level").value);

  if (!weight || !height || !age) {
    alert("Please fill in all required fields.");
    return;
  }

  // BMI Calculation
  const bmi = weight / (height * height);

  // BMR Calculation
  const bmr = sex === "male"
    ? 10 * weight + 6.25 * (height * 100) - 5 * age + 5
    : 10 * weight + 6.25 * (height * 100) - 5 * age - 161;

  // TDEE Calculation
  const tdee = bmr * activityLevel;

  // Hydration Recommendation (35ml/kg of weight)
  const hydration = (weight * 35) / 1000; // in liters

  // Waist-to-Hip Ratio
  const waistToHipRatio = waist && hip ? (waist / hip).toFixed(2) : "N/A";

  // Body Fat Percentage (Using US Navy Method)
  let bodyFatPercentage = "N/A";
  if (waist && neck && height) {
    if (sex === "male") {
      bodyFatPercentage = (
        86.010 * Math.log10(waist - neck) -
        70.041 * Math.log10(height * 100) +
        36.76
      ).toFixed(2);
    } else {
      bodyFatPercentage = (
        163.205 * Math.log10(waist + hip - neck) -
        97.684 * Math.log10(height * 100) -
        78.387
      ).toFixed(2);
    }
  }

  // Calorie Deficit for 0.5 kg/week weight loss
  const calorieDeficit = (tdee - 500).toFixed(2);

  // Macros (Assuming 40% protein, 30% fat, 30% carbs)
  const protein = ((tdee * 0.4) / 4).toFixed(2); // grams
  const fat = ((tdee * 0.3) / 9).toFixed(2); // grams
  const carbs = ((tdee * 0.3) / 4).toFixed(2); // grams

  // Step-to-Calories Burned (Assuming 0.04 calories/step)
  const stepCalories = steps ? (steps * 0.04).toFixed(2) : "N/A";

  // Sleep and Weight Loss Relation
  const sleepAdvice = sleep
    ? sleep >= 7
      ? "You're getting enough sleep for weight loss."
      : "Consider improving sleep duration for better weight loss."
    : "N/A";

  // Goal Weight Timeline (Assuming 0.5 kg/week loss)
  const goalWeightTimeline = weight > 0
    ? `Approx. ${(weight / 0.5).toFixed(0)} weeks to reach your goal weight.`
    : "N/A";

  const resultsHTML = `
    <h3>Results:</h3>
    <p><strong>BMI:</strong> ${bmi.toFixed(2)}</p>
    <p><strong>BMR:</strong> ${bmr.toFixed(2)} calories/day</p>
    <p><strong>TDEE:</strong> ${tdee.toFixed(2)} calories/day</p>
    <p><strong>Hydration Recommendation:</strong> ${hydration.toFixed(2)} liters/day</p>
    <p><strong>Waist-to-Hip Ratio:</strong> ${waistToHipRatio}</p>
    <p><strong>Body Fat Percentage:</strong> ${bodyFatPercentage}%</p>
    <p><strong>Calorie Deficit:</strong> ${calorieDeficit} calories/day</p>
    <p><strong>Macros:</strong> Protein: ${protein}g, Fat: ${fat}g, Carbs: ${carbs}g</p>
    <p><strong>Step Calories Burned:</strong> ${stepCalories} calories</p>
    <p><strong>Sleep Advice:</strong> ${sleepAdvice}</p>
    <p><strong>Goal Weight Timeline:</strong> ${goalWeightTimeline}</p>
  `;

  document.getElementById("results-content").innerHTML = resultsHTML;
  openModal();
}

function openModal() {
  document.getElementById("results-modal").style.display = "block";
  document.body.classList.add("modal-open");
}

function closeModal() {
  document.getElementById("results-modal").style.display = "none";
  document.body.classList.remove("modal-open");
}

function printResults() {
  const printContent = document.getElementById("results-content").innerHTML;
  const printWindow = window.open("", "_blank");
  printWindow.document.write(`
    <html>
      <head>
        <title>Print Results</title>
      </head>
      <body>
        ${printContent}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}

