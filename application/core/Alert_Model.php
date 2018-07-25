<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Add alert handler for success, info, warning and error(danger)
 *
 * TODO: Add test cases
 *
 * @author Shavi Pathania <shavi@xorlabs.in>
 */

class Alert_Model extends CI_Model {

    /* --------------------------------------------------------------
     * VARIABLES
     * ------------------------------------------------------------ */

    /**
     * A multidimensional array of alerts.
     */
    protected $alerts;

    /**
     * Delimiters for alerts.
     */
    protected $alert_start_delimiter = '<p>';
    protected $alert_end_delimiter = '</p>';

    /* --------------------------------------------------------------
     * GENERIC METHODS
     * ------------------------------------------------------------ */

    /**
     * Initialise the model, tie into the CodeIgniter superobject and
     * try our best to guess the table name.
     */
    public function __construct()
    {
        parent::__construct();

        // Init the alerts
        $this->alerts = $this->_init_alerts();
    }

    /* --------------------------------------------------------------
     * ALERTS METHODS
     * ------------------------------------------------------------ */

    /**
     * Initialization for alerts
     */
    protected function _init_alerts()
    {
        return array( 
                    'success' => array(),
                    'info' => array(),
                    'warning' => array(),
                    'error' => array() 
                );
    }

    /**
     * Set delimiter
     */
    public function set_alert_delimiters($start, $end)
    {
        $this->alert_start_delimiter = $start;
        $this->alert_end_delimiter = $end;
    }

    /**
     * Setter for alert
     */
    public function set_alert($type, $msg)
    {
        $this->alerts[$type][] = $msg;
    }

    /**
     * Setter for alerts
     */
    public function set_alerts($type, $arr)
    {
        $this->alerts[$type] = array_merge($this->alerts[$type], $arr);
    }

    /**
     * Getter for alert
     */
    public function get_alert($type)
    {
        return $this->alerts[$type];
    }

    /**
     * Get concatenated alerts of particular type
     */
    public function alerts($type)
    {
        $output = '';
        foreach ($this->alerts[$type] as $alert)
        {
            $output .= $this->alert_start_delimiter . $alert . $this->alert_end_delimiter;
        }
        return $output;
    }

    /**
     * Get alerts array
     */
    public function alerts_array()
    {
        return $this->alerts;
    }

    /**
     * Clear alerts of all types
     * OR just provided type
     */
    public function clear_alerts($type = NULL)
    {
        if ( ! $type)
        {
            $this->alerts = NULL;
            $this->alerts = $this->_init_alerts();
        }
        else
        {
            $this->alerts[$type] = array();
        }
    }
	

}

/* End of file Alert_Model.php */
/* Location: ./application/core/Alert_Model.php */