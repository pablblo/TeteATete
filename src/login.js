import { showOnlyBlock, toggleModal } from "./functions.js";

document.addEventListener('DOMContentLoaded', () => {
    // Handle form display based on localStorage
    const savedFormId = localStorage.getItem('currentForm');
    showOnlyBlock(savedFormId || "login-container", "form-container");

    // Add click event listeners to navigation links
    document.querySelectorAll(".links a").forEach(link => {
        link.addEventListener("click", event => {
            event.preventDefault();
            const targetId = event.target.getAttribute("target-id");
            if (targetId) {
                showOnlyBlock(targetId, "form-container");
                localStorage.setItem('currentForm', targetId);
            }
        });
    });

    // Handle registration form submission and modal interactions
    const registrationForm = document.getElementById("registrationForm");
    const cguModal = document.getElementById("myCGUModal");

    if (registrationForm && cguModal) {
        registrationForm.addEventListener("submit", event => {
            event.preventDefault();
            toggleModal("myCGUModal", true);
        });
        window.addEventListener("click", event => {
            if (event.target === cguModal) {
                toggleModal("myCGUModal", false);
            }
        });
    }

    // Handle the CGU modal "Confirm" button
    const confirmButton = document.getElementById("myOtherBtn");
    if (confirmButton) {
        confirmButton.addEventListener("click", () => {
            const checkbox = document.getElementById("checkbox");
            if (checkbox && checkbox.checked) {
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "form";
                hiddenInput.value = "register";
                registrationForm.appendChild(hiddenInput);
                registrationForm.submit();
            }
        });
    }
});