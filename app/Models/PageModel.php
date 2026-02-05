<?php namespace App\Models;

class PageModel extends BaseModel
{
    protected $builder;
    protected $builderPagesVendor;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('pages');
        $this->builderPagesVendor = $this->db->table('pages_vendor');
    }

    //input values
    public function inputValues()
    {
        $data = [
            'lang_id' => inputPost('lang_id'),
            'title' => inputPost('title'),
            'slug' => inputPost('slug'),
            'description' => inputPost('description'),
            'keywords' => inputPost('keywords'),
            'page_content' => inputPost('page_content'),
            'page_order' => inputPost('page_order'),
            'visibility' => inputPost('visibility'),
            'title_active' => inputPost('title_active'),
            'location' => inputPost('location')
        ];
        return $data;
    }

    //add page
    public function addPage()
    {
        $data = $this->inputValues();
        if (empty($data['slug'])) {
            $data['slug'] = strSlug($data['title']);
        } else {
            $data['slug'] = removeSpecialCharacters($data['slug']);
            if (!empty($data['slug'])) {
                $data['slug'] = str_replace(' ', '-', $data['slug']);
            }
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->builder->insert($data)) {
            $lastId = $this->db->insertID();
            $this->updateSlug($lastId);
        }
        return true;
    }

    //edit page
    public function editPage($id)
    {
        $page = $this->getPageById($id);
        if (!empty($page)) {
            $data = $this->inputValues();
            return $this->builder->where('id', $page->id)->update($data);
        }
        return false;
    }

    //update slug
    public function updateSlug($id)
    {
        $page = $this->getPageById($id);
        if (empty($page)) {
            if (empty($page->slug) || $page->slug == '-') {
                $data = ['slug' => $page->id];
                $this->builder->where('id', $page->id)->update($data);
            } else {
                if (!empty($this->checkPageSlug($page->slug, $id))) {
                    $data = ['slug' => $page->slug . '-' . $page->id];
                    $this->builder->where('id', $page->id)->update($data);
                }
            }
        }
    }

    //check page slug
    public function checkPageSlug($slug, $id)
    {
        return $this->builder->where('slug', removeSpecialCharacters($slug))->where('id !=', clrNum($id))->get()->getRow();
    }

    //check page slug for product
    public function checkPageSlugForProduct($slug)
    {
        return $this->builder->where('slug', removeSpecialCharacters($slug))->get()->getRow();
    }

    //get menu links
    public function getMenuLinks($langId)
    {
        return getCacheData('menu_links_' . $langId, function () use ($langId) {
            return $this->builder->select('id, title, slug, page_order, location, page_default_name')->where('lang_id', clrNum($langId))->where('visibility', 1)->orderBy('page_order')->get()->getResult();
        }, 'static');
    }

    //get pages
    public function getPages()
    {
        return $this->builder->orderBy('page_order')->get()->getResult();
    }

    //get page
    public function getPage($slug)
    {
        return $this->builder->where('slug', strSlug($slug))->where('visibility', 1)->where('pages.lang_id', selectedLangId())->get()->getRow();
    }

    //get page by id
    public function getPageById($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get page by default name
    public function getPageByDefaultName($defaultName, $langId)
    {
        return $this->builder->where('page_default_name', cleanStr($defaultName))->where('visibility', 1)->where('lang_id', clrNum($langId))->get()->getRow();
    }

    //get sitemap pages
    public function getSitemapPages()
    {
        return $this->builder->where('pages.visibility', 1)->get()->getResult();
    }

    //delete page
    public function deletePage($id)
    {
        $page = $this->getPageById($id);
        if (!empty($page)) {
            return $this->builder->where('id', clrNum($id))->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Pages Vendor
     * --------------------------------------------------------------------
     */

    //add vendor pages
    public function addVendorPages($userId)
    {
        $data = [
            'user_id' => clrNum($userId),
            'content_shop_policies' => '',
            'status_shop_policies' => 0
        ];
        if (empty($this->getVendorPagesByUserId($userId))) {
            $this->builderPagesVendor->insert($data);
        }
    }

    //edit vendor pages
    public function editVendorPages($userId)
    {
        $pages = $this->getVendorPagesByUserId($userId);
        if (!empty($pages)) {
            $data = [
                'content_shop_policies' => inputPost('content_shop_policies'),
                'status_shop_policies' => inputPost('status_shop_policies') == 1 ? 1 : 0,
            ];
            return $this->builderPagesVendor->where('user_id', clrNum($userId))->update($data);
        }
        return false;
    }

    //get vendor pages by user id
    public function getVendorPagesByUserId($userId)
    {
        return $this->builderPagesVendor->where('user_id', clrNum($userId))->get()->getRow();
    }

}
