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

$debug    = false;

require_once('../../../core.php');

$t_core_path = config_get('core_path');
$t_bug_table = db_get_table('mantis_bug_table');
$t_user_table = db_get_table('mantis_user_table');
$t_project_table = db_get_table('mantis_project_table');

// ugly hack: overwrite global variable
//so that 'auth_get_current_user_id' runs in CLI mode
$g_cache_current_user_id = 1;

$number_of_columns = 10;
$current_weekeday = date("l");

//set $g_plugin_current[0] global variable to 'InitosReminder',
//otherwise plugin_config_get() is not working for me
plugin_push_current('InitosReminder');

// generel filter:
$f_initosreminder_subject = plugin_config_get('initosreminder_subject');
$f_initosreminder_body = plugin_config_get('initosreminder_body');
$f_initosreminder_sender = plugin_config_get('initosreminder_sender');
$f_initosreminder_login = plugin_config_get('initosreminder_login');
$f_initosreminder_filters = plugin_config_get('initosreminder_filters');

/**
 * Generate email for $t_email with content $buglist
 *  
 * @param string $t_email Email of the receiver 
 * @param string $buglist Main content of the email 
 * 
 * @return void
 */
function generate_email( $t_email, $buglist )
{
    $f_initosreminder_subject = plugin_config_get('initosreminder_subject');
    $f_initosreminder_body = plugin_config_get('initosreminder_body');

    $output = $f_initosreminder_body."\n\n";
    $output .=  $buglist;

    if ( !is_blank($t_email) ) {
        email_store($t_email, $f_initosreminder_subject, $output);
    }
}

/**
 * Return formatted string that contains bug project, id, summary and url
 * 
 * @param object $row_bugs       Bug object
 * @param array  $projects_array Array that contains project 
 * names as values and project ids for keys
 * 
 * @return string
 */
function format_bug_row($row_bugs, $projects_array)
{
    $project_name = "[".$projects_array[$row_bugs[project_id]]." ".$row_bugs[id]."]";
    $output = $project_name." ".$row_bugs['summary']."\n";
    $output .= "    " . string_get_bug_view_url_with_fqdn($row_bugs['id']) . "\n";
    return $output;
}

$user_query="select id,username,realname,email from $t_user_table";
$user_results = db_query_bound($user_query);
if (!$user_results) {
    echo lang_get('mailgenerate_no_users'); 
    return;
}

// get all projects, create array with keys = project ids, values = project names
$projects_query = "select id,name from $t_project_table";
$projects_result = db_query_bound($projects_query);
$projects_array = array();
while (  $row  =  db_fetch_array($projects_result) ) {
    $projects_array[$row[id]] = $row[name];
}

