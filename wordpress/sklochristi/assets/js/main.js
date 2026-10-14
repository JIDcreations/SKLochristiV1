/* SK Lochristi — gedrag */
(function () {
  "use strict";

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  /* ---------- Header nav ---------- */
  var toggle = $(".nav-toggle");
  var nav = $(".site-nav");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", String(open));
      toggle.setAttribute("aria-label", open ? "Menu sluiten" : "Menu openen");
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && nav.classList.contains("is-open")) {
        nav.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
      }
    });
  }

  /* ---------- Dropdowns ---------- */
  $all(".site-nav__group").forEach(function (group) {
    var trigger = $(".site-nav__trigger", group);
    if (!trigger) return;
    var setOpen = function (open) {
      group.classList.toggle("is-open", open);
      trigger.setAttribute("aria-expanded", String(open));
    };
    trigger.addEventListener("click", function () { setOpen(!group.classList.contains("is-open")); });
    $all(".site-nav__sub a", group).forEach(function (a) {
      a.addEventListener("click", function () {
        setOpen(false);
        if (nav && toggle) { nav.classList.remove("is-open"); toggle.setAttribute("aria-expanded", "false"); }
      });
    });
    document.addEventListener("click", function (e) { if (!group.contains(e.target)) setOpen(false); });
    group.addEventListener("focusout", function (e) { if (!group.contains(e.relatedTarget)) setOpen(false); });
    group.addEventListener("mouseleave", function () { if (window.matchMedia("(hover: hover) and (min-width: 961px)").matches) setOpen(false); });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && group.classList.contains("is-open")) { setOpen(false); trigger.focus(); }
    });
  });

  /* ---------- Artikel: link kopiëren ---------- */
  $all("[data-copy-link]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (!navigator.clipboard) return;
      navigator.clipboard.writeText(btn.getAttribute("data-copy-link") || location.href).then(function () {
        var label = $("span", btn);
        btn.classList.add("is-copied");
        label.textContent = "Gekopieerd";
        setTimeout(function () { btn.classList.remove("is-copied"); label.textContent = "Link kopiëren"; }, 2000);
      });
    });
  });

  /* ---------- Documenten: open het document uit de link (#id) ---------- */
  function openFromHash() {
    var id = decodeURIComponent(location.hash.slice(1));
    var target = id && document.getElementById(id);
    if (target && target.tagName === "DETAILS") target.open = true;
  }
  openFromHash();
  window.addEventListener("hashchange", openFromHash);

  /* ---------- Subnavigatie: actieve sectie ---------- */
  var subLinks = $all(".subnav a[href^='#']");
  if (subLinks.length && "IntersectionObserver" in window) {
    var byId = {};
    subLinks.forEach(function (a) { byId[a.getAttribute("href").slice(1)] = a; });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          subLinks.forEach(function (a) { a.classList.remove("is-active"); });
          var link = byId[entry.target.id];
          if (link) {
            link.classList.add("is-active");
            link.scrollIntoView({ block: "nearest", inline: "nearest" });
          }
        }
      });
    }, { rootMargin: "-45% 0px -50% 0px" });
    Object.keys(byId).forEach(function (id) {
      var sec = document.getElementById(id);
      if (sec) io.observe(sec);
    });
  }
})();
