<?php
/**
 * 'Tools' is a small admin-tool-module to provide some autotasks for icms and some more..
 *
 * File: /admin/tools.php
 *
 * admin tool triggering
 *
 * @copyright	Copyright QM-B (Steffen Flohrer) 2013
 * @license		http://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License (GPL)
 * ----------------------------------------------------------------------------------------------------------
 * 				Tools
 * @since		1.00
 * @author		QM-B <qm-b@hotmail.de>
 * @version		$Id$
 * @package		tools
 * @version		$Revision$
 *
 */

include_once 'admin_header.php';
icms_cp_header();
icms::$module->displayAdminMenu( 0, _MI_TOOLS_MENU_TOOLS);
icms::$module->setVar("ipf", TRUE);
icms::$module->registerClassPath(TRUE);
$clean_op = isset($_GET['op']) ? filter_input(INPUT_GET, "op", FILTER_SANITIZE_STRING) : "";
$clean_op = isset($_POST['op']) ? filter_input(INPUT_POST, "op", FILTER_SANITIZE_STRING) : $clean_op;

$valid_op = array("trigger_all", "trigger_cache", "trigger_sessions", "trigger_optimize", "trigger_templates", "delete_log", "confirm_delete", "confirm_delete", "");

if(in_array($clean_op, $valid_op, TRUE)) {
	$icmsAdminTpl->assign("tools_title", _MI_TOOLS_MENU_TOOLS);
	$icmsAdminTpl->assign("tools_url", TOOLS_URL);
	$icmsAdminTpl->assign("tools_admin_url", TOOLS_ADMIN_URL);
	$icmsAdminTpl->assign("tools_tool", TRUE);
	$logPath = TOOLS_TRUST_PATH.'logs/log_tool.php';
	switch ($clean_op) {
		case 'trigger_all':
			mod_tools_Tools::instance();
			mod_tools_Tools::runTools();
			redirect_header(TOOLS_ADMIN_URL.'tools.php', 3);
			break;
		case 'trigger_cache':
			mod_tools_Tools::instance();
			mod_tools_Tools::clearCache();
			redirect_header(TOOLS_ADMIN_URL.'tools.php', 3);
			break;
		case 'trigger_sessions':
			mod_tools_Tools::instance();
			mod_tools_Tools::clearSessions();
			redirect_header(TOOLS_ADMIN_URL.'tools.php', 3);
			break;
		case 'trigger_templates':
			mod_tools_Tools::instance();
			mod_tools_Tools::clearTemplates();
			redirect_header(TOOLS_ADMIN_URL.'tools.php', 3);
			break;
		case 'trigger_optimize':
			mod_tools_Tools::instance();
			mod_tools_Tools::maintainDB();
			redirect_header(TOOLS_ADMIN_URL.'tools.php', 3);
			break;
		case 'delete_log':
		case 'confirm_delete':
			if (isset($_POST['confirm_delete'])) {
				if (!icms::$security->check()) {
					redirect_header(icms_getPreviousPage(), 3, _AM_TOOLS_SECURITY_CHECK_FAILED . implode('<br />', icms::$security->getErrors()));
				} else {
					if(icms_core_Filesystem::deleteFile($logPath)) {
						$icmsAdminTpl->assign('tools_delete_ok', TRUE);
					} else {
						$icmsAdminTpl->assign('tools_delete_failed', TRUE);
					}
				}
			} else {
				icms_core_Message::confirm(array("confirm_delete" => TRUE), $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], _AM_TOOLS_BACKUP_DELETE_CONFIRM, _YES);
			}
			$icmsAdminTpl->display("db:tools_admin.html");
			break;
		default:
			if($clean_op == "delete_ok") $icmsAdminTpl->assign('tools_delete_ok', TRUE);
			if($clean_op == "delete_failed") $icmsAdminTpl->assign('tools_delete_failed', TRUE);
			$logFile = TOOLS_TRUST_PATH.'logs/log_tool.php';
			if(is_file($logFile)) {
				$filectime = $toolsConfig['last_mc'];
				$warning = ($filectime <= (time()-(60*60*24*7))) ? TRUE : FALSE;
				$created = date("M d, Y ".strtolower("\a\T")." H:i:s \G\M\T P", $filectime);
				$icmsAdminTpl->assign('tools_warning', $warning);
				$icmsAdminTpl->assign('tools_tool_ok', TRUE);
				$icmsAdminTpl->assign("tools_lastChanged", sprintf(_AM_TOOLS_LAST_MAINTENANCE, $created));
			} else {
				$icmsAdminTpl->assign('tools_tool_warning', TRUE);
			}
			$icmsAdminTpl->display("db:tools_admin.html");
			break;
	}
}

include_once 'admin_footer.php';