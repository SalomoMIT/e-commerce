<?php namespace App\Models;

class LocationModel extends BaseModel
{
    protected $builderCountries;
    protected $builderStates;
    protected $builderCities;

    public function __construct()
    {
        parent::__construct();
        $this->builderCountries = $this->db->table('location_countries');
        $this->builderStates = $this->db->table('location_states');
        $this->builderCities = $this->db->table('location_cities');
    }

    /*
     * --------------------------------------------------------------------
     * Country
     * --------------------------------------------------------------------
     */

    //add country
    public function addCountry()
    {
        $data = [
            'name' => inputPost('name'),
            'continent_code' => inputPost('continent_code'),
            'status' => inputPost('status')
        ];
        return $this->builderCountries->insert($data);
    }

    //update country
    public function editCountry($id)
    {
        $country = $this->getCountry($id);
        if (!empty($country)) {
            $data = [
                'name' => inputPost('name'),
                'continent_code' => inputPost('continent_code'),
                'status' => inputPost('status')
            ];
            return $this->builderCountries->where('id', $country->id)->update($data);
        }
        return false;
    }

    //get country
    public function getCountry($id)
    {
        return $this->builderCountries->where('id', clrNum($id))->get()->getRow();
    }

    //get active countries
    public function getActiveCountries()
    {
        return getCacheData('countries', function () {
            return $this->builderCountries->where('status', 1)->orderBy('name')->get()->getResult();
        }, 'static');
    }

    //get countries
    public function getCountries()
    {
        return $this->builderCountries->orderBy('name')->get()->getResult();
    }

    //get countries by continent
    public function getCountriesByContinent($continentCode)
    {
        return $this->builderCountries->where('continent_code', cleanStr($continentCode))->where('status', 1)->orderBy('name')->get()->getResult();
    }

    //get country count
    public function getCountryCount()
    {
        $this->filterCountries();
        return $this->builderCountries->countAllResults();
    }

    //get paginated countries
    public function getCountriesPaginated($perPage, $offset)
    {
        $this->filterCountries();
        return $this->builderCountries->orderBy('id')->limit($perPage, $offset)->get()->getResult();
    }

    //activate inactivate countries
    public function activateInactivateCountries($action)
    {
        $status = 1;
        if ($action == 'inactivate') {
            $status = 0;
        }
        $this->builderCountries->update(['status' => $status]);
    }

    //filter country
    public function filterCountries()
    {
        $q = inputGet('q');
        if (!empty($q)) {
            $this->builderCountries->like('name', removeSpecialCharacters($q));
        }
    }

    //delete country
    public function deleteCountry($id)
    {
        $country = $this->getCountry($id);
        if (!empty($country)) {
            return $this->builderCountries->where('id', $country->id)->delete();
        }
        return false;
    }

    //location settings
    public function updateLocationSettings()
    {
        $data = [
            'single_country_mode' => inputPost('single_country_mode'),
            'single_country_id' => inputPost('single_country_id')
        ];
        return $this->db->table('general_settings')->where('id', 1)->update($data);
    }

    /*
     * --------------------------------------------------------------------
     * State
     * --------------------------------------------------------------------
     */

    //add state
    public function addState()
    {
        $data = [
            'name' => inputPost('name'),
            'country_id' => inputPost('country_id')
        ];
        return $this->builderStates->insert($data);
    }

    //edit state
    public function editState($id)
    {
        $state = $this->getState($id);
        if (!empty($state)) {
            $data = [
                'name' => inputPost('name'),
                'country_id' => inputPost('country_id')
            ];
            return $this->builderStates->where('id', $state->id)->update($data);
        }
        return false;
    }

    //get states
    public function getStates()
    {
        return $this->builderStates->orderBy('name')->get()->getResult();
    }

    //get state
    public function getState($id)
    {
        return $this->builderStates->where('id', clrNum($id))->get()->getRow();
    }

    //build query state
    public function filterStates()
    {
        $countryId = inputGet('country');
        $q = removeSpecialCharacters(inputGet('q'));
        $this->builderStates->join('location_countries', 'location_states.country_id = location_countries.id');
        $this->builderStates->select('location_states.*, location_countries.name as country_name, location_countries.status as country_status');
        if (!empty($countryId)) {
            $this->builderStates->where('location_states.country_id', $countryId);
        }
        if (!empty($q)) {
            $this->builderStates->groupStart()->like('location_countries.name', $q)->orLike('location_states.name', $q)->groupEnd();
        }
    }

    //get state count
    public function getStateCount()
    {
        $this->filterStates();
        return $this->builderStates->countAllResults();
    }

