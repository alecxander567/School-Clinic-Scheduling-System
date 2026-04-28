/**
 * health-record.js
 * Handles: flash alerts (via toast), URL cleanup, search filtering,
 *          view/edit modal population, delete confirmation.
 */

document.addEventListener("DOMContentLoaded", function () {
  if (window.location.search.includes("saved=1")) {
    var url = new URL(window.location.href);
    url.searchParams.delete("saved");
    window.history.replaceState({}, document.title, url.toString());
  }

  var searchInput = document.getElementById("healthRecordSearch");
  var clearBtn = document.getElementById("searchClear");
  var grid = document.getElementById("recordsGrid");

  if (!searchInput || !grid) return;

  function filterCards(query) {
    var q = query.trim().toLowerCase();
    var cards = grid.querySelectorAll(".health-record-card");
    var visible = 0;

    cards.forEach(function (card) {
      var text = card.textContent.toLowerCase();
      var show = q === "" || text.includes(q);
      card.style.display = show ? "" : "none";
      if (show) visible++;
    });

    /* No-results message */
    var noResults = document.getElementById("hr-no-results");

    if (visible === 0 && q !== "") {
      if (!noResults) {
        noResults = document.createElement("div");
        noResults.id = "hr-no-results";
        noResults.className = "hr-no-results fade-in";
        noResults.innerHTML =
          '<svg class="hr-empty-icon w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
          '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>' +
          "</svg>" +
          '<p class="hr-empty-title">No records found</p>' +
          '<p class="hr-empty-subtitle">Try a different name, student number, or course</p>';
        grid.appendChild(noResults);
      }
      noResults.style.display = "";
    } else if (noResults) {
      noResults.style.display = "none";
    }
  }

  searchInput.addEventListener("input", function () {
    filterCards(this.value);
    clearBtn.classList.toggle("visible", this.value.length > 0);
  });

  clearBtn.addEventListener("click", function () {
    searchInput.value = "";
    clearBtn.classList.remove("visible");
    filterCards("");
    searchInput.focus();
  });
});

function openViewModal(record) {
  var first = record.first_name || "";
  var last = record.last_name || "";
  var name = (first + " " + last).trim();

  document.getElementById("view-subtitle").textContent = name;
  document.getElementById("view-avatar").textContent = (
    first.charAt(0) + last.charAt(0)
  ).toUpperCase();
  document.getElementById("view-name").textContent = name;

  document.getElementById("view-meta").innerHTML =
    pill(record.student_number || "—") +
    pill(record.course || "—") +
    pill("Year " + (record.year_level || "—"));

  var allergyEl = document.getElementById("view-allergies");
  var hasAllergy = record.allergies && record.allergies.trim();
  allergyEl.textContent =
    hasAllergy ? record.allergies.trim() : "None reported";
  allergyEl.className =
    "hr-view-content" + (hasAllergy ? "" : " hr-view-content--empty");

  var historyEl = document.getElementById("view-history");
  var hasHistory = record.medical_history && record.medical_history.trim();
  historyEl.textContent =
    hasHistory ? record.medical_history.trim() : "No medical history recorded";
  historyEl.className =
    "hr-view-content" + (hasHistory ? "" : " hr-view-content--empty");

  var date =
    record.created_at ?
      new Date(record.created_at).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : "—";
  document.getElementById("view-timestamp").innerHTML =
    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;">' +
    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
    "</svg>Record created " +
    date;

  openModal("viewHealthRecordModal");
}

function openEditModal(record) {
  var first = record.first_name || "";
  var last = record.last_name || "";

  document.getElementById("edit_record_id").value = record.id;
  document.getElementById("edit_student_id").value = record.student_id;

  document.getElementById("edit-avatar").textContent = (
    first.charAt(0) + last.charAt(0)
  ).toUpperCase();
  document.getElementById("edit-student-name").textContent = (
    first +
    " " +
    last
  ).trim();

  document.getElementById("edit-student-meta").innerHTML =
    pill(record.student_number || "—") +
    pill(record.course || "—") +
    pill("Year " + (record.year_level || "—"));

  document.getElementById("edit_allergies").value = record.allergies || "";
  document.getElementById("edit_medical_history").value =
    record.medical_history || "";

  openModal("editHealthRecordModal");
}

function deleteRecord(id, studentName) {
  confirmDeleteWithCallback(id, studentName, function (recordId) {
    window.location.href = "?action=delete&id=" + recordId;
  });
}

function pill(text) {
  return '<span class="hr-meta-pill">' + escapeHtml(text) + "</span>";
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
