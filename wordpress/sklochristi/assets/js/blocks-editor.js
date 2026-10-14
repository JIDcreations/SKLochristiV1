/* SK Lochristi — eigen blokken in de editor.
   De weergave en de attributen komen van de server (inc/blocks.php); hier staan enkel de instellingen. */
(function (wp) {
  "use strict";

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var ServerSideRender = wp.serverSideRender;
  var useSelect = wp.data.useSelect;

  function textControl(props, key, label, help) {
    return el(c.TextControl, {
      label: label, help: help, value: props.attributes[key] || "",
      onChange: function (v) { var o = {}; o[key] = v; props.setAttributes(o); },
      __nextHasNoMarginBottom: true, __next40pxDefaultSize: true
    });
  }

  function imageControl(props, key, label) {
    var id = props.attributes[key];
    return el(be.MediaUploadCheck, null,
      el("div", { style: { marginTop: "16px" } },
        el("p", { style: { marginBottom: "8px", fontWeight: 500 } }, label),
        el(be.MediaUpload, {
          onSelect: function (media) { var o = {}; o[key] = media.id; props.setAttributes(o); },
          allowedTypes: ["image"], value: id,
          render: function (obj) {
            return el(Fragment, null,
              el(c.Button, { variant: "secondary", onClick: obj.open }, id ? "Afbeelding vervangen" : "Afbeelding kiezen"),
              id ? el(c.Button, { variant: "link", isDestructive: true, style: { marginLeft: "12px" }, onClick: function () { var o = {}; o[key] = 0; props.setAttributes(o); } }, "Verwijderen") : null
            );
          }
        })
      )
    );
  }

  var blocks = {
    "site-header": { title: "Header", icon: "menu", description: "Logo en hoofdmenu. Het menu pas je aan via Weergave > Menu's." },
    "site-footer": { title: "Footer", icon: "editor-insertmore", description: "Footer met menu's, adressen en sociale media." },
    "page-hero": { title: "Paginatitel", icon: "heading", description: "Titel en samenvatting van de pagina." },
    "hero": {
      title: "Middenstip (home)", icon: "marker",
      controls: function (p) {
        return [
          imageControl(p, "imageId", "Achtergrondfoto"),
          el("hr"),
          textControl(p, "leftTitle", "Links: titel"),
          textControl(p, "leftText", "Links: tekst"),
          textControl(p, "rightTitle", "Rechts: titel"),
          textControl(p, "rightText", "Rechts: tekst")
        ];
      }
    },
    "shortcuts": { title: "Snelkoppelingen", icon: "screenoptions", description: "Kalender, evenementen en webshop." },
    "latest-news": { title: "Laatste nieuws", icon: "megaphone", description: "De 3 nieuwste berichten.", manage: ["Nieuws", "post"] },
    "news-feed": { title: "Nieuwsoverzicht", icon: "excerpt-view", description: "Alle berichten met filter op seniors en jeugd.", manage: ["Nieuws", "post"] },
    "article-head": { title: "Artikel: kop", icon: "format-aside", description: "Titel, auteur, datum en foto van het bericht." },
    "article-share": { title: "Artikel: delen", icon: "share", description: "Deelknoppen onder een artikel." },
    "more-news": { title: "Meer nieuws", icon: "excerpt-view", description: "3 andere berichten onder een artikel." },
    "events": { title: "Evenementenlijst", icon: "calendar-alt", description: "Komende evenementen. Beheer ze via Evenementen in het menu links.", manage: ["Evenementen", "evenement"] },
    "sponsors": {
      title: "Sponsorborden", icon: "awards", description: "Logo's uit Sponsors.", manage: ["Sponsors", "sponsor"],
      controls: function (p) {
        return [
          el(c.RangeControl, { label: "Aantal sponsors (0 = alle)", min: 0, max: 40, value: p.attributes.limit, onChange: function (v) { p.setAttributes({ limit: v || 0 }); }, __nextHasNoMarginBottom: true, __next40pxDefaultSize: true }),
          el(c.ToggleControl, { label: "Toon 'Jouw bord hier?'", checked: !!p.attributes.cta, onChange: function (v) { p.setAttributes({ cta: v }); }, __nextHasNoMarginBottom: true })
        ];
      }
    },
    "team-list": {
      title: "Ploegenlijst", icon: "groups", description: "Ploegen uit Ploegen, per groep.", manage: ["Ploegen", "ploeg"],
      controls: function (p) {
        return [el(c.SelectControl, {
          label: "Groep", value: p.attributes.groep,
          options: [{ label: "Seniors", value: "seniors" }, { label: "Jeugd", value: "jeugd" }],
          onChange: function (v) { p.setAttributes({ groep: v }); }, __nextHasNoMarginBottom: true, __next40pxDefaultSize: true
        })];
      }
    },
    "documents": {
      title: "Documentenlijst", icon: "media-document", description: "Documenten uit één rubriek.", manage: ["Documenten", "document"],
      controls: function (p) {
        return [el(RubriekSelect, { value: p.attributes.rubriek, onChange: function (v) { p.setAttributes({ rubriek: v }); } })];
      }
    },
    "person": {
      title: "Persoon", icon: "admin-users", description: "Naam, rol en e-mail. Zonder foto tonen we de initialen.",
      controls: function (p) {
        return [
          textControl(p, "name", "Naam"),
          textControl(p, "role", "Rol"),
          textControl(p, "email", "E-mail", "Mag leeg blijven."),
          imageControl(p, "imageId", "Foto")
        ];
      }
    }
  };

  function RubriekSelect(props) {
    var terms = useSelect(function (select) {
      return select("core").getEntityRecords("taxonomy", "rubriek", { per_page: -1 });
    }, []);
    var options = [{ label: "Kies een rubriek", value: "" }].concat((terms || []).map(function (t) {
      return { label: t.name, value: t.slug };
    }));
    return el(c.SelectControl, { label: "Rubriek", value: props.value, options: options, onChange: props.onChange, __nextHasNoMarginBottom: true, __next40pxDefaultSize: true });
  }


  /* ---------- Berichtkaart: foto, categorie, auteur, samenvatting ---------- */
  var useEntityProp = wp.coreData.useEntityProp;
  var CATEGORY_ORDER = ["seniors", "jeugd", "club"];

  function PostSetup() {
    var blockProps = be.useBlockProps({ className: "skl-setup" });
    var featured = useEntityProp("postType", "post", "featured_media");
    var cats = useEntityProp("postType", "post", "categories");
    var meta = useEntityProp("postType", "post", "meta");
    var excerpt = useEntityProp("postType", "post", "excerpt");
    var data = useSelect(function (select) {
      var core = select("core");
      return {
        media: featured[0] ? core.getMedia(featured[0]) : null,
        terms: core.getEntityRecords("taxonomy", "category", { per_page: -1 }),
        me: core.getCurrentUser()
      };
    }, [featured[0]]);

    var m = meta[0] || {};
    var setMeta = function (key, value) { var o = Object.assign({}, m); o[key] = value; meta[1](o); };
    var terms = (data.terms || []).filter(function (t) { return CATEGORY_ORDER.indexOf(t.slug) !== -1; })
      .sort(function (a, b) { return CATEGORY_ORDER.indexOf(a.slug) - CATEGORY_ORDER.indexOf(b.slug); });
    var current = cats[0] || [];
    var url = data.media && (data.media.media_details && data.media.media_details.sizes && data.media.media_details.sizes.large ? data.media.media_details.sizes.large.source_url : data.media.source_url);

    var photo = featured[0]
      ? el("div", { className: "skl-setup__preview" + (m.affiche ? " is-poster" : "") },
          url ? el("img", { src: url, alt: "" }) : el(c.Spinner),
          el("div", { className: "skl-setup__photo-actions" },
            el(be.MediaUpload, {
              onSelect: function (media) { featured[1](media.id); }, allowedTypes: ["image"], value: featured[0],
              render: function (o) { return el(c.Button, { variant: "secondary", onClick: o.open }, "Andere foto"); }
            }),
            el(c.Button, { variant: "tertiary", isDestructive: true, onClick: function () { featured[1](0); } }, "Verwijderen")
          ))
      : el(be.MediaPlaceholder, {
          icon: "format-image",
          labels: { title: "Foto", instructions: "Sleep een foto hierheen, upload er een of kies uit de mediabibliotheek. Deze foto staat bovenaan het artikel en op de nieuwskaarten." },
          accept: "image/*", allowedTypes: ["image"],
          onSelect: function (media) { featured[1](media.id); }
        });

    return el("div", blockProps,
      el("div", { className: "skl-setup__head" },
        el("strong", null, "Nieuwsbericht"),
        el("span", null, "Kies een foto en categorie, schrijf je tekst eronder en klik op Publiceren.")
      ),
      el("div", { className: "skl-setup__grid" },
        el("div", { className: "skl-setup__photo" }, photo),
        el("div", { className: "skl-setup__fields" },
          el("div", { className: "skl-setup__field" },
            el("span", { className: "skl-setup__label" }, "Categorie"),
            el("div", { className: "skl-setup__pills", role: "group", "aria-label": "Categorie" },
              terms.length ? terms.map(function (t) {
                var on = current.indexOf(t.id) !== -1;
                return el("button", {
                  key: t.id, type: "button", className: "skl-setup__pill" + (on ? " is-on" : ""), "aria-pressed": on,
                  onClick: function () { cats[1]([t.id]); }
                }, t.name);
              }) : el(c.Spinner)
            )
          ),
          el(c.TextControl, {
            label: "Auteur", value: m.auteur || "", placeholder: data.me ? data.me.name : "",
            help: "Leeg = je eigen naam.", onChange: function (v) { setMeta("auteur", v); },
            __nextHasNoMarginBottom: true, __next40pxDefaultSize: true
          }),
          el(c.ToggleControl, {
            label: "De foto is een affiche", help: "Aan = de foto wordt niet bijgesneden en staat naast de titel.",
            checked: !!m.affiche, onChange: function (v) { setMeta("affiche", v); }, __nextHasNoMarginBottom: true
          }),
          el(c.TextareaControl, {
            label: "Korte samenvatting", help: "Staat op de nieuwskaarten. Leeg = het begin van je tekst.",
            value: typeof excerpt[0] === "string" ? excerpt[0] : "", rows: 3,
            onChange: function (v) { excerpt[1](v); }, __nextHasNoMarginBottom: true
          })
        )
      )
    );
  }

  wp.blocks.registerBlockType("skl/post-setup", {
    apiVersion: 3,
    title: "Berichtkaart",
    description: "Foto, categorie, auteur en samenvatting van het nieuwsbericht. Enkel zichtbaar in de editor.",
    icon: "id-alt",
    category: "sklochristi",
    supports: { html: false, inserter: false, multiple: false, reusable: false, lock: false, __experimentalToolbar: false },
    edit: PostSetup,
    save: function () { return null; }
  });

  /* ---------- Documentkaart: rubriek, omschrijving, pdf ---------- */
  function DocumentSetup() {
    var blockProps = be.useBlockProps({ className: "skl-setup" });
    var terms = useEntityProp("postType", "document", "rubriek");
    var meta = useEntityProp("postType", "document", "meta");
    var m = meta[0] || {};
    var setMeta = function (key, value) { var o = Object.assign({}, m); o[key] = value; meta[1](o); };
    var data = useSelect(function (select) {
      var core = select("core");
      return {
        rubrieken: core.getEntityRecords("taxonomy", "rubriek", { per_page: -1, orderby: "id", order: "asc" }),
        pdf: m.pdf ? core.getMedia(m.pdf) : null
      };
    }, [m.pdf]);
    var current = terms[0] || [];
    var fileName = data.pdf && (data.pdf.source_url || "").split("/").pop();

    var pdf = m.pdf
      ? el("div", { className: "skl-setup__file" },
          el("div", { className: "skl-setup__file-info" },
            el("svg", { viewBox: "0 0 24 24", width: 36, height: 36, fill: "none", stroke: "currentColor", strokeWidth: 1.8, "aria-hidden": true },
              el("path", { d: "M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" }), el("path", { d: "M14 3v5h5M9 13h6M9 17h4" })),
            el("div", null,
              el("strong", null, data.pdf ? (data.pdf.title && data.pdf.title.rendered) || fileName : "Pdf laden…"),
              fileName ? el("span", null, fileName) : null
            )
          ),
          el("div", { className: "skl-setup__photo-actions" },
            el(be.MediaUpload, {
              onSelect: function (media) { setMeta("pdf", media.id); }, allowedTypes: ["application/pdf"], value: m.pdf,
              render: function (o) { return el(c.Button, { variant: "secondary", onClick: o.open }, "Andere pdf"); }
            }),
            el(c.Button, { variant: "tertiary", isDestructive: true, onClick: function () { setMeta("pdf", 0); } }, "Verwijderen")
          )
        )
      : el(be.MediaPlaceholder, {
          icon: "pdf",
          labels: { title: "Pdf", instructions: "Sleep een pdf hierheen, upload er een of kies uit de mediabibliotheek. Bezoekers kunnen hem downloaden." },
          accept: "application/pdf", allowedTypes: ["application/pdf"],
          onSelect: function (media) { setMeta("pdf", media.id); }
        });

    return el("div", blockProps,
      el("div", { className: "skl-setup__head" },
        el("strong", null, "Document"),
        el("span", null, "Kies een rubriek, voeg een pdf toe en/of schrijf de tekst eronder. Klik dan op Publiceren.")
      ),
      el("div", { className: "skl-setup__grid skl-setup__grid--doc" },
        el("div", { className: "skl-setup__fields" },
          el("div", { className: "skl-setup__field" },
            el("span", { className: "skl-setup__label" }, "Rubriek op de pagina Documenten"),
            el("div", { className: "skl-setup__pills", role: "group", "aria-label": "Rubriek" },
              data.rubrieken ? data.rubrieken.map(function (t) {
                var on = current.indexOf(t.id) !== -1;
                return el("button", {
                  key: t.id, type: "button", className: "skl-setup__pill" + (on ? " is-on" : ""), "aria-pressed": on,
                  onClick: function () { terms[1]([t.id]); }
                }, t.name);
              }) : el(c.Spinner)
            ),
            !current.length ? el("p", { className: "skl-setup__warn" }, "Kies een rubriek, anders verschijnt het document nergens op de site.") : null
          ),
          el(c.TextControl, {
            label: "Korte omschrijving", value: m.omschrijving || "", placeholder: "Bv. Voor jeugdtrainers",
            help: "Kleine regel onder de titel.", onChange: function (v) { setMeta("omschrijving", v); },
            __nextHasNoMarginBottom: true, __next40pxDefaultSize: true
          })
        ),
        el("div", { className: "skl-setup__photo" }, pdf)
      )
    );
  }

  wp.blocks.registerBlockType("skl/document-setup", {
    apiVersion: 3,
    title: "Documentkaart",
    description: "Rubriek, omschrijving en pdf van het document. Enkel zichtbaar in de editor.",
    icon: "media-document",
    category: "sklochristi",
    supports: { html: false, inserter: false, multiple: false, reusable: false, lock: false, __experimentalToolbar: false },
    edit: DocumentSetup,
    save: function () { return null; }
  });

  Object.keys(blocks).forEach(function (name) {
    var def = blocks[name];
    var full = "skl/" + name;
    wp.blocks.registerBlockType(full, {
      apiVersion: 3,
      title: def.title,
      description: def.description,
      icon: def.icon,
      category: "sklochristi",
      supports: { html: false },
      edit: function (props) {
        var blockProps = be.useBlockProps();
        var admin = (window.sklEditor && window.sklEditor.adminUrl) || "/wp-admin/";
        var manage = def.manage ? el("div", { className: "skl-manage" },
          el("span", null, "Deze lijst vult zich vanzelf uit ", el("strong", null, def.manage[0]), ". Aanpassen doe je daar, niet hier."),
          el("a", { className: "skl-manage__btn", target: "_top", href: admin + "edit.php?post_type=" + def.manage[1] }, def.manage[0] + " beheren"),
          el("a", { className: "skl-manage__btn is-primary", target: "_top", href: admin + "post-new.php?post_type=" + def.manage[1] }, "Nieuw toevoegen")
        ) : null;
        return el("div", blockProps,
          manage,
          def.controls ? el(be.InspectorControls, null, el(c.PanelBody, { title: "Instellingen" }, def.controls(props))) : null,
          el(c.Disabled, null, el(ServerSideRender, { block: full, attributes: props.attributes }))
        );
      },
      save: function () { return null; }
    });
  });

  // Enkel de knopstijlen van de club.
  wp.domReady(function () {
    ["fill", "outline"].forEach(function (s) { wp.blocks.unregisterBlockStyle("core/button", s); });
  });
})(window.wp);