    //get paginated states
    public function getStatesPaginated($perPage, $offset)
    {
        $this->filterStates();
        return $this->builderStates->orderBy('location_states.id')->limit($perPage, $offset)->get()->getResult();
    }

    //get states by country
    public function getStatesByCountry($countryId)
    {
        return $this->builderStates->where('country_id', clrNum($countryId))->orderBy('name')->get()->getResult();
    }

    //delete state
    public function deleteState($id)
    {
        $state = $this->getState($id);
        if (!empty($state)) {
            return $this->builderStates->where('id', $state->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * City
     * --------------------------------------------------------------------
     */

    //add city
    public function addCity()
    {
        $data = [
            'name' => inputPost('name'),
            'country_id' => inputPost('country_id'),
            'state_id' => inputPost('state_id')
        ];
        return $this->builderCities->insert($data);
    }

    //update city
    public function editCity($id)
    {
        $city = $this->getCity($id);
        if (!empty($city)) {
            $data = [
                'name' => inputPost('name'),
                'country_id' => inputPost('country_id'),
                'state_id' => inputPost('state_id')
            ];
            return $this->builderCities->where('id', $city->id)->update($data);
        }
        return false;
    }

    //get city
    public function getCity($id)
    {
        return $this->builderCities->where('id', clrNum($id))->get()->getRow();
    }

    //get cities
    public function getCities()
    {
        return $this->builderCities->orderBy('name')->get()->getResult();
    }

    //build query state
    public function filterCities()
    {
        $countryId = inputGet('country');
        $stateId = inputGet('state');
        $q = removeSpecialCharacters(inputGet('q'));
        $this->builderCities->join('location_countries', 'location_cities.country_id = location_countries.id');
        $this->builderCities->join('location_states', 'location_cities.state_id = location_states.id');
        $this->builderCities->select('location_cities.*, location_countries.name as country_name, location_states.name as state_name');
        if (!empty($countryId)) {
            $this->builderCities->where('location_cities.country_id', $countryId);
        }
        if (!empty($stateId)) {
            $this->builderCities->where('location_cities.state_id', $stateId);
        }
        if (!empty($q)) {
            $this->builderCities->groupStart()->like('location_cities.name', $q)->orLike('location_cities.name', $q)->groupEnd();
        }
    }

    //get city count
    public function getCityCount()
    {
        $this->filterCities();
        return $this->builderCities->countAllResults();
    }

    //get paginated cities
    public function getCitiesPaginated($perPage, $offset)
    {
        $this->filterCities();
        return $this->builderCities->orderBy('location_cities.id')->limit($perPage, $offset)->get()->getResult();
    }

    //get cities by country
    public function getCitiesByCountry($countryId)
    {
        return $this->builderCities->where('country_id', $countryId)->orderBy('name')->get()->getResult();
    }

    //get cities by state
    public function getCitiesByState($stateId)
    {
        return $this->builderCities->where('state_id', $stateId)->orderBy('name')->get()->getResult();
    }

    //delete city
    public function deleteCity($id)
    {
        $city = $this->getCity($id);
        if (!empty($city)) {
            return $this->builderCities->where('id', $city->id)->delete();
        }
        return false;
    }

    //get default location input
    public function getDefaultLocationInput($defaultLocation)
    {
        $str = '';
        if (!empty($defaultLocation->country_id)) {
            $select = 'location_countries.name AS country';
            if (!empty($defaultLocation->state_id)) {
                $select .= ',(SELECT location_states.name FROM location_states WHERE location_states.id = ' . clrNum($defaultLocation->state_id) . ') AS state';
            }
            if (!empty($defaultLocation->city_id)) {
                $select .= ',(SELECT location_cities.name FROM location_cities WHERE location_cities.id = ' . clrNum($defaultLocation->city_id) . ') AS city';
            }
            $row = $this->builderCountries->select($select)->where('id', clrNum($defaultLocation->country_id))->get()->getRow();
            if (!empty($row->city)) {
                $str .= $row->city . ', ';
            }
            if (!empty($row->state)) {
                $str .= $row->state . ', ';
            }
            if (!empty($row->country)) {
                $str .= $row->country;
            }
        }
        return $str;
    }

    //set default location
    public function setDefaultLocation()
    {
        if (inputPost('submit') == 'reset') {
            helperSetSession('mds_default_location', '');
        } else {
            $location = new \stdClass();
            $location->country_id = !empty(inputPost('country_id')) ? inputPost('country_id') : 0;
            $location->state_id = !empty(inputPost('state_id')) ? inputPost('state_id') : 0;
            $location->city_id = !empty(inputPost('city_id')) ? inputPost('city_id') : 0;
            helperSetSession('mds_default_location', serialize($location));
        }
    }
}