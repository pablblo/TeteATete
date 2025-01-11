import { showOnlyBlock } from "./functions.js";

document.addEventListener('DOMContentLoaded', () => {
    const savedFormId = localStorage.getItem('currentForm');
    if (savedFormId) {
        showOnlyBlock(savedFormId, "form-container");
    } else {
        showOnlyBlock("login-container", "form-container");
    }
});

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