document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("materials-container");
    const nettoField = document.getElementById("netto_weight");

    // ADD MATERIAL
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-material")) {
            // Find the first row to clone it
            const firstRow = document.querySelector(".material-row");
            const newRow = firstRow.cloneNode(true);

            // Reset values in the new row
            newRow.querySelector("select").value = "";
            newRow.querySelector(".weight-input").value = "";

            container.appendChild(newRow);
            calculateNetto(); // Recalculate just in case
        }
    });

    // REMOVE MATERIAL
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-material")) {
            const rows = document.querySelectorAll(".material-row");

            if (rows.length > 1) {
                e.target.closest(".material-row").remove();
                calculateNetto(); // Recalculate after removing
            } else {
                // If it's the last row, just clear it instead of deleting
                const lastRow = rows[0];
                lastRow.querySelector("select").value = "";
                lastRow.querySelector(".weight-input").value = "";
                calculateNetto();
            }
        }
    });

    // AUTO CALCULATE NETTO (Global listener for inputs)
    document.addEventListener("input", function (e) {
        if (e.target.classList.contains("weight-input")) {
            calculateNetto();
        }
    });

    function calculateNetto() {
        let total = 0;
        // We target the class you assigned to the weight inputs
        const weightInputs = document.querySelectorAll(".weight-input");
        
        weightInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) {
                total += val;
            }
        });

        if (nettoField) {
            nettoField.value = total.toFixed(2);
            // Debugging: uncomment the line below to see if it's running in your console
            // console.log("New Total:", total);
        }
    }

    // Use "change" and "input" to cover all bases (typing and clicking arrows)
    document.addEventListener("input", function (e) {
        if (e.target.classList.contains("weight-input")) {
            calculateNetto();
        }
    });

    // Run it once immediately so the page is correct on load
    calculateNetto();
});