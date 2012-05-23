<?php
class Zuora_PreviewOptions extends Zuora_Object
{
	const TYPE_NAMESPACE = 'http://api.zuora.com/';
    protected $zType = 'PreviewOptions';
	
	public function getSoapVar()
    {
        return new SoapVar(
            (array)$this->_data,
            SOAP_ENC_OBJECT,
            $this->zType,
            self::TYPE_NAMESPACE
        );
    }
}
