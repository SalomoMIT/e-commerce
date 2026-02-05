<?php namespace App\Models;

require_once APPPATH . 'ThirdParty/intervention-image/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\EncoderInterface;

class UploadModel extends BaseModel
{
    protected $imgQuality;

    public function __construct()
    {
        parent::__construct();
        $this->imgQuality = PRODUCT_IMAGE_QUALITY;
    }

    //upload file
    private function upload($inputName, $folderName, $namePrefix, $allowedExtensions = null, $keepOrjName = false)
    {
        if (!empty($allowedExtensions) && is_array($allowedExtensions)) {
            if (!$this->checkAllowedFileTypes($inputName, $allowedExtensions)) {
                return null;
            }
        }

        $file = $this->request->getFile($inputName);
        if ($file && $file->isValid()) {
            $orjName = $file->getName();
            $name = strSlug(pathinfo($orjName, PATHINFO_FILENAME));
            $ext = strtolower(pathinfo($orjName, PATHINFO_EXTENSION));

            $token = generateToken(true);
            if (empty($name)) {
                $name = $token;
            }

            $useDateFolder = ($folderName !== 'temp' && $folderName !== 'logo' && $folderName !== 'digital-files');
            $dateFolder = $useDateFolder ? $this->createUploadDirectory($folderName) : '';
            $directory = $useDateFolder
                ? 'uploads/' . $folderName . '/' . $dateFolder . '/'
                : 'uploads/' . $folderName . '/';

            $uniqueName = $namePrefix . $token . '.' . $ext;

            if ($keepOrjName) {
                $fullName = $name . '.' . $ext;
                if (file_exists(FCPATH . $directory . $fullName)) {
                    $fullName = $name . '-' . uniqid() . '.' . $ext;
                }
                $uniqueName = $fullName;
            }

            $path = $directory . $uniqueName;

            if ($file->move(FCPATH . $directory, $uniqueName)) {
                return [
                    'name' => ($useDateFolder ? $dateFolder . '/' : '') . $uniqueName,
                    'orjName' => $orjName,
                    'path' => $path,
                    'ext' => $ext,
                    'storage' => 'local'
                ];
            }
        }

        return null;
    }

    //upload temp image
    public function uploadTempFile($inputName, $isImage = true, $allowedExtensions = [])
    {
        if ($isImage) {
            $allowedExtensions = ['jpg', 'jpeg', 'webp', 'png', 'gif'];
        }
        return $this->upload($inputName, 'temp', 'temp_', $allowedExtensions);
    }

    //optimize image
    public function optimizeImage($resizeMethod, $tempPath, $folderName, $prefix, $width, $height, $watermarkType = null)
    {
        if (empty($tempPath) || !file_exists($tempPath)) {
            return '';
        }

        try {
            // Setup & Configuration
            $manager = new ImageManager(new GdDriver());
            $imgQuality = in_array($prefix, ['flag_', 'brand_', 'pwa_', 'profile_', 'newsletter_']) ? 100 : 85;
            $moveToStorage = !in_array($prefix, ['flag_', 'slider_']);
            $originalExt = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION)) ?: 'jpg';
            $convertToWebp = ($this->productSettings->image_file_format === 'WEBP' && $originalExt !== 'webp');

            $finalExt = $convertToWebp ? 'webp' : $originalExt;
            $encoder = match ($finalExt) {
                'webp' => new WebpEncoder($imgQuality),
                'png' => new PngEncoder(),
                'gif' => new \Intervention\Image\Encoders\GifEncoder(),
                default => new JpegEncoder($imgQuality),
            };

            // Filename & Path
            if ($resizeMethod === 'cover' && !empty($width) && !empty($height)) {
                $prefix .= $width . 'x' . $height . '_';
            } elseif (!empty($width)) {
                $prefix .= 'w' . $width . '_';
            } elseif (!empty($height)) {
                $prefix .= 'h' . $height . '_';
            }

            $isTemp = ($folderName === 'temp');
            $dateFolder = $isTemp ? '' : $this->createUploadDirectory($folderName);
            $uploadDirectory = 'uploads/' . $folderName . '/' . ($isTemp ? '' : $dateFolder . '/');
            $newName = $prefix . generateToken(true);
            $fullFileName = $newName . '.' . $finalExt;
            $savePath = FCPATH . $uploadDirectory . $fullFileName;
            $returnName = ($isTemp ? '' : $dateFolder . '/') . $fullFileName;

            // Image Processing
            $image = $manager->read($tempPath)->orient();

