"""Schrijft de blokinhoud van alle pagina's naar content/pages/*.html.
Teksten komen 1-op-1 uit de statische site."""
from pathlib import Path

from blocks import (button, buttons, group, heading, html, image, li, lst, p, raw,
                    section, section_head, text_link)

OUT = Path(__file__).parent / "content" / "pages"
OUT.mkdir(parents=True, exist_ok=True)

SPONSOR_PDF = "{{FILE:https://sklochristi.be/wp-content/uploads/2023/02/SK-Lochristi-Sponsor-deck-compressed.pdf}}"
MISSIE_PDF = "{{FILE:https://sklochristi.be/wp-content/uploads/2021/02/Missie-en-visie-SK-Lochristi.pdf}}"
TEAMTRAINING = "https://sklochristi.be/club/jeugd/hoe-aansluiten/"

pages = {}

# ---------------------------------------------------------------- Home
pages["home"] = "\n\n".join([
    raw("skl/hero", {"imageId": "__HERO__"}).replace('"__HERO__"', "{{ID:assets/img/news/DSC_9731.jpg}}"),
    raw("skl/shortcuts"),
    section([
        section_head("Laatste nieuws", action=buttons(button("Alle nieuws", "/nieuws/", "ghost")), anchor="news-title"),
        raw("skl/latest-news"),
    ]),
    section([
        image("assets/img/club/groep-2.jpg", "Een jeugdploeg van SK Lochristi in blauw-wit tenue op het veld", "split__media",
              "Onze jongste spelers, met de serres van Lochristi op de achtergrond."),
        group([
            heading("Een eerste ploeg uit eigen opleiding"),
            p("SK Lochristi bouwt aan een eerste ploeg met spelers uit de eigen jeugd, van rond de kerktorens van groot Lochristi.", "lede"),
            p("Eén bestuur zorgt voor alle ploegen, van de U6 tot de eerste ploeg. Beloftevolle jeugdspelers trainen mee bij de A-beloften en het eerste elftal, zodat de stap naar de seniors klein blijft."),
            group([buttons(button("Leer de club kennen", "/over-de-club/")), text_link("Lid worden", "/lid-worden/")], "actions"),
        ]),
    ], cls="section--line", wrap_cls="split"),
    section([
        section_head("Langs de lijn", "Deze bedrijven steunen SK Lochristi. Zonder hen geen truitjes, geen verlichting en geen jeugdwerking."),
        raw("skl/sponsors", {"limit": 11, "cta": True}),
        group([
            p("Samen met onze Business Friends: een 25-tal bedrijven uit Lochristi en omstreken."),
            text_link("Alle sponsors bekijken", "/sponsor-worden/#sponsors"),
        ], "sponsor-strip"),
    ]),
])

# ---------------------------------------------------------------- Over de club
def pillar(title, body):
    return group([heading(title, 3), body], "pillar")


def person(name, role, email="", image_path=""):
    attrs = {"name": name, "role": role, "email": email}
    block = raw("skl/person", attrs)
    if image_path:
        block = block.replace(" /-->", "").rstrip("}") + ',"imageId":{{ID:' + image_path + '}}} /-->'
    return block


def timeline_item(when, text):
    return group([p(when, "when"), p(text)], "timeline__item")


