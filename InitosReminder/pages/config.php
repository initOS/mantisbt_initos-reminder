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


auth_reauthenticate();
html_page_top(plugin_lang_get('overview'));
print_manage_menu();
$f_initosreminder_filters = plugin_config_get('initosreminder_filters');
$f_initosreminder_filters_count 
    = plugin_config_get('initosreminder_filters_count');

/**
 * Print parameter ' selected="selected" ' if
 * the option is selected
 * 
 * @param unknown_type $p_var Selected option
 * @param unknown_type $p_val Option value
 * 
 * @return void
 */
function reminder_check_selected( $p_var, $p_val = true ) 
{
    if ( is_array($p_var) ) {
        foreach ( $p_var as $t_this_var ) {

            // catch the case where one entry is 0 and the other is a string.
            if ( is_string($t_this_var) && is_string($p_val) ) {
                if ( $t_this_var === $p_val ) {
                    echo ' selected="selected" ';
                    return;
                }
            } else if ( is_array($p_val) ) {
                foreach ( $p_val as $t_this_val ) {
                    if ( $t_this_var == $t_this_val ) {
                        echo ' selected="selected" ';
                        return;
                    }
                }
            } else if ( $t_this_var == $p_val ) {
                echo ' selected="selected" ';
                return;
            }
        }
    } else {
        if ( is_string($p_var) && is_string($p_val) ) {
            if ( $p_var === $p_val ) {
                echo ' selected="selected" ';
                return;
            }
        } else if ( $p_var == $p_val ) {
            echo ' selected="selected" ';
            return;
        }
    }
}

/**
 * Print available filters for the current project and user
 * 
 * @param string $p_name Name of the config parameter
 * 
 * @return void
 */
function my_print_query_option_list( $p_name )
{
    // get available queries for the current project and user
    $t_queries = filter_db_get_available_queries();
    $t_selection = plugin_config_get($p_name);
    echo '<select name="' . $p_name . '" size="' . count($t_queries) . '">';
    foreach ( $t_queries as $t_key => $t_value ) {
        echo '<option value="' . $t_key . '"';
        reminder_check_selected($t_selection, $t_key);
        echo '>' . $t_value . '</option>';
    }
    echo '</select>';
}

/**
 * Function used for usort
 * 
 * @param object $item1 First item to compare
 * @param object $item2 Second item to compare
 * 
 * @return number
 */
function user_asc_sort($item1, $item2)
{
    if ($item1['realname'] == $item2['realname']) return 0;
    return ($item1['realname'] < $item2['realname']) ? -1 : 1;
}

/**
 * Print users list
 * 
 * @param string $p_name Name of the config parameter
 * 
 * @return void
 */
function my_print_users_option_list( $p_name ) 
{
    $t_current_project_id = helper_get_current_project();
    
    // $p_project_id = ALL_PROJECTS, if project_id is skipped
    $t_enum_values = project_get_all_user_rows($t_current_project_id); 
    usort($t_enum_values, 'user_asc_sort');                               
    $t_selection = plugin_config_get($p_name);
    echo '<select name="' .
          $p_name . '[]" multiple="multiple" size="' .
          count($t_enum_values) . '">';
    foreach ( $t_enum_values as $t_value ) {
        echo '<option value="' . $t_value['id'] . '"';
        reminder_check_selected($t_selection, $t_value['id']);
        echo '>' . $t_value['realname'] . '</option>';    
    }
    echo '</select>';
}

?>
    
<form action="<?php echo plugin_page('filter_update') ?>" method="post">
<?php echo form_security_field('plugin_initosreminder_config_update') ?>