            switch ($resizeMethod) {
                case 'cover':
                    if (!empty($width) && !empty($height)) {
                        $image->cover($width, $height);
                    }
                    break;
                case 'resize':
                    $image->scaleDown($width, $height);
                    break;
            }

            // Apply watermark
            if ($watermarkType !== null) {
                $image = $this->applyWatermark($image, $watermarkType, $width);
            }

            // Save
            $image->encode($encoder)->save($savePath);

            if ($this->activeStorage !== 'local' && !$isTemp && $moveToStorage) {
                $this->moveToStorage($savePath, $uploadDirectory . $fullFileName);
            }

            return $returnName;


        } catch (\Exception $e) {
            log_message('error', 'optimizeImage() failed for ' . $tempPath . ': ' . $e->getMessage());
            return '';
        }
    }

    //upload blog image
    public function uploadBlogImage($tempPath, $size)
    {
        if ($size === 'small') {
            $fileName = $this->optimizeImage('cover', $tempPath, 'blog', 'img_', 500, 332, 'blog');
        } else {
            $fileName = $this->optimizeImage('resize', $tempPath, 'blog', 'img_', 1280, null, 'blog');
        }

        return 'uploads/blog/' . $fileName;
    }

    //upload category image
    public function uploadCategoryImage($tempPath)
    {
        return 'uploads/category/' . $this->optimizeImage('cover', $tempPath, 'category', 'category_', 420, 420);
    }

    //upload slider image
    public function uploadSliderImage($tempPath, $isMobile)
    {
        if ($isMobile) {
            $fileName = $this->optimizeImage('cover', $tempPath, 'slider', 'slider_', 768, 500);
        } else {
            $fileName = $this->optimizeImage('cover', $tempPath, 'slider', 'slider_', 1920, 600);
        }

        return 'uploads/slider/' . $fileName;
    }

    //upload profile image
    public function uploadProfileImage($tempPath, $type = 'profile')
    {
        if ($type == 'profile') {
            return 'uploads/profile/' . $this->optimizeImage('cover', $tempPath, 'profile', 'profile_', 300, 300);
        } elseif ($type == 'cover') {
            return 'uploads/profile/' . $this->optimizeImage('cover', $tempPath, 'profile', 'cover_', 1920, 400);
        }
        return '';
    }

    //upload newsletter image
    public function uploadNewsletterImage($tempPath)
    {
        return 'uploads/blocks/' . $this->optimizeImage('cover', $tempPath, 'blocks', 'newsletter_', 500, 500);
    }

    //upload affiliate image
    public function uploadAffiliateImage($tempPath)
    {
        return 'uploads/blocks/' . $this->optimizeImage('cover', $tempPath, 'blocks', 'img_', 1200, 980);
    }

    //upload brand
    public function uploadBrand($tempPath)
    {
        return 'uploads/blocks/' . $this->optimizeImage('resize', $tempPath, 'blocks', 'brand_', 256, null);
    }

    //vendor document upload
    public function uploadVendorDocuments(): array
    {
        $uploadedFiles = $this->request->getFiles();
        $result = [];

        if (isset($uploadedFiles['file'])) {
            $files = $uploadedFiles['file'];

            if (!is_array($files)) {
                $files = [$files];
            }

            $dateFolder = $this->createUploadDirectory('support');

            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved() && $file->getSize() <= 5 * 1024 * 1024) {
                    $ext = $file->getExtension();
                    $newFileName = 'file_' . generateToken(true) . '.' . $ext;

                    $tempPath = 'uploads/temp/' . $newFileName;
                    $storageFolder = 'uploads/support/' . $dateFolder;
                    $finalPath = $storageFolder . '/' . $newFileName;

                    if ($file->move(FCPATH . 'uploads/temp', $newFileName)) {
                        if ($this->moveToStorage(FCPATH . $tempPath, $storageFolder . '/' . $newFileName)) {
                            $result[] = [
                                'name' => $file->getClientName(),
                                'path' => $finalPath,
                                'storage' => $this->activeStorage
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }

    //upload logo
    public function uploadLogo($inputName)
    {
        return $this->upload($inputName, 'logo', 'logo_', ['jpg', 'jpeg', 'png', 'gif', 'svg']);
    }

    //upload favicon
    public function uploadFavicon($inputName)
    {
        return $this->upload($inputName, 'logo', 'favicon_', ['jpg', 'jpeg', 'png', 'gif']);
    }

    //upload pwa logo
    public function uploadPwaLogo(string $tempPath)
    {
        if (empty($tempPath) || !file_exists($tempPath)) {
            return false;
        }

        $manager = new ImageManager(new GdDriver());

        $sizes = [
            'lg' => [512, 512],
            'md' => [192, 192],
            'sm' => [144, 144],
        ];

        $output = [];

        foreach ($sizes as $key => [$width, $height]) {
            $relativePath = "uploads/logo/pwa_{$width}x{$height}.png";
            $fullPath = FCPATH . $relativePath;

            $manager->read($tempPath)->orient()->cover($width, $height)->toPng()->save($fullPath);

            $output[$key] = $relativePath;
        }

        $this->deleteTempFile($tempPath);

        return $output;
    }

    //upload flag
    public function uploadFlag($tempPath)
    {
        return 'uploads/blocks/' . $this->optimizeImage('resize', $tempPath, 'blocks', 'flag_', null, 100);
    }

    //upload ad
    public function uploadAd($inputName)
    {
        return $this->handleDirectUpload($inputName, 'blocks', 'block_', [
            'jpg', 'jpeg', 'webp', 'png', 'gif'
        ]);
    }

    //upload receipt
    public function uploadReceipt($inputName)
    {
        return $this->handleDirectUpload($inputName, 'receipts', 'receipt_', [
            'pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx'
        ]);
    }

    //upload video
    public function uploadVideo($inputName)
    {
        return $this->handleDirectUpload($inputName, 'videos', 'video_', [
            'mp4', 'webm'
        ]);
    }

    //upload audio
    public function uploadAudio($inputName)
    {
        return $this->handleDirectUpload($inputName, 'audios', 'audio_', [
            'mp3', 'wav'
        ]);
    }

    //digital file upload
    public function uploadDigitalFile($inputName)
    {
        $folderName = 'digital-files';
        return $this->upload($inputName, $folderName, 'digital-file-');
    }

    //upload ticket attachment
    public function uploadAttachment($tempPath)
    {
        if (empty($tempPath) || !file_exists($tempPath)) {
            return false;
        }

        $ext = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));
        if (!in_array($ext, getAppDefault('safeExtensions'))) {
            return false;
        }

        $newName = 'attachment_' . uniqid('', true) . '.' . $ext;
        $dateFolder = $this->createUploadDirectory('support', $this->activeStorage === 'local');
        $path = 'uploads/support/' . $dateFolder . '/' . $newName;

        if ($this->moveToStorage($tempPath, $path)) {
            return $dateFolder . '/' . $newName;
        }

        return false;
    }

    //upload file directly
    private function handleDirectUpload($inputName, $folderName, $prefix, $allowedExtensions)
    {
        if (!$this->checkAllowedFileTypes($inputName, $allowedExtensions)) {
            return null;
        }

        if ($this->activeStorage !== 'local') {
            $file = $this->request->getFile($inputName);
            if (!$file || !$file->isValid()) {
                return null;
            }

            $ext = strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION));
            $fileName = $prefix . generateToken(true) . '.' . $ext;
            $dateFolder = $this->createUploadDirectory($folderName, false);
            $targetPath = 'uploads/' . $folderName . '/' . $dateFolder . '/' . $fileName;

            if ($this->moveToStorage($file->getTempName(), $targetPath)) {
                return [
                    'name' => $dateFolder . '/' . $fileName,
                    'path' => $targetPath,
                    'storage' => $this->activeStorage
                ];
            }

            return null;
        }

        return $this->upload($inputName, $folderName, $prefix, $allowedExtensions);
    }

    //move file to storage
    public function moveToStorage($sourcePath, $targetPath, $deleteSource = true)
    {
        if (!file_exists($sourcePath) || empty($targetPath)) {
            return false;
        }

        try {
            if ($this->activeStorage === 'local') {
                $destination = FCPATH . ltrim($targetPath, '/');

                $dir = dirname($destination);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                if (copy($sourcePath, $destination)) {
                    if ($deleteSource) {
                        @unlink($sourcePath);
                    }
                    return true;
                }

                return false;
            } else {
                $settings = getSettingsUnserialized('storage');
                $storageLib = new \App\Libraries\Storage($settings);
                if ($storageLib->putObject($targetPath, $sourcePath)) {
                    if ($deleteSource) {
                        @unlink($sourcePath);
                    }
                    return true;
                }
            }

        } catch (\Throwable $e) {
            log_message('error', 'moveToStorage() failed: ' . $e->getMessage());
        }

        return false;
    }

    //download temp image
    public function downloadTempImage(?string $url, string $fileName = 'temp')
    {
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (empty($url)) {
            return false;
        }

        // Use parse_url to get the path for extension checking, as it handles non-ASCII characters
        $urlPath = parse_url($url, PHP_URL_PATH);
        if ($urlPath === false) {
            return false;
        }

        $ext = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return false;
        }

        $dir = FCPATH . 'uploads/temp/';
        if (!is_dir($dir)) {
            // Create directory recursively
            mkdir($dir, 0755, true);
        }

        $path = $dir . $fileName . '.' . $ext;
        if (file_exists($path)) {
            unlink($path);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Execute the cURL session
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }

        // Check for a successful HTTP status code (2xx range)
        if ($httpCode < 200 || $httpCode >= 300) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        // Save the image data to the file
        if ($imageData !== false) {
            if (file_put_contents($path, $imageData)) {
                return $path;
            } else {
                return false;
            }
        }

        return false;
    }

    //check allowed file types
    public function checkAllowedFileTypes($fileName, $allowedTypes)
    {
        if (empty($_FILES[$fileName]['name'])) {
            return false;
        }

        $ext = strtolower(pathinfo($_FILES[$fileName]['name'], PATHINFO_EXTENSION));

        //normalize allowed extensions (trim quotes, lowercase)
        $cleanTypes = array_map(function ($type) {
            return strtolower(trim($type, "\"' "));
        }, $allowedTypes);

        return in_array($ext, $cleanTypes, true);
    }

    //add watermark
    private function applyWatermark(ImageInterface $image, string $watermarkType, ?int $width): ImageInterface
    {
        $settings = getSettingsUnserialized('watermark');
        $addWatermark = (
            ($watermarkType === 'product' && $settings->w_product_images == 1) ||
            ($watermarkType === 'blog' && $settings->w_blog_images == 1)
        );

        if ($width < PRODUCT_IMAGE_DEFAULT && $settings->w_thumbnail_images != 1) {
            $addWatermark = false;
        }

        if ($addWatermark) {
            $fontSize = (float)$settings->w_font_size;
            if ($width > PRODUCT_IMAGE_DEFAULT) $fontSize *= 1.8;
            elseif ($width <= PRODUCT_IMAGE_SMALL) $fontSize *= 0.72;

            $vAlign = ($settings->w_vrt_alignment == 'center') ? 'middle' : $settings->w_vrt_alignment;
            $hAlign = $settings->w_hor_alignment;
            $imageWidth = $image->width();
            $imageHeight = $image->height();

            // Determine the anchor coordinates (x, y) based on alignment settings
            $x = match ($hAlign) {
                'left' => 15, // Margin from left edge
                'center' => intval($imageWidth / 2), // Exactly in the horizontal center
                'right' => $imageWidth - 15, // Margin from right edge
            };

            $y = match ($vAlign) {
                'top' => 15, // Margin from top edge
                'middle' => intval($imageHeight / 2), // Exactly in the vertical center
                'bottom' => $imageHeight - 15, // Margin from bottom edge
            };

            $image->text(esc($settings->w_text), $x, $y, function ($font) use ($fontSize, $hAlign, $vAlign) {
                $font->filename(FCPATH . 'assets/fonts/open-sans/OpenSans-Bold.ttf');
                $font->size($fontSize);
                $font->color('rgba(255, 255, 255, 0.5)');
                $font->align($hAlign);
                $font->valign($vAlign);
                $font->lineHeight(1.6);
                $font->angle(0);
            });
        }

        return $image;
    }

    //get new extension
    private function getNewExtension($path)
    {
        $originalExt = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (empty($originalExt)) {
            $originalExt = 'jpg';
        }

        switch ($this->productSettings->image_file_format) {
            case 'JPG':
                return 'jpg';
            case 'PNG':
                return 'png';
            case 'WEBP':
                return $originalExt;
            default:
                return $originalExt;
        }
    }

    //create upload directory
    public function createUploadDirectory($folder, $createLocal = true)
    {
        $directory = date('Ym');

        if ($createLocal) {
            $directoryPath = FCPATH . 'uploads/' . trim($folder, '/') . '/' . $directory;

            if (!is_dir($directoryPath)) {
                if (!@mkdir($directoryPath, 0755, true)) {
                    log_message('error', 'Failed to create directory: ' . $directoryPath);
                }
            }

            $indexFile = $directoryPath . '/index.html';
            if (!file_exists($indexFile)) {
                @copy(FCPATH . 'uploads/index.html', $indexFile);
            }
        }

        return $directory;
    }

    //delete temp file
    public function deleteTempFile($path)
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
