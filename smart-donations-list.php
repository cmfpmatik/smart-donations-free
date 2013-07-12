


<?php
if(!defined('ABSPATH'))
    die('Forbidden');

$action=$_GET['action'];
if($action==="add"){
    include(SMART_DONATIONS_DIR.'/smart-donations-add-new.php');
    return;
}

echo "<h1>Donation Buttons</h1>";
echo sprintf('<h2 ><a style="color:blue; text-decoration: underline;" href="?page=%s&action=%s">Add New</a></h2>',$_REQUEST['page'],'add');

require_once("smart-donations-helpers.php");



$donation_id=$_GET['id'];



if($action!=null&&$donation_id!=null)
{
    global $wpdb;

    if($action==="delete")
    {
        $wpdb->query($wpdb->prepare("delete from ".SMART_DONATIONS_TABLE_NAME." WHERE donation_id=$donation_id"));
        delete_transient("rednao_smart_donations_donation_$donation_id");
    }

    if($action==="edit")
    {
        $result=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".SMART_DONATIONS_TABLE_NAME." WHERE donation_id=$donation_id"));

        if(count($result)>0)
        {
            $result=$result[0];
            $options=rednao_smart_donations_json_object($result->options,$result->styles);

            $script=<<<EOF
                        <script type="text/javascript" language="javascript">
                            var smartDonationsSavedId="%s";
                            var smartDonationsSavedEmail="%s";
                            var smartDonationsSavedName="%s";
                            var smartDonationsSavedReturningUrl="%s";
                            var smartDonationsSavedOptions=jQuery.parseJSON('%s');
                            var smartDonationsDonationProvider="%s";
                        </script>
EOF;
            echo sprintf($script,$result->donation_id,$result->email,$result->donation_name,$result->returning_url,$options,$result->donation_provider);
            include(SMART_DONATIONS_DIR.'/smart-donations-add-new.php');
            return;

        }


    }
}


if(!class_exists('WP_LIST_TABLE'))
{
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}


class Donations extends WP_List_Table
{
    function get_columns()
    {
        return array(
          donation_name=>'Donation Name',
          email=>'Email',
          donation_type=>'Type',
          donation_id=>'Donation Id'
        );
    }

    function prepare_items()
    {
        $this->_column_headers=array($this->get_columns(),array(),$this->get_sortable_columns());
        global $wpdb;
        $this->items=$result=$wpdb->get_results("SELECT donation_id,donation_name,email,donation_type FROM ".SMART_DONATIONS_TABLE_NAME);
    }

    function get_sortable_columns()
    {

    }

    function column_default($item, $column_name)
    {
        return $item->$column_name;
    }

    function column_donation_name($item) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&id=%s&action=%s">Edit</a>',$_REQUEST['page'],$item->donation_id,'edit'),
            'delete'    => sprintf('<a href="?page=%s&id=%s&action=%s">Delete</a>',$_REQUEST['page'],$item->donation_id,'delete'),
        );

        return sprintf('%1$s %2$s', $item->donation_name, $this->row_actions($actions) );
    }
}

$donationList=new Donations();
$donationList->prepare_items();
$donationList->display();

?>




