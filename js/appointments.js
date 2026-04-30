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

function generateQRCode(appointmentId, button) {
  const originalText = button.innerText;
  button.innerText = "Generating...";
  button.disabled = true;

  fetch("/api/generate-qr.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ appointment_id: appointmentId }),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log("QR Response:", data);
      if (data.success) {
        showQRModal(data.qr_url, data.priority_count, appointmentId);
      } else {
        alert("Error: " + data.error);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Failed to generate QR code: " + error.message);
    })
    .finally(() => {
      button.innerText = originalText;
      button.disabled = false;
    });
}

function showQRModal(qrUrl, priorityCount, appointmentId) {
  if (!document.getElementById("qrModalStyles")) {
    const style = document.createElement("style");
    style.id = "qrModalStyles";
    style.textContent = `
      .qr-backdrop {
        position: fixed; inset: 0;
        background: rgba(15,23,18,0.55);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        z-index: 1000;
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
        animation: qrFadeIn 0.2s ease;
      }
      @keyframes qrFadeIn { from { opacity: 0; } to { opacity: 1; } }
      @keyframes qrModalIn {
        from { opacity: 0; transform: translateY(-14px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
      }
      .qr-modal {
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 24px 64px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.08), 0 0 0 1px rgba(45,138,110,0.08);
        width: 100%; max-width: 26rem;
        max-height: 90vh; overflow-y: auto;
        display: flex; flex-direction: column;
        animation: qrModalIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
        font-family: 'DM Sans', system-ui, sans-serif;
        scrollbar-width: none;
        -ms-overflow-style: none;
      }
      .qr-modal::-webkit-scrollbar {
        display: none;
      }
      .qr-modal-header {
        display: flex; align-items: flex-start; gap: 0.75rem;
        padding: 1.25rem 1.5rem;
      }
      .qr-modal-header-icon {
        flex-shrink: 0; width: 2.25rem; height: 2.25rem;
        border-radius: 0.6rem; background: #eaf5f0; border: 1px solid #c4e5d9;
        display: flex; align-items: center; justify-content: center; color: #2d8a6e;
      }
      .qr-modal-title { font-size: 0.95rem; font-weight: 600; color: #1a2e25; margin: 0; line-height: 1.3; }
      .qr-modal-subtitle { font-size: 0.72rem; color: #7a9b8a; margin: 0.15rem 0 0; }
      .qr-modal-close {
        flex-shrink: 0; margin-left: auto; width: 2rem; height: 2rem;
        border-radius: 0.4rem; border: 1px solid #e4ede7; background: #f6faf7; color: #627a6e;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: background 0.15s, color 0.15s, border-color 0.15s;
      }
      .qr-modal-close:hover { background: #fce8e8; border-color: #f0b8b8; color: #c0504d; }
      .qr-divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #e4ede7 20%, #e4ede7 80%, transparent);
        flex-shrink: 0;
      }
      .qr-modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
      .qr-code-wrap {
        display: flex; justify-content: center; align-items: center;
        background: #f3f9f6; border: 1px solid #daeee5;
        border-radius: 0.75rem; padding: 1.25rem;
        min-height: 220px;
      }
      .qr-url-box {
        background: #f3f9f6; border: 1px solid #daeee5;
        border-radius: 0.75rem; padding: 0.75rem 1rem;
      }
      .qr-url-label {
        font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: #2d8a6e; margin: 0 0 0.35rem;
        display: flex; align-items: center; gap: 0.3rem;
      }
      .qr-url-text {
        font-size: 0.72rem; color: #2c4b3e; word-break: break-all;
        font-family: monospace; line-height: 1.5;
      }
      .qr-queue-strip {
        display: flex; align-items: center; gap: 0.875rem;
        background: #f3f9f6; border: 1px solid #daeee5;
        border-radius: 0.75rem; padding: 0.75rem 1rem;
      }
      .qr-priority-badge {
        flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: 0.75rem;
        background: #2d8a6e; color: #fff;
        font-size: 1rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
      }
      .qr-queue-label { font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #2d8a6e; margin: 0 0 0.2rem; }
      .qr-queue-value { font-size: 0.82rem; color: #2c4b3e; margin: 0; }
      .qr-modal-footer {
        padding: 1rem 1.5rem;
        display: flex; gap: 0.5rem;
      }
      .qr-btn {
        flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;
        padding: 0.5rem 0.75rem; border-radius: 0.55rem;
        font-size: 0.78rem; font-weight: 600; font-family: 'DM Sans', system-ui, sans-serif;
        cursor: pointer; transition: all 0.18s ease; border: 1.5px solid transparent; white-space: nowrap;
      }
      .qr-btn-primary { background: #2d8a6e; color: #fff; border-color: #2d8a6e; }
      .qr-btn-primary:hover { background: #236b56; border-color: #236b56; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(45,138,110,0.3); }
      .qr-btn-secondary { background: #f0f7f3; color: #2d6b52; border-color: #c8dfd4; }
      .qr-btn-secondary:hover { background: #e0f0e8; border-color: #2d8a6e; }
      .qr-btn-ghost { background: #f4f8f5; color: #456b5b; border-color: #d4e6dd; }
      .qr-btn-ghost:hover { background: #e8f2ec; border-color: #b8d8ca; }
      .qr-tip {
        text-align: center;
        font-size: 0.68rem; color: #9ab8ab; padding: 0 1.5rem 1rem;
      }
    `;
    document.head.appendChild(style);
  }

  const modalHtml = `
    <div id="qrModal" class="qr-backdrop" onclick="if(event.target===this) closeQRModal()">
      <div class="qr-modal">

        <div class="qr-modal-header">
          <div class="qr-modal-header-icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
          </div>
          <div style="min-width:0;flex:1">
            <p class="qr-modal-title">Appointment QR Code</p>
            <p class="qr-modal-subtitle">Scan to open the registration form</p>
          </div>
          <button class="qr-modal-close" onclick="closeQRModal()" aria-label="Close">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="qr-divider"></div>

        <div class="qr-modal-body">

          <div class="qr-code-wrap" id="qrCodeContainer">
            <div style="color:#9ab8ab;font-size:0.8rem">Generating QR code…</div>
          </div>

          <div class="qr-url-box">
            <p class="qr-url-label">
              <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
              </svg>
              Registration URL
            </p>
            <p class="qr-url-text">${qrUrl}</p>
          </div>

          <div class="qr-queue-strip">
            <div class="qr-priority-badge">#${priorityCount + 1}</div>
            <div>
              <p class="qr-queue-label">Next Priority Number</p>
              <p class="qr-queue-value">${priorityCount} student${priorityCount !== 1 ? "s" : ""} currently in queue</p>
            </div>
          </div>

        </div>

        <div class="qr-divider"></div>

        <div class="qr-modal-footer">
          <button class="qr-btn qr-btn-secondary" onclick="copyToClipboard('${qrUrl}')">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Copy URL
          </button>
          <button class="qr-btn qr-btn-primary" onclick="printQR()">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print QR
          </button>
          <button class="qr-btn qr-btn-ghost" onclick="closeQRModal()">
            Close
          </button>
        </div>

        <p class="qr-tip">💡 Students can also open the form by visiting the URL directly</p>

      </div>
    </div>
  `;

  document.body.insertAdjacentHTML("beforeend", modalHtml);

  if (typeof QRCode !== "undefined") {
    const qrContainer = document.getElementById("qrCodeContainer");
    qrContainer.innerHTML = "";
    new QRCode(qrContainer, {
      text: qrUrl,
      width: 180,
      height: 180,
      colorDark: "#1a2e25",
      colorLight: "#f3f9f6",
    });
  } else {
    document.getElementById("qrCodeContainer").innerHTML =
      `<p style="color:#c0504d;font-size:0.8rem">QR library not loaded.<br><span style="color:#9ab8ab">${qrUrl}</span></p>`;
  }
}

