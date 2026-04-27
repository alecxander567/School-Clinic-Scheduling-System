/**
 * students/add.js
 * Handles: sidebar link correction, submenu auto-open,
 *          client-side form validation with dynamic alerts.
 * All functionality preserved from add.php inline scripts.
 */

document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll("nav a, .sidebar a, aside a")
    .forEach(function (link) {
      const href = link.getAttribute("href");
      if (
        !href ||
        href.startsWith("http") ||
        href.startsWith("#") ||
        href.startsWith("/")
      )
        return;

      if (href === "dashboard.php" || href === "logout.php") {
        link.setAttribute("href", "../" + href);
      } else if (href === "students/add.php" || href === "add.php") {
        link.setAttribute("href", "add.php");
      } else if (href === "students/list.php" || href === "list.php") {
        link.setAttribute("href", "list.php");
      } else if (!href.includes("../") && !href.startsWith("students/")) {
        link.setAttribute("href", "../" + href);
      }
    });

  const studentPages = ["list.php", "add.php"];
  const currentPage = window.location.pathname.split("/").pop();

  if (studentPages.includes(currentPage)) {
    document
      .querySelectorAll('a[href="list.php"], a[href="add.php"]')
      .forEach(function (link) {
        const parentSubmenu = link.closest(".submenu");
        if (parentSubmenu && parentSubmenu.classList.contains("hidden")) {
          parentSubmenu.classList.remove("hidden");
          const parentMenuId = parentSubmenu.id;
          if (parentMenuId) {
            const arrow = document.getElementById("arrow-" + parentMenuId);
            if (arrow) arrow.style.transform = "rotate(180deg)";
          }
        }
      });
  }

  const form = document.getElementById("studentForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    const studentNumber = form
      .querySelector('input[name="student_number"]')
      .value.trim();
    const firstName = form
      .querySelector('input[name="first_name"]')
      .value.trim();
    const lastName = form.querySelector('input[name="last_name"]').value.trim();
    const course = form.querySelector('select[name="course"]').value;
    const yearLevel = form.querySelector('select[name="year_level"]').value;
    const contactNumber = form
      .querySelector('input[name="contact_number"]')
      .value.trim();

    if (
      !studentNumber ||
      !firstName ||
      !lastName ||
      !course ||
      !yearLevel ||
      !contactNumber
    ) {
      e.preventDefault();
      if (window.alertManager) {
        window.alertManager.error("Please fill in all required fields.");
      }
      return;
    }

    // Visual feedback: disable button while submitting
    const submitBtn = document.getElementById("submitBtn");
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Saving…
            `;
    }
  });
});
