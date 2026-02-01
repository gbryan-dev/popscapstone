<?php
header('Content-Type: application/json');

// Image file path (same directory)
$imagePath = __DIR__ . '/sample.jpg';

// Check if file exists
if (!file_exists($imagePath)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'sample.jpg not found in the same directory as analyze.php'
    ]);
    exit;
}

try {
    $metadata = extractImageMetadata($imagePath);
    $errorAnalysis = performErrorLevelAnalysis($imagePath);

    echo json_encode([
        'success' => true,
        'metadata' => $metadata,
        'errorAnalysis' => $errorAnalysis
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

/**
 * Extract metadata from image
 */
function extractImageMetadata($imagePath) {
    $metadata = [];

    // Basic image information
    $imageInfo = @getimagesize($imagePath);
    if ($imageInfo) {
        $metadata['Image Width'] = $imageInfo[0] . ' px';
        $metadata['Image Height'] = $imageInfo[1] . ' px';
        $metadata['Image Type'] = getImageTypeName($imageInfo[2]);
        $metadata['Aspect Ratio'] = number_format($imageInfo[0] / $imageInfo[1], 2);
    }

    // File information
    $metadata['File Size'] = formatBytes(filesize($imagePath));
    $metadata['File Name'] = basename($imagePath);
    $metadata['Modified Date'] = date('Y-m-d H:i:s', filemtime($imagePath));
    $metadata['MIME Type'] = mime_content_type($imagePath);

    // EXIF data (if available)
    if (function_exists('exif_read_data')) {
        $exif = @exif_read_data($imagePath);
        if ($exif) {
            if (isset($exif['Make'])) $metadata['Camera Make'] = $exif['Make'];
            if (isset($exif['Model'])) $metadata['Camera Model'] = $exif['Model'];
            if (isset($exif['DateTime'])) $metadata['Photo Date'] = $exif['DateTime'];
            if (isset($exif['ExposureTime'])) $metadata['Exposure Time'] = $exif['ExposureTime'];
            if (isset($exif['FNumber'])) $metadata['F-Number'] = $exif['FNumber'];
            if (isset($exif['ISOSpeedRatings'])) $metadata['ISO'] = $exif['ISOSpeedRatings'];
            if (isset($exif['FocalLength'])) $metadata['Focal Length'] = $exif['FocalLength'];
            if (isset($exif['Orientation'])) $metadata['Orientation'] = $exif['Orientation'];
        }
    }

    // JPEG specific info
    $jpegInfo = getJPEGInfo($imagePath);
    if ($jpegInfo) {
        $metadata = array_merge($metadata, $jpegInfo);
    }

    return $metadata;
}

/**
 * Perform Error Level Analysis
 */
function performErrorLevelAnalysis($imagePath) {
    $analysis = [
        'errorLevels' => [],
        'stats' => [],
        'qualityAssessment' => ''
    ];

    try {
        // Get image dimensions
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return $analysis;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Create image resources
        $originalImage = @imagecreatefromjpeg($imagePath);
        if (!$originalImage) {
            throw new Exception('Failed to load image');
        }

        // Re-encode at different quality levels to detect compression artifacts
        $reEncodedHigh = imagecreatefromstring(encodeImageAtQuality($originalImage, 95));
        $reEncodedLow = imagecreatefromstring(encodeImageAtQuality($originalImage, 50));

        // Calculate error metrics
        $totalPixels = $width * $height;
        $redErrors = 0;
        $greenErrors = 0;
        $blueErrors = 0;

        // Sample pixels to analyze error levels
        $sampleRate = max(1, intval(sqrt($totalPixels / 1000)));

        for ($y = 0; $y < $height; $y += $sampleRate) {
            for ($x = 0; $x < $width; $x += $sampleRate) {
                $originalRGB = imagecolorat($originalImage, $x, $y);
                $reEncodedRGB = imagecolorat($reEncodedLow, $x, $y);

                $orig = imagecolorsforindex($originalImage, $originalRGB);
                $reenc = imagecolorsforindex($reEncodedLow, $reEncodedRGB);

                $redErrors += abs($orig['red'] - $reenc['red']);
                $greenErrors += abs($orig['green'] - $reenc['green']);
                $blueErrors += abs($orig['blue'] - $reenc['blue']);
            }
        }

        // Normalize error levels (0-1 scale)
        $sampleCount = ceil($width / $sampleRate) * ceil($height / $sampleRate);
        $analysis['errorLevels']['Red Channel'] = min(1, ($redErrors / ($sampleCount * 255)));
        $analysis['errorLevels']['Green Channel'] = min(1, ($greenErrors / ($sampleCount * 255)));
        $analysis['errorLevels']['Blue Channel'] = min(1, ($blueErrors / ($sampleCount * 255)));

        // Calculate average error
        $avgError = ($analysis['errorLevels']['Red Channel'] + 
                    $analysis['errorLevels']['Green Channel'] + 
                    $analysis['errorLevels']['Blue Channel']) / 3;

        // Statistics
        $analysis['stats']['Average Error'] = round($avgError * 100, 2) . '%';
        $analysis['stats']['Max Error'] = round(max($analysis['errorLevels']) * 100, 2) . '%';
        $analysis['stats']['Total Pixels'] = number_format($totalPixels);
        $analysis['stats']['Compression Ratio'] = round((filesize($imagePath) / ($width * $height * 3)) * 100, 2) . '%';

        // Quality assessment
        if ($avgError < 0.05) {
            $analysis['qualityAssessment'] = '✓ Excellent - Minimal compression artifacts detected';
        } elseif ($avgError < 0.15) {
            $analysis['qualityAssessment'] = '✓ Good - Low compression artifacts';
        } elseif ($avgError < 0.30) {
            $analysis['qualityAssessment'] = '⚠ Fair - Moderate compression artifacts present';
        } else {
            $analysis['qualityAssessment'] = '✗ Poor - High compression artifacts detected';
        }

        // Cleanup
        imagedestroy($originalImage);
        imagedestroy($reEncodedHigh);
        imagedestroy($reEncodedLow);

    } catch (Exception $e) {
        $analysis['qualityAssessment'] = 'Analysis failed: ' . $e->getMessage();
    }

    return $analysis;
}

/**
 * Helper functions
 */

function getImageTypeName($type) {
    $types = [
        1 => 'GIF',
        2 => 'JPEG',
        3 => 'PNG',
        4 => 'SWF',
        5 => 'PSD',
        6 => 'BMP',
        7 => 'TIFF (Intel)',
        8 => 'TIFF (Motorola)',
        9 => 'JPC',
        10 => 'JP2',
        11 => 'JPX',
        12 => 'JB2',
        13 => 'SWC',
        14 => 'IFF',
        15 => 'WBMP',
        16 => 'XBM',
        17 => 'ICO',
        18 => 'WebP'
    ];
    return $types[$type] ?? 'Unknown';
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getJPEGInfo($imagePath) {
    $info = [];
    $handle = fopen($imagePath, 'rb');
    
    if (!$handle) return $info;

    // Read JPEG markers
    $data = fread($handle, 2);
    if ($data === "\xFF\xD8") {
        $info['JPEG Signature'] = 'Valid (FFD8)';

        while (!feof($handle)) {
            $marker = fread($handle, 2);
            if (strlen($marker) < 2) break;

            $markerType = ord($marker[1]);

            if ($markerType === 0xC0 || $markerType === 0xC1 || $markerType === 0xC2) {
                // SOF marker (Start of Frame)
                $length = (ord($marker[0]) << 8) | ord($marker[1]);
                $data = fread($handle, $length - 2);
                
                if (strlen($data) >= 7) {
                    $precision = ord($data[0]);
                    $height = (ord($data[1]) << 8) | ord($data[2]);
                    $width = (ord($data[3]) << 8) | ord($data[4]);
                    $components = ord($data[5]);

                    $info['Bit Precision'] = $precision . ' bits';
                    $info['Color Components'] = $components;
                }
                break;
            }

            if ($markerType === 0xE0) {
                // APP0 marker (JFIF)
                $length = (ord($marker[0]) << 8) | ord($marker[1]);
                fread($handle, $length - 2);
            } else {
                $length = (ord($marker[0]) << 8) | ord($marker[1]);
                fread($handle, max(0, $length - 2));
            }
        }
    }

    fclose($handle);
    return $info;
}

function encodeImageAtQuality($image, $quality) {
    ob_start();
    imagejpeg($image, null, $quality);
    return ob_get_clean();
}

?>