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
	if (permission_exists('invoice_delete')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//connect to the database
	$database = database::new();

//get the id
	if (count($_GET) > 0) {
		$id = $_GET["id"];
		$contact_uuid = $_GET["contact_uuid"];
		$back = $_GET["back"];
	}

//delete invoice
	if (!empty($id) && is_uuid($id)) {
		$sql = "delete from v_invoices ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$sql .= "and invoice_uuid = :invoice_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['invoice_uuid'] = $id;
		$database->execute($sql, $parameters);
	}

//redirect the user
	$_SESSION['message'] = $text['message-delete'];
	header("Location: ".(($back != '') ? $back : "invoices.php?id=".$contact_uuid));

?>
