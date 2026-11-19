import fitz
import base64

logo_path = r'C:\xampp\htdocs\path\teacher-admin\assets\images\path_creation_logo.png'
with open(logo_path, 'rb') as f:
    logo_b64 = base64.b64encode(f.read()).decode('utf-8')

# Let's test the layout in SVG to verify all pixel measurements
test_page_svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 320" width="900" height="320">
    <defs>
        <!-- Laurel Leaf -->
        <g id="laurel-leaf">
            <path d="M 0,0 Q -4.2,-8.5 0,-17 Q 4.2,-8.5 0,0 Z" fill="#d9232d" />
        </g>
    </defs>

    <!-- Page Container -->
    <rect x="0" y="0" width="900" height="320" rx="18" fill="#ffffff" stroke="#d1d5db" stroke-width="1.5" />

    <!-- TOP-RIGHT DECORATIVE GEOMETRIC CORNER -->
    <g transform="translate(710, 0)">
        <!-- Black Corner Triangle -->
        <polygon points="105,0 190,0 190,75" fill="#18181b" />
        
        <!-- Bold Red Tab with rounded bottom-left -->
        <path d="M 22,0 
                 L 72,50 
                 Q 83,61 100,61 
                 L 136,61 
                 L 96,0 
                 Z" fill="#d9232d" />

        <!-- Thin Red Diagonal Accent Stripe -->
        <polygon points="140,67 148,67 190,109 190,117 182,117" fill="#d9232d" />
    </g>

    <!-- SCHOOL BRANDING: LOGO, DIVIDER, NAME, SLOGAN -->
    <g transform="translate(42, 26)">
        <!-- Logo Image -->
        <image href="data:image/png;base64,{logo_b64}" x="0" y="0" width="52" height="52" preserveAspectRatio="xMidYMid meet" />
        
        <!-- Vertical Divider -->
        <line x1="66" y1="2" x2="66" y2="50" stroke="#cbd5e1" stroke-width="1.8" />
        
        <!-- School Name: Path Creation Academy -->
        <!-- 'Path Creation' bold italic black -->
        <text x="80" y="27" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="27" font-weight="800" font-style="italic" fill="#111827" letter-spacing="-0.3">Path Creation</text>
        <!-- 'Academy' bold upright red -->
        <text x="268" y="27" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="27" font-weight="800" font-style="normal" fill="#d9232d" letter-spacing="-0.3">Academy</text>
        
        <!-- Slogan: LEARN - BUILD - GROW -->
        <text x="82" y="46" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="10.5" font-weight="700" fill="#4b5563" letter-spacing="4.5">LEARN &bull; BUILD &bull; GROW</text>
    </g>

    <!-- EMBLEM & TITLE: GRADUATION CAP, LAUREL WREATH, RESULT REPORT CARD -->
    <g transform="translate(450, 135)">
        <!-- Center: Graduation Cap sitting above title -->
        <g transform="translate(0, -22)">
            <!-- Diamond Mortarboard -->
            <polygon points="0,-15 38,-3 0,9 -38,-3" fill="#d9232d" />
            <!-- Under rim 3D shadow -->
            <polygon points="-38,-3 0,9 0,11 -38,-1" fill="#b91c1c" />
            <polygon points="38,-3 0,9 0,11 38,-1" fill="#b91c1c" />
            <!-- Skull Cap base -->
            <path d="M -16,5 L -16,16 C -16,23 16,23 16,16 L 16,5 Q 0,11 -16,5 Z" fill="#d9232d" />
            <!-- Center Button -->
            <circle cx="0" cy="-3" r="2.2" fill="#d9232d" />
            <!-- Tassel hanging left -->
            <path d="M 0,-3 L -21,4 L -23,16" stroke="#d9232d" stroke-width="2" fill="none" stroke-linecap="round" />
            <polygon points="-26,16 -21,16 -21,25 -26,25" fill="#d9232d" />
        </g>

        <!-- Left Laurel Branch -->
        <g transform="translate(-235, -42)">
            <path d="M 40,78 Q 14,48 22,8" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
            <use href="#laurel-leaf" transform="translate(22, 8) rotate(22)" />
            <use href="#laurel-leaf" transform="translate(18, 19) rotate(-42)" />
            <use href="#laurel-leaf" transform="translate(20, 21) rotate(48)" />
            <use href="#laurel-leaf" transform="translate(15, 33) rotate(-35)" />
            <use href="#laurel-leaf" transform="translate(17, 35) rotate(58)" />
            <use href="#laurel-leaf" transform="translate(15, 47) rotate(-22)" />
            <use href="#laurel-leaf" transform="translate(18, 49) rotate(70)" />
            <use href="#laurel-leaf" transform="translate(20, 61) rotate(-10)" />
            <use href="#laurel-leaf" transform="translate(24, 62) rotate(82)" />
            <use href="#laurel-leaf" transform="translate(29, 72) rotate(15)" />
            <use href="#laurel-leaf" transform="translate(35, 73) rotate(95)" />
            <use href="#laurel-leaf" transform="translate(40, 78) rotate(115)" />
        </g>

        <!-- Title: Result Report Card -->
        <text x="0" y="30" text-anchor="middle" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-weight="800" font-size="34" fill="#111827" letter-spacing="0.3">Result Report Card</text>

        <!-- Right Laurel Branch (Mirrored) -->
        <g transform="translate(235, -42) scale(-1, 1)">
            <path d="M 40,78 Q 14,48 22,8" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
            <use href="#laurel-leaf" transform="translate(22, 8) rotate(22)" />
            <use href="#laurel-leaf" transform="translate(18, 19) rotate(-42)" />
            <use href="#laurel-leaf" transform="translate(20, 21) rotate(48)" />
            <use href="#laurel-leaf" transform="translate(15, 33) rotate(-35)" />
            <use href="#laurel-leaf" transform="translate(17, 35) rotate(58)" />
            <use href="#laurel-leaf" transform="translate(15, 47) rotate(-22)" />
            <use href="#laurel-leaf" transform="translate(18, 49) rotate(70)" />
            <use href="#laurel-leaf" transform="translate(20, 61) rotate(-10)" />
            <use href="#laurel-leaf" transform="translate(24, 62) rotate(82)" />
            <use href="#laurel-leaf" transform="translate(29, 72) rotate(15)" />
            <use href="#laurel-leaf" transform="translate(35, 73) rotate(95)" />
            <use href="#laurel-leaf" transform="translate(40, 78) rotate(115)" />
        </g>

        <!-- SUBTITLE: Academic Session 2025 – 2026 flanked by red lines -->
        <g transform="translate(0, 56)">
            <line x1="-200" y1="0" x2="-130" y2="0" stroke="#d9232d" stroke-width="2.2" stroke-linecap="round" />
            <text x="0" y="4.5" text-anchor="middle" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-weight="600" font-size="15.5" fill="#1f2937" letter-spacing="0.3">Academic Session 2025 – 2026</text>
            <line x1="130" y1="0" x2="200" y2="0" stroke="#d9232d" stroke-width="2.2" stroke-linecap="round" />
        </g>
    </g>

    <!-- DUMMY STUDENT DETAILS BOX PREVIEW -->
    <rect x="42" y="225" width="816" height="75" rx="10" fill="#ffffff" stroke="#d1d5db" stroke-width="1" />
</svg>'''

svg_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_page_svg.svg'
png_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_page_svg.png'

with open(svg_path, 'w', encoding='utf-8') as f:
    f.write(test_page_svg)

doc = fitz.open(svg_path)
page = doc[0]
pix = page.get_pixmap(dpi=150)
pix.save(png_path)
print("Saved test_page_svg.png")