<table class='width100' cellspacing='1' align='center'>
    <tr>
        <td colspan="4" class="form-title">
            <?php echo lang_get('config_form_header'); ?>
        </td>        
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" ><?php echo lang_get('config_mail_subject'); ?></td>
        <td width="60%">
            <input type="text" 
                name="initosreminder_subject"
                size="100" maxlength="100" 
                value="<?php echo plugin_config_get('initosreminder_subject')?>" >
        </td>
        <td width="100px"></td>
        <td width="100px"></td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" ><?php echo lang_get('config_mail_body'); ?></td>
        <td width="60%">
            <textarea NAME="initosreminder_body" rows=4 cols=75 ><?php echo plugin_config_get('initosreminder_body')?>
            </textarea>
        </td>
        <td width="100px"></td>
        <td width="100px"></td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" ><?php echo lang_get('config_sender'); ?></td>
        <td width="60%">
            <input type="text"
                   name="initosreminder_sender"
                   size="100" maxlength="100"
                   value="<?php echo plugin_config_get('initosreminder_sender')?>" >
        </td>
        <td width="100px"></td>
        <td width="100px"></td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" ><?php echo lang_get('config_run_script'); ?></td>
        <td width="60%">
            <input type="text" 
                   name="initosreminder_login"
                   size="100" maxlength="100"
                   value="<?php echo plugin_config_get('initosreminder_login')?>" >
        </td>
        <td width="100px"></td>
        <td width="100px"></td>
    </tr>
