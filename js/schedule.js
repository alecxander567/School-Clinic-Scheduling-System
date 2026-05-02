function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(
    () => showToast("QR link copied to clipboard!", "success"),
    () => prompt("Press Ctrl+C to copy:", text),
  );
}

function showToast(message, type = "success") {
  const existing = document.getElementById("schedule-toast");
  if (existing) existing.remove();

  const toast = document.createElement("div");
  toast.id = "schedule-toast";
  toast.style.cssText = `
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    background: ${type === "success" ? "#0f6e56" : "#a32d2d"};
    color: #fff; padding: 0.625rem 1.125rem;
    border-radius: 10px; font-size: 0.875rem; font-weight: 500;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    animation: fadeUp 0.3s ease;
    display: flex; align-items: center; gap: 8px;
  `;
  toast.innerHTML = `
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="${
          type === "success" ?
            "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          : "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        }" />
    </svg>
    ${message}
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateY(8px)";
    toast.style.transition = "all 0.3s";
  }, 2800);
  setTimeout(() => toast.remove(), 3200);
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".alert-close").forEach((btn) => {
    btn.addEventListener("click", () => {
      const alert = btn.closest(".alert");
      if (alert) {
        alert.style.transition = "opacity 0.25s, transform 0.25s";
        alert.style.opacity = "0";
        alert.style.transform = "translateY(-6px)";
        setTimeout(() => alert.remove(), 280);
      }
    });
  });

  document.querySelectorAll(".capacity-bar-fill[data-pct]").forEach((bar) => {
    const pct = parseFloat(bar.dataset.pct) || 0;
    bar.style.width = "0%";
    requestAnimationFrame(() => {
      setTimeout(() => {
        bar.style.width = pct + "%";
      }, 100);
    });
  });

  const searchInput = document.getElementById("studentSearch");
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const q = searchInput.value.toLowerCase().trim();
      document.querySelectorAll(".students-table tbody tr").forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = q === "" || text.includes(q) ? "" : "none";
      });
    });
  }
});
