function viewDetails(id) {
  const modal = document.getElementById("detailsModal");
  const modalBody = document.getElementById("modalBody");

  modalBody.innerHTML = `
    <div style="display:flex; align-items:center; justify-content:center; padding:3rem; gap:0.75rem; color:#2d8a6e;">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
      <span style="font-size:0.85rem; color:#2d8a6e;">Loading consultation details…</span>
    </div>
    <style>@keyframes spin{to{transform:rotate(360deg)}}</style>`;

  // Show the backdrop
  modal.style.display = "flex";
  document.body.style.overflow = "hidden";

  const modalBox = modal.querySelector(".modal");
  if (modalBox) {
    modalBox.classList.remove("cs-modal-animate");
    void modalBox.offsetWidth; 
    modalBox.classList.add("cs-modal-animate");
  }

  fetch(`../api/get_consultation_details.php?id=${id}`)
    .then((r) => r.json())
    .then((data) => {
      if (data.success) displayConsultationDetails(data.data);
      else
        modalBody.innerHTML = `
          <div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;padding:3rem;color:#c0504d;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span style="font-size:0.85rem;">${escapeHtml(data.error)}</span>
          </div>`;
    })
    .catch(() => {
      modalBody.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;padding:3rem;color:#c0504d;">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span style="font-size:0.85rem;">Failed to load details. Please try again.</span>
        </div>`;
    });
}

function displayConsultationDetails(d) {
  const modalBody = document.getElementById("modalBody");
  const students = d.student_list ? d.student_list.split("||") : [];

  const section = (icon, label, content) => `
    <div class="cs-section">
      <div class="cs-section-header">
        ${icon}
        <span>${label}</span>
      </div>
      <div class="cs-section-body">${content}</div>
    </div>`;

  const row = (label, value) => `
    <div class="cs-row">
      <span class="cs-label">${label}</span>
      <span class="cs-value">${value}</span>
    </div>`;

  const pill = (text, type = "success") => `
    <span class="cs-pill cs-pill--${type}">${escapeHtml(text)}</span>`;

  const studentItems =
    students.length ?
      students
        .map((s) => {
          const initial = s.trim().charAt(0).toUpperCase();
          return `
          <div class="cs-student-row">
            <div class="cs-avatar">${initial}</div>
            <span>${escapeHtml(s.trim())}</span>
          </div>`;
        })
        .join("")
    : `<p class="cs-empty">No students registered</p>`;

  modalBody.innerHTML = `
    <style>
      .cs-section{background:#f9fbf9;border:0.5px solid #e2ebe6;border-radius:.75rem;overflow:hidden}
      .cs-section-header{display:flex;align-items:center;gap:.4rem;padding:.5rem .875rem;border-bottom:.5px solid #e2ebe6;font-size:.67rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#2d8a6e}
      .cs-section-header svg{flex-shrink:0}
      .cs-section-body{padding:.75rem .875rem;display:flex;flex-direction:column;gap:.4rem}
      .cs-row{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem}
      .cs-label{font-size:.75rem;color:#7a9b8a;flex-shrink:0}
      .cs-value{font-size:.82rem;color:#1a2e25;text-align:right;line-height:1.5}
      .cs-pill{display:inline-flex;align-items:center;font-size:.67rem;font-weight:600;border-radius:9999px;padding:.15rem .6rem;border:.5px solid}
      .cs-pill--success{background:#eaf5f0;color:#2d8a6e;border-color:#c4e5d9}
      .cs-pill--warning{background:#faeeda;color:#854f0b;border-color:#fac775}
      .cs-pill--danger{background:#fcebeb;color:#a32d2d;border-color:#f7c1c1}
      .cs-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
      @media(max-width:480px){.cs-grid{grid-template-columns:1fr}}
      .cs-student-row{display:flex;align-items:center;gap:.5rem;padding:.45rem .625rem;background:#fff;border:.5px solid #e2ebe6;border-radius:.5rem;font-size:.8rem;color:#1a2e25}
      .cs-avatar{flex-shrink:0;width:1.6rem;height:1.6rem;border-radius:50%;background:#eaf5f0;border:.5px solid #c4e5d9;color:#2d8a6e;font-size:.62rem;font-weight:600;display:flex;align-items:center;justify-content:center}
      .cs-empty{font-size:.82rem;color:#9ab8ab;font-style:italic;margin:0}
      .cs-note{font-size:.82rem;color:#1a2e25;line-height:1.65;white-space:pre-wrap;margin:0}
      .cs-timestamp{font-size:.68rem;color:#9ab8ab;display:flex;align-items:center;gap:.3rem;margin:0}
    </style>

    <div style="display:flex;flex-direction:column;gap:.875rem;">

      ${section(
        `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
        "Service",
        row("Name", `<strong>${escapeHtml(d.service_name)}</strong>`) +
          row("Status", pill(d.status_name, getStatusType(d.status_name))) +
          (d.service_description ?
            row("Description", escapeHtml(d.service_description))
          : ""),
      )}

      <div class="cs-grid">
        ${section(
          `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>`,
          "Provider",
          row(
            "Name",
            `<strong>${escapeHtml(d.provider_name || "N/A")}</strong>`,
          ) + row("Specialization", escapeHtml(d.specialization || "N/A")),
        )}
        ${section(
          `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`,
          "Schedule",
          row("Date", `<strong>${formatDate(d.visit_date)}</strong>`) +
            row(
              "Time",
              `${formatTime(d.start_time)} – ${formatTime(d.end_time)}`,
            ),
        )}
      </div>

      <div class="cs-section">
        <div class="cs-section-header">
          <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          <span>Students</span>
          <span class="cs-pill cs-pill--success" style="margin-left:auto;">${d.total_registered || 0} / ${d.max_students}</span>
        </div>
        <div class="cs-section-body">${studentItems}</div>
      </div>

      ${
        d.appointment_notes ?
          section(
            `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>`,
            "Notes",
            `<p class="cs-note">${escapeHtml(d.appointment_notes)}</p>`,
          )
        : ""
      }

      <p class="cs-timestamp">
        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Created: ${formatDateTime(d.created_at)}
      </p>
    </div>`;
}

function getStatusType(status) {
  if (!status) return "success";
  const s = status.toLowerCase();
  if (s.includes("cancel") || s.includes("reject")) return "danger";
  if (s.includes("pending") || s.includes("waiting")) return "warning";
  return "success";
}

function generateReport(id, serviceName) {
  if (confirm(`Generate report for ${serviceName}?`)) {
    window.location.href = `../reports/generate_consultation_report.php?id=${id}`;
  }
}

function closeModal() {
  const modal = document.getElementById("detailsModal");
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "";
  }
}

// Helper functions
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function formatDate(dateString) {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function formatTime(timeString) {
  if (!timeString) return "N/A";
  const [hours, minutes] = timeString.split(":");
  const d = new Date();
  d.setHours(parseInt(hours), parseInt(minutes));
  return d.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}

function formatDateTime(dateTimeString) {
  if (!dateTimeString) return "N/A";
  return new Date(dateTimeString).toLocaleString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}

// Close modal when clicking outside
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("detailsModal");
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });
  }
});
