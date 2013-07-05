<?php namespace Gewora\Envato;

/**
 * Main file of the package
 * 
 *
 * @package Gewora/Envato
 * @author Michael Wihl <admin@gewora.net>
 * @copyright Copyright (c) 2013 by Gewora Project Team
 * @license BSD-3-Clause
 * @version 1.0.0
 * @access public
 */

use Illuminate\Config\Repository;

class Envato
{    
    /*
    * Holds the Config Instance
    *
    * @var Illuminate\Config\Repository
    */
    public $config;   
    
    public function __construct(Repository $config) 
    {
        // Fetch the config data and set up the required url´s
        $this->config = $config;
        $this->username = $this->config->get('envato::username');
        $this->api_key = $this->config->get('envato::api_key');
        $this->private_url = 'http://marketplace.envato.com/api/edge/' .$this->username. '/' .$this->api_key. '/';
        $this->public_url = 'http://marketplace.envato.com/api/edge/set.json';
    }  
    
    /*
    * Retrieve the balance on your account.
    *
    * @return string
    */    
    public function balance()
    {
       $balance = $this->get_private_data('vitals');
       return $balance->balance;
    }    
    
    /*
    * Retrieve details for your most recent sales.
    *
    * @param int $limit  - Optional - The number of sales to return.
    * @return array A list of your recent sales.
    */
    public function recent_sales($limit = null)
    {
        $sales = $this->get_private_data('recent-sales');
        return $this->set_limit($sales, $limit);
    } 
    
    /*
    * Retrieve your account information - Balance, Country, Name, etc.
    *
    * @return array
    */
    public function account_information()
    {
        return $this->get_private_data('account');
    }
    
    /*
    * Retrieve monthly stats/sales - Number of sales, monthly income, etc.
    * 
    * @param int $limit  - Optional - The number of months to return.
    * @return array
    */
    public function earnings_and_sales_by_month($limit = null)
    {
        $earnings = $this->get_private_data('earnings-and-sales-by-month');
        return $this->set_limit($earnings, $limit);
    }  
    
    /*
    * Perform a search on all envato marketplaces, or a specific one.
    *
    * @param string $search_query What do you want to search for?
    * @param string - Optional - $marketplace_name The name of the marketplace you want to search in.
    * @param string $type - Optional - The item type/category. See the search options on marketplace.
    * @param integer $limit - Optional - The number of items to return.
    * @return array
    */
   public function search($search_query = null, $marketplace_name = '', $type = '', $limit = null)
   {
        if(is_null($search_query)) return false;

        // We can't use spaces and need to replace them with pipes.
        else $search_query = preg_replace('/\s/', '|', $search_query);

        $url = preg_replace('/set/i', 'search:' . $marketplace_name . ',' . $type . ',' . $search_query, $this->public_url );
        $search_results = $this->curl($url)->search;
        return $this->set_limit($search_results, $limit);
    }

    /*
    * Returns the featured item, featured author, and free file of the month for the given marketplace.
    *
    * @param string $marketplace_name - The desired marketplace.
    * @return array
    */
    public function featured_items($marketplace = 'themeforest')
    {
        $url = preg_replace('/set/i', 'features:' . $marketplace, $this->public_url);
        return $this->fetch($url, 'features');
    }    
    
    /*
    * Retrieve the details for a specific marketplace item.
    *
    * @param string $item_id - The id of the item you want to retrive the details
    * @return object
    */
    public function item_details($item_id)
    {
        $url = preg_replace('/set/i', 'item:' . $item_id, $this->public_url);
        return $this->fetch($url, 'item');
    }  
    
    /*
    * This function is similar to new_files, but focuses on a specific author's files.
    *
    * @param string $username - The desired username.
    * @param string $marketplace_name - The desired marketplace.
    * @param int $limit  - Optional - The number of files to return.
    * @return array
    */
    public function new_files_from_user($username = null, $marketplace_name = 'themeforest', $limit = null)
    {
        if(is_null($username)) $username = $this->username;
        $url = preg_replace('/set/i', 'new-files-from-user:' . $username . ',' . $marketplace_name, $this->public_url);
        return $this->set_limit( $this->fetch($url, 'new-files-from-user'), $limit );
    }
    
