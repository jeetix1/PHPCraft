<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get or set the username and world
$username = isset($_GET['username']) ? $_GET['username'] : 'defaultUser';
$playerWorld = isset($_GET['world']) ? $_GET['world'] : null;
$playerFile = "players/{$username}.txt";

// Initialize or read player data
if (!file_exists($playerFile)) {
    $playerData = ['world' => 'world', 'x' => 0, 'y' => 0];
    file_put_contents($playerFile, json_encode($playerData));
} else {
    $playerData = json_decode(file_get_contents($playerFile), true);
}

// Extract player info
if ($playerWorld === null) {
    $playerWorld = $playerData['world'];
}

$_SESSION['x'] = $playerData['x'];
$_SESSION['y'] = $playerData['y'];

if (isset($_GET['getCoords']) && $_GET['getCoords'] === 'true') {
    header('Content-Type: application/json');
    echo json_encode(['x' => $_SESSION['x'], 'y' => $_SESSION['y']]);
    exit;
}

if (!isset($_SESSION['selected'])) {
    $_SESSION['selected'] = 'none';
}

$file = "worlds/{$playerWorld}.txt";

if (!file_exists($file)) {
    $world = array_fill(0, 21, array_fill(0, 21, 'grass'));
    file_put_contents($file, serialize($world));
} else {
    $world = unserialize(file_get_contents($file));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'left')
        $_SESSION['x']--;
    if ($action === 'right')
        $_SESSION['x']++;
    if ($action === 'up')
        $_SESSION['y']--;
    if ($action === 'down')
        $_SESSION['y']++;

    if (in_array($action, ['grass', 'water', 'stone', 'dirt', 'none'])) {
        $_SESSION['selected'] = $action;
    }

    if ($_SESSION['selected'] !== 'none') {
        $world[$_SESSION['y'] + 10][$_SESSION['x'] + 10] = $_SESSION['selected'];
        file_put_contents($file, serialize($world));
    }

    // Update player data
    $playerData['world'] = $playerWorld;
    $playerData['x'] = $_SESSION['x'];
    $playerData['y'] = $_SESSION['y'];
    file_put_contents($playerFile, json_encode($playerData));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>PHPCraft 2D!</title>
    <link rel="stylesheet" href="style.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('h2').textContent = 'Selected Block: <?php echo $_SESSION['selected']; ?>';

        });
    </script>
</head>

<body>
    <h1>Welcome to PHPCraft 2D!</h1>
    <pre>Move around with arrow keys. No fancy controllers required. Place blocks like grass, water, stone, and dirt. You're basically a god.</pre>
    <h2>World Around You:</h2>
    <form method="get">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $username; ?>" autocomplete="off">
        <label for="world">World:</label>
        <input type="text" id="world" name="world" value="<?php echo $playerWorld; ?>" autocomplete="off">
        <button type="submit">Go to World</button>
    </form>

    <form id="block-form" method="post">
        <button type="button" name="action" value="none">None</button>
        <button type="button" name="action" value="grass">Grass</button>
        <button type="button" name="action" value="water">Water</button>
        <button type="button" name="action" value="stone">Stone</button>
        <button type="button" name="action" value="dirt">Dirt</button>
    </form>
    <table>
        <?php
        $colors = ['grass' => 'green', 'water' => 'blue', 'stone' => 'gray', 'dirt' => 'brown', 'void' => 'black'];
        for ($i = $_SESSION['y'] - 10; $i <= $_SESSION['y'] + 10; $i++) {
            echo "<tr>";
            for ($j = $_SESSION['x'] - 10; $j <= $_SESSION['x'] + 10; $j++) {
                $cell = $world[$i + 10][$j + 10] ?? 'void';
                $color = $colors[$cell];

                if ($i === $_SESSION['y'] && $j === $_SESSION['x']) {
                    $additionalClass = ' pulsing';
                    echo "<td class='$additionalClass' style='width:20px;height:20px;background-color:$color;'>😼</td>";
                } else {
                    echo "<td style='width:20px;height:20px;background-color:$color;'></td>";
                }
            }
            echo "</tr>";
        }
        ?>
    </table>
    <pre>Coordinates: <?php echo $_SESSION['x'] . ', ' . $_SESSION['y']; ?> Selected Block: <?php echo $_SESSION['selected']; ?></pre>
    <h3>Legend:</h3>
    <ul>
        <li>Grass: <span style="color:green;">🌱</span></li>
        <li>Water: <span style="color:blue;">🌊</span></li>
        <li>Stone: <span style="color:gray;">⛰️</span></li>
        <li>Dirt: <span style="color:brown;">🌄</span></li>
        <li>Player: <span style="color:yellowgreen;">😼</span></li>
    </ul>
    <h3>Instructions:</h3>
    <ul>
        <li>Use the arrow keys to move around the world.</li>
        <li>Click on a block type to select it.</li>
        <li>Click on a block in the world to place the selected block. You will keep placing selected block as you move until you cnage it.</li>
    </ul>
    <h3>Tools</h3>
    <ul>
        <li><a href="tools/worldphoto.php?world=<?php echo $playerWorld; ?>">World Photo</a></li>
        <li><a href="tools/worldphoto.php?username=<?php echo $username; ?>&world=<?php echo $playerWorld; ?>">Your World Photo</a></li>
        <li><a href="tools/worldphoto2.php?world=<?php echo $playerWorld; ?>">World Photo</a></li>
        <li><a href="tools/worldphoto2.php?username=<?php echo $username; ?>&world=<?php echo $playerWorld; ?>">Your World Photo</a></li>
    </ul>
    <script>
        document.addEventListener('keydown', function (event) {
            let action;
            if (event.keyCode === 37) action = 'left';
            if (event.keyCode === 38) action = 'up';
            if (event.keyCode === 39) action = 'right';
            if (event.keyCode === 40) action = 'down';

            if (action) {
                handleAction(action);
            }
        });

        // Button click event listeners
        document.querySelectorAll('#block-form button').forEach(button => {
            button.addEventListener('click', function (event) {
                const action = event.target.value;
                console.log('Button clicked: ', action);
                handleAction(action, true);
                document.querySelector('h2').textContent = 'Selected Block: ' + action;
            });
        });

        function handleAction(action) {
            console.log("Handling action: ", action);
            const form = new FormData();
            form.append('action', action);
            fetch('', {
                method: 'POST',
                body: form
            }).then(() => {
                console.log("Fetch complete for action: ", action);
                // Always fetch updated coordinates
                fetch('?getCoords=true')
                    .then(response => response.json())
                    .then(data => {
                        console.log("Updated coordinates received: ", data);
                        const urlParams = new URLSearchParams(window.location.search);
                        const username = urlParams.get('username') || 'defaultUser';
                        const world = urlParams.get('world') || 'defaultWorld';
                        const newURL = `?username=${username}&world=${world}&x=${data.x}&y=${data.y}`;
                        console.log("Updating URL to: ", newURL);
                        window.location.href = newURL;
                    });
            });
        }
    </script>
</body>

</html>