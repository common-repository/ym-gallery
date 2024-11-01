<?php
/*
Plugin Name: ym gallery
Plugin URI: 
Description:ギャラリーを挿入するプラグインです。
Author: Takuro Yamao
Author URI: http://unionnet.jp/
Version: 1.0.0
*/


//インスタンスの生成、initの実行
$ymGallery = new ymGallery;

class ymGallery{

    /**
     * [__construct sescription]
    */
    public function __construct(){
        add_action('admin_menu',array($this,'add_menu'));
        add_action('admin_print_footer_scripts',array($this,'admin_script'));
        add_action('admin_enqueue_scripts',array($this,'my_admin_scripts'));
        add_action('admin_enqueue_scripts',array($this,'my_admin_styles'));
        add_action('wp_ajax_ym_gallery_update_options', array($this,'update_options'));
        add_action('wp_ajax_ym_gallery_update_settings', array($this,'update_settings'));
        add_action('admin_init',array($this,'add_gallery_box'));
        add_action('wp_print_footer_scripts', array( $this, 'print_scripts' ) );
        add_action('wp_enqueue_scripts', array($this,'load_js'));
        add_action('wp_print_styles', array($this,'load_css'));
        add_action('save_post', array($this,'update_options'));   
        add_action('wp', array($this,'ym_short_code'));
    }

    public function ym_short_code(){
      if(is_single()) {  
        global $post; 
        $id = $post->ID;
        $short_setting = get_post_meta($id,'ym_gallery_short_id',ture);
        $ym_options = get_post_meta($id,'ym_gallery_option',ture);
        if($short_setting['short'] == 0){
          add_shortcode('ym_gallery', array($this,'add_short'));
        }elseif(empty($short_setting['short']) || $short_setting['short'] == 1){  
          if(!empty($ym_options)){
            add_filter('the_content', array($this,'add_short'));
          }
        }
      }
    }

    //css読み込み
    function load_css(){
      wp_enqueue_style('ym_gallery_view' , plugin_dir_url(__FILE__) .'css/ym_gallery_view.css');
      wp_enqueue_style('jquery.fancybox-1.3.4' , plugin_dir_url(__FILE__) .'fancybox/jquery.fancybox-1.3.4.css');
      wp_enqueue_style('jquery.bxslider' , plugin_dir_url(__FILE__) .'css/jquery.bxslider.css');
      wp_enqueue_style('thumbnail' , plugin_dir_url(__FILE__) .'css/thumbnail.css');
    }

    //js読み込み
    function load_js(){
      wp_enqueue_script( 'jquery');
      wp_enqueue_script( 'ym_gallery_view' , plugin_dir_url(__FILE__) .'js/ym_gallery_view.js', null,null ,true);
      wp_enqueue_script( 'jquery.mousewheel-3.0.4.pack' , plugin_dir_url(__FILE__) .'fancybox/jquery.mousewheel-3.0.4.pack.js', null,null ,true);
      wp_enqueue_script( 'jquery.fancybox-1.3.4' , plugin_dir_url(__FILE__) .'fancybox/jquery.fancybox-1.3.4.js', null,null ,true);
      wp_enqueue_script( 'jquery.bxslider.min' , plugin_dir_url(__FILE__) .'js/jquery.bxslider.min.js', null,null ,true);
      wp_enqueue_script( 'thumbnail' , plugin_dir_url(__FILE__) .'js/thumbnail.js', null,null ,true);
    }


    //実行スクリプト呼び出し
    function print_scripts(){
      global $post;
      $id = $post->ID;
      $post_set = get_post_meta($id,'ym_gallery_settings',true);
      $post_set2 = get_post_meta($id,'ym_gallery_settings2',true);
      $post_set3 = get_post_meta($id,'ym_gallery_settings3',true);
      $ym_settings = ($post_set) ? $post_set : get_option('ym_gallery_default_settings');
      $ym_settings2 = ($post_set2) ? $post_set2 : get_option('ym_gallery_default_settings2');
      $ym_settings3 = ($post_set3) ? $post_set3 : get_option('ym_gallery_default_settings3');    

      if($ym_settings['use'] == 0){
      ?>
       <script>
       jQuery(function(){
          jQuery('.bxslider').bxSlider({
            mode : '<?php echo ($ym_settings['animation']==0) ? 'horizontal': 'fade'; ?>',
            auto : <?php echo ($ym_settings['auto']==0) ? 'true': 'false'; ?>,
            pager : <?php echo ($ym_settings['pager']==0) ? 'true': 'false'; ?>,
            controls : <?php echo ($ym_settings['controls']==0) ? 'true': 'false'; ?>
          }); 
        });
        </script>   
      <?php
      }elseif($ym_settings2['fancy'] == 0){
      ?>
      <script>
        jQuery(document).ready(function() {
          jQuery(".fancybox").fancybox();
        });
      </script>
      <?php 
      }elseif($ym_settings3['thum'] == 0){ 
      ?>
      <script>
        jQuery(function(){
          changeImg();
        });
      </script>
    <?php 
      }
    }

