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
        const inputs = document.querySelectorAll(".weight-input");
        
        inputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                total += value;
            }
        });

        if (nettoField) {
            nettoField.value = total.toFixed(2);
        }
    }
    
    // Initial calculation on page load (in case data is already there)
    calculateNetto();
});