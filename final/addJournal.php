<?php

require './config.php';

search_and_redirect(__FILE__);

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style1.css">
	<title>Accounting - Journal Entry</title>
</head>
<body>

<div class="topnav">
  <a href="accHome.php">Home</a>
  <a class="active"  href="#">Add Journal</a>
  <a href="viewJournal.php">View Journal</a>
  <a style="float: right;" href="do.Logout.php">Logout</a>
</div>


<div class="centerDiv">

	<p><h2>Add New Journal</h2></p>
	<form method="POST" action="do.addJournal.php" onsubmit="return validate()" enctype="multipart/form-data">

		<table>
			<tr>
				<th width="200px">Dates</th>
				<th width="400px">Accounts</th>
				<th>Debit</th>
				<th>Credit</th>
			</tr>
			<tr>
				<td>
					<?php $strDate = date("Y-m-d"); ?>
					<input type="date" name="date" min="<?php echo $strDate ?>" value="<?php echo $strDate ?>">
				</td>

				<td>
					<div id="div1"></div>
				</td>

				<td>
					<div id="div2"></div>
				</td>

				<td>
					<div id="div3"></div>
				</td>
			</tr>

			<tr>
				<td colspan="4" width="200px">Descriptions: <input style="width: 800px;" type="text" name="desc"></th>
				
			</tr>

			<tr>
				<td></td>
				<td><input type="file" id="fileIn" name="documents[]" multiple="multiple" accept=".pdf,.xls,.doc,.csv" value="Upload files..." ><input type="button" name="removeFile" value="Remove Files"  onclick="remove()"></td>
				<td><input type="button" name="add field" value="Add New Field" onclick="add_fields()"></td>
				<td><input style="margin-right: 10px;" type="submit" value="Submit" id="submit" disabled><input type="reset" value="Clear" name=""></td>
			</tr>
		</table>
	</form>

</div>


</body>

<script type="text/javascript" src="accounts.js"></script>
<script>

	function get_accounts ()
	{
		var s = '';
		var t = '<option value="{{value}}">{{name}}</option>';
		for (var i = 0; i < accounts.length; i++){
			var index = i + 1;
			var q = t.replace("{{value}}", index.toString()).replace("{{name}}", accounts[i]);
			s = s + q;
		}
		return s;
	}

	var field_count = 0;

	var accounts_html = get_accounts();
	var submit_btn = document.getElementById('submit');
	var table_field_1 = document.getElementById('div1');
	var table_field_2 = document.getElementById('div2');
	var table_field_3 = document.getElementById('div3');

	function add_field(count) {
		var s = '';

		var form_options = 'type="number" step="0.001" min="0" onchange="validate()"';

		var f1 = '<select id="room" name="acc{{count}}">{{options}}</select>';
		var f2 = '<input {{options}} name="debit{{count}}" id="debit{{count}}" class="input-debit"><br>';
		var f3 = '<input {{options}} name="credit{{count}}" id="credit{{count}}" class="input-credit"><br>';

		var c = count.toString();

		f1 = f1.replace("{{count}}", c);
		f1 = f1.replace("{{options}}", accounts_html);

		f2 = f2.replace("{{options}}", form_options).replace("{{count}}", c).replace("{{count}}", c);

		f3 = f3.replace("{{options}}", form_options).replace("{{count}}", c).replace("{{count}}", c);

		var ndiv1 = document.createElement("div");
		var ndiv2 = document.createElement("div");
		var ndiv3 = document.createElement("div");

		ndiv1.innerHTML = f1;
		ndiv2.innerHTML = f2;
		ndiv3.innerHTML = f3;

		table_field_1.appendChild(ndiv1);
		table_field_2.appendChild(ndiv2);
		table_field_3.appendChild(ndiv3);
	}

	function add_fields()
	{
		if( field_count < 5)
		{
			add_field(field_count++);
		}
	}

	function remove()
	{
		document.getElementById('fileIn').value=""
	}

	function validate()
	{
		var total_debit = 0;
		var total_credit = 0;

		var submit_ok = true;

		for(var i = 0; i < field_count; i++) {
			var debitId = "debit" + i.toString();
			var creditId = "credit" + i.toString();
			var debit = document.getElementById(debitId);
			var credit = document.getElementById(creditId);

			console.log(debit, credit);

			// both fields are empty for a record
			if (debit.value == "" && credit.value == "") {
				submit_ok = false;
				break;
			}

			// both fields are set for a record
			if ( (debit.value != "") && (credit.value != "") ) {
				console.log("both values set");
				submit_ok = false;
				break;
			}

			if (debit.value != "") {
				// debit.stepUp();
				// debit.stepDown();
				total_debit = total_debit + parseFloat(debit.value);
			}

			if (credit.value != "") {
				// credit.stepUp();
				// credit.stepDown();
				total_credit = total_credit + parseFloat(credit.value);
			}
		}

		if ( total_credit != total_debit ) {
			submit_ok = false;
		}

		submit_btn.disabled = !submit_ok;

		return submit_ok;
	} // validate

	(function() {
		add_field(field_count++);
		submit_btn.disabled = true;
	})();

</script>
</html>
