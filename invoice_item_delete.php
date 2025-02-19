<?php
/* $Id$ */
/*
	Copyright (C) 2008-2025 Mark J Crane
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";

//get permissions
	if (permission_exists('invoice_item_delete')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get the http variables
	if (count($_GET) > 0) {
		$id = $_GET["id"];
		$invoice_uuid = $_GET["invoice_uuid"];
		$contact_uuid = $_GET["contact_uuid"];
		$back = $_GET["back"];
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//connect to the database
	$database = database::new();

//delete invoice_item
	if (!empty($id) && is_uuid($id)) {
		$sql = "delete from v_invoice_items ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$sql .= "and invoice_item_uuid = :invoice_item_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['invoice_item_uuid'] = $id;
		$database->execute($sql, $parameters);
	}

//redirect the user
	$_SESSION['message'] = $text['message-delete'];
	$back = ($back != '') ? "&back=".$back : null;
	header("Location: invoice_edit.php?id=".$invoice_uuid."&contact_uuid=".$contact_uuid.$back);

?>
