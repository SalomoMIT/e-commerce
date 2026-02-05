<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\FieldModel;
use App\Models\ProductAdminModel;

class BulkController extends BaseController
{

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Category Upload
     */
    public function categoryUpload()
    {
        checkPermission('categories');
        $data['title'] = trans("bulk_category_upload");

        echo view('admin/includes/_header', $data);
        echo view('admin/category/buld_category_upload', $data);
        echo view('admin/includes/_footer');
    }


    /**
     * Custom Field Upload
     */
    public function bulkCustomFieldUpload()
    {
        checkPermission('custom_fields');
        $data['title'] = trans("bulk_custom_field_upload");

        echo view('admin/includes/_header', $data);
        echo view('admin/category/bulk_custom_field_upload', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Download CSV Files Post
     */
    public function downloadCsvFilesPost()
    {
        $submit = inputPost('submit');
        $type = inputPost('type');
        $allowedTypes = ['product', 'category', 'custom_field'];

        if ($submit != 'csv_template' && $submit != 'csv_example') {
            $submit = 'csv_template';
        }

        if (!in_array($type, $allowedTypes)) {
            redirectToBackURL();
        }

        if ($type == 'product') {
            if (!isVendor() && !hasPermission('products')) {
                redirectToBackURL();
            }
        } elseif ($type == 'category') {
            checkPermission('categories');
        } elseif ($type == 'custom_field') {
            checkPermission('custom_fields');
        }

        $fileName = '';
        if ($submit == 'csv_template') {
            $fileName = "csv_{$type}_template.csv";
        } else {
            $fileName = "csv_{$type}_example.csv";
        }

        $filePath = FCPATH . 'assets/file/' . $fileName;

        if (file_exists($filePath)) {
            return $this->response->download($filePath, null);
        }

        redirectToBackURL();
    }

    /**
     * Upload CSV File Post
     */
    public function uploadCsvFilePost()
    {
        $data = [];
        $file = $this->request->getFile('file');
        if (!$file->isValid() || $file->getClientExtension() !== 'csv') {
            $data = ['result' => 0, 'message' => 'Invalid file'];
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/temp', $newName);
        $data = ['result' => 1, 'file_name' => $newName];

        $data['csrfToken'] = csrf_hash();
        return jsonResponse($data);
    }

    /**
     * Process CSV
     */
    public function processCsvChunk()
    {
        $fileName = inputPost('file_name');
        $start = clrNum(inputPost('start'));
        $limit = clrNum(inputPost('limit'));
        $dataType = inputPost('data_type');

        $filePath = FCPATH . 'uploads/temp/' . $fileName;
        if (!file_exists($filePath)) {
            return jsonResponse(['success' => false, 'message' => 'File not found.']);
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return jsonResponse(['success' => false, 'message' => 'Cannot open file.']);
        }

        $header = null;
        $currentLine = 0;
        $processed = 0;

        if ($dataType === 'category') {
            $model = new CategoryModel();
        } elseif ($dataType === 'custom_field') {
            $model = new FieldModel();
        } elseif ($dataType === 'product') {
            $model = new ProductAdminModel();
        } else {
            fclose($handle);
            return jsonResponse(['success' => false, 'message' => 'Invalid data type.']);
        }

        while (($data = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
            if ($currentLine === 0) {
                $header = array_map('trim', $data);
                $currentLine++;
                continue;
            }

            if ($currentLine >= $start + 1 && $currentLine < $start + 1 + $limit) {
                // Skip empty lines or mismatched columns
                if (empty(array_filter($data)) || count($data) !== count($header)) {
                    $currentLine++;
                    continue;
                }

                $assoc = array_combine($header, $data);
                if ($assoc) {

                    if ($dataType == 'product' && inputPost('bulk_action') == 'edit_products') {
                        $model->updateCSVItem($assoc);
                    } else {
                        $model->insertCSVItem($assoc);
                    }

                    $processed++;
                }
            }

            if ($currentLine >= $start + 1 + $limit) {
                break;
            }

            $currentLine++;
        }

        fclose($handle);

        $totalLines = $this->countCsvLines($filePath);
        $totalRows = max(0, $totalLines - 1); // exclude header

        // If all rows are processed, delete the file
        if ($start + $limit >= $totalRows) {
            if ($dataType == 'category') {
                $model->rebuildCategoryPaths();
            }
            @unlink($filePath);
        }

        resetCacheData(true);

        return jsonResponse([
            'success' => true,
            'total' => $totalRows,
            'processed' => $processed
        ]);
    }

    //count csv lines
    private function countCsvLines($filePath)
    {
        $linecount = 0;
        $handle = fopen($filePath, 'r');
        while (!feof($handle)) {
            fgets($handle);
            $linecount++;
        }
        fclose($handle);
        return $linecount;
    }

}
