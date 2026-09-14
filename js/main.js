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
      html += '<li class="board board--cta"><a href="over-ons.html#sponsors" style="display:grid;place-items:center;text-decoration:none;color:inherit;padding:.75rem">' +
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

    article.innerHTML =
      '<header class="article-head"><div class="wrap">' +
        '<a class="back-link" href="nieuws.html"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>Alle nieuws</a>' +
        storyMeta(item) +
        "<h1>" + esc(item.title) + "</h1>" +
        '<div class="byline"><span class="avatar" aria-hidden="true">' + esc(initials(item.author)) + "</span>" +
          "<div><strong>" + esc(item.author) + '</strong><time datetime="' + item.date + '">' + formatDate(item.date, true) + "</time></div></div>" +
      "</div></header>" +
      '<figure class="article-cover' + (item.poster ? " article-cover--poster" : "") + '" style="margin-block:0"><img src="' + esc(item.image) + '" alt=""></figure>' +
      '<div class="article-body">' + item.body.map(function (p) { return "<p>" + esc(p) + "</p>"; }).join("") + "</div>" +
      (item.gallery ? '<div class="article-gallery">' + item.gallery.map(function (g) {
        return '<figure style="margin:0"><img src="' + esc(g) + '" alt="" loading="lazy"></figure>';
      }).join("") + "</div>" : "");

    var moreList = $("[data-more-news]");
    if (moreList) moreList.innerHTML = more.map(function (n) { return storyCard(n); }).join("");
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
