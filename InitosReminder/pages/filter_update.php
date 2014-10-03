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


form_security_validate('plugin_initosreminder_config_update');
$f_initosreminder_filters = plugin_config_get('initosreminder_filters');

// ========================== GENERIC VALUES ========================== //
$f_initosreminder_subject
    = gpc_get_string('initosreminder_subject', 'Task Reminder');
$f_initosreminder_body
    = gpc_get_string('initosreminder_body', 'Task Reminder');
$f_initosreminder_sender
    = gpc_get_string('initosreminder_sender', 'mantis@initos.com');
$f_initosreminder_login = gpc_get_string('initosreminder_login', 'admin');

plugin_config_set('initosreminder_subject', $f_initosreminder_subject);
plugin_config_set('initosreminder_body', $f_initosreminder_body);    
plugin_config_set('initosreminder_sender', $f_initosreminder_sender);    
plugin_config_set('initosreminder_login', $f_initosreminder_login);

foreach ($f_initosreminder_filters as $filter) {
    // ======================= PREPARE FILTER NAMES ======================= //
    $f_active_name = 'active_'.$filter."_filter";
    $f_active_on_name = 'active_on_'.$filter."_filter";
    $f_label_name = 'label_'.$filter."_filter";
    $f_due_within_name = 'due_within_'.$filter."_filter";
    $f_due_within_active_name = 'due_within_active_'.$filter."_filter";
    $f_due_in_past_name = 'due_in_past_'.$filter."_filter";
    $f_inactive_since_name = 'inactive_since_'.$filter."_filter";
    $f_inactive_since_act_name = 'inactive_since_act_'.$filter."_filter";
    $f_use_filter_name = 'use_filter_'.$filter."_filter";
    $f_use_filter_active_name = 'use_filter_active_'.$filter."_filter";
    $f_users_name = 'users_'.$filter."_filter";
    $f_users_active_name = 'users_active_'.$filter."_filter";
    
    // ========================= GET POST VALUES ========================= //
    $f_initosreminder_active_filter = gpc_get_int($f_active_name, ON);
    $f_initosreminder_active_on_filter
        = gpc_get_string_array(
            $f_active_on_name,
            'Monday,Tuesday,Wednesday,Thursday,Friday'
        );
    $f_initosreminder_label_filter
        = gpc_get_string(
            $f_label_name,
            'Bug Reminder for assignee, Weekday, Bug due within 3 days'
        );
    $f_initosreminder_due_within_filter = gpc_get_int($f_due_within_name, 3);
    $f_initosreminder_due_within_active_filter
        = gpc_get_int($f_due_within_active_name, ON);
    $f_initosreminder_due_in_past_filter
        = gpc_get_int($f_due_in_past_name, ON);
    $f_initosreminder_inactive_since_filter
        = gpc_get_int($f_inactive_since_name, 7);
    $f_initosreminder_inactive_since_act_filter
        = gpc_get_int($f_inactive_since_act_name, OFF);
    $f_initosreminder_use_filter_filter = gpc_get_int($f_use_filter_name, 0);
    $f_initosreminder_use_filter_active_filter
        = gpc_get_int($f_use_filter_active_name, ON);
    $f_initosreminder_users_filter
        = gpc_get_int_array($f_users_name, array());
    $f_initosreminder_users_active_filter
        = gpc_get_int($f_users_active_name, OFF);
    
    // ==================== FILTER SET CONFIG OPTIONS ==================== //
    plugin_config_set($f_active_name, $f_initosreminder_active_filter);    
    plugin_config_set($f_active_on_name, $f_initosreminder_active_on_filter);    
    plugin_config_set($f_label_name, $f_initosreminder_label_filter);

    plugin_config_set(
        $f_due_within_name,
        $f_initosreminder_due_within_filter
    );    
    plugin_config_set(
        $f_due_within_active_name,
        $f_initosreminder_due_within_active_filter
    );    
    plugin_config_set(
        $f_due_in_past_name,
        $f_initosreminder_due_in_past_filter
    );    
    plugin_config_set(
        $f_inactive_since_name,
        $f_initosreminder_inactive_since_filter
    );    
    plugin_config_set(
        $f_inactive_since_act_name,
        $f_initosreminder_inactive_since_act_filter
    );        

    plugin_config_set(
        $f_use_filter_name,
        $f_initosreminder_use_filter_filter
    );
    plugin_config_set(
        $f_use_filter_active_name,
        $f_initosreminder_use_filter_active_filter
    );
    plugin_config_set($f_users_name, $f_initosreminder_users_filter);
    plugin_config_set(
        $f_users_active_name,
        $f_initosreminder_users_active_filter
    );
}                                                                              
    
form_security_purge('plugin_initosreminder_config_update');
print_successful_redirect(plugin_page('config', true));
