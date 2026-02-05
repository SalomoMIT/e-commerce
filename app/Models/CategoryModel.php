<?php namespace App\Models;

class CategoryModel extends BaseModel
{
    protected $builder;
    protected $builderPaths;
    protected $builderLang;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('categories');
        $this->builderPaths = $this->db->table('category_paths');
        $this->builderLang = $this->db->table('category_lang');
    }

    //build query
    public function buildQuery($langId = null, $sortResults = true, $searchTerm = null)
    {
        $langId = clrNum($langId ?? $this->activeLang->id);
        $defaultLangId = clrNum($this->defaultLang->id);

        $this->builder->resetQuery();

        $this->builder->select('categories.*, categories.parent_id AS join_parent_id');

        $compiledSubQuery = $this->db->table('category_lang')->select('name')->where('category_lang.category_id = categories.id')->whereIn('lang_id', $langId != $defaultLangId ? [$langId, $defaultLangId] : [$langId])
            ->when($langId != $defaultLangId, fn($qb) => $qb->orderBy("lang_id = $langId", 'DESC', false))->limit(1)->getCompiledSelect(false);

        $this->builder->select("($compiledSubQuery) AS cat_name")
            ->select('(SELECT slug FROM categories WHERE id = join_parent_id) AS parent_slug')
            ->select('(SELECT id FROM categories AS sub_categories WHERE sub_categories.parent_id = categories.id LIMIT 1) AS has_subcategory');
        if ($sortResults) {
            $this->builder->orderBy($this->getOrderBy());
        }
    }

    //render category menu
    public function renderCategoryMenu($langId, $parentCategories)
    {
        $selectedNav = $this->generalSettings->selected_navigation == 2 ? 'nav_large' : 'nav';

        return getCacheData('menu_categories_html_' . $selectedNav . '_lang_' . $langId, function () use ($langId, $parentCategories, $selectedNav) {

            $menuCategories = $this->getMenuCategoryTree($langId, $parentCategories);

            $rawHtml = $selectedNav === 'nav_large'
                ? view("nav/_nav_main_large", ['menuCategories' => $menuCategories])
                : view("nav/_nav_main", ['menuCategories' => $menuCategories]);

            return minifyHtmlOutput($rawHtml);

        }, 'category');
    }

    //get menu category tree
    private function getMenuCategoryTree($langId, $parentCategories)
    {
        $parentCategoriesMenu = array_filter($parentCategories, function ($category) {
            return isset($category->show_on_main_menu) && $category->show_on_main_menu == 1;
        });

        if (empty($parentCategoriesMenu)) {
            return [];
        }
        $parentIds = array_column($parentCategoriesMenu, 'id');

        $childIds = [];
        $childCategories = $this->getMenuChildCategories($langId, $parentIds, LIMIT_NAV_LEVEL2);
        if (!empty($childCategories)) {
            $childIds = array_column($childCategories, 'id');
        }

        $subChildCategories = $this->getMenuChildCategories($langId, $childIds, LIMIT_NAV_LEVEL3);

        $tree = [];
        foreach ($parentCategoriesMenu as $parent) {
            $parent->children = [];
            $parent->childrenWithImage = [];
            foreach ($childCategories as $child) {
                if (!empty($child->parent_id) && $child->parent_id == $parent->id) {
                    $child->children = [];
                    if (!empty($child->image) && $child->show_image_on_main_menu) {
                        $parent->childrenWithImage[] = $child;
                    }
                    foreach ($subChildCategories as $subChild) {
                        if (!empty($subChild->parent_id) && $subChild->parent_id == $child->id) {
                            $child->children[] = $subChild;
                            if (!empty($subChild->image) && $subChild->show_image_on_main_menu) {
                                $parent->childrenWithImage[] = $subChild;
                            }
                        }
                    }
                    $parent->children[] = $child;
                }
            }
            $tree[] = $parent;
        }

        return $tree;
    }

    //get menu child categories
    public function getMenuChildCategories($langId, $parentIds, $limit = 10)
    {
        if (empty($parentIds)) {
            return [];
        }

        $strArray = implodeSafeIds($parentIds);
        if (empty($strArray)) {
            return [];
        }

        $langId = clrNum($langId);
        $limit = clrNum($limit);

        $sortField = $this->getOrderBy('c');
        if ($this->generalSettings->sort_categories == 'alphabetically') {
            $sortField = "(SELECT name FROM category_lang WHERE category_id = c.id AND lang_id = {$langId} LIMIT 1)";
        }
        $sql = "SELECT id FROM 
              ( SELECT c.id, c.slug, c.parent_id,
                @row_number := IF(@current_parent = c.parent_id, @row_number + 1, 1) AS rn,
                @current_parent := c.parent_id
                FROM categories c
                JOIN (SELECT @current_parent := NULL, @row_number := 0) AS vars
                WHERE c.status = 1 AND c.show_on_main_menu = 1 AND c.parent_id IN (" . $strArray . ")
                ORDER BY c.parent_id ASC, {$sortField}
             ) AS ranked_categories WHERE rn <= {$limit}";
        $this->buildQuery($langId, false);
        return $this->builder->where('status', 1)->where('id IN (' . $sql . ')')->get()->getResult();
    }

    //get parent categories
    public function getParentCategories($onlyActive = true)
    {
        $this->buildQuery();
        if ($onlyActive) {
            $this->builder->where('status', 1);
        }
        return $this->builder->where('parent_id', 0)->get()->getResult();
    }

    //get subcategories by parent id
    public function getSubCategoriesByParentId($parentId)
    {
        $this->buildQuery();
        return $this->builder->where('categories.parent_id', clrNum($parentId))->get()->getResult();
    }

    //get category
    public function getCategory($id)
    {
        $this->buildQuery(null, false);
        return $this->builder->where('categories.id', clrNum($id))->get()->getRow();
    }

    //get category details
    public function getCategoryDetails($categoryId, $langId)
    {
        return $this->builderLang->where('category_id', clrNum($categoryId))->where('lang_id', clrNum($langId))->get()->getRow();
    }

    //get category by slug
    public function getCategoryBySlug($slug, $type = 'all')
    {
        $this->buildQuery(null, false);
        if ($type == 'parent') {
            $this->builder->where('parent_id', 0);
        } elseif ($type == 'sub') {
            $this->builder->where('parent_id != ', 0);
        }
        return $this->builder->where('status', 1)->where('categories.slug', cleanStr($slug))->get()->getRow();
    }

    //get featured categories
    public function getFeaturedCategories($langId)
    {
        return getCacheData('featured_categories_' . $langId, function () use ($langId) {
            $this->buildQuery($langId, false);
            return $this->builder->where('status', 1)->where('is_featured', 1)->orderBy('featured_order')->get()->getResult();
        }, 'category');
    }

    //get index categories
    public function getIndexCategories($langId)
    {
        return getCacheData('index_categories_' . $langId, function () use ($langId) {
            $this->buildQuery($langId, false);
            return $this->builder->where('status', 1)->where('show_products_on_index', 1)->orderBy('homepage_order')->get()->getResult();
        }, 'category');
    }

    //get parent categories tree ids
    public function getCategoryParentTree($categoryId, $onlyActive = true)
    {
        $categoryId = clrNum($categoryId);
        if (empty($categoryId) || !is_numeric($categoryId)) {
            return [];
        }

        $this->buildQuery(null, false);
        if ($onlyActive) {
            $this->builder->where('status', 1);
        }
        return $this->builder->join('category_paths', 'category_paths.ancestor_id = categories.id')->where('category_paths.descendant_id', $categoryId)->where('status', 1)
            ->orderBy('category_paths.depth DESC')->get()->getResult();
    }

    //get subcategories tree ids
    public function getSubCategoriesTreeIds($categoryId)
    {
        $categoryId = clrNum($categoryId);
        if (empty($categoryId)) {
            return [];
        }

        $results = $this->db->table('category_paths')->select('descendant_id')->where('ancestor_id', $categoryId)->get()->getResultArray();
        if (empty($results)) {
            return [];
        }
        return array_column($results, 'descendant_id');
    }

    //get categories by id array
    public function getCategoriesByIdArray($array)
    {
        if (empty($array) || !is_array($array)) {
            return [];
        }

        $ids = array_filter(array_map('clrNum', $array), function ($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            return [];
        }

        $this->buildQuery();
        return $this->builder->whereIn('categories.id', $ids)->get()->getResult();
    }

    //get seller categories
    public function getSellerCategories($sellerId, $parentId = 0)
    {
        if (empty($sellerId)) {
            return [];
        }
        if (empty($parentId)) {
            $parentId = 0;
        }

        $productCategories = getCacheData('product_categories_seller_' . $sellerId, function () use ($sellerId) {
            return $this->db->table('products')->distinct()->select('category_id')->where('user_id', $sellerId)->where('is_active', 1)->get()->getResultArray();
        }, 'product');

        if (empty($productCategories)) {
            return [];
        }

        $productCategoryIds = array_column($productCategories, 'category_id');

        $ancestorCategories = $this->builderPaths->distinct()->select('ancestor_id')->whereIn('descendant_id', $productCategoryIds)->get()->getResultArray();
        if (empty($ancestorCategories)) {
            return [];
        }

        $this->buildQuery();
        $allCategoryIds = array_column($ancestorCategories, 'ancestor_id');
        return $this->builder->whereIn('id', $allCategoryIds)->groupStart()->where('parent_id', $parentId)->orWhere('id', $parentId)->groupEnd()->orderBy('category_order', 'ASC')->get()->getResult();
    }

    public function getSellerCategoriesResultArray($sellerId)
    {
        $this->buildQuery(null, false);
        $subQuery = $this->db->table('products')->select('category_id')->where('is_active', 1)->where('user_id', clrNum($sellerId));
        return $this->builder->whereIn('categories.id', $subQuery)->get()->getResultArray();
    }

    public function getOrderBy($tableAlias = null)
    {
        $sort = $this->generalSettings->sort_categories ?? 'default';
        $prefix = $tableAlias ? "$tableAlias." : "";
        switch ($sort) {
            case 'date':
                return "{$prefix}created_at ASC";
            case 'date_desc':
                return "{$prefix}created_at DESC";
            case 'alphabetically':
                return "{$prefix}cat_name ASC";
            default:
                return "{$prefix}category_order ASC, {$prefix}id ASC";
        }
    }

    //get all cached data for category page
    public function getCachedCategoryPageData($langId, $category = null)
    {
        if (!empty($category)) {
            $cacheKey = 'category_page_data_' . $langId . '_cat_' . $category->id;

            return getCacheData($cacheKey, function () use ($langId, $category) {
                $parentCategoriesTree = $this->getCategoryParentTree($category->id);
                $data = [
                    'parentCategory' => $category->parent_id != 0 ? $this->getCategory($category->parent_id) : null,
                    'parentCategoriesTree' => $parentCategoriesTree,
                    'categoryDetails' => $this->getCategoryDetails($category->id, $langId),
                    'categories' => $this->getSubCategoriesByParentId($category->id),
                ];

                $fieldModel = new FieldModel();
                $data['customFilters'] = $fieldModel->getCustomFilters($category->id, $langId, $parentCategoriesTree, true);

                return $data;

            }, 'category');

        } else {

            $cacheKey = 'category_page_data_' . $langId;
            return getCacheData($cacheKey, function () use ($langId) {

                $fieldModel = new FieldModel();
                $data = [
                    'customFilters' => $fieldModel->getCustomFilters(null, $langId, null, true)
                ];
                return $data;

            }, 'category');
        }
    }

    /*
     * --------------------------------------------------------------------
     * Back-End
     * --------------------------------------------------------------------
     */

    //set input data
    public function setInputData()
    {
        $data = [
            'slug' => inputPost('slug'),
            'category_order' => inputPost('category_order'),
            'status' => !empty(inputPost('status')) ? 1 : 0,
            'show_on_main_menu' => !empty(inputPost('show_on_main_menu')) ? 1 : 0,
            'show_image_on_main_menu' => !empty(inputPost('show_image_on_main_menu')) ? 1 : 0,
            'show_description' => !empty(inputPost('show_description')) ? 1 : 0
        ];

        if (empty($data['slug'])) {
            $name = inputPost('name_' . $this->generalSettings->site_lang);
            $data['slug'] = generateSlug($data['slug'], $name);
        }

        $categoryIdsArray = inputPost('category_id');
        $data['parent_id'] = !empty($categoryIdsArray) && !empty($filteredArray = array_filter((array)$categoryIdsArray)) ? end($filteredArray) : 0;

        // set commission
        $data = setCommissionFormValues($data);

        return $data;
    }

    //add category
    public function addCategory()
    {
        $data = $this->setInputData();
        $uploadModel = new UploadModel();
        $tempFile = $uploadModel->uploadTempFile('file');

        if (!empty($tempFile) && !empty($tempFile['path'])) {
            $data['image'] = $uploadModel->uploadCategoryImage($tempFile['path']);
            $data['storage'] = $this->activeStorage;
            $uploadModel->deleteTempFile($tempFile['path']);
        }

        $data['featured_order'] = 1;
        $data['is_featured'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');

        // Start the database transaction.
        $this->db->transStart();

        // Perform all related database write operations.
        $this->builder->insert($data);

        $categoryId = $this->db->insertID();

        $this->updateSlug($categoryId);
        $this->addEditCategoryDetails($categoryId);
        $this->insertCategoryPaths($categoryId, $data['parent_id']);

        $this->db->transComplete();

        // Check the final status of the transaction and clean up if necessary.
        if ($this->db->transStatus() === false) {
            return false;
        }

        return true;
    }

    //edit category
    public function editCategory(int $id)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            return false;
        }

        $data = $this->setInputData();
        $uploadModel = new UploadModel();
        $tempFile = $uploadModel->uploadTempFile('file');

        if (!empty($tempFile) && !empty($tempFile['path'])) {
            $data['image'] = $uploadModel->uploadCategoryImage($tempFile['path']);
            $data['storage'] = $this->activeStorage;
            $uploadModel->deleteTempFile($tempFile['path']);
            $this->deleteCategoryImage($category->id);
        }

        // Prevent circular references.
        // A category cannot be moved to be a child of one of its own descendants.
        $newParentId = $data['parent_id'];
        if ($category->parent_id != $newParentId) {
            if ($this->isCategoryDescendant($id, $newParentId)) {
                return false;
            }
        }

        // Start the database transaction.
        $this->db->transStart();

        // Update the main category data.
        $this->builder->where('id', $category->id)->update($data);
        $this->updateSlug($category->id);
        $this->addEditCategoryDetails($category->id);

        // If the parent_id has changed, we need to rebuild the paths for the entire subtree.
        if ($category->parent_id != $newParentId) {
            $this->updateCategoryPaths($category->id, $newParentId);
        }

        $this->db->transComplete();

        // Check transaction status.
        if ($this->db->transStatus() === false) {
            return false;
        }

        return true;
    }

    //update slug
    public function updateSlug($id)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            return;
        }

        $slug = cleanStr($category->slug);
        $categoryId = clrNum($category->id);

        if (empty($slug) || $slug === '-') {
            $slug = (string)$categoryId;
        }

        $exists = $this->builder->where('slug', $slug)->where('id !=', $categoryId)->countAllResults(true);
        if ($exists > 0) {
            $slug = $slug . '-' . $categoryId;
        }
        $this->builder->where('id', $categoryId)->update(['slug' => $slug]);
    }

    //add edit category name
    public function addEditCategoryDetails($categoryId)
    {
        $categoryId = clrNum($categoryId);
        foreach ($this->activeLanguages as $language) {
            $data = [
                'name' => inputPost('name_' . $language->id),
                'meta_title' => inputPost('meta_title_' . $language->id),
                'meta_description' => inputPost('meta_description_' . $language->id),
                'meta_keywords' => inputPost('meta_keywords_' . $language->id)
            ];

            $exists = $this->builderLang->where('category_id', $categoryId)->where('lang_id', $language->id)->countAllResults(true);
            if ($exists > 0) {
                $this->builderLang->where('category_id', $categoryId)->where('lang_id', $language->id)->update($data);
            } else {
                $data['category_id'] = $categoryId;
                $data['lang_id'] = $language->id;
                $this->builderLang->insert($data);
            }
        }
    }

    //get search categories count
    public function getCategoriesSearchCount()
    {
        $q = cleanStr(inputGet('q'));
        $this->buildQuery(null, false);
        if (!empty($q)) {
            $this->builder->join('category_lang', 'category_lang.category_id = categories.id')->where('category_lang.lang_id', clrNum($this->activeLang->id))
                ->like('category_lang.name', $q, 'after');
        }

        return $this->builder->countAllResults();
    }

    //get search categories paginated
    public function getCategoriesSearchPaginated($perPage, $offset)
    {
        $q = cleanStr(inputGet('q'));

        $this->buildQuery(null, false);
        if (!empty($q)) {
            $this->builder->join('category_lang', 'category_lang.category_id = categories.id')->where('category_lang.lang_id', clrNum($this->activeLang->id))
                ->like('category_lang.name', $q, 'after');
        }

        return $this->builder->orderBy('categories.created_at', 'DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //update settings
    public function updateSettings()
    {
        $data = [
            'sort_categories' => inputPost('sort_categories'),
            'sort_parent_categories_by_order' => !empty(inputPost('sort_parent_categories_by_order')) ? 1 : 0,
        ];
        return $this->db->table('general_settings')->where('id', 1)->update($data);
    }

    //insert csv item
    public function insertCSVItem($row)
    {
        if (empty($row)) {
            return false;
        }

        $defaultLangId = $this->generalSettings->site_lang ?? 1;

        $categoryId = getCsvNum($row, 'category_id');
        if (empty($categoryId)) {
            return false;
        }

        $slug = getCsvText($row, 'slug');
        $parentId = getCsvNum($row, 'parent_id', 0);
        $order = getCsvNum($row, 'category_order', 1);

        $existing = $this->builder->where('id', $categoryId)->get()->getFirstRow();
        if (!empty($existing)) {
            return false;
        }

        //try to generate slug from name if empty
        if (empty($slug)) {
            $defaultName = $row['name'] ?? null;
            if (!empty($defaultName)) {
                $slug = strSlug($defaultName);
            }
        }

        if (empty($slug)) {
            $slug = uniqid();
        }

        //insert base category
        $categoryData = [
            'id' => $categoryId,
            'slug' => $slug,
            'parent_id' => $parentId,
            'category_order' => $order,
            'featured_order' => 1,
            'homepage_order' => 5,
            'status' => 1,
            'is_featured' => 0,
            'show_on_main_menu' => 1,
            'show_image_on_main_menu' => 0,
            'show_products_on_index' => 0,
            'show_subcategory_products' => 0,
            'storage' => 'local',
            'image' => '',
            'show_description' => 0
        ];

        if ($this->builder->insert($categoryData)) {
            foreach ($this->activeLanguages as $language) {
                $suffix = $language->id == $defaultLangId ? '' : '_lang' . $language->id;

                $name = getCsvText($row, 'name' . $suffix);
                $metaTitle = getCsvText($row, 'meta_title' . $suffix);
                $metaDesc = getCsvText($row, 'meta_description' . $suffix);
                $metaKeywords = getCsvText($row, 'meta_keywords' . $suffix);

                if ($name !== '') {
                    $existingLang = $this->builderLang->where('category_id', $categoryId)->where('lang_id', $language->id)->get()->getFirstRow();
                    if (empty($existingLang)) {
                        $langData = [
                            'category_id' => $categoryId,
                            'lang_id' => $language->id,
                            'name' => $name,
                            'meta_title' => $metaTitle,
                            'meta_description' => $metaDesc,
                            'meta_keywords' => $metaKeywords
                        ];
                        $this->builderLang->insert($langData);
                    }
                }
            }
        }
        return true;
    }

    //set unset featured category
    public function setUnsetFeaturedCategory($categoryId)
    {
        $category = $this->getCategory($categoryId);
        if (empty($category)) {
            return false;
        }
        $data = [];
        if (inputPost('is_form') !== null) {
            $data['is_featured'] = inputPost('is_form') == 1 ? 1 : 0;
        } else {
            $data['is_featured'] = $category->is_featured == 1 ? 0 : 1;
        }
        return $this->builder->where('id', $category->id)->update($data);
    }

    //set unset index category
    public function setUnsetIndexCategory($categoryId)
    {
        $category = $this->getCategory($categoryId);
        if (empty($category)) {
            return false;
        }

        $data = [];
        if (inputPost('is_form') !== null) {
            $data['show_products_on_index'] = inputPost('is_form') == 1 ? 1 : 0;
        } else {
            $data['show_products_on_index'] = $category->show_products_on_index == 1 ? 0 : 1;
        }

        $showSubcategoryProducts = inputPost('show_subcategory_products');
        $data['show_subcategory_products'] = !empty($showSubcategoryProducts) ? 1 : 0;
        return $this->builder->where('id', clrNum($category->id))->update($data);
    }

    //edit featured categories order
    public function editFeaturedCategoriesOrder()
    {
        $categoryId = clrNum(inputPost('category_id'));
        $order = clrNum(inputPost('order'));
        if (empty($categoryId) || !is_numeric($order)) {
            return false;
        }
        return $this->builder->where('id', $categoryId)->update(['featured_order' => $order]);
    }

    //update index categories order
    public function editIndexCategoriesOrder()
    {
        $categoryId = clrNum(inputPost('category_id'));
        $order = clrNum(inputPost('order'));
        if (empty($categoryId) || !is_numeric($order)) {
            return false;
        }
        return $this->builder->where('id', $categoryId)->update(['homepage_order' => $order]);
    }

    //get paginated sitemap categories
    public function getSitemapCategoriesPaginated($perPage, $offset)
    {
        $this->buildQuery();
        return $this->builder->limit($perPage, $offset)->get()->getResult();
    }

    //set category paths if empty
    public function ensureCategoryPathExists()
    {
        $row = $this->builderPaths->select('id')->limit(1)->get()->getFirstRow();
        if (empty($row)) {
            $this->rebuildCategoryPaths();
        }
    }

    //delete category image
    public function deleteCategoryImage($categoryId)
    {
        $category = $this->getCategory($categoryId);
        if (empty($category) || empty($category->image)) {
            return false;
        }

        deleteStorageFile($category->image, $category->storage);
        return $this->builder->where('id', $category->id)->update(['image' => '', 'storage' => 'local']);
    }

    //delete category
    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            return false;
        }

        if (countItems($this->getSubCategoriesByParentId($category->id)) > 0) {
            return false;
        }

        $this->db->transStart();

        // Perform all database deletions
        $this->builderPaths->groupStart()->where('ancestor_id', $category->id)->orWhere('descendant_id', $category->id)->groupEnd()->delete();
        $this->builderLang->where('category_id', $category->id)->delete();
        $this->builder->where('id', $category->id)->delete();

        $this->db->transComplete();

        // Check if the transaction was successful
        if ($this->db->transStatus() === true) {
            $this->deleteCategoryImage($category->id);
            return true;
        }

        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Category Paths
     * --------------------------------------------------------------------
     */

    /**
     * Inserts the hierarchical paths for a new category into the closure table.
     *
     * @param int $categoryId The ID of the newly inserted category.
     * @param int|null $parentId The parent ID of the new category. Can be 0 or null for a root category.
     */
    protected function insertCategoryPaths(int $categoryId, ?int $parentId)
    {
        $batchData = [];
        // Insert the self-reference path (a category is its own ancestor and descendant with depth 0).
        $batchData[] = [
            'ancestor_id' => $categoryId,
            'descendant_id' => $categoryId,
            'depth' => 0
        ];

        if ($parentId !== null && $parentId > 0) {
            // Fetch all ancestor paths of the parent category.
            $parentPaths = $this->builderPaths->where('descendant_id', $parentId)->get()->getResult();
            if (!empty($parentPaths)) {
                foreach ($parentPaths as $path) {
                    // For each ancestor of the parent, create a new path for the new category.
                    $batchData[] = [
                        'ancestor_id' => $path->ancestor_id,
                        'descendant_id' => $categoryId,
                        'depth' => $path->depth + 1
                    ];
                }
            }
        }

        // Insert all generated paths in a single, efficient batch query.
        if (!empty($batchData)) {
            $this->builderPaths->insertBatch($batchData);
        }
    }

    /**
     * Rebuilds the closure table paths when a category (and its subtree) is moved to a new parent.
     *
     * @param int $categoryId The ID of the category being moved.
     * @param int $newParentId The ID of the new parent category.
     */
    protected function updateCategoryPaths(int $categoryId, int $newParentId)
    {
        // Disconnect the moved subtree from its old ancestors
        $sqlDelete = "DELETE a FROM category_paths AS a
                  JOIN category_paths AS d ON a.descendant_id = d.descendant_id
                  LEFT JOIN category_paths AS p ON a.ancestor_id = p.descendant_id AND p.ancestor_id = ?
                  WHERE d.ancestor_id = ? AND p.descendant_id IS NULL";
        $this->db->query($sqlDelete, [$categoryId, $categoryId]);

        // Reconnect the moved subtree to its new ancestors
        if ($newParentId > 0) {
            $sqlInsert = "INSERT INTO category_paths (ancestor_id, descendant_id, depth)
                      SELECT supertree.ancestor_id, subtree.descendant_id, supertree.depth + subtree.depth + 1
                      FROM category_paths AS supertree
                      CROSS JOIN category_paths AS subtree
                      WHERE supertree.descendant_id = ? AND subtree.ancestor_id = ?";
            $this->db->query($sqlInsert, [$newParentId, $categoryId]);
        }
    }

    /**
     * Checks if a given category ($descendantId) is a descendant of another category ($ancestorId).
     *
     * @param int $ancestorId The potential ancestor category's ID.
     * @param int $descendantId The potential descendant category's ID.
     * @return bool
     */
    protected function isCategoryDescendant(int $ancestorId, int $descendantId): bool
    {
        if (empty($descendantId) || empty($ancestorId)) {
            return false;
        }
        $query = $this->builderPaths->where('ancestor_id', $ancestorId)->where('descendant_id', $descendantId)->countAllResults();
        return $query > 0;
    }

    /**
     * Rebuilds the entire category_paths table from scratch.
     *
     * @return bool True on success, false on failure.
     */
    public function rebuildCategoryPaths()
    {
        // Start the database transaction.
        $this->db->transStart();

        // Clear the existing paths table.
        $this->builderPaths->emptyTable();

        // Fetch all categories to build the hierarchy.
        $categories = $this->builder->select('id, parent_id')->get()->getResult();
        if (empty($categories)) {
            $this->db->transComplete();
            return true;
        }

        // Build an ID => ParentID map for quick lookups in memory.
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat->id] = $cat->parent_id;
        }

        $batchData = [];
        $batchSize = 1000; // The number of rows to insert at once.

        // Rebuild paths for each category.
        foreach ($categories as $cat) {
            // Insert the self-reference path (depth = 0).
            $batchData[] = [
                'ancestor_id' => $cat->id,
                'descendant_id' => $cat->id,
                'depth' => 0
            ];

            // Walk up the parent chain to create paths to all ancestors.
            $parentId = $cat->parent_id;
            $depth = 1;
            while (!empty($parentId) && isset($categoryMap[$parentId])) {
                $batchData[] = [
                    'ancestor_id' => $parentId,
                    'descendant_id' => $cat->id,
                    'depth' => $depth
                ];
                // Move to the next parent up the chain.
                $parentId = $categoryMap[$parentId];
                $depth++;
            }

            // To improve performance and manage memory, insert in batches.
            if (count($batchData) >= $batchSize) {
                $this->builderPaths->insertBatch($batchData);
                $batchData = []; // Reset the batch array.
            }
        }

        // Insert any remaining records that didn't fill a full batch.
        if (!empty($batchData)) {
            $this->builderPaths->insertBatch($batchData);
        }

        // Complete the transaction.
        $this->db->transComplete();

        // Check the final status.
        if ($this->db->transStatus() === false) {
            return false;
        }

        return true;
    }

}