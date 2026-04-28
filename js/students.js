/**
 * students/list.js
 * Handles: edit modal, delete confirmation, sidebar links,
 *          submenu auto-open, table search.
 */

function openEditModal(student) {
  document.getElementById("editStudentModal_id").value = student.id;
  document.getElementById("student_number").value = student.student_number;
  document.getElementById("first_name").value = student.first_name;
  document.getElementById("last_name").value = student.last_name;
  document.getElementById("contact_number").value = student.contact_number;
  document.getElementById("course").value = student.course;
  document.getElementById("year_level").value = student.year_level;
  openModal("editStudentModal");
}

function confirmDeleteStudent(id, name) {
  confirmDeleteWithCallback(id, name, function (itemId) {
    window.location.href = `?action=delete&id=${itemId}`;
  });
}

function initSearch() {
  const input = document.getElementById("studentSearch");
  const clearBtn = document.getElementById("searchClear");
  const tbody = document.querySelector(".simple-table tbody");
  if (!input || !tbody) return;

  const allRows = Array.from(tbody.querySelectorAll("tr")).filter(
    (r) => !r.querySelector("[colspan]"),
  );
  let noResultsRow = null;

  function filterTable() {
    const q = input.value.trim().toLowerCase();
    clearBtn.classList.toggle("visible", q.length > 0);

    if (noResultsRow) {
      noResultsRow.remove();
      noResultsRow = null;
    }

    let visibleCount = 0;
    allRows.forEach(function (row) {
      const match = !q || row.textContent.toLowerCase().includes(q);
      row.style.display = match ? "" : "none";
      if (match) visibleCount++;
    });

    if (q && visibleCount === 0) {
      noResultsRow = document.createElement("tr");
      noResultsRow.className = "no-results-row";
      noResultsRow.innerHTML = `
        <td colspan="8">
          <div class="no-results-inner">
            <svg class="w-8 h-8" style="opacity:0.35;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
            <p style="font-size:0.82rem; font-weight:600; color:#2c4b3e;">No results for "${escapeHtml(input.value)}"</p>
            <p style="font-size:0.72rem; color:#8bae9d;">Try a different name, course, or student number.</p>
          </div>
        </td>`;
      tbody.appendChild(noResultsRow);
    }

    const footerEl = document.querySelector(".simple-footer p:first-child");
    if (footerEl) {
      const total = allRows.length;
      footerEl.innerHTML =
        q ?
          `Showing <strong>${visibleCount}</strong> of <strong>${total}</strong> student${total !== 1 ? "s" : ""}`
        : `Showing <strong>${total}</strong> student${total !== 1 ? "s" : ""}`;
    }
  }

  input.addEventListener("input", filterTable);
  clearBtn.addEventListener("click", function () {
    input.value = "";
    filterTable();
    input.focus();
  });
}

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, function (c) {
    return {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
    }[c];
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initSearch();

  const isInSubfolder = window.location.pathname.includes("/students/");
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
        link.setAttribute(
          "href",
          isInSubfolder ? "add.php" : "students/add.php",
        );
      } else if (href === "students/list.php" || href === "list.php") {
        link.setAttribute(
          "href",
          isInSubfolder ? "list.php" : "students/list.php",
        );
      } else if (!href.includes("../") && !href.startsWith("students/")) {
        if (isInSubfolder) link.setAttribute("href", "../" + href);
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
});
