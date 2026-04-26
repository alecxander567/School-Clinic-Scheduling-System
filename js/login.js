function switchTab(tab) {
  const isLogin = tab === "login";
  document.getElementById("panel-login").style.display =
    isLogin ? "block" : "none";
  document.getElementById("panel-signup").style.display =
    isLogin ? "none" : "block";
  document.getElementById("tab-login").classList.toggle("active", isLogin);
  document.getElementById("tab-signup").classList.toggle("active", !isLogin);
}

function toggleMenu() {
  const menu = document.getElementById("mobile-menu");
  const open = document.getElementById("icon-open");
  const close = document.getElementById("icon-close");
  const isOpen = menu.classList.toggle("open");
  open.classList.toggle("hidden", isOpen);
  close.classList.toggle("hidden", !isOpen);
}

setTimeout(() => {
  document
    .querySelectorAll(
      '[style*="background:#fff0f0"], [style*="background:#e1f5ee"]',
    )
    .forEach((el) => {
      el.style.transition = "opacity .3s";
      el.style.opacity = "0";
      setTimeout(() => el.remove(), 300);
    });
}, 5000);
