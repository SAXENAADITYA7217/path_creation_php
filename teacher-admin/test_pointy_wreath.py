import fitz

# High-fidelity SVG of laurel leaf branch
# Leaf is pointed ellipse: M 0,0 C -3,-6 0,-14 6,-18 C 8,-10 6,-3 0,0 Z
svg_wreath = '''<svg xmlns="http://www.w3.org/2000/svg" width="56" height="82" viewBox="0 0 56 82">
    <!-- Central curved stem -->
    <path d="M 44,78 Q 14,46 22,8" stroke="#d9232d" stroke-width="2" fill="none" stroke-linecap="round" />
    
    <!-- Top tip leaf -->
    <path d="M 22,8 C 19,1 24,0 27,4 C 29,8 26,10 22,8 Z" fill="#d9232d" />
    
    <!-- Pair 1 -->
    <path d="M 21,15 C 13,9 11,2 17,2 C 22,3 23,10 21,15 Z" fill="#d9232d" />
    <path d="M 23,17 C 29,11 38,13 36,19 C 33,23 27,20 23,17 Z" fill="#d9232d" />
    
    <!-- Pair 2 -->
    <path d="M 17,27 C 9,22 6,15 13,14 C 19,14 21,22 17,27 Z" fill="#d9232d" />
    <path d="M 20,29 C 27,23 36,27 34,33 C 31,37 25,33 20,29 Z" fill="#d9232d" />
    
    <!-- Pair 3 -->
    <path d="M 16,40 C 7,37 4,30 11,28 C 17,27 20,35 16,40 Z" fill="#d9232d" />
    <path d="M 20,43 C 28,38 37,43 33,49 C 29,53 24,48 20,43 Z" fill="#d9232d" />
    
    <!-- Pair 4 -->
    <path d="M 18,54 C 9,54 7,46 14,43 C 20,41 23,49 18,54 Z" fill="#d9232d" />
    <path d="M 23,57 C 31,53 40,60 35,66 C 30,69 26,63 23,57 Z" fill="#d9232d" />
    
    <!-- Pair 5 / Bottom Leaves -->
    <path d="M 25,67 C 17,70 16,62 22,58 C 28,55 30,63 25,67 Z" fill="#d9232d" />
    <path d="M 31,69 C 39,69 44,78 38,82 C 33,84 31,76 31,69 Z" fill="#d9232d" />
    
    <!-- Inward bottom leaf -->
    <path d="M 39,76 C 35,82 43,84 48,80 C 51,76 45,73 39,76 Z" fill="#d9232d" />
</svg>'''

doc = fitz.open(stream=svg_wreath.encode('utf-8'), filetype='svg')
pix = doc[0].get_pixmap(dpi=150)
pix.save(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_wreath_pointy.png')
print("Rendered test_wreath_pointy.png")