    /*
    * Retrieve public user data for a specific username
    *
    * @param string $username
    * @return array
    */
    public function public_user_data($username = null)
    {
        if(is_null($username)) $username = $this->username;
        $url = preg_replace('/set/i', 'user:' . $username, $this->public_url);
        return $this->fetch($url, 'user');
    }    
    
    /*
    * Retrieve the most popular files from the previous week.
    *
    * @param string $marketplace_name - Desired marketplace.
    * @param int $limit  - Optional - The number of items to return.
    * @return array
    * week.
    */
    public function most_popular($marketplace_name = 'themeforest')
    {
        $url = preg_replace('/set/i', 'popular:' . $marketplace_name, $this->public_url);
        return $this->fetch($url, 'popular');
    }
    
    /*
    * Retrieves the new files from a specific marketplaces and category.
    *
    * @param string $marketplace_name - Marketplace name.
    * @param string $category  - The name of the category from which you want the new files
    * @param int $limit  - Optional - The number of files to return.
    * @return array
    */
    public function new_files($marketplace_name = 'themeforest', $category = 'wordpress', $limit = null)
    {
        $url = preg_replace('/set/i', 'new-files:' . $marketplace_name . ','. $category, $this->public_url);
        $results = $this->fetch($url, 'new-files');

        if ($results)
        {
            return $this->set_limit($results, $limit);
        } else {
            return null;
        }
    }
    
    /*
    * Retrieve an array of all items in the desired collection.
    *
    * @param string $collection_id - The id of the requested collection. You find the id in the url of the collection
    * @return array
    */
    public function collection($collection_id = null)
    {
        if(is_null($collection_id)) return false;
        $url = preg_replace('/set/i', 'collection:' . $collection_id, $this->public_url);
        return $this->fetch($url, 'collection');
    }   
    
    /* 
    * Verify if a person purchased your item.
    *
    * @param string $purchase_code - The buyer's purchase code
    * @return object|bool - If purchased, returns an object containing the purchase details.
    */
    public function verify_purchase($purchase_code)
    {
        $verify = $this->get_private_data('verify-purchase', $purchase_code);
        if(!isset($verify->buyer)) return FALSE;       
        return $verify;
    }   
    
    /* 
    * Retrieve the infomrations to the given set
    *
    * @param string $set - The desired set
    * @return string|object|array
    */
    protected function get_private_data($set, $purchase_code = null)
    {
        // Generate the complete url
        if(is_null($purchase_code)) 
        {
            $url = $this->private_url . $set . ':' . '.json';
        } else {
            $url = $this->private_url . $set . ':' . $purchase_code . '.json';
        }
        
        // Fetch the data
        $fetch = $this->fetch($url);

        if (isset($fetch->error))
        {
            return 'Username, API Key, or purchase code is invalid.';
        }
      
        return $fetch->$set;        
    }
    
    /*
    * Set a limit on the retrieved data
    *
    * @param array $data  - The original array.
    * @param int $limit - Specifies the number of array items in the result.
    * @return array
    */
    public function set_limit($data, $limit)
    {
        if (!is_int($limit)) return $data;

        // Make sure that there are enough items to filter through...
        if ($limit > count($orig_arr)) $limit = count($data);

        $data_new = array();
        for ( $i = 0; $i <= $limit - 1; $i++ ) {
            $data_new[] = $data[$i];
        }
        return $data_new;
    }    
    
    /*
    * Fetches the data from the Envato API.
    *
    * @param string $url - The full url for the API call.
    * @param string $set - Optional - The name of the set to retrieve.
    */    
    protected function fetch($url, $set = null)
    {
         $result = $this->curl($url);

        if ($result) {
            if(isset($set)) 
            {
                $result = $result->{$set};
            }             
         } else {
             exit('Could not retrieve data.');
         }

         return $result;
    }    
    
    /**
    * General function which fetches the desired data using curl
    *
    * @param string $url - The url to fetch.
    * @return object
    */    
    protected function curl($url)
    {
        if(empty($url)) return false;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data);

        return $data; // string or null
    }
}