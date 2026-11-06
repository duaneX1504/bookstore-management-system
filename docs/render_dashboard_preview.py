from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'screenshots'
OUT.mkdir(exist_ok=True)
W, H = 1440, 980
COL = {'paper':'#FBFAF7','white':'#FFFFFF','ink':'#262522','muted':'#85837E','line':'#ECE9E2','purple':'#6D5CE8','navy':'#24243A','green':'#397A5B','orange':'#BC8540','red':'#D47761'}
font_path='/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'
bold_path='/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'
serif_path='/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf'
def f(size,bold=False,serif=False): return ImageFont.truetype(serif_path if serif else (bold_path if bold else font_path),size)
def box(draw,xy,fill,outline=None,r=8,w=1): draw.rounded_rectangle(xy,radius=r,fill=fill,outline=outline,width=w)
def txt(draw,xy,text,size=13,color=None,bold=False,serif=False): draw.text(xy,text,font=f(size,bold,serif),fill=color or COL['ink'])
def line(draw,xy,fill=None,w=1): draw.line(xy,fill=fill or COL['line'],width=w)

def dashboard(form=False):
 im=Image.new('RGB',(W,H),COL['paper']);d=ImageDraw.Draw(im)
 d.rectangle((0,0,242,H),fill=COL['white']);line(d,[(241,0),(241,H)])
 box(d,(25,27,60,62),COL['navy'],r=10);txt(d,(37,31),'P',21,COL['white'],True,True);txt(d,(69,27),'page & pine',18,COL['ink'],True,True);txt(d,(70,51),'B O O K S T O R E   M A N A G E R',7,'#A09D95',True)
 txt(d,(27,119),'W O R K S P A C E',9,'#A19E96',True)
 for y,label,icon,active in [(151,'Overview','▦',1),(196,'Book inventory','▤',0),(241,'Orders','▧',0),(286,'Catalog','◫',0)]:
  if active:box(d,(15,y-8,225,y+30),'#F1EFFF',r=8)
  txt(d,(31,y),icon,17,COL['purple'] if active else '#716F69',True);txt(d,(61,y+3),label,12,COL['purple'] if active else '#716F69',active)
 box(d,(18,790,224,868),'#F8F6F1',r=10);txt(d,(31,804),'✦',17,'#B49355',True);txt(d,(57,804),'Keep the shelves ready',10,'#4D493F',True);txt(d,(57,825),'Check low stock before your',9,'#898477');txt(d,(57,839),'next order arrives.',9,'#898477')
 line(d,[(17,891),(224,891)]);box(d,(27,910,60,943),'#E9E4D8',r=20);txt(d,(35,920),'DH',10,'#706149',True);txt(d,(70,913),'Store Admin',10,COL['ink'],True);txt(d,(70,930),'Bookshop team',9,'#98958E')
 # topbar
 line(d,[(242,61),(W,61)]);txt(d,(275,25),'Workspace   /   Overview',11,'#8D8A83');txt(d,(1150,25),'Fri, Nov 6, 2026',10,'#99958E');box(d,(1366,18,1394,46),'#E9E4D8',r=14);txt(d,(1372,27),'DH',8,'#706149',True)
 txt(d,(275,89),'F R I D A Y ,   N O V E M B E R   6 ,   2 0 2 6',9,'#A5A197',True);txt(d,(275,112),'Good afternoon, Store Admin',26,COL['ink'],True,True);txt(d,(643,121),'✦',16,'#C7A65B',True);txt(d,(275,151),'Here’s what’s happening at your bookstore today.',12,'#8F8C86');box(d,(1220,109,1395,149),COL['purple'],r=7);txt(d,(1241,120),'+  Add a book',11,COL['white'],True)
 cards=[('▤','4','Books in catalog','Across all categories','#F0EDFF',COL['purple'],'Catalog'),('▣','50','Units on hand','Available to sell','#EAF5EF','#438060','In stock'),('▧','2','Open orders','Pending or processing','#FFF2E3',COL['orange'],'Orders'),('!','1','Low stock titles','Fewer than 8 copies','#FFF0ED',COL['red'],'Needs attention')]
 for i,(ico,val,label,foot,bg,fg,tag) in enumerate(cards):
  x=275+i*282;box(d,(x,181,x+267,305),COL['white'],outline=COL['line'],r=9);box(d,(x+14,195,x+43,224),bg,r=8);txt(d,(x+23,200),ico,15,fg,True);txt(d,(x+211,203),tag,9,fg if tag in ('In stock','Needs attention') else '#AAA69D',True);txt(d,(x+15,235),val,26,COL['ink'],True,True);txt(d,(x+15,267),label,11,'#55534E');txt(d,(x+15,285),foot,9,'#AAA69D')
 # inventory panel
 box(d,(275,322,1053,904),COL['white'],outline=COL['line'],r=9);txt(d,(295,341),'Y O U R   C O L L E C T I O N',9,'#A19E96',True);txt(d,(295,360),'Book inventory',18,COL['ink'],True,True);txt(d,(295,388),'Manage titles, pricing, and stock levels.',10,'#8F8C86');txt(d,(955,355),'Export list  ↗',10,COL['purple'],True)
 box(d,(295,416,713,451),COL['white'],outline='#E9E6DF',r=6);txt(d,(307,424),'⌕',20,'#9A968F');txt(d,(337,429),'Search titles, authors, ISBN...',10,'#B3AFA8');box(d,(721,416,989,451),COL['white'],outline='#E9E6DF',r=6);txt(d,(733,428),'Category',9,'#96928A');txt(d,(802,428),'All categories   ▾',10,'#4F4D48');box(d,(997,416,1033,451),COL['white'],outline='#E9E6DF',r=6);txt(d,(1007,421),'↻',17,'#817D75')
 headers=[('BOOK',295),('ISBN',555),('CATEGORY',695),('PRICE',850),('STOCK',936)]
 for label,x in headers:txt(d,(x,469),label,8,'#A29E96',True)
 line(d,[(295,489),(1033,489)])
 rows=[('Kindred','Octavia E. Butler','9780446675505','Science Fiction','$16.99','14 copies','#E8DCCC'),('Atomic Habits','James Clear','9780735211292','Personal Development','$21.50','22 copies','#E5E0EF'),('Beloved','Toni Morrison','9781400033416','Literary Fiction','$15.00','8 copies','#E2E8DD'),('Parable of the Sower','Octavia E. Butler','9780807083697','Science Fiction','$17.25','6 copies','#F0E0DF')]
 for i,row in enumerate(rows):
  y=505+i*77;title,author,isbn,cat,price,stock,cover=row
  box(d,(296,y,326,y+39),cover,r=3);txt(d,(306,y+9),title[0],13,'#746247',True,True);txt(d,(338,y+3),title,10,'#383733',True);txt(d,(338,y+21),author,9,'#A19D94');txt(d,(555,y+14),isbn,9,'#6D6A64');box(d,(695,y+12,695+min(130,max(82,len(cat)*5)),y+31),'#F4F1EA',r=10);txt(d,(702,y+18),cat,8,'#79746A');txt(d,(850,y+14),price,10,'#514F4A',True);txt(d,(936,y+14),stock,9,COL['orange'] if i==3 else COL['green'],True);txt(d,(1010,y+13),'✎   ×',11,'#8C8981');line(d,[(295,y+60),(1033,y+60)])
 txt(d,(295,865),'4 books shown',9,'#AAA69E');txt(d,(748,865),'Stock levels update when orders are placed',8,'#AAA69E')
 # orders panel
 box(d,(1068,322,1395,904),COL['white'],outline=COL['line'],r=9);txt(d,(1085,341),'S A L E S',9,'#A19E96',True);txt(d,(1085,360),'Recent orders',17,COL['ink'],True,True);txt(d,(1323,366),'View all →',9,COL['purple'],True)
 order_data=[('Morgan Reed','#2048','Processing','1 × Kindred','1 × Atomic Habits','$38.49'),('Avery Johnson','#2047','Pending','2 × Beloved','$30.00')]
 y=402
 for name,num,status,*details in order_data:
  txt(d,(1085,y),name,10,'#484641',True);txt(d,(1168,y),num,9,'#A29E96');box(d,(1283,y-4,1377,y+17),'#F0EDFF' if status=='Processing' else '#FFF3E5',r=10);txt(d,(1293,y+2),status,8,'#6558C7' if status=='Processing' else '#A87234',True)
  total=details[-1];linestr='  •  '.join(details[:-1]);txt(d,(1085,y+27),linestr,8,'#939087');txt(d,(1085,y+50),total,10,'#55514A',True)
  y+=112;line(d,[(1085,y-5),(1378,y-5)])
 box(d,(1085,810,1378,874),'#F8F6F1',r=7);txt(d,(1096,821),'✦',13,'#9A8B6B',True);txt(d,(1118,821),'New orders automatically reduce',8,'#8E8778');txt(d,(1118,836),'the available stock for each book.',8,'#8E8778')
 txt(d,(275,933),'Page & Pine Bookstore Manager',9,'#B0ACA3');txt(d,(1263,933),'Inventory made simple',9,'#B0ACA3')
 if form:
  overlay=Image.new('RGBA',(W,H),(29,27,39,115));im=Image.alpha_composite(im.convert('RGBA'),overlay).convert('RGB');d=ImageDraw.Draw(im)
  box(d,(460,215,980,790),COL['white'],outline='#E8E5DE',r=13);txt(d,(485,239),'C A T A L O G',9,COL['purple'],True);txt(d,(485,261),'Add a book',23,COL['ink'],True,True);txt(d,(944,238),'×',24,'#8F8B84')
  def field(x,y,w,label,value):
   txt(d,(x,y),label,10,'#716E67',True);box(d,(x,y+20,x+w,y+56),COL['white'],outline='#E7E3DC',r=6);txt(d,(x+10,y+32),value,10,'#A19D94')
  field(485,311,470,'Book title','Enter a title')
  field(485,393,220,'ISBN','13-digit ISBN');field(735,393,220,'Publisher','Publisher name')
  field(485,475,220,'Author','Select an author  ▾');field(735,475,220,'Category','Select a category  ▾')
  field(485,557,220,'Price ($)','0.00');field(735,557,220,'Stock quantity','0')
  line(d,[(485,675),(955,675)]);box(d,(745,704,837,741),'#F1EFEA',r=7);txt(d,(766,716),'Cancel',10,'#625F58',True);box(d,(847,704,955,741),COL['purple'],r=7);txt(d,(869,716),'Save book',10,COL['white'],True)
 return im

dashboard().save(OUT/'bookstore-dashboard.png')
dashboard(True).save(OUT/'book-form.png')
print('Wrote dashboard and add-book form previews.')
