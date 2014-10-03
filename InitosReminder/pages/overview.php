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


html_page_top(plugin_lang_get('overview'));
print_manage_menu();

$debug    = true;

require_once( 'core.php' );
$t_core_path = config_get('core_path');
$t_bug_table = db_get_table('mantis_bug_table');
$t_user_table = db_get_table('mantis_user_table');
$t_project_table = db_get_table('mantis_project_table');

$number_of_columns = 10;
$current_weekeday = date("l");
            
// general filter:
$f_initosreminder_subject = plugin_config_get('initosreminder_subject');    
$f_initosreminder_sender = plugin_config_get('initosreminder_sender');    
$f_initosreminder_login = plugin_config_get('initosreminder_login');
$f_initosreminder_filters = plugin_config_get('initosreminder_filters');    

$user_query="select id,username,realname,email from $t_user_table";
$user_results = db_query_bound($user_query);
if (!$user_results) {
    echo lang_get('overview_no_users');
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
        // ===================== PREPARE FILTER NAMES ===================== //
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
        
        // ====================== GET CONFIG VALUES ====================== //
        $f_initosreminder_active_filter = plugin_config_get($f_active_name);
        $f_initosreminder_active_on_filter
            = plugin_config_get($f_active_on_name);
        $f_initosreminder_label_filter = plugin_config_get($f_label_name);
        $f_initosreminder_due_within_filter
            = plugin_config_get($f_due_within_name);
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
            if (in_array($row_users['id'], $f_initosreminder_users_filter) == FALSE) {
                continue;
            }
        }
        
        //-- =================== RUN THROUGH THE FILTER =================== //
        if ($f_initosreminder_active_filter != '1' AND $debug == true) {
             $output .= "<table class='width100' cellspacing='1' align='center'".
                        " style='padding-left: 20px;' bgcolor='#C8C8E8'><tr>";
             $output .= "<td class='form-title' colspan='".$number_of_columns.
                        "'>Filter #".$filter.": ".$f_initosreminder_label_filter.
                        " <b>".lang_get('overview_filter_set_to_inactive').
                        "</b></td></tr></table>";
        } elseif (!in_array($current_weekeday, $f_initosreminder_active_on_filter) 
            AND $debug == true
        ) {
            $output .= "<table class='width100'".
                               " cellspacing='1'".
                               " align='center'".
                               " style='padding-left: 20px;'".
                               " bgcolor='#C8C8E8'><tr>";
            $output .= "<td class='form-title' colspan='".
                       $number_of_columns.
                       "'>Filter #".$filter.
                       ": ".
                       $f_initosreminder_label_filter.
                       " <b>".
                       lang_get('overview_filter_is_inactive_today').
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
                $output .= "<table class='width100'".
                           " cellspacing='1'".
                           " align='center'".
                           " style='margin-top: 00px;'>";
                $output .= "<tr><td class='form-title'>".
                           lang_get('overview_query').
                           $filter.":<br> ".$bug_query."</td></tr>";
                $output .= "</table>";
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
                $t_rows = filter_get_bug_rows(
                    $f_page_number, $t_per_page, $t_page_count, 
                    $t_bug_count, $t_deserialize_filter_string, 
                    helper_get_current_project()
                );
                foreach ($t_rows as $filter_row_bug) {
                    $bug_results = db_query_bound($bug_query);
                    $f_continue_flag = 1;
                    while ($row_bugs = db_fetch_array($bug_results)) {
                        if ($row_bugs[id] == $filter_row_bug->id) {
                            $f_row_bug = $row_bugs;
                            $f_continue_flag = 0;
                        }   
                    }
                    if($f_continue_flag) continue;
                    
                    // find out names for this case; use general approach
                    // not dependent whether mailto is assignee or reporter
                    $reporter_id = $f_row_bug['reporter_id'];    
                    $side_query
                        = "select username,realname".
                          " from $t_user_table where id = $reporter_id";
                    $side_results = db_query_bound($side_query);
                    while ($sub_name = db_fetch_array($side_results)) {
                        $repoerter_u = $sub_name['username'];
                        $reporter_name = $sub_name['realname'];
                    }

                    if ($f_row_bug['handler_id'] != '0') {
                        $handler_id = $f_row_bug['handler_id'];    
                        $side_query = "select username,realname".
                                      " from $t_user_table where id = $handler_id";
                        $side_results = db_query_bound($side_query);
                        while ($sub_name = db_fetch_array($side_results)) {
                            $handler_u = $sub_name['username'];
                            $handler_name = $sub_name['realname'];
                        }
                    } else {
                        $handler_u = "";
                        $handler_name = "";
                    }

                    $timeleft = ($f_row_bug['due_date'] - time(true));
                    $minus = "";
                    $minusclass = "";
                    if ($timeleft < 0) {
                        $timeleft = - $timeleft;
                        $minus = "- ";
                        $minusclass = " class='overdue'";
                    }
                    $f_output .= "<tr ".helper_alternate_class().">";
                    $f_output .= "<td>".$f_row_bug[id]."</td>";
                    $f_output 
                        .= "<td>".$projects_array[$f_row_bug[project_id]]."</td>";
                    $f_output .= "<td>".$f_row_bug[summary]."</td>";
                    $f_output .= "<td>".
                                 date(
                                     config_get('short_date_format'),
                                     $f_row_bug['due_date']
                                 ).
                                "</td>";
                    $f_output .= "<td".
                                 $minusclass.
                                 ">".
                                 $minus.date('d', $timeleft).
                                 "d ".
                                 date('H:i:s', $timeleft).
                                 "h</td>";
                    $f_output .= "<td>".
                                 get_enum_element('status', $f_row_bug[status]).
                                 "</td>";    
                    $f_output .= "<td>".
                                 get_enum_element('severity', $f_row_bug[severity]).
                                 "</td>";
                    $f_output .= "<td>".
                                 get_enum_element('priority', $f_row_bug[priority]).
                                 "</td>";
                    if ($f_row_bug['handler_id'] != '0') {
                        $f_output .= "<td>".$handler_u.", ".$handler_name."</td>" ;
                    } else {
                        $f_output  .= "<td class='overdue'>".
                                      lang_get('overview_not_assigned').
                                      "</td>" ;
                    }

                    $f_output  .= "<td>".$repoerter_u.", ".$reporter_name."</td>";
                    $f_output  .= "</tr>";                       
                }                    
            } else {
                $f_output = "";
                while ($row_bugs = db_fetch_array($bug_results)) {
                    // find out names for this case; use general approach
                    //not dependent whether mailto is assignee or reporter
                    $reporter_id = $row_bugs['reporter_id'];    
                    $side_query = "select username,realname".
                                  " from $t_user_table where id = $reporter_id";
                    $side_results = db_query_bound($side_query);
                    while ($sub_name = db_fetch_array($side_results)) {
                        $repoerter_u = $sub_name['username'];
                        $reporter_name = $sub_name['realname'];
                    }

                    if ($row_bugs['handler_id'] != '0') {
                        $handler_id = $row_bugs['handler_id'];    
                        $side_query = "select username,realname".
                                      " from $t_user_table where id = $handler_id";
                        $side_results = db_query_bound($side_query);
                        while ($sub_name = db_fetch_array($side_results)) {
                            $handler_u = $sub_name['username'];
                            $handler_name = $sub_name['realname'];
                        }
                    } else {
                        $handler_u = "";
                        $handler_name = "";
                    }

                    $timeleft = ($row_bugs['due_date'] - time(true));
                    $minus = "";
                    $minusclass = "";
                    if ($timeleft < 0) {
                        $timeleft = - $timeleft;
                        $minus = "- ";
                        $minusclass = " class='overdue'";
                    }
                    $f_output .= "<tr ".helper_alternate_class().">";
                    $f_output .= "<td>".$row_bugs[id]."</td>";
                    $f_output .= "<td>".
                                 $projects_array[$row_bugs[project_id]].
                                 "</td>";
                    $f_output .= "<td>".$row_bugs[summary]."</td>";
                    $f_output .= "<td>".
                                 date(config_get('short_date_format'), $row_bugs['due_date']).
                                 "</td>";
                    $f_output .= "<td".$minusclass.">".
                                 $minus.date('d', $timeleft).
                                 "d ".
                                 date('H:i:s', $timeleft).
                                 "h</td>";
                    $f_output .= "<td>".
                                 get_enum_element('status', $row_bugs[status]).
                                 "</td>";    
                    $f_output .= "<td>".
                                 get_enum_element('severity', $row_bugs[severity]).
                                 "</td>";
                    $f_output .= "<td>".
                                 get_enum_element('priority', $row_bugs[priority]).
                                 "</td>";

                    if ($row_bugs['handler_id'] != '0') {
                        $f_output .= "<td>".$handler_u.", ".$handler_name."</td>" ;
                    } else {
                        $f_output .= "<td class='overdue'>".
                                     lang_get('initosreminder_not_assigned').
                                     "</td>" ;
                    }

                    $f_output  .= "<td>".$repoerter_u.", ".$reporter_name."</td>";
                    $f_output  .= "</tr>";
                }
            }
            if (!empty($f_output)) {
                $output .= "<table class='width100'".
                           " cellspacing='1'".
                           " align='center'".
                           " style='padding-left: 20px;'".
                           " bgcolor='#C8C8E8'>".
                           "<tr>";
                $output .= "<td class='form-title' colspan='".
                           $number_of_columns."'>".
                           lang_get('overview_filter').
                           $filter." :".
                           $f_initosreminder_label_filter."</td></tr><tr>";
                $output .= "<td class='category'>".
                           lang_get('overview_issue_id')."</td>" ;
                $output .= "<td class='category'>".
                           lang_get('overview_project_name')."</td>" ;
                $output .= "<td class='category'>".
                           lang_get('overview_summary')."</td>" ;
                $output .= "<td class='category'>".
                           lang_get('overview_due_date')."</td>";
                $output .= "<td class='category'>".
                           lang_get('overview_time_left')."</td>";
                $output .= "<td class='category'>".
                           lang_get('overview_status')."</td>";
                $output .= "<td class='category'>".
                           lang_get('overview_severity')."</td>";
                $output .= "<td class='category'>".
                           lang_get('overview_priority')."</td>";
                $output .= "<td class='category'>".
                           lang_get('overview_asigned')."</td>" ;
                $output .= "<td class='category'>".
                           lang_get('overview_reported')."</td>" ;
                $output .= "</tr>";
                $output .= $f_output;
                $output .= "</table>";
            }
            
        }    
    }
    
    //check if there is at least one result for the user
    if (empty($output)) {
        continue;
    }
    // header for every user ... the filters will be run / shown inside of here
    $output_h = "<table class='width100'".
                " cellspacing='1'".
                " align='center'".
                " style='margin-top: 20px;'>";
    $output_h .= "<tr><td class='form-title'>Reminder List of Bugs for: ".
                 $row_users[username].
                 ", (".$row_users[email].")</td></tr>";
    $output_h .= "</table>";
    echo $output_h.$output;
}

html_page_bottom();
