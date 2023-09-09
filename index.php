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
    $playerData = ['world' => 'world', 'x' => 0, 'y' => 0, 'emoji' => '😼'];
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
    if (isset($_POST['emoji'])) {
        $newEmoji = $_POST['emoji'];
        $playerData['emoji'] = $newEmoji;
        file_put_contents($playerFile, json_encode($playerData));
    }

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
// Initialize an empty array to hold other players' data
    $otherPlayers = [];

    // Scan the players directory
    foreach (glob("players/*.txt") as $file) {
        $otherPlayerData = json_decode(file_get_contents($file), true);

        // Check if the other player is in the same world
        if ($otherPlayerData['world'] === $playerWorld && $file !== $playerFile) {
            $otherPlayers[] = $otherPlayerData;
        }
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
    <div class="grid-container">
        <div class="grid-item1">
            <?php include 'left.php'; ?>
        </div>
        <div class="grid-item2">
            <?php include 'controls.php'; ?>
            <div class="grid-item2-table">
                <table>
                    <?php
                    $emoji = $playerData['emoji'];
                    $colors = ['grass' => 'green', 'water' => 'blue', 'stone' => 'gray', 'dirt' => 'brown', 'void' => 'black'];
                    for ($i = $_SESSION['y'] - 10; $i <= $_SESSION['y'] + 10; $i++) {
                        echo "<tr>";
                        for ($j = $_SESSION['x'] - 10; $j <= $_SESSION['x'] + 10; $j++) {
                            $cell = $world[$i + 10][$j + 10] ?? 'void';
                            $color = $colors[$cell];

                            $isPlayer = false;

                            // Check if any other player is at this coordinate
                            foreach ($otherPlayers as $otherPlayer) {
                                if ($otherPlayer['x'] === $j && $otherPlayer['y'] === $i) {
                                    $isPlayer = true;
                                    $playerEmoji = $otherPlayer['emoji'];
                                    break;
                                }
                            }

                            if ($isPlayer) {
                                echo "<td style='width:30px;height:30px;background-color:$color;'>$playerEmoji</td>";
                            } elseif ($i === $_SESSION['y'] && $j === $_SESSION['x']) {
                                $additionalClass = ' pulsing';
                                echo "<td class='$additionalClass' style='width:30px;height:30px;background-color:$color;'>$emoji</td>";
                            } else {
                                echo "<td style='width:30px;height:30px;background-color:$color;'></td>";
                            }
                        }
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
            <pre>Coordinates: <?php echo $_SESSION['x'] . ', ' . $_SESSION['y']; ?> Selected Block: <?php echo $_SESSION['selected']; ?></pre>
        </div>
        <div class="grid-item3">
            <?php include 'legend.php'; ?>
        </div>
    </div>
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

        document.getElementById('emoji').addEventListener('change', function () {
            const newEmoji = this.value;
            const form = new FormData();
            form.append('emoji', newEmoji);
            fetch('', {
                method: 'POST',
                body: form
            }).then(() => {
                console.log("Emoji changed to: ", newEmoji);
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