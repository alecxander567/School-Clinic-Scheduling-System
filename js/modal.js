// js/modal.js - Reusable modal functions

// Close modal by ID
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
  }
}

// Open modal by ID
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "flex";
  }
}

// Close modal when clicking outside
document.addEventListener("click", function (event) {
  if (event.target.classList && event.target.classList.contains("modal")) {
    closeModal(event.target.id);
  }
});

// Escape key closes modals
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    document.querySelectorAll(".modal").forEach((modal) => {
      if (modal.style.display === "flex") {
        closeModal(modal.id);
      }
    });
  }
});
