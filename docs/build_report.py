from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / 'docs'

def shade(cell, fill):
    tcPr = cell._tc.get_or_add_tcPr(); shd = OxmlElement('w:shd'); shd.set(qn('w:fill'), fill); tcPr.append(shd)
def set_cell(cell, text, bold=False, color='292823', size=9):
    cell.text=''; p=cell.paragraphs[0]; p.paragraph_format.space_after=Pt(2); r=p.add_run(text); r.bold=bold; r.font.name='Aptos'; r.font.size=Pt(size); r.font.color.rgb=RGBColor.from_string(color); cell.vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER

doc=Document(); sec=doc.sections[0]; sec.top_margin=Inches(.65); sec.bottom_margin=Inches(.65); sec.left_margin=Inches(.75); sec.right_margin=Inches(.75)
styles=doc.styles
styles['Normal'].font.name='Aptos'; styles['Normal']._element.rPr.rFonts.set(qn('w:ascii'),'Aptos'); styles['Normal']._element.rPr.rFonts.set(qn('w:hAnsi'),'Aptos'); styles['Normal'].font.size=Pt(10); styles['Normal'].font.color.rgb=RGBColor(55,53,48)
styles['Normal'].paragraph_format.space_after=Pt(6); styles['Normal'].paragraph_format.line_spacing=1.08
for sty,size,color in [('Heading 1',17,'24243A'),('Heading 2',12,'6D5CE8')]:
 s=styles[sty]; s.font.name='Aptos Display'; s._element.rPr.rFonts.set(qn('w:ascii'),'Aptos Display'); s._element.rPr.rFonts.set(qn('w:hAnsi'),'Aptos Display'); s.font.size=Pt(size); s.font.bold=True; s.font.color.rgb=RGBColor.from_string(color); s.paragraph_format.space_before=Pt(10); s.paragraph_format.space_after=Pt(5)

p=doc.add_paragraph(style='Title'); p.alignment=WD_ALIGN_PARAGRAPH.LEFT; p.paragraph_format.space_after=Pt(3); r=p.add_run('Page & Pine Bookstore Management System'); r.font.name='Aptos Display'; r.font.size=Pt(25); r.font.bold=True; r.font.color.rgb=RGBColor(36,36,58)
p=doc.add_paragraph(); p.paragraph_format.space_after=Pt(12); r=p.add_run('Relational Database Project Report'); r.font.size=Pt(14); r.font.color.rgb=RGBColor(109,92,232)
for label,value in [('Student','Duane Harris'),('Presentation date','November 19, 2026'),('Repository URL','https://github.com/duaneX1504/bookstore-management-system')]:
 p=doc.add_paragraph(); p.paragraph_format.space_after=Pt(2); rr=p.add_run(label+': '); rr.bold=True; p.add_run(value)

doc.add_heading('Project overview',level=1)
doc.add_paragraph('Page & Pine is a bookstore inventory and order management system. The dashboard gives a store employee a view of the book catalog, available stock, low-stock titles, and recent orders. The project shows how a relational database can support a small bookstore workflow from catalog entry through order fulfillment.')
doc.add_heading('Languages and tools',level=2)
t=doc.add_table(rows=1,cols=2); t.alignment=WD_TABLE_ALIGNMENT.LEFT; t.style='Table Grid'
for i,h in enumerate(['Layer','Technology']): set_cell(t.rows[0].cells[i],h,True,'FFFFFF',9); shade(t.rows[0].cells[i],'24243A')
for row in [('Frontend','HTML, CSS, JavaScript'),('Backend','PHP 8 with PDO'),('Database','MySQL 8 / MariaDB'),('Local server','XAMPP Apache and MySQL'),('API format','REST-style HTTP with JSON')]:
 cells=t.add_row().cells; set_cell(cells[0],row[0],True); set_cell(cells[1],row[1]);
doc.add_heading('Main functions',level=2)
for item in ['Create, view, update, and delete books through the dashboard and JSON API.','Search books by title, author, or ISBN; filter by category and export the catalog to CSV.','View order totals and line items, then update order status.','Create orders through the API with stock validation and a database transaction. Cancelled orders restore the reserved stock.']:
 p=doc.add_paragraph(style='List Bullet'); p.paragraph_format.space_after=Pt(2); p.add_run(item)

doc.add_page_break(); doc.add_heading('Database design',level=1)
doc.add_paragraph('The schema has six tables. Authors, categories, customers, and books are stored separately to reduce repeated data. Orders and books form a many-to-many relationship, resolved by the order_items table.')
t=doc.add_table(rows=1,cols=3);t.alignment=WD_TABLE_ALIGNMENT.LEFT;t.style='Table Grid'
for i,h in enumerate(['Table','Key','Main attributes']):set_cell(t.rows[0].cells[i],h,True,'FFFFFF',9);shade(t.rows[0].cells[i],'24243A')
rows=[('authors','author_id PK','name, email'),('categories','category_id PK','category_name'),('books','book_id PK','isbn, title, author_id FK, category_id FK, publisher, price, stock_qty, created_at'),('customers','customer_id PK','full_name, email, phone'),('orders','order_id PK','customer_id FK, order_date, status'),('order_items','order_id + book_id PK','order_id FK, book_id FK, quantity, unit_price')]
for row in rows:
 cells=t.add_row().cells
 for i,v in enumerate(row):set_cell(cells[i],v,bold=(i==0),size=8)
