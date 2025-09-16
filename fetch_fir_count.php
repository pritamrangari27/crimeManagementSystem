<?php
include 'conn.php';

// Fetch the count of FIRs with status 'Sent'
$query = "SELECT COUNT(*) as sent_firs FROM FIR WHERE status = 'Sent'";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo $row['sent_firs']; // Return the count of FIRs with status 'Sent'
} else {
    echo 0; // Return 0 if there's an error or no FIRs with status 'Sent'
}
?>