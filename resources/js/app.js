window.addEventListener("DOMContentLoaded", () => {
  const button = document.querySelector('button[data-name="navButton"]');
  const navbar = document.querySelector(".navbar-collapse");
  if (button && navbar) {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      const isCollapsed = navbar.dataset.collapsed === "collapse";
      if (isCollapsed) {
        navbar.dataset.collapsed = "expand";
        navbar.style.height = "283px";
      } else {
        navbar.dataset.collapsed = "collapse";
        navbar.style.height = "0px";
      }
    });
  }
});
