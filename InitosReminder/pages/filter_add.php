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


$f_initosreminder_filters_count = plugin_config_get('initosreminder_filters_count');
$f_initosreminder_filters = plugin_config_get('initosreminder_filters');

if (count($f_initosreminder_filters) == $f_initosreminder_filters_count) {
    print_successful_redirect(plugin_page('config', true));
    break;
}

for ($i = 1; $i <= $f_initosreminder_filters_count;$i++) {
    if (in_array($i, $f_initosreminder_filters) == FALSE) {
        $filter = $i;
        break;
    }
}

$f_initosreminder_filters[] = $filter;
plugin_config_set('initosreminder_filters', $f_initosreminder_filters);

 // ======================== PREPARE FILTER NAMES ======================== //
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

 // ======================== SET DEFAULT VALUES ======================== //
plugin_config_set($f_active_name, ON);
plugin_config_set(
    $f_active_on_name,
    array("Monday","Tuesday","Wednesday","Thursday","Friday")
);
plugin_config_set(
    $f_label_name,
    'Bug Reminder for assignee, Weekday, Bug due within 3 days'
);
plugin_config_set($f_due_within_name, 3);
plugin_config_set($f_due_within_active_name, ON);
plugin_config_set($f_due_in_past_name, ON);
plugin_config_set($f_inactive_since_name, 7);
plugin_config_set($f_inactive_since_act_name, OFF);
plugin_config_set($f_use_filter_name, 0);
plugin_config_set($f_use_filter_active_name, ON);
plugin_config_set($f_users_name, array());
plugin_config_set($f_users_active_name, OFF);
    
print_successful_redirect(plugin_page('config', true));
