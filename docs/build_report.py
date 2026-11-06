from pathlib import Path
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs" / "project-report.docx"
doc = Document()
section = doc.sections[0]
section.top_margin = Inches(.65)
section.bottom_margin = Inches(.65)
section.left_margin = Inches(.72)
section.right_margin = Inches(.72)

NAVY = "111827"
RED = "C51E31"
GRAY = "E7E9EF"
BODY = "303441"

styles = doc.styles
styles["Normal"].font.name = "Aptos"
styles["Normal"].font.size = Pt(10)
styles["Normal"].font.color.rgb = RGBColor.from_string(BODY)
styles["Normal"].paragraph_format.space_after = Pt(5)
for name, size in [("Title", 25), ("Heading 1", 16), ("Heading 2", 12)]:
    style = styles[name]
    style.font.name = "Aptos Display"
    style.font.size = Pt(size)
    style.font.bold = True
    style.font.color.rgb = RGBColor.from_string("000000")
    style.paragraph_format.space_before = Pt(10 if name != "Title" else 0)
    style.paragraph_format.space_after = Pt(5)

def shade(cell, fill):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:fill"), fill)
    tc_pr.append(shd)

def cell_text(cell, text, bold=False, color=BODY, size=8.5):
    cell.text = ""
    p = cell.paragraphs[0]
    p.paragraph_format.space_after = Pt(1)
    r = p.add_run(text)
    r.bold = bold
    r.font.name = "Aptos"
    r.font.size = Pt(size)
    r.font.color.rgb = RGBColor.from_string(color)
    cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER

def table(headers, rows, widths=None):
    t = doc.add_table(rows=1, cols=len(headers))
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    t.style = "Table Grid"
    for i, h in enumerate(headers):
        cell_text(t.rows[0].cells[i], h, True, "FFFFFF", 8.5)
        shade(t.rows[0].cells[i], NAVY)
    for ridx, row in enumerate(rows):
        cells = t.add_row().cells
        for i, value in enumerate(row):
            cell_text(cells[i], value, False, BODY, 8)
            if ridx % 2 == 1:
                shade(cells[i], "F3F5F8")
    if widths:
        for row in t.rows:
            for i, width in enumerate(widths):
                row.cells[i].width = Inches(width)
    doc.add_paragraph().paragraph_format.space_after = Pt(1)
    return t

def bullet(text):
    p = doc.add_paragraph(style="List Bullet")
    p.paragraph_format.space_after = Pt(2)
    p.add_run(text)
    return p

def caption(text):
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_after = Pt(6)
    r = p.add_run(text)
    r.italic = True
    r.font.size = Pt(8)
    r.font.color.rgb = RGBColor.from_string("6B7280")

p = doc.add_paragraph(style="Title")
p.add_run("Panel and Page Comic Shop Management System")
p = doc.add_paragraph()
p.paragraph_format.space_after = Pt(2)
r = p.add_run("Relational Database Project")
r.bold = True
r.font.size = Pt(12)
r.font.color.rgb = RGBColor.from_string(RED)
table(["Project detail", "Value"], [
    ("Prepared by", "Duane Harris"),
    ("Presentation date", "November 19, 2026"),
    ("Project repository", "https://github.com/duaneX1504/bookstore-management-system"),
], [1.65, 5.6])

doc.add_heading("Project overview", level=1)
doc.add_paragraph(
    "Panel and Page is an inventory and order manager for a comic shop. It tracks comic editions by title, "
    "series, issue or collected range, publisher, lead creator, genre, format, condition, store price, and stock. "
    "The dashboard helps staff maintain the catalog, check low-stock editions, search and filter inventory, "
    "export a CSV, and review recent orders."
)
doc.add_paragraph(
    "The seeded catalog contains real Marvel and DC titles, including The Amazing Spider-Man, "
    "X-Men: Days of Future Past, Batman: Year One, and Watchmen. Store prices, inventory amounts, "
    "condition grades, and catalog codes are fictional practice values."
)

doc.add_heading("Languages and tools", level=1)
table(["Layer", "Technology"], [
    ("Frontend", "HTML, CSS, JavaScript"),
    ("Backend", "PHP 8 with PDO"),
    ("Relational database", "MySQL 8 or MariaDB"),
    ("Local environment", "XAMPP: Apache and MySQL"),
    ("API", "REST-style HTTP requests with JSON responses"),
], [2.0, 5.25])

