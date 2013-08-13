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
                    $this->dbProvider->InsertTransaction($properties);
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
}
$ipn=new rednao_paypal_ipn(new wordpress_connection_provide(), new smart_donations_db_privider());
$ipn->ProcessCall();

?>