    //投稿画面にフィールド読み込み
    function add_gallery_box() {
      add_meta_box( 'myplugin_sectionid', 'ギャラリー',array($this,'ym_gallery_menu'), 'post', 'normal', 'high');
    }

    //ショートコード作成
    function add_short($atts){
      extract( shortcode_atts( array(
          // 属性が省略された時のデフォルト値を設定する
          'id' => '',
          // ...etc
      ), $atts ) );
      global $post;
      $id = (empty($id))?$post->ID:$id;

      $post_set = get_post_meta($id,'ym_gallery_settings',true);
      $post_set2 = get_post_meta($id,'ym_gallery_settings2',true);
      $post_set3 = get_post_meta($id,'ym_gallery_settings3',true);
      $ym_settings = ($post_set) ? $post_set : get_option('ym_gallery_default_settings');
      $ym_settings2 = ($post_set2) ? $post_set2 : get_option('ym_gallery_default_settings2');
      $ym_settings3 = ($post_set3) ? $post_set3 : get_option('ym_gallery_default_settings3');
      $output = "";
      $data = get_post_meta($id,'ym_gallery_option',true);
      
      if($ym_settings['use'] == 0){
        $output .= '<div class="slideWrap">' . PHP_EOL;
        $output .= '<ul class="bxslider">' . PHP_EOL;
        foreach($data as $k => $v):
        $output .= '<li><img src="'.$v['value'] .'" alt="'. $v['key'] .'"></li>' . PHP_EOL;
        endforeach; 
        $output .= '</ul>' . PHP_EOL;
        $output .= '</div>' . PHP_EOL;
      
      }elseif($ym_settings2['fancy'] == 0){
     
        $output .= '<ul class="ym_fancy">' . PHP_EOL;
      foreach($data as $k => $v):
        $output .= '<li><a href="'. $v['value'] .'" class="fancybox"><img src="'. $this->get_attachment_image_src($v['value'], 'thumbnail') .'" alt="'. $v['key'].'"></a></li>' . PHP_EOL;
      endforeach;
        $output .= '</ul>' . PHP_EOL;
    
      }elseif($ym_settings3['thum'] == 0){
     
      foreach($data as $k => $v):
         $output .=  '<p class="ym_mainP"><img src="'. $v['value'] .'" alt="'. $v['key'].'"></p>' . PHP_EOL;
         break; 
      endforeach;
        $output .= '<ul class="ym_thumP">' . PHP_EOL;
      foreach($data as $k => $v):
          $output .= '<li><img src="'. $v['value'] .'" alt="'. $v['key'] .'"></li>' . PHP_EOL;
      endforeach;
        $output .= '</ul>' . PHP_EOL;
      
      }else{
    
        $output .= '<ul class="ym_gallery">' . PHP_EOL;
      foreach($data as $k => $v):
        $output .= '<li><img src="'. $v['value'] .'" alt="'. $v['key'] .'"></li>' . PHP_EOL;
      endforeach; 
        $output .= '</ul>' . PHP_EOL;
      }

    return $output;
    }


