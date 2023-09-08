<?php
session_start();

if (isset($_GET['getCoords']) && $_GET['getCoords'] === 'true') {
    header('Content-Type: application/json');
    echo json_encode(['x' => $_SESSION['x'], 'y' => $_SESSION['y']]);
    exit;
}

$_SESSION['x'] = isset($_GET['x']) ? (int)$_GET['x'] : (isset($_SESSION['x']) ? $_SESSION['x'] : 0);
$_SESSION['y'] = isset($_GET['y']) ? (int)$_GET['y'] : (isset($_SESSION['y']) ? $_SESSION['y'] : 0);

if (!isset($_SESSION['selected'])) {
    $_SESSION['selected'] = 'none';
}

$file = 'world.txt';
if (!file_exists($file)) {
    $world = array_fill(0, 21, array_fill(0, 21, 'grass'));
    file_put_contents($file, serialize($world));
} else {
    $world = unserialize(file_get_contents($file));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'left') $_SESSION['x']--;
    if ($action === 'right') $_SESSION['x']++;
    if ($action === 'up') $_SESSION['y']--;
    if ($action === 'down') $_SESSION['y']++;

    if (in_array($action, ['grass', 'water', 'stone', 'dirt', 'none'])) {
        $_SESSION['selected'] = $action;
    }

    if ($_SESSION['selected'] !== 'none') {
        $world[$_SESSION['y'] + 10][$_SESSION['x'] + 10] = $_SESSION['selected'];
        file_put_contents($file, serialize($world));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHPCraft 2D!</title>
    <style>
    @keyframes pulse {
        0% {
            opacity: 0.5;
        }
        50% {
            opacity: 1;
        }
        100% {
            opacity: 0.5;
        }
    }
    .pulsing {
        animation: pulse 2s infinite;
        background-color: yellowgreen;
    }
    </style>
    <script>
    document.addEventListener('keydown', function(event) {
        let action;
        if (event.keyCode === 37) action = 'left';
        if (event.keyCode === 38) action = 'up';
        if (event.keyCode === 39) action = 'right';
        if (event.keyCode === 40) action = 'down';
        
        if (action) {
            handleAction(action);
        }
    });
    
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        const action = formData.get('action');
        handleAction(action, true);
        document.querySelector('h2').textContent = 'Selected Block: ' + action;
    });
    
    function handleAction(action, isBlockChange = false) {
        const form = new FormData();
        form.append('action', action);
        fetch('', {
            method: 'POST',
            body: form
        }).then(() => {
            if (!isBlockChange) {
                fetch('?getCoords=true')
                    .then(response => response.json())
                    .then(data => {
                        const newURL = `?x=${data.x}&y=${data.y}`;
                        window.location.href = newURL;
                    });
            }
        });
    }
    </script>
</head>
<body>
    <h1>Welcome to PHPCraft 2D!</h1>
    <h2>Selected Block: <?php echo $_SESSION['selected']; ?></h2>
    <form method="post">
        <button name="action" value="none">None</button>
        <button name="action" value="grass">Grass</button>
        <button name="action" value="water">Water</button>
        <button name="action" value="stone">Stone</button>
        <button name="action" value="dirt">Dirt</button>
    </form>
    <h2>World Around You:</h2>
    <table border="1">
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
</body>
</html>
