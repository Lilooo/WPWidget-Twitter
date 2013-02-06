<?php
/**
 * Plugin Name: Plugin Twitter 1.1
 * Description: Un plugin spécialement adapté au fonctionnement de l'API v1.1 Twitter
 * Version: 0.1
 * Author: Lilo0o
 * Author URI: http://www.web-hour.com
 */
 
add_action('admin_menu', 'add_plugins_menu');

function add_plugins_menu(){
    add_options_page('Options Plugin LeBo', 'Plugin LeBo', 'manage_options', 'plugin-lebo', 'my_plugin_options');
}

function my_plugin_options() {
//        echo '<div>
//    <h2>Plugin LeBo</h2>
//    Options relatives au fonctionnement du plugin LeBo.
//    <form action="options.php" method="post">';
//
//    settings_fields('plugin_options');
//    do_settings_sections('plugin');
//
//    echo '<input name="Submit" type="submit" value="'. esc_attr_e('Save Changes').'" />
//    </form></div>';
    ?>
    <div>
    <h2>Plugin LeBo</h2>
    Options relatives au fonctionnement du plugin LeBo.
    <form action="options.php" method="post">
    <?php settings_fields('plugin_options'); ?>
    <?php do_settings_sections('plugin'); ?>
 
    <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form></div>
 
    <?php
    }

add_action('admin_init', 'plugin_admin_init');

function plugin_admin_init(){
    register_setting( 'plugin_options', 'plugin_options'/*, 'plugin_options_validate' */);
    add_settings_section('plugin_main', 'Paramètres Twitter', 'plugin_section_text', 'plugin');
    add_settings_field('plugin_text_string', 'Consumer key', 'plugin_setting_string', 'plugin', 'plugin_main');
    add_settings_field('plugin_text_string2', 'Consumer secret', 'plugin_setting_string2', 'plugin', 'plugin_main');
    add_settings_field('plugin_text_string3', 'oAuth Token', 'plugin_setting_string3', 'plugin', 'plugin_main');
    add_settings_field('plugin_text_string4', 'oAuth Token secret', 'plugin_setting_string4', 'plugin', 'plugin_main');

}

function plugin_section_text() {
echo '<p>Identifiants de votre application Twitter. Pour créer une nouvelle application, connectez-vous sur <a href="http://dev.twitter.com" target="_blank">dev.twitter.com</a></p>';
}

function plugin_setting_string() {
$options = get_option('plugin_options');
echo "<input id='plugin_text_string' name='plugin_options[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

function plugin_setting_string2() {
$options = get_option('plugin_options');
echo "<input id='plugin_text_string2' name='plugin_options[text_string2]' size='40' type='text' value='{$options['text_string2']}' />";
}

function plugin_setting_string3() {
$options = get_option('plugin_options');
echo "<input id='plugin_text_string3' name='plugin_options[text_string3]' size='40' type='text' value='{$options['text_string3']}' />";
}

function plugin_setting_string4() {
$options = get_option('plugin_options');
echo "<input id='plugin_text_string4' name='plugin_options[text_string4]' size='40' type='text' value='{$options['text_string4']}' />";
}

//function plugin_options_validate($input) {
//$options = get_option('plugin_options');
//$options['text_string'] = trim($input['text_string']);
//if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
//$options['text_string'] = '';
//}
//return $options;
//}

add_action('widgets_init','plugin_init');
 
function plugin_init(){
    register_widget("twitter_widget");
    }

/*
 * Transform Tweet plain text into clickable text
 */
function parseTweet($text) {
    $text = preg_replace('#http://[a-z0-9._/-]+#i', '<a  target="_blank" href="$0">$0</a>', $text); //Link
    $text = preg_replace('#@([a-z0-9_]+)#i', '@<a  target="_blank" href="http://twitter.com/$1">$1</a>', $text); //usernames
    $text = preg_replace('# \#([a-z0-9_-]+)#i', ' #<a target="_blank" href="http://search.twitter.com/search?q=%23$1">$1</a>', $text); //Hashtags
    $text = preg_replace('#https://[a-z0-9._/-]+#i', '<a  target="_blank" href="$0">$0</a>', $text); //Links
    return $text;
}    

function twitter_messages($nb_twit = 1, $username = '', $custom_key='', $custom_secret='', $token='', $token_secret=''){
    $consumer_key=$custom_key;
    $consumer_secret=$custom_secret;
    $oauth_token = $token;
    $oauth_token_secret = $token_secret;
    
    if(!empty($consumer_key) && !empty($consumer_secret) &&
            !empty($oauth_token) && !empty($oauth_token_secret)) {
        $twBloc = array();

        //2 - Include @abraham's PHP twitteroauth Library
        require_once('twitteroauth/twitteroauth.php');

        //3 - Authentication
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

        //4 - Start Querying
        $query = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$username.'&count='.$nb_twit; //Your Twitter API query
        $content = $connection->get($query);
        } else {
            return 'Please update your settings to provide valid credentials';
        }

        if(!empty($content)){
            foreach($content as $tweet){
                $twBloc[] = '<div class="twitter_status" id="'.$tweet->id_str.'">
                      <ul class="twitter_list">
                           <li class="twitter-item">
                                <p class="twitter-timestamp">
                                    <a href="http://twitter.com/'.$tweet->user->screen_name.'" target="_blank" class="tweet-day">'.date("d",strtotime($tweet->created_at)).'</a>
                                    <span class="tweet-month">'.date("M",strtotime($tweet->created_at)).'</span>
                                </p>
                                <p class="twitter-message">'.parseTweet($tweet->text).'</p>
                            </li>
                        </ul>
                    </div>';
                    }
            return $twBloc;
         }
         return $twBloc[0] = 'No tweet found';
}

class twitter_widget extends WP_widget{
	

	function twitter_widget(){
		$options = array(
			"classname" => "twitter-widget",
			"description" => "Un widget qui sert à récupérer les derniers tweets"
		);
		$this->WP_widget("twitter-widget", "Widget Twitter",$options);
	}
	
	function widget($args,$d){
                $options = get_option('plugin_options');
		extract ($args);
		echo $before_widget;
		echo $before_title.$d["titre"].$after_title;
                $twTable = twitter_messages($d["show_count"], $d["username"],$options['text_string'],$options['text_string2'],$options['text_string3'],$options['text_string4']);
                if (is_array($twTable)) {
                    foreach ($twTable as $value) {
                        echo($value);
                    }
                }
//		echo $d["titre"];
		echo $after_widget;
	}
	
	function update($new,$old){
		return $new;
	}	
	
	function form($d){
		$defaut = array(
			"titre"         => "Widget Twitter",
                        "username"      => "NOE_interactive",
                        "show_count"    => "3"
		);
		$d = wp_parse_args($d,$defaut);
                $form = '<p>
			<label for="'.$this->get_field_id('titre').'">Titre : </label>
			<input value="'.$d["titre"].'" name="'.$this->get_field_name('titre').'" id="'.$this->get_field_id('titre').'" type="text"/>
		</p>
                <p>
			<label for="'.$this->get_field_id('username').'">Username Twitter : </label>
			<input value="'.$d["username"].'" name="'.$this->get_field_name('username').'" id="'.$this->get_field_id('username').'" type="text"/>
		</p>
                <p>
			<label for="'.$this->get_field_id('show_count').'"> Nombre de tweets à afficher : </label>
			<input value="'.$d["show_count"].'" name="'.$this->get_field_name('show_count').'" id="'.$this->get_field_id('show_count').'" type="text"/>
		</p>';
                echo $form;
//        print_r($d);
	}
	
}
