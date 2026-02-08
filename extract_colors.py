from PIL import Image
from collections import Counter

def get_dominant_colors(image_path, num_colors=5):
    try:
        img = Image.open(image_path)
        img = img.convert("RGB")
        img = img.resize((150, 150))  # Resize for faster processing
        pixels = list(img.getdata())
        
        # Simple frequency count
        counts = Counter(pixels)
        most_common = counts.most_common(num_colors)
        
        print(f"Dominant colors in {image_path}:")
        for color, count in most_common:
            hex_color = '#{:02x}{:02x}{:02x}'.format(*color)
            print(f"Color: {color}, Hex: {hex_color}, Count: {count}")
            
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    get_dominant_colors(r"d:\Wratl\wrtl\public\images\mainlogo.png")
