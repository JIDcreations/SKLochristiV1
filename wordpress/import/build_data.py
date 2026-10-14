"""Zet de inhoud van de statische site om naar content/data.json voor import.php:
nieuws, evenementen en sponsors (uit js/data.js), ploegen (uit ploegen.html)
en documenten (uit documenten.html)."""
import json
import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = Path(__file__).parent / "content" / "data.json"

# ---- js/data.js via node
js = subprocess.run(
    ["node", "-e", "const vm=require('vm'),fs=require('fs');const ctx={};ctx.window=ctx;"
     "vm.runInNewContext(fs.readFileSync(process.argv[1],'utf8'),ctx);process.stdout.write(JSON.stringify(ctx.SKL))",
     str(ROOT / "js" / "data.js")],
    capture_output=True, text=True, check=True,
).stdout
skl = json.loads(js)

CATEGORY = {"seniors": "Seniors", "jeugd": "Jeugd", "club": "Club"}


def news_content(item):
    blocks = [f"<!-- wp:paragraph -->\n<p>{p}</p>\n<!-- /wp:paragraph -->" for p in item["body"]]
    if item.get("gallery"):
        imgs = "\n\n".join(
            '<!-- wp:image {"id":{{ID:%s}},"sizeSlug":"large","linkDestination":"none"} -->\n'
            '<figure class="wp-block-image size-large"><img src="{{URL:%s}}" alt="" class="wp-image-{{ID:%s}}"/></figure>\n'
            "<!-- /wp:image -->" % (g, g, g)
            for g in item["gallery"]
        )
        blocks.append(
            '<!-- wp:gallery {"linkTo":"none","className":"article-gallery"} -->\n'
            f'<figure class="wp-block-gallery has-nested-images columns-default is-cropped article-gallery">{imgs}</figure>\n'
            "<!-- /wp:gallery -->"
        )
    return "\n\n".join(blocks)


news = [{
    "slug": n["slug"],
    "title": n["title"],
    "date": n["date"],
    "author": n["author"],
    "category": n["category"],
    "image": n["image"],
    "poster": bool(n.get("poster")),
    "excerpt": n["excerpt"],
    "content": news_content(n),
} for n in skl["news"]]

events = [{
    "title": e["title"],
    "date": e["date"].replace("-", ""),
    "time": e.get("time", ""),
    "place": e.get("place", ""),
    "provisional": bool(e.get("example")),
} for e in skl["events"]]

sponsors = [{"name": s["name"], "logo": s["logo"]} for s in skl["sponsors"]]

# ---- ploegen.html
teams = []
ploegen = (ROOT / "ploegen.html").read_text(encoding="utf-8")
for group, block in re.findall(r'<h3 id="(seniors|jeugd)-title">.*?<ul class="team-list">(.*?)</ul>', ploegen, re.S):
    for li in re.findall(r"<li>(.*?)</li>", block, re.S):
        name = re.search(r"<strong>(.*?)</strong>", li).group(1)
        reeks = re.search(r"<span>(.*?)</span>", li).group(1)
        info = re.search(r"<small>(.*?)</small>", li)
        teams.append({"name": name, "group": group, "reeks": reeks, "info": info.group(1) if info else ""})

# ---- documenten.html
docs = []
html = (ROOT / "documenten.html").read_text(encoding="utf-8")
RUBRIEKEN = {
    "visie-en-waarden": "Visie en waarden",
    "charters": "Charters per rol",
    "afspraken": "Afspraken en reglement",
    "meisjesvoetbal": "Meisjesvoetbal",
}
for section_id, section in re.findall(r'<section[^>]*id="([^"]+)"[^>]*>(.*?)</section>', html, re.S):
    if section_id not in RUBRIEKEN:
        continue
    for order, (doc_id, inner) in enumerate(re.findall(r'<details class="doc" id="([^"]+)">(.*?)</details>', section, re.S)):
        title = re.search(r'<span class="doc__title">(.*?)</span>', inner).group(1)
        meta = re.search(r'<span class="doc__meta">(.*?)</span>', inner)
        body = re.search(r'<div class="doc__body">(.*)</div>\s*$', inner, re.S).group(1)
        pdf = re.search(r'<p class="doc__download"><a[^>]*href="([^"]+)"', body)
        body = re.sub(r'<p class="doc__download">.*?</p>', "", body, flags=re.S)
        # Interne link naar een ander document op dezelfde pagina blijft werken.
        body = "\n".join(line.strip() for line in body.strip().splitlines())
        docs.append({
            "slug": doc_id,
            "title": title,
            "meta": meta.group(1) if meta else "",
            "rubriek": section_id,
            "rubriek_name": RUBRIEKEN[section_id],
            "order": order,
            "pdf": pdf.group(1) if pdf else "",
            # Bevat het document svg of tabellen, dan als HTML-blok bewaren zodat de editor niets wegfiltert.
            "content": ("<!-- wp:html -->\n" + body + "\n<!-- /wp:html -->") if re.search(r"<svg|<table", body) else body,
        })

OUT.write_text(json.dumps({
    "categories": CATEGORY,
    "news": news,
    "events": events,
    "sponsors": sponsors,
    "teams": teams,
    "documents": docs,
}, ensure_ascii=False, indent=1), encoding="utf-8")

print(f"{len(news)} nieuws, {len(events)} evenementen, {len(sponsors)} sponsors, {len(teams)} ploegen, {len(docs)} documenten")
