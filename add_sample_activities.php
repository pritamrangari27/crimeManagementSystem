<?php
include "conn.php";

// Create activity_logs table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    user VARCHAR(100) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($create_table_sql)) {
    die("Error creating activity_logs table: " . $conn->error);
}

// Sample activities to add
$activities = [
    [
        'activity_type' => 'police',
        'description' => '<span class="font-weight-bold">New police officer</span> John Smith added to Wakad Police Station',
        'user' => 'admin',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ],
    [
        'activity_type' => 'fir',
        'description' => '<span class="font-weight-bold">FIR</span> from PCMC Police Station for Phishing & Online Fraud has been <span class="font-weight-bold">Approved</span>',
        'user' => 'admin',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ],
    [
        'activity_type' => 'criminal',
        'description' => '<span class="font-weight-bold">Criminal record</span> for James Wilson updated with new charges',
        'user' => 'admin',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-3 days'))
    ],
    [
        'activity_type' => 'station',
        'description' => '<span class="font-weight-bold">New police station</span> Kharadi Police Station (SY104) added in Maharashtra',
        'user' => 'admin',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-5 days'))
    ],
    [
        'activity_type' => 'login',
        'description' => '<span class="font-weight-bold">admin</span> logged in as Admin',
        'user' => 'admin',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
    ]
];

// Insert sample activities
$insert_sql = "INSERT INTO activity_logs (activity_type, description, user, timestamp) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssss", $activity_type, $description, $user, $timestamp);

foreach ($activities as $activity) {
    $activity_type = $activity['activity_type'];
    $description = $activity['description'];
    $user = $activity['user'];
    $timestamp = $activity['timestamp'];
    
    if (!$stmt->execute()) {
        echo "Error adding activity: " . $stmt->error . "<br>";
    } else {
        echo "Added activity: " . $description . "<br>";
    }
}

$stmt->close();
$conn->close();

echo "<p>Sample activities added successfully. <a href='homepage.php'>Go to Dashboard</a></p>";
?>