pages["over-de-club"] = "\n\n".join([
    section([
        group([
            heading("Een club die zijn eigen spelers opleidt"),
            p("Na het stopzetten van de ploeg in 3de amateur koos SK Lochristi voor een nieuwe koers. Met SK Lochristi 2.0 startte de club opnieuw in 3de provinciale, met een eerste ploeg die enkel uit eigen opgeleide spelers bestaat."),
            p("Die keuze bepaalt nog altijd hoe we werken. De sportieve cel senioren zit geregeld samen met de jeugdcoördinator bovenbouw om talent op te volgen. Beloftevolle spelers trainen eerst mee bij de A-beloften en de eerste ploeg, en krijgen daarna kansen in wedstrijden."),
            p("Zo bouwen we een eerste ploeg met spelers uit eigen opleiding, van rond de kerktorens van groot Lochristi."),
            p("Aan te vullen door de club: oprichtingsjaar en de vroege geschiedenis van SK Lochristi.", "placeholder-note"),
        ], "prose"),
        group([
            timeline_item("SK Lochristi 2.0", "Doorstart in 3de provinciale met een eerste ploeg uit eigen opleiding."),
            timeline_item("2022–2023", "Eén bestuur voor de hele club, van U6 tot en met de eerste ploeg. Seniors en jeugd houden elk een eigen sportieve cel."),
            timeline_item("2023–2026", "Drie seizoenen in 2de provinciale. In april 2026 degradeert de eerste ploeg."),
            timeline_item("2026–2027", "Nieuwe start in 3de provinciale C met Filip Cornelis als T1 en Andy Van Rysselberghe als T2."),
        ], "timeline"),
    ], anchor="geschiedenis", wrap_cls="split split--top"),
    section([
        group([
            group([heading("Onze visie"), p("“Samen plezant sportief voetballen”", "visie-motto")]),
            group([
                p("De jeugdafdeling van SK Lochristi heeft als doel jeugdvoetbalspelers op te leiden, en dit in de breedste zin van het woord. Het doel van het jeugdbeleid is opleidingsgericht voetbal met het kind op de centrale plaats."),
                p("Iedereen, ongeacht zijn/haar niveau, is welkom op SK Lochristi. Om onze missie te kunnen vervullen steunen wij op 4 essentiële pijlers: opleiding, kindvriendelijkheid, fun en fairplay."),
            ], "visie-intro"),
        ], "visie-head"),
        group([
            pillar("Opleiding", lst([
                "Vormen van (voetbal)intelligente spelers",
                "Vormen van voetballers met een zekere persoonlijkheid",
                "Focus op individuele basistechniek, fysiek, tactiek en mentaal",
                "Aanvallend voetbal wanneer het mogelijk is, alsook verdedigend",
            ])),
            pillar("Kindvriendelijkheid", p("Binnen de club wordt veel aandacht besteed aan een pedagogisch verantwoorde benadering van de speler. De opleiding dient steeds afgestemd te zijn op de individuele leefwereld van de leden en dient een bijdrage te leveren aan hun opvoeding.")),
            pillar("Fun", p("Het is van het grootste belang dat de spelers zich thuis voelen bij SK Lochristi en met plezier afzakken naar de club. Het is minstens even belangrijk dat ook de ouders en familie zich thuis voelen in de club.")),
            pillar("Fairplay", lst([
                "Respect voor de speler",
                "Respect voor de tegenstander",
                "Wederzijds respect tussen trainers, begeleiders en bestuursleden",
                "Respect voor de andere clubs",
                "Respect van en voor de ouders en sympathisanten",
            ])),
        ], "pillars"),
        group([
            buttons(button("Documenten en charters", "/documenten/", "white")),
            text_link("Volledige missie en visie (PDF)", MISSIE_PDF, external=True),
        ], "actions"),
    ], cls="section--navy on-navy", anchor="visie"),
    section([
        section_head("Bestuur en staf", "Eén bestuur voor de hele club, twee sportieve cellen: één voor de seniors en één voor de jeugd."),
        group([heading("Eerste ploeg", 3), group([
            person("Filip Cornelis", "Hoofdtrainer (T1)", image_path="assets/img/news/filip-cornelis.jpg"),
            person("Andy Van Rysselberghe", "Assistent-trainer (T2)"),
            person("Danny Den Hert", "Afgevaardigde"),
            person("Luc Viaene", "Afgevaardigde en gerechtelijk correspondent"),
            person("Patrik Van de Velde", "Terreincommissaris"),
        ], "people")], "people-group"),
        group([heading("Jeugdwerking", 3), group([
            person("Andy Van Rysselberghe", "Technisch verantwoordelijke jeugdopleiding (TVJO)", "tvjo@sklochristi.be"),
            person("Wesley Quatacker", "Assistent TVJO (AVJO)", "avjo@sklochristi.be"),
            person("Nico Wieme", "Coördinator bovenbouw", "bovenbouw@sklochristi.be"),
            person("Gert-Jan Mores", "Coördinator middenbouw", "middenbouw@sklochristi.be"),
            person("Tom De Sutter", "Coördinator onderbouw", "onderbouw@sklochristi.be"),
        ], "people")], "people-group"),
        group([heading("Bestuur", 3), group([
            person("", "Voorzitter"),
            person("", "Secretaris"),
            person("", "Penningmeester"),
        ], "people"),
            p("Aan te vullen door de club: namen, rollen en foto's van het bestuur. Zonder foto tonen we de initialen.", "placeholder-note"),
        ], "people-group"),
    ], anchor="bestuur"),
])

