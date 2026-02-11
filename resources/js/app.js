window.addEventListener("DOMContentLoaded", () => {
  // mobile nav hamburger
  const mobileNavHamburger = () => {
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
  };

  // search tips accordion
  const searchTipsAccordion = () => {
    // en and ar links
    const aboutInfoLinks = document.querySelectorAll(
      '[data-name="aboutinfo-link"]',
    );

    const aboutContent = document.querySelector(
      "[data-name='aboutinfo-content']",
    );

    aboutInfoLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const isOpen = aboutContent.classList.contains("open");

        // remove available class during transition
        aboutInfoLinks.forEach((l) =>
          l.classList.remove("aboutinfo-link-available"),
        );

        if (isOpen) {
          aboutInfoLinks.forEach((l) => l.classList.remove("open"));
          aboutInfoLinks.forEach((l) =>
            l.setAttribute("aria-expanded", "false"),
          );

          aboutContent.classList.remove("open");
          aboutContent.style.height = "0px";

          // readd available class after transition
          setTimeout(() => {
            aboutInfoLinks.forEach((l) => {
              l.classList.add("aboutinfo-link-available");
            });
          }, 500);
        } else {
          aboutInfoLinks.forEach((l) => l.classList.add("open"));
          aboutInfoLinks.forEach((l) =>
            l.setAttribute("aria-expanded", "true"),
          );

          aboutContent.classList.add("open");
          aboutContent.style.height = aboutContent.scrollHeight + "px";

          // readd available class after transition
          setTimeout(() => {
            aboutInfoLinks.forEach((l) =>
              l.classList.add("aboutinfo-link-available"),
            );
          }, 500);
        }
      });
    });
  };

  // bookpage - iframe remove loader animation for .bubblingG
  const bookIframeRemoveLoader = () => {
    const bookIframe = document.querySelector("iframe[data-name='book']");
    if (bookIframe) {
      bookIframe.addEventListener("load", function () {
        document.body.classList.remove("io-loading");
        bookIframe.style.opacity = "1";
      });
    }
  };

  mobileNavHamburger();
  searchTipsAccordion();
  bookIframeRemoveLoader();
});
