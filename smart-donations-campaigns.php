<?php

if(!defined('ABSPATH'))
    die('Forbidden');

wp_enqueue_script('jquery');
wp_enqueue_script('isolated-slider',plugin_dir_url(__FILE__).'js/rednao-isolated-jq.js',array('jquery'));
wp_enqueue_script('campaigns',plugin_dir_url(__FILE__).'js/smart-donations-campaigns.js',array('isolated-slider'));
wp_enqueue_style('smart-donations-main-style',plugin_dir_url(__FILE__).'css/mainStyle.css');
wp_enqueue_style('smart-donations-Slider',plugin_dir_url(__FILE__).'css/smartDonationsSlider/jquery-ui-1.10.2.custom.min.css');

echo "<h1>Campaigns</h1>";
echo sprintf('<h2 ><a id="sDonationsAddNew" style="color:blue; text-decoration: underline;" href="?page=%s&action=%s">Add New</a></h2>',$_REQUEST['page'],'add');

$campaign_id=$_GET['id'];
$action=$_GET['action'];

if($action!=null&&$campaign_id!=null)
{
    global $wpdb;

    if($action==="delete")
        $wpdb->query($wpdb->prepare("delete from ".SMART_DONATIONS_CAMPAIGN_TABLE." WHERE campaign_id=$campaign_id"));

}


class Donations extends WP_List_Table
{
    function get_columns()
    {
        return array(
            name=>'Name',
            description=>'Description',
            goal=>'Goal',
            campaign_id=>'Campaign Id'
        );
    }

    function prepare_items()
    {
        $this->_column_headers=array($this->get_columns(),array('campaign_id'),$this->get_sortable_columns());
        global $wpdb;
        $this->items=$result=$wpdb->get_results("SELECT campaign_id,name,description,goal FROM ".SMART_DONATIONS_CAMPAIGN_TABLE);

        if(count($this->items)>0)
        {

            $json= "<script> var smartDonationsCampaignArray=[";
            foreach ($this->items as $item) {
                $json.='{"campaign_id":"'.urldecode($item->campaign_id).'",';
                $json.='"description":"'.urldecode($item->description).'",';
                $json.='"goal":"'.urldecode($item->goal).'",';
                $json.='"name":"'.urldecode($item->name).'"},';
            }
            $json=substr($json,0,-1);
            $json.= "];</script>";
            echo $json;
        }


    }

    function get_sortable_columns()
    {

    }

    function column_default($item, $column_name)
    {
        return $item->$column_name;
    }

    function column_name($item) {
        $actions = array(
            'edit'      => sprintf("<a href='javascript:smartDonationsEditCampaign($item->campaign_id)'>Edit</a>"),
            'delete'    => sprintf('<a href="?page=%s&id=%s&action=%s">Delete</a>',$_REQUEST['page'],$item->campaign_id,'delete'),
        );

        return sprintf('%1$s %2$s', $item->name, $this->row_actions($actions) );
    }
}

$donationList=new Donations();
$donationList->prepare_items();
$donationList->display();

?>


<div id="smart-donations-add-new-campaign-panel" title="Create campaign">
    <table>
        <tr>
            <td>Name</td>
            <td><input type="text" id="smart_donations_campaign_name" name="name" class="smartDonationsConfigurationFields"></td>
        </tr>

        <tr>
            <td>Description</td>
            <td><input type="text" id="smart_donations_campaign_description" name="description" class="smartDonationsConfigurationFields"></td>
        </tr>

        <tr>
            <td>Goal</td>
            <td><input type="text" name="goal" id="smart_donations_campaign_goal" class="smartDonationsConfigurationFields smartDonationsNumericField"></td>
        </tr>

        <tr>
            <td colspan="2"><div style="text-align: right"><button id="campaignSaveButton">Save</button></div></td>
        </tr>
    </table>
</div>