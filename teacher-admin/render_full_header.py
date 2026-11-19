import subprocess
import os
import base64

logo_path = r'C:\xampp\htdocs\path\teacher-admin\assets\images\path_creation_logo.png'
with open(logo_path, 'rb') as f:
    logo_b64 = base64.b64encode(f.read()).decode('utf-8')
logo_uri = f'data:image/png;base64,{logo_b64}'

html_content = f'''<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Card Header</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,700;0,800;1,700;1,800&family=Poppins:wght@600;700;800&family=Inter:wght@600;700;800&display=swap" rel="stylesheet">
<style>
    * {{
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }}
    body {{
        background: #f1f5f9;
        padding: 20px;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    }}
    .report-card-page {{
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 18px;
        padding: 30px 42px 25px 42px;
        max-width: 900px;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }}
    
    /* Top Right Corner Geometry */
    .top-right-decor {{
        position: absolute;
        top: 0;
        right: 0;
        width: 175px;
        height: 120px;
        pointer-events: none;
        z-index: 1;
    }}

    /* School Brand Header */
    .school-brand-row {{
        display: flex;
        align-items: center;
        position: relative;
        z-index: 2;
        margin-bottom: 8px;
    }}
    .brand-logo {{
        height: 52px;
        width: auto;
        display: block;
        flex-shrink: 0;
    }}
    .brand-divider {{
        width: 1.5px;
        height: 44px;
        background-color: #cbd5e1;
        margin: 0 16px;
        flex-shrink: 0;
    }}
    .brand-titles {{
        display: flex;
        flex-direction: column;
        justify-content: center;
    }}
    .brand-name {{
        font-family: 'Montserrat', 'Segoe UI', sans-serif;
        font-size: 1.65rem;
        line-height: 1.1;
        letter-spacing: -0.3px;
    }}
    .brand-name .name-path {{
        font-weight: 800;
        font-style: italic;
        color: #111827;
    }}
    .brand-name .name-academy {{
        font-weight: 800;
        font-style: normal;
        color: #e5252a;
        margin-left: 5px;
    }}
    .brand-tagline {{
        font-family: 'Montserrat', 'Segoe UI', sans-serif;
        font-size: 0.72rem;
        font-weight: 700;
        color: #4b5563;
        letter-spacing: 4px;
        text-transform: uppercase;
        margin-top: 4px;
    }}

    /* Center Emblem and Title */
    .emblem-section {{
        text-align: center;
        position: relative;
        z-index: 2;
        margin-top: 4px;
        margin-bottom: 6px;
    }}
    .title-wrapper {{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }}
    .center-title-box {{
        display: flex;
        flex-direction: column;
        align-items: center;
    }}
    .report-card-heading {{
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 2.15rem;
        font-weight: 800;
        color: #111827;
        margin: 0;
        letter-spacing: 0.3px;
        line-height: 1.15;
    }}

    .session-row {{
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 6px;
    }}
    .session-line {{
        height: 2px;
        width: 80px;
        background-color: #e5252a;
        border-radius: 1px;
    }}
    .session-text {{
        font-family: 'Segoe UI', Roboto, sans-serif;
        font-weight: 600;
        font-size: 1.05rem;
        color: #1f2937;
        margin: 0 16px;
        letter-spacing: 0.3px;
    }}
</style>
</head>
<body>

<div class="report-card-page">
    <!-- TOP-RIGHT DECORATIVE GEOMETRIC CORNER -->
    <svg class="top-right-decor" viewBox="0 0 200 130" xmlns="http://www.w3.org/2000/svg">
        <!-- Dark charcoal / black corner triangle -->
        <polygon points="120,0 200,0 200,68" fill="#18181b" />
        
        <!-- Bold Red Tab with rounded bottom-left -->
        <path d="M 40,0 
                 L 86,46 
                 Q 96,56 112,56 
                 L 148,56 
                 L 112,0 
                 Z" fill="#e5252a" />

        <!-- Thin Red Diagonal Accent Stripe -->
        <polygon points="152,62 160,62 200,102 200,110 192,110" fill="#e5252a" />
    </svg>

    <!-- HEADER: LOGO & SCHOOL BRANDING -->
    <div class="school-brand-row">
        <img src="{logo_uri}" alt="Path Creation Academy" class="brand-logo">
        <div class="brand-divider"></div>
        <div class="brand-titles">
            <div class="brand-name">
                <span class="name-path">Path Creation</span><span class="name-academy">Academy</span>
            </div>
            <div class="brand-tagline">LEARN &bull; BUILD &bull; GROW</div>
        </div>
    </div>

    <!-- EMBLEM & TITLE: LAUREL WREATH, CAP, REPORT CARD -->
    <div class="emblem-section">
        <div class="title-wrapper">
            <!-- Left Laurel Branch -->
            <svg width="46" height="74" viewBox="0 0 54 86" style="flex-shrink: 0;">
                <defs>
                    <g id="leaf-shape">
                        <path d="M 0,0 Q -4.8,-8.5 0,-17 Q 4.8,-8.5 0,0 Z" fill="#e5252a" />
                    </g>
                </defs>
                <!-- Curved stem -->
                <path d="M 42,82 Q 13,48 22,8" stroke="#e5252a" stroke-width="2.2" fill="none" stroke-linecap="round" />
                <!-- Leaves -->
                <use href="#leaf-shape" transform="translate(22, 8) rotate(22)" />
                <use href="#leaf-shape" transform="translate(18, 19) rotate(-42)" />
                <use href="#leaf-shape" transform="translate(20, 21) rotate(48)" />
                <use href="#leaf-shape" transform="translate(15, 33) rotate(-35)" />
                <use href="#leaf-shape" transform="translate(17, 35) rotate(58)" />
                <use href="#leaf-shape" transform="translate(15, 47) rotate(-22)" />
                <use href="#leaf-shape" transform="translate(18, 49) rotate(70)" />
                <use href="#leaf-shape" transform="translate(20, 61) rotate(-10)" />
                <use href="#leaf-shape" transform="translate(24, 62) rotate(82)" />
                <use href="#leaf-shape" transform="translate(29, 72) rotate(15)" />
                <use href="#leaf-shape" transform="translate(35, 73) rotate(95)" />
                <use href="#leaf-shape" transform="translate(41, 81) rotate(115)" />
            </svg>

            <!-- Center Cap & Title -->
            <div class="center-title-box">
                <!-- Red Graduation Cap -->
                <svg width="46" height="32" viewBox="0 0 90 64" style="margin-bottom: 2px;">
                    <!-- Mortarboard Diamond -->
                    <polygon points="45,4 85,19 45,34 5,19" fill="#e5252a" />
                    <!-- 3D rim -->
                    <polygon points="5,19 45,34 45,36 5,21" fill="#b91c1c" />
                    <polygon points="85,19 45,34 45,36 85,21" fill="#b91c1c" />
                    <!-- Base Skull Cap -->
                    <path d="M 27,27 L 27,42 C 27,51 63,51 63,42 L 63,27 Q 45,34 27,27 Z" fill="#e5252a" />
                    <!-- Button -->
                    <circle cx="45" cy="19" r="2.8" fill="#e5252a" />
                    <!-- Tassel (to left) -->
                    <path d="M 45,19 L 20,26 L 17,42" stroke="#e5252a" stroke-width="2.2" fill="none" stroke-linecap="round" />
                    <polygon points="14,42 20,42 19,53 13,53" fill="#e5252a" />
                </svg>
                
                <h2 class="report-card-heading">Result Report Card</h2>
            </div>

            <!-- Right Laurel Branch (Mirrored) -->
            <svg width="46" height="74" viewBox="0 0 54 86" style="flex-shrink: 0; transform: scaleX(-1);">
                <defs>
                    <g id="leaf-shape-r">
                        <path d="M 0,0 Q -4.8,-8.5 0,-17 Q 4.8,-8.5 0,0 Z" fill="#e5252a" />
                    </g>
                </defs>
                <path d="M 42,82 Q 13,48 22,8" stroke="#e5252a" stroke-width="2.2" fill="none" stroke-linecap="round" />
                <use href="#leaf-shape-r" transform="translate(22, 8) rotate(22)" />
                <use href="#leaf-shape-r" transform="translate(18, 19) rotate(-42)" />
                <use href="#leaf-shape-r" transform="translate(20, 21) rotate(48)" />
                <use href="#leaf-shape-r" transform="translate(15, 33) rotate(-35)" />
                <use href="#leaf-shape-r" transform="translate(17, 35) rotate(58)" />
                <use href="#leaf-shape-r" transform="translate(15, 47) rotate(-22)" />
                <use href="#leaf-shape-r" transform="translate(18, 49) rotate(70)" />
                <use href="#leaf-shape-r" transform="translate(20, 61) rotate(-10)" />
                <use href="#leaf-shape-r" transform="translate(24, 62) rotate(82)" />
                <use href="#leaf-shape-r" transform="translate(29, 72) rotate(15)" />
                <use href="#leaf-shape-r" transform="translate(35, 73) rotate(95)" />
                <use href="#leaf-shape-r" transform="translate(41, 81) rotate(115)" />
            </svg>
        </div>

        <!-- Session flanked by red lines -->
        <div class="session-row">
            <div class="session-line"></div>
            <span class="session-text">Academic Session 2025 &ndash; 2026</span>
            <div class="session-line"></div>
        </div>
    </div>
</div>

</body>
</html>
'''

html_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\test_header.html'
with open(html_path, 'w', encoding='utf-8') as f:
    f.write(html_content)

print("Saved test_header.html")

# Take screenshot with msedge
shot_path = r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\header_screenshot.png'
edge_bin = r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'

cmd = [
    edge_bin,
    '--headless',
    '--disable-gpu',
    f'--screenshot={shot_path}',
    '--window-size=960,350',
    f'file:///{html_path.replace(os.sep, "/")}'
]

res = subprocess.run(cmd, capture_output=True, text=True)
print("Screenshot return code:", res.returncode)
print("Saved screenshot to", shot_path)
