/* calendar.js – Appointment Calendar page interactions */

function openSidebar() {
  document.getElementById("sidebar").style.transform = "translateX(0)";
  document.getElementById("overlay").classList.remove("hidden");
}

function closeSidebar() {
  document.getElementById("sidebar").style.transform = "translateX(-100%)";
  document.getElementById("overlay").classList.add("hidden");
}

function toggleSubmenu(id) {
  const el = document.getElementById(id);
  const arrow = document.getElementById("arrow-" + id);
  const hidden = el.classList.contains("hidden");
  el.classList.toggle("hidden", !hidden);
  if (arrow) {
    arrow.style.transform = hidden ? "rotate(180deg)" : "";
  }
}

function confirmDelete(id, redirectDate, redirectMonth) {
  const btn = document.getElementById("deleteConfirmBtn");
  const base = btn.dataset.baseUrl;
  btn.href =
    base +
    "?id=" +
    id +
    "&redirect_date=" +
    encodeURIComponent(redirectDate) +
    "&redirect_month=" +
    encodeURIComponent(redirectMonth);
  document.getElementById("deleteModal").classList.add("is-open");
}

function closeDeleteModal() {
  document.getElementById("deleteModal").classList.remove("is-open");
}

document.addEventListener("DOMContentLoaded", function () {
  /* Close delete modal when clicking the backdrop */
  const modal = document.getElementById("deleteModal");
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === this) closeDeleteModal();
    });
  }

  /* Close sidebar overlay on resize to desktop */
  window.addEventListener("resize", function () {
    if (window.innerWidth >= 1024) {
      closeSidebar();
    }
  });
});
