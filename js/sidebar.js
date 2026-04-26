function toggleSubmenu(menuId) {
  const submenu = document.getElementById(menuId);
  const arrow = document.getElementById("arrow-" + menuId);

  if (submenu) {
    if (submenu.classList.contains("hidden")) {
      submenu.classList.remove("hidden");
      if (arrow) arrow.style.transform = "rotate(180deg)";
    } else {
      submenu.classList.add("hidden");
      if (arrow) arrow.style.transform = "rotate(0deg)";
    }
  }
}

// Set active menu based on current URL
document.addEventListener("DOMContentLoaded", function () {
  const currentPath = window.location.pathname.split("/").pop();
  const allLinks = document.querySelectorAll("nav a");

  allLinks.forEach((link) => {
    const href = link.getAttribute("href");
    if (href === currentPath) {
      link.classList.add("bg-blue-600", "text-white");

      // Open parent submenu
      let parent = link.closest(".submenu");
      if (parent) {
        parent.classList.remove("hidden");
        const parentId = parent.id;
        if (parentId) {
          const arrow = document.getElementById("arrow-" + parentId);
          if (arrow) arrow.style.transform = "rotate(180deg)";
        }
      }
    }
  });

  // Close submenus when clicking outside (optional)
  document.addEventListener("click", function (e) {
    if (!e.target.closest("nav")) {
      document.querySelectorAll(".submenu").forEach((submenu) => {
        if (!submenu.classList.contains("hidden")) {
          submenu.classList.add("hidden");
          const arrowId = submenu.id;
          const arrow = document.getElementById("arrow-" + arrowId);
          if (arrow) arrow.style.transform = "rotate(0deg)";
        }
      });
    }
  });

  // Mobile menu toggle
  const mobileMenuButton = document.getElementById("mobileMenuButton");
  const sidebar = document.querySelector("aside");
  const mobileOverlay = document.getElementById("mobileOverlay");

  if (mobileMenuButton && sidebar && mobileOverlay) {
    mobileMenuButton.addEventListener("click", () => {
      sidebar.classList.toggle("-translate-x-full");
      mobileOverlay.classList.toggle("hidden");
    });

    mobileOverlay.addEventListener("click", () => {
      sidebar.classList.add("-translate-x-full");
      mobileOverlay.classList.add("hidden");
    });
  }

  // Handle responsive behavior
  function handleResponsive() {
    if (window.innerWidth >= 1024) {
      if (sidebar) sidebar.classList.remove("-translate-x-full");
      if (mobileOverlay) mobileOverlay.classList.add("hidden");
    } else {
      if (sidebar && !sidebar.classList.contains("-translate-x-full")) {
        sidebar.classList.add("-translate-x-full");
      }
    }
  }

  window.addEventListener("resize", handleResponsive);
  handleResponsive();
});
