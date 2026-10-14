"""Kleine helpers die geldige Gutenberg-blokmarkup schrijven.

Afbeeldingen en bestanden staan als placeholders in de output:
  {{ID:pad}}   -> bijlage-ID   (pad relatief aan de repo-root of een url)
  {{URL:pad}}  -> url van de bijlage (formaat large)
  {{FILE:pad}} -> url van het bestand
Die vult import.php in.
"""
import json


def _attrs(attrs):
    attrs = {k: v for k, v in attrs.items() if v not in (None, "", False)}
    return (" " + json.dumps(attrs, ensure_ascii=False, separators=(",", ":"))) if attrs else ""


def _cls(*names):
    return " ".join(n for n in names if n)


def raw(name, attrs=None):
    """Dynamisch blok zonder inhoud, bv. raw('skl/sponsors', {'limit': 11})."""
    return f"<!-- wp:{name}{_attrs(attrs or {})} /-->"


def group(children, cls="", tag="div", anchor="", label=""):
    attrs = {"className": cls}
    if tag != "div":
        attrs = {"tagName": tag, **attrs}
    id_attr = f' id="{anchor}"' if anchor else ""
    aria = f' aria-label="{label}"' if label else ""
    if label:
        attrs["ariaLabel"] = label
    inner = "\n\n".join(children)
    return (f"<!-- wp:group{_attrs(attrs)} -->\n"
            f'<{tag}{id_attr} class="{_cls("wp-block-group", cls)}"{aria}>{inner}</{tag}>\n'
            f"<!-- /wp:group -->")


def heading(text, level=2, cls="", anchor=""):
    attrs = {"className": cls}
    if level != 2:
        attrs = {"level": level, **attrs}
    id_attr = f' id="{anchor}"' if anchor else ""
    return (f"<!-- wp:heading{_attrs(attrs)} -->\n"
            f'<h{level} class="{_cls("wp-block-heading", cls)}"{id_attr}>{text}</h{level}>\n'
            f"<!-- /wp:heading -->")


def p(html, cls=""):
    c = f' class="{cls}"' if cls else ""
    return f"<!-- wp:paragraph{_attrs({'className': cls})} -->\n<p{c}>{html}</p>\n<!-- /wp:paragraph -->"


def li(html, children=None):
    nested = "".join(children or [])
    return f"<!-- wp:list-item -->\n<li>{html}{nested}</li>\n<!-- /wp:list-item -->"


def lst(items, ordered=False, cls=""):
    tag = "ol" if ordered else "ul"
    attrs = {"ordered": ordered, "className": cls}
    inner = "\n\n".join(li(i) if isinstance(i, str) and not i.startswith("<!-- wp:list-item") else i for i in items)
    return (f"<!-- wp:list{_attrs(attrs)} -->\n"
            f'<{tag} class="{_cls("wp-block-list", cls)}">{inner}</{tag}>\n'
            f"<!-- /wp:list -->")


def button(text, href, style="", external=False):
    cls = f"is-style-{style}" if style else ""
    target = ' target="_blank" rel="noreferrer noopener"' if external else ""
    return (f"<!-- wp:button{_attrs({'className': cls})} -->\n"
            f'<div class="{_cls("wp-block-button", cls)}"><a class="wp-block-button__link wp-element-button" href="{href}"{target}>{text}</a></div>\n'
            f"<!-- /wp:button -->")


def buttons(*items, cls=""):
    inner = "\n\n".join(items)
    return (f"<!-- wp:buttons{_attrs({'className': cls})} -->\n"
            f'<div class="{_cls("wp-block-buttons", cls)}">{inner}</div>\n'
            f"<!-- /wp:buttons -->")


def image(path, alt="", cls="", caption=""):
    attrs = {"id": "__ID__", "sizeSlug": "large", "linkDestination": "none", "className": cls}
    comment = _attrs(attrs).replace('"__ID__"', "{{ID:" + path + "}}")
    cap = f'<figcaption class="wp-element-caption">{caption}</figcaption>' if caption else ""
    return (f"<!-- wp:image{comment} -->\n"
            f'<figure class="{_cls("wp-block-image size-large", cls)}"><img src="{{{{URL:{path}}}}}" alt="{alt}" class="wp-image-{{{{ID:{path}}}}}"/>{cap}</figure>\n'
            f"<!-- /wp:image -->")


def html(markup):
    return f"<!-- wp:html -->\n{markup}\n<!-- /wp:html -->"


# Veelgebruikte bouwstenen van deze site

def section(children, cls="", anchor="", wrap_cls=""):
    return group([group(children, _cls("wrap", wrap_cls))], _cls("section", cls), tag="section", anchor=anchor)


def section_head(title, text="", action=None, anchor=""):
    head = [heading(title, anchor=anchor)]
    if text:
        head.append(p(text))
    children = [group(head)]
    if action:
        children.append(action)
    return group(children, "section-head")


def text_link(label, href, external=False):
    target = ' target="_blank" rel="noreferrer noopener"' if external else ""
    return p(f'<a href="{href}"{target}>{label}</a>', "text-link")
