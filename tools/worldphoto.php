<?php
// Read the world from the world.txt file

// Read username and world name from URL parameters
$username = isset($_GET['username']) ? $_GET['username'] : 'defaultUser';
$world = isset($_GET['world']) ? $_GET['world'] : 'defaultWorld';

// Update file path to read from the new directory
$file = "../worlds/{$world}.txt";

if (!file_exists($file)) {
    die("world.txt file does not exist");
}
$world = unserialize(file_get_contents($file));

// Preset start and end coordinates
$startX = -300;  // Starting X-coordinate
$endX = 300;   // Ending X-coordinate
$startY = -300;  // Starting Y-coordinate
$endY = 300;   // Ending Y-coordinate

// Initialize image based on preset coordinates
$width = $endX - $startX + 1;
$height = $endY - $startY + 1;
$image = imagecreatetruecolor($width, $height);

// Define colors
$colors = [
    'grass' => imagecolorallocate($image, 0, 128, 0),
    'water' => imagecolorallocate($image, 0, 0, 255),
    'stone' => imagecolorallocate($image, 128, 128, 128),
    'dirt'  => imagecolorallocate($image, 139, 69, 19),
    'void'  => imagecolorallocate($image, 0, 0, 0),
];

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
