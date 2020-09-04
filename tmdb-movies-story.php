<?php
/*
Plugin Name: TMDb Movie Story
Plugin URI: https://webeer.tech/tmdb-movie-story
Description: TMDbから映画のあらすじを取得するショートコード
Version: 1.0
Author: ATORI
Author URI: https://webeer.tech
License: arorii
*/

// ショートコード
function lds_tmdb_story( $atts ) {
  extract( shortcode_atts( array(
    'year' => '',
		'title' => '',
		'lang' => 'ja-JA',
		'apikey' => get_option('tmdbms_setting')[tamdb_apikey],
  ), $atts ) );

  //作品タイトル
  $title = urlencode( $title );

	//あらすじを取得
	$url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $apikey . '&language=' . $lang . '&query=' . $title . '&page=1&include_adult=false&year=' . $year;

	$content = file_get_contents($url);
	$data = json_decode($content, true);
	$overview = $data['results'][0]['overview'];

	return $overview;

}
add_shortcode('tmdb_story', 'lds_tmdb_story');

// オプションページを作る
class TMDBMSSettingsPage
{
    private $options;
 
    /**
     * 初期化処理です。
     */
    public function __construct()
    {
        // メニューを追加します。
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        // ページの初期化を行います。
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
 
    /**
     * メニューを追加します。
     */
    public function add_plugin_page()
    {
        add_options_page( '映画のあらすじ', '映画のあらすじ', 'manage_options', 'tmdbms_setting', array( $this, 'create_admin_page' ) );
    }
 
    /**
     * 設定ページの初期化を行います。
     */
    public function page_init()
    {
        // 設定を登録します(入力値チェック用)。
        register_setting( 'tmdbms_setting', 'tmdbms_setting', array( $this, 'sanitize' ) );
 
        // 入力項目のセクションを追加します。
        add_settings_section( 'tmdbms_setting_section_id', '', '', 'tmdbms_setting' );
 
        // 入力項目のセクションに項目を1つ追加します。
        add_settings_field( 'tamdb_apikey', 'APIKey', array( $this, 'tamdb_apikey_callback' ), 'tmdbms_setting', 'tmdbms_setting_section_id' );
    }
 
    /**
     * 設定ページのHTMLを出力します。
     */
    public function create_admin_page()
    {
        // 設定値を取得
        $this->options = get_option( 'tmdbms_setting' );
        ?>
        <div class="wrap">
            <h2>TMDb Movie Story</h2>
            <?php
            global $parent_file;
            if ( $parent_file != 'options-general.php' ) {
                require(ABSPATH . 'wp-admin/options-head.php');
            }
            ?>
            <form method="post" action="options.php">
            <?php
                // 隠しフィールドなどを出力します(register_setting()の$option_groupと同じものを指定)。
                settings_fields( 'tmdbms_setting' );
                // 入力項目を出力します(設定ページのslugを指定)。
                do_settings_sections( 'tmdbms_setting' );
                // 送信ボタンを出力します。
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }
 
    /**
     * 入力項目のHTMLを出力します。
     */
    public function tamdb_apikey_callback()
    {
        $tamdb_apikey = isset( $this->options['tamdb_apikey'] ) ? $this->options['tamdb_apikey'] : '';
        ?>
				<input type="text" id="tamdb_apikey" name="tmdbms_setting[tamdb_apikey]" value="<?php esc_attr_e( $tamdb_apikey ) ?>">
				<p>TMDbで取得したAPIキーを入力してください。</p>
				<?php
    }
 
    /**
     * 送信された入力値の調整を行います。
     *
     * @param array $input 設定値
     */
    public function sanitize( $input )
    {
        $this->options = get_option( 'tmdbms_setting' );
 
        $new_input = array();
 
        // APIキーがある場合値を調整
        if( isset( $input['tamdb_apikey'] ) && trim( $input['tamdb_apikey'] ) !== '' ) {
            $new_input['tamdb_apikey'] = sanitize_text_field( $input['tamdb_apikey'] );
        }
        // APIキーがない場合エラーを出力
        else {
            add_settings_error( 'tmdbms_setting', 'tamdb_apikey', 'APIキーを入力して下さい。' );
 
            // 値をDBの設定値に戻します。
            $new_input['tamdb_apikey'] = isset( $this->options['tamdb_apikey'] ) ? $this->options['tamdb_apikey'] : '';
        }
 
        return $new_input;
    }
 
}
 
// 管理画面を表示している場合のみ実行します。
if( is_admin() ) {
    $tmdbms_settings_page = new TMDBMSSettingsPage();
}




