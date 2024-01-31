let menu = document.querySelector('#menu-btn');
let navbar = document.querySelector('.header .navbar');

menu.onclick = () =>{
   menu.classList.toggle('fa-times');
   navbar.classList.toggle('active');
};

window.onscroll = () =>{
   menu.classList.remove('fa-times');
   navbar.classList.remove('active');
};


document.querySelector('#close-edit').onclick = () =>{
   document.querySelector('.edit-form-container').style.display = 'none';
   window.location.href = 'admin.php';
};

document.addEventListener("DOMContentLoaded", function() {
   const quantityInputs = document.querySelectorAll(".quantity-input input");
   const plusButtons = document.querySelectorAll(".quantity-btn.plus");
   const minusButtons = document.querySelectorAll(".quantity-btn.minus");

   plusButtons.forEach((plusButton, index) => {
       plusButton.addEventListener("click", function() {
           const input = quantityInputs[index];
           let currentValue = parseInt(input.value);
           if (!isNaN(currentValue) && currentValue < 10) {
               input.value = currentValue + 1;
           }
       });
   });

   minusButtons.forEach((minusButton, index) => {
       minusButton.addEventListener("click", function() {
           const input = quantityInputs[index];
           let currentValue = parseInt(input.value);
           if (!isNaN(currentValue) && currentValue > 0) {
               input.value = currentValue - 1;
           }
       });
   });
});

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('qrForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var qrUname = document.querySelector('input[name="qrUname"]').value;
        var qrContent = document.querySelector('input[name="qrContent"]').value;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'generate_qr.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText;
                document.getElementById('checkoutQRCode').src = "data:image/png;base64," + response;
            }
        };

        var formData = new FormData();
        formData.append('qrUname', qrUname);
        formData.append('qrContent', qrContent);

        xhr.send(formData);
    });
});
