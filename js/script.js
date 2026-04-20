document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("materials-container");
    const nettoField = document.getElementById("netto_weight");

    // ADD MATERIAL (delegated because buttons are dynamic)
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-material")) {

            const row = document.querySelector(".material-row").cloneNode(true);

            // reset values
            row.querySelector("select").value = "";
            row.querySelector("input").value = "";

            container.appendChild(row);
        }
    });

    // REMOVE MATERIAL
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-material")) {

            const rows = document.querySelectorAll(".material-row");

            // keep at least one row
            if (rows.length > 1) {
                e.target.closest(".material-row").remove();
                calculateNetto();
            }
        }
    });

    // AUTO CALCULATE NETTO
    container.addEventListener("input", function (e) {
        if (e.target.classList.contains("weight-input")) {
            calculateNetto();
        }
    });

    function calculateNetto() {
        let total = 0;

        document.querySelectorAll(".weight-input").forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                total += value;
            }
        });

        nettoField.value = total.toFixed(2);
    }

});