pp=doc.add_paragraph();pp.alignment=WD_ALIGN_PARAGRAPH.CENTER;pp.paragraph_format.space_before=Pt(7);pp.add_run().add_picture(str(ROOT/'screenshots'/'database-erd.png'),width=Inches(6.65))
cap=doc.add_paragraph();cap.alignment=WD_ALIGN_PARAGRAPH.CENTER;cap.paragraph_format.space_after=Pt(2);rr=cap.add_run('Figure 1. Primary and foreign key relationships');rr.italic=True;rr.font.size=Pt(8);rr.font.color.rgb=RGBColor(110,106,98)
doc.add_heading('Keys, constraints, and transactions',level=2)
doc.add_paragraph('Foreign keys link books to authors and categories, orders to customers, and order items to both orders and books. ISBN, customer email, and category name are unique. Prices and stock cannot be negative, and order quantities must be positive. The order API checks inventory, saves the order and line items, and reduces stock in one transaction. If validation fails, the database rolls back the change. The order item stores unit_price so the saved order total remains consistent if the catalog price changes later.')
doc.add_heading('API design',level=2)
doc.add_paragraph('The PHP API accepts GET, POST, PUT/PATCH, and DELETE requests for books, authors, categories, and customers. Orders support reading, transactional creation, status updates, and deletion with stock restoration. Prepared PDO statements protect SQL operations. Input checks return JSON errors with HTTP status codes for invalid input, missing records, duplicate values, or related-record conflicts.')

# screenshot page 1
doc.add_page_break(); doc.add_heading('User interface',level=1)
doc.add_paragraph('Dashboard preview using the project’s seeded example records. The running dashboard displays the same inventory controls, summary metrics, and order details.')
p=doc.add_paragraph();p.alignment=WD_ALIGN_PARAGRAPH.CENTER;p.paragraph_format.space_after=Pt(4);p.add_run().add_picture(str(ROOT/'screenshots'/'bookstore-dashboard.png'),width=Inches(6.75))
p=doc.add_paragraph();p.alignment=WD_ALIGN_PARAGRAPH.CENTER;r=p.add_run('Figure 2. Bookstore dashboard preview');r.italic=True;r.font.size=Pt(9);r.font.color.rgb=RGBColor(110,106,98)

# screenshot page 2
doc.add_page_break(); doc.add_heading('Book entry form',level=1)
doc.add_paragraph('The add and edit form keeps the book fields together. The same form sends data to the PHP API for insertion or update.')
p=doc.add_paragraph();p.alignment=WD_ALIGN_PARAGRAPH.CENTER;p.add_run().add_picture(str(ROOT/'screenshots'/'book-form.png'),width=Inches(6.75))
p=doc.add_paragraph();p.alignment=WD_ALIGN_PARAGRAPH.CENTER;r=p.add_run('Figure 3. Add-book form preview');r.italic=True;r.font.size=Pt(9);r.font.color.rgb=RGBColor(110,106,98)

doc.add_page_break();doc.add_heading('Recreation and demonstration',level=1)
doc.add_paragraph('The project README includes complete setup instructions, the database schema, API reference, example requests, and Postman steps. To recreate the system, copy the folder into C:\\xampp\\htdocs, start Apache and MySQL, import database/schema.sql through phpMyAdmin, copy config.example.php to config.php, and open http://localhost/bookstore-management-system/.')
doc.add_heading('Suggested live walkthrough',level=2)
for item in ['Show the dashboard and filter the catalog. Add a temporary book, edit its stock, then remove the test record.','In Postman, create an order with one or more books. Show the API response and the reduced stock in the dashboard.','Update the order status to Cancelled and show the stock restoration.','Explain the order_items composite key, foreign keys, prepared statements, and transaction rollback.']:
 p=doc.add_paragraph(style='List Number');p.paragraph_format.space_after=Pt(3);p.add_run(item)
doc.add_heading('GitHub repository',level=2)
doc.add_paragraph('Repository URL: https://github.com/duaneX1504/bookstore-management-system. The repository contains the source code, database schema, setup instructions, screenshots, and recreation documentation.')
doc.add_heading('Scope',level=2)
doc.add_paragraph('This classroom version does not include user accounts, staff permissions, payment processing, shipping-provider integrations, or public hosting. The sample customer records are fictional.')

# footer
for section in doc.sections:
 p=section.footer.paragraphs[0];p.alignment=WD_ALIGN_PARAGRAPH.RIGHT
 r=p.add_run('Page & Pine  |  Database Systems Project');r.font.size=Pt(8);r.font.color.rgb=RGBColor(150,146,137)
out=DOCS/'project-report.docx';doc.save(out);print(out)
