/* SK Lochristi — gedrag */
(function () {
  "use strict";

  document.documentElement.classList.add("js");

  var SKL = window.SKL || {};
  var news = SKL.news || [];

  var CATEGORY_LABEL = { seniors: "Seniors", jeugd: "Jeugd", club: "Club" };
  var MONTHS = ["januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december"];
  var DAYS = ["zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag"];

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function esc(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function formatDate(iso, withDay) {
    var d = new Date(iso + "T12:00:00");
    var s = d.getDate() + " " + MONTHS[d.getMonth()] + " " + d.getFullYear();
    return withDay ? DAYS[d.getDay()] + " " + s : s;
  }

  function initials(name) {
    return name.split(/\s+/).filter(function (p) { return /^[A-Z]/.test(p); })
      .map(function (p) { return p[0]; }).slice(0, 2).join("");
  }

  function articleUrl(item) { return "artikel.html?id=" + encodeURIComponent(item.slug); }

  function storyMeta(item, withAuthor) {
    return '<div class="story__meta">' +
      '<span class="tag tag--' + item.category + '">' + CATEGORY_LABEL[item.category] + "</span>" +
      '<time class="date" datetime="' + item.date + '">' + formatDate(item.date) + "</time>" +
      (withAuthor ? '<span class="date">door ' + esc(item.author) + "</span>" : "") +
      "</div>";
  }

  function storyCard(item, variant, withAuthor) {
    var cls = "story" + (variant ? " story--" + variant : "");
    return '<article class="' + cls + '">' +
      '<div class="story__media"><img src="' + esc(item.image) + '" alt="" loading="lazy"' +
      (item.poster ? ' style="object-position:50% 20%"' : "") + "></div>" +
      '<div class="story__body">' + storyMeta(item, withAuthor) +
      '<h3><a href="' + articleUrl(item) + '">' + esc(item.title) + "</a></h3>" +
      "<p>" + esc(item.excerpt) + "</p>" +
      "</div></article>";
  }

  var SHARE_ICONS = {
    facebook: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21h2.9z"/></svg>',
    whatsapp: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1-.7-.3-1.4-.7-2-1.3-.5-.5-1-1.1-1.3-1.7-.1-.2 0-.4.1-.5l.4-.4.2-.4v-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.6.6-.9 1.3-.9 2.1 0 .9.4 1.8.9 2.5 1.1 1.6 2.5 2.9 4.2 3.7.5.2 1 .4 1.4.5.6.2 1.1.1 1.6.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.2-.2z"/></svg>',
    link: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1"/><path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1"/></svg>'
  };

  function shareBar(title) {
    var url = encodeURIComponent(location.href);
    return '<aside class="article-share" aria-label="Deel dit bericht"><div class="article-share__inner">' +
      '<span class="article-share__label">Deel dit bericht</span><div class="article-share__links">' +
      '<a class="share-btn" href="https://www.facebook.com/sharer/sharer.php?u=' + url + '" target="_blank" rel="noopener">' + SHARE_ICONS.facebook + "Facebook</a>" +
      '<a class="share-btn" href="https://wa.me/?text=' + encodeURIComponent(title + " ") + url + '" target="_blank" rel="noopener">' + SHARE_ICONS.whatsapp + "WhatsApp</a>" +
      '<button class="share-btn" type="button" data-copy-link>' + SHARE_ICONS.link + "<span>Link kopiëren</span></button>" +
      "</div></div></aside>";
  }

  document.addEventListener("click", function (e) {
    var btn = e.target.closest && e.target.closest("[data-copy-link]");
    if (!btn || !navigator.clipboard) return;
    navigator.clipboard.writeText(location.href).then(function () {
      var label = btn.querySelector("span");
      btn.classList.add("is-copied");
      label.textContent = "Gekopieerd";
      setTimeout(function () { btn.classList.remove("is-copied"); label.textContent = "Link kopiëren"; }, 2000);
    });
  });

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

  /* ---------- Club dropdown ---------- */
  var group = $(".site-nav__group");
  var trigger = group && $(".site-nav__trigger", group);
  if (trigger) {
    var setOpen = function (open) {
      group.classList.toggle("is-open", open);
      trigger.setAttribute("aria-expanded", String(open));
    };
    trigger.addEventListener("click", function () { setOpen(!group.classList.contains("is-open")); });
    $all(".site-nav__sub a", group).forEach(function (a) {
      a.addEventListener("click", function () {
        setOpen(false);
        if (nav) { nav.classList.remove("is-open"); toggle.setAttribute("aria-expanded", "false"); }
      });
    });
    document.addEventListener("click", function (e) { if (!group.contains(e.target)) setOpen(false); });
    group.addEventListener("focusout", function (e) { if (!group.contains(e.relatedTarget)) setOpen(false); });
    group.addEventListener("mouseleave", function () { if (window.matchMedia("(hover: hover) and (min-width: 961px)").matches) setOpen(false); });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && group.classList.contains("is-open")) { setOpen(false); trigger.focus(); }
    });
  }

  /* ---------- External match links ---------- */
  $all("[data-link]").forEach(function (a) {
    var href = SKL.links && SKL.links[a.getAttribute("data-link")];
    if (href) a.href = href;
  });

  /* ---------- Home: latest news ---------- */
  var latest = $("[data-latest-news]");
  if (latest && news.length) {
    var top = news.slice(0, 3);
    latest.innerHTML =
      storyCard(top[0]) +
      '<div class="news-lead__side">' + top.slice(1).map(function (n) { return storyCard(n, "row"); }).join("") + "</div>";
  }

  /* ---------- Sponsors ---------- */
  $all("[data-sponsors]").forEach(function (list) {
    var limit = parseInt(list.getAttribute("data-sponsors"), 10) || SKL.sponsors.length;
    var html = SKL.sponsors.slice(0, limit).map(function (s) {
      return '<li class="board"><img src="' + esc(s.logo) + '" alt="' + esc(s.name) + '" loading="lazy"></li>';
    }).join("");
    if (list.hasAttribute("data-sponsor-cta")) {
      html += '<li class="board board--cta"><a href="sponsor-worden.html" style="display:grid;place-items:center;text-decoration:none;color:inherit;padding:.75rem">' +
        "<span>Jouw bord hier?<small>Word sponsor</small></span></a></li>";
    }
    list.innerHTML = html;
  });

  /* ---------- News feed with filter ---------- */
  var feed = $("[data-feed]");
  if (feed) {
    var pills = $all("[data-filter]");
    var status = $("[data-feed-status]");

    pills.forEach(function (p) {
      var cat = p.getAttribute("data-filter");
      var count = cat === "alles" ? news.length : news.filter(function (n) { return n.category === cat; }).length;
      p.insertAdjacentHTML("beforeend", ' <span class="count">' + count + "</span>");
    });

    var render = function (cat) {
      var items = cat === "alles" ? news : news.filter(function (n) { return n.category === cat; });
      pills.forEach(function (p) { p.setAttribute("aria-pressed", String(p.getAttribute("data-filter") === cat)); });

      if (!items.length) {
        feed.innerHTML = '<p class="feed-empty">Nog geen berichten in deze categorie. Bekijk <a class="text-link" href="nieuws.html">alle nieuws</a>.</p>';
      } else {
        feed.innerHTML = items.map(function (n, i) {
          return storyCard(n, i === 0 ? "featured" : "", true);
        }).join("");
      }
      if (status) status.textContent = items.length + (items.length === 1 ? " bericht" : " berichten") + " getoond";
    };

    var params = new URLSearchParams(location.search);
    var initial = params.get("categorie");
    if (["seniors", "jeugd"].indexOf(initial) === -1) initial = "alles";
    render(initial);

    pills.forEach(function (p) {
      p.addEventListener("click", function () {
        var cat = p.getAttribute("data-filter");
        render(cat);
        var url = cat === "alles" ? location.pathname : location.pathname + "?categorie=" + cat;
        history.replaceState(null, "", url);
      });
    });
  }

  /* ---------- Article detail ---------- */
  var article = $("[data-article]");
  if (article) {
    var id = new URLSearchParams(location.search).get("id");
    var item = news.filter(function (n) { return n.slug === id; })[0] || news[0];
    document.title = item.title + " | SK Lochristi";

    var more = news.filter(function (n) { return n !== item && n.category === item.category; }).slice(0, 3);
    if (more.length < 3) more = more.concat(news.filter(function (n) { return n !== item && more.indexOf(n) === -1; }).slice(0, 3 - more.length));

    var words = item.body.join(" ").split(/\s+/).length;
    var minutes = Math.max(1, Math.round(words / 200));
    var lines = '<svg class="page-hero__lines" viewBox="0 0 400 400" aria-hidden="true"><circle cx="200" cy="200" r="150"/><line x1="200" y1="-200" x2="200" y2="600"/><circle cx="200" cy="200" r="4" style="fill:currentColor"/></svg>';
    var poster = item.poster ? '<figure class="article-hero__poster"><img src="' + esc(item.image) + '" alt=""></figure>' : "";

    article.innerHTML =
      '<header class="article-hero' + (item.poster ? " article-hero--poster" : "") + '">' + lines +
        '<div class="wrap article-hero__inner"><div>' +
          '<a class="back-link" href="nieuws.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>Alle nieuws</a>' +
          storyMeta(item).replace("</div>", '<span class="date">' + minutes + " min lezen</span></div>") +
          "<h1>" + esc(item.title) + "</h1>" +
          '<div class="byline"><span class="avatar" aria-hidden="true">' + esc(initials(item.author)) + "</span>" +
            "<div><strong>" + esc(item.author) + '</strong><time datetime="' + item.date + '">' + formatDate(item.date, true) + "</time></div></div>" +
        "</div>" + poster + "</div>" +
      "</header>" +
      (item.poster ? "" : '<figure class="article-photo" style="margin-bottom:0"><img src="' + esc(item.image) + '" alt=""></figure>') +
      '<div class="article-body">' + item.body.map(function (p) { return "<p>" + esc(p) + "</p>"; }).join("") +
      (item.gallery ? '<div class="article-gallery">' + item.gallery.map(function (g) {
        return '<figure style="margin:0"><img src="' + esc(g) + '" alt="" loading="lazy"></figure>';
      }).join("") + "</div>" : "") +
      "</div>" + shareBar(item.title);

    var moreList = $("[data-more-news]");
    if (moreList) moreList.innerHTML = more.map(function (n) { return storyCard(n); }).join("");
  }

  /* ---------- Events: simple list, upcoming only ---------- */
  var eventsBox = $("[data-events]");
  if (eventsBox) {
    var now = new Date();
    var today = now.getFullYear() + "-" + String(now.getMonth() + 1).padStart(2, "0") + "-" + String(now.getDate()).padStart(2, "0");
    var upcoming = (SKL.events || []).filter(function (e) { return e.date >= today; })
      .sort(function (a, b) { return a.date < b.date ? -1 : a.date > b.date ? 1 : 0; });

    eventsBox.innerHTML = upcoming.length
      ? '<ul class="events">' + upcoming.map(function (e) {
          return '<li class="event">' +
            '<time class="event__date" datetime="' + esc(e.date) + '">' + formatDate(e.date, true) + "</time>" +
            '<strong class="event__title">' + esc(e.title) + "</strong>" +
            '<span class="event__time">' + esc(e.time || "") + "</span>" +
            '<span class="event__place">' + esc(e.place || "") + "</span>" +
            "</li>";
        }).join("") + "</ul>" +
        (upcoming.some(function (e) { return e.example; })
          ? '<p class="placeholder-note">Voorlopige data. De definitieve datum en locatie bevestigen we hier en op Facebook.</p>' : "")
      : '<p class="feed-empty">Binnenkort meer activiteiten. Volg ons op <a class="text-link" href="https://www.facebook.com/sklochristi" target="_blank" rel="noopener">Facebook</a>.</p>';
  }

  /* ---------- Over ons: sub navigation highlight ---------- */
  var subLinks = $all(".subnav a");
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

  /* ---------- Footer year ---------- */
  $all("[data-year]").forEach(function (el) { el.textContent = new Date().getFullYear(); });
})();
