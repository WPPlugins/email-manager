<?php

/*
  The Schedules class
  Author: Mucunguzi Ayebare
  Author URI: http://zanto.org/
  Author email: ayebare@zanto.org
 */

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPEM_Schedules_Class extends WP_List_Table {
    /*
     * Set up a constructor that references the parent constructor. We 
     */

    protected $schedules;

    function __construct() {
        global $status, $page;
        $this->schedules = WPEM_Schedules::get_schedules();
		$this->schedule_stgs =  WPEM()->modules['settings']->settings['schedule'];
		$this->send_time = ($this->schedule_stgs['send_time']['hh']*3600)+($this->schedule_stgs['send_time']['mn']*60);
        //Set parent defaults
        parent::__construct(array(
            'singular' => 'schedule', //singular name of the listed records
            'plural' => 'schedules', //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    /**
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. 
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     * */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'next_send':
            case 'status':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_interval($item) {
        return $item['frequency'];
    }

    function column_start_date($item) {
        return date_i18n(DATE_FORMAT,  strtotime($item['start_date']));
    }

    function column_end_date($item) {
        return date_i18n(DATE_FORMAT,  strtotime($item['end_date']));
    }
	
	function column_next_send($item) { 
	    
		if($item['status']=='Expired'){
		    return '--';
		}elseif($item['next_send']){
		    return date_i18n(DATE_FORMAT, $item['next_send']).' '.date_i18n(TIME_FORMAT, $this->send_time);
		}else{
		     return "--";
		}
    }

    function prepare_column_data($data) {
        if (empty($data))
            return $data;

        $return_data = $column_data = array();
        foreach ($data as $row_data) {
            //id
            $column_data['id'] = $row_data['id'];
            //template
            $column_data['template'] = $row_data['template'];
            //title
            $column_data['title'] = $row_data['title'];
            //start date
            $column_data['start_date'] = $row_data['start_date'];
            //end date
            $column_data['end_date'] = $row_data['end_date'];
            //interval
            $column_data['frequency'] = $row_data['frequency'];
            // Status
            $start_date_str = strtotime($column_data['start_date']);
            $end_date_str = strtotime($column_data['end_date']);

            if ($end_date_str < strtotime("now")) {
                $column_data['status'] = 'Expired';
            } elseif ($column_data['frequency'] == 'once' && $start_date_str+$this->send_time < strtotime("now")) {
                $column_data['status'] = 'Expired';
            } else {
                $column_data['status'] = 'Active';
            }
            // calculate next send
            if ($column_data['status'] == 'Active') {
                $column_data['next_send'] = $this->next_send($start_date_str, $column_data['frequency'], $end_date_str);
            } else {
                $column_data['next_send'] = 'Expired';
            }
            $return_data[$row_data['id']] = $column_data;
        }
        return $return_data;
    }

    function next_send($start_date_str, $frequency, $end_date_str) {

        if ($end_date_str+$this->send_time < time()) {
            return false;
        }
		
		$next_send_str = WPEM_Schedules::next_send($start_date_str, $frequency, $end_date_str,$this->send_time);

        if ($next_send_str) {
		    return $next_send_str;
        } else {
            return false;
        }
    }

    /**
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (schedule title only)
     * */
    function column_title($item) {

        //Build row actions
        $actions = array(
            'edit' => sprintf('<a href="%s&action=%s&schedule=%s&temp_id=%s">Edit</a>', admin_url('admin.php?page=wpem_mail&mail_scope=schedules'), 'edit', $item['id'], $item['template']),
            'delete' => sprintf('<a href="%s&action=%s&schedule=%s">Delete</a>', admin_url('admin.php?page=wpem_mail&mail_scope=schedules'), 'delete', $item['id']),
        );

        //Return the title contents
        return sprintf('<a href="%1$s" class="row-title" title="%2$s">%2$s</a> %3$s',
                        /* $1%s */ admin_url('admin.php?page=wpem_mail&mail_scope=schedules&action=edit&schedule=' . $item['id']),
                        /* $2%s */ $item['title'],
                        /* $3%s */ $this->row_actions($actions)
        );
    }

    /**
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. =
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (schedule title only)
     * * */
    function column_cb($item) {
        return sprintf(
                        '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                        /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("schedule")
                        /* $2%s */ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    /**
     * REQUIRED! This method dictates the table's columns and titles. 
     * @return array An associative array containing column information: 'slugs'=>'Visible Names'
     * */
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'title' => 'Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'interval' => 'Interval',
            'next_send' => 'Next Send',
            'status' => 'Status'
        );
        return $columns;
    }

    /**
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     * */
    function get_sortable_columns() {
        $sortable_columns = array(
		    'id' => array('id', true), //true means it's already sorted
            'title' => array('title',false), //true means it's already sorted
            'start_date' => array('start_date',false),
            'end_date' => array('end_date',false),
			'interval' => array('frequency',false),
			'next_send' => array('next_send',false),
			'status' => array('status',false)
        );
        return $sortable_columns;
    }

    /**
     * include bulk actions in your list table
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Names'
     * */
    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    

    /**     * ***********************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     * ************************************************************************ */
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */


        //$schedules = WPEM_Schedules::get_schedules();
        $data = $this->prepare_column_data($this->schedules);

        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a, $b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to desc
			if(isset($_REQUEST['orderby'])){
			switch(($_REQUEST['orderby'])){
			    case 'start_date':
                case 'end_date':
			    case 'next_send':
				$result = strcmp(strtotime($a[$orderby]), strtotime($b[$orderby])); //Determine sort order
				break;
				default:
				      $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
				break;
			}
			}else{
			    $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
			}
            
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');


        /*         * *********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         * ******************************************************************** */


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

}