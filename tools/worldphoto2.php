<?php
// Read the world name from the URL parameters
$worldName = isset($_GET['world']) ? $_GET['world'] : 'world';

// Define the file path based on the world name
$file = "../worlds/{$worldName}.txt";

// Check if the world file exists and read its content
if (file_exists($file)) {
    $world = unserialize(file_get_contents($file));
} else {
    die("World file does not exist.");
}

// Create an image
$im = imagecreatetruecolor(420, 420);

// Define colors
$grass = imagecolorallocate($im, 0, 128, 0);
$water = imagecolorallocate($im, 0, 0, 255);
$stone = imagecolorallocate($im, 128, 128, 128);
$dirt = imagecolorallocate($im, 139, 69, 19);
$void = imagecolorallocate($im, 0, 0, 0);

// Loop through the world array to set pixels
for ($y = 0; $y < 21; $y++) {
    for ($x = 0; $x < 21; $x++) {
        $cell = $world[$y][$x];
        $color = $void; // Default to 'void' color
        if ($cell === 'grass') $color = $grass;
        if ($cell === 'water') $color = $water;
        if ($cell === 'stone') $color = $stone;
        if ($cell === 'dirt') $color = $dirt;

        // Set the pixel color on the image
        imagesetpixel($im, $x * 20, $y * 20, $color);
    }
}

// Output the image
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>
