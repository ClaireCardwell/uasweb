<?php
// Database connection parameters
$host = "localhost"; // Change if needed
$user = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$database = "berita"; // Name of your database

// Create a connection
$conn = new mysqli($host, $user, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to update the view count
function updateViewCount($conn, $postId) {
    $sql = "UPDATE posts SET view = view + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $conn->error); // Error handling
    }
    $stmt->bind_param("i", $postId);
    if ($stmt->execute()) {
        $stmt->close();
        return true; // Successfully updated
    } else {
        echo "Failed to update view count: " . $stmt->error; // Error handling
        $stmt->close();
        return false; // Failed to update
    }
}

// Function to fetch recent posts with pagination
function fetchRecentPosts($conn, $limit, $offset) {
    $sql = "SELECT id, judul, isi, images, view FROM posts ORDER BY tanggal_publikasi DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $conn->error); // Error handling
    }
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch trending posts
function fetchTrendingPosts($conn) {
    $sql = "SELECT judul, isi, images, view FROM posts ORDER BY view DESC LIMIT 5"; // Adjust limit as needed
    return $conn->query($sql);
}
?>