# ---------------------------------------------------------------- Ploegen
pages["ploegen"] = "\n\n".join([
    section([
        heading("Seniors en jeugd", cls="visually-hidden"),
        group([
            group([
                image("assets/img/news/DSC_9731.jpg", "Ploegfoto van de eerste ploeg en de beloften van SK Lochristi"),
                group([
                    heading("Seniors", 3),
                    p("De eerste ploeg speelt in 3de provinciale C. De beloften en PU21 vormen de laatste stap vanuit de jeugd."),
                    raw("skl/team-list", {"groep": "seniors"}),
                ], "team-block__body"),
            ], "team-block", tag="article"),
            group([
                image("assets/img/club/DSC_1554.jpg", "Ploegfoto van een jeugdploeg van SK Lochristi"),
                group([
                    heading("Jeugd", 3),
                    p("Een 3-sterren jeugdopleiding (IP) met een technisch verantwoordelijke en een coördinator per bouw."),
                    raw("skl/team-list", {"groep": "jeugd"}),
                ], "team-block__body"),
            ], "team-block", tag="article"),
        ], "teams"),
    ], anchor="ploegen"),
    section([
        group([
            group([heading("Zin om mee te spelen?"), p("Kom eerst een teamtraining meevolgen bij de ploeg van jouw leeftijd.")]),
            buttons(button("Lid worden", "/lid-worden/", "white")),
        ], "section-head section-head--flush"),
    ], cls="section--navy on-navy"),
])

# ---------------------------------------------------------------- Lid worden
def step(title, text):
    return group([heading(title, 3), p(text)], "step")


pages["lid-worden"] = "\n\n".join([
    section([
        section_head("Hoe word je lid bij SK Lochristi?", action=buttons(button("Teamtraining aanvragen", TEAMTRAINING))),
        group([
            step("Aanvraag teamtraining", "Vul het aanvraagformulier in om een proeftraining bij SK Lochristi aan te vragen. Vertel ons wat meer over jezelf, je geboortejaar en je voetbalervaring."),
            step("We nemen contact op", "Na je aanvraag nemen we contact met je op. We bekijken samen welk team het beste bij je past en spreken een moment af om mee te trainen."),
            step("Meetrainen", "Tijd om het veld op te gaan! Je traint mee met het team en ontdekt de sfeer, onze werking en de manier waarop we samen beter worden."),
            step("Evaluatie", "Na de proeftraining bekijken we samen hoe het verlopen is. We bespreken de mogelijkheden en de volgende stappen richting een aansluiting bij SK Lochristi."),
        ], "steps steps--light"),
        group([
            person("Wesley Quatacker", "Assistent TVJO, aanspreekpunt voor nieuwe jeugdspelers"),
            buttons(button("Mail Wesley", "mailto:avjo@sklochristi.be")),
        ], "contact-card contact-card--light"),
        p('Seniorspeler? Mail naar <a href="mailto:info@sklochristi.be">info@sklochristi.be</a>.', "note"),
    ], anchor="lid-worden"),
    section([
        group([
            group([heading("Ook zin om bij SK Lochristi te voetballen?"), p("Zet de eerste stap en vraag je teamtraining aan. We kijken ernaar uit om je te leren kennen!")]),
            buttons(button("Teamtraining aanvragen", TEAMTRAINING, "white")),
        ], "section-head section-head--flush"),
    ], cls="section--navy on-navy"),
    section([
        heading("Meer info", cls="visually-hidden"),
        group([
            group([p('<a href="/ploegen/">Ploegen</a>', "link-card__title"), p("Welke ploeg past bij jouw leeftijd?")], "link-card"),
            group([p('<a href="/contact/#terreinen">Terreinen</a>', "link-card__title"), p("Waar trainen en spelen we?")], "link-card"),
            group([p('<a href="/documenten/#formulieren">Formulieren</a>', "link-card__title"), p("Sportongeval en tussenkomst ziekenfonds")], "link-card"),
            group([p('<a href="/documenten/#charters">Charters</a>', "link-card__title"), p("Wat we verwachten van spelers en ouders")], "link-card"),
        ], "link-cards"),
    ], cls="section--line"),
])

