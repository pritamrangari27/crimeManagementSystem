<?php
include 'conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM police WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Record deleted successfully!');
            window.location.href = 'managePolice.php';
        </script>";
    } else {
        echo "<script>
            alert('Error deleting record: " . mysqli_error($conn) . "');
            window.location.href = 'managePolice.php';
        </script>";
    }
}
?>
