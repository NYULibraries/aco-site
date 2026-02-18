window.addEventListener("DOMContentLoaded", () => {
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
      if (event.origin !== "https://sites.dlib.nyu.edu") {
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
          // console.log("Viewer initialized");
          break;

        case "viewer:loaded":
          // console.log("Viewer loaded");
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
            window.location.href = `${window.location.origin}/book/${newPid}/1`;
          }
          break;

        case "button:button-metadata:on":
          break;

        case "button:button-metadata:off":
          break;

        default:
          console.log("Unknown viewer event:", parsedEvent.fire);
          break;
      }
    });
  };

  handleIframeResize();
  handleBookViewerMessages();
});
