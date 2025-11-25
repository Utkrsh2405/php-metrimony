<?php
session_start();
include_once("../../includes/dbconn.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch settings
    $settings_sql = "SELECT * FROM footer_settings LIMIT 1";
    $settings_result = mysqli_query($conn, $settings_sql);
    $settings = mysqli_fetch_assoc($settings_result);

    // Fetch links
    $links_sql = "SELECT * FROM footer_links ORDER BY column_name, display_order";
    $links_result = mysqli_query($conn, $links_sql);
    $links = [];
    while ($row = mysqli_fetch_assoc($links_result)) {
        $links[] = $row;
    }

    echo json_encode(['settings' => $settings, 'links' => $links]);

} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $copyright_text = mysqli_real_escape_string($conn, $_POST['copyright_text']);
        $facebook_link = mysqli_real_escape_string($conn, $_POST['facebook_link']);
        $twitter_link = mysqli_real_escape_string($conn, $_POST['twitter_link']);
        $instagram_link = mysqli_real_escape_string($conn, $_POST['instagram_link']);
        $youtube_link = mysqli_real_escape_string($conn, $_POST['youtube_link']);

        $background_image_sql = "";
        if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] == 0) {
            $target_dir = "../../uploads/homepage/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES["background_image"]["name"], PATHINFO_EXTENSION);
            $new_filename = "footer_bg_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $target_file)) {
                $background_image_sql = ", background_image = '$new_filename'";
            }
        }

        $sql = "UPDATE footer_settings SET 
                address = '$address',
                phone = '$phone',
                email = '$email',
                copyright_text = '$copyright_text',
                facebook_link = '$facebook_link',
                twitter_link = '$twitter_link',
                instagram_link = '$instagram_link',
                youtube_link = '$youtube_link'
                $background_image_sql
                WHERE id = 1";

        // If no row exists, insert one
        $check_sql = "SELECT id FROM footer_settings LIMIT 1";
        if (mysqli_num_rows(mysqli_query($conn, $check_sql)) == 0) {
             $sql = "INSERT INTO footer_settings (address, phone, email, copyright_text, facebook_link, twitter_link, instagram_link, youtube_link) 
                     VALUES ('$address', '$phone', '$email', '$copyright_text', '$facebook_link', '$twitter_link', '$instagram_link', '$youtube_link')";
        }

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($conn)]);
        }

    } elseif ($action === 'add_link') {
        $column_name = mysqli_real_escape_string($conn, $_POST['column_name']);
        $link_label = mysqli_real_escape_string($conn, $_POST['link_label']);
        $link_url = mysqli_real_escape_string($conn, $_POST['link_url']);
        
        // Get max order
        $order_sql = "SELECT MAX(display_order) as max_order FROM footer_links WHERE column_name = '$column_name'";
        $order_res = mysqli_query($conn, $order_sql);
        $order_row = mysqli_fetch_assoc($order_res);
        $display_order = ($order_row['max_order'] ?? 0) + 1;

        $sql = "INSERT INTO footer_links (column_name, link_label, link_url, display_order) 
                VALUES ('$column_name', '$link_label', '$link_url', $display_order)";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($conn)]);
        }

    } elseif ($action === 'delete_link') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM footer_links WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => mysqli_error($conn)]);
        }
    }
}
?>
