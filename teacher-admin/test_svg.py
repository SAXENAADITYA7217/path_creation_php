import fitz

# In viewBox 0 0 240 140:
# Full image width is 350, height is 111 in reference.
# Corner has width 123, height 111 (scale ~2:1)
# Let's use viewBox="0 0 200 130"
svg_code = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 130" width="200" height="130">
    <!-- Dark/Black Corner Triangle -->
    <polygon points="120,0 200,0 200,68" fill="#18181b" />
    
    <!-- Red Tab with smooth rounded bottom-left -->
    <path d="M 40,0 
             L 86,46 
             Q 96,56 112,56 
             L 148,56 
             L 112,0 
             Z" fill="#e5252a" />

    <!-- Thin Red Diagonal Accent Stripe -->
    <polygon points="152,62 160,62 200,102 200,110 192,110" fill="#e5252a" />
</svg>'''

svg_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_corner.svg'
png_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_corner.png'

with open(svg_path, 'w', encoding='utf-8') as f:
    f.write(svg_code)

doc = fitz.open(svg_path)
page = doc[0]
pix = page.get_pixmap(dpi=150)
pix.save(png_path)
print("Updated test_corner.png")
