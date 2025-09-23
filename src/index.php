<?php

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$mikrotikHost = $_ENV['MIKROTIK_HOST'] ?? 'localhost';
$mikrotikUser = $_ENV['MIKROTIK_USER'] ?? 'admin';
$mikrotikPass = $_ENV['MIKROTIK_PASS'] ?? '';
$mikrotikPort = $_ENV['MIKROTIK_PORT'] ?? 8728;

require_once __DIR__ . '/vendor/evilfreelancer/routeros-api-php/routerosapi.php';

$api = new RouterosAPI();
$api->debug = false;

$status = 'disconnected';
$activeUsers = [];

try {
    if ($api->connect($mikrotikHost, $mikrotikUser, $mikrotikPass, $mikrotikPort)) {
        $status = 'connected';
        $activeUsers = $api->comm("/ppp/active/print", array(
            "?disabled" => "false",
            "=.proplist" => "name,address,caller-id"
        ));
        $api->disconnect();
    } else {
        $status = 'disconnected';
    }
} catch (Exception $e) {
    $status = 'disconnected';
    // Log the exception for debugging
    error_log("MikroTik connection/query error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MikroTik PPPoE Monitor (PHP)</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 900px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .status { text-align: center; margin-bottom: 20px; }
        .status span { font-weight: bold; padding: 5px 10px; border-radius: 5px; }
        .status .connected { background-color: #4CAF50; color: white; }
        .status .disconnected { background-color: #f44336; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #e9e9e9; }
        .no-data { text-align: center; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>MikroTik PPPoE Monitor (PHP)</h1>
        <div class="status">
            MikroTik Status: <span class="<?php echo $status; ?>"><?php echo ucfirst($status); ?></span>
        </div>

        <h2>Active PPPoE Users</h2>
        <?php if (!empty($activeUsers)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Caller ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeUsers as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($user['caller-id'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No active users found or connection failed.</p>
        <?php endif; ?>
    </div>
</body>
</html>
