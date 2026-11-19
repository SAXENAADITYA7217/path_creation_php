import fitz
import os
import base64

logo_path = r'C:\xampp\htdocs\path\teacher-admin\assets\images\path_creation_logo.png'
with open(logo_path, 'rb') as f:
    logo_b64 = base64.b64encode(f.read()).decode('utf-8')

svg_full = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 850 200" width="850" height="200">
    <defs>
        <!-- Laurel Leaf: pointed oval -->
        <g id="laurel-leaf">
            <path d="M 0,0 Q -4.2,-8.5 0,-17 Q 4.2,-8.5 0,0 Z" fill="#e5252a" />
        </g>
    </defs>

    <!-- White Background with Border -->
    <rect x="0" y="0" width="850" height="200" rx="16" fill="#ffffff" stroke="#d1d5db" stroke-width="1.5" />

    <!-- TOP-RIGHT DECORATIVE GEOMETRIC CORNER -->
    <g transform="translate(675, 0)">
        <!-- Black Corner Triangle -->
        <polygon points="95,0 175,0 175,68" fill="#18181b" />
        
        <!-- Bold Red Tab with rounded bottom-left -->
        <path d="M 20,0 
                 L 66,46 
                 Q 76,56 92,56 
                 L 124,56 
                 L 88,0 
                 Z" fill="#e5252a" />

        <!-- Thin Red Diagonal Accent Stripe -->
        <polygon points="128,62 136,62 175,101 175,109 167,109" fill="#e5252a" />
    </g>

    <!-- SCHOOL BRANDING: LOGO, DIVIDER, NAME, SLOGAN -->
    <g transform="translate(36, 20)">
        <!-- Logo Image -->
        <image href="data:image/png;base64,{logo_b64}" x="0" y="2" width="48" height="48" preserveAspectRatio="xMidYMid meet" />
        
        <!-- Vertical Divider -->
        <line x1="60" y1="4" x2="60" y2="48" stroke="#9ca3af" stroke-width="1.8" />
        
        <!-- School Name: Path Creation Academy -->
        <!-- 'Path Creation' bold italic black -->
        <text x="74" y="27" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="26" font-weight="800" font-style="italic" fill="#111827" letter-spacing="-0.3">Path Creation</text>
        <!-- 'Academy' bold upright red -->
        <text x="254" y="27" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="26" font-weight="800" font-style="normal" fill="#e5252a" letter-spacing="-0.3">Academy</text>
        
        <!-- Slogan: LEARN - BUILD - GROW -->
        <text x="76" y="45" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="10" font-weight="700" fill="#4b5563" letter-spacing="4.5">LEARN &bull; BUILD &bull; GROW</text>
    </g>

    <!-- EMBLEM & TITLE: GRADUATION CAP, LAUREL WREATH, RESULT REPORT CARD -->
    <g transform="translate(425, 96)">
        <!-- Center: Graduation Cap sitting above title -->
        <g transform="translate(0, -18)">
            <!-- Diamond Mortarboard -->
            <polygon points="0,-14 36,-2 0,10 -36,-2" fill="#e5252a" />
            <!-- Under rim 3D shadow -->
            <polygon points="-36,-2 0,10 0,12 -36,0" fill="#b91c1c" />
            <polygon points="36,-2 0,10 0,12 36,0" fill="#b91c1c" />
            <!-- Skull Cap base -->
            <path d="M -15,5 L -15,15 C -15,22 15,22 15,15 L 15,5 Q 0,10 -15,5 Z" fill="#e5252a" />
            <!-- Center Button -->
            <circle cx="0" cy="-2" r="2.2" fill="#e5252a" />
            <!-- Tassel hanging left -->
            <path d="M 0,-2 L -20,4 L -22,15" stroke="#e5252a" stroke-width="2" fill="none" stroke-linecap="round" />
            <polygon points="-25,15 -20,15 -20,23 -25,23" fill="#e5252a" />
        </g>

        <!-- Left Laurel Branch -->
        <g transform="translate(-230, -38)">
            <path d="M 40,78 Q 14,48 22,8" stroke="#e5252a" stroke-width="2.2" fill="none" stroke-linecap="round" />
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
        <text x="0" y="32" text-anchor="middle" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-weight="800" font-size="34" fill="#111827" letter-spacing="0.3">Result Report Card</text>

        <!-- Right Laurel Branch (Mirrored) -->
        <g transform="translate(230, -38) scale(-1, 1)">
            <path d="M 40,78 Q 14,48 22,8" stroke="#e5252a" stroke-width="2.2" fill="none" stroke-linecap="round" />
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
        <g transform="translate(0, 58)">
            <line x1="-195" y1="0" x2="-125" y2="0" stroke="#e5252a" stroke-width="2.2" stroke-linecap="round" />
            <text x="0" y="4.5" text-anchor="middle" font-family="'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-weight="600" font-size="15.5" fill="#1f2937" letter-spacing="0.3">Academic Session 2025 – 2026</text>
            <line x1="125" y1="0" x2="195" y2="0" stroke="#e5252a" stroke-width="2.2" stroke-linecap="round" />
        </g>
    </g>
</svg>'''

svg_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_complete_header.svg'
png_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_complete_header.png'

with open(svg_path, 'w', encoding='utf-8') as f:
    f.write(svg_full)

doc = fitz.open(svg_path)
page = doc[0]
pix = page.get_pixmap(dpi=150)
pix.save(png_path)
print("Saved updated test_complete_header.png")
