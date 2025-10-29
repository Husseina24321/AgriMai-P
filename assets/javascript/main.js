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

document.addEventListener("DOMContentLoaded", () => {
    // Sélection du bouton Retour
    const openModalRetour = document.getElementById("openModal2");
    const popupRetour = document.getElementById("popup-retour");
    // Ouvrir la modale
    openModalRetour.addEventListener('click', function(){
        popupRetour.style.display ='block';
    })

    const closeModalRetour = document.querySelector(".js-close-button2");


    // Fermer la modale
    closeModalRetour.addEventListener('click', function(){
        popupRetour.style.display ='none';
    })

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
document.addEventListener("DOMContentLoaded", () => {
    // Ouvrir le formulaire
    const openFormBtn = document.getElementById("openForm");
    const formModal = document.getElementById("popup-form");

    openFormBtn.addEventListener('click', function(){
        formModal.style.display = "block";
    });

    // Fermer le formulaire
    const closeButton = formModal.querySelector('.js-close-button');
    closeButton.addEventListener('click', function(e){
        e.preventDefault();
        formModal.style.display ='none';
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const banner = document.getElementById("cookie-banner");
    const settings = document.getElementById("cookie-settings");
    const openPage = document.getElementById("open-page");
    const acceptBtn = document.getElementById("accept-cookies");
    const rejectBtn = document.getElementById("reject-cookies");
    const form = document.getElementById("cookie-form");
    const acceptAll = document.getElementById("accept-all");
    const rejectAll = document.getElementById("reject-all");

    // Vérifie si l’utilisateur a déjà fait un choix
    const preferences = JSON.parse(localStorage.getItem("cookiePreferences"));

    if (!preferences) {
        banner.style.display = "flex"; // Affiche la bannière
    }

    // Ouvrir les paramètres
    openPage.addEventListener("click", (e) => {
        e.preventDefault();
        banner.style.display = "none";
        settings.style.display = "block";
    });

    // Tout accepter depuis la bannière
    acceptBtn.addEventListener("click", () => {
        savePreferences({ analytics: true, ads: true });
    });

    // Tout refuser depuis la bannière
    rejectBtn.addEventListener("click", () => {
        savePreferences({ analytics: false, ads: false });
    });

    // Tout accepter depuis la page des paramètres
    acceptAll.addEventListener("click", () => {
        form.querySelectorAll('input[type="checkbox"]').forEach((box) => {
            if (!box.disabled) box.checked = true;
        });
    });

    // Tout refuser depuis la page des paramètres
    rejectAll.addEventListener("click", () => {
        form.querySelectorAll('input[type="checkbox"]').forEach((box) => {
            if (!box.disabled) box.checked = false;
        });
    });

    // Soumettre le formulaire
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const analytics = form.querySelector('[name="analytics"]').checked;
        const ads = form.querySelector('[name="ads"]').checked;
        savePreferences({ analytics, ads });
    });

    // Fonction pour sauvegarder les préférences
    function savePreferences(prefs) {
        localStorage.setItem("cookiePreferences", JSON.stringify(prefs));
        banner.style.display = "none";
        settings.style.display = "none";
        alert("Vos préférences ont été enregistrées !");
    }
});




