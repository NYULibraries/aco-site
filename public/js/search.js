window.addEventListener("DOMContentLoaded", () => {
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

  searchTipsAccordion();
});