<?php
foreach($f_initosreminder_filters as $filter):
    //prepare filter names
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
?>
    <!-- ========================= FILTERS BEGIN ========================= -->
    <tr>
        <td colspan="3" class="form-title">
            <?php echo lang_get('config_filter').$filter; ?>
        </td>        
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category"><?php echo lang_get('config_is_filter_active'); ?></td>
        <td></td>
        <td class="center">
            <label>
                <input type="radio" 
                       name="<?php echo $f_active_name; ?>" 
                       value="0" 
                       <?php echo ( OFF == plugin_config_get($f_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_disabled') ?>
            </label>
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio" 
                       name="<?php echo $f_active_name; ?>" 
                       value="1"
                       <?php echo ( ON == plugin_config_get($f_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_enabled') ?>
            </label>
        </td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_active_on'); ?>
        </td>
        <td class="left">
            <?php $t_dayselection = plugin_config_get($f_active_on_name);?>
            <select name="<?php echo $f_active_on_name.'[]';?>"
                    multiple="multiple" 
                    size="7">
                <option 
                    <?php reminder_check_selected($t_dayselection, 'Monday');?>
                    value="Monday">
                        <?php echo lang_get('config_monday'); ?>
                </option>
                <option 
                    <?php reminder_check_selected($t_dayselection, 'Tuesday');?>
                    value="Tuesday">
                        <?php echo lang_get('config_tuesday'); ?>
                </option>
                <option 
                    <?php reminder_check_selected($t_dayselection, 'Wednesday');?>
                    value="Wednesday">
                        <?php echo lang_get('config_wednesday'); ?>
                </option>
                <option 
                    <?php reminder_check_selected($t_dayselection, 'Thursday');?>
                    value="Thursday">
                        <?php echo lang_get('config_thursday'); ?>
                </option>
                <option
                    <?php reminder_check_selected($t_dayselection, 'Friday');?>
                    value="Friday">
                        <?php echo lang_get('config_friday'); ?>
                </option>
                <option
                    <?php reminder_check_selected($t_dayselection, 'Saturday');?>
                    value="Saturday">
                        <?php echo lang_get('config_saturday'); ?>
                </option>
                <option
                    <?php reminder_check_selected($t_dayselection, 'Sunday');?>
                    value="Sunday">
                        <?php echo lang_get('config_sunday'); ?>
                </option>
            </select>
        </td>
        <td width="100px"></td>
        <td width="100px"></td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_filter_label'); ?>
        </td>
        <td width="60%">
            <input type="text"
                   name="<?php echo $f_label_name; ?>"
                   size="100"
                   maxlength="100"
                   value="<?php echo plugin_config_get($f_label_name)?>" />
        </td>
        <td width="100px"></td>
        <td width="100px"></td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_inactive_days'); ?>
        </td>
        <td class="left">
            <input type="text"
                   name="<?php echo $f_inactive_since_name; ?>"
                   size="3"
                   maxlength="3"
                   value="<?php echo plugin_config_get($f_inactive_since_name)?>" />
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_inactive_since_act_name; ?>"
                       value="0" 
                       <?php echo ( OFF == plugin_config_get($f_inactive_since_act_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_disabled') ?>
            </label>
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_inactive_since_act_name; ?>"
                       value="1"
                       <?php echo ( ON == plugin_config_get($f_inactive_since_act_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_enabled') ?>
            </label>
        </td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_within_x_days'); ?>
        </td>
        <td class="left">
            <input type="text"
                   name="<?php echo $f_due_within_name; ?>"
                   size="3"
                   maxlength="3"
                   value="<?php echo plugin_config_get($f_due_within_name)?>" >
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_due_within_active_name; ?>"
                       value="0" 
                       <?php echo ( OFF == plugin_config_get($f_due_within_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_disabled') ?>
            </label>
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_due_within_active_name; ?>"
                       value="1"
                       <?php echo ( ON == plugin_config_get($f_due_within_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_enabled') ?>
            </label>
        </td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_dates_in_past'); ?>
        </td>
        <td class="right">
            
        </td>
        <td class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_due_in_past_name; ?>"
                       value="0"
                       <?php echo ( OFF == plugin_config_get($f_due_in_past_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_disabled') ?>
            </label>
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_due_in_past_name; ?>"
                       value="1"
                       <?php echo ( ON == plugin_config_get($f_due_in_past_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_enabled') ?>
            </label>
        </td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_use_filter'); ?>
        </td>
        <td class="left">
            <?php my_print_query_option_list($f_use_filter_name) ?>    
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_use_filter_active_name; ?>"
                       value="0"
                       <?php echo ( OFF == plugin_config_get($f_use_filter_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_disabled') ?>
            </label>
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_use_filter_active_name; ?>"
                       value="1"
                       <?php echo ( ON == plugin_config_get($f_use_filter_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_enabled') ?>
            </label>
        </td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td class="category" >
            <?php echo lang_get('config_user_list'); ?>
        </td>
        <td class="left">
            <?php my_print_users_option_list($f_users_name) ?>    
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_users_active_name; ?>"
                       value="0"
                       <?php echo ( OFF == plugin_config_get($f_users_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_disabled') ?>
            </label>
        </td>
        <td width="100px" class="center">
            <label>
                <input type="radio"
                       name="<?php echo $f_users_active_name; ?>"
                       value="1"
                       <?php echo ( ON == plugin_config_get($f_users_active_name) ) ? 'checked="checked" ' : ''?>/>
                <?php echo lang_get('reminder_store_enabled') ?>
            </label>
        </td>
    </tr>
    <tr <?php echo helper_alternate_class() ?>>
        <td colspan="4" style="text-align: center;">
            <a href=<?php echo plugin_page('filter_remove')."&filter=".$filter?> >
                <?php echo lang_get('config_remove_filter'); ?>
            </a>
        </td>
    </tr>
<?php 
endforeach;
?>
        <!-- ======================== FILTERS end ======================== -->
    <tr <?php echo helper_alternate_class() ?>>
        <td colspan="4" style="text-align: center;">
            <?php if ($f_initosreminder_filters_count == count($f_initosreminder_filters)): ?> 
                <p><?php echo lang_get('config_max_filter');?></p> 
            <?php else:?>
                <a href = <?php echo plugin_page('filter_add')?>>
                    <?php echo lang_get('config_add_filter');?>
                </a>
            <?php endif;?>
        </td>
    </tr>
    <tr>
        <td colspan="2"><input type="submit"/></td>    
    </tr>
</table>
</form>
<?php
html_page_bottom();
?>
