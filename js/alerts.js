// js/alerts.js - For dynamic alerts via AJAX

class AlertManager {
  constructor() {
    this.alerts = [];
    this.container = null;
  }

  // Initialize alert container
  init(containerId = "alert-container") {
    this.container = document.getElementById(containerId);
    if (!this.container) {
      // Create container if it doesn't exist
      this.container = document.createElement("div");
      this.container.id = containerId;
      const mainContent = document.querySelector("main");
      if (mainContent && mainContent.firstChild) {
        mainContent.insertBefore(this.container, mainContent.firstChild);
      }
    }
    return this;
  }

  // Show a new alert
  show(message, type = "info", dismissible = true, autoDismiss = 5000) {
    const alertId =
      "alert-" + Date.now() + "-" + Math.random().toString(36).substr(2, 9);
    const alertHtml = this.createAlertHtml(message, type, dismissible, alertId);

    if (this.container) {
      this.container.insertAdjacentHTML("beforeend", alertHtml);
      const alertElement = document.getElementById(alertId);
      this.alerts.push(alertId);

      // Auto dismiss after specified time
      if (autoDismiss) {
        setTimeout(() => {
          this.dismiss(alertId);
        }, autoDismiss);
      }
    }

    return alertId;
  }

  // Create alert HTML
  createAlertHtml(message, type, dismissible, alertId) {
    const icons = {
      success:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
      error:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
      warning:
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
      info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    };

    const colors = {
      success: {
        bg: "#e6f4ea",
        border: "#b8e0cc",
        text: "#1e7b5c",
        icon: "#2d8a6e",
      },
      error: { bg: "#fee", border: "#fcc", text: "#c62828", icon: "#e07070" },
      warning: {
        bg: "#fff3e0",
        border: "#ffe0b2",
        text: "#e65100",
        icon: "#ff9800",
      },
      info: {
        bg: "#e3f2fd",
        border: "#bbdefb",
        text: "#1565c0",
        icon: "#2196f3",
      },
    };

    const color = colors[type] || colors.info;
    const icon = icons[type] || icons.info;
    const dismissBtn =
      dismissible ?
        `
            <button onclick="window.alertManager.dismiss('${alertId}')" class="flex-shrink-0 ml-3" style="color: ${color.text};">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `
      : "";

    return `
            <div id="${alertId}" class="alert mb-4 p-4 rounded-lg flex items-start justify-between animate-slide-down"
                 style="background: ${color.bg}; border: 1px solid ${color.border};">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="${color.icon}" viewBox="0 0 24 24">
                        ${icon}
                    </svg>
                    <div style="color: ${color.text};" class="text-sm">${this.escapeHtml(message)}</div>
                </div>
                ${dismissBtn}
            </div>
        `;
  }

  // Dismiss a specific alert
  dismiss(alertId) {
    const alertElement = document.getElementById(alertId);
    if (alertElement) {
      alertElement.style.opacity = "0";
      alertElement.style.transform = "translateX(100%)";
      setTimeout(() => {
        alertElement.remove();
      }, 300);
    }
    this.alerts = this.alerts.filter((id) => id !== alertId);
  }

  // Dismiss all alerts
  dismissAll() {
    this.alerts.forEach((alertId) => this.dismiss(alertId));
  }

  // Helper to escape HTML
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Convenience methods
  success(message, autoDismiss = 5000) {
    return this.show(message, "success", true, autoDismiss);
  }

  error(message, autoDismiss = 5000) {
    return this.show(message, "error", true, autoDismiss);
  }

  warning(message, autoDismiss = 5000) {
    return this.show(message, "warning", true, autoDismiss);
  }

  info(message, autoDismiss = 5000) {
    return this.show(message, "info", true, autoDismiss);
  }
}

// Initialize global alert manager
window.alertManager = new AlertManager();
document.addEventListener("DOMContentLoaded", () => {
  window.alertManager.init();
});
