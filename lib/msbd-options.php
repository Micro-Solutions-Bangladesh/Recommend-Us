<?php
class MSBDOptions {

    var $options_name;

    var $defaults;

    /**
     *
     * @var MIXED STRING/BOOL
     */
    var $updated = FALSE;

    /**
     *
     * @var RecommendUs
     */
    var $core;

    var $old_pt_slug;
    var $new_pt_slug;

    /**
     *
     * @param RecommendUs $core
     */
    public function __construct($core) {
        $this->core = $core;
        if (isset($_POST['update'])) {
            $this->updated = $_POST['update'];
        }
        $this->options_name = $core->options_name;
        $this->defaults = array(
            'version' => '1.0.0',
            'reviews_order' => 'asc',
            'ms_authority_label' => 'manage_options',
            'ms_give_credit'=> 'checked',
            'ms_allowed_html_tags'=> 'strong,bold,i,u,br',
            'show_date' => '',
            'add_glyphicons' => 'checked',
            'add_msrp_styles' => 'checked',
          );
        if ($this->get_option() == FALSE) {
            $this->set_to_defaults();
        }
    }

    public function set_to_defaults() {
        delete_option($this->options_name);
        foreach ($this->defaults as $key=>$value) {
            $this->update_option($key, $value);
        }
    }

    public function update_options() {
        if (isset($_POST['update']) && $_POST['update'] === 'msr-update-options') {
            
            if (!isset($_POST['ms_give_credit'])) { $_POST['ms_give_credit'] = NULL; }
            if (!isset($_POST['show_date'])) { $_POST['show_date'] = NULL; }
            if (!isset($_POST['add_msrp_styles'])) { $_POST['add_msrp_styles'] = NULL; }
            if (!isset($_POST['add_glyphicons'])) { $_POST['add_glyphicons'] = NULL; }
            if (!isset($_POST['ms_allowed_html_tags'])) { $_POST['ms_allowed_html_tags'] = NULL; }
            
            
            
            $current_settings = $this->get_option();
            $clean_current_settings = array();
            foreach ($current_settings as $k=>$val) {
                if ($k != NULL) {
                    $clean_current_settings[$k] = $val;
                }
            }
            $this->defaults = array_merge($this->defaults, $clean_current_settings);
            $update = array_merge($this->defaults, $_POST);
            $data = array();
            foreach ($update as $key=>$value) {
                if ($key != 'update' && $key != NULL) {
                    $data[$key] = $value;
                }
            }

            $this->update_option($data);
            $_POST['update'] = NULL;
            $this->updated = 'wpm-update-options';
        }
        else if (isset($_POST['update']) && ($_POST['update'] === 'msrp-update-support' || $_POST['update'] === 'msrp-update-support-prompt')) {
            $current_settings = $this->get_option();
            $this->defaults = array_merge($this->defaults, $current_settings);
            $update = array_merge($this->defaults, $_POST);
            $data = array();
            foreach ($update as $key=>$value) {
                if ($key != 'update' && $key != NULL) {
                    $data[$key] = $value;

                }
            }
            $this->update_option($data);
            $_POST['update'] = NULL;
            $this->updated = 'msrp-update-support';
        }
    }



    // From metabox v1.0.6

    /**
    * Gets an option for an array'd wp_options,
    * accounting for if the wp_option itself does not exist,
    * or if the option within the option
    * (cue Inception's 'BWAAAAAAAH' here) exists.
    * @since  Version 1.0.0
    * @param  string $opt_name
    * @return mixed (or FALSE on fail)
    */
    public function get_option($opt_name = '') {
       $options = get_option($this->options_name);

       // maybe return the whole options array?
       if ($opt_name == '') {
           return $options;
       }

       // are the options already set at all?
       if ($options == FALSE) {
           return $options;
       }

       // the options are set, let's see if the specific one exists
       if (! isset($options[$opt_name])) {
           return FALSE;
       }

       // the options are set, that specific option exists. return it
       return $options[$opt_name];
    }

    /**
    * Wrapper to update wp_options. allows for function overriding
    * (using an array instead of 'key, value') and allows for
    * multiple options to be stored in one name option array without
    * overriding previous options.
    * @since  Version 1.0.0
    * @param  string $opt_name
    * @param  mixed $opt_val
    */
    public function update_option($opt_name, $opt_val = '') {
       // ----- allow a function override where we just use a key/val array
       if (is_array($opt_name) && $opt_val == '') {
           foreach ($opt_name as $real_opt_name => $real_opt_value) {
               $this->update_option($real_opt_name, $real_opt_value);
           }
       }
       else {
           $current_options = $this->get_option(); // get all the stored options

           // ----- make sure we at least start with blank options
           if ($current_options == FALSE) {
               $current_options = array();
           }

           // ----- now save using the wordpress function
           $new_option = array($opt_name => $opt_val);
           update_option($this->options_name, array_merge($current_options, $new_option));
       }
    }

    /**
    * Given an option that is an array, either update or add
    * a value (or data) to that option and save it
    * @since  Version 1.0.0
    * @param  string $opt_name
    * @param  mixed $key_or_val
    * @param  mixed $value
    */
    public function append_to_option($opt_name, $key_or_val, $value = NULL, $merge_values = TRUE) {
       $key = '';
       $val = '';
       $results = $this->get_option($opt_name);

       // ----- always use at least an empty array!
       if (! $results) {
           $results = array();
       }

       // ----- allow function override, to use automatic array indexing
       if ($value === NULL) {
           $val = $key_or_val;

           // if value is not in array, then add it.
           if (! in_array($val, $results)) {
               $results[] = $val;
           }
       }
       else {
           $key = $key_or_val;
           $val = $value;

           // ----- should we append the array value to an existing array?
           if ($merge_values && isset($results[$key]) && is_array($results[$key]) && is_array($val)) {
                   $results[$key] = array_merge($results[$key], $val);
           }
           else {
                   // ----- don't care if key'd value exists. we override it anyway
                   $results[$key] = $val;
           }
       }

       // use our internal function to update the option data!
       $this->update_option($opt_name, $results);
    }

    public function update_messages() {
        if ($this->updated == 'msr-update-options') {
            echo '<div class="updated">The options have been successfully updated.</div>';
            $this->updated = FALSE;
        }
        else if ($this->updated == 'msrp-update-support') {
             echo '<div class="updated">Thank you for supporting the development team! We really appreciate how awesome you are.</div>';
            $this->updated = FALSE;
        }
    }
}