# ---------------------------------------------------------------- Sponsor worden
def why(title, text):
    return group([heading(title, 3), p(text)], "why__item")


def package(name, price, items):
    return group([heading(name, 3), p(price, "price"), lst(items)], "package")


def row(name, desc, price):
    return group([p(name, "pl-name"), p(desc, "pl-desc"), p(price, "pl-price")], "pl-row")


def price_group(title, rows, note=""):
    children = [heading(title, 3), group(rows, "pl-rows")]
    if note:
        children.append(p(note, "pricelist__note"))
    return group(children, "pricelist__group")


def contact(name, *lines):
    return group([p(name, "sponsor-contacts__name")] + [p(l) for l in lines], "sponsor-contacts__item")


pages["sponsor-worden"] = "\n\n".join([
    section([
        heading("Sponsoring bij SK Lochristi", cls="visually-hidden"),
        group([
            why("Een forum voor nieuwe relaties", "SK Lochristi wil u een forum aanreiken, om nieuwe commerciële relaties aan te knopen en dit alles binnen een gemoedelijke familiaire sfeer."),
            why("Respons op uw inbreng", "Het is de doelstelling van onze vereniging om onze sponsors steeds de nodige respons te geven op hun inbreng. Ons sterk imago en de solide structuur van onze club dragen hier zeker toe bij."),
            why("Afgestemd op uw doelen", 'Uit de ervaring die we hebben met de sponsors, weten we dat een sponsoring sterk afhankelijk is van de persoonlijke doelstellingen. Om dit tegemoet te komen hebben we enkele <a href="#formules">sponsor formules</a> samengesteld die als basis kunnen dienen voor uw pakket.'),
        ], "why"),
    ]),
    section([
        group([
            section_head("Sponsorpakketten", "Drie basispakketten. Elk pakket bevat alles van het pakket ervoor.",
                         action=buttons(button("Sponsorbrochure (PDF)", SPONSOR_PDF, "white", external=True), cls="has-download")),
            group([
                package("Bronze", "€300", ["Logo onderaan partnerpagina website", "Uitnodiging nieuwjaarsreceptie", "Seizoensabonnement", "Uitnodiging jaarlijks sponsordiner"]),
                package("Silver", "€600", ["<strong>Alles van Bronze</strong>", "Logo homepage website", "Logo tv kantine", "Logo wedstrijdblad"]),
                package("Gold", "€900", ["<strong>Alles van Silver</strong>", "1x social media post", "Logo bovenaan + samenvatting partnerpagina website", "Logo wedstrijdkalender"]),
            ], "packages"),
        ], "section--navy on-navy sponsor-panel"),
    ], cls="section--tight section--flush-top", anchor="formules"),
    section([
        section_head("Aanvullen naar wens", "Deze formules zijn zeker niet alles wat we te bieden hebben. Elk van deze formules kan nog via persoonlijke wensen aangevuld worden (ingangskaarten, sponsoring van bepaalde evenementen, shirt sponsoring, …)."),
        group([
            price_group("Outfit", [row("Polo, shirt, trainingstas of training", "1ste ploeg &amp; beloften, jeugdteams. Voor- of achterkant.", "Op aanvraag")]),
            price_group("Wedstrijd", [
                row("Aftrap", "Aftrap thuiswedstrijd + vermelding in kantine voor 1 seizoen", "€100"),
                row("Wedstrijd", "2 wedstrijdtickets + glaasje bubbels", "€100"),
                row("Wedstrijdblad", "Logo op wedstrijdblad", "€100"),
            ]),
            price_group("Reclamepanelen", [
                row("Omheiningspaneel Bosdreef of Beervelde", "0,8 m x 2,5 m, +€150 per extra lopende meter", "€375/jaar"),
                row("Paalpaneel Bosdreef", "1 m x 2,5 m, +€200 per extra lopende meter", "€500/jaar"),
                row("Paalpaneel Beervelde", "2,42 m x 2,5 m, +€250 per extra lopende meter", "€675/jaar"),
                row("Scorebord", "Complex Bosdreef en complex Beervelde", "Op aanvraag"),
                row("Paneel kantine", "0,5 m x 0,5 m, Bosdreef of Beervelde", "€125/jaar"),
            ], "Bij elk paneel komt een éénmalige productiekost."),
            price_group("Tornooien", [
                row("Hoofdsponsor", "Tornooi draagt jouw naam, banner met logo op de velden, overhandiging beker winnaar", "€1000/tornooi"),
                row("Terreinsponsor", "Eén van de velden wordt naar jouw zaak vernoemd, banner op het veld", "€250/tornooi"),
                row("Beker- &amp; medaillesponsor", "Overhandiging medailles, optie logo's op bekers/medailles", "€250/tornooi"),
            ], "Plus productiekost van banners of bekers en medailles met logo."),
            price_group("Andere", [
                row("Wedstrijdkalender", "Logo op seizoenkalender", "€125"),
                row("Speler van de maand", "Logo &amp; vermelding in social media post", "€100"),
                row("Gouden Schoen", "Hoofdsponsor Gouden Schoen &amp; backdrop bij uitreiking", "€500"),
            ]),
            group([
                heading("Sponsoring op maat", 3),
                p("Vind je niet wat je zoekt? Andere ideeën? Wij werken graag andere op maat gemaakte opties voor je uit op basis van jouw budget en onze mogelijkheden."),
                p("Vanaf €100", "price"),
            ], "pricelist__group pricelist__group--custom"),
        ], "pricelist"),
    ]),
    section([
        group([
            heading("Samen de juiste formule"),
            p("We zijn er dan ook van overtuigd dat we samen de correcte formule kunnen vinden om uw commerciële doelstellingen samen met SK Lochristi te realiseren.", "lede"),
            p("Wij zien er dan ook naar uit u te mogen begroeten binnen onze vereniging."),
            p("Met sportieve groeten<br><strong>SK Lochristi</strong>", "signoff"),
        ]),
        group([
            contact("Matthias Esprit", '<a href="tel:+32497484043">0497 48 40 43</a>', '<a href="mailto:matthiasesprit@gmail.com">matthiasesprit@gmail.com</a>'),
            contact("Lieven Schoonjans", '<a href="tel:+32475602428">0475 60 24 28</a>', '<a href="mailto:lieven.schoonjans@pwc.com">lieven.schoonjans@pwc.com</a>'),
            contact("Algemeen", '<a href="mailto:info@sklochristi.be?subject=Sponsor%20worden">info@sklochristi.be</a>'),
        ], "sponsor-contacts"),
    ], cls="section--navy on-navy", wrap_cls="split split--top"),
    section([
        heading("Business Friends", cls="split__title"),
        group([
            p("De Business Friends zijn een 25-tal bedrijven uit Lochristi en omstreken die zich verenigd hebben om de voetbalploeg SK Lochristi, vooral financieel, te ondersteunen.", "lede"),
            p("We proberen er voor te zorgen dat er enkel bedrijven uit verschillende sectoren aansluiten, dit om geen concurrenten met elkaar op te zadelen. Voor het nieuwe sportcomplex in Beervelde zal ook vooral op de aangesloten aannemers beroep gedaan worden."),
            p("In praktijk zijn we zo'n beetje een serviceclub met als doel SK Lochristi te steunen. Regelmatig worden er activiteiten georganiseerd zoals bedrijfsbezoeken (Drukkerij Geers, tomaten- en slaserres in Roeselare, Flanders Horse Event…). Meestal eindigen we met een etentje!"),
        ], "prose"),
    ], anchor="business-friends", wrap_cls="split split--top"),
    section([
        section_head("Onze sponsors"),
        raw("skl/sponsors"),
    ], cls="section--line", anchor="sponsors"),
])

