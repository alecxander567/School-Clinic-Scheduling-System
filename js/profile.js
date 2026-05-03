document.addEventListener("DOMContentLoaded", () => {
  initPasswordStrength();
  initPasswordValidation();
  initInputFocusEffects();
});

function initPasswordStrength() {
  const input = document.getElementById("new_password");
  const segments = document.querySelectorAll(".strength-bar-segment");
  const label = document.querySelector(".strength-label");
  if (!input || !segments.length) return;

  input.addEventListener("input", () => {
    const val = input.value;
    const score = getPasswordScore(val);
    renderStrength(score, segments, label);
  });
}

function getPasswordScore(password) {
  if (!password) return 0;
  let score = 0;
  if (password.length >= 6) score++;
  if (password.length >= 10) score++;
  if (/[A-Z]/.test(password)) score++;
  if (/[0-9]/.test(password)) score++;
  if (/[^A-Za-z0-9]/.test(password)) score++;
  return Math.min(score, 4);
}

function renderStrength(score, segments, label) {
  const levels = [
    { color: "transparent", text: "" },
    { color: "#e07070", text: "Weak" },
    { color: "#e8a84d", text: "Fair" },
    { color: "#6abf8a", text: "Good" },
    { color: "#2d8a6e", text: "Strong" },
  ];

  const level = levels[score];

  segments.forEach((seg, i) => {
    seg.style.background = i < score ? level.color : "var(--color-border)";
  });

  if (label) {
    label.textContent = level.text;
    label.style.color = score > 0 ? level.color : "var(--color-text-soft)";
  }
}

function initPasswordValidation() {
  const confirmInput = document.getElementById("confirm_password");
  const newInput = document.getElementById("new_password");
  if (!confirmInput || !newInput) return;

  const check = () => {
    const match = !confirmInput.value || confirmInput.value === newInput.value;
    confirmInput.style.borderColor = match ? "" : "#e07070";
    confirmInput.style.boxShadow =
      match ? "" : "0 0 0 3px rgba(224,112,112,0.1)";
  };

  confirmInput.addEventListener("input", check);
  newInput.addEventListener("input", check);
}

function validatePasswordForm() {
  const newPw = document.getElementById("new_password").value;
  const confPw = document.getElementById("confirm_password").value;

  if (newPw !== confPw) {
    showInlineError("confirm_password", "Passwords do not match.");
    return false;
  }
  if (newPw.length < 6) {
    showInlineError("new_password", "Password must be at least 6 characters.");
    return false;
  }
  return true;
}

function showInlineError(inputId, message) {
  const input = document.getElementById(inputId);
  if (!input) return;

  input.focus();
  input.style.borderColor = "#e07070";
  input.style.boxShadow = "0 0 0 3px rgba(224,112,112,0.1)";

  let err = input.parentElement.querySelector(".inline-error");
  if (!err) {
    err = document.createElement("p");
    err.className = "inline-error";
    err.style.cssText = "font-size:11px;color:#a32d2d;margin-top:4px;";
    input.parentElement.appendChild(err);
  }
  err.textContent = message;

  input.addEventListener(
    "input",
    () => {
      input.style.borderColor = "";
      input.style.boxShadow = "";
      if (err) err.remove();
    },
    { once: true },
  );
}

function initInputFocusEffects() {
  document.querySelectorAll(".form-input").forEach((input) => {
    const label = input.previousElementSibling;
    if (!label || !label.classList.contains("form-label")) return;

    input.addEventListener("focus", () => {
      label.style.color = "var(--color-primary)";
    });
    input.addEventListener("blur", () => {
      label.style.color = "";
    });
  });
}
