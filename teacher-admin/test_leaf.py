import fitz

svg_test_leaf = '''<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="-50 -50 100 100">
    <path d="M 0,0 Q -8,-15 0,-30 Q 8,-15 0,0 Z" fill="#d9232d" />
</svg>'''

doc = fitz.open(stream=svg_test_leaf.encode('utf-8'), filetype='svg')
pix = doc[0].get_pixmap(dpi=150)
pix.save(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_sharp_leaf.png')
print("Rendered test_sharp_leaf.png")
