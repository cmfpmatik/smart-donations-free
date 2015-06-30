<?php

function SMARTFREE_DONATIONS_check_license($email,$key,&$error,$isNew)
{
    if($email!=null||$key!=null)
    {
        if(get_transient("SMARTFREE_DONATIONS_check_again"))
            return true;
        if(SMARTFREE_DONATIONS_license_is_valid($email,$key,$error))
        {
            update_option('SMARTFREE_DONATIONS_email',$email);
            update_option('SMARTFREE_DONATIONS_key',$key);
            require_once('smart-donations-wall-widget.php');

            set_transient("SMARTFREE_DONATIONS_check_again",1,60*60*24*7);
            return true;
        }
    }

    return false;
}

function SMARTFREE_DONATIONS_check_license_with_options(&$error)
{
    if(get_transient("SMARTFREE_DONATIONS_check_again"))
        return true;
    $email=get_option('SMARTFREE_DONATIONS_email');
    $key=get_option('SMARTFREE_DONATIONS_key');
    return SMARTFREE_DONATIONS_check_license(($email?$email:""), ($key?$key:""),$error,false);
}

function SMARTFREE_DONATIONS_license_is_valid($email,$key,&$error)
{
    $email=trim($email);
    $key=trim($key);
    delete_transient("SMARTFREE_DONATIONS_check_again");
    $response=wp_remote_post(DEVOARCES_URL.'SMARTFREE_DONATIONS_license_validation.php',array('body'=> array( 'email'=>$email,'key'=>$key),'timeout'=>10));
    if($response instanceof WP_Error)
    {
        $error= $response->get_error_message();
        return false;
    }

    if(strcmp ($response['body'], "valid") == 0)
        return true;
    else{
        return false;
    }

}