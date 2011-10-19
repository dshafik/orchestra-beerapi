<?php
/**
 * Action Beer 
 * 
 * Get a list of, or details on a single beer. Post a new rating (between 1 and 5)
 * 
 * @link http://getfrapi.com
 * @author Frapi <frapi@getfrapi.com>
 * @link /beer/:id
 */
class Action_Beer extends Frapi_Action implements Frapi_Action_Interface
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
		$beers = $db->selectCollection('beers');
		
		$beer = $beers->findOne(array('_id' => $this->getParam('id')));
		
		if (!$beer) {
			throw new Frapi_Error('BEER_NOT_FOUND');
		}
		
		$ratings = $db->selectCollection('ratings');
		if (isset($_GET['user'])) {
			$user_rating = $ratings->findOne(array('user' => $_GET['user'], 'beer' => $this->getParam('id')));
			
			$beer['user_rating'] = ($user_rating) ? (int) $user_rating['rating'] : 0;
		}
		
		return $beer;
    }

    /**
     * Post Request Handler
     * 
     * This method is called when a request is a POST
     * 
     * @return array
     */
    public function executePost()
    {
        if (!isset($_POST['rating']) || !isset($_POST['user'])) {
			throw new Frapi_Error('MISSING_REQUIRED_PARAM');
		}
		
		if ($_POST['rating'] < 1 || $_POST['rating'] > 5) {
			throw new Frapi_Error('INVALID_RATING');
		}
		
		$mongo = new Mongo("mongodb://89fcf089:jg98mfsb2m4vck1lakcul9cscl@dbh63.mongolab.com:27637/orchestra_89fcf089_bf5d8");
		$db = $mongo->selectDB('orchestra_89fcf089_bf5d8');
		
		$beers = $db->selectCollection('beers');
		$beer = $beers->findOne(array('_id' => $this->getParam('id')));
		
		if (!$beer) {
			throw new Frapi_Error('BEER_NOT_FOUND');
		}
		
		$ratings = $db->selectCollection('ratings');
		
		$ratings->update(
					array('user' => $_POST['user'], 'beer' => $this->getParam('id')),
					array('$set' => array('rating' => $_POST['rating'])),
					array('upsert' => true, 'fsync' => true)
				);
		
		
		// update the beers average
		$total_rating = 0;
		$votes = 0;
		
		foreach ($ratings->find() as $rating) {
			$votes++;
			$total_rating += $rating['rating'];
		}
		
		$average_rating = $total_rating / $votes;
		
		// Round to the nearest 0.5
		$temp1 = $average_rating * 2;
		$temp2 = round($temp1, 0);
		$average_rating = $temp2 / 2;
		
		$beers->update(
				array('_id' => $this->getParam('id')),
				array('$set' => array("rating" => $average_rating))
			);
		
		$beer = $beers->findOne(array('_id' => $this->getParam('id')));
		
		if (!$beer) {
			throw new Frapi_Error('BEER_NOT_FOUND');
		}
		
		$beer['user_rating'] = $_POST['rating'];
		
		return $beer;
    }

}

