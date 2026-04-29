document.addEventListener("DOMContentLoaded", () => {
  const body = document.body;
  const themeToggles = document.querySelectorAll(".theme-toggle");
  const mobileToggle = document.getElementById("mobileToggle");
  const navMenu = document.getElementById("navMenu");
  const adminMenuToggle = document.getElementById("adminMenuToggle");
  const adminSidebar = document.getElementById("adminSidebar");
  const adminCloseBtn = document.getElementById("adminCloseBtn");

  const savedTheme = localStorage.getItem("tikuse_theme");

  if (savedTheme === "dark") {
    body.classList.add("dark-theme");
  } else {
    body.classList.remove("dark-theme");
  }

  const refreshThemeButtons = () => {
    const isDark = body.classList.contains("dark-theme");
    themeToggles.forEach((button) => {
      button.innerHTML = isDark ? "&#9790;" : "&#9728;";
      button.title = isDark ? "Switch to light theme" : "Switch to dark theme";
    });
  };

  refreshThemeButtons();

  themeToggles.forEach((button) => {
    button.addEventListener("click", () => {
      body.classList.toggle("dark-theme");
      const isDark = body.classList.contains("dark-theme");
      localStorage.setItem("tikuse_theme", isDark ? "dark" : "light");
      refreshThemeButtons();
    });
  });

  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener("click", () => {
      navMenu.classList.toggle("open");
    });
  }

  if (adminMenuToggle && adminSidebar) {
    adminMenuToggle.addEventListener("click", () => {
      adminSidebar.classList.add("open");
    });
  }

  if (adminCloseBtn && adminSidebar) {
    adminCloseBtn.addEventListener("click", () => {
      adminSidebar.classList.remove("open");
    });
  }
});
