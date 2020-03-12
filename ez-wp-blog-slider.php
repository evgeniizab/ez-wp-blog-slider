<?php
/*
 * Plugin Name: EZ BLOG SLIDER
 * Plugin URI: https://zev-s.com/kod/wordpress/ez-wp-blog-slider/
 * Description: Слайдер записей
 * Version: 0.0.1
 * Author: Evgenii Z
 * Author URI: https://zabairachnyi.com
 * License: GPLv2 or later
 
 Example: [ezwpblogslider id=39]
 */

add_action('wp_print_scripts', 'owl_register_scripts');
add_action('wp_print_styles', 'owl_register_styles');

function owl_register_scripts() {
    wp_register_script('js.wp.zblog', plugins_url('vendor/owl/owl.carousel.js', __FILE__));
    wp_register_script('js.wp.zblog.script', plugins_url('vendor/owl/main.js', __FILE__));

    wp_enqueue_script('jquery');
    wp_enqueue_script('js.wp.zblog');
    wp_enqueue_script('js.wp.zblog.script');
}

function owl_register_styles() {
    wp_register_style('wp.zblog', plugins_url('vendor/owl/owl.carousel.css', __FILE__));
    wp_register_style('wp.zblog.theme', plugins_url('vendor/owl/owl.theme.css', __FILE__));
    wp_register_style('wp.zblog.transitions', plugins_url('vendor/owl/owl.transitions.css', __FILE__));
  	wp_register_style('wp.zblog.style', plugins_url('style.css', __FILE__));


    wp_enqueue_style('wp.zblog');
    wp_enqueue_style('wp.zblog.theme');
    wp_enqueue_style('wp.zblog.transitions');
 wp_enqueue_style('wp.zblog.style');

}


add_shortcode( 'ezwpblogslider', 'ezwpblogslider' );

function ezwpblogslider($atts){

  extract(shortcode_atts(array('id' => '', 'width' => ''), $atts )
  );
  if ($width == '') {$width = '100%';}

  $out ='';
    $query = new WP_Query('cat=' . $id);
    if ($query->have_posts()) {
    	$out .= '<div class="my-deals" style="width:'.$width.'"><div id="wpzblog" class="owl-theme" style="opacity: 1; display: block;width:100%;">';
        while ( $query->have_posts() ) {
            $query->the_post();
        	$image = get_the_post_thumbnail( get_the_ID(), array(400, 400) );
        	$url = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
         	$sr_zblog_pdf = get_post_meta(get_the_ID(), 'sr_zblog_pdf', true); 
        	$sr_zblog_url = get_post_meta(get_the_ID(), 'sr_zblog_url', true); 
        	$out .= '<div class="item">';				 
                $out .= '<div class="my-deals-dsc" style="width:'.$lw.'"><h3 >' . get_the_title() . '</h3>'  . get_the_content();
        	if ($sr_zblog_url != '') { $out .= '<div class="my-deals-btn"><a href="'.$sr_zblog_url.'" target="zblogurl">Смотреть в карточке арбитражных дел</a></div>';}
        	$out .= '</div><div class="my-deals-div-img" style="width:'.$rw.'"><img class="my-deals-img" src="'.$url.'">';
        	if ($sr_zblog_pdf != '') { $out .= '<div class="my-deals-pdf"><a href="'.$sr_zblog_pdf.'" target="zblogpdf"><img src="https://dolgoffnet.ru/img/my-price/pdficon.png">Скачать</a></div>'; }
        	$out .= '</div></div>';
        }
    	$out .= '</div></div>';
} else {$out .= 'Записей для вывода шорткодом нет';}
wp_reset_query();
return $out;
}


function my_extra_fields() {

	add_meta_box( 'extra_fields', 'ez_wp_blog_slider', 'ez_wp_blog_slider_func', 'post', 'normal', 'high'  );
}

// подключаем функцию активации мета блока (my_extra_fields)
add_action('add_meta_boxes', 'my_extra_fields', 1);


// html код блока
function ez_wp_blog_slider_func($post){
	?>
	<p><label>Ссылка на PDF файл <input type="text" name="extra[sr_zblog_pdf]" value="<?php echo get_post_meta($post->ID, 'sr_zblog_pdf', true); ?>"  /></label></p>
	<p><label>Ссылка на внешний документ <input type="text" name="extra[sr_zblog_url]" value="<?php echo get_post_meta($post->ID, 'sr_zblog_url', true); ?>"  /></label></p>

	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
}
// включаем обновление полей при сохранении
add_action( 'save_post', 'my_extra_fields_update', 0 );

// Сохраняем данные, при сохранении поста
function my_extra_fields_update( $post_id ){
	// базовая проверка
	if (
		   empty( $_POST['extra'] )
		|| ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ )
		|| wp_is_post_autosave( $post_id )
		|| wp_is_post_revision( $post_id )
	)
		return false;

	// Все ОК! Теперь, нужно сохранить/удалить данные
	foreach( $_POST['extra'] as $key => $value ){
		if( empty($value) ){
			delete_post_meta( $post_id, $key ); // удаляем поле если значение пустое
			continue;
		}

		update_post_meta( $post_id, $key, $value ); // add_post_meta() работает автоматически
	}

	return $post_id;
}

	
