<?php namespace App\Models;

class ProfileModel extends BaseModel
{
    protected $builder;
    protected $builderShippingAddresses;
    protected $builderFollowers;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('users');
        $this->builderShippingAddresses = $this->db->table('shipping_addresses');
        $this->builderFollowers = $this->db->table('followers');
    }
    public function getProfile($id)
    {
        return $this->builder->where('id', user()->id)->get()->getRow();
    }
    //update profile
    public function editProfile($data)
    {
        $uploadModel = new UploadModel();
        $file = $uploadModel->uploadTempFile('file');
        if (!empty($file) && !empty($file['path'])) {
            $data['avatar'] = $uploadModel->uploadProfileImage($file['path']);
            $data['storage_avatar'] = $this->activeStorage;
            deleteStorageFile(user()->avatar, user()->storage_avatar);
            $uploadModel->deleteTempFile($file['path']);
        }
        $imageCover = $uploadModel->uploadTempFile('image_cover');
        if (!empty($imageCover) && !empty($imageCover['path'])) {
            $data['cover_image'] = $uploadModel->uploadProfileImage($imageCover['path'], 'cover');
            $data['storage_cover'] = $this->activeStorage;
            deleteStorageFile(user()->cover_image, user()->storage_cover);
            $uploadModel->deleteTempFile($imageCover['path']);
        }
        if (empty($data['show_email'])) {
            $data['show_email'] = 0;
        }
        if (empty($data['show_phone'])) {
            $data['show_phone'] = 0;
        }
        if (!isVendor()) {
            $authModel = new AuthModel();
            $data['username'] = $data['first_name'] . ' ' . $data['last_name'];
            if (!$authModel->isUniqueUsername($data['username'], user()->id)) {
                $data['username'] = $authModel->generateUniqueUsername($data['username']);
            }
        }
        if ($this->generalSettings->email_verification == 1) {
            if (user()->email != $data['email']) {
                $data['email_status'] = 0;
                $authModel = new AuthModel();
                $authModel->addActivationEmail(user(), $data['email']);
            }
        }
        return $this->builder->where('id', user()->id)->update($data);
    }

    //edit user
    public function editUser($id)
    {
        $user = getUser($id);
        if (!empty($user)) {
            $data = [
                'username' => inputPost('username'),
                'email' => inputPost('email'),
                'slug' => inputPost('slug'),
                'first_name' => inputPost('first_name'),
                'last_name' => inputPost('last_name'),
                'phone_number' => inputPost('phone_number'),
                'about_me' => inputPost('about_me'),
                'country_id' => inputPost('country_id'),
                'state_id' => inputPost('state_id'),
                'city_id' => inputPost('city_id'),
                'address' => inputPost('address'),
                'zip_code' => inputPost('zip_code')
            ];

            $settingsModel = new SettingsModel();
            $social = $settingsModel->getSocialMediaData(true);
            $data['social_media_data'] = !empty($social) ? serialize($social) : '';

            $uploadModel = new UploadModel();
            $tempFile = $uploadModel->uploadTempFile('file');
            if (!empty($tempFile) && !empty($tempFile['path'])) {
                $data['avatar'] = $uploadModel->uploadProfileImage($tempFile['path']);
                $data['storage_avatar'] = $this->activeStorage;
                $uploadModel->deleteTempFile($tempFile['path']);
                deleteStorageFile($user->avatar, $user->storage_avatar);
            }

            //set commission
            $data = setCommissionFormValues($data);

            return $this->builder->where('id', $user->id)->update($data);
        }
    }

    //update location
    public function updateLocation()
    {
        if (authCheck()) {
            $data = [
                'country_id' => !empty(inputPost('country_id')) ? inputPost('country_id') : 0,
                'state_id' => !empty(inputPost('state_id')) ? inputPost('state_id') : 0,
                'city_id' => !empty(inputPost('city_id')) ? inputPost('city_id') : 0,
                'address' => !empty(inputPost('address')) ? inputPost('address') : '',
                'zip_code' => !empty(inputPost('zip_code')) ? inputPost('zip_code') : '',
                'show_location' => !empty(inputPost('show_location')) ? 1 : 0
            ];
            return $this->builder->where('id', user()->id)->update($data);
        }
        return false;
    }

    //update shop settings
    public function updateShopSettings($shopName)
    {
        if (authCheck()) {
            if (inputPost('submit') == 'vacation_mode') {
                $data = [
                    'vacation_mode' => !empty(inputPost('vacation_mode')) ? 1 : 0,
                    'vacation_message' => inputPost('vacation_message')
                ];
            } else {
                $data = [
                    'about_me' => inputPost('about_me'),
                    'show_rss_feeds' => !empty(inputPost('show_rss_feeds')) ? 1 : 0
                ];
                if (isAdmin() || $this->generalSettings->vendors_change_shop_name == 1) {
                    $data['username'] = $shopName;
                }
            }
            return $this->builder->where('id', user()->id)->update($data);
        }
        return false;
    }

    //update vendor vat rates
    public function updateVendorVatRates()
    {
        $isFixedVat = 0;
        if (!empty(inputPost('is_fixed_vat'))) {
            $isFixedVat = 1;
        }
        if ($isFixedVat) {
            $data['is_fixed_vat'] = 1;
            $data['fixed_vat_rate'] = inputPost('fixed_vat_rate');
            if (empty($data['fixed_vat_rate']) || $data['fixed_vat_rate'] <= 0 || $data['fixed_vat_rate'] > 99.9) {
                $data['fixed_vat_rate'] = 0;
            }
        } else {
            $data['is_fixed_vat'] = 0;
            $data['vat_rates_data'] = '';
            $data['vat_rates_data_state'] = '';
            $inputCountry = inputPost('vat_data_country');
            $inputState = inputPost('vat_data_state');
            $arrayCountries = array();
            $arrayStates = array();
            if (!empty($inputCountry)) {
                $array = explode(',', $inputCountry);
                if (!empty($array)) {
                    foreach ($array as $item) {
                        $arraySub = explode(':', $item);
                        if (!empty($arraySub[0]) && !empty($arraySub[1])) {
                            $arrayCountries[$arraySub[0]] = $arraySub[1];
                        }
                    }
                }
            }
            if (!empty($inputState)) {
                $array = explode(',', $inputState);
                if (!empty($array)) {
                    foreach ($array as $item) {
                        $arraySub = explode(':', $item);
                        if (!empty($arraySub[0]) && !empty($arraySub[1])) {
                            $arrayStates[$arraySub[0]] = $arraySub[1];
                        }
                    }
                }
            }
            if (!empty($arrayCountries) && countItems($arrayCountries) > 0) {
                $data['vat_rates_data'] = serialize($arrayCountries);
            }
            if (!empty($arrayStates) && countItems($arrayStates) > 0) {
                $data['vat_rates_data_state'] = serialize($arrayStates);
            }
        }
        if (!empty($data)) {
            return $this->builder->where('id', user()->id)->update($data);
        }
        return false;
    }

    //update cash on delivery
    public function updateCashOnDelivery()
    {
        if (authCheck()) {
            $data = [
                'cash_on_delivery' => !empty(inputPost('cash_on_delivery')) ? 1 : 0
            ];
            return $this->builder->where('id', user()->id)->update($data);
        }
        return false;
    }

    //shipping address input values
    public function shippingAddressInputValues()
    {
        return [
            'title' => inputPost('title'),
            'first_name' => inputPost('first_name'),
            'last_name' => inputPost('last_name'),
            'email' => inputPost('email'),
            'phone_number' => inputPost('phone_number'),
            'address' => inputPost('address'),
            'country_id' => inputPost('country_id'),
            'state_id' => inputPost('state_id'),
            'province_id' => inputPost('province_id'),
            'province_name' => inputPost('province_name'),
            'city_id' => inputPost('city_id'),
            'city_name' => inputPost('city_name'),
            'district_id' => inputPost('district_id'),
            'district_name' => inputPost('district_name'),
            'city' => inputPost('city'),
            'zip_code' => inputPost('zip_code'),
            'address_type' => inputPost('address_type') == 'billing' ? 'billing' : 'shipping'
        ];
    }

    //add shipping address
    public function addShippingAddress()
    {
        $data = $this->shippingAddressInputValues();
        $data['user_id'] = user()->id;
        return $this->builderShippingAddresses->insert($data);
    }
    public function saveCourier()
    {
        $couriers = inputPost('couriers');
        // jika masih string JSON → decode dulu
        if (is_string($couriers)) {
            $couriers = json_decode($couriers, true);
        }

        foreach ($couriers as &$c) {
            if (isset($c['name'])) {
                $name = $c['name'];

                // hapus newline
                $name = str_replace("\n", '', $name);

                // + jadi spasi
                $name = str_replace('+', ' ', $name);

                // spasi ganda jadi 1
                $name = preg_replace('/\s+/', ' ', $name);

                // trim depan belakang
                $name = trim($name);

                $c['name'] = $name;
            }
        }
        unset($c); // best practice setelah foreach reference

        $row = $this->updateKurir(array("couriers"=>json_encode($couriers)),user()->id);
        print_r($row);
        
    }
    //edit shipping address
    public function editShippingAddress()
    {
        $id = inputPost('id');
        $row = $this->getShippingAddressById($id, user()->id);
        if (!empty($row) && user()->id == $row->user_id) {
            $data = $this->shippingAddressInputValues();

            return $this->builderShippingAddresses->where('id', $row->id)->update($data);
        }
        return false;
    }
    public function updateKurir($data,$userId)
    {
        return $this->builder->where('id', clrNum($userId))->update($data);
    }
    //get shipping address
    public function getShippingAddressById($addressId, $userId)
    {
        return $this->builderShippingAddresses->where('id', clrNum($addressId))->where('user_id', clrNum($userId))->get()->getRow();
    }

    //delete shipping address
    public function deleteShippingAddress()
    {
        $id = inputPost('id');
        $row = $this->getShippingAddressById($id, user()->id);
        if (!empty($row) && user()->id == $row->user_id) {
            return $this->builderShippingAddresses->where('id', $row->id)->delete();
        }
        return false;
    }

    //update update social media
    public function updateSocialMedia()
    {
        $settingsModel = new SettingsModel();
        $social = $settingsModel->getSocialMediaData(true);
        $data['social_media_data'] = !empty($social) ? serialize($social) : '';
        return $this->builder->where('id', user()->id)->update($data);
    }

    //change password
    public function changePassword()
    {
        $data = [
            'old_password' => inputPost('old_password'),
            'password' => inputPost('password'),
            'password_confirm' => inputPost('password_confirm')
        ];
        if (!empty(user()->password)) {
            if (!password_verify($data['old_password'], user()->password)) {
                setErrorMessage(trans("msg_wrong_old_password"));
                redirectToUrl(generateUrl('settings', 'change_password'));
            }
        }
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        if ($this->builder->where('id', user()->id)->update(['password' => $password, 'token' => ''])) {
            $userId = user()->id;
            $authModel = new AuthModel();
            $authModel->logout();
            $authModel->loginUser(getUser($userId));
            return true;
        }
        return false;
    }

    //add delete account request
    public function addDeleteAccountRequest($user)
    {
        if (!empty($user)) {
            $data = [
                'account_delete_req' => 1,
                'account_delete_req_date' => date('Y-m-d H:i:s')
            ];
            return $this->builder->where('id', $user->id)->update($data);
        }
        return false;
    }

    //follow user
    public function followUnfollowUser()
    {
        $data = [
            'following_id' => inputPost('user_id'),
            'follower_id' => user()->id
        ];
        $follow = $this->getFollow($data['following_id'], $data['follower_id']);
        if (empty($follow)) {
            $this->builderFollowers->insert($data);
        } else {
            $this->builderFollowers->where('id', $follow->id)->delete();
        }
    }

    //get shipping addresses
    public function getShippingAddresses($userId)
    {
        return $this->builderShippingAddresses->where('user_id', clrNum($userId))->get()->getResult();
    }

    //get first shipping addresses
    public function getFirstShippingAddress($userId, $addressType)
    {
        return $this->builderShippingAddresses->where('user_id', clrNum($userId))->where('address_type', cleanStr($addressType))->get(1)->getRow();
    }

    //follow
    public function getFollow($followingId, $followerId)
    {
        return $this->builderFollowers->where('following_id', clrNum($followingId))->where('follower_id', clrNum($followerId))->get()->getRow();
    }

    //is user follows
    public function isUserFollows($followingId, $followerId)
    {
        if (empty($this->getFollow($followingId, $followerId))) {
            return false;
        }
        return true;
    }

    //get followers
    public function getFollowers($followingId)
    {
        return $this->builderFollowers->select('users.*, (SELECT permissions FROM roles_permissions WHERE roles_permissions.id = users.role_id LIMIT 1) AS permissions')
            ->join('users', 'followers.follower_id = users.id')->where('following_id', clrNum($followingId))->get()->getResult();
    }

    //get followers count
    public function getFollowersCount($followingId)
    {
        return $this->builderFollowers->join('users', 'followers.follower_id = users.id')->select('users.*')->where('following_id', clrNum($followingId))->countAllResults();
    }

    //get followed users
    public function getFollowedUsers($followerId)
    {
        return $this->builderFollowers->select('users.*, (SELECT permissions FROM roles_permissions WHERE roles_permissions.id = users.role_id LIMIT 1) AS permissions')
            ->join('users', 'followers.following_id = users.id')->where('follower_id', clrNum($followerId))->get()->getResult();
    }

    //get following users
    public function getFollowingUsersCount($followerId)
    {
        return $this->builderFollowers->join('users', 'followers.following_id = users.id')->select('users.*')->where('follower_id', clrNum($followerId))->countAllResults();
    }
}
