<?php
class Zuora_Amendment extends Zuora_Object
{
    const TYPE_NAMESPACE = 'http://object.api.zuora.com/';
    protected $zType = 'Amendment';
 
    public $zRatePlanData;
 
    public function __construct(Zuora_RatePlan  $zRatePlan = null)
    {
		$this->zRatePlanData = new Zuora_RatePlanData($zRatePlan);
    }
    
    //public function __construct(Zuora_RatePlanData  $zRatePlanData = null)
    //{
        //$this->zRatePlanData = $zRatePlanData;
      //  $this->zRatePlanData = new Zuora_RatePlanData($zRatePlanData);
    // }
    public function getSoapVar()
    {
        return new SoapVar(
            array_merge(array(
                'RatePlanData'=>$this->zRatePlanData->getSoapVar()
				), (array)$this->_data),
            SOAP_ENC_OBJECT,
            $this->zType,
            self::TYPE_NAMESPACE
        );
	}
}
?>