<?php
/**
 * IMAGE VALIDATION TEST
 * Test the new image validation arrays and functions
 */

include_once 'function.php';

echo "<h1>Image Validation Test</h1>";

// Display allowed types
echo "<h2>Allowed Image Types:</h2>";
echo "<pre>";
global $ALLOWED_IMAGE_TYPES, $ALLOWED_MIME_TYPES;
print_r($ALLOWED_IMAGE_TYPES);
echo "</pre>";

echo "<h2>Allowed MIME Types:</h2>";
echo "<pre>";
print_r($ALLOWED_MIME_TYPES);
echo "</pre>";

// Test isImageFile function
echo "<h2>Testing isImageFile() function:</h2>";

$test_files = [
    'photo.jpg' => 'image/jpeg',
    'picture.png' => 'image/png',
    'animation.gif' => 'image/gif',
    'modern.webp' => 'image/webp',
    'document.pdf' => 'application/pdf',
    'script.php' => 'text/php',
    'malicious.exe' => 'application/octet-stream'
];

foreach ($test_files as $filename => $mime_type) {
    $is_image = isImageFile($filename, $mime_type);
    echo "<p><strong>$filename</strong> ($mime_type): " . ($is_image ? '<span style="color:green">✓ IMAGE</span>' : '<span style="color:red">✗ NOT IMAGE</span>') . "</p>";
}

// Test validateImageUpload function (simulation)
echo "<h2>Testing validateImageUpload() function:</h2>";

$valid_image = [
    'name' => 'test.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => '/tmp/test.jpg',
    'error' => UPLOAD_ERR_OK,
    'size' => 1024000
];

$invalid_file = [
    'name' => 'virus.exe',
    'type' => 'application/octet-stream',
    'tmp_name' => '/tmp/virus.exe',
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

echo "<h3>Valid Image:</h3>";
$result1 = validateImageUpload($valid_image);
echo "<pre>";
print_r($result1);
echo "</pre>";

echo "<h3>Invalid File:</h3>";
$result2 = validateImageUpload($invalid_file);
echo "<pre>";
print_r($result2);
echo "</pre>";

echo "<hr><p><strong>Usage in your code:</strong></p>";
echo "<pre>";
echo "// Check if file is image
if (isImageFile(\$_FILES['file']['name'], \$_FILES['file']['type'])) {
    echo 'This is an image!';
} else {
    echo 'This is not an image!';
}

// Validate upload
\$validation = validateImageUpload(\$_FILES['file']);
if (\$validation['valid']) {
    // Upload the file
    \$result = uploadFile(\$_FILES['file']);
    if (\$result['success']) {
        echo 'File uploaded successfully: ' . \$result['filename'];
    }
} else {
    echo 'Upload failed: ' . \$validation['error'];
}";
echo "</pre>";
?>