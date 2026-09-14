/* Collapses the main navigation on narrow screens.
   The markup ships expanded, so the menu still works with scripting off. */
(function () {
  "use strict";

  var toggle = document.querySelector(".nav-toggle");
  var nav = document.getElementById("primary-nav");
  if (!toggle || !nav) { return; }

  var narrow = window.matchMedia("(max-width: 56rem)");

  function apply() {
    if (narrow.matches) {
      toggle.hidden = false;
      nav.hidden = toggle.getAttribute("aria-expanded") !== "true";
    } else {
      toggle.hidden = true;
      nav.hidden = false;
    }
  }

  toggle.hidden = false;
  toggle.setAttribute("aria-expanded", "false");

  toggle.addEventListener("click", function () {
    var open = toggle.getAttribute("aria-expanded") === "true";
    toggle.setAttribute("aria-expanded", open ? "false" : "true");
    apply();
  });

  if (typeof narrow.addEventListener === "function") {
    narrow.addEventListener("change", apply);
  } else if (typeof narrow.addListener === "function") {
    narrow.addListener(apply);
  }

  apply();
})();
