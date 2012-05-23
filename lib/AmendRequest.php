<?php
class Zuora_AmendRequest extends Zuora_Object
{
    protected $zType = 'AmendRequest';
    
    public function __construct(
         Zuora_Amendment $zAmendment,
        Zuora_AmendOptions $zAmendOptions = null,
        Zuora_PreviewOptions $zPreviewOptions = null
    )
    {
        $this->Amendment = $zAmendment;
        if (isset($zAmendOptions)) {
            $this->AmendOptions = $zAmendOptions;
        }
        if (isset($zPreviewOptions)) {
            $this->PreviewOptions = $zPreviewOptions;
        }
    }
	
	public function getSoapVar()
    {
        return new SoapVar(
            array( 
                'Amendment'=>$this->Amendment->getSoapVar(),
                NULL,
                NULL
				),
            SOAP_ENC_OBJECT,
            $this->zType,
            self::TYPE_NAMESPACE
        );
	}
}
