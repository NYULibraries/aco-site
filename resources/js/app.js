window.addEventListener("DOMContentLoaded", () => {
  const button = document.querySelector('button[data-name="navButton"]');
  const navbar = document.querySelector(".navbar-collapse");
  if (button && navbar) {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      const isCollapsed = button.getAttribute("aria-expanded") === "false";
      if (isCollapsed) {
        button.setAttribute("aria-expanded", "true");
        navbar.style.height = "283px";
      } else {
        button.setAttribute("aria-expanded", "false");
        navbar.style.height = "0px";
      }
    });
  }
});