function copyToClipboard(text) {
  const btn = document.querySelector(".qr-btn-secondary");
  const originalHTML = btn.innerHTML;

  navigator.clipboard
    .writeText(text)
    .then(() => {
      btn.innerHTML = `
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Copied!`;
      setTimeout(() => {
        btn.innerHTML = originalHTML;
      }, 2000);
    })
    .catch(() => {
      btn.textContent = "Failed to copy";
      setTimeout(() => {
        btn.innerHTML = originalHTML;
      }, 2000);
    });
}

function openQRUrl(url) {
  window.open(url, "_blank");
}

function closeQRModal() {
  const modal = document.getElementById("qrModal");
  if (modal) modal.remove();
}

function printQR() {
  const canvas = document.querySelector("#qrCodeContainer canvas");
  if (!canvas) {
    alert("QR code not ready yet.");
    return;
  }

  const dataUrl = canvas.toDataURL();
  const win = window.open("", "_blank");
  win.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Print QR Code</title>
        <style>
          body { margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; font-family: 'DM Sans', system-ui, sans-serif; }
          img { width: 220px; height: 220px; }
          p { font-size: 11px; color: #2c4b3e; margin-top: 10px; word-break: break-all; text-align: center; max-width: 260px; }
        </style>
      </head>
      <body>
        <img src="${dataUrl}" />
        <p>${document.querySelector(".qr-url-text")?.textContent ?? ""}</p>
        <script>window.onload = () => { window.print(); window.close(); }<\/script>
      </body>
    </html>
  `);
  win.document.close();
}

function downloadQR() {
  const qrContainer = document.querySelector("#qrCodeContainer canvas");
  if (qrContainer) {
    const link = document.createElement("a");
    link.download = "qrcode.png";
    link.href = qrContainer.toDataURL();
    link.click();
  } else {
    alert("Cannot download QR code");
  }
}

function syncCardCapacity(aptId, filled, max) {
  const pct = Math.min(100, max > 0 ? Math.round((filled / max) * 100) : 0);
  const isFull = filled >= max;

  const fill = document.getElementById("cap-fill-" + aptId);
  const label = document.getElementById("cap-label-" + aptId);
  const pill = document.getElementById("next-priority-" + aptId);
  const qrBtn = document.getElementById("qr-btn-" + aptId);

  if (fill) {
    fill.style.width = pct + "%";
    fill.classList.toggle("cap-fill--full", isFull);
  }

  if (label) {
    label.innerHTML = "<strong>" + filled + " / " + max + "</strong> students";
  }

  if (pill) {
    if (isFull) {
      pill.classList.add("next-priority--closed");
      pill.innerHTML = `
        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
        Fully booked`;
    } else {
      pill.classList.remove("next-priority--closed");
      pill.innerHTML = `
        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
        </svg>
        Next priority: <span>#${filled + 1}</span>`;
    }
  }

  if (qrBtn) {
    qrBtn.disabled = isFull;
  }
}

(function startCapacityPoller() {
  function getVisibleIds() {
    return Array.from(document.querySelectorAll("[id^='apt-card-']"))
      .map((el) => el.id.replace("apt-card-", ""))
      .filter(Boolean);
  }

  function pollAll() {
    const ids = getVisibleIds();
    if (!ids.length) return;

    ids.forEach((id) => {
      fetch(`../api/get-appointment.php?id=${id}`)
        .then((r) => r.json())
        .then((data) => {
          if (data.success && data.appointment) {
            const apt = data.appointment;
            const filled = parseInt(apt.registered_students || 0);
            const max = parseInt(apt.max_students || 1);
            syncCardCapacity(apt.id, filled, max);
          }
        })
        .catch(() => {});
    });
  }

  // Run immediately on load, then every 15 seconds
  pollAll();
  setInterval(pollAll, 15000);
})();
