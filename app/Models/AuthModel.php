<?php namespace App\Models;

class AuthModel extends BaseModel
{
    protected $builder;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('users');
    }

    //input values
    public function inputValues()
    {
        return [
            'email' => inputPost('email'),
            'first_name' => inputPost('first_name'),
            'last_name' => inputPost('last_name'),
            'password' => inputPost('password')
        ];
    }

    //login
    public function login()
    {
        $data = $this->inputValues();

        //rate limiting
        $attempts = helperGetSession('login_attempts') ?? 0;
        $lastTry = helperGetSession('last_login_try') ?? null;
        if ($attempts >= 5 && $lastTry !== null && (time() - $lastTry) < 300) { // 5 attempts / 5 minutes
            setErrorMessage(trans("too_many_attempts"));
            return false;
        }

        $user = $this->getUserByEmail($data['email']);
        if (empty($user) || !password_verify($data['password'], $user->password)) {
            helperSetSession('login_attempts', $attempts + 1);
            helperSetSession('last_login_try', time());
            setErrorMessage(trans("login_error"));
            return false;
        }

        if ((int)$user->email_status !== 1) {
            setErrorMessage(
                trans("msg_confirmed_required") .
                ' <a href="javascript:void(0)" class="color-link link-underlined link-mobile-alert" ' .
                'onclick="sendActivationEmail(\'' . esc($user->token) . '\', \'login\');">' .
                trans("resend_activation_email") . '</a>'
            );
            return false;
        }

        if ((int)$user->banned === 1) {
            setErrorMessage(trans("msg_ban_error"));
            return false;
        }

        helperDeleteSession('login_attempts');
        helperDeleteSession('last_login_try');

        $this->loginUser($user);
        return true;
    }

    //login user
    public function loginUser($user)
    {
        if (!empty($user)) {
            $authToken = $user->token;
            if (empty($authToken)) {
                $authToken = generateToken();
                $this->builder->where('id', $user->id)->update(['token' => $authToken]);
            }
            //regenerate session to prevent fixation
            $this->session->regenerate();
            // Set session data
            $this->session->set([
                'auth_user_id' => $user->id,
                'auth_token' => $authToken,
            ]);

            $this->addUserLoginActivity();

            //move guest cart
            $cartModel = new CartModel();
            $cartModel->mergeGuestCartToUser($user->id);
        }
    }

    //login with social provider
    public function loginWithSocialProvider($provider, $socialUser)
    {
        if (empty($socialUser) || empty($socialUser->email)) {
            return false;
        }

        $user = $this->getUserByEmail($socialUser->email);

        if (empty($user)) {
            $now = date('Y-m-d H:i:s');
            $displayName = !empty($socialUser->name) ? $socialUser->name : 'user-' . uniqid();
            $username = $this->generateUniqueUsername($displayName);
            $slug = $this->generateUniqueSlug($username);

            $data = [
                'email' => $socialUser->email,
                'email_status' => 1,
                'token' => generateToken(),
                'role_id' => $this->generalSettings->vendor_verification_system != 1 ? 2 : 3,
                'username' => $username,
                'first_name' => $socialUser->firstName ?? '',
                'last_name' => $socialUser->lastName ?? '',
                'slug' => $slug,
                'avatar' => '',
                'user_type' => $provider,
                'last_seen' => $now,
                'created_at' => $now
            ];

            $data[$provider . '_id'] = $socialUser->id ?? null;

            if ($this->builder->insert($data)) {
                $user = $this->getUserByEmail($socialUser->email);

                $avatarUrl = $socialUser->pictureURL ?? $socialUser->avatar ?? null;

                if (!empty($user) && !empty($avatarUrl)) {
                    $this->downloadSocialProfileImage($user, $avatarUrl);
                }
            }
        }

        if (empty($user)) {
            return false;
        }

        if ((int)$user->banned === 1) {
            setErrorMessage(trans("msg_ban_error"));
            return false;
        }

        $this->loginUser($user);
        return true;
    }

    //download social profile image
    public function downloadSocialProfileImage($user, $imgURL)
    {
        if (empty($user) || empty($imgURL)) {
            return;
        }

        $uploadModel = new UploadModel();
        $tempPath = $uploadModel->downloadTempImage($imgURL, 'profile_temp');

        if (!empty($tempPath) && file_exists($tempPath)) {
            $avatarPath = $uploadModel->uploadProfileImage($tempPath);

            if (!empty($avatarPath)) {
                $this->builder->where('id', $user->id)->update([
                    'avatar' => $avatarPath,
                    'storage_avatar' => $this->activeStorage
                ]);
            }

            $uploadModel->deleteTempFile($tempPath);
        }
    }

    //register
    public function register()
    {
        $data = $this->inputValues();

        //validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        //identity fields
        $data['username'] = $this->generateUniqueUsername($data['first_name'] . ' ' . $data['last_name']);
        $data['slug'] = $this->generateUniqueSlug($data['username']);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        //default values
        $now = date('Y-m-d H:i:s');
        $data['role_id'] = $this->generalSettings->vendor_verification_system != 1 ? 2 : 3;
        $data['user_type'] = 'registered';
        $data['banned'] = 0;
        $data['token'] = generateToken();
        $data['last_seen'] = $now;
        $data['created_at'] = $now;
        $data['email_status'] = $this->generalSettings->email_verification == 1 ? 0 : 1;

        //insert into DB
        if (!$this->builder->insert($data)) {
            return false;
        }

        $userId = $this->db->insertID();
        $this->updateSlug($userId);
        $user = $this->getUser($userId);

        if (empty($user)) {
            return false;
        }

        if ($this->generalSettings->email_verification == 1) {
            $this->addActivationEmail($user);
            redirectToUrl(generateUrl('register_success') . '?u=' . $user->token);
        } else {
            $this->loginUser($user);
        }

        return true;
    }

    //generate unique username
    public function generateUniqueUsername($username)
    {
        $newUsername = $username;
        $counter = 1;
        while (!empty($this->getUserByUsername($newUsername))) {
            $newUsername = $username . ' ' . $counter;
            $counter++;
            if ($counter > 5) {
                $newUsername = $username . '-' . uniqid();
                break;
            }
        }
        return $newUsername;
    }

    //generate unique slug
    public function generateUniqueSlug($username)
    {
        $slug = strSlug($username);
        $counter = 1;
        while (!empty($this->getUserBySlug($slug))) {
            $slug = strSlug($username . '-' . $counter);
            $counter++;
            if ($counter > 5) {
                $slug = strSlug($username . '-' . uniqid());
                break;
            }
        }
        return $slug;
    }

    //add user
    public function addUser()
    {
        $data = $this->inputValues();
        $data['username'] = $this->generateUniqueUsername($data['first_name'] . ' ' . $data['last_name']);
        $data['slug'] = $this->generateUniqueSlug($data['username']);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role_id'] = (int)inputPost('role_id') ?: 3;
        $data['user_type'] = 'registered';
        $data['banned'] = 0;
        $data['email_status'] = 1;
        $data['token'] = generateToken();
        $now = date('Y-m-d H:i:s');
        $data['last_seen'] = $now;
        $data['created_at'] = $now;

        return $this->builder->insert($data);
    }

    //logout
    public function logout()
    {
        $this->session->remove('auth_user_id');
        $this->session->remove('auth_token');
    }

    //reset password
    public function resetPassword($user)
    {
        if (!empty($user)) {
            $data = [
                'password' => password_hash(inputPost('password'), PASSWORD_DEFAULT),
                'token' => ''
            ];
            return $this->builder->where('id', $user->id)->update($data);
        }
        return false;
    }

    //update last seen time
    public function updateLastSeen()
    {
        if (authCheck()) {
            $this->builder->where('id', user()->id)->update(['last_seen' => date('Y-m-d H:i:s')]);
        }
    }

    //join affiliate program
    public function joinAffiliateProgram()
    {
        if (empty(inputPost('terms'))) {
            return false;
        }
        $data = [
            'first_name' => inputPost('first_name'),
            'last_name' => inputPost('last_name'),
            'phone_number' => inputPost('phone_number'),
            'country_id' => !empty(inputPost('country_id')) ? inputPost('country_id') : 0,
            'state_id' => !empty(inputPost('state_id')) ? inputPost('state_id') : 0,
            'city_id' => !empty(inputPost('city_id')) ? inputPost('city_id') : 0,
            'address' => !empty(inputPost('address')) ? inputPost('address') : '',
            'zip_code' => !empty(inputPost('zip_code')) ? inputPost('zip_code') : ''
        ];
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['phone_number']) || empty($data['country_id'])) {
            return false;
        }

        $data['is_affiliate'] = 1;
        return $this->builder->where('id', user()->id)->update($data);
    }

    //update affiliate settings
    public function updateAffiliateSettings()
    {
        /*
         * vendor_affiliate_status
         * 0: disabled
         * 1: all products
         * 2: selected products
         */
        $data = [
            'vendor_affiliate_status' => inputPost('vendor_affiliate_status'),
            'affiliate_commission_rate' => inputPost('affiliate_commission_rate'),
            'affiliate_discount_rate' => inputPost('affiliate_discount_rate')
        ];
        if ($data['vendor_affiliate_status'] != 0 && $data['vendor_affiliate_status'] != 1 && $data['vendor_affiliate_status'] != 2) {
            $data['vendor_affiliate_status'] = 0;
        }
        return $this->builder->where('id', user()->id)->update($data);
    }

    //query user
    public function buildQueryUser()
    {
        $this->builder->resetQuery();
        $this->builder->select('users.*, (SELECT permissions FROM roles_permissions WHERE roles_permissions.id = users.role_id LIMIT 1) AS permissions');
    }

    //get user by id
    public function getUser($id)
    {
        $this->buildQueryUser();
        return $this->builder->where('users.id', clrNum($id))->get()->getRow();
    }

    //get user by email
    public function getUserByEmail($email)
    {
        $this->buildQueryUser();
        return $this->builder->where('users.email', removeSpecialCharacters($email))->get()->getRow();
    }

    //get user by username
    public function getUserByUsername($username)
    {
        $this->buildQueryUser();
        return $this->builder->where('users.username', removeSpecialCharacters($username))->get()->getRow();
    }

    //get user by slug
    public function getUserBySlug($slug)
    {
        $this->buildQueryUser();
        return $this->builder->where('users.slug', removeSpecialCharacters($slug))->get()->getRow();
    }

    //get user by token
    public function getUserByToken($token)
    {
        $this->buildQueryUser();
        return $this->builder->where('users.token', removeSpecialCharacters($token))->get()->getRow();
    }

    //get users count
    public function getUsersCount()
    {
        return $this->builder->countAllResults();
    }

    //get users count by role
    public function getVendorsCount()
    {
        $this->filterVendors();
        return $this->builder->countAllResults();
    }

    //load more users
    public function loadMoreUsers($q, $perPage, $offset)
    {
        $q = cleanStr($q);
        if (!empty($q)) {
            $this->builder->like('username', $q)->orLike('email', $q);
        }
        return $this->builder->select('id, username, email')->orderBy('id')->limit($perPage, $offset)->get()->getResult();
    }

    //load users dropdown
    public function loadUsersDropdown($q)
    {
        $q = cleanStr($q);
        if (!empty($q)) {
            $this->builder->like('id', $q)->orLike('username', $q);
        }
        return $this->builder->select('id, username')->orderBy('username DESC')->limit(50)->get()->getResult();
    }

    //get paginated vendors
    public function getVendorsPaginated($perPage, $offset)
    {
        $this->filterVendors();
        return $this->builder->orderBy('num_products DESC, created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //filter vendor
    public function filterVendors()
    {
        $q = removeSpecialCharacters(inputGet('q'));
        $isAffiliate = clrNum(inputGet('affiliate')) == 1 ? 1 : 0;

        $this->builder->select('users.*, (SELECT COUNT(id) FROM products WHERE users.id = products.user_id AND products.is_active = 1) AS num_products')->where('banned', 0);
        if ($this->generalSettings->vendor_verification_system == 1) {
            $this->builder->where('EXISTS (SELECT 1 FROM products WHERE users.id = products.user_id AND products.is_active = 1)');
        }
        if (!empty($q)) {
            $this->builder->groupStart()->like('users.username', cleanStr($q))->groupEnd();
        }
        if ($isAffiliate == 1) {
            $this->builder->where('users.is_affiliate', 1);
        }

        //filter by location
        $defaultLocation = getContextValue('defaultLocation');
        if (!empty($defaultLocation->country_id)) {
            $this->builder->where('users.country_id', clrNum($defaultLocation->country_id));
        }
        if (!empty($defaultLocation->state_id)) {
            $this->builder->where('users.state_id', clrNum($defaultLocation->state_id));
        }
        if (!empty($defaultLocation->city_id)) {
            $this->builder->where('users.city_id', clrNum($defaultLocation->city_id));
        }
    }

    //get latest users
    public function getLatestUsers($limit)
    {
        return $this->builder->orderBy('id DESC')->get(clrNum($limit))->getResult();
    }

    //update slug
    public function updateSlug($id)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            return;
        }

        $slug = $user->slug;
        if (empty($slug) || $slug === '-') {
            $slug = 'user-' . $user->id;
        } elseif (!$this->isSlugUnique($slug, $id)) {
            $slug = $slug . '-' . $user->id;
        } else {
            return;
        }
        $this->builder->where('id', $user->id)->update(['slug' => $slug]);
    }

    //is slug unique
    public function isSlugUnique($slug, $id)
    {
        $exists = $this->builder->where('id !=', clrNum($id))->where('slug', cleanStr($slug))->get()->getRow();

        return empty($exists);
    }

    //check if email is unique
    public function isEmailUnique($email, $userId = 0)
    {
        $user = $this->getUserByEmail($email);
        if (empty($user)) {
            return true;
        }

        return (int)$user->id === (int)$userId;
    }

    //check if username is unique
    public function isUniqueUsername($username, $userId = 0)
    {
        $user = $this->getUserByUsername($username);
        if (empty($user)) {
            return true;
        }

        return (int)$user->id === (int)$userId;
    }

    //update user token
    public function updateUserToken($id, $token)
    {
        if (empty($id) || empty($token)) {
            return false;
        }

        return $this->builder->where('id', clrNum($id))->update(['token' => $token]);
    }

    //verify email
    public function verifyEmail($user)
    {
        if (!$user || empty($user->id)) {
            return false;
        }

        return $this->builder->where('id', $user->id)->update(['email_status' => 1, 'token' => generateToken()]);
    }

    //ban user
    public function banUser($id)
    {
        $user = $this->getUser($id);
        if (!$user) {
            return false;
        }

        $newStatus = ($user->banned == 1) ? 0 : 1;
        if ((int)$user->banned === $newStatus) {
            return true;
        }

        return $this->builder->where('id', $user->id)->update(['banned' => $newStatus]);
    }

    //add delete user affiliate program
    public function addDeleteUserAffiliateProgram($id)
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            return false;
        }

        $newStatus = ($user->is_affiliate == 1) ? 2 : 1;
        if ((int)$user->is_affiliate === $newStatus) {
            return true;
        }

        return $this->builder->where('id', $user->id)->update(['is_affiliate' => $newStatus]);
    }

    //get user emails by ids
    public function getUserEmailsByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }

        if (empty($ids)) {
            return [];
        }

        $rows = $this->builder->select('email')->whereIn('id', $ids)->get()->getResult();
        if (empty($rows)) {
            return [];
        }

        return array_map(fn($item) => $item->email, $rows);
    }

    //delete cover image
    public function deleteCoverImage()
    {
        if (!authCheck()) {
            return false;
        }

        $user = user();
        if (!empty($user->cover_image)) {
            deleteStorageFile($user->cover_image, $user->storage_cover);
        }

        return $this->builder->where('id', $user->id)->update(['cover_image' => '', 'storage_cover' => 'local']);
    }

    //delete user
    public function deleteUser($id): bool
    {
        $user = $this->getUser($id);
        if (empty($user)) {
            return false;
        }

        $role = $this->db->table("roles_permissions")->where('id', $user->role_id)->get()->getRow();
        if (!empty($role) && $role->is_super_admin) {
            return false;
        }

        $this->db->transStart();

        deleteStorageFile($user->avatar, $user->storage_avatar);

        $this->db->table('comments')->where('user_id', $user->id)->delete();
        $this->db->table('reviews')->where('user_id', $user->id)->delete();
        $this->db->table('user_login_activities')->where('user_id', $user->id)->delete();
        $this->db->table('wishlist')->where('user_id', $user->id)->delete();
        $this->db->table('users_membership_plans')->where('user_id', $user->id)->delete();

        $productAdminModel = new ProductAdminModel();
        $products = $this->db->table('products')->where('user_id', $user->id)->get()->getResult();
        foreach ($products as $product) {
            $productAdminModel->deleteProductPermanently($product->id);
        }

        $this->builder->where('id', $user->id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    //add activation email
    public function addActivationEmail($user, $email = null)
    {
        if ($this->generalSettings->email_verification != 1 || empty($user)) {
            return;
        }

        $email = $email ?: $user->email;
        $token = $user->token ?: generateToken();

        if (empty($user->token)) {
            $this->updateUserToken($user->id, $token);
        }

        addToEmailQueue([
            'email_type' => 'activation',
            'email_address' => $email,
            'email_subject' => trans("confirm_your_account"),
            'email_priority' => 1,
            'template_path' => 'email/main',
            'email_data' => serialize([
                'content' => trans("msg_confirmation_email"),
                'url' => base_url('confirm-account?token=' . $token),
                'buttonText' => trans("confirm_your_account"),
            ]),
        ]);
    }

    //add user login activity
    public function addUserLoginActivity()
    {
        if (!empty($this->session->get('auth_user_id')) && !empty($this->session->get('auth_token'))) {
            $data = [
                'user_id' => clrNum($this->session->get('auth_user_id')),
                'ip_address' => getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('user_login_activities')->insert($data);
        }
    }
}
