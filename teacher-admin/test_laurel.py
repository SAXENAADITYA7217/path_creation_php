import fitz

svg_laurel = '''<svg xmlns="http://www.w3.org/2000/svg" width="60" height="90" viewBox="0 0 60 90">
    <defs>
        <!-- Laurel Leaf: 16px long, 7px wide, pointed ends -->
        <g id="leaf">
            <path d="M 0,0 Q -3.8,-8 0,-16 Q 3.8,-8 0,0 Z" fill="#d9232d" />
        </g>
    </defs>

    <!-- Curved branch stem -->
    <path d="M 44,84 Q 14,50 24,10" stroke="#d9232d" stroke-width="2" fill="none" stroke-linecap="round" />

    <!-- Top tip leaf -->
    <use href="#leaf" transform="translate(24, 10) rotate(22)" />

    <!-- Pair 1 -->
    <use href="#leaf" transform="translate(20, 20) rotate(-42)" />
    <use href="#leaf" transform="translate(22, 22) rotate(48)" />

    <!-- Pair 2 -->
    <use href="#leaf" transform="translate(17, 34) rotate(-35)" />
    <use href="#leaf" transform="translate(19, 36) rotate(58)" />

    <!-- Pair 3 -->
    <use href="#leaf" transform="translate(17, 48) rotate(-22)" />
    <use href="#leaf" transform="translate(20, 50) rotate(70)" />

    <!-- Pair 4 -->
    <use href="#leaf" transform="translate(22, 63) rotate(-10)" />
    <use href="#leaf" transform="translate(26, 64) rotate(82)" />

    <!-- Pair 5 / Bottom -->
    <use href="#leaf" transform="translate(31, 74) rotate(15)" />
    <use href="#leaf" transform="translate(37, 75) rotate(95)" />

    <!-- Inward bottom leaf -->
    <use href="#leaf" transform="translate(43, 83) rotate(115)" />
</svg>'''

doc = fitz.open(stream=svg_laurel.encode('utf-8'), filetype='svg')
pix = doc[0].get_pixmap(dpi=150)
pix.save(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_laurel_perfect.png')
print("Rendered test_laurel_perfect.png")
