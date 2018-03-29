<?php

session_start();

require_once __DIR__ . '/../src/template.php';
require_once __DIR__ . '/../src/user.php';
require_once __DIR__ . '/../src/journal.php';

Template::setPathDepth(1);
User::setPathDepth(1);

User::route(TRUE);

if (User::getUserType() != User::TYPE_USER)
    die("only user can add journal entries.");

$msg = "";
$err = "";
if (isset($_POST['submit'])) {

    $date = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['date']);
    if ($date === FALSE)
        die("invalid request");
    $date = $date->format('Y-m-d H:i:00');
    $desc = $_POST['journal_desc'];
    $entries = array();

    foreach ($_POST['account'] as $key => $value) {
        $entries[] = array( $value, $_POST['debit'][$key], $_POST['credit'][$key] );
    }

    $total_files = count($_FILES['documents']['name']);
    $files = array();
    for ($i = 0; $i < $total_files; $i++) {
        $tmpFilePath = $_FILES['documents']['tmp_name'][$i];

        // TODO: upload error handling
        if ($tmpFilePath != "") {
            $collisionAvoidance = bin2hex(random_bytes(4)) . "-";
            $newFilePath = __DIR__ . "/../files/" . $collisionAvoidance . $_FILES['documents']['name'][$i];

            $files[] =  $collisionAvoidance . $_FILES['documents']['name'][$i];

            if ( ! move_uploaded_file($tmpFilePath, $newFilePath)) {
                // TODO: fix copy handling
            }
        }
    }

    $ret = Journal::createJournalEntry(User::getUserID(), $date, $entries, $desc, $files);

    if ($ret == Journal::CREATE_OK) {
        $msg = "Entry has been added!";
    } else {
        $err = "Something went wrong. Please try again later.";
    }

}

?>
<!doctype html>
<html lang="en">
<head>
    <?= Template::header() ?>
    <link rel="stylesheet" href="../public/css/style.css">

    <title><?= Template::title("Add Journal Entry") ?></title>
</head>
<body>
<?=Template::navbar()?>

<div class="container body-container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-8">

            <form action="add.php" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">
                <div class="form-group text-center">
                    <h2>Add Journal Entry</h2>
                </div>

                <?php
                    $strDate = date('Y-m-d\TH:i');
                    $minDate = date('Y-m-d') . 'T00:00'; ?>
                <div class="form-group">
                    <label for="journal_date">Date/Time</label>
                    <input name="date" type="datetime-local" class="form-control" id="journal_date" min="<?= $minDate ?>"
                           value="<?= $strDate ?>" required>
                </div>
                <div class="form-group">
                    <label for="journal_desc" class="bmd-label-floating">Description</label>
                    <input name="journal_desc" type="text" class="form-control" id="journal_desc" value="">
                </div>

                <div class="form-group">
                    <label for="attachments">Attachments</label>
                    <input type="file" id="attachments" name="documents[]" class="form-control-file" multiple="multiple" accept=".pdf,.xls,.doc,.csv">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="accounts-table">
                        <thead>
                        <tr>
                            <th scope="col" class="text-center">Account</th>
                            <th scope="col" class="text-center">Debit</th>
                            <th scope="col" class="text-center">Credit</th>
                            <th scope="col" class="text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr id="accounts-table-ref-account">
                            <th scope="row" class="form-group">
                                <select name="account[]" class="custom-select">
                                    <?php foreach (Account::getAccounts(FALSE, Account::STATUS_ENABLED) as $acc) { ?>
                                        <option value="<?= $acc->id ?>"><?= $acc->id ?> - <?= $acc->name ?></option>
                                    <?php } // foreach $acc ?>
                                </select>
                            </th>
                            <td class="form-group">
                                <input name="debit[]" type="number" class="form-control input-debit" step="0.01" min="0">
                            </td>
                            <td class="form-group">
                                <input name="credit[]" type="number" class="form-control input-credit" step="0.01" min="0">
                            </td>
                            <td class="btn-group-sm">
                                <button type="button" class="btn btn-danger bmd-btn-fab nukem" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group btn-group-sm text-right">
                    <button type="button" class="btn btn-primary bmd-btn-fab account-add-btn">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <?php if ($msg != "") { ?>
                    <div class="alert alert-success" role="alert"><?= $msg ?></div>
                <?php } ?>
                <?php if ($err != "") { ?>
                    <div class="alert alert-danger" role="alert"><?= $err ?></div>
                <?php } ?>

                <div class="form-group text-center">
                    <?php if (isset($_POST['submit'])) { ?>
                        <a class="btn btn-primary btn-outline-secondary" href="../home.php">Go Back</a>
                    <?php } else { ?>
                        <a class="btn btn-primary btn-outline-secondary" href="../home.php">Cancel</a>
                    <?php } ?>
                    <button name="submit" type="submit" class="btn btn-primary btn-outline-primary">Submit</button>
                </div>
            </form>


        </div>
    </div>
</div>

<?= Template::footer() ?>
<script>
    var validateForm = function() {
        var total_debit = 0;
        var total_credit = 0;
        var submit_ok = true;

        $('#accounts-table tbody tr').each( function(i, e) {

            debit = $(e).find('.input-debit');
            credit = $(e).find('.input-credit');

            // both fields are empty for a record
            if (debit.val() == "" && credit.val() == "") {
                submit_ok = false;
                $.snackbar({content: "Missing entries"});
                return false;
            }

            // both fields are set for a record
            if ( (debit.val() != "") && (credit.val() != "") ) {
                $.snackbar({content: "Entry cannot have both debit and credit."});
                submit_ok = false;
                return false;
            }

            if (debit.val() != "") {
                total_debit = total_debit + parseFloat(debit.val());
            }

            if (credit.val() != "") {
                total_credit = total_credit + parseFloat(credit.val());
            }
        });

        if ( total_credit != total_debit ) {
            submit_ok = false;
            $.snackbar({content: "Debits must equal Credits!"});
        }

        return submit_ok;
    };

    $(document).ready(function() {
        $_account_ref = $('#accounts-table-ref-account').clone();
        $_account_ref.find('.nukem').removeAttr('disabled');

        $('.account-add-btn').on('click', function() {
            $t = $_account_ref.clone();
            $t.removeAttr('id');

            $t.find('.nukem').on('click', function() {
                $(this).parents('tr').remove();
            });

            $('#accounts-table tbody').append($t);
        });
    });
</script>
</body>
</html>
