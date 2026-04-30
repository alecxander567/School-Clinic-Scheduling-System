function applyFilters() {
  const date = document.getElementById("filterDate").value;
  const status = document.getElementById("filterStatus").value;
  const params = [];
  if (date) params.push("date=" + encodeURIComponent(date));
  if (status) params.push("status=" + encodeURIComponent(status));
  window.location.href =
    window.location.pathname + (params.length ? "?" + params.join("&") : "");
}

function resetFilters() {
  window.location.href = window.location.pathname;
}

function statusBadgeClass(statusName) {
  const s = (statusName || "").toLowerCase();
  if (s.includes("progress")) return "apt-badge--progress";
  if (s.includes("cancel")) return "apt-badge--cancelled";
  if (s.includes("complet") || s.includes("done"))
    return "apt-badge--completed";
  if (s.includes("confirm")) return "apt-badge--confirmed";
  return "apt-badge--scheduled";
}

function deleteAppointment(id, serviceName, btn) {
  confirmDeleteWithCallback(id, serviceName, function (appointmentId) {
    const originalText = btn.textContent;
    btn.textContent = "Deleting...";
    btn.disabled = true;

    fetch("../api/delete-appointment.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "appointment_id=" + appointmentId,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          sessionStorage.setItem("flash_type", "success");
          sessionStorage.setItem(
            "flash_message",
            "Appointment deleted successfully!",
          );
          location.reload();
        } else {
          sessionStorage.setItem("flash_type", "error");
          sessionStorage.setItem(
            "flash_message",
            data.message || "Failed to delete appointment.",
          );
          location.reload();
        }
      })
      .catch((error) => {
        console.error("deleteAppointment error:", error);
        sessionStorage.setItem("flash_type", "error");
        sessionStorage.setItem(
          "flash_message",
          "An error occurred while deleting the appointment.",
        );
        location.reload();
      });
  });
}
