<?php
/*
Plugin Name: Widgets Bootstrapfy
Description: Allows to add extra content (Bootstrap classes) to any WordPress widgets 
Version: 1.0
*/

class Widgets_Bootstrapfy {

  function Widgets_Bootstrapfy() {
    $this->init();
  }
  
  /**
   * Init
   */
  function init() {
    // When widget update, modify data to add new params
    add_filter( 'widget_update_callback', array($this, 'widget_update'), 10, 4);
    // Modify widget results adding some HTML 
    add_action('dynamic_sidebar_before', array($this, 'modify_widgets_display'), 10, 2);
    // Modify widget form
    add_action( 'load-widgets.php', array($this, 'modify_widgets_forms') );
  }

  /*
   * When widget update, modify data to add new params
   * 
   * @param array $instance Widget instance
   * @param array $new_instance not used
   * @param array $old_instance not used
   * @param object $obj not used
   * @return array
   */
  function widget_update($instance, $new_instance, $old_instance, $obj ) {
    // If bootstrap columns are sent then add to the widget instance
    if (isset($_POST['widget_bootstrap_columns'])) $instance['widget_bootstrap_columns'] = $_POST['widget_bootstrap_columns'];
    return $instance;
  }
  
  
  /**
   * Modify widget results adding some HTML
   * 
   * @param string $index Widget index
   * @param boolean $bool not used
   */
  function modify_widgets_display($index, $bool) {
    global $wp_registered_widgets;
    // Gets all widgets
    $sidebars_widgets = wp_get_sidebars_widgets();
    foreach ( (array) $sidebars_widgets[$index] as $id ) {
      if ( !isset($wp_registered_widgets[$id]) ) continue;
      // Capture callback function to show special HTML
      $wp_registered_widgets[$id]['_callback'] = $wp_registered_widgets[$id]['callback'];
      $wp_registered_widgets[$id]['callback'] = function($args, $widget_args) use ($id) {
        global $wp_registered_widgets;
        // Gets widget instance data
        $instance = get_option($wp_registered_widgets[$id]['_callback'][0]->option_name);
        // Gets widget bootstrap param
        $bootstrap = isset($instance[$wp_registered_widgets[$id]['_callback'][0]->number]['widget_bootstrap_columns'])? $instance[$wp_registered_widgets[$id]['_callback'][0]->number]['widget_bootstrap_columns'] : false;
        // Wrap the widget into a div with Bootstrap class
        if ($bootstrap) echo '<div class="col-sm-'.$bootstrap.'">';
        call_user_func_array($wp_registered_widgets[$id]['_callback'], array($args, $widget_args));
        if ($bootstrap) echo '</div>';
      };
    }
  }
  
  /**
   * Modify widget form
   */
  function modify_widgets_forms() {
    global $wp_registered_widget_controls;
    // For each widget
    foreach($wp_registered_widget_controls as $id=>$widget) {
      // Capture callback function to show more params into the form
      $wp_registered_widget_controls[$id]['_callback'] = $wp_registered_widget_controls[$id]['callback'];
      $wp_registered_widget_controls[$id]['callback'] = function($data) use ($id) {
        global $wp_registered_widget_controls;
        // Recuperamos los datos del widget para incluir el valor de widget_bootstrap_columns
        $instance = get_option($wp_registered_widget_controls[$id]['_callback'][0]->option_name);
        $widget_bootstrap_columns = isset($instance[$data['number']]) && isset($instance[$data['number']]['widget_bootstrap_columns'])? $instance[$data['number']]['widget_bootstrap_columns']:'';
        // Adds input for bootstrap columns
        echo '<div  class="widget_added"><p>'.__('Number of columns', 'displaynone').' (col-sm-<input type="number" name="widget_bootstrap_columns" min="1" max="12" value="'.$widget_bootstrap_columns.'" />)</p><hr /></div>';
        call_user_func_array($wp_registered_widget_controls[$id]['_callback'], array($data));
      };
    }
  }

}

$widgets_bootstrapfy = new Widgets_Bootstrapfy();