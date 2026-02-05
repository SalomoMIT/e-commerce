<?php namespace App\Models;

class FieldModel extends BaseModel
{
    protected $builder;
    protected $builderFieldOptions;
    protected $builderFieldProduct;
    protected $builderFieldCategory;
    protected $langId;
    protected $defaultLangId;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('custom_fields');
        $this->builderFieldOptions = $this->db->table('custom_fields_options');
        $this->builderFieldProduct = $this->db->table('custom_fields_product');
        $this->builderFieldCategory = $this->db->table('custom_fields_category');

        $this->langId = $this->activeLang->id;
        $this->defaultLangId = $this->defaultLang->id;
    }

    //input values
    public function inputValues()
    {
        return [
            'is_required' => !empty(inputPost('is_required')) ? 1 : 0,
            'where_to_display' => inputPost('where_to_display'),
            'status' => !empty(inputPost('status')) ? 1 : 0,
            'field_order' => inputPost('field_order')
        ];
    }

    //add field
    public function addField()
    {
        $data = $this->inputValues();
        //generate filter key
        $fieldName = inputPost('name_' . $this->activeLang->id);
        $data['product_filter_key'] = $this->createProductFilterKey($fieldName);
        $data['field_type'] = inputPost('field_type');
        if ($this->builder->insert($data)) {
            $fieldId = $this->db->insertID();
            $this->addEditCustomFieldName($fieldId);

            return $fieldId;
        }
        return false;
    }

    //update field
    public function editField($id)
    {
        $field = $this->getField($id);
        if (!empty($field)) {
            $data = $this->inputValues();
            if (empty($data['is_required'])) {
                $data['is_required'] = 0;
            }
            $key = inputPost('product_filter_key');
            $data['product_filter_key'] = $this->createProductFilterKey($key, $field->id);
            $data['field_type'] = inputPost('field_type');

            $this->addEditCustomFieldName($field->id);

            return $this->builder->where('id', $field->id)->update($data);
        }
        return false;
    }

    //add edit custom field name
    public function addEditCustomFieldName($fieldId)
    {
        $fieldId = clrNum($fieldId);
        foreach ($this->activeLanguages as $language) {
            $data = [
                'name' => inputPost('name_' . $language->id)
            ];

            $exists = $this->db->table('custom_field_lang')->where('field_id', $fieldId)->where('lang_id', $language->id)->countAllResults();
            if ($exists > 0) {
                $this->db->table('custom_field_lang')->where('field_id', $fieldId)->where('lang_id', $language->id)->update($data);
            } else {
                $data['field_id'] = $fieldId;
                $data['lang_id'] = $language->id;
                $this->db->table('custom_field_lang')->insert($data);
            }
        }
    }

    //create unique product filter key
    private function createProductFilterKey($name, $id = null)
    {
        $key = '';
        if (!empty($name)) {
            $key = strSlug($name);
            //check filter key exists
            $row = $this->getFieldByFilterKey($key, $id);
            if (!empty($row)) {
                $key = $key . '-' . rand(1, 999);
                $row = $this->getFieldByFilterKey($key, $id);
                if (!empty($row)) {
                    $key = $key . '-' . uniqid();
                }
            }
        }
        if (empty($key)) {
            $key = uniqid();
        }
        return $key;
    }

    //add field option
    public function addFieldOption($fieldId)
    {
        $mainOption = inputPost('option_name_' . selectedLangId());
        $data = [
            'field_id' => $fieldId,
            'option_key' => strSlug($mainOption)
        ];
        if ($this->builderFieldOptions->insert($data)) {
            $optionId = $this->db->insertID();

            //add field option lang
            foreach ($this->activeLanguages as $language) {
                $name = inputPost('option_name_' . $language->id);
                $this->addEditCustomFieldOptionName($optionId, $language->id, $name);
            }

            return true;
        }

        return false;
    }

    //edit field option
    public function editFieldOption()
    {
        $id = inputPost('option_id');
        $fieldOption = $this->getFieldOption($id);

        if (empty($fieldOption)) {
            return false;
        }

        $optionText = inputPost("option_text");
        $langId = inputPost("lang_id");
        $optionKey = '';

        if ($langId == selectedLangId()) {
            $optionKey = strSlug($optionText);
        }

        if (!empty($optionKey)) {
            $this->builderFieldOptions->where('id', $fieldOption->id)->update(['option_key' => $optionKey]);
        }

        $this->addEditCustomFieldOptionName($fieldOption->id, $langId, $optionText);

        return true;
    }

    //add edit custom field option name
    public function addEditCustomFieldOptionName($optionId, $langId, $name)
    {
        $optionId = clrNum($optionId);
        $langId = clrNum($langId);

        $exists = $this->db->table('custom_field_option_lang')->where('option_id', $optionId)->where('lang_id', $langId)->countAllResults();
        if ($exists > 0) {
            $this->db->table('custom_field_option_lang')->where('option_id', $optionId)->where('lang_id', $langId)->update(['name' => $name]);
        } else {
            $this->db->table('custom_field_option_lang')->insert([
                'option_id' => $optionId,
                'lang_id' => $langId,
                'name' => $name
            ]);
        }
    }

    //get field
    public function getField($id)
    {
        if ($this->langId == $this->defaultLangId) {
            $this->builder->select('custom_fields.*, cfl.name AS name')
                ->join('custom_field_lang cfl', 'cfl.field_id = custom_fields.id AND cfl.lang_id = ' . $this->db->escape($this->langId), 'left');
        } else {
            $this->builder->select('custom_fields.*, COALESCE(cfl_selected.name, cfl_default.name) AS name')
                ->join('custom_field_lang cfl_selected', 'cfl_selected.field_id = custom_fields.id AND cfl_selected.lang_id = ' . $this->db->escape($this->langId), 'left')
                ->join('custom_field_lang cfl_default', 'cfl_default.field_id = custom_fields.id AND cfl_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left');
        }

        return $this->builder->where('custom_fields.id', clrNum($id))->get()->getRow();
    }

    //get field by filter key
    public function getFieldByFilterKey($filterKey, $exceptId = null)
    {
        if (!empty($exceptId)) {
            $this->builder->where('id != ', clrNum($exceptId));
        }
        return $this->builder->where('product_filter_key', $filterKey)->get()->getRow();
    }

    //get fields
    public function getFields()
    {
        return $this->builder->orderBy('field_order')->get()->getResult();
    }

    //get field count
    public function getFieldCount($langId)
    {
        $this->filterFields($langId);
        return $this->builder->countAllResults();
    }

    //get paginated fields
    public function getFieldsPaginated($perPage, $offset, $langId)
    {
        $this->filterFields($langId);
        $this->builder->select('custom_fields.*, custom_field_lang.name as field_name');
        return $this->builder->orderBy('id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //filter fields
    private function filterFields($langId)
    {
        $q = inputGet('q');

        $this->builder->join('custom_field_lang', 'custom_fields.id = custom_field_lang.field_id', 'left')
            ->where('lang_id', clrNum($langId));

        if (!empty($q)) {
            $this->builder->like('custom_field_lang.name', $q);
        }
    }

    //get field names array
    public function getFieldNamesArray($fieldId)
    {
        $nameArray = array();
        $result = $this->db->table('custom_field_lang')->where('field_id', $fieldId)->get()->getResult();
        if (!empty($result)) {
            foreach ($result as $item) {
                $nameArray[$item->lang_id] = $item->name;
            }
        }

        return $nameArray;
    }

    //get custom fields by category
    public function getCustomFieldsByCategory($categoryId)
    {
        $category = getCategory($categoryId);
        if (empty($category)) {
            return [];
        }

        $categories = getCategoryParentTree($category->id);
        if (empty($categories)) {
            return [];
        }

        $categoryIds = array_column($categories, 'id');
        if (empty($categoryIds)) {
            return [];
        }

        $builder = $this->builder;
        if ($this->langId == $this->defaultLangId) {
            $builder->select('custom_fields.*, cfl.name AS name, custom_fields_category.category_id AS category_id')
                ->join('custom_fields_category', 'custom_fields_category.field_id = custom_fields.id')
                ->join('custom_field_lang cfl', 'cfl.field_id = custom_fields.id AND cfl.lang_id = ' . $this->db->escape($this->langId), 'left');
        } else {
            $builder->select('custom_fields.*, COALESCE(cfl_selected.name, cfl_default.name) AS name, custom_fields_category.category_id AS category_id')
                ->join('custom_fields_category', 'custom_fields_category.field_id = custom_fields.id')
                ->join('custom_field_lang cfl_selected', 'cfl_selected.field_id = custom_fields.id AND cfl_selected.lang_id = ' . $this->db->escape($this->langId), 'left')
                ->join('custom_field_lang cfl_default', 'cfl_default.field_id = custom_fields.id AND cfl_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left');
        }

        $builder->where('custom_fields.status', 1)
            ->whereIn('custom_fields_category.category_id', $categoryIds, false)
            ->orderBy('custom_fields.field_order');

        $results = $builder->get()->getResult();

        $unique = [];
        foreach ($results as $item) {
            $unique[$item->id] = $item;
        }

        return array_values($unique);
    }


    //get field categories
    public function getFieldCategories($fieldId)
    {
        return $this->builderFieldCategory->where('field_id', clrNum($fieldId))->get()->getResult();
    }

    //get field options
    public function getFieldOptions($customField, $langId)
    {
        if (empty($customField)) {
            return [];
        }

        $builder = $this->builderFieldOptions;
        if ($langId == $this->defaultLangId) {
            $builder->select('custom_fields_options.*, cfol.name AS name');
            $builder->join('custom_field_option_lang cfol', 'cfol.option_id = custom_fields_options.id AND cfol.lang_id = ' . $this->db->escape($langId), 'left');
        } else {
            $builder->select('custom_fields_options.*, COALESCE(cfol_selected.name, cfol_default.name) AS name');
            $builder->join('custom_field_option_lang cfol_selected', 'cfol_selected.option_id = custom_fields_options.id AND cfol_selected.lang_id = ' . $this->db->escape($langId), 'left');
            $builder->join('custom_field_option_lang cfol_default', 'cfol_default.option_id = custom_fields_options.id AND cfol_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left');
        }

        $builder->where('custom_fields_options.field_id', clrNum($customField->id));

        if ($customField->sort_options == 'date') {
            $builder->orderBy('custom_fields_options.id', 'ASC');
        } elseif ($customField->sort_options == 'date_desc') {
            $builder->orderBy('custom_fields_options.id', 'DESC');
        } elseif ($customField->sort_options == 'alphabetically') {
            $builder->orderBy('name', 'ASC');
        }

        return $builder->get()->getResult();
    }

    //get field options name array
    public function getFieldOptionsNameArray($fieldId)
    {
        $optionsArray = [];

        $result = $this->builderFieldOptions->select('custom_field_option_lang.lang_id, custom_field_option_lang.name, custom_fields_options.id AS option_id')
            ->join('custom_field_option_lang', 'custom_fields_options.id = custom_field_option_lang.option_id')
            ->where('custom_fields_options.field_id', clrNum($fieldId))
            ->get()->getResult();

        if (empty($result)) {
            return [];
        }

        foreach ($result as $item) {
            $optionsArray[$item->option_id][$item->lang_id] = $item->name;
        }

        return $optionsArray;
    }

    //update field options settings
    public function updateFieldOptionsSettings()
    {
        $fieldId = inputPost('field_id');
        $data = ['sort_options' => inputPost('sort_options')];
        return $this->builder->where('id', clrNum($fieldId))->update($data);
    }

    //get field all options
    public function getFieldAllOptions($fieldId)
    {
        return $this->builderFieldOptions->where('custom_fields_options.field_id', clrNum($fieldId))->get()->getResult();
    }

    //get field option
    public function getFieldOption($optionId)
    {
        return $this->builderFieldOptions->where('id', clrNum($optionId))->get()->getRow();
    }

    //add category to field
    public function addCategoryToField()
    {
        $fieldId = clrNum(inputPost('field_id'));
        $categoryId = getDropdownCategoryId();
        $row = $this->getCategoryField($fieldId, $categoryId);
        if (empty($row)) {
            $data = [
                'field_id' => $fieldId,
                'category_id' => $categoryId
            ];
            return $this->builderFieldCategory->insert($data);
        }
        return false;
    }

    //get category field
    public function getCategoryField($fieldId, $categoryId)
    {
        return $this->builderFieldCategory->where('field_id', clrNum($fieldId))->where('category_id', clrNum($categoryId))->get()->getRow();
    }

    //get selected custom field values for product
    public function getSelectedCustomFieldValuesForProduct($fieldId, $productId, $langId)
    {
        $fieldId = clrNum($fieldId);
        $productId = clrNum($productId);
        $langId = clrNum($langId);

        $defaultLangId = $this->defaultLangId ?? 1;

        $builder = $this->db->table('custom_fields_product AS cfp');
        $builder->join('custom_fields_options AS cfo', 'cfo.id = cfp.selected_option_id', 'left');

        if ($langId == $defaultLangId) {
            $builder->select('cfp.*, cfol.name AS selected_option_name');
            $builder->join('custom_field_option_lang AS cfol', 'cfol.option_id = cfo.id AND cfol.lang_id = ' . $this->db->escape($langId), 'left');
        } else {
            $builder->select('cfp.*, COALESCE(cfol_selected.name, cfol_default.name) AS selected_option_name');
            $builder->join('custom_field_option_lang AS cfol_selected', 'cfol_selected.option_id = cfo.id AND cfol_selected.lang_id = ' . $this->db->escape($langId), 'left');
            $builder->join('custom_field_option_lang AS cfol_default', 'cfol_default.option_id = cfo.id AND cfol_default.lang_id = ' . $this->db->escape($defaultLangId), 'left');
        }

        $builder->where('cfp.field_id', $fieldId)->where('cfp.product_id', $productId);
        return $builder->get()->getResult();
    }

    //get product custom field input value
    public function getProductCustomFieldInputValue($fieldId, $productId)
    {
        $row = $this->builderFieldProduct->where('field_id', clrNum($fieldId))->where('product_id', clrNum($productId))->get()->getRow();
        if (!empty($row) && !empty($row->field_value)) {
            return $row->field_value;
        }
        return '';
    }

    //delete category from field
    public function deleteCategoryFromField($fieldId, $categoryId)
    {
        return $this->builderFieldCategory->where('field_id', clrNum($fieldId))->where('category_id', clrNum($categoryId))->delete();
    }

    //delete custom field option
    public function deleteCustomFieldOption($id)
    {
        $option = $this->getFieldOption($id);
        if (!empty($option)) {
            $this->db->transStart();

            $this->builderFieldOptions->where('id', $option->id)->delete();
            $this->db->table('custom_field_option_lang')->where('option_id', $option->id)->delete();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return false;
            }
            return true;
        }
        return false;
    }

    //get product custom fields values
    public function getProductCustomFieldsValues($productId, $brand, $langId)
    {
        $arrayTop = [];
        $arrayBottom = [];

        if (!empty($brand)) {
            $data = [
                'name' => trans('brand'),
                'value' => $brand
            ];
            if ($this->productSettings->brand_where_to_display == 1) {
                $arrayTop[] = $data;
            } else {
                $arrayBottom[] = $data;
            }
        }

        $builder = $this->db->table('custom_fields_product');

        $selectColumns = 'custom_fields.id AS field_id, custom_fields.field_type, custom_fields.where_to_display, custom_fields_product.field_value';

        if ($langId == $this->defaultLangId) {
            $builder->select($selectColumns . ', cfl.name AS field_name, cfol.name AS option_name')
                ->join('custom_fields', 'custom_fields.id = custom_fields_product.field_id')
                ->join('custom_field_lang cfl', 'cfl.field_id = custom_fields.id AND cfl.lang_id = ' . $this->db->escape($langId), 'left')
                ->join('custom_field_option_lang cfol', 'cfol.option_id = custom_fields_product.selected_option_id AND cfol.lang_id = ' . $this->db->escape($langId), 'left');
        } else {
            $builder->select($selectColumns . ', COALESCE(cfl_selected.name, cfl_default.name) AS field_name, COALESCE(cfol_selected.name, cfol_default.name) AS option_name')
                ->join('custom_fields', 'custom_fields.id = custom_fields_product.field_id')
                ->join('custom_field_lang cfl_selected', 'cfl_selected.field_id = custom_fields.id AND cfl_selected.lang_id = ' . $this->db->escape($langId), 'left')
                ->join('custom_field_lang cfl_default', 'cfl_default.field_id = custom_fields.id AND cfl_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left')
                ->join('custom_field_option_lang cfol_selected', 'cfol_selected.option_id = custom_fields_product.selected_option_id AND cfol_selected.lang_id = ' . $this->db->escape($langId), 'left')
                ->join('custom_field_option_lang cfol_default', 'cfol_default.option_id = custom_fields_product.selected_option_id AND cfol_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left');
        }

        $builder->where('custom_fields.status', 1)->where('custom_fields_product.product_id', clrNum($productId))->orderBy('custom_fields.field_order');

        $results = $builder->get()->getResult();

        $processedFields = [];
        if (!empty($results)) {
            foreach ($results as $item) {
                if (!isset($processedFields[$item->field_id])) {
                    $processedFields[$item->field_id] = [
                        'name' => $item->field_name,
                        'value' => '',
                        'where_to_display' => $item->where_to_display
                    ];
                }

                if ($item->field_type == 'text' || $item->field_type == 'textarea' || $item->field_type == 'number' || $item->field_type == 'date') {
                    $processedFields[$item->field_id]['value'] = $item->field_value;
                } else {
                    if (!empty($item->option_name)) {
                        if (!empty($processedFields[$item->field_id]['value'])) {
                            $processedFields[$item->field_id]['value'] .= ', ';
                        }
                        $processedFields[$item->field_id]['value'] .= $item->option_name;
                    }
                }
            }
        }

        if (!empty($processedFields)) {
            foreach ($processedFields as $field) {
                if ($field['where_to_display'] == 1) {
                    $arrayTop[] = $field;
                } else {
                    $arrayBottom[] = $field;
                }
            }
        }

        return ['top' => $arrayTop, 'bottom' => $arrayBottom];
    }

    //delete field product values by product id
    public function deleteFieldProductValuesByProductId($productId)
    {
        if (!empty($productId)) {
            $this->builderFieldProduct->where('product_id', clrNum($productId))->delete();
        }
    }

    //delete field
    public function deleteField($id)
    {
        $field = $this->getField($id);
        if (empty($field)) {
            return false;
        }

        $this->db->transBegin();

        try {
            $optionIds = $this->builderFieldOptions->where('field_id', $field->id)->select('id')->get()->getResultArray();
            $optionIds = array_column($optionIds, 'id');

            if (!empty($optionIds)) {
                $this->db->table('custom_field_option_lang')->whereIn('option_id', $optionIds)->delete();
            }

            $this->builderFieldOptions->where('field_id', $field->id)->delete();
            $this->builderFieldCategory->where('field_id', $field->id)->delete();
            $this->builderFieldProduct->where('field_id', $field->id)->delete();
            $this->db->table('custom_field_lang')->where('field_id', $field->id)->delete();
            $this->builder->where('id', $field->id)->delete();

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();
            return true;

        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }

    //insert csv item
    public function insertCSVItem($row)
    {
        if (empty($row)) {
            return false;
        }

        $this->db->transStart();

        $field = [
            'field_type' => getCsvText($row, 'field_type'),
            'is_required' => getCsvNum($row, 'is_required', 1),
            'status' => getCsvNum($row, 'status', 1),
            'field_order' => getCsvNum($row, 'field_order', 1),
            'is_product_filter' => getCsvNum($row, 'is_product_filter', 1),
            'sort_options' => 'alphabetically',
            'where_to_display' => 2,
        ];

        $name = getCsvText($row, 'name');
        $slug = $this->createProductFilterKey($name);
        $field['product_filter_key'] = $slug !== '' ? $slug : uniqid();

        if ($this->builder->insert($field)) {
            $fieldId = $this->db->insertID();

            // Insert name for each active language
            foreach ($this->activeLanguages as $language) {
                $suffix = $language->id == $this->defaultLangId ? '' : '_lang' . $language->id;
                $langName = getCsvText($row, 'name' . $suffix);
                if ($langName !== '') {
                    $data = [
                        'field_id' => $fieldId,
                        'lang_id' => $language->id,
                        'name' => $langName
                    ];
                    $this->db->table('custom_field_lang')->insert($data);
                }
            }

            // Insert categories
            $categoryIdStr = getCsvText($row, 'category_id');
            if ($categoryIdStr !== '') {
                $categoryIds = explode(',', $categoryIdStr);

                $existingCategoryRelations = $this->builderFieldCategory->select('category_id')->where('field_id', $fieldId)->get()->getResultArray();
                $existingCategoryIds = array_column($existingCategoryRelations, 'category_id');

                $categoriesToInsert = [];
                foreach ($categoryIds as $categoryId) {
                    $categoryId = trim($categoryId ?? '');
                    if (is_numeric($categoryId) && $categoryId > 0) {
                        $categoryId = (int)$categoryId;
                        if (!in_array($categoryId, $existingCategoryIds)) {
                            $categoriesToInsert[] = [
                                'category_id' => $categoryId,
                                'field_id' => $fieldId,
                            ];
                            $existingCategoryIds[] = $categoryId;
                        }
                    }
                }

                if (!empty($categoriesToInsert)) {
                    $this->builderFieldCategory->insertBatch($categoriesToInsert);
                }
            }

            // Insert options
            $this->insertCSVItemOptions($row, $fieldId);
        }

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    //insert csv item options
    private function insertCSVItemOptions($row, $fieldId)
    {
        $defaultLangId = $this->generalSettings->site_lang ?? 1;

        $optionsMap = [];
        $optionKeys = [];

        foreach ($this->activeLanguages as $language) {
            $suffix = $language->id == $defaultLangId ? '' : '_lang' . $language->id;
            $optionStr = getCsvText($row, 'options' . $suffix);

            if ($optionStr !== '') {
                $options = array_filter(array_map('trim', explode(',', $optionStr)));

                foreach ($options as $i => $optionName) {
                    $optionsMap[$i][] = [
                        'lang_id' => $language->id,
                        'name'    => $optionName
                    ];

                    if ($language->id == $defaultLangId && !isset($optionKeys[$i])) {
                        $slug = strSlug($optionName);
                        $optionKeys[$i] = $slug !== '' ? $slug : uniqid();
                    }
                }
            }
        }

        foreach ($optionsMap as $i => $translations) {
            if (empty($translations) || !is_array($translations)) {
                continue;
            }

            $optionKey = $optionKeys[$i] ?? uniqid();

            $this->db->table('custom_fields_options')->insert([
                'field_id'   => (int)$fieldId,
                'option_key' => $optionKey,
            ]);

            $optionId = $this->db->insertID();
            if (!$optionId) {
                continue;
            }

            $langDataBatch = [];
            foreach ($translations as $translation) {
                $langDataBatch[] = [
                    'option_id' => $optionId,
                    'lang_id'   => $translation['lang_id'],
                    'name'      => $translation['name'],
                ];
            }

            if (!empty($langDataBatch)) {
                $this->db->table('custom_field_option_lang')->insertBatch($langDataBatch);
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Product Filters
     * --------------------------------------------------------------------
     */

    //get custom filters
    public function getCustomFilters($categoryId = null, $langId = 1, $categories = null, $getFilterOptions = false)
    {
        $categoryIds = [];
        if (!empty($categoryId)) {
            $categoryIds[] = $categoryId;
        }
        if (!empty($categories)) {
            $additionalIds = array_column($categories, 'id');
            $categoryIds = array_unique(array_merge($categoryIds, $additionalIds));
        }
        sort($categoryIds);


        $customFilters = [];

        if (!empty($categoryIds)) {
            $builder = $this->db->table('custom_fields');
            $builder->distinct();

            if ($langId == $this->defaultLangId) {
                $builder->select('custom_fields.*, cfl.name as field_name')
                    ->join('custom_field_lang as cfl', 'cfl.field_id = custom_fields.id AND cfl.lang_id = ' . $this->db->escape($langId), 'left');
            } else {
                $builder->select('custom_fields.*, COALESCE(cfl_selected.name, cfl_default.name) AS field_name')
                    ->join('custom_field_lang cfl_selected', 'cfl_selected.field_id = custom_fields.id AND cfl_selected.lang_id = ' . $this->db->escape($langId), 'left')
                    ->join('custom_field_lang cfl_default', 'cfl_default.field_id = custom_fields.id AND cfl_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left');
            }

            $builder->where('custom_fields.status', 1)->where('custom_fields.is_product_filter', 1)->whereIn('custom_fields.field_type', ['single_select', 'multi_select'])
                ->join('custom_fields_category', 'custom_fields_category.field_id = custom_fields.id')->whereIn('custom_fields_category.category_id', $categoryIds);

            $customFilters = $builder->orderBy('custom_fields.field_order')->get()->getResult();
        }

        if ($this->productSettings->brand_status == 1) {
            $brandFilter = (object)[
                'id' => 'brand',
                'field_name' => trans("brand"),
                'product_filter_key' => 'brand',
                'options' => [],
                'has_more_options' => false
            ];

            array_unshift($customFilters, $brandFilter);
        }

        if (!$getFilterOptions || empty($customFilters)) {
            return $customFilters;
        }

        $limit = defined('CUSTOM_FILTERS_LOAD_LIMIT') ? (int)CUSTOM_FILTERS_LOAD_LIMIT : 50;

        foreach ($customFilters as $customFilter) {
            $result = [];
            if ($customFilter->id === 'brand') {
                $commonModel = new CommonModel();
                $result = $commonModel->getBrands($langId, $categoryId, null, $limit, 0);
                $customFilter->options = $result['brands'] ?? [];
                $customFilter->has_more_options = $result['hasMore'] ?? false;
            } else {
                $result = $this->loadFilterOptions($customFilter, $langId, '', $limit, 0);
                $customFilter->options = $result['options'] ?? [];
                $customFilter->has_more_options = $result['hasMore'] ?? false;
            }
        }

        return $customFilters;
    }

    //load custom filter options
    public function loadFilterOptions($field, $langId, $searchTerm, $limit, $offset)
    {
        if (empty($field)) {
            return ['options' => [], 'hasMore' => false];
        }

        $sortType = $field->sort_options;
        $fieldId = (int)$field->id;
        $langId = (int)$langId;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $builder = $this->db->table('custom_fields_options o');
        $builder->select('o.id, o.field_id, o.option_key');

        $nameColumnExpression = '';

        if ($langId == $this->defaultLangId) {
            $builder->select('l.name')->join('custom_field_option_lang l', 'l.option_id = o.id AND l.lang_id = ' . $this->db->escape($langId), 'left');
            $nameColumnExpression = 'l.name';
        } else {
            $selectClause = 'COALESCE(l_selected.name, l_default.name) AS name';
            $builder->select($selectClause, false)
                ->join('custom_field_option_lang l_selected', 'l_selected.option_id = o.id AND l_selected.lang_id = ' . $this->db->escape($langId), 'left')
                ->join('custom_field_option_lang l_default', 'l_default.option_id = o.id AND l_default.lang_id = ' . $this->db->escape($this->defaultLangId), 'left');
            $nameColumnExpression = 'COALESCE(l_selected.name, l_default.name)';
        }

        $orderByClause = 'o.id ASC';
        if ($sortType == 'alphabetically') {
            $orderByClause = "o.option_key ASC";
        } elseif ($sortType == 'date_desc') {
            $orderByClause = 'o.id DESC';
        }

        $builder->where('o.field_id', $fieldId);

        if (!empty($searchTerm)) {
            $builder->having($nameColumnExpression . " LIKE '%" . $this->db->escapeLikeString($searchTerm) . "%'");
        }

        $options = $builder->orderBy($orderByClause, '', false)->limit($limit + 1, $offset)->get()->getResult();

        $hasMore = count($options) > $limit;
        if ($hasMore) {
            // Remove the extra item, it was only for the check.
            array_pop($options);
        }

        return ['options' => $options, 'hasMore' => $hasMore];
    }

    //get selected filter and their option names
    public function getCustomFiltersDisplayNames($langId)
    {
        $queryParams = $this->request->getGet();

        $selectedFilterKeys = [];
        $selectedOptionKeys = [];

        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $valueString) {
                // Skip non-filter parameters
                if (in_array($key, ['sort', 'page', 'search', 'p_min', 'p_max'])) {
                    continue;
                }
                $selectedFilterKeys[] = $key;
                $selectedOptionKeys = array_merge($selectedOptionKeys, explode(',', $valueString));
            }
            $selectedOptionKeys = array_filter(array_unique($selectedOptionKeys));
        }

        $data = [
            'fieldNames' => [],
            'optionNames' => []
        ];

        if (!empty($selectedFilterKeys) && !empty($selectedOptionKeys)) {

            $fieldsBuilder = $this->db->table('custom_fields as cf');
            if ($langId == $this->defaultLang->id) {
                $fieldsBuilder->select('cf.product_filter_key, cfl.name')->join('custom_field_lang as cfl', 'cfl.field_id = cf.id AND cfl.lang_id = ' . $this->db->escape($langId), 'left');
            } else {
                $fieldsBuilder->select('cf.product_filter_key, COALESCE(cfl_selected.name, cfl_default.name) AS name')
                    ->join('custom_field_lang cfl_selected', 'cfl_selected.field_id = cf.id AND cfl_selected.lang_id = ' . $this->db->escape($langId), 'left')
                    ->join('custom_field_lang cfl_default', 'cfl_default.field_id = cf.id AND cfl_default.lang_id = ' . $this->db->escape($this->defaultLang->id), 'left');
            }
            $fields = $fieldsBuilder->whereIn('cf.product_filter_key', $selectedFilterKeys)->get()->getResult();

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $data['fieldNames'][$field->product_filter_key] = $field->name;
                }
            }

            $optionsBuilder = $this->db->table('custom_fields_options as cfo');
            $optionsBuilder->select('cfo.option_key, cf.product_filter_key');
            if ($langId == $this->defaultLang->id) {
                $optionsBuilder->select('cfol.name')->join('custom_field_option_lang as cfol', 'cfol.option_id = cfo.id AND cfol.lang_id = ' . $this->db->escape($langId), 'left');
            } else {
                $optionsBuilder->select('COALESCE(cfol_selected.name, cfol_default.name) AS name')
                    ->join('custom_field_option_lang cfol_selected', 'cfol_selected.option_id = cfo.id AND cfol_selected.lang_id = ' . $this->db->escape($langId), 'left')
                    ->join('custom_field_option_lang cfol_default', 'cfol_default.option_id = cfo.id AND cfol_default.lang_id = ' . $this->db->escape($this->defaultLang->id), 'left');
            }
            $options = $optionsBuilder->join('custom_fields as cf', 'cf.id = cfo.field_id')->whereIn('cfo.option_key', $selectedOptionKeys)->whereIn('cf.product_filter_key', $selectedFilterKeys)->get()->getResult();

            if (!empty($options)) {
                foreach ($options as $option) {
                    $uniqueKey = $option->product_filter_key . '_' . $option->option_key;
                    $data['optionNames'][$uniqueKey] = $option->name;
                }
            }
        }

        return $data;
    }

    //add or remove a custom field from filters
    public function toggleProductFilterStatus($fieldId)
    {
        $field = $this->getField($fieldId);

        if (empty($field)) {
            return false;
        }

        $newStatus = ($field->is_product_filter == 1) ? 0 : 1;
        $data = ['is_product_filter' => $newStatus];

        return $this->db->table('custom_fields')->where('id', $fieldId)->update($data);
    }
}