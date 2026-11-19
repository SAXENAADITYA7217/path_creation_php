import fitz

# High-fidelity SVG of the entire emblem
svg_emblem = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 540 130" width="540" height="130">
    <defs>
        <!-- Leaf template -->
        <path id="w-leaf" d="M 0,0 C -3,-5 0,-14 9,-18 C 11,-10 7,-2 0,0 Z" fill="#d9232d" />
    </defs>

    <!-- LEFT LAUREL WREATH -->
    <g transform="translate(68, 6)">
        <!-- Stem -->
        <path d="M 38,76 Q 16,48 24,12" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
        <!-- Leaves: 1 tip + 4 pairs + 1 bottom -->
        <!-- Tip -->
        <path d="M 24,12 C 22,4 27,2 30,7 C 32,13 28,15 24,12 Z" fill="#d9232d" />
        <!-- Pair 1 -->
        <path d="M 23,19 C 14,14 13,6 19,7 C 26,8 26,17 23,19 Z" fill="#d9232d" />
        <path d="M 26,20 C 33,14 41,19 37,25 C 33,30 27,24 26,20 Z" fill="#d9232d" />
        <!-- Pair 2 -->
        <path d="M 20,32 C 10,29 8,20 15,20 C 22,21 23,30 20,32 Z" fill="#d9232d" />
        <path d="M 24,33 C 33,29 39,36 34,42 C 29,46 25,39 24,33 Z" fill="#d9232d" />
        <!-- Pair 3 -->
        <path d="M 19,46 C 9,46 6,36 13,35 C 20,34 23,43 19,46 Z" fill="#d9232d" />
        <path d="M 25,48 C 34,46 39,54 34,60 C 28,64 24,56 25,48 Z" fill="#d9232d" />
        <!-- Pair 4 -->
        <path d="M 23,61 C 13,64 11,54 18,51 C 25,48 27,58 23,61 Z" fill="#d9232d" />
        <path d="M 29,62 C 38,62 42,71 36,77 C 29,80 27,70 29,62 Z" fill="#d9232d" />
        <!-- Bottom Pair/Leaf -->
        <path d="M 33,74 C 25,79 23,71 28,68 C 33,65 37,70 33,74 Z" fill="#d9232d" />
        <path d="M 38,76 C 44,83 39,89 33,85 C 30,81 35,76 38,76 Z" fill="#d9232d" />
    </g>

    <!-- CENTER GRADUATION CAP -->
    <g transform="translate(270, 20)">
        <!-- Mortarboard Diamond -->
        <polygon points="0,-14 36,-2 0,10 -36,-2" fill="#d9232d" />
        <!-- 3D under rim -->
        <polygon points="-36,-2 0,10 0,12 -36,0" fill="#b91c1c" />
        <polygon points="36,-2 0,10 0,12 36,0" fill="#b91c1c" />
        <!-- Base skull cap -->
        <path d="M -15,6 L -15,16 C -15,23 15,23 15,16 L 15,6 Q 0,11 -15,6 Z" fill="#d9232d" />
        <!-- Button -->
        <circle cx="0" cy="-2" r="2.2" fill="#d9232d" />
        <!-- Tassel -->
        <path d="M 0,-2 L -20,4 L -22,16" stroke="#d9232d" stroke-width="1.8" fill="none" stroke-linecap="round" />
        <polygon points="-24,16 -20,16 -20,24 -25,24" fill="#d9232d" />
    </g>

    <!-- TITLE: Result Report Card -->
    <text x="270" y="78" text-anchor="middle" font-family="'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif" font-weight="800" font-size="34" fill="#111827" letter-spacing="0.3">Result Report Card</text>

    <!-- RIGHT LAUREL WREATH (mirrored) -->
    <g transform="translate(472, 6) scale(-1, 1)">
        <path d="M 38,76 Q 16,48 24,12" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
        <path d="M 24,12 C 22,4 27,2 30,7 C 32,13 28,15 24,12 Z" fill="#d9232d" />
        <path d="M 23,19 C 14,14 13,6 19,7 C 26,8 26,17 23,19 Z" fill="#d9232d" />
        <path d="M 26,20 C 33,14 41,19 37,25 C 33,30 27,24 26,20 Z" fill="#d9232d" />
        <path d="M 20,32 C 10,29 8,20 15,20 C 22,21 23,30 20,32 Z" fill="#d9232d" />
        <path d="M 24,33 C 33,29 39,36 34,42 C 29,46 25,39 24,33 Z" fill="#d9232d" />
        <path d="M 19,46 C 9,46 6,36 13,35 C 20,34 23,43 19,46 Z" fill="#d9232d" />
        <path d="M 25,48 C 34,46 39,54 34,60 C 28,64 24,56 25,48 Z" fill="#d9232d" />
        <path d="M 23,61 C 13,64 11,54 18,51 C 25,48 27,58 23,61 Z" fill="#d9232d" />
        <path d="M 29,62 C 38,62 42,71 36,77 C 29,80 27,70 29,62 Z" fill="#d9232d" />
        <path d="M 33,74 C 25,79 23,71 28,68 C 33,65 37,70 33,74 Z" fill="#d9232d" />
        <path d="M 38,76 C 44,83 39,89 33,85 C 30,81 35,76 38,76 Z" fill="#d9232d" />
    </g>

    <!-- SUBTITLE: Academic Session 2025 – 2026 flanked by red lines -->
    <line x1="105" y1="108" x2="168" y2="108" stroke="#d9232d" stroke-width="2.2" stroke-linecap="round" />
    <text x="270" y="113" text-anchor="middle" font-family="'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif" font-weight="600" font-size="16.5" fill="#1f2937" letter-spacing="0.4">Academic Session 2025 – 2026</text>
    <line x1="372" y1="108" x2="435" y2="108" stroke="#d9232d" stroke-width="2.2" stroke-linecap="round" />
</svg>'''

svg_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_emblem3.svg'
png_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_emblem3.png'

with open(svg_path, 'w', encoding='utf-8') as f:
    f.write(svg_emblem)

doc = fitz.open(svg_path)
page = doc[0]
pix = page.get_pixmap(dpi=150)
pix.save(png_path)
print("Rendered test_emblem3.png")
