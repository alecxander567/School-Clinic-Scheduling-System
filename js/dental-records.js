/* dental-records.js — standalone script for dental-records.php */

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.style.display = "flex";
  document.body.style.overflow = "hidden";

  // trap focus inside modal
  const focusable = modal.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
  );
  if (focusable.length) focusable[0].focus();
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.style.display = "none";
  document.body.style.overflow = "";
}

/* Close on backdrop click */
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("hr-modal-backdrop")) {
    e.target.style.display = "none";
    document.body.style.overflow = "";
  }
});

/* Close on Escape */
document.addEventListener("keydown", function (e) {
  if (e.key !== "Escape") return;
  document.querySelectorAll(".hr-modal-backdrop").forEach(function (modal) {
    if (modal.style.display !== "none") closeModal(modal.id);
  });
});

function openViewModal(record) {
  const modal = document.getElementById("viewDentalRecordModal");
  if (!modal) return;

  var first = record.first_name || "";
  var last = record.last_name || "";

  document.getElementById("view-name").textContent = first + " " + last;
  document.getElementById("view-avatar").textContent = (
    first.charAt(0) + last.charAt(0)
  ).toUpperCase();
  document.getElementById("view-meta").textContent =
    (record.student_number || "") +
    " \u2022 " +
    (record.course || "N/A") +
    " \u2022 Year " +
    (record.year_level || "N/A");

  document.getElementById("view-visit-date").textContent = formatDate(
    record.visit_date,
  );
  document.getElementById("view-diagnosis").textContent =
    record.diagnosis || "No diagnosis recorded";
  document.getElementById("view-treatment").textContent =
    record.treatment || "No treatment recorded";
  document.getElementById("view-procedures").textContent =
    record.procedures || "No additional procedures recorded";

  openModal("viewDentalRecordModal");
}

function openEditModal(record) {
  document.getElementById("edit_visit_id").value = record.visit_id;
  document.getElementById("edit_visit_date").value = (
    record.visit_date || ""
  ).replace(" ", "T");
  document.getElementById("edit_diagnosis").value = record.diagnosis || "";
  document.getElementById("edit_treatment").value = record.treatment || "";

  var subtitle = document.getElementById("edit-subtitle");
  if (subtitle) {
    subtitle.textContent =
      "Editing record for " +
      (record.first_name || "") +
      " " +
      (record.last_name || "");
  }

  /* rebuild procedures list */
  var container = document.getElementById("edit-procedures-container");
  container.innerHTML = "";
  editProcedureCount = 0;

  if (record.procedure_names) {
    var names = record.procedure_names.split("||");
    var descriptions =
      record.procedure_descriptions ?
        record.procedure_descriptions.split("||")
      : [];
    names.forEach(function (name, i) {
      if (name) addEditProcedure(name, descriptions[i] || "");
    });
  }

  openModal("editDentalRecordModal");
}

function deleteRecord(id, studentName) {
  var modal = document.getElementById("deleteConfirmModal");
  if (modal) {
    /* update the modal's student name label if present */
    var nameEl = modal.querySelector("[data-student-name]");
    if (nameEl) nameEl.textContent = studentName;

    /* store id so confirm button can use it */
    modal.dataset.deleteId = id;
    openModal("deleteConfirmModal");
  } else {
    /* fallback */
    if (confirm("Delete dental record for " + studentName + "?")) {
      window.location.href = "dental-records.php?action=delete&id=" + id;
    }
  }
}

/* Called by the confirm button inside DeleteConfirm modal */
function confirmDelete() {
  var modal = document.getElementById("deleteConfirmModal");
  if (!modal) return;
  var id = modal.dataset.deleteId;
  if (id) window.location.href = "dental-records.php?action=delete&id=" + id;
}

function formatDate(dateString) {
  if (!dateString) return "N/A";
  var date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

(function () {
  var btn = document.getElementById("mobileMenuButton");
  var sidebar = document.getElementById("sidebar");
  var overlay = document.getElementById("mobileOverlay");
  if (!btn || !sidebar) return;

  function openSidebar() {
    sidebar.style.transform = "translateX(0)";
    if (overlay) overlay.classList.remove("hidden");
  }

  function closeSidebar() {
    sidebar.style.transform = "";
    if (overlay) overlay.classList.add("hidden");
  }

  btn.addEventListener("click", function () {
    var open = sidebar.style.transform === "translateX(0)";
    open ? closeSidebar() : openSidebar();
  });

  if (overlay) overlay.addEventListener("click", closeSidebar);
})();

function toggleSubmenu(itemId) {
  var list = document.getElementById(itemId);
  var arrow = document.getElementById("arrow-" + itemId);
  if (!list) return;

  var isHidden = list.classList.contains("hidden");
  list.classList.toggle("hidden", !isHidden);
  if (arrow) arrow.style.transform = isHidden ? "rotate(180deg)" : "";
}
