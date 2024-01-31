const firebaseConfig = {
    apiKey: "AIzaSyAlPBvf_e9AFr9E2mdh-Xa97HkfzNgZN3c",
    authDomain: "transact-bfe6a.firebaseapp.com",
    databaseURL: "https://transact-bfe6a-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "transact-bfe6a",
    storageBucket: "transact-bfe6a.appspot.com",
    messagingSenderId: "448881526926",
    appId: "1:448881526926:web:9c41d5b0400d70813261df",
    measurementId: "G-Y1S9CCRDKF",
    databaseURL: "https://transact-bfe6a-default-rtdb.asia-southeast1.firebasedatabase.app"
};

firebase.initializeApp(firebaseConfig);

document.addEventListener("DOMContentLoaded", function () {
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("sidebar");
    const tableBody = document.querySelector("tbody");

    if (sidebarToggle) {
        sidebarToggle.addEventListener("click", function () {
            sidebar.classList.toggle("active");
        });
    }

    if (!firebase.apps.length) {
        const firebaseConfig = {
        };


        firebase.initializeApp(firebaseConfig);
    }

    const database = firebase.database();
    const transactRef = database.ref("transact");

 
    function fetchData() {
        transactRef.on("value", function (snapshot) {
            tableBody.innerHTML = "";

            snapshot.forEach(function (childSnapshot) {
                const data = childSnapshot.val();

                const row = tableBody.insertRow();

                const transactionIDCell = row.insertCell(0);
                const amountCell = row.insertCell(1);
                const timestamp = row.insertCell(2);
                const date = row.insertCell(3);

                transactionIDCell.textContent = data.transactionID; 
                amountCell.textContent = data.scannedAmount;
                timestamp.textContent = data.timestamp;
                date.textContent = data.date;
            });
        });
    }

    fetchData();
});
