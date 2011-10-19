<?php

/**
 * Action Beers 
 * 
 * GET a list of all beers
 * 
 * @link http://getfrapi.com
 * @author Frapi <frapi@getfrapi.com>
 * @link /beers
 */
class Action_Beers extends Frapi_Action implements Frapi_Action_Interface
{

    /**
     * Required parameters
     * 
     * @var An array of required parameters.
     */
    protected $requiredParams = array();

    /**
     * The data container to use in toArray()
     * 
     * @var A container of data to fill and return in toArray()
     */
    private $data = array();

    /**
     * To Array
     * 
     * This method returns the value found in the database 
     * into an associative array.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Default Call Method
     * 
     * This method is called when no specific request handler has been found
     * 
     * @return array
     */
    public function executeAction()
    {
        throw new Frapi_Error('METHOD_NOT_ALLOWED');
    }

    /**
     * Get Request Handler
     * 
     * This method is called when a request is a GET
     * 
     * @return array
     */
    public function executeGet()
    {
		$mongo = new Mongo("mongodb://89fcf089:jg98mfsb2m4vck1lakcul9cscl@dbh63.mongolab.com:27637/orchestra_89fcf089_bf5d8");
		
		$db = $mongo->selectDB('orchestra_89fcf089_bf5d8');
		/*$db->dropCollection('beers');
		$db->createCollection('beers');*/

		$collection = $db->selectCollection('beers');
		$ratings = $db->selectCollection('ratings');
		
		/*$json =  file_get_contents("http://brewerydb.com/api/beers?apikey=82c54cb1efb67f50b65a34ed059e9af5&brewery_id=515&format=json");
		
		$data = json_decode($json);
		
		foreach ($data->beers->beer as $key => $value) {
			unset($data->beers->beer[$key]->brewery);
			unset($data->beers->beer[$key]->created);
			unset($data->beers->beer[$key]->updated);
			$data->beers->beer[$key]->_id = $data->beers->beer[$key]->id;
			$data->beers->beer[$key]->rating = 0;
			unset($data->beers->beer[$key]->id);
			
			$collection->save((array) $data->beers->beer[$key]);
		}*/
		
		$beers = iterator_to_array($collection->find(array(), array('_id', 'name'))->sort(array('name' => 1)));
		
		if (isset($_GET['user'])) {
			$user_ratings_result = $ratings->find(array('user' => $_GET['user']));
			if ($user_ratings_result) {
				$user_ratings = array();
				foreach ($user_ratings_result as $rating) {
					$user_ratings[$rating['beer']] = (int) $rating['rating'];
				}
				
				foreach ($beers as &$beer) {
					$beer['user_rating'] = (isset($user_ratings[$beer['_id']])) ? $user_ratings[$beer['_id']] : 0;
				}
			}
		}
		
		return $beers;
    }

}

