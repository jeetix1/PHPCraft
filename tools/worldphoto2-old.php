<?php
// Read the world from the world.txt file
$file = 'world.txt';
if (!file_exists($file)) {
    die("world.txt file does not exist");
}
$world = unserialize(file_get_contents($file));

// Preset center coordinates and dimensions
$centerX = 10;  // Center X-coordinate
$centerY = 10;  // Center Y-coordinate
$dimension = 300; // Dimension of the square area

// Initialize image based on preset coordinates
$image = imagecreatetruecolor($dimension, $dimension);

// Define colors
$colors = [
    'grass' => imagecolorallocate($image, 0, 128, 0),
    'water' => imagecolorallocate($image, 0, 0, 255),
    'stone' => imagecolorallocate($image, 128, 128, 128),
    'dirt'  => imagecolorallocate($image, 139, 69, 19),
    'void'  => imagecolorallocate($image, 0, 0, 0),
];

// Calculate start and end coordinates based on center and dimension
$startX = $centerX - floor($dimension / 2);
$endX = $startX + $dimension - 1;
$startY = $centerY - floor($dimension / 2);
$endY = $startY + $dimension - 1;

// Loop through each cell in the world within the preset coordinates
for ($y = $startY; $y <= $endY; $y++) {
    for ($x = $startX; $x <= $endX; $x++) {
        $cell = $world[$y][$x] ?? 'void';
        $color = $colors[$cell] ?? $colors['void'];
        imagesetpixel($image, $x - $startX, $y - $startY, $color);
    }
}

// Output image
header("Content-Type: image/png");
imagepng($image);

// Free memory
imagedestroy($image);
?>
