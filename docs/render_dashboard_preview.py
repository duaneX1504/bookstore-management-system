from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "screenshots"
OUT.mkdir(exist_ok=True)
W, H = 1440, 980
C = {
    "paper": "#F7F8FB", "white": "#FFFFFF", "ink": "#202431", "muted": "#858A96",
    "line": "#E7E9EF", "red": "#DC2438", "red_dark": "#B51C2E", "navy": "#111827",
    "green": "#397A5B", "orange": "#BC8540", "blue": "#3653A5", "purple": "#654A9A",
}
FONT = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
BOLD = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
SERIF = "/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf"

def f(size, bold=False, serif=False):
    return ImageFont.truetype(SERIF if serif else (BOLD if bold else FONT), size)

def box(draw, coords, fill, outline=None, radius=8, width=1):
    draw.rounded_rectangle(coords, radius=radius, fill=fill, outline=outline, width=width)

def txt(draw, xy, value, size=12, color=None, bold=False, serif=False):
    draw.text(xy, str(value), font=f(size, bold, serif), fill=color or C["ink"])

def rule(draw, coords, fill=None, width=1):
    draw.line(coords, fill=fill or C["line"], width=width)

COMICS = [
    ("The Amazing Spider-Man", "The Amazing Spider-Man · Stan Lee", "Marvel Comics", "#1", "VF", "$5.99", "17", "M"),
    ("Batman: Year One", "Batman · Frank Miller", "DC Comics", "#404–407", "VF", "$14.99", "12", "D"),
    ("X-Men: Days of Future Past", "Uncanny X-Men · Chris Claremont", "Marvel Comics", "#141–142", "NM", "$19.99", "8", "M"),
    ("Watchmen", "Watchmen · Alan Moore", "DC Comics", "#1–12", "NM", "$19.99", "5", "D"),
]

