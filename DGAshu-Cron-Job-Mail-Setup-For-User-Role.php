<?php
/* 
Plugin Name: DGAshu Cron Job Mail Setup For User Role - DGAshu
Version: 1.0.0
Author: DGAshu
Author URI: https://dgashu.com/
Contributors: DGAshu
Tags: mailer, cron setup, send mail to the user role, custom mailer
Description: This is Mailer setup for the cron Job Mail Send for Specific User Role
*/

//Exit if accessed directly
if(!defined('ABSPATH')) exit; 

class DGAshuCronJobMailSetupForUserRole{
    function __construct(){
        add_action('admin_menu', array($this,'CJMail'));
        add_filter( 'cron_schedules', array($this,'CJMail_add_cron_interval'));
        add_action('wp',array($this,'checkDuplicateEvents'));
        add_action("CJMailSchdule", array($this,'CJMailExecute'));
        add_action("admin_init",array($this,"settings"));
    }
    function settings(){
        // For To Mail
        add_settings_section( 'cjmailer_to_section', null, null, "cjmailer" );
        add_settings_field( "cjmailerTo", "Interval for Mail Shoot in second", array($this,"cjmailerToHtml"), "cjmailer", "cjmailer_to_section");
        register_setting( "CJMailerPlugin", "cjmailerTo", array('sanitize_callback'=>'sanitize_text_field','default'=>'60'));
        //For Subject
        add_settings_section( 'cjmailer_subject_section', null, null, "cjmailer" );
        add_settings_field( "cjmailerSubject", "Email Compose", array($this,"cjmailerToHtmlSubject"), "cjmailer", "cjmailer_subject_section");
        register_setting( "CJMailerPlugin", "cjmailerSubject", array('sanitize_callback'=>'sanitize_textarea_field','default'=>'The subject for mail'));
        //For Mail Body
        add_settings_section( 'cjmailer_body_section', null, null, "cjmailer" );
        add_settings_field( "cjmailerBody", "Email Compose", array($this,"cjmailerToHtmlBody"), "cjmailer", "cjmailer_body_section");
        register_setting( "CJMailerPlugin", "cjmailerBody", array('sanitize_callback'=>'sanitize_textarea_field','default'=>'Hi This is test Content'));
        //For User Mail Sent To Be
        add_settings_section( 'cjmailer_user_section', null, null, "cjmailer" );
        add_settings_field( "cjmailerUser", "Select User type for Email", array($this,"cjmailerUserType"), "cjmailer", "cjmailer_user_section");
        register_setting( "CJMailerPlugin", "cjmailerUser", array('sanitize_callback'=>'sanitize_textarea_field','default'=>'subscriber'));
        
    }

    function cjmailerToHtmlSubject(){?>
        <input type="text" name="cjmailerSubject" value="<?php echo esc_attr(get_option( 'cjmailerSubject' )); ?>">
    <?php }

    function cjmailerUserType(){
        global $wp_roles;
        ?>
        <select name="cjmailerUser">
            <?php foreach ( $wp_roles->roles as $key=>$value ): ?>
            <option value="<?php echo esc_attr($key); ?>" <?php if(get_option('cjmailerUser')== $key){echo "selected";} ?>><?php echo esc_attr($value['name']); ?></option>
            <?php endforeach; ?>
        </select>
        
    <?php } 
    
    function cjmailerToHtml(){?>
        <input type="number" name="cjmailerTo" value="<?php echo esc_attr(get_option( 'cjmailerTo' )); ?>">
    <?php }

    function cjmailerToHtmlBody(){
                $content = esc_attr(get_option( 'cjmailerBody' ));
                $custom_editor_id = "editorid";
                $custom_editor_name = "cjmailerBody";
                $args = array(
                        'media_buttons' => false, // This setting removes the media button.
                        'textarea_name' => $custom_editor_name, // Set custom name.
                        'textarea_rows' => get_option('default_post_edit_rows', 10), //Determine the number of rows.
                        'quicktags' => false, // Remove view as HTML button.
                    );
                wp_editor( $content, $custom_editor_id, $args );
 }
    function CJmail (){
    add_menu_page( "CJmail Cron", "CJMail By DGAshu", "manage_options","cjmailer", array($this,"cjmailer_html"), "dashicons-email", 100 );
    }
    function cjmailer_html(){?>
        <div class="wrap">
            <h1>Email Setup</h1>
            <form action="options.php" method="POST">
                <?php 
                    settings_fields('CJMailerPlugin');
                    do_settings_sections('cjmailer'); 

                    submit_button();
                ?>
            </form>
        </div>
    <?php }
    //Adding Custom Interval
    function CJMail_add_cron_interval( $schedules ) { 
        $schedules['sixty_seconds'] = array(
            'interval' => get_option( 'cjmailerto','60' ),
            'display'  => esc_html__( 'Every '.get_option( 'cjmailerto','60' ).' Seconds' ), );
        return $schedules;
    }
    function checkDuplicateEvents(){
        if(! wp_next_scheduled("CJMailSchdule")){
            wp_schedule_event( time(), "sixty_seconds", "CJMailSchdule");
        }
    }
    function CJMailExecute(){
        

        $args1 = array(
     'role' => get_option( 'cjmailerUser','administrator' ),
     'orderby' => 'user_nicename',
     'order' => 'ASC'
    );
     $subscribers = get_users($args1);
     //$subscribers = get_users();

     foreach ($subscribers as $user) {
         $to = $user->user_email;
        $subject = get_option( 'cjmailerSubject','Subject for Mail' );
        $body = get_option( 'cjmailerBody','this is the content option' );
        $headers = array('Content-Type: text/html; charset=UTF-8','From:'.get_option( 'blogname' ).'<'.get_option('admin_email').'>'); 
        wp_mail( $to, $subject, $body, $headers );
        
     }
    }
}
$dgashucronjobmailsetupforuserrRole = new DGAshuCronJobMailSetupForUserRole();