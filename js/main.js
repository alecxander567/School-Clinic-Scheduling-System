document.addEventListener("DOMContentLoaded", function () {
  // Authentication form toggle between Login and Signup
  const loginForm = document.getElementById("login-form");
  const signupForm = document.getElementById("signup-form");
  const tabBtns = document.querySelectorAll(".auth-tab-btn");

  if (tabBtns.length > 0) {
    tabBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const tabId = this.getAttribute("data-tab");

        // Update button styles
        tabBtns.forEach((btn) => {
          btn.classList.remove("text-blue-600", "border-blue-600");
          btn.classList.add("text-gray-500");
          btn.style.borderBottomWidth = "0";
        });
        this.classList.remove("text-gray-500");
        this.classList.add("text-blue-600");
        this.style.borderBottomWidth = "2px";

        // Show/hide forms
        if (tabId === "login") {
          if (loginForm) loginForm.classList.remove("hidden");
          if (signupForm) signupForm.classList.add("hidden");
        } else if (tabId === "signup") {
          if (loginForm) loginForm.classList.add("hidden");
          if (signupForm) signupForm.classList.remove("hidden");
        }
      });
    });
  }

  // Form validation helper functions
  function showError(inputElement, message) {
    // Remove any existing error message
    const existingError =
      inputElement.parentNode.querySelector(".error-message");
    if (existingError) existingError.remove();

    // Add error styling
    inputElement.classList.add("border-red-500");

    // Create error message element
    const errorDiv = document.createElement("div");
    errorDiv.className = "error-message text-red-500 text-xs mt-1";
    errorDiv.textContent = message;
    inputElement.parentNode.appendChild(errorDiv);
  }

  function clearError(inputElement) {
    inputElement.classList.remove("border-red-500");
    const errorMsg = inputElement.parentNode.querySelector(".error-message");
    if (errorMsg) errorMsg.remove();
  }

  // Real-time validation for signup form
  const signupFormElem = document.querySelector("#signup-form form");
  if (signupFormElem) {
    const usernameInput = signupFormElem.querySelector(
      'input[name="signup_username"]',
    );
    const emailInput = signupFormElem.querySelector(
      'input[name="signup_email"]',
    );
    const passwordInput = signupFormElem.querySelector(
      'input[name="signup_password"]',
    );

    if (usernameInput) {
      usernameInput.addEventListener("input", function () {
        if (this.value.length < 3 && this.value.length > 0) {
          showError(this, "Username must be at least 3 characters");
        } else {
          clearError(this);
        }
      });
    }

    if (emailInput) {
      emailInput.addEventListener("input", function () {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value.length > 0 && !emailRegex.test(this.value)) {
          showError(this, "Please enter a valid email address");
        } else {
          clearError(this);
        }
      });
    }

    if (passwordInput) {
      passwordInput.addEventListener("input", function () {
        if (this.value.length > 0 && this.value.length < 6) {
          showError(this, "Password must be at least 6 characters");
        } else {
          clearError(this);
        }
      });
    }

    // Form submission validation
    signupFormElem.addEventListener("submit", function (e) {
      let isValid = true;

      if (usernameInput && usernameInput.value.trim().length < 3) {
        showError(usernameInput, "Username must be at least 3 characters");
        isValid = false;
      }

      if (emailInput && emailInput.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value.trim())) {
          showError(emailInput, "Please enter a valid email address");
          isValid = false;
        }
      }

      if (passwordInput && passwordInput.value.length < 6) {
        showError(passwordInput, "Password must be at least 6 characters");
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  }

  // Dashboard table row actions with confirmation
  const deleteButtons = document.querySelectorAll("button.text-red-600");
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      if (!confirm("Are you sure you want to delete this appointment?")) {
        e.preventDefault();
      }
    });
  });

  // Add hover animation to cards
  const cards = document.querySelectorAll(".bg-white.rounded-xl");
  cards.forEach((card) => {
    card.classList.add("hover-lift");
  });

  // Auto-hide flash messages after 5 seconds
  const flashMessages = document.querySelectorAll(".bg-red-100, .bg-green-100");
  flashMessages.forEach((msg) => {
    setTimeout(() => {
      msg.style.opacity = "0";
      setTimeout(() => {
        if (msg.parentNode) msg.style.display = "none";
      }, 300);
    }, 5000);
  });

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");
      if (targetId === "#") return;

      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        targetElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Add loading state to buttons on form submit
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function () {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="spinner mx-auto"></div>';
        submitBtn.disabled = true;

        // Restore after a delay (in case of slow network)
        setTimeout(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }, 3000);
      }
    });
  });
});
