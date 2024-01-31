
$(document).ready(function() {
    $('#product_ingredient').multiselect({
        enableFiltering: true, // Optional: Enable filtering of options
    });
});
function hideShowDivs(select) {
    var selectedValue = select.value;
    if (select == "0") {
        document.getElementById('div_sandwich').style.display = 'none';
    }
    else if (select == selectedValue) {
        document.getElementById('div_sandwich').style.display = 'block';
    }
    // else if (val == "Beverage") {
    //     document.getElementById('div_sandwich').style.display = 'block';
    // }

}
document.querySelector("select[name='product_ingredient[]']").addEventListener("click", function () {
    const checkboxes = document.getElementById("ingredientCheckboxes");
    checkboxes.style.display = this.selectedIndex === 0 ? "none" : "block";
});