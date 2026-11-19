import fitz

svg_existing = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
    <svg width="48" height="68" viewBox="0 0 50 75">
        <path d="M 42,72 Q 18,48 24,6" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
        <path d="M 24,6 C 20,2 14,7 18,12 C 22,14 26,10 24,6 Z" fill="#d9232d" />
        <path d="M 28,14 C 36,9 41,17 35,22 C 30,24 27,19 28,14 Z" fill="#d9232d" />
        <path d="M 21,19 C 12,16 9,25 15,29 C 20,30 23,25 21,19 Z" fill="#d9232d" />
        <path d="M 26,30 C 35,26 39,35 33,40 C 28,41 24,35 26,30 Z" fill="#d9232d" />
        <path d="M 18,35 C 8,34 7,43 14,47 C 19,47 22,41 18,35 Z" fill="#d9232d" />
        <path d="M 28,47 C 37,44 40,53 34,57 C 29,58 25,53 28,47 Z" fill="#d9232d" />
        <path d="M 19,52 C 10,53 10,61 18,64 C 22,63 24,57 19,52 Z" fill="#d9232d" />
        <path d="M 33,62 C 42,61 43,70 37,74 C 32,74 30,68 33,62 Z" fill="#d9232d" />
        <path d="M 25,67 C 18,69 20,76 28,77 C 32,75 32,69 25,67 Z" fill="#d9232d" />
    </svg>
</svg>'''

doc = fitz.open(stream=svg_existing.encode('utf-8'), filetype='svg')
pix = doc[0].get_pixmap(dpi=150)
pix.save(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_existing_wreath.png')
print("Rendered test_existing_wreath.png")
