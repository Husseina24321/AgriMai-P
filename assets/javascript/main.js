document.addEventListener("DOMContentLoaded", () => {
    const openTheModal = document.getElementById("openModal");
    const  modalBtn= document.getElementById("popup-livraison");
    openTheModal.addEventListener('click', function(){
        modalBtn.style.display = "block";
    });
    const closeButton = document.querySelector('.js-close-button');
    // const laCroix = document.querySelector('.close-button');
    closeButton.addEventListener('click', function(){
        modalBtn.style.display ='none';
    });

});

document.addEventListener("DOMContentLoaded", function () {
    const lotSelect = document.querySelector(".lots");
    const lotButtons = document.querySelectorAll(".productDetail-lot-btn");
    const hiddenLotInput = document.getElementById("selected_lot");
    const messageTextarea = document.getElementById("message");

    function updateSelectedLot(value) {
        hiddenLotInput.value = value;
        if (messageTextarea) {
            messageTextarea.value = value
                ? `Bonjour, je souhaite commander ${value} de votre produit.`
                : "";
        }
    }

    // Quand on choisit dans le <select>
    if (lotSelect) {
        lotSelect.addEventListener("change", function () {
            updateSelectedLot(this.value);
        });
    }

    // Quand on clique sur les boutons 2Kg / 5Kg
    lotButtons.forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const value = this.textContent.trim().toLowerCase(); // "2kg" ou "5kg"
            if (lotSelect) lotSelect.value = value; // synchronise le <select>
            updateSelectedLot(value);
        });
    });
});
