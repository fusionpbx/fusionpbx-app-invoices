<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2025
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('invoice_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//additional includes
	require_once "resources/header.php";
	require_once "resources/paging.php";

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//connect to the database
	$database = database::new();

//get variables used to control the order
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//get the contact id
	$contact_uuid = $_REQUEST["id"];

//prepare to page the results
	$sql = "SELECT count(*) as num_rows FROM v_invoices ";
	$sql .= "LEFT OUTER JOIN v_contacts ";
	$sql .= "ON v_invoices.contact_uuid_to = v_contacts.contact_uuid ";
	$sql .= "where v_invoices.domain_uuid = '$domain_uuid' ";
	if (strlen($contact_uuid) > 0) {
		$sql .= "and v_invoices.contact_uuid_to = '$contact_uuid' ";
	}
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$row = $database->select($sql, $parameters, 'row');
	if (!empty($row['num_rows'])) {
		$num_rows = $row['num_rows'];
	}
	else {
		$num_rows = '0';
	}
	unset($sql, $parameters);

//prepare to page the results
	$rows_per_page = 150;
	$param = "";
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; }
	list($paging_controls, $rows_per_page, $var3) = paging($num_rows, $param, $rows_per_page);
	$offset = $rows_per_page * $page;

//get the list
	$sql = "SELECT * FROM v_invoices ";
	$sql .= "LEFT OUTER JOIN v_contacts ";
	$sql .= "ON v_invoices.contact_uuid_to = v_contacts.contact_uuid ";
	$sql .= "where v_invoices.domain_uuid = :domain_uuid ";
	if (strlen($contact_uuid) > 0) {
		$sql .= "and v_invoices.contact_uuid_to = :contact_uuid ";
		$parameters['contact_uuid'] = $contact_uuid;
	}
	if (strlen($order_by) == 0) {
		$sql .= "order by v_invoices.invoice_number desc ";
	}
	else {
		$sql .= "order by v_invoices.$order_by $order ";
	}
	$sql .= "limit $rows_per_page offset $offset ";
	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$invoices = $database->select($sql, $parameters ?? '', 'all');
	unset($sql, $parameters);

//set the row style
	$c = 0;
	$row_style["0"] = "row_style0";
	$row_style["1"] = "row_style1";

//show the content
	echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='50%' align='left' valign='top' nowrap='nowrap'><b>".$text['title-invoices']."</b><br><br></td>\n";
	echo "		<td width='50%' align=\"right\" valign='top'>\n";
	if ($contact_uuid != '') {
		echo "			<input type='button' class='btn' name='' alt='back' onclick=\"history.go(-1);\" value='Back'>\n";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "</table>\n";

//show the invoices
	echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
	echo "<tr>\n";
	echo "<th>&nbsp;</th>\n";
	echo th_order_by('invoice_number', $text['label-invoice_number'], $order_by, $order);
	echo th_order_by('invoice_type', $text['label-invoice_type'], $order_by, $order);
	echo th_order_by('contact_organization', $text['label-contact_to_organization'], $order_by, $order);
	echo th_order_by('contact_name_given', $text['label-contact_to_given_name'], $order_by, $order);
	echo th_order_by('contact_name_family', $text['label-contact_to_family_name'], $order_by, $order);
	echo th_order_by('invoice_date', $text['label-invoice_date'], $order_by, $order);
	echo "<td align='right' width='42'>\n";
	if (permission_exists('invoice_add')) {
		echo "	<a href='invoice_edit.php?contact_uuid=".$_GET['id']."' alt='".$text['button-add']."'>$v_link_label_add</a>\n";
	}
	else {
		echo "	&nbsp;\n";
	}
	echo "</td>\n";
	echo "<tr>\n";

	if (is_array($invoices)) {
		foreach($invoices as $row) {
			$back = ($contact_uuid != '') ? "&back=".urlencode("invoices.php?id=".$contact_uuid) : null;
			$tr_link = (permission_exists('invoice_edit')) ? "href='invoice_edit.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['invoice_uuid']).escape($back)."'" : null;
			echo "<tr ".$tr_link.">\n";
			echo "	<td align='center' valign='middle' class='".$row_style[$c]."' style='padding: 0px 0px 0px 5px;'>";
			if ($row['invoice_paid'] == 1) {
				echo "<img src='paid.png' style='width: 16px; height: 16px; border; none;'>";
			}
			else {
				echo "<img src='unpaid.png' style='width: 16px; height: 16px; border; none;'>";
			}
			echo "	</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'><a href='invoice_edit.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['invoice_uuid']).escape($back)."' alt='".$text['button-edit']."'>".escape($row['invoice_number'])."</a>&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".$text['label-invoice_type_'.escape($row['invoice_type'])]."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['contact_organization'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['contact_name_given'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['contact_name_family'])."&nbsp;</td>\n";
			echo "	<td valign='top' class='".$row_style[$c]."'>".escape($row['invoice_date'])."&nbsp;</td>\n";
			echo "	<td class='list_control_icons'>\n";
			if (permission_exists('invoice_edit')) {
				echo 	"<a href='invoice_edit.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['invoice_uuid']).escape($back)."' alt='".$text['button-edit']."'>$v_link_label_edit</a>";
			}
			if (permission_exists('invoice_delete')) {
				echo 	"<a href='invoice_delete.php?contact_uuid=".escape($row['contact_uuid'])."&id=".escape($row['invoice_uuid']).escape($back)."' alt='".$text['button-delete']."' onclick=\"return confirm('".$text['confirm-delete']."')\">$v_link_label_delete</a>";
			}
			echo 	"</td>\n";
			echo "</tr>\n";
			if ($c==0) { $c=1; } else { $c=0; }
		} //end foreach
		unset($invoices);
	} //end if results

	echo "<tr>\n";
	echo "<td colspan='10' align='left'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap='nowrap'>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap='nowrap'>$paging_controls</td>\n";
	echo "		<td width='33.3%' align='right'>\n";
	if (permission_exists('invoice_add')) {
	echo "			<a href='invoice_edit.php?contact_uuid=".$_GET['id']."' alt='".$text['button-add']."'>$v_link_label_add</a>\n";
	}
	else {
		echo "		&nbsp;\n";
	}
	echo "		</td>\n";
	echo "	</tr>\n";
 	echo "	</table>\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br /><br />";

//include the footer
	require_once "resources/footer.php";

?>