//run through the filters for each user
while ($row_users = db_fetch_array($user_results)) {
    $output = "";
    foreach ($f_initosreminder_filters as $filter) {
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

        // ======================== GET CONFIG VALUES ======================== //
        $f_initosreminder_active_filter = plugin_config_get($f_active_name);
        $f_initosreminder_active_on_filter = plugin_config_get($f_active_on_name);
        $f_initosreminder_label_filter = plugin_config_get($f_label_name);
        $f_initosreminder_due_within_filter = plugin_config_get($f_due_within_name);
        $f_initosreminder_due_within_active_filter
            = plugin_config_get($f_due_within_active_name);
        $f_initosreminder_due_in_past_filter
            = plugin_config_get($f_due_in_past_name);
        $f_initosreminder_inactive_since_filter
            = plugin_config_get($f_inactive_since_name);
        $f_initosreminder_inactive_since_act_filter
            = plugin_config_get($f_inactive_since_act_name);
        $f_initosreminder_use_filter_filter
            = plugin_config_get($f_use_filter_name);
        $f_initosreminder_use_filter_active_filter
            = plugin_config_get($f_use_filter_active_name);
        $f_initosreminder_users_filter = plugin_config_get($f_users_name);
        $f_initosreminder_users_active_filter
            = plugin_config_get($f_users_active_name);

        $due_limit_filter
            = time(true)+ ($f_initosreminder_due_within_filter*24*60*60);
        $inactive_limit_filter
            = time(true) - ($f_initosreminder_inactive_since_filter*24*60*60);

        //check if the users tuple for the current filter is on
        if ($f_initosreminder_users_active_filter == 1) {
            //check if the user is in the users tuple
            //and if its not contuniu to the next filter
            if ( in_array($row_users['id'], $f_initosreminder_users_filter) == FALSE ) {
                continue;
            }
        }

        //-- ==================== RUN THROUGH THE FILTER ================== //
        if ($f_initosreminder_active_filter != '1' AND $debug == true) {
             echo  "<table class='width100'".
                   " cellspacing='1'".
                   " align='center'".
                   " style='padding-left: 20px;' bgcolor='#C8C8E8'>".
                   "<tr>";
             echo  "<td class='form-title' colspan='".
                    $number_of_columns."'>Filter #".
                    $filter.": ".
                    $f_initosreminder_label_filter." <b>".
                    lang_get('mailgenerate_filter_set_to_inactive').
                    "</b></td></tr></table>";
        } elseif (!in_array($current_weekeday, $f_initosreminder_active_on_filter)
            AND $debug == true 
        ) {
            echo "<table class='width100'".
                 " cellspacing='1'".
                 " align='center'".
                 " style='padding-left: 20px;' bgcolor='#C8C8E8'>".
                 "<tr>";
            echo "<td class='form-title' colspan='".
                 $number_of_columns.
                 "'>Filter #".
                 $filter.
                 ": ".
                 $f_initosreminder_label_filter.
                 " <b>".
                 lang_get('mailgenerate_filter_is_inactive_today').
                 "</b></td></tr></table>";
        } elseif (in_array($current_weekeday, $f_initosreminder_active_on_filter)
            AND $f_initosreminder_active_filter == '1'
        ) {

            $bug_query="select id,".
                       "reporter_id,".
                       "handler_id,".
                       "summary,".
                       "due_date,".
                       "priority,".
                       "severity,".
                       "status,".
                       "project_id".
                       " from $t_bug_table ";

            if ($f_initosreminder_due_within_active_filter == '1') {
                $bug_query 
                    .= "WHERE due_date>1 AND due_date <= $due_limit_filter ";
            }

            // for correct query syntax
            if ($f_initosreminder_due_in_past_filter == '0' 
                && $f_initosreminder_due_within_active_filter == '0'
            ) {
                $bug_query .= "WHERE due_date>1 AND due_date >= ".time(true)." ";
            } elseif ($f_initosreminder_due_in_past_filter == '0') {
                $bug_query .= "AND due_date>1 AND due_date >= ".time(true)." ";
            }

            if ($f_initosreminder_inactive_since_act_filter == '1'
                && ($f_initosreminder_due_within_active_filter == '1'
                || $f_initosreminder_due_in_past_filter == '0')
            ) {
                $bug_query .= "AND last_updated <= $inactive_limit_filter ";
            } elseif ($f_initosreminder_inactive_since_act_filter == '1') {
                $bug_query .= "WHERE last_updated <= $inactive_limit_filter ";
            }
            
            $bug_query .= "ORDER BY due_date ";

            $bug_results = db_query_bound($bug_query);
            if ($debug == true) {
                // the query ... for debug reasons ...
                echo "<table class='width100'".
                     " cellspacing='1'".
                     " align='center'".
                     " style='margin-top: 00px;'>";
                echo "<tr><td class='form-title'>".
                     lang_get(' mailgenerate_query').
                     $filter.
                     ":<br> ".
                     $bug_query.
                     "</td></tr>";
                echo "</table>";
            }

            // store bugs that match the 'due_date' criterion
            $interesting_bugs = array();
            while ($row_bugs = db_fetch_array($bug_results)) {
                $interesting_bugs[$row_bugs['id']] = $row_bugs;
            }

            $t_filter_string 
                = filter_db_get_filter($f_initosreminder_use_filter_filter);
            if ($f_initosreminder_use_filter_active_filter == 1
                && !empty($t_filter_string)
            ) {
                $f_output = "";
                $t_deserialize_filter_string = filter_deserialize($t_filter_string);
                foreach ($t_deserialize_filter_string['reporter_id'] as $key => $reporter) {
                    if ($reporter == META_FILTER_MYSELF) {
                        $t_deserialize_filter_string['reporter_id'][$key]
                            = $row_users['id'];
                    }
                }
                foreach ($t_deserialize_filter_string['handler_id'] as $key => $handler) {
                    if ($handler == META_FILTER_MYSELF) {
                        $t_deserialize_filter_string['handler_id'][$key]
                            = $row_users['id'];
                    }
                }
                // reset value of variable
                $t_per_page = null;
                $t_rows = filter_get_bug_rows(
                    $f_page_number, $t_per_page, $t_page_count,
                    $t_bug_count, $t_deserialize_filter_string,
                    helper_get_current_project()
                );
                foreach ($t_rows as $filter_row_bug) {
                    if (!array_key_exists($filter_row_bug->id, $interesting_bugs)) {
                        continue;
                    }
                    $f_row_bug = $interesting_bugs[$filter_row_bug->id];
                    $f_output .= format_bug_row($f_row_bug, $projects_array);
                }
            } else {
                $f_output = "";
                while ($row_bugs = db_fetch_array($bug_results)) {
                    $f_output .= format_bug_row($row_bugs, $projects_array);
                }
            }
            if (!empty($f_output)) {
                $output .= lang_get('mailgenerate_filter').
                           " ".$f_initosreminder_label_filter."\n";
                $output .= $f_output;
            }
        }
    }
    if (!empty($output)) {
        generate_email($row_users['email'], $output);
    }
    print $row_users['email'] . " gets:\n" . $output;
}

if (OFF == config_get('email_send_using_cronjob')) {
    email_send_all();
}
