<?php
/*
Plugin Name: Dynamic Blocks for Post/Page
Version: 1
Description: Add dynamic blocks for page/post
Author: CodeG.pl
 */

/**
 * Unistall the plugin
 */
function uninstall()
{
    global $wpdb;
    $table = $wpdb->prefix . 'postmeta';
    $wpdb->delete($table, array('meta_key' => '_dynamic_blocks'));
}
register_deactivation_hook(__FILE__, 'uninstall');

/**
 * Add meta box in page
 */
function admin_init()
{
    add_meta_box(
        'dynamic_blocks',
        __('Dynamic Blocks', 'dynamic_blocks'),
        'dynamic_blocks_render',
        'page',
        'side',
        'default');
}
add_action('admin_init', 'admin_init');

/**
 * Render HTML blocks / script / style
 */
function dynamic_blocks_render()
{
    global $post;
    wp_nonce_field(plugin_basename(__FILE__), 'dynamicMeta_noncename');
    ?>
    <div id="meta_inner">
        <?php

    $dynamic_blocks = get_post_meta($post->ID, 'dynamic_blocks', true);

    $c = 0;
    if (count($dynamic_blocks) > 0) {
        if (is_array($dynamic_blocks)) {
            foreach ($dynamic_blocks as $block) {
                if (isset($block['title'])) {
                    echo '<div class="dynamic_block">';
                    echo '<input type="text"  name="dynamic_blocks[' . $c . '][title]" value="' . $block['title'] . '" placeholder="'.__('Title').'" class="dynamic_blocks_title"  style=""/>';
                    wp_editor(htmlspecialchars_decode($block['content']), 'dynamic_blocks_editor_' . $c, $settings = array('textarea_name' => 'dynamic_blocks[' . $c . '][content]', 'textarea_rows' => '5'));
                    echo '<span class="remove"><button type="button" class="button button-primary button-small" style="float:right" >' . __('Remove') . '</button></span></div>';
                    $c += 1;
                }
            }}
    }
    ?>
    </div>
    <span class="add"><button type="button" class="button button-primary button-small" style="margin-top: 10px;"><?php echo __('Add'); ?></button></span>
    <!-- STYLE -->
    <style>
        #dynamic_blocks {
            border:1px solid #0bb596;
        }
        #dynamic_blocks h2.hndle.ui-sortable-handle {
            background-color: #0bb596;
            color:#fff;
            text-transform: uppercase;
        }
        #dynamic_blocks input.dynamic_blocks_title {
            padding-top: 10px;
            padding-bottom: 10px;
            width: 100%;
            font-weight: bold;
            border: 2px solid #506964;
        }
        #dynamic_blocks .dynamic_block {
            margin-bottom: 20px;
            padding-bottom: 40px;
            border-bottom: 2px solid #0bb596;
        }
        #dynamic_blocks .dynamic_block .wp-editor-wrap {
            margin-top: 10px;
        }
    </style>
    <!-- END STYLE -->
    <!-- SCRIPT -->
    <script>
        var $ =jQuery.noConflict();
        $(document).ready(function() {
            var count = <?php echo $c; ?>;
            $(".add").click(function() {
                count = count + 1;
                add_editor(count);
                return false;
            });
            $(".remove").live('click', function() {
                $(this).parent().remove();
            });
        });
        function add_editor(id) {
        My_New_Global_Settings =  tinyMCEPreInit.mceInit.content;
        jQuery.post(ajaxurl,
            { action: "dynamics_blocks_add_editor",id: id},
            function(response,status){
                if(status=='success') {
                $('#dynamic_blocks .inside #meta_inner').append('<div class="dynamic_block">  \n\
                    <input type="text"  name="dynamic_blocks['+id+'][title]" value="" placeholder="<?php echo __('Title');?>" class="dynamic_blocks_title"  style=""/>\n\
                     '+response+'\n\
                <span class="remove"><button type="button" class="button button-primary button-small" style="float:right" ><?php echo __('Remove'); ?></button></span>' );
                tinymce.init(My_New_Global_Settings);
                tinyMCE.execCommand('mceAddEditor', false, "dynamic_blocks_editor_"+id);
                quicktags({id : "dynamic_blocks_editor_"+id});
              }
            }
        );
    }
    </script>
    <!-- END SCRIPT -->

    <?php

}

/**
 * Save data
 */
function save_dynamic_blocks($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['dynamicMeta_noncename'])) return;
    if (!wp_verify_nonce($_POST['dynamicMeta_noncename'], plugin_basename(__FILE__))) return;

    $dynamic_blocks = $_POST['dynamic_blocks'];
    update_post_meta($post_id, 'dynamic_blocks', $dynamic_blocks);
}
add_action('save_post', 'save_dynamic_blocks');

add_action('wp_ajax_dynamics_blocks_add_editor', function () {
    wp_editor('', 'dynamic_blocks_editor_' . $_POST['id'], $settings = array('textarea_name' => 'dynamic_blocks[' . $_POST['id'] . '][content]', 'textarea_rows' => '5'));
});

/**
 * Shortcode for template
 * Example: [dynamic_blocks root-class='col-lg-3' template='<div class="dynamic-blocks-single"><h2>{title}</h2><br>{content}</div>']
 */
function dynamic_blocks_shortcode($atts)
{
    global $post;
    $html = '';
    $dynamic_blocks = get_post_meta($post->ID,'dynamic_blocks',true);
    if($dynamic_blocks && is_array($dynamic_blocks) && count($dynamic_blocks)>0) {
        $html .= '<div class="'.(array_key_exists('root-class', $atts)?$atts['root-class']:'').'" id="dynamic_blocks">';
        foreach( $dynamic_blocks as $block ) {
               if(array_key_exists('template', $atts)) $template = htmlspecialchars_decode($atts['template']);
               else $template = '<div class="dynamic-blocs-single">{title}<br>{content}</div>';

               $html .= str_replace(['{title}','{content}'], [$block['title'],apply_filters('the_content', $block['content'])],$template);
          }
          $html .= '</div>';
    }
    return $html;
}
add_shortcode('dynamic_blocks', 'dynamic_blocks_shortcode');

/**
 * Variable for template
 * Example: if(function_exists('get_dynamic_blocks')) var_dump(get_dynamic_blocks());
 */
function get_dynamic_blocks() {
    global $post;
    $data = [];
    $dynamic_blocks = get_post_meta($post->ID,'dynamic_blocks',true);
    if($dynamic_blocks && is_array($dynamic_blocks) && count($dynamic_blocks)>0) {
        foreach( $dynamic_blocks as $block ) {
            $data[] = ['title'=>$block['title'],'content'=>apply_filters('the_content', $block['content'])];
        }
    }
    return $data;
}
add_action( 'init', 'get_dynamic_blocks' );
