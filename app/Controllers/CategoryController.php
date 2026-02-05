<?php

namespace App\Controllers;

use App\Models\FieldModel;
use App\Models\TagModel;

class CategoryController extends BaseAdminController
{
    protected $fieldModel;
    protected $tagModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $panelSettings = getPanelSettings();

        $this->fieldModel = new FieldModel();
        $this->tagModel = new TagModel();
    }

    /**
     * Categories
     */
    public function categories()
    {
        checkPermission('categories');
        $data['title'] = trans("categories");
        $langId = selectedLangId();
        $data['lang'] = $langId;

        $data['categories'] = $this->categoryModel->getParentCategories(false);
        $q = cleanStr(inputGet('q'));
        if (!empty($q)) {
            $data['searchMode'] = true;
            $numRows = $this->categoryModel->getCategoriesSearchCount();
            $data['pager'] = paginate($this->perPage, $numRows);
            $data['categories'] = $this->categoryModel->getCategoriesSearchPaginated($this->perPage, $data['pager']->offset);
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/category/categories', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Category
     */
    public function addCategory()
    {
        checkPermission('categories');
        $data['title'] = trans("add_category");
        $data['parentCategories'] = $this->categoryModel->getParentCategories();

        echo view('admin/includes/_header', $data);
        echo view('admin/category/add_category', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Category Post
     */
    public function addCategoryPost()
    {
        checkPermission('categories');
        if ($this->categoryModel->addCategory()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Edit Category
     */
    public function editCategory($id)
    {
        checkPermission('categories');
        $data['title'] = trans("update_category");
        $data['category'] = $this->categoryModel->getCategory($id);
        if (empty($data['category'])) {
            return redirect()->to(adminUrl('categories'));
        }

        $data['categoryDetails'] = [];
        foreach ($this->activeLanguages as $language) {
            $data['categoryDetails'][$language->id] = getCategoryDetails($data['category']->id, $language->id);
        }

        echo view('admin/includes/_header', $data);
        echo view('admin/category/edit_category', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Update Category Post
     */
    public function editCategoryPost()
    {
        checkPermission('categories');
        $id = inputPost('id');
        if ($this->categoryModel->editCategory($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Category Settings Post
     */
    public function categorySettingsPost()
    {
        checkPermission('categories');

        $submit = inputPost('submit');

        if ($submit == 'buildPaths') {
            $this->categoryModel->rebuildCategoryPaths();
            setSuccessMessage(trans("msg_updated"));
        } else {
            if ($this->categoryModel->updateSettings()) {
                setSuccessMessage(trans("msg_updated"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }

        redirectToBackUrl();
    }

    /**
     * Delete Category Post
     */
    public function deleteCategoryPost()
    {
        checkPermission('categories');
        $id = inputPost('id');
        if (!empty($this->categoryModel->getSubCategoriesByParentId($id))) {
            setErrorMessage(trans("msg_delete_subcategories"));
        } else {
            if ($this->categoryModel->deleteCategory($id)) {
                setSuccessMessage(trans("msg_deleted"));
            } else {
                setErrorMessage(trans("msg_error"));
            }
        }
    }

    /**
     * Edit featured categories order
     */
    public function editFeaturedCategoriesOrderPost()
    {
        checkPermission('categories');
        $this->categoryModel->editFeaturedCategoriesOrder();
        return jsonResponse();
    }

    /**
     * Edit index categories order
     */
    public function editIndexCategoriesOrderPost()
    {
        checkPermission('categories');
        $this->categoryModel->editIndexCategoriesOrder();
        return jsonResponse();
    }

    /**
     * Load categories
     */
    public function loadCategories()
    {
        checkPermission('categories');
        $data = [
            'result' => 0
        ];
        $subCategories = $this->categoryModel->getSubCategoriesByParentId(inputPost('id'));
        if (!empty($subCategories)) {
            $data = [
                'result' => 1,
                'htmlContent' => view('admin/category/_load_categories', ['categories' => $subCategories, 'padding' => true])
            ];
        }
        return jsonResponse($data);
    }

    /**
     * Delete category image
     */
    public function deleteCategoryImagePost()
    {
        checkPermission('categories');
        $categoryId = inputPost('category_id');
        $this->categoryModel->deleteCategoryImage($categoryId);
        return jsonResponse();
    }

    /*
     * --------------------------------------------------------------------
     * Custom Fields
     * --------------------------------------------------------------------
     */

    /**
     * Custom Fields
     */
    public function customFields()
    {
        checkPermission('custom_fields');
        $data['title'] = trans("custom_fields");

        $numRows = $this->fieldModel->getFieldCount($this->activeLang->id);
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['fields'] = $this->fieldModel->getFieldsPaginated($this->perPage, $data['pager']->offset, $this->activeLang->id);

        echo view('admin/includes/_header', $data);
        echo view('admin/category/custom_fields', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Custom Field
     */
    public function addCustomField()
    {
        checkPermission('custom_fields');
        $data['title'] = trans("add_custom_field");
        $data['categories'] = $this->categoryModel->getParentCategories();

        echo view('admin/includes/_header', $data);
        echo view('admin/category/add_custom_field', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Custom Field Post
     */
    public function addCustomFieldPost()
    {
        checkPermission('custom_fields');
        $insertId = $this->fieldModel->addField();
        if ($insertId) {
            return redirect()->to(adminUrl('custom-field-options/' . $insertId));
        }
        setErrorMessage(trans("msg_error"));
        return redirect()->back()->withInput();
    }

    /**
     * Edit Custom Field
     */
    public function editCustomField($id)
    {
        checkPermission('custom_fields');
        $data['title'] = trans("update_custom_field");
        $data['field'] = $this->fieldModel->getField($id);
        if (empty($data['field'])) {
            return redirect()->to(adminUrl('custom-fields'));
        }
        $data['categories'] = $this->categoryModel->getParentCategories();
        $data['fieldCategories'] = $this->fieldModel->getFieldCategories($data['field']->id);
        $data['fieldNamesArray'] = $this->fieldModel->getFieldNamesArray($data['field']->id);

        echo view('admin/includes/_header', $data);
        echo view('admin/category/edit_custom_field', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Update Custom Field Post
     */
    public function editCustomFieldPost()
    {
        checkPermission('custom_fields');
        $id = inputPost('id');
        if ($this->fieldModel->editField($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Custom Field Post
     */
    public function deleteCustomFieldPost()
    {
        checkPermission('custom_fields');
        $id = inputPost('id');
        if ($this->fieldModel->deleteField($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
    }

    /**
     * Add Remove Custom Fields Filters
     */
    public function addRemoveCustomFieldFiltersPost()
    {
        checkPermission('custom_fields');
        $id = inputPost('id');
        if ($this->fieldModel->toggleProductFilterStatus($id)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Custom Field Options
     */
    public function customFieldOptions($id)
    {
        checkPermission('custom_fields');
        $data['title'] = trans("add_custom_field");
        $data['field'] = $this->fieldModel->getField($id);
        if (empty($data['field'])) {
            return redirect()->to(adminUrl('custom-fields'));
        }
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['options'] = $this->fieldModel->getFieldAllOptions($id);
        $data['fieldCategories'] = $this->fieldModel->getFieldCategories($id);
        $data['optionsNameArray'] = $this->fieldModel->getFieldOptionsNameArray($id);

        echo view('admin/includes/_header', $data);
        echo view('admin/category/custom_field_options', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add custom field option
     */
    public function addCustomFieldOptionPost()
    {
        checkPermission('custom_fields');
        $fieldId = inputPost('field_id');
        $this->fieldModel->addFieldOption($fieldId);
        redirectToBackUrl();
    }

    /**
     * Update Custom Field Option Post
     */
    public function editCustomFieldOptionPost()
    {
        checkPermission('custom_fields');
        $this->fieldModel->editFieldOption();
        return jsonResponse();
    }

    /**
     * Delete custom field option
     */
    public function deleteCustomFieldOption()
    {
        checkPermission('custom_fields');
        $id = inputPost('id');
        $this->fieldModel->deleteCustomFieldOption($id);
        return jsonResponse();
    }

    /**
     * Add category to custom field
     */
    public function addCategoryToCustomField()
    {
        checkPermission('custom_fields');
        $this->fieldModel->addCategoryToField();
        redirectToBackUrl();
    }

    /**
     * Custom Field Settings Post
     */
    public function customFieldSettingsPost()
    {
        checkPermission('custom_fields');
        if ($this->fieldModel->updateFieldOptionsSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete category from a custom field
     */
    public function deleteCategoryFromField()
    {
        checkPermission('custom_fields');
        $fieldId = inputPost('field_id');
        $categoryId = inputPost('category_id');
        $this->fieldModel->deleteCategoryFromField($fieldId, $categoryId);
        return jsonResponse();
    }


    /**
     * --------------------------------------------------------------------------
     * Brands
     * --------------------------------------------------------------------------
     */

    /**
     * Brands
     */
    public function brands()
    {
        checkPermission('brands');
        $data['title'] = trans("brands");
        $data['userSession'] = getUserSession();

        $q = removeForbiddenCharacters(inputGet('q'));
        $numRows = $this->commonModel->getBrandsCount($q);
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['brands'] = $this->commonModel->getBrandsPaginated($this->activeLang->id, $this->perPage, $data['pager']->offset, $q);

        echo view('admin/includes/_header', $data);
        echo view('admin/brand/brands', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Brand
     */
    public function addBrand()
    {
        checkPermission('brands');
        $data['title'] = trans("add_brand");
        $data['userSession'] = getUserSession();
        $data['parentCategories'] = $this->categoryModel->getParentCategories();

        echo view('admin/includes/_header', $data);
        echo view('admin/brand/add', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Edit Brand
     */
    public function editBrand($id)
    {
        checkPermission('brands');
        $data['title'] = trans("edit_brand");
        $data['userSession'] = getUserSession();

        $data['brand'] = $this->commonModel->getBrand($id);
        if (empty($data['brand'])) {
            redirectToUrl(adminUrl('brands'));
        }
        $data['parentCategories'] = $this->categoryModel->getParentCategories();
        $data['brandNamesArray'] = $this->commonModel->getBrandNameLanguageArray($data['brand']->id);
        $data['brandCategoryIdsArray'] = $this->commonModel->getBrandCategoryIdsArray($data['brand']->id);

        echo view('admin/includes/_header', $data);
        echo view('admin/brand/edit', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Brand Post
     */
    public function addBrandPost()
    {
        checkPermission('brands');
        if ($this->commonModel->addBrand()) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('add-brand'));
    }

    /**
     * Edit Brand Post
     */
    public function editBrandPost()
    {
        checkPermission('brands');
        if ($this->commonModel->editBrand()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Brand Post
     */
    public function deleteBrandPost()
    {
        checkPermission('brands');
        $id = inputPost('id');
        if ($this->commonModel->deleteBrand($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        exit();
    }

    /**
     * Brand Settings Post
     */
    public function brandSettingsPost()
    {
        checkPermission('brands');
        if ($this->commonModel->updateBrandSettings()) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        return redirect()->to(adminUrl('brands'));
    }

    /**
     * --------------------------------------------------------------------------
     * Tags
     * --------------------------------------------------------------------------
     */

    /**
     * Tags
     */
    public function tags()
    {
        checkPermission('tags');
        $data['title'] = trans("tags");
        $numRows = $this->tagModel->getTagsCount();
        $data['pager'] = paginate($this->perPage, $numRows);
        $data['tags'] = $this->tagModel->getTagsPaginated($this->perPage, $data['pager']->offset);

        echo view('admin/includes/_header', $data);
        echo view('admin/category/tags', $data);
        echo view('admin/includes/_footer');
    }

    /**
     * Add Tag Post
     */
    public function addTagPost()
    {
        checkPermission('tags');
        $tag = inputPost('tag');
        $langId = inputPost('lang_id');
        if ($this->tagModel->addOrGetTag($tag, $langId, false)) {
            setSuccessMessage(trans("msg_added"));
        } else {
            setErrorMessage(trans("msg_tag_exists"));
        }
        redirectToBackURL();
    }

    /**
     * Edit Tag Post
     */
    public function editTagPost()
    {
        checkPermission('tags');
        $id = inputPost('id');
        $tag = inputPost('tag');
        $langId = inputPost('lang_id');
        if ($this->tagModel->editTag($id, $tag, $langId)) {
            setSuccessMessage(trans("msg_updated"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackURL();
    }

    /**
     * Delete Tag Post
     */
    public function deleteTagPost()
    {
        checkPermission('tags');
        $id = inputPost('id');
        if ($this->tagModel->deleteTag($id)) {
            setSuccessMessage(trans("msg_deleted"));
        } else {
            setErrorMessage(trans("msg_error"));
        }
        exit();
    }
}