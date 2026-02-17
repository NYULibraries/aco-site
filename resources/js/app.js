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

  const handleIframeResize = () => {
    const calculateAvailableHeight = () => {
      const body = document.querySelector("body.book");
      // if 404 page there is no body with book class
      if (!body) return;
      const children = Array.from(body.children);
      const iframe = document.querySelector("iframe[data-name='book']");
      let height = document.documentElement.clientHeight;
      for (let i = 0; i < children.length; i++) {
        if (children[i] === iframe) {
          continue;
        }
        height -= children[i].offsetHeight;
        if (height <= 0) {
          break;
        }
      }

      if (iframe) {
        iframe.style.height = `${height}px`;
      }
      return height;
    };
    window.addEventListener("load", () => {
      calculateAvailableHeight();
    });
    window.addEventListener("resize", () => {
      calculateAvailableHeight();
    });
  };

  const handleBookViewerMessages = () => {
    window.addEventListener("message", (event) => {
      if (event.origin !== 'https://sites.dlib.nyu.edu') {
        return;
      }

      let parsedEvent;
      try {
        parsedEvent = JSON.parse(event.data);
      } catch (e) {
        return;
      }

      switch (parsedEvent.fire) {
        case "viewer:init":
          console.log("Viewer initialized");
          break;

        case "viewer:loaded":
          console.log("Viewer loaded");
          break;

        // bookpage - iframe remove loader animation for .bubblingG
        case "viewer:contentready":
          const iframe = document.querySelector("iframe[data-name='book']");
          if (iframe) {
            iframe.style.opacity = "1";
            document.body.classList.remove("io-loading");
          }
          break;

        case "viewer:sequence:change":
          const sequence = parsedEvent.message.sequence;
          window.history.pushState({}, "", `${sequence}`);
          break;

        case "change:option:multivolume":
          if (parsedEvent.message.length > 10) {
            const newPid = parsedEvent.message.slice(14, -10);
            window.location.href = `${window.location.origin}/books/${newPid}/1`;
          }
          break;

        default:
          console.log("Unknown viewer event:", parsedEvent.fire);
          break;
      }
    });
  };

  mobileNavHamburger();
  searchTipsAccordion();
  bookIframeRemoveLoader();
  handleIframeResize();
  // handleBookViewerMessages();
});
