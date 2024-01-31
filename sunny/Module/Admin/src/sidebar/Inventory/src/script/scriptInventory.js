    document.addEventListener("DOMContentLoaded", function () {
        const categoryFilter = document.getElementById("categoryFilter");
        const tableRows = document.querySelectorAll("tbody tr");

        categoryFilter.addEventListener("change", function () {
            const selectedCategory = categoryFilter.value;
            tableRows.forEach(function (row) {
                const categoryCell = row.querySelector(".category-cell").textContent;

                if (selectedCategory === "All" || selectedCategory === categoryCell) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
