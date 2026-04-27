/**
 * students/list.js
 * Handles: edit modal, delete confirmation, sidebar links,
 *          submenu auto-open, toast notifications, table search.
 */

// ─── Toast System ─────────────────────────────────────────────
const Toast = {
  _icons: {
    success:
      '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    error:
      '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    warning:
      '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
  },
  _labels: {
    success: "Success",
    error: "Error",
    warning: "Warning",
    info: "Info",
  },

  _getContainer() {
    let c = document.getElementById("toast-container");
    if (!c) {
      c = document.createElement("div");
      c.id = "toast-container";
      document.body.appendChild(c);
    }
    return c;
  },

  show(message, type = "info", duration = 4500) {
    const container = this._getContainer();
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    toast.innerHTML = `
            <div class="toast-icon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${this._icons[type] || this._icons.info}
                </svg>
            </div>
            <div style="flex:1; min-width:0;">
                <p class="toast-label">${this._labels[type] || type}</p>
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close" aria-label="Dismiss">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
        `;

    container.appendChild(toast);
    requestAnimationFrame(() =>
      requestAnimationFrame(() => toast.classList.add("toast-show")),
    );

    const dismiss = () => {
      toast.classList.add("toast-hide");
      toast.addEventListener("transitionend", () => toast.remove(), {
        once: true,
      });
    };

    toast.querySelector(".toast-close").addEventListener("click", dismiss);
    setTimeout(dismiss, duration);
    return toast;
  },

  success(msg, d) {
    return this.show(msg, "success", d);
  },
  error(msg, d) {
    return this.show(msg, "error", d);
  },
  warning(msg, d) {
    return this.show(msg, "warning", d);
  },
  info(msg, d) {
    return this.show(msg, "info", d);
  },
};
window.Toast = Toast;

// ─── Edit Modal ───────────────────────────────────────────────
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

// ─── Delete Confirmation ──────────────────────────────────────
function confirmDeleteStudent(id, name) {
  confirmDeleteWithCallback(id, name, function (itemId) {
    window.location.href = `?action=delete&id=${itemId}`;
  });
}

// ─── Table Search ─────────────────────────────────────────────
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

// ─── Flash → Toast bridge ─────────────────────────────────────
function bridgeFlashAlerts() {
  document.querySelectorAll(".clinic-alert").forEach(function (el) {
    let type = "info";
    const style = el.getAttribute("style") || "";
    if (style.includes("#2d8a6e")) type = "success";
    else if (style.includes("#c62828")) type = "error";
    else if (style.includes("#c88a00")) type = "warning";
    else if (style.includes("#2563eb")) type = "info";

    const msgEl = el.querySelector("p:last-of-type");
    const message = msgEl ? msgEl.textContent.trim() : el.textContent.trim();

    Toast.show(message, type, 5000);
    el.remove();
  });
}

// ─── DOM Ready ────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  bridgeFlashAlerts();
  initSearch();

  // ── Sidebar link correction ────────────────────────────
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

  // ── Auto-open Students submenu ─────────────────────────
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
