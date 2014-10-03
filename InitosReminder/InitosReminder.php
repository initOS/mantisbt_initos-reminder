<?php
# MantisBT - a php based bugtracking system
#
# Copyright (C) 2009 Cas Nuy <cas@nuy.info>.
# Copyright (C) 2011 Markus Schneider <markus.schneider@initos.com >. 
# Copyright (C) 2014 Nikolina Todorova <nikolina.todorova@initos.com>.
#
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT. If not, see <http://www.gnu.org/licenses/>.

/**
* MantisBT Reminder Plugin - add new filter functionality for creating 
* reminder emails.
*/
class InitosReminderPlugin extends MantisPlugin
{
    const FILTER_COUNT = 10;
    
    /**
     * implementation of abstract plugin base class function
     * 
     * @return void
    */
    function register() 
    {
        $this->name = 'initOS Reminder';
        $this->description = 'E-Mail Reminder';
        $this->version = '0.15';
        $this->requires = array('MantisCore' => '1.2.0',);
        $this->author = 'initOS';
        $this->contact = 'info-at-initos.com';
        $this->url = 'http://www.initos.com';
        $this->page = 'config';
    }

    /**
     * plugin config
     * 
     * @return array
     */
    function config() 
    {
        $configuration = array(
            'initosreminder_subject' => 'Task Reminder',
            'initosreminder_body' => 
                'Following filters from the MantisBT system would kindly'.
                ' remind you about outstanding duties.',
            'initosreminder_sender' => 'mantis@localhost',
            'initosreminder_login' => 'admin',
            'initosreminder_filters' => array(1),
            'initosreminder_filters_count' => 
                InitosReminderPlugin::FILTER_COUNT,
        );
        
        for ($filter = 1; $filter <= InitosReminderPlugin::FILTER_COUNT; $filter++) {
            $f_active_name = 'active_'.$filter."_filter";
            $f_active_on_name = 'active_on_'.$filter."_filter";
            $f_label_name = 'label_'.$filter."_filter";
            $f_due_within_name = 'due_within_'.$filter."_filter";
            $f_due_within_active_name 
                = 'due_within_active_'.$filter."_filter";
            $f_due_in_past_name = 'due_in_past_'.$filter."_filter";
            $f_inactive_since_name = 'inactive_since_'.$filter."_filter";
            $f_inactive_since_act_name
                = 'inactive_since_act_'.$filter."_filter";
            $f_use_filter_name 
                = 'use_filter_'.$filter."_filter";
            $f_use_filter_active_name 
                = 'use_filter_active_'.$filter."_filter";
            $f_users_name = 'users_'.$filter."_filter";
            $f_users_active_name 
                = 'users_active_'.$filter."_filter";
            
            $configuration[$f_active_name] =  ON;
            $configuration[$f_active_on_name] 
                = array("Monday","Tuesday","Wednesday","Thursday","Friday");
            $configuration[$f_label_name] 
                = "Bug Reminder for assignee, Weekday, Bug due within 3 days";
            $configuration[$f_due_within_name] = 3;
            $configuration[$f_due_within_active_name] = ON;
            $configuration[$f_due_in_past_name] = OFF;
            $configuration[$f_inactive_since_name] = 7;
            $configuration[$f_inactive_since_act_name] = OFF;
            $configuration[$f_use_filter_name] = 0;
            $configuration[$f_use_filter_active_name] = ON;
            $configuration[$f_users_name] = array();
            $configuration[$f_users_active_name] = OFF;
        }
            
        return $configuration;
    }
    
    /**
     * init - hook domenu function to the EVENT_MENU_MANAGE
     * 
     * @return void
     */
    function init() 
    { 
        plugin_event_hook('EVENT_MENU_MANAGE', 'domenu');
    }

    /**
     * create menu entry
     * 
     * @return array
     */
    function domenu() 
    {
        return array('<a href="'. plugin_page('overview.php') . '">' . 
                     lang_get('initosreminder_overview') . '</a>' );
    }        
}
