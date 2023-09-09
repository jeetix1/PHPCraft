<h1>Welcome to PHPCraft 2D!</h1>
<pre>Move around with arrow keys. No fancy controllers required.<br>Place blocks like grass, water, stone, and dirt. You're basically a god.</pre>
<h2>World Around You:</h2>
<form method="get">
    <label for="username">User:</label>
    <input type="text" id="username" name="username" value="<?php echo $username; ?>" autocomplete="off">
    <select id="emoji" name="emoji">
        <option value="😼" <?php if ($playerData['emoji'] === '😼')
            echo 'selected'; ?>>😼</option>
        <option value="👽" <?php if ($playerData['emoji'] === '👽')
            echo 'selected'; ?>>👽</option>
        <option value="👾" <?php if ($playerData['emoji'] === '👾')
            echo 'selected'; ?>>👾</option>
        <option value="🤖" <?php if ($playerData['emoji'] === '🤖')
            echo 'selected'; ?>>🤖</option>
        <option value="👻" <?php if ($playerData['emoji'] === '👻')
            echo 'selected'; ?>>👻</option>
        <option value="🐶" <?php if ($playerData['emoji'] === '🐶')
            echo 'selected'; ?>>🐶</option>
        <option value="🦁" <?php if ($playerData['emoji'] === '🦁')
            echo 'selected'; ?>>🦁</option>
        <option value="🐯" <?php if ($playerData['emoji'] === '🐯')
            echo 'selected'; ?>>🐯</option>
        <option value="🐭" <?php if ($playerData['emoji'] === '🐭')
            echo 'selected'; ?>>🐭</option>
        <option value="🐸" <?php if ($playerData['emoji'] === '🐸')
            echo 'selected'; ?>>🐸</option>
    </select>
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