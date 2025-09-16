<?php
include 'conn.php';

if (isset($_GET['id'])) { // Use 'id' instead of 'station_id'
    $station_id = $_GET['id']; // Use 'id' as the parameter name
    $sql = "DELETE FROM police_station WHERE station_id = $station_id";
    if (mysqli_query($conn, $sql)) {
        // Redirect to managePoliceStation.php after successful deletion
        header("Location: managePoliceStation.php");
        exit();
    } else {
        // Redirect with an error message if deletion fails
        header("Location: managePoliceStation.php?error=" . urlencode(mysqli_error($conn)));
        exit();
    }
} else {
    // Redirect if no ID is provided
    header("Location: managePoliceStation.php?error=No ID provided");
    exit();
}
?>