<?php
// Recreate the missing database triggers
echo "<h2>🔧 Recreating Database Triggers</h2>";

$servername = "localhost";
$username = "root";
$password = "";
$database = "grocery";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
echo "✅ Connected to database<br><br>";

// First, let's check if triggers exist and drop them if they do
$triggers = ['delete', 'insert1', 'update1'];
foreach ($triggers as $trigger) {
    $result = $conn->query("SHOW TRIGGERS WHERE `Trigger` = '$trigger'");
    if ($result->num_rows > 0) {
        $conn->query("DROP TRIGGER IF EXISTS `$trigger`");
        echo "Dropped existing trigger: $trigger<br>";
    }
}

// Recreate the DELETE trigger - this is the critical one for transactions
echo "<br>Creating DELETE trigger...<br>";
$delete_trigger = "
CREATE TRIGGER `delete` BEFORE DELETE ON `cart` FOR EACH ROW 
BEGIN
    INSERT INTO purchase(pcid, ppid, no_of_items, cost_of_items, date_time) 
    VALUES(OLD.cid, OLD.pid, OLD.no_of_items, OLD.no_of_items * OLD.cost_of_item, NOW());
END
";

if ($conn->query($delete_trigger)) {
    echo "✅ DELETE trigger created successfully<br>";
} else {
    echo "❌ Failed to create DELETE trigger: " . $conn->error . "<br>";
}

// Recreate the INSERT trigger
echo "<br>Creating INSERT trigger...<br>";
$insert_trigger = "
CREATE TRIGGER `insert1` AFTER INSERT ON `cart` FOR EACH ROW 
BEGIN
    UPDATE products SET no_of_items = no_of_items - 1 WHERE ID = NEW.pid;
END
";

if ($conn->query($insert_trigger)) {
    echo "✅ INSERT trigger created successfully<br>";
} else {
    echo "❌ Failed to create INSERT trigger: " . $conn->error . "<br>";
}

// Recreate the UPDATE trigger
echo "<br>Creating UPDATE trigger...<br>";
$update_trigger = "
CREATE TRIGGER `update1` AFTER UPDATE ON `cart` FOR EACH ROW 
BEGIN
    UPDATE products SET no_of_items = no_of_items - 1 WHERE ID = OLD.pid;
END
";

if ($conn->query($update_trigger)) {
    echo "✅ UPDATE trigger created successfully<br>";
} else {
    echo "❌ Failed to create UPDATE trigger: " . $conn->error . "<br>";
}

// Verify triggers were created
echo "<br><h3>Verifying triggers:</h3>";
$result = $conn->query("SHOW TRIGGERS");
if ($result->num_rows > 0) {
    echo "✅ Found " . $result->num_rows . " triggers:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- Trigger: <strong>" . $row['Trigger'] . "</strong> on table: " . $row['Table'] . " Event: " . $row['Event'] . "<br>";
    }
} else {
    echo "❌ No triggers found!<br>";
}

$conn->close();

echo "<br><h3>🎯 Test the system now!</h3>";
echo "The transaction system should now work properly. When customers checkout, their purchases will be automatically recorded.";
?>