<?php

/**
 * Action Results 
 * 
 * Retrieve the overall results
 * 
 * @link http://getfrapi.com
 * @author Frapi <frapi@getfrapi.com>
 * @link /results
 */
class Action_Results extends Frapi_Action implements Frapi_Action_Interface
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
		
		$results = $beers->find(array(), array('_id', 'name', 'rating'))->sort(array('rating' => -1, 'name' => 1));
		
		return iterator_to_array($results);
    }

}

