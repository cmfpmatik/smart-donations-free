<?php
require_once('../../../../wp-config.php');
require_once('wordpress_connection_provide.php');
require_once('smart_donations_db_privider.php');
class rednao_paypal_ipn {

    /** @var  connection_provider_base */
    private $connectionProvider;
    /**
     * @var smart_donations_db_privider
     */
    private $dbProvider;


    function __construct(connection_provider_base $connectionProvider,smart_donations_db_privider $dbProvider){
        $this->connectionProvider=$connectionProvider;
        $this->dbProvider = $dbProvider;
    }

    public function ProcessCall()
    {
        if($this->connectionProvider->IsValid())
        {
            $properties=array();
            if (isset($_POST['txn_id'])) {
                $properties['txn_id']=$_POST['txn_id'];
            }else
                $properties['txn_id']='';


            if (isset($_POST['payer_email'])) {
                $properties['payer_email']=$_POST['payer_email'];
            }else
                $properties['payer_email']='';


            if (isset($_POST['first_name'])) {
                $properties['first_name']=$_POST['first_name'];
            }else
                $properties['first_name']='';


            if (isset($_POST['last_name'])) {
                $properties['last_name']=$_POST['last_name'];
            }else
                $properties['last_name']='';

            if (isset($_POST['mc_fee'])) {
                $properties['mc_fee']=$_POST['mc_fee'];
            }else
                $properties['mc_fee']='';

            if (isset($_POST['mc_gross'])) {
                $properties['mc_gross']=$_POST['mc_gross'];
            }else
                $properties['mc_gross']='';


            if (isset($_POST['payment_date'])) {
                $properties['date']=$_POST['payment_date'];
            }else
                $properties['date']='';


            if (isset($_POST['additional_fields'])) {
                $properties['additional_fields']=$_POST['additional_fields'];
            }else
                $properties['additional_fields']='';


            if (isset($_POST['custom'])) {
                $properties['campaign_id']=$_POST['custom'];
            }else
                $properties['campaign_id']='';

            if (isset($_POST["receiver_email"])) {
                $receiverEmail=$_POST["receiver_email"];
            }else
                $receiverEmail='';

            if($properties['campaign_id']!==null)
            {
                $campaign_id=$properties['campaign_id'];
                global $wpdb;
                $result=$wpdb->get_results($wpdb->prepare("select progress_id from ".SMART_DONATIONS_PROGRESS_TABLE." where campaign_id=%d",$campaign_id));
                foreach($result as $key=>$value)
                {
                    delete_transient("rednao_smart_donations_progress_$value->progress_id");
                }
            }

            if($this->DonationWasReceived())
            {
                if($this->ReceiverEmailIsValid($receiverEmail))
                {
                    $properties['status']='c';
                    $formId="";
                    if(!is_numeric($properties['campaign_id']))
                    {
                        $formString=rawurldecode($properties['campaign_id']);
                        parse_str($formString,$formStringParameters);
                        if(sizeof($formStringParameters)==2)
                        {
                            $properties['campaign_id']=$formStringParameters['campaign_id'];
                            $formId= $formStringParameters['formId'];
                        }else
                        {
                            this.SendFormError($properties['payer_email'],'the parameters sent by paypal are corrupt',$properties);
                            return;
                        }
                    }

                    if($this->dbProvider->InsertTransaction($properties))
                    {
                        $campaign_id=$properties['campaign_id'];
                        if($campaign_id>0)
                        {
                            global $wpdb;
                            $results=$wpdb->get_results($wpdb->prepare("SELECT email_subject,thank_you_email FROM ".SMART_DONATIONS_CAMPAIGN_TABLE." where campaign_id=%d",$campaign_id));

                            if(count($results)>0)
                            {
                                $result=$results[0];
                                if($result->email_subject!=null){
                                    try
                                    {
                                        $headers  = 'MIME-Version: 1.0'."\r\n";
                                        $headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
                                        wp_mail($_POST['receiver_email'],$result->email_subject,$result->thank_you_email,$headers);
                                    }catch(Exception $e)
                                    {
                                        $this->SendFormError($e->getMessage(),$properties);
                                    }
                                }
                            }
                        }
                    }

                    if($formId!=null)
                    {
                        $this->ProcessForm($properties,$formId);
                    }
                }
            }

            if($this->DonationWasRefunded())
            {

                if (isset($_POST['parent_txn_id'])) {
                    $this->dbProvider->RefundTransaction($_POST['parent_txn_id']);
                }

            }

        }
    }


    private function DonationWasReceived()
    {
        if (isset($_POST['payment_status'])) {
            return $_POST['payment_status']==="Pending"||$_POST['payment_status']=="Completed";
        }
        return false;



    }

    private function DonationWasRefunded()
    {
        if (isset($_POST['payment_status'])) {
            $status=strtolower($_POST['payment_status']);
            return $status==="refunded"||$status=="denied"||$status=="expired"||$status=="failed"||$status=="reversed"||$status=="voided";
        }
        return false;
    }

    private function ReceiverEmailIsValid($receiverEmail)
    {
        global $wpdb;
        $count=$wpdb->get_var($wpdb->prepare("select count(*) from ".SMART_DONATIONS_TABLE_NAME." where email=%s",$receiverEmail));

        return $count>0;
    }

    private function ProcessForm($properties,$formId)
    {
        $form=get_transient($formId);
        if($form==false)
        {
            this.SendFormError('The submitted form was not found, that means this transaction was processed 3 days after the payment',$properties);
            return;
        }




        $formElementsValues=explode('&',$form);

        try
        {
            $this->SendFormEmail($formElementsValues,$properties);

        }catch(Exception $e)
        {
            $this->SendFormError($e->getMessage(),$properties);
        }



    }

    private function SendFormError($error,$properties)
    {

    }

    private function SendFormEmail($formElementsValues,$properties)
    {
        $email='<table border="1" cellspacing="1">';

        $email.="<tr><td style='padding:10px'><b>".Donor."</b></td><td style='padding:10px'>". $properties['payer_email']."</td></tr>";

        foreach($formElementsValues as $value)
        {
            $splitValue=explode('=',$value);

            $label=rawurldecode($splitValue[0]);
            $email.="<tr><td style='padding:10px'><b>".htmlentities($label)."</b></td>";
            if(sizeof($splitValue)==2)
            {
                $value=explode(',',$splitValue[1]);


                if(sizeof($value)>=2)
                {
                    $email.="<td><table>";
                    for($i=0;$i<sizeof($value);$i++)
                    {
                        $email.="<tr><td style='padding:10px'>".htmlentities(rawurldecode($value[$i]))."</td></tr>";
                    }
                    $email.="</table></td>";
                }else
                    $email.="<td style='padding:10px'>".htmlentities(rawurldecode($value[0]))."</td>";
            }

            $email.="</tr>";
        }

        $email.="</table>";
        try
        {
            $headers  = 'MIME-Version: 1.0'."\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
            wp_mail($_POST['receiver_email'],'Donation Received',$email,$headers);
        }catch(Exception $e)
        {
            $this->SendFormError($e->getMessage(),$properties);
        }
    }

}
$ipn=new rednao_paypal_ipn(new wordpress_connection_provide(), new smart_donations_db_privider());
$ipn->ProcessCall();

?>