    //管理画面のJSの実行（Ajaxなど）
    public function admin_script(){
     
    ?>
    <script>
    (function($){
      $(function(){

        $('#ym_add_calumn').on('click',function(){
          var len = parseInt($('.ym_data').length);
          var tr =  '<tr class="ym_data"><th><span class="ym_delete">削除</span><input name="ym_gallery_option['+ len +'][key]" type="text" class="ym_title" value=""></th><td><input required name="ym_gallery_option['+ len  +'][value]" type="text" id="ym_gallery_option-" class="ym_image" value=""><a class="media-upload" href="JavaScript:void(0);" rel="ym_gallery_option-">Select File</a><img src="" width="150px" class="ym_image_elem"></td></tr>';
            $('.form-table tbody#ym_tbody').append(tr);
        });

        //投稿ページでのAjaxで非同期通信
        function data_update(type){
          var newObj ={},
              slideObj ={},
              fancyObj ={},
              thumObj ={},
              idObj ={};

          $('.ym_data').each(function(i){
            var key = $(this).find('.ym_title').val();
            var value = $(this).find('.ym_image').val();
             newObj[i] = {key: key,value : value};              
          });

          $('.ym_data').each(function(i){
            var key = $(this).find('.ym_key').val();
            var value = $(this).find('.ym_value:checked').val();
             slideObj[key] = value;              
          });

          $('.ym_data2').each(function(i){
            var key = $(this).find('.ym_key2').val();
            var value = $(this).find('.ym_value2:checked').val();
             fancyObj[key] = value;              
          });
           $('.ym_data3').each(function(i){
            var key = $(this).find('.ym_key3').val();
            var value = $(this).find('.ym_value3:checked').val();
             thumObj[key] = value;              
          });
          
          $('.ym_data4').each(function(i){
            var key = $(this).find('.ym_key4').val();
            var value = $(this).find('.ym_value4:checked').val();
             idObj[key] = value;              
          });

          <?php
          $post_id = $_GET['post'] ? $_GET['post'] : 0;
          ?>
          var id = <?php echo  $post_id; ?>;
          $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {id : id,action : 'ym_gallery_update_options' ,ym_gallery_option : newObj, ym_gallery_settings : slideObj , ym_gallery_settings2 : fancyObj , ym_gallery_settings3 : thumObj, ym_gallery_short_id : idObj },
            success : function(data){
              if(type =="update")
              {
               $('#has-newer-autosave').text(data);
               $('.ym_gallery-template-edit-msg').fadeIn('fast',function(){
                 $(this).delay(5000).fadeOut('slow');
               });
              }
            }
          });        
        }

        //設定ページでのAjaxで非同期通信
        function setting_update(type){
          var slideObj ={},
              fancyObj ={},
              thumObj ={};

          $('.ym_data').each(function(i){
            var key = $(this).find('.ym_key').val();
            var value = $(this).find('.ym_value:checked').val();
             slideObj[key] = value;              
          });

          $('.ym_data2').each(function(i){
            var key = $(this).find('.ym_key2').val();
            var value = $(this).find('.ym_value2:checked').val();
             fancyObj[key] = value;              
          });

          $('.ym_data3').each(function(i){
            var key = $(this).find('.ym_key3').val();
            var value = $(this).find('.ym_value3:checked').val();
             thumObj[key] = value;              
          });

          $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {action : 'ym_gallery_update_settings' ,ym_gallery_default_settings : slideObj , ym_gallery_default_settings2 : fancyObj, ym_gallery_default_settings3 : thumObj },
            success : function(data){
              if(type =="update")
              {
               $('#has-newer-autosave').text(data);
               $('.ym_gallery-template-edit-msg').fadeIn('fast',function(){
                $(this).delay(5000).fadeOut('slow');
               });
              }
            }
          });
        }

        //データ削除
        $(document).on('click','.ym_delete',function(){
          $(this).parents('tr').remove();
          data_update('delete');
        });

        //データ保存（投稿ページ）
        $('#ym_gallery_update').on('click',function(){
          data_update('update');
        });
        $('#publishing-action input').on('click',function(event){
          $('#ym_gallery_update').trigger('click');
          data_update('update');
        });

        //データ保存（設定ページ）
        $('#ym_gallery_setting_update,#ym_gallery_setting_update2').on('click',function(){
          setting_update('update');
        });
      
      });
    })(jQuery);

    //アップローダー
    jQuery('document').ready(function(){
      jQuery(document).on('click','.media-upload',function(){

        var click_elem = jQuery(this);
        var input = click_elem.parent().find('.ym_image');
        var image = click_elem.parent().find('.ym_image_elem');
          window.send_to_editor = function(html) {
              imgurl = jQuery('img', html).attr('src');//srcの値を取得
              input.val(imgurl);//.after('<img src="'+imgurl+'" >');//値をセット
              image.attr("src",imgurl);//srcを上書き
              tb_remove();
          }   

          tb_show(null, 'media-upload.php?post_id=0&type=image&TB_iframe=true');
          return false;
      }); 
    }); 

    </script>
    <?php
    }

    //管理画面でのCSS読み込み
    function my_admin_styles() {

      global $wp_scripts;
      $ui = $wp_scripts->query('jquery-ui-core');

      wp_enqueue_style('thickbox');
      // wp_enqueue_style('jquery-ui-core');
      wp_enqueue_style('jquery-ui',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css");         
      wp_enqueue_style('ym_gallery' , plugin_dir_url(__FILE__) .'css/ym_gallery.css');
      // wp_enqueue_style('jquery-ui' , plugin_dir_url(__FILE__) .'css/jquery-ui.css');
     
    }
    
    //管理画面でのJS読み込み
    function my_admin_scripts() {
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-sortable');
      wp_enqueue_script('jquery-ui-tabs');
      wp_enqueue_script('ym_gallery' , plugin_dir_url(__FILE__) .'js/script.js');
    }


    //管理画面のメニューに追加
    public function add_menu(){
      add_options_page('YMギャラリー', 'YMギャラリー', '2', 'ym_gallery_menu',array($this,'ym_gallery_option_page'));
    }


    //投稿ページの表示生成
    public function ym_gallery_menu(){
      if(isset($_POST['ym_gallery_update']))
      $this->update();
      $ym_options = (get_post_meta($_GET['post'],'ym_gallery_option',true)) ? get_post_meta($_GET['post'],'ym_gallery_option',true) : array();

    ?>
      <script>
      jQuery(document).ready(function(){
          jQuery('#ym_tbody').sortable();
          jQuery('#tabmenu').tabs();
      });
      </script>
      <div class="wrap">
        <h2>ギャラリー追加フォーム</h2>
        <div class=" ym_gallery-template-edit-msg" style="display:none"><p id="has-newer-autosave"></p></div>
        
        <p id="showSet">詳細設定をする</p>
        <div class="setTable">
        <?php $this->display_option_table($_GET['post']);?>
        </div>

        <?php $post_id = $_GET['post']; ?>
        
        <p id="showShort">任意の箇所でギャラリーを使用する</p>
        <?php

        $ym_post_short = get_post_meta($post_id,'ym_gallery_short_id',true);
        ?>
        <div class="setShort">
          <table>
            <tr class="ym_data4">
              <th>任意の箇所でギャラリーを使用する<input type="hidden" class="ym_key4" value="short" /></th>
              <td><label><input type="radio" name="ym_gallery_short_id[short]" <?php if( $ym_post_short['short']==0) echo 'checked="checked"';?> class="ym_value4" value="0" />する</label>
              <label><input type="radio" name="ym_gallery_short_id[short]" class="ym_value4" <?php if(empty($ym_post_short) || $ym_post_short['short']==1) echo 'checked="checked"';?> value="1" />しない</label></td>
            </tr>
          </table>
          <p>公開後ショートコードを任意の箇所にペーストてください</p>
          <p><?php echo '[ym_gallery id='.$post_id.']' ?></p>
        </div>

        <table class="form-table">
          <tr>
            <th width="320">画像の名前</th>
            <td>ファイルを選択してください</td>
          </tr>
          <tbody id="ym_tbody">
            
            <?php foreach($ym_options as $k => $v) : ?>
            
              <tr class="ym_data">
                <th><span class="ym_delete">削除</span><input name="ym_gallery_option[<?php echo $k;?>][key]" type="text" class="ym_title" value="<?php echo $v['key'];?>" /></th>
                <td><input type="text"  name="ym_gallery_option[<?php echo $k;?>][value]" id="ym_gallery_option-<?php echo $k;?>" class="ym_image" value="<?php echo $v['value'];?>" />
                <a class="media-upload" href="JavaScript:void(0);" rel="ym_gallery_option-<?php echo $k;?>">Select File</a> 
                <img src="<?php echo $this->get_attachment_image_src($v['value'], 'thumbnail') ;?>" style="width:150px;" class="ym_image_elem">
                </td>
              </tr>
            <?php endforeach; ?>
            
          </tbody>
          <tr>
            <th></th>
            <td><input type="button" class="button button-primary" value="カラム追加" id="ym_add_calumn"></td>
          </tr>
          <?php if(isset($_GET['post'])) :?>
          <tr>
            <th></th>
            <td><input type="button" class="button button-primary" value="保存" id="ym_gallery_update" name="ym_gallery_update"></td>
          </tr>
        <?php endif;?>
        </table>


      </div>
      <?php
    }


    //Ajaxでデータの保存(投稿ページ)
    public function update_options( $post_id = null ){
      $id = ($post_id == null ) ? (int) $_POST['id'] : $post_id ;
      if($id > 0){
        update_post_meta($id,'ym_gallery_option',$_POST['ym_gallery_option']);
        update_post_meta($id,'ym_gallery_settings',$_POST['ym_gallery_settings']);
        update_post_meta($id,'ym_gallery_settings2',$_POST['ym_gallery_settings2']);
        update_post_meta($id,'ym_gallery_settings3',$_POST['ym_gallery_settings3']);
        update_post_meta($id,'ym_gallery_short_id',$_POST['ym_gallery_short_id']);
      }
     if($_POST['action']=='ym_gallery_update_options') 
     exit('保存しました。');
    }
    

    //Ajaxでデータの保存(設定ページ)
    public function update_settings(){
     
      update_option('ym_gallery_default_settings',$_POST['ym_gallery_default_settings']);
      update_option('ym_gallery_default_settings2',$_POST['ym_gallery_default_settings2']);
      update_option('ym_gallery_default_settings3',$_POST['ym_gallery_default_settings3']);

      exit('保存しました。');
    }
    

    /**
     * 画像のURLからattachemnt_idを取得する
     *
     * @param string $url 画像のURL
     * @return int attachment_id
     */
    public function get_attachment_id($url){
      global $wpdb;
      $sql = "SELECT ID FROM {$wpdb->posts} WHERE guid = %s";
      // preg_match('/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches);
       $post_name = $url;
       $id = (int)$wpdb->get_var($wpdb->prepare($sql, $post_name));
     
       if($id == 0){
        $sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s";
        preg_match('/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches);
        $post_name = $matches[1];
        $id = (int)$wpdb->get_var($wpdb->prepare($sql, $post_name));
       }
      return $id;
    }
 
    /**
     * 画像のURLのサイズ違いのURLを取得する
     * 
     * @param string $url 画像のURL
     * @param string $size 画像のサイズ (thumbnail, medium, large or full)
     */ 
    public function get_attachment_image_src($url, $size){
      $image = wp_get_attachment_image_src($this->get_attachment_id(esc_url($url)), $size);
      
      return $image[0];
    }
     
    public function get_settings($type="default",$post_options=""){
      $ym_settings = array();

       $ym_settings[0]= get_option('ym_gallery_default_settings');
       $ym_settings[1] = get_option('ym_gallery_default_settings2');
       $ym_settings[2] = get_option('ym_gallery_default_settings3');

      if($type=="post"&&!empty($post_options)) {

        $ym_post_setting = get_post_meta($post_options,'ym_gallery_settings',true);
        $ym_post_setting2 = get_post_meta($post_options,'ym_gallery_settings2',true);
        $ym_post_setting3 = get_post_meta($post_options,'ym_gallery_settings3',true);
        
        $ym_settings[0] = (!empty($ym_post_setting)) ?  $ym_post_setting : $ym_settings[0];
        $ym_settings[1] = (!empty($ym_post_setting2)) ?  $ym_post_setting2 : $ym_settings[1];
        $ym_settings[2] = (!empty($ym_post_setting3)) ?  $ym_post_setting3 : $ym_settings[2];
        
      }
        return $ym_settings;
    }


     /**
      * [ym_gallery_option_page オプションページ]
      * @return [type] [description]
      */
    //設定ページの生成
    public function ym_gallery_option_page(){ 
       // $ym_settings = $this->get_settings();

      ?>
       <div class="wrap">
        <h2>ギャラリー設定</h2>
        <div class=" ym_gallery-template-edit-msg" style="display:none"><p id="has-newer-autosave"></p></div>
        <?php $this->display_option_table();?>
      </div>
      <?php 
    } 

    
    public function display_option_table($post_options=""){

      if(!empty($post_options)) {

        $ym_settings = $this->get_settings('post',$post_options);
              
      }else{
        $ym_settings = $this->get_settings();

      }?>
      <div id="tabmenu">
        <ul>
          <li class="slide"><a href="#tabs-1">スライダーの設定</a></li>
          <li class="slide2"><a href="#tabs-2">ファンシーボックスの設定</a></li>
          <li class="slide3"><a href="#tabs-3">サムネイルギャラリーの設定</a></li>
        </ul>
        <table id="tabs-1" class="form-table">
          <tr>
            <th width="320">スライダーオプション項目</th>
            <td>設定を選択してください</td>
          </tr>
          <tr class="ym_data">
            <th>スライダーを使用する<input type="hidden" class="ym_key" value="use" /></th>
            <td><label><input type="radio" name="ym_gallery_settings[use]" <?php if($ym_settings[0]['use']==0) echo 'checked="checked"';?> class="ym_value" value="0" />使用する</label>
            <label><input type="radio" name="ym_gallery_settings[use]" class="ym_value"<?php if($ym_settings[0]['use']==1) echo 'checked="checked"';?> value="1" />使用しない</label></td>
          </tr>
          <tr class="ym_data">
            <th>表示方法<input type="hidden" class="ym_key" value="animation" /></th>
            <td><label><input type="radio" name="ym_gallery_settings[animation]" <?php if($ym_settings[0]['animation']==0) echo 'checked="checked"';?> class="ym_value" value="0" />スライド</label>
            <label><input type="radio" name="ym_gallery_settings[animation]" class="ym_value" <?php if($ym_settings[0]['animation']==1) echo 'checked="checked"';?> value="1" />フェード</label></td>
          </tr>
          <tr class="ym_data">
            <th>オートスライド<input type="hidden" class="ym_key" value="auto" /></th>
            <td><label><input type="radio" name="ym_gallery_settings[auto]" <?php if($ym_settings[0]['auto']==0) echo 'checked="checked"';?> class="ym_value" value="0" />true</label>
            <label><input type="radio" name="ym_gallery_settings[auto]" class="ym_value" <?php if($ym_settings[0]['auto']==1) echo 'checked="checked"';?> value="1" />false</label></td>
          </tr>
          <tr class="ym_data">
            <th>ページャー<input type="hidden" class="ym_key" value="pager" /></th>
            <td><label><input type="radio" name="ym_gallery_settings[pager]" <?php if($ym_settings[0]['pager']==0) echo 'checked="checked"';?> class="ym_value" value="0" />true</label>
            <label><input type="radio" name="ym_gallery_settings[pager]" class="ym_value" <?php if($ym_settings[0]['pager']==1) echo 'checked="checked"';?> value="1" />false</label></td>
          </tr>  
          <tr class="ym_data">
            <th>コントロール<input type="hidden" class="ym_key" value="controls" /></th>
            <td><label><input type="radio" name="ym_gallery_settings[controls]" <?php if($ym_settings[0]['controls']==0) echo 'checked="checked"';?> class="ym_value" value="0" />true</label>
            <label><input type="radio" name="ym_gallery_settings[controls]" class="ym_value" <?php if($ym_settings[0]['controls']==1) echo 'checked="checked"';?> value="1" />false</label></td>
          </tr>
          <?php if(empty($post_options)): ?>
          <tr>
            <th></th>
            <td><input type="button" class="button button-primary" value="保存" id="ym_gallery_setting_update" name="ym_gallery_update"></td>
          </tr>
          <?php endif;?>
        </table>

        <table id="tabs-2" class="form-table">
          <tr>
            <th width="320">ファンシーボックスオプション項目</th>
            <td>設定を選択してください</td>
          </tr>
          <tr class="ym_data2">
            <th>ファンシーボックスを使用する<input type="hidden" class="ym_key2" value="fancy" /></th>
            <td><label><input type="radio" name="ym_gallery_settings2[fancy]" <?php if($ym_settings[1]['fancy']==0) echo 'checked="checked"';?> class="ym_value2" value="0" />する</label>
            <label><input type="radio" name="ym_gallery_settings2[fancy]" class="ym_value2" <?php if($ym_settings[1]['fancy']==1) echo 'checked="checked"';?> value="1" />しない</label></td>
          </tr>
        <?php if(empty($post_options)): ?>
          <tr>
            <th></th>
            <td><input type="button" class="button button-primary" value="保存" id="ym_gallery_setting_update2" name="ym_gallery_update2"></td>
          </tr>
        <?php endif;?>
        </table>

        <table id="tabs-3" class="form-table">
          <tr>
            <th width="320">サムネイルギャラリーオプション項目</th>
            <td>設定を選択してください</td>
          </tr>
          <tr class="ym_data3">
            <th>サムネイルギャラリーを使用する<input type="hidden" class="ym_key3" value="thum" /></th>
            <td><label><input type="radio" name="ym_gallery_settings3[thum]" <?php if($ym_settings[2]['thum']==0) echo 'checked="checked"';?> class="ym_value3" value="0" />する</label>
            <label><input type="radio" name="ym_gallery_settings3[thum]" class="ym_value3" <?php if($ym_settings[2]['thum']==1) echo 'checked="checked"';?> value="1" />しない</label></td>
          </tr>
        <?php if(empty($post_options)): ?>
          <tr>
            <th></th>
            <td><input type="button" class="button button-primary" value="保存" id="ym_gallery_setting_update2" name="ym_gallery_update2"></td>
          </tr>
        <?php endif;?>
        </table>
      </div>

      <?php
    }
}