# ---------------------------------------------------------------- Contact
def who(title, name, link):
    return group([heading(title, 3), p(name), p(link, "text-link")], "who__item")


def ground(img, alt, title, address, uses, query, tel, phone):
    return group([
        image(img, alt, "ground__media"),
        group([heading(title, 3), p(address, "ground__address")]),
        p(uses, "uses"),
        group([
            buttons(button("Route plannen", f"https://www.google.com/maps/search/?api=1&amp;query={query}", external=True)),
            text_link(phone, f"tel:{tel}"),
        ], "row"),
    ], "ground", tag="article")


pages["contact"] = "\n\n".join([
    section([
        section_head("Wie contacteer ik?"),
        group([
            who("Nieuwe jeugdspeler", "Wesley Quatacker, assistent TVJO", '<a href="mailto:avjo@sklochristi.be">avjo@sklochristi.be</a>'),
            who("Jeugdopleiding", "Andy Van Rysselberghe, TVJO", '<a href="mailto:tvjo@sklochristi.be">tvjo@sklochristi.be</a>'),
            who("Seniors en algemene vragen", "Secretariaat", '<a href="mailto:info@sklochristi.be">info@sklochristi.be</a>'),
            who("Sponsoring", "Formules en contactpersonen", '<a href="/sponsor-worden/">Sponsor worden</a>'),
        ], "who"),
        p('Alle trainers en coördinatoren staan op <a href="/over-de-club/#bestuur">Over de club</a>.', "note"),
    ]),
    section([
        section_head("Onze complexen", "SK Lochristi speelt op twee complexen, allebei in Lochristi."),
        group([
            ground("assets/img/news/DSC_9441.jpg", "Wedstrijd van SK Lochristi", "Complex Bosdreef", "Bosdreef 57, 9080 Lochristi",
                   "Trainingen van de B-beloften en PU21, thuiswedstrijden van de zondagsreserven.", "Bosdreef+57+9080+Lochristi", "+3293550252", "09 355 02 52"),
            ground("assets/img/club/DSC_1422.jpg", "Jeugdploeg van SK Lochristi op het veld", "Complex Beerveldsebaan", "Beerveldsebaan 24, 9080 Lochristi",
                   "Thuiswedstrijden van de B-beloften en PU21.", "Beerveldsebaan+24+9080+Lochristi", "+3293559510", "09 355 95 10"),
        ], "grounds"),
        p("Aan te vullen door de club: een foto van elk complex. Nu staan er wedstrijdfoto's.", "placeholder-note"),
    ], cls="section--line", anchor="terreinen"),
])

