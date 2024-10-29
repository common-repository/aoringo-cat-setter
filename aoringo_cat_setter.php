<?php
/*
  Plugin Name: aoringo CAT setter
  Description: 記事中の単語を拾い上げてカテゴリーを設定します。
  Version: 0.1.1
  Author: aoringo
  Author URI: http://cre.jp/honyomusi/
 */

function aoringo_cat_setter($test) {
  global $post;
  //記事内容読み込み
  $okikae = array(
      "\r\n" => "\n",
      "\r" => "\n",
  );
  $postcontent = strtr($_POST[content], $okikae);
  $postcontent .="\n" . $_POST[post_title];
  $postcontent = strip_tags(stripslashes($postcontent));
  //タグ一覧を取得。一つずつ内容と比較して同一の物があればタグリストに追加する。
  $catcat_rendo = explode(",", get_option('catcat_rendo'));
  $catcat_rendocount = count($catcat_rendo);
  for ($i = 0; $i < $catcat_rendocount; $i++) {
    if (strpos($postcontent, $catcat_rendo[$i]) !== false) {
      $catplus .=$catcat_rendo[$i + 1] . ",";
    }
    $i++;
  }
  if (isset($catplus)) {
    $post_cat = $_POST[post_category];
    $catplus_rendo = explode(",", rtrim($catplus, ","));
    foreach ($catplus_rendo as $catcat)
      array_push($post_cat, $catcat);
  }else{
    return;
  }
  //現在のタグと追加するタグを合わせる。同一名称はwordpress側で排除されるので気にしない☆$jidoutaglist
  wp_set_post_categories($_POST[post_ID], $post_cat);
}

add_action('save_post', 'aoringo_cat_setter', 11);

// ダッシュボード設定へのリンクを追加
function aoringocatcat_option_menu() {
  add_submenu_page('options-general.php', 'aoringo CAT setterの設定', 'aoringo CAT setterの設定', 8, __FILE__, 'aoringocatcat_admin_page');
}

add_action('admin_menu', 'aoringocatcat_option_menu');

//***************************************************************** 以下設定画面用コード ****************************************************//
// 設定画面構成コード
function aoringocatcat_admin_page() {
  //設定保存用処理、改行や今後の処理に関わりそうな文字を整理する。タグなども除去している。
  $jokyo = array("\n" => "", "\r" => "", "$" => "", '"' => "&quot;",
      "'" => "&apos;",
      '\\' => "",
      "" => "&nbsp;",
      "<" => "&lt;",
      ">" => "&gt;",
      "@" => "&copy;",
      "$" => "＄",);

  if ($_POST['posted'] == 'Y') {
    $catcat_rendo = explode(",", rtrim(preg_replace("/,(?=,)/iu", "", strtr(strip_tags(stripslashes($_POST['catcat_rendo'])), $jokyo)), ","));
    $$catcat_rendocount = count($catcat_rendo);
    for ($i = 0; $i < $$catcat_rendocount; $i++) {
      $catcat .= $catcat_rendo[$i] . ",";
      $catcat_rendo[$i + 1] = preg_replace("/[^0-9]/ui", "$1", $catcat_rendo[$i + 1]);
      $catcat .= $catcat_rendo[++$i] . ",";
    }
    update_option('catcat_rendo', rtrim($catcat, ","));
    //if( is_numeric( $_POST[ 'loglog_table_pa_sen'  ] ) >= 100 ) {update_option('loglog_table_pa_sen', strip_tags(stripslashes($_POST['loglog_table_pa_sen'])));}
  }
// htmlで記述するため一旦phpから外れてend文では隠すようにしている。

  if ($_POST['posted'] == 'Y') :
    ?><div class="updated"><p><strong>設定を保存した気がします！</strong></p></div><?php endif; ?>

  <?php if ($_POST['posted'] == 'Y') : ?>
                                                                                                                                                                                                                            <!-- order = <?php echo $_POST['order']; ?>, striped = <?php echo stripslashes($_POST['order']); ?>, saved = <?php get_option('fjscp_order'); ?> -->
  <?php endif; ?>
  <!-- おそらく設定画面用のクラスなのだろうこれは -->
  <style type="text/css">
    <!--
    .taglist{
      margin-right: 5px;
      margin-bottom: 5px;
      padding:2px 5px;
      border:solid 1px #5f9ea0;
      border-radius: 10px;
      float:left;
      font-size: 16px;
    }
    .one{
      background-color: #afeeee;
    }
    .two{
      background-color: #e0ffff;
    }
    .twree{
      background-color: #ffc0cb;
      border-color: #b22222;
    }
    .form-table{
      width: 90%;
    }
    .kanma{
      float:left;
      padding:2px 0px;
      margin-bottom: 5px;
      font-size: 16px;
    }
    -->
  </style>
  <div class="wrap">
    <h2>Aoringo CAT setterの設定</h2>
    <form method="post" action="<?php
  echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
  // フォームタグはmethodがpostの場合は本文としてデータを送信する。actionにアドレスを入れるとそのアドレスのフォームがリロードされたときなどに入力された状態で出力される。
  ?>">
      <input type="hidden" name="posted" value="Y">
      <p>要望、報告などは<A Href="http://cre.jp/honyomusi/" Target="_blank">http://cre.jp/honyomusi/</A>までお気軽にどうぞ</p>
      <p class="submit"><input type="submit" name="Submit" class="button-primary" value="変更を保存" /></p>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">このブログに登録されているカテゴリーの名前・ＩＤリスト</th>
          <td>
            <?php
            foreach (get_terms('category', 'orderby=id&hide_empty=0') as $cat) {
              echo '<span class="taglist one">';
              echo get_cat_name($cat->term_id) . " ⇒ " . $cat->term_id;
              echo '</span><span class="kanma">,</span>';
            }
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="catcat_rendo">単語と連動するカテゴリー</label>
          <td><textarea name="catcat_rendo" id="catcat_rendo" class="regular-text code" style="width:650px;" rows="2"><?php echo get_option('catcat_rendo'); ?></textarea><br />
            連動したい単語の後にカテゴリー<font color = "red">ＩＤ</font>を入力してください。カンマ（,）で区切ってください。<br />
            <font color = "red">※</font>カテゴリーを新たに作ることはできません。<br />
            <?php
            $taglist = explode(",", get_option('catcat_rendo'));
            $taglist_count = count($taglist);
            for ($i = 0; $i < $taglist_count; $i++) {
              echo '<span class="taglist twree">' . "$taglist[$i] → " . "（" . $taglist[++$i] . "）" . get_cat_name($taglist[$i]) . "</span>";
            }
            ?>
          </td>
        </tr>
      </table>
      <p class="submit"><input type="submit" name="Submit" class="button-primary" value="変更を保存" /></p>
    </form>
  </div>
  <?php
}

function aoringocatsetter_init_option() {
  //インストール時の初期設定
  if (!get_option('aoringocat_installed')) {
    update_option('catcat_rendo', '未分類だぞ、うさみちゃん, 1');
    update_option('aoringocat_installed', 1);
  }
}

register_activation_hook(__FILE__, 'aoringocatsetter_init_option')
?>