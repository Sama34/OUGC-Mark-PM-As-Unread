<?php

/***************************************************************************
 *
 *   OUGC Mark PM As Unread plugin (/inc/plugins/ougc_markpmasunread.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Allow users to mark private messages as unread.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Tell MyBB when to run the hook
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', 'ougc_markpmasunread_loadlang');
	$plugins->add_hook('admin_style_templates_set', 'ougc_markpmasunread_loadlang');;
	$plugins->add_hook('admin_config_settings_change', 'ougc_markpmasunread_settings_change');
}
elseif(THIS_SCRIPT == 'private.php')
{
	$plugins->add_hook('private_start', 'ougc_markpmasunread_do_mark');
	$plugins->add_hook('private_end', 'ougc_markpmasunread_private');

	global $templatelist;

	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'ougcmarkpmasunread';
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

function ougc_markpmasunread_info()
{
	global $lang;
	ougc_markpmasunread_loadlang();

	return array(
		'name'					=> 'OUGC Mark PM As Unread',
		'description'			=> $lang->setting_group_ougc_markpmasunread,
		'website'				=> 'http://omarg.me',
		'author'				=> 'Omar G.',
		'authorsite'			=> 'http://omarg.me',
		'version'				=> '1.8',
		'versioncode'			=> 1800,
		'compatibility'			=> '18*',
		'pluginlibraryversion'	=> 12,
		'pluginlibraryurl'		=> 'http://mods.mybb.com/view/pluginlibrary'
	);
}

// Activate our plugin
function ougc_markpmasunread_activate()
{
	global $db, $lang, $PL, $cache;
	ougc_markpmasunread_plreq();
	ougc_markpmasunread_deactivate();
	ougc_markpmasunread_loadlang();
	$PL or require_once PLUGINLIBRARY;

	$PL->settings('ougc_markpmasunread', $lang->setting_group_ougc_markpmasunread, $lang->setting_group_ougc_markpmasunread_desc, array(
		'groups'			=> array(
		   'title'			=> $lang->setting_ougc_markpmasunread_groups,
		   'description'	=> $lang->setting_ougc_markpmasunread_groups_desc,
		   'optionscode'	=> 'groupselect',
		   'value'			=> -1
		),
	));

	$db->delete_query('templates', 'title=\'ougc_markpmasunread\' AND sid=\'-1\'');

	$PL->templates('ougcmarkpmasunread', '<lang:setting_group_ougc_markpmasunread>', array(
		''	=> '<span class="smalltext">[<a href="{$mybb->settings[\'bburl\']}/private.php?markunread={$pmid}&amp;my_post_code={$mybb->post_code}">{$lang->ougc_markpmasunread_var}</a>]</span>',
	));

	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('private_messagebit', '#'.preg_quote('{$denyreceipt}').'#i', '{$denyreceipt}<!--MARKUNREAD[{$message[\'pmid\']}]-->');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_markpmasunread_info();

	if(!isset($plugins['markpmasunread']))
	{
		$plugins['markpmasunread'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['markpmasunread'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// Deactivate our plugin
function ougc_markpmasunread_deactivate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('private_messagebit', '#'.preg_quote('<!--MARKUNREAD[{$message[\'pmid\']}]-->').'#i', '', 0);
}

// uninstall
function ougc_markpmasunread_install()
{
}

// _is_installed
function ougc_markpmasunread_is_installed()
{
	global $settings;

	return isset($settings['ougc_markpmasunread_groups']);
}

// _install
function ougc_markpmasunread_uninstall()
{
	global $PL, $cache;

	// Delete settings
	$PL->settings_delete('ougc_markpmasunread');

	// Delete templates
	$PL->templates_delete('ougcmarkpmasunread');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['markpmasunread']))
	{
		unset($plugins['markpmasunread']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// Language support for settings
function ougc_markpmasunread_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');
	$groupname = $db->fetch_field($query, 'name');
	if($groupname == 'ougc_markpmasunread')
	{
		ougc_markpmasunread_loadlang();
	}
}
// Mark a PM as unread
function ougc_markpmasunread_do_mark()
{
	global $mybb;

	if(isset($mybb->input['markunread']))
	{
		if(!($mybb->settings['ougc_markpmasunread_groups'] == -1 || is_member($mybb->settings['ougc_markpmasunread_groups'])))
		{
			error_no_permission();
		}

		verify_post_check($mybb->input['my_post_code']);

		if($mark = ougc_markpmasunread_markunread($mybb->input['markunread'], $mybb->user['uid']))
		{
			global $lang;
			ougc_markpmasunread_loadlang();

			isset($lang->{$mark}) or $lang->{$mark} = $lang->ougc_markpmasunread_error_unkown;
			error($lang->{$mark});
		}

		$mybb->settings['redirects'] = $mybb->user['showredirect'] = 0;
		redirect('private.php');
	}
}

// Neat trick to avoid core edits or whatnot
function ougc_markpmasunread_private()
{
	global $settings;

	if(!($settings['ougc_markpmasunread_groups'] == -1 || is_member($settings['ougc_markpmasunread_groups'])))
	{
		return;
	}

	global $messagelist;

	preg_match_all('#\<\!--MARKUNREAD\[([0-9]+)\]--\>#i', $messagelist, $matches);

	$matches = array_unique(array_map('intval', $matches[1]));

	if(!$matches)
	{
		return;
	}

	global $db, $mybb, $lang, $templates;
	ougc_markpmasunread_loadlang();

	$a_search = $r_search = array();
	$query = $db->simple_select('privatemessages', 'pmid', "pmid IN (".implode(',', $matches).") AND status='1'");
	while($pmid = $db->fetch_field($query, 'pmid'))
	{
		$pmid = (int)$pmid;
		$a_search[$pmid] = "<!--MARKUNREAD[{$pmid}]-->";
		eval('$r_search['.$pmid.'] = "'.$templates->get('ougcmarkpmasunread').'";');
	}

	if($a_search && $r_search)
	{
		$messagelist = str_replace($a_search, $r_search, $messagelist);
	}

	#$messagelist = preg_replace('#\<\!--MARKUNREAD\[([0-9]+)\]--\>#i', '', $messagelist);
}

// Mark a PM as unread
function ougc_markpmasunread_markunread($pmid, $uid)
{
	if(($pmid = (int)$pmid) < 0)
	{
		return 'ougc_markpmasunread_error_invalidpm';
	}

	$uid = (int)$uid;

	global $db;

	$query = $db->simple_select('privatemessages', 'pmid, status', "pmid='{$pmid}' AND uid='{$uid}'");
	$pm = $db->fetch_array($query);
	if(!$pm['pmid'])
	{
		return 'ougc_markpmasunread_error_invalidpm';
	}

	if(!$pm['status'])
	{
		return 'ougc_markpmasunread_error_alreadyunread';
	}

	$db->update_query('privatemessages', array('status' => 0, 'receipt' => 1), "pmid='{$pmid}'");

	global $mybb;

	// Update the unread count - it has now changed.
	function_exists('update_pm_count') or require_once MYBB_ROOT.'inc/functions_user.php';
	$update = update_pm_count($uid, 6);

	if(!array_key_exists('unreadpms', (array)$update))
	{
		return 'ougc_markpmasunread_error_userupdate';
	}

	// Update PM notice value
	$db->update_query('users', array('pmnotice' => 2), "uid='{$uid}'");

	return false;
}

// Load language
function ougc_markpmasunread_loadlang()
{
	global $lang;

	isset($lang->setting_group_ougc_markpmasunread) or $lang->load('ougc_markpmasunread');
}

// PL requirement check
function ougc_markpmasunread_plreq()
{
	if(!file_exists(PLUGINLIBRARY))
	{
		global $lang;
		$info = ougc_markpmasunread_info();

		flash_message($lang->sprintf($lang->ougc_markpmasunread_plreq, $info['pluginlibraryurl'], $info['pluginlibraryversion']), 'error');
		admin_redirect('index.php?module=config-plugins');
	}
	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pluginlibraryversion'])
	{
		global $lang;
		$info = ougc_markpmasunread_info();

		flash_message($lang->sprintf($lang->ougc_markpmasunread_plold, $PL->version, $info['pluginlibraryversion'], $info['pluginlibraryurl']), 'error');
		admin_redirect('index.php?module=config-plugins');
	}
}