# ---------------------------------------------------------------- Evenementen
pages["evenementen"] = section([raw("skl/events")], cls="section--tight")

# ---------------------------------------------------------------- Documenten
def docs_section(anchor, title, text, rubriek, line=False):
    return section([section_head(title, text), raw("skl/documents", {"rubriek": rubriek})],
                   cls="section--tight docs-group" + (" section--line" if line else ""), anchor=anchor)


FORMS_SPORT = [
    ("Sportongeval – Aangifteformulier", "https://sklochristi.be/download/sportongeval-aangifteformulier/"),
    ("Sportongeval – Werkwijze", "https://sklochristi.be/download/sportongeval-werkwijze/"),
    ("Regels van het voetbalspel 2022-2023", "https://sklochristi.be/download/regels-van-het-voetbalspel-2022-2023/"),
]
FORMS_ZIEKENKAS = [
    ("Helan", "https://sklochristi.be/download/tussenkomst-sport-helan-22-23/"),
    ("Vlaams Neutraal", "https://sklochristi.be/download/tussenkomst-sport-vlaams-neutraal-22-23/"),
    ("Neutraal Ziekenfonds Vlaanderen", "https://sklochristi.be/download/tussenkomst-sport-neutraal-ziekenfonds-vlaanderen-22-23/"),
    ("Liberale Mutualiteit", "https://sklochristi.be/download/tussenkomst-sport-liberale-mutualiteit-22-23/"),
    ("Christelijke Mutualiteit", "https://sklochristi.be/download/tussenkomst-sport-christelijke-mutualiteit-22-23/"),
    ("Solidaris", "https://sklochristi.be/download/tussenkomst-sport-solidaris-22-23/"),
]