doc.add_heading("Database design", level=1)
doc.add_paragraph(
    "The database has seven related tables. Publishers, creators, and genres are stored once and referenced "
    "from comic records. Each record identifies one lead creator to keep this class model manageable. "
    "Customers place orders, and order_items connects orders with comic editions using a composite primary key."
)
table(["Table", "Primary key", "Main fields and relationships"], [
    ("publishers", "publisher_id", "publisher_name (unique)"),
    ("creators", "creator_id", "creator_name (unique)"),
    ("genres", "genre_id", "genre_name (unique)"),
    ("comics", "comic_id", "catalog_code, title, series_title, issue_number, publisher_id FK, creator_id FK, genre_id FK, format, condition_grade, price, stock_qty"),
    ("customers", "customer_id", "full_name, email (unique), phone"),
    ("orders", "order_id", "customer_id FK, order_date, status"),
    ("order_items", "order_id + comic_id", "both columns are foreign keys; quantity and unit_price"),
], [1.25, 1.4, 4.6])
doc.add_paragraph(
    "Foreign keys protect related data. Unique constraints prevent duplicate publisher, creator, genre, "
    "catalog-code, and customer-email values. Price and stock cannot be negative. The order API checks stock "
    "and saves an order, its lines, and the stock deduction in one transaction. A cancellation restores reserved "
    "copies. Each order line stores its unit price so prior orders keep the price charged at checkout."
)
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.add_run().add_picture(str(ROOT / "screenshots" / "database-erd.png"), width=Inches(6.8))
caption("Figure 1. Comic shop relational database")

doc.add_heading("Frontend and API", level=1)
doc.add_paragraph(
    "The dashboard summarizes the number of comic editions, available copies, open orders, and low-stock "
    "editions. Staff can create, read, update, and delete comic records. The form stores comic metadata and "
    "store inventory information. Search includes title, series, issue range, creator, and catalog code. "
    "A publisher filter narrows the catalog, while CSV export downloads the current inventory."
)
doc.add_paragraph(
    "The PHP API uses prepared PDO statements and validates incoming values. Comics, publishers, creators, "
    "genres, and customers support GET, POST, PUT/PATCH, and DELETE requests. Orders support reads, creation "
    "with stock deduction, status changes, and deletion with stock restoration. Responses use JSON and HTTP "
    "status codes for success, invalid input, missing records, and related-record conflicts."
)

doc.add_heading("Dashboard screenshot", level=1)
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.add_run().add_picture(str(ROOT / "screenshots" / "bookstore-dashboard.png"), width=Inches(6.8))
caption("Figure 2. Panel and Page comic shop dashboard preview")

doc.add_heading("Comic entry form", level=1)
doc.add_paragraph(
    "The form records a title and series, issue or collected range, store catalog code, publisher, lead creator, "
    "genre, format, condition grade, price, and stock quantity."
)
p = doc.add_paragraph()
p.alignment = WD_ALIGN_PARAGRAPH.CENTER
p.add_run().add_picture(str(ROOT / "screenshots" / "book-form.png"), width=Inches(6.1))
caption("Figure 3. Add-comic form preview")

doc.add_heading("Setup and class demonstration", level=1)
doc.add_paragraph(
    "To recreate the project, place the folder in C:\\xampp\\htdocs, start Apache and MySQL, import "
    "database/schema.sql through phpMyAdmin, copy config.example.php to config.php, and open "
    "http://localhost/bookstore-management-system/. The SQL file creates comic_store_db and seeds sample records."
)
for item in [
    "Show the Marvel and DC sample editions, then create or edit a temporary comic record.",
    "Use Postman to place an order and point out the stock deduction.",
    "Cancel the order and show the reserved copies return to inventory.",
    "Explain the foreign keys, order_items composite key, prepared statements, and transaction rollback.",
]:
    bullet(item)

doc.add_heading("Project repository and title references", level=1)
doc.add_paragraph("GitHub: https://github.com/duaneX1504/bookstore-management-system")
doc.add_paragraph("Marvel catalog: https://www.marvel.com/comics/series/32866/the_amazing_spider-man")
doc.add_paragraph("Marvel collection: https://www.marvel.com/comics/collection/41778/x-men_days_of_future_past_tpb_new_printing_trade_paperback")
doc.add_paragraph("DC graphic novel: https://www.dc.com/graphic-novels/batman-year-one-1987/absolute-batman-year-one")
doc.add_paragraph("DC graphic novel: https://www.dc.com/graphic-novels/watchmen-1986/watchmen")
doc.add_paragraph(
    "The project is a classroom demonstration. It does not include user accounts, payment processing, "
    "shipping integrations, barcode scanning, or public hosting. A future version could support multiple "
    "credited creators per edition with a comic_creators junction table."
)

footer = section.footer.paragraphs[0]
footer.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = footer.add_run("Panel and Page  |  Database Systems Project")
r.font.size = Pt(8)
r.font.color.rgb = RGBColor.from_string("6B7280")

doc.core_properties.title = "Panel and Page Comic Shop Management System"
doc.core_properties.author = "Duane Harris"
doc.core_properties.subject = "Relational database software project"
doc.save(OUT)
print(OUT)
