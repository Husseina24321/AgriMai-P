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