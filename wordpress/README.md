# SK Lochristi – WordPress

- `sklochristi/` – block theme. Lokaal gekoppeld via symlink:
  `~/Local Sites/Sklochristi/app/public/wp-content/themes/sklochristi`
- `import/` – zet de inhoud van de statische site in WordPress.

## Opnieuw opstarten
1. Local openen → site **Sklochristi** starten
2. Terminal in deze repo → `claude`
3. WP-CLI: `wordpress/bin/wp <commando>` (bv. `wordpress/bin/wp post list`)

## Import opnieuw draaien

```sh
cd wordpress/import
python3 build_pages.py   # pagina's -> content/pages/*.html
python3 build_data.py    # nieuws, ploegen, sponsors, evenementen, documenten -> content/data.json
wordpress/bin/wp eval-file wordpress/import/import.php --user=admin        # alles, vanuit de repo-root
wordpress/bin/wp eval-file wordpress/import/import.php news --user=admin   # enkel nieuws
```

Bestaande items worden bijgewerkt (op slug), media worden één keer geüpload.

## Wat beheert de club zelf
- Ploegen, Evenementen, Sponsors, Documenten (eigen menu-items in de admin, velden via ACF)
- Nieuws = gewone berichten (categorie Seniors / Jeugd / Club, auteur + affiche in de zijbalk)
- Menu's via Weergave > Menu's (hoofdmenu, footer)
- Paginateksten in de editor; de tekst onder de paginatitel = de samenvatting van de pagina

Vereist plugin: Advanced Custom Fields.
