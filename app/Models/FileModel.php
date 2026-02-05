<?php namespace App\Models;

class FileModel extends BaseModel
{
    protected $builderImages;
    protected $builderMedia;
    protected $builderBlogImages;
    protected $builderDigitalFiles;
    protected $builderFileManager;

    public function __construct()
    {
        parent::__construct();
        $this->builderImages = $this->db->table('images');
        $this->builderMedia = $this->db->table('media');
        $this->builderBlogImages = $this->db->table('blog_images');
        $this->builderDigitalFiles = $this->db->table('digital_files');
        $this->builderFileManager = $this->db->table('images_file_manager');
    }

    //upload image
    public function uploadImage($saveInSession = false)
    {
        $uploadModel = new UploadModel();
        $response = [];
        $productId = $saveInSession ? 0 : inputPost('product_id');
        $isOptionImage = !empty(inputPost('is_option_image')) ? 1 : 0;
        $folderName = $saveInSession ? 'temp' : 'images';
        $tempFile = $uploadModel->uploadTempFile('file');
        if (!empty($tempFile) && !empty($tempFile['path'])) {
            $data = [
                'product_id' => $productId,
                'image_small' => $uploadModel->optimizeImage('resize', $tempFile['path'], $folderName, 'img_', PRODUCT_IMAGE_SMALL, null, 'product'),
                'image_default' => $uploadModel->optimizeImage('resize', $tempFile['path'], $folderName, 'img_', PRODUCT_IMAGE_DEFAULT, null, 'product'),
                'image_big' => $uploadModel->optimizeImage('resize', $tempFile['path'], $folderName, 'img_', PRODUCT_IMAGE_BIG, null, 'product'),
                'is_main' => 0,
                'is_option_image' => $isOptionImage ? 1 : 0,
                'storage' => $this->activeStorage
            ];

            $uploadModel->deleteTempFile($tempFile['path']);

            if ($saveInSession) {
                $fileId = inputPost('file_id');
                $images = $this->getSessProductImagesArray();
                if (empty($images)) {
                    $images = [];
                }
                $data['file_id'] = $fileId;
                $data['file_time'] = time();
                $item = json_decode(json_encode($data));
                array_push($images, $item);
                helperSetSession('mds_product_images', $images);
                $response = ['file_id' => $fileId];
            } else {
                $this->dbReconnect();
                if ($this->builderImages->insert($data)) {
                    $response = ['image_id' => $this->db->insertID()];
                    $this->updateProductImageCache($productId);
                    setProductAsEdited($productId);
                }
            }
        }

        return jsonResponse($response);
    }

    //add product images
    public function addProductImages($productId): bool
    {
        try {
            $uploadModel = new UploadModel();

            $images = $this->getSessProductImagesArray();
            if (empty($images)) {
                return false;
            }

            $dateFolder = $uploadModel->createUploadDirectory('images', false);
            $baseDirectory = 'uploads/images/' . $dateFolder . '/';

            foreach ($images as $image) {
                if (!is_object($image)) {
                    continue;
                }

                $storage = $image->storage ?? $this->activeStorage;

                $paths = [
                    'image_small' => $image->image_small,
                    'image_default' => $image->image_default,
                    'image_big' => $image->image_big,
                ];

                foreach ($paths as $filename) {
                    $sourcePath = FCPATH . 'uploads/temp/' . $filename;
                    $targetPath = $baseDirectory . $filename;
                    $uploadModel->moveToStorage($sourcePath, $targetPath, $storage);
                }

                $data = [
                    'product_id' => $productId,
                    'image_small' => $dateFolder . '/' . $image->image_small,
                    'image_default' => $dateFolder . '/' . $image->image_default,
                    'image_big' => $dateFolder . '/' . $image->image_big,
                    'is_main' => $image->is_main,
                    'storage' => $storage
                ];

                $this->dbReconnect();
                $this->builderImages->insert($data);
            }

            $this->updateProductImageCache($productId);

            helperDeleteSession('mds_product_images');

            return true;

        } catch (\Throwable $e) {
            log_message('error', 'addProductImages failed: ' . $e->getMessage());
        }

        return false;
    }