def forms_list(items):
    return lst([f'<a href="{{{{FILE:{url}}}}}" target="_blank" rel="noreferrer noopener">{label}</a>' for label, url in items], cls="forms__list")


pages["documenten"] = "\n\n".join([
    group([group([lst([
        '<a href="#visie-en-waarden">Visie en waarden</a>',
        '<a href="#charters">Charters per rol</a>',
        '<a href="#afspraken">Afspraken en reglement</a>',
        '<a href="#meisjesvoetbal">Meisjesvoetbal</a>',
        '<a href="#formulieren">Formulieren</a>',
    ])], "wrap")], "subnav", tag="nav"),
    docs_section("visie-en-waarden", "Visie en waarden", "Waar SK Lochristi voor staat: fairplay, respect en voetbalplezier.", "visie-en-waarden"),
    docs_section("charters", "Charters per rol", "Wat de club verwacht van spelers, jeugdopleiders, afgevaardigden en ouders.", "charters", line=True),
    docs_section("afspraken", "Afspraken en reglement", "De praktische afspraken voor trainingen en wedstrijden, het huishoudelijk reglement en hoe de club omgaat met overtredingen van de fair play.", "afspraken"),
    docs_section("meisjesvoetbal", "Meisjesvoetbal", "Hoe SK Lochristi meisjes laat voetballen in gemengde ploegen, en bij wie je terechtkan.", "meisjesvoetbal", line=True),
    section([
        section_head("Formulieren", "Aangifte van een sportongeval, de spelregels en de formulieren voor een tussenkomst van je ziekenfonds."),
        group([
            group([heading("Sportongeval en reglement", 3), forms_list(FORMS_SPORT)], "forms__group"),
            group([
                heading("Tussenkomst ziekenkas", 3),
                p("Een tussenkomst in het lidgeld kan worden bekomen via uw ziekenfonds. U hoeft enkel het formulier van uw ziekenfonds af te drukken, eventueel te vervolledigen, te voorzien van een klever en dit in te dienen bij uw ziekenfonds. Formulieren voor het nieuwe seizoen worden steeds tegen medio september beschikbaar gesteld.", "forms__intro"),
                p("Formulieren seizoen 2022-2023", "forms__season"),
                forms_list(FORMS_ZIEKENKAS),
            ], "forms__group"),
        ], "forms"),
    ], cls="section--tight docs-group", anchor="formulieren"),
])

for slug, content in pages.items():
    (OUT / f"{slug}.html").write_text(content + "\n", encoding="utf-8")
    print("schreef", slug)
