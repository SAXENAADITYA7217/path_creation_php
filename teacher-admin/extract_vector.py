import cv2
import numpy as np

# Load the full user image
img = cv2.imread(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\.user_uploaded\media_1793601061776_3903e675.png')
h, w, _ = img.shape
print(f'Loaded image: {w}x{h}')

# Convert BGR to RGB
rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

# Mask red color
# Red: R is high, G and B are low
r = rgb[:, :, 0].astype(int)
g = rgb[:, :, 1].astype(int)
b = rgb[:, :, 2].astype(int)

red_mask = (r > 150) & (r - g > 50) & (r - b > 50)
red_mask_uint8 = (red_mask * 255).astype(np.uint8)

# 1. Extract Left Wreath (approx x in 70..130, y in 45..105)
# Let's crop region of left wreath
wreath_roi = red_mask_uint8.copy()
# Zero out everything outside left wreath
wreath_roi[:, :60] = 0
wreath_roi[:, 125:] = 0
wreath_roi[:45, :] = 0
wreath_roi[100:, :] = 0

contours, _ = cv2.findContours(wreath_roi, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_TC89_KCOS)

print(f'Left wreath contours found: {len(contours)}')
svg_paths = []
for c in contours:
    if cv2.contourArea(c) > 4: # filter noise
        # approximate contour slightly to make smooth SVG
        epsilon = 0.5
        approx = cv2.approxPolyDP(c, epsilon, True)
        pts = approx.reshape(-1, 2)
        d = f'M {pts[0][0]},{pts[0][1]} ' + ' '.join([f'L {p[0]},{p[1]}' for p in pts[1:]]) + ' Z'
        svg_paths.append(d)

print('SVG paths for left wreath count:', len(svg_paths))
with open(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\wreath_paths.txt', 'w') as f:
    f.write('\n'.join(svg_paths))

# 2. Extract Graduation Cap (approx x in 150..205, y in 40..80)
cap_roi = red_mask_uint8.copy()
cap_roi[:, :150] = 0
cap_roi[:, 205:] = 0
cap_roi[:40, :] = 0
cap_roi[80:, :] = 0

contours_cap, _ = cv2.findContours(cap_roi, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_TC89_KCOS)
print(f'Cap contours found: {len(contours_cap)}')
cap_svg_paths = []
for c in contours_cap:
    if cv2.contourArea(c) > 3:
        epsilon = 0.5
        approx = cv2.approxPolyDP(c, epsilon, True)
        pts = approx.reshape(-1, 2)
        d = f'M {pts[0][0]},{pts[0][1]} ' + ' '.join([f'L {p[0]},{p[1]}' for p in pts[1:]]) + ' Z'
        cap_svg_paths.append(d)

with open(r'C:\Users\Aditya Saxena\.gemini\antigravity\brain\34342fb8-370b-49f9-9099-729287dd2cef\scratch\cap_paths.txt', 'w') as f:
    f.write('\n'.join(cap_svg_paths))

print('Done extracting exact paths!')
