<?php namespace App\Models;

use CodeIgniter\Model;

class TagModel extends BaseModel
{
    protected $builder;
    protected $builderProductTags;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('tags');
        $this->builderProductTags = $this->db->table('product_tags');
    }

    //add or get tag
    public function addOrGetTag($tag, $langId, $returExistingId = true)
    {
        $tag = trim((string)($tag ?? ''));
        $langId = (int)$langId;

        if ($tag === '' || $langId === 0) {
            return 0;
        }

        $row = $this->builder->where('tag', $tag)->where('lang_id', $langId)->get()->getRow();
        if ($row) {
            if ($returExistingId == false) {
                return false;
            }
            return $row->id;
        }

        $this->builder->insert([
            'tag' => $tag,
            'lang_id' => $langId
        ]);

        return $this->db->insertID();
    }

    //edit tag
    public function editTag($id, $tag, $langId)
    {
        $row = $this->getTagById($id);
        if (!empty($row)) {
            $tag = trim($tag ?? '');
            if (!empty($tag) && mb_strlen($tag) > 1) {
                $data = [
                    'tag' => $tag,
                    'lang_id' => clrNum($langId),
                ];
                return $this->builder->where('id', $row->id)->update($data);
            }
        }
        return false;
    }

    //get tag
    public function getTag($tag)
    {
        return $this->builder->where('tags.tag', cleanStr($tag))->get()->getRow();
    }

    //get tag by id
    public function getTagById($id)
    {
        return $this->builder->where('tags.id', clrNum($id))->get()->getRow();
    }

    //get tag suggestions
    public function getTagSuggestions($q, $langId)
    {
        $result = $this->builder->select("tag")->like('tags.tag', cleanStr($q), 'after')->where('lang_id', clrNum($langId))->limit(10)->distinct()->get()->getResult();
        $tags = [];
        if (countItems($result) > 0) {
            $tags = array_map(function ($item) {
                return esc($item->tag);
            }, $result);
        }
        return $tags;
    }

    //get tags count
    public function getTagsCount()
    {
        $this->filterTags();
        return $this->builder->countAllResults();
    }

    //get paginated tags
    public function getTagsPaginated($perPage, $offset)
    {
        $this->filterTags();
        return $this->builder->select('tags.*, 
        (SELECT COUNT(product_tags.id) FROM product_tags WHERE product_tags.tag_id = tags.id) AS count,
        (SELECT name FROM languages WHERE tags.lang_id = languages.id) AS lang_name')->orderBy('id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //filter tags
    public function filterTags()
    {
        $q = inputGet('q');
        $langId = clrNum(inputGet('lang_id'));
        if (!empty($q)) {
            $this->builder->like('tag', cleanStr($q), 'after');
        }
        if (!empty($langId)) {
            $this->builder->where('lang_id', clrNum($langId));
        }
    }

    //get product tags
    public function getProductTags($productId, $langId)
    {
        return $this->builder->join('product_tags', 'tags.id = product_tags.tag_id')
            ->where('product_id', clrNum($productId))->where('lang_id', clrNum($langId))->get()->getResult();
    }

    //get product tags string
    public function getProductTagsString($productId, $langId)
    {
        $tagsStr = '';
        $tags = $this->builder->join('product_tags', 'tags.id = product_tags.tag_id')
            ->where('product_tags.product_id', clrNum($productId))->where('tags.lang_id', clrNum($langId))->get()->getResultArray();

        if (!empty($tags)) {
            $array = array_column($tags, 'tag');
            if (!empty($array)) {
                $tagsStr = implode(',', $array);
            }
        }

        return $tagsStr;
    }

    //add or edit product tags
    public function saveProductTags($productId, $langId, $newTags = [], $deleteOldTags = true)
    {
        $existing = $this->builderProductTags->select('product_tags.tag_id')->join('tags', 'tags.id = product_tags.tag_id')
            ->where('product_tags.product_id', clrNum($productId))->where('tags.lang_id', clrNum($langId))->get()->getResultArray();
        $existingTagIds = array_column($existing, 'tag_id');

        if (empty($newTags)) {
            $tagsInput = inputPost('tags_' . $langId);
            $tagsInputDecoded = json_decode($tagsInput, true);
            if (is_array($tagsInputDecoded)) {
                foreach ($tagsInputDecoded as $item) {
                    if (isset($item['value']) && is_string($item['value'])) {
                        $newTags[] = trim($item['value']);
                    }
                }
            }
        }

        $newTagIds = [];
        foreach ($newTags as $tag) {
            $tagId = $this->addOrGetTag($tag, $langId);
            if ($tagId > 0) {
                $newTagIds[] = $tagId;
            }
        }

        //find removed tags and delete
        if ($deleteOldTags) {
            $toDelete = array_diff((array)$existingTagIds, (array)$newTagIds);
            if (!empty($toDelete) && countItems($toDelete) > 0) {
                $this->builderProductTags->where('product_id', clrNum($productId))->whereIn('tag_id', $toDelete)->delete();
            }
        }

        //find inserted tags
        $toInsert = array_diff((array)$newTagIds, (array)$existingTagIds);
        if (!empty($toInsert)) {
            foreach ($toInsert as $tagId) {
                $this->db->table('product_tags')->insert([
                    'tag_id' => $tagId,
                    'product_id' => $productId
                ]);
            }
        }
    }

    //delete product tags
    public function deleteProductTags($productId)
    {
        $this->builderProductTags->where('product_id', clrNum($productId))->delete();
    }

    //delete tag
    public function deleteTag($id)
    {
        $this->builder->where('id', clrNum($id))->delete();
        $this->builderProductTags->where('tag_id', clrNum($id))->delete();
        return true;
    }
}
