window.addEventListener("DOMContentLoaded", () => {
  // Takes the values from the field, scope , and input to query viewer params
  function searchFormAggregateSubmit() {
    const form = document.querySelector('form.advanced[role="search"]');
    if (!form) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const fieldSelect = form?.querySelector("select.field-select");
      const scopeSelect = form?.querySelector("select.scope-select");
      const searchInput = form?.querySelector('input[type="text"]');
      const fieldValue = fieldSelect.value;
      const scopeValue = scopeSelect.value;
      const textValue = searchInput?.value.trim();

      if (!textValue) {
        window.location.href = "/searchcollections";
        return;
      }
      // uses removequerydiacritics to remove accents and special chars
      const normalizedTextValue = removeQueryDiacritics(textValue);

      if (!fieldSelect || !scopeSelect || !searchInput) return;
      if (!fieldValue || !scopeValue || !normalizedTextValue) return;

      // if input is empty, redirect to searchcollections
      if (normalizedTextValue === "") {
        window.location.href = "/searchcollections";
      }
      const params = new URLSearchParams({
        [fieldValue]: normalizedTextValue,
        scope: scopeValue,
      });
      window.location.href = `/search?${params.toString()}`;
    });
  }

  function filterResults() {
    const filterRowsPerPage = document.getElementById("rpp-select-el");
    const filterByParam = document.getElementById("sort-select-el");

    if (!filterRowsPerPage || !filterByParam) return;

    const currentUrl = new URL(window.location.href);

    filterRowsPerPage.addEventListener("change", (e) => {
      const options = e.target.selectedOptions[0];
      const value = options.value;
      currentUrl.searchParams.set("rpp", value);
      window.location.href = currentUrl;
    });

    filterByParam.addEventListener("change", (e) => {
      const options = e.target.selectedOptions[0];
      // default to asc if no sortDir
      const dir = options.dataset.sortDir || "asc";
      const value = options.value;
      currentUrl.searchParams.set("sort", `${value} ${dir}`);
      window.location.href = currentUrl;
    });
  }

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
  searchFormAggregateSubmit();
  filterResults();
});

