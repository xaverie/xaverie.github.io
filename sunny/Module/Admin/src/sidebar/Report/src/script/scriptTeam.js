document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("add-submit").addEventListener("click", function () {
    document.getElementById("addForm").reset();
  });

  const editButtons = document.querySelectorAll(".edit-button");
  editButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const id = button.getAttribute("data-id");
      const name = button.getAttribute("data-name");
      const email = button.getAttribute("data-email");
      const address = button.getAttribute("data-address");
      const phone = button.getAttribute("data-phone");

      document.getElementById("edit-id").value = id;
      document.getElementById("edit-name").value = name;
      document.getElementById("edit-email").value = email;
      document.getElementById("edit-address").value = address;
      document.getElementById("edit-phone").value = phone;
    });
  });

  document.getElementById("addForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("src/php/add_employee.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload();
        } else {
          alert("Failed to add employee: " + data.error);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  });

  document.getElementById("editForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("src/php/edit_employee.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload();
        } else {
          alert("Failed to edit employee: " + data.error);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  });
});