    //set image main session
    public function setSessImageMain($fileId)
    {
        $images = $this->getSessProductImagesArray();
        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image->file_id == $fileId) {
                    $image->is_main = 1;
                } else {
                    $image->is_main = 0;
                }
            }
        }
        helperSetSession('mds_product_images', $images);
    }

    //set image main
    public function setImageMain($imageId, $productId)
    {
        $images = $this->getProductImages($productId);
        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image->id == $imageId) {
                    $data['is_main'] = 1;
                } else {
                    $data['is_main'] = 0;
                }
                $this->builderImages->where('id', $image->id)->update($data);
            }
        }
        $this->updateProductImageCache($productId);
    }

    //get product images array session
    public function getSessProductImagesArray()
    {
        $images = array();
        if (!empty(helperGetSession('mds_product_images'))) {
            $images = helperGetSession('mds_product_images');
        }
        if (!empty($images)) {
            usort($images, function ($a, $b) {
                if ($a->file_time == $b->file_time) return 0;
                return $a->file_time < $b->file_time ? 1 : -1;
            });
        }
        return $images;
    }

    //get product images
    public function getProductImages($productId, $getOptionImages = false)
    {
        $this->builderImages->where('product_id', clrNum($productId));
        if ($getOptionImages == false) {
            $this->builderImages->where('is_option_image', 0);
        }
        return $this->builderImages->orderBy('images.is_main DESC, images.id')->get(300)->getResult();
    }

    //get product image
    public function getImage($imageId)
    {
        return $this->builderImages->where('images.id', clrNum($imageId))->get()->getRow();
    }

    //get product main image
    public function getProductMainImage($productId)
    {
        return $this->builderImages->where('product_id', clrNum($productId))->orderBy('images.is_main DESC')->get()->getRow();
    }

    //update product image cache
    public function updateProductImageCache($productId)
    {
        $productId = clrNum($productId);
        $images = $this->builderImages->select('storage, image_small')->where('product_id', $productId)->where('is_option_image', 0)
            ->orderBy('is_main', 'DESC')->orderBy('id')->limit(2)->get()->getResult();

        $array = [];
        foreach ($images as $image) {
            $array[] = [
                'image' => $image->image_small,
                'storage' => $image->storage,
            ];
        }

        $this->db->table('products')->where('id', $productId)->update(['image_cache' => json_encode($array)]);
    }

    //delete image session
    public function deleteImageSession($fileId)
    {
        $images = $this->getSessProductImagesArray();
        $imagesNew = [];

        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image->file_id == $fileId) {
                    deleteStorageFile('uploads/temp/' . $image->image_small);
                    deleteStorageFile('uploads/temp/' . $image->image_default);
                    deleteStorageFile('uploads/temp/' . $image->image_big);
                    continue;
                }

                $item = (object)[
                    'image_small' => $image->image_small,
                    'image_default' => $image->image_default,
                    'image_big' => $image->image_big,
                    'file_id' => $image->file_id,
                    'is_main' => $image->is_main,
                    'file_time' => $image->file_time
                ];

                $imagesNew[] = $item;
            }
        }

        helperSetSession('mds_product_images', $imagesNew);
    }

    //delete product image
    public function deleteProductImage($imageId)
    {
        $image = $this->getImage($imageId);
        if (!empty($image)) {
            deleteStorageFile('uploads/images/' . $image->image_small, $image->storage);
            deleteStorageFile('uploads/images/' . $image->image_default, $image->storage);
            deleteStorageFile('uploads/images/' . $image->image_big, $image->storage);
            $this->builderImages->where('id', $image->id)->delete();
            $this->updateProductImageCache($image->product_id);
            setProductAsEdited($image->product_id);
        }
    }

    //delete product images
    public function deleteProductImages($productId)
    {
        $images = $this->getProductImages($productId);
        if (!empty($images)) {
            foreach ($images as $image) {
                //do not delete if image used in orders
                $row = $this->db->table("order_items")->where("image_id", $image->id)->get()->getRow();
                if (empty($row)) {
                    $this->deleteProductImage($image->id);
                }
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * File Manager
     * --------------------------------------------------------------------
     */

    //upload image
    public function uploadFileManagerImage()
    {
        $uploadModel = new UploadModel();
        $tempFile = $uploadModel->uploadTempFile('file');
        if (!empty($tempFile) && !empty($tempFile['path'])) {
            $data = [
                'image_path' => $uploadModel->optimizeImage('resize', $tempFile['path'], 'images-file-manager', 'img_', 1280, null, 'product'),
                'storage' => $this->activeStorage,
                'user_id' => user()->id
            ];
            $this->dbReconnect();
            $this->builderFileManager->insert($data);
            $uploadModel->deleteTempFile($tempFile['path']);
        }
    }

    //get user file manager images
    public function getUserFileManagerImages($userId)
    {
        return $this->builderFileManager->where('user_id', clrNum($userId))->orderBy('id DESC')->get()->getResult();
    }

    //get file manager image
    public function getFileManagerImage($fileId)
    {
        return $this->builderFileManager->where('id', clrNum($fileId))->get()->getRow();
    }

    //delete file manager image
    public function deleteFileManagerImage($fileId, $userId)
    {
        $image = $this->getFileManagerImage($fileId);
        if (!empty($image) && $image->user_id == $userId) {
            deleteStorageFile('uploads/images-file-manager/' . $image->image_path, $image->storage);
            $this->builderFileManager->where('id', $image->id)->delete();
        }
    }

    /*
     * --------------------------------------------------------------------
     * Blog Images
     * --------------------------------------------------------------------
     */

    //upload image
    public function uploadBlogImage()
    {
        $uploadModel = new UploadModel();
        $tempFile = $uploadModel->uploadTempFile('file');
        if (!empty($tempFile) && !empty($tempFile['path'])) {
            $data = [
                'image_path' => $uploadModel->uploadBlogImage($tempFile['path'], 'big'),
                'image_path_thumb' => $uploadModel->uploadBlogImage($tempFile['path'], 'small'),
                'storage' => $this->activeStorage,
                'user_id' => user()->id
            ];
            $this->dbReconnect();
            $this->builderBlogImages->insert($data);
            $uploadModel->deleteTempFile($tempFile['path']);
        }
    }

    //get blog images
    public function getBlogImages($limit)
    {
        return $this->builderBlogImages->orderBy('id DESC')->get(clrNum($limit))->getResult();
    }

    //load more blog images
    public function loadMoreBlogImages($min, $limit)
    {
        return $this->builderBlogImages->where('id < ', clrNum($min))->orderBy('id DESC')->get(clrNum($limit))->getResult();
    }

    //get blog image
    public function getBlogImage($id)
    {
        return $this->builderBlogImages->where('id', clrNum($id))->get()->getRow();
    }

    //delete blog image
    public function deleteBlogImage($id)
    {
        $image = $this->getBlogImage($id);
        if (!empty($image)) {
            deleteStorageFile($image->image_path, $image->storage);
            deleteStorageFile($image->image_path_thumb, $image->storage);
            $this->builderBlogImages->where('id', $image->id)->delete();
        }
    }

    /*
     * --------------------------------------------------------------------
     * Digital Files
     * --------------------------------------------------------------------
     */

    //upload digital files
    public function uploadDigitalFile($productId)
    {
        $uploadModel = new UploadModel();

        $product = getProduct($productId);
        if (!empty($product)) {
            $file = $uploadModel->uploadDigitalFile('file');
            if (!empty($file) && !empty($file['path'])) {
                $data = [
                    'product_id' => $productId,
                    'user_id' => user()->id,
                    'file_name' => $file['name'],
                    'storage' => 'local',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->dbReconnect();
                $this->builderDigitalFiles->insert($data);
            }
            setProductAsEdited($product->id);
        }
    }

    //get product digital file
    public function getProductDigitalFile($productId)
    {
        return $this->builderDigitalFiles->where('product_id', clrNum($productId))->get()->getRow();
    }

    //get digital file
    public function getDigitalFile($id)
    {
        return $this->builderDigitalFiles->where('id', clrNum($id))->get()->getRow();
    }

    //create license key file
    public function createLicenseKeyFile($product, $sale, $licenseKey = null)
    {
        if (empty($product)) {
            return false;
        }

        $filename = 'license_certificate.txt';
        $dirPath = FCPATH . 'uploads/temp/';
        $filePath = $dirPath . $filename;

        if (!is_dir($dirPath) || !is_writable($dirPath)) {
            return false;
        }

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $seller = getUser($product->user_id);
        $product = $this->db->table('products')->where('id', $product->id)->get()->getRow();
        $productDetails = getProductDetails($product->id, selectedLangId());

        //prepare content with BOM
        $bom = "\xEF\xBB\xBF";
        $text = $bom . "\n" . strtoupper($this->generalSettings->application_name ?? '') . ' ' . strtoupper(trans("license_certificate")) . "\n==============================================\n\n";

        if (!empty($productDetails)) {
            $text .= trans("product") . ":\n" . $productDetails->title . "\n\n";
        }

        $text .= trans("product_url") . ":\n" . generateProductUrl($product) . "\n\n";

        if (!empty($seller)) {
            $text .= trans("seller") . ":\n" . getUsername($seller) . "\n\n";
        }

        if (!empty($sale)) {
            $buyer = getUser($sale->buyer_id);
            if (!empty($buyer)) {
                $text .= trans("buyer") . ":\n" . getUsername($buyer) . "\n\n";
            }
        }

        if (!empty($sale)) {
            $text .= trans("purchase_code") . ":\n" . $sale->purchase_code . "\n\n";
            if (!empty($sale->license_key)) {
                $text .= trans("license_key") . ":\n" . $sale->license_key . "\n\n";
            }
        } else {
            if (!empty($licenseKey)) {
                $text .= trans("license_key") . ":\n" . $licenseKey . "\n\n";
            }
        }

        //try writing with file_put_contents
        $writeSuccess = @file_put_contents($filePath, $text);

        //fallback to fopen if needed
        if ($writeSuccess === false) {
            $handle = @fopen($filePath, 'w');
            if ($handle) {
                fwrite($handle, $text);
                fclose($handle);
            } else {
                return false;
            }
        }

        //force download with proper headers
        if (file_exists($filePath)) {
            header('Content-Type: text/plain; charset=UTF-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);

            @unlink($filePath);
            exit();
        }
        return false;
    }

    //delete digital file
    public function deleteDigitalFile($fileId)
    {
        $digitalFile = $this->getDigitalFile($fileId);
        if (!empty($digitalFile)) {
            if (($digitalFile->user_id == user()->id) || hasPermission('products')) {
                deleteStorageFile('uploads/digital-files/' . $digitalFile->file_name);
                setProductAsEdited($digitalFile->product_id);
                return $this->builderDigitalFiles->where('id', $digitalFile->id)->delete();
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Videos
     * --------------------------------------------------------------------
     */

    //upload video
    public function uploadVideo($productId)
    {
        $uploadModel = new UploadModel();

        $file = $uploadModel->uploadVideo('file');
        if (!empty($file)) {
            $data = [
                'product_id' => $productId,
                'media_type' => 'video',
                'file_name' => $file['name'],
                'storage' => $file['storage']
            ];
            $this->dbReconnect();
            $this->builderMedia->insert($data);
            setProductAsEdited($productId);
        }
    }

    //get product video
    public function getProductVideo($productId)
    {
        return $this->builderMedia->where('product_id', clrNum($productId))->where('media_type', 'video')->get()->getRow();
    }

    //delete video
    public function deleteVideo($productId)
    {
        $video = $this->getProductVideo($productId);
        if (!empty($video)) {
            deleteStorageFile('uploads/videos/' . $video->file_name, $video->storage);
            setProductAsEdited($productId);
            return $this->builderMedia->where('id', $video->id)->delete();
        }
    }

    /*
     * --------------------------------------------------------------------
     * Audios
     * --------------------------------------------------------------------
     */

    //upload audio
    public function uploadAudio($productId)
    {
        $uploadModel = new UploadModel();

        $file = $uploadModel->uploadAudio('file');
        if (!empty($file)) {
            $data = [
                'product_id' => $productId,
                'media_type' => 'audio',
                'file_name' => $file['name'],
                'storage' => $file['storage']
            ];
            $this->dbReconnect();
            $this->builderMedia->insert($data);
            setProductAsEdited($productId);
        }
    }

    //get product audio
    public function getProductAudio($productId)
    {
        return $this->builderMedia->where('product_id', clrNum($productId))->where('media_type', 'audio')->get()->getRow();
    }

    //delete audio
    public function deleteAudio($productId)
    {
        $audio = $this->getProductAudio($productId);
        if (!empty($audio)) {
            deleteStorageFile('uploads/audios/' . $audio->file_name, $audio->storage);
            setProductAsEdited($productId);
            return $this->builderMedia->where('id', $audio->id)->delete();
        }
    }

    /*
     * --------------------------------------------------------------------
     * Support Attachments
     * --------------------------------------------------------------------
     */

    //upload attachment
    public function uploadAttachment($ticketType)
    {
        $uploadModel = new UploadModel();

        $tempFile = $uploadModel->uploadTempFile('file', false, getAppDefault('safeExtensions'));
        if (empty($tempFile['path'])) {
            return false;
        }

        $attachment = (object)[
            'fileId' => uniqid(),
            'name' => $tempFile['orjName'] ?? 'file',
            'tempPath' => $tempFile['path'],
            'ticketType' => $ticketType
        ];

        $attachments = helperGetSession('ticket_attachments') ?? [];
        $attachments[] = $attachment;

        helperSetSession('ticket_attachments', $attachments);
        return true;
    }

    //delete attachment
    public function deleteAttachment($id)
    {
        $filesSessionNew = array();
        $ticketAttachments = helperGetSession('ticket_attachments');
        if (!empty($ticketAttachments)) {
            foreach ($ticketAttachments as $item) {
                if ($item->fileId == $id) {
                    @unlink($item->tempPath);
                } else {
                    array_push($filesSessionNew, $item);
                }
            }
        }
        helperSetSession('ticket_attachments', $filesSessionNew);
    }

    //reconnect to database
    public function dbReconnect()
    {
        try {
            $this->db->query("SELECT 1");
        } catch (\Exception $e) {
            $this->db->reconnect();
        }
    }
}