def dashboard(form=False):
    im = Image.new("RGB", (W, H), C["paper"])
    d = ImageDraw.Draw(im)
    d.rectangle((0, 0, 242, H), fill=C["white"])
    rule(d, [(241, 0), (241, H)])
    box(d, (25, 25, 60, 60), C["navy"], radius=9)
    txt(d, (37, 30), "P", 20, C["white"], True, True)
    txt(d, (69, 25), "panel & page", 17, C["ink"], True, True)
    txt(d, (70, 49), "C O M I C   S H O P   M A N A G E R", 6, "#9398A3", True)
    txt(d, (27, 112), "S H O P   W O R K S P A C E", 8, "#9398A3", True)
    for y, label, icon, active in [(145, "Overview", "▦", True), (188, "Comic inventory", "▤", False), (231, "Orders", "▧", False), (274, "Publishers", "◫", False)]:
        if active:
            box(d, (15, y-8, 225, y+29), "#FFF0ED", radius=8)
        txt(d, (31, y), icon, 16, C["red"] if active else "#6F7480", True)
        txt(d, (61, y+2), label, 11, C["red_dark"] if active else "#6F7480", active)
    box(d, (18, 790, 224, 866), "#F8F6F2", radius=10)
    txt(d, (31, 803), "✦", 16, "#B49355", True)
    txt(d, (57, 804), "Keep the pull list ready", 9, "#4D493F", True)
    txt(d, (57, 824), "Check low stock before the", 8, "#898477")
    txt(d, (57, 838), "next release day.", 8, "#898477")
    rule(d, [(17, 890), (224, 890)])
    box(d, (27, 907, 60, 940), "#EEE7DF", radius=18)
    txt(d, (35, 917), "DH", 9, "#706149", True)
    txt(d, (70, 910), "Store Admin", 9, C["ink"], True)
    txt(d, (70, 927), "Comic shop team", 8, "#98958E")

    rule(d, [(242, 60), (W, 60)])
    txt(d, (275, 24), "Shop workspace   /   Overview", 10, "#858A96")
    txt(d, (1153, 24), "Fri, Nov 6, 2026", 9, "#999DA6")
    box(d, (1366, 16, 1394, 44), "#EEE7DF", radius=14)
    txt(d, (1372, 24), "DH", 8, "#706149", True)
    txt(d, (275, 88), "F R I D A Y ,   N O V E M B E R   6 ,   2 0 2 6", 8, "#969BA5", True)
    txt(d, (275, 111), "Welcome back, Store Admin", 23, C["ink"], True, True)
    txt(d, (640, 119), "✦", 14, "#C7A65B", True)
    txt(d, (275, 148), "Here’s what’s happening at your comic shop today.", 11, "#858A96")
    box(d, (1220, 106, 1395, 146), C["red"], radius=7)
    txt(d, (1240, 118), "+  Add a comic", 10, C["white"], True)

    cards = [
        ("▤", "8", "Comic editions", "Across Marvel and DC", "#FFF0ED", C["red"], "Catalog"),
        ("▣", "69", "Copies in stock", "Available to sell", "#EAF5EF", "#438060", "On the shelf"),
        ("▧", "2", "Open orders", "Pending or processing", "#FFF2E3", C["orange"], "Orders"),
        ("!", "4", "Low-stock editions", "Fewer than 8 copies", "#EDF2FF", C["blue"], "Pull list check"),
    ]
    for i, (icon, val, label, foot, bg, fg, tag) in enumerate(cards):
        x = 275 + i * 282
        box(d, (x, 177, x+267, 301), C["white"], C["line"], 9)
        box(d, (x+14, 191, x+43, 220), bg, radius=8)
        txt(d, (x+23, 196), icon, 14, fg, True)
        txt(d, (x+190, 199), tag, 8, fg, True)
        txt(d, (x+15, 231), val, 24, C["ink"], True, True)
        txt(d, (x+15, 262), label, 10, "#555A65")
        txt(d, (x+15, 280), foot, 8, "#9B9FAA")

    box(d, (275, 318, 1053, 903), C["white"], C["line"], 9)
    txt(d, (295, 337), "T H E   C O M I C   W A L L", 8, "#9398A3", True)
    txt(d, (295, 355), "Comic inventory", 17, C["ink"], True, True)
    txt(d, (295, 383), "Track series, issue numbers, condition, and quantity.", 9, "#858A96")
    txt(d, (955, 350), "Export list  ↗", 9, C["red_dark"], True)
    box(d, (295, 410, 713, 445), C["white"], "#E5E7EC", radius=6)
    txt(d, (307, 417), "⌕", 18, "#9297A1")
    txt(d, (337, 423), "Search comics, creators, series...", 9, "#A0A5AF")
    box(d, (721, 410, 989, 445), C["white"], "#E5E7EC", radius=6)
    txt(d, (733, 422), "Publisher", 8, "#969BA5")
    txt(d, (795, 422), "All publishers   ▾", 9, "#4F535D")
    box(d, (997, 410, 1033, 445), C["white"], "#E5E7EC", radius=6)
    txt(d, (1007, 416), "↻", 16, "#817D75")
    headers = [("COMIC / SERIES", 295), ("PUBLISHER", 556), ("ISSUE", 690), ("GRADE", 812), ("PRICE", 872), ("STOCK", 938)]
    for label, x in headers:
        txt(d, (x, 462), label, 7, "#9297A1", True)
    rule(d, [(295, 481), (1033, 481)])
    for i, row in enumerate(COMICS):
        title, subtitle, publisher, issue, grade, price, stock, mark = row
        y = 496 + i * 78
        cover = ["#F5D9DB", "#DFE7FA", "#E8E2F6", "#F7E9CA"][i]
        color = [C["red_dark"], C["blue"], C["purple"], "#94702C"][i]
        box(d, (296, y, 326, y+40), cover, radius=3)
        txt(d, (306, y+9), mark, 13, color, True, True)
        txt(d, (337, y+3), title[:30], 9, "#383B45", True)
        txt(d, (337, y+20), subtitle[:34], 7, "#9196A1")
        pub_bg, pub_fg = ("#FFF0ED", C["red_dark"]) if publisher.startswith("Marvel") else ("#EDF2FF", C["blue"])
        box(d, (556, y+10, 556+min(115,len(publisher)*5), y+30), pub_bg, radius=10)
        txt(d, (562, y+15), "Marvel" if mark=="M" else "DC", 8, pub_fg, True)
        txt(d, (690, y+13), issue, 8, "#555A65", True)
        grade_bg, grade_fg = ("#EAF5EF", C["green"]) if grade=="NM" else ("#EDF2FF", C["blue"])
        box(d, (812, y+8, 844, y+29), grade_bg, radius=5)
        txt(d, (819, y+13), grade, 8, grade_fg, True)
        txt(d, (872, y+13), price, 9, "#514F4A", True)
        txt(d, (938, y+13), stock, 8, C["orange"] if int(stock)<8 else C["green"], True)
        txt(d, (1010, y+12), "✎  ×", 10, "#8C8981")
        rule(d, [(295, y+61), (1033, y+61)])
    txt(d, (295, 833), "8 editions shown", 8, "#9B9FAA")
    txt(d, (748, 833), "Orders update available copies", 8, "#9B9FAA")

    box(d, (1068, 318, 1395, 903), C["white"], C["line"], 9)
    txt(d, (1085, 337), "P U L L   D E S K", 8, "#9398A3", True)
    txt(d, (1085, 355), "Recent orders", 16, C["ink"], True, True)
    txt(d, (1323, 361), "View all →", 8, C["red_dark"], True)
    orders = [
        ("Morgan Reed", "#2048", "Processing", "1 × The Amazing Spider-Man", "1 × X-Men: Days of Future Past", "$25.98"),
        ("Avery Johnson", "#2047", "Pending", "1 × Black Panther: A Nation Under Our Feet", "$17.99"),
    ]
    y = 399
    for order in orders:
        name, num, status, *details = order
        txt(d, (1085, y), name, 9, "#484641", True)
        txt(d, (1168, y), num, 8, "#A29E96")
        bg, fg = ("#FFF0ED", C["red_dark"]) if status == "Processing" else ("#FFF3E5", "#A87234")
        box(d, (1283, y-4, 1377, y+17), bg, radius=10)
        txt(d, (1292, y+2), status, 7, fg, True)
        total = details[-1]
        for j, item in enumerate(details[:-1]):
            txt(d, (1085, y+26+j*13), item[:45], 7, "#858A96")
        txt(d, (1085, y+57), total, 9, "#55514A", True)
        y += 120
        rule(d, [(1085, y-8), (1378, y-8)])
    box(d, (1085, 810, 1378, 870), "#F8F6F2", radius=7)
    txt(d, (1096, 819), "✦", 12, "#9A8B6B", True)
    txt(d, (1118, 820), "New orders reserve selected copies.", 8, "#8E8778")
    txt(d, (1118, 836), "Cancelling an order restores stock.", 8, "#8E8778")
    txt(d, (275, 930), "Panel & Page Comic Shop Manager", 8, "#9B9FAA")
    txt(d, (1260, 930), "Track every issue", 8, "#9B9FAA")

    if form:
        overlay = Image.new("RGBA", (W,H), (17,24,39,132))
        im = Image.alpha_composite(im.convert("RGBA"), overlay).convert("RGB")
        d = ImageDraw.Draw(im)
        box(d, (405, 125, 1035, 850), C["white"], "#E5E7EC", 13)
        txt(d, (435, 150), "C O M I C   C A T A L O G", 8, C["red_dark"], True)
        txt(d, (435, 174), "Add a comic", 22, C["ink"], True, True)
        txt(d, (996, 147), "×", 22, "#8F8B84")
        def field(x, y, w, label, value):
            txt(d, (x, y), label, 9, "#636875", True)
            box(d, (x, y+18, x+w, y+52), C["white"], "#E2E5EB", 6)
            txt(d, (x+9, y+29), value, 8, "#969BA5")
        field(435, 222, 275, "Comic title", "Batman: Year One")
        field(730, 222, 275, "Series title", "Batman")
        field(435, 294, 275, "Issue or collected range", "404–407")
        field(730, 294, 275, "Store catalog code", "DC-BAT-Y1")
        field(435, 366, 275, "Publisher", "DC Comics   ▾")
        field(730, 366, 275, "Lead creator", "Frank Miller   ▾")
        field(435, 438, 275, "Genre", "Crime and Noir   ▾")
        field(730, 438, 275, "Format", "Trade Paperback   ▾")
        field(435, 510, 275, "Condition grade", "VF · Very Fine   ▾")
        field(730, 510, 275, "Store price ($)", "14.99")
        field(435, 582, 570, "Copies in stock", "12")
        rule(d, [(435, 735), (1005, 735)])
        box(d, (788, 768, 884, 805), "#F1EFEA", radius=7)
        txt(d, (810, 779), "Cancel", 9, "#625F58", True)
        box(d, (894, 768, 1005, 805), C["red"], radius=7)
        txt(d, (916, 779), "Save comic", 9, C["white"], True)
    return im

dashboard().save(OUT / "bookstore-dashboard.png")
dashboard(True).save(OUT / "book-form.png")
print("Wrote comic shop dashboard and comic entry form previews.")
