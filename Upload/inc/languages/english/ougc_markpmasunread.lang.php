<?php

/***************************************************************************
 *
 *   OUGC Mark PM As Unread plugin (/inc/languages/english/ougc_markpmasunread.php)
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

$l['ougc_markpmasunread'] = 'OUGC Mark PM As Unread';
$l['ougc_markpmasunread_d'] = 'Allow users to mark private messages as unread.';
$l['ougc_markpmasunread_var'] = 'Mark Unread';

$l['ougc_markpmasunread_error_invalidpm'] = 'The private message doesn\'t exists.';
$l['ougc_markpmasunread_error_alreadyunread'] = 'The private message is already marked as unread.';
$l['ougc_markpmasunread_error_unkown'] = 'A unkown error was found.';
$l['ougc_markpmasunread_error_userupdate'] = 'It was not possible to update your PM Count.';

// PluginLibrary
$l['ougc_markpmasunread_plreq'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_markpmasunread_plold'] = 'This plugin requires PluginLibrary version {2} or later, whereas your current version is {1}. Please do update <a href="{3}">PluginLibrary</a>.';

// Settings
$l['ougc_markpmasunread_s_groups'] = 'Allowed Groups';
$l['ougc_markpmasunread_s_groups_d'] = 'Comma separated list of groups allowed to use this feature. Leave empty for all.';