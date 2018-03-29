<?php

session_start();

require_once __DIR__ . '/../src/template.php';
require_once __DIR__ . '/../src/user.php';
require_once __DIR__ . '/../src/journal.php';

Template::setPathDepth(1);
User::setPathDepth(1);

User::route(TRUE);

$actionable = FALSE;
$actionable_action = NULL;

if (isset($_GET['action'])) {
    $actionable = TRUE;
    $actionable_action = $_GET['action'];

    if ($_GET['action'] == 'account') {
        $entries = Journal::getJournalEntriesByAccount($_GET['id']);
        $account = Account::getAccounts($_GET['id'])[0];
        $balances = Account::getAccountBalance($_GET['id']);
    }
} else {
    if (User::getUserType() == User::TYPE_USER) {
        $entries = Journal::getJournalEntries(FALSE, User::getUserID());
    } else if (User::getUserType() == User::TYPE_MANAGER) {
        $entries = Journal::getJournalEntries();
    } else {
        $entries = Journal::getJournalEntries();
    }
}

/* defaults */
$__view_entry_date = FALSE;
$__view_entry_user = FALSE;
$__view_entry_desc = FALSE;
$__view_entry_account_name = FALSE;
$__view_entry_account_debit = FALSE;
$__view_entry_account_credit = FALSE;
$__view_entry_status = FALSE;
$__view_entry_update = FALSE;

$__view_entry_debitcredit_colspan = 3;

/* modifications */
$__view_entry_date = TRUE;

if (User::getUserType() != User::TYPE_USER || ($actionable && $actionable_action == 'account')) {
    $__view_entry_user = TRUE;
}

$__view_entry_desc = TRUE;

if ($actionable == TRUE) {
    if ($actionable_action == 'account') {
        $__view_entry_account_name = FALSE;
        $__view_entry_debitcredit_colspan = 2;
    } else {
        $__view_entry_account_name = TRUE;
    }
} else {
    $__view_entry_account_name = TRUE;
}

$__view_entry_account_debit = TRUE;
$__view_entry_account_credit = TRUE;
$__view_entry_status = TRUE;

if ($actionable == FALSE) {
    if (User::getUserType() == User::TYPE_MANAGER) {
        $__view_entry_update = TRUE;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <?= Template::header() ?>
    <link rel="stylesheet" href="../public/css/style.css">

    <title><?= Template::title("Account View") ?></title>
</head>
<body>
<?= Template::navbar() ?>

<div class="container-fluid body-container">
    <?php if ($actionable) {
        if ($actionable_action == 'account') {
            ?>
            <div class="jumbotron">
                <h1 class="text-center"><?= $account->id ?> - <?= $account->name ?></h1>
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td>Category</td>
                        <th><?= $account->category ?></th>
                    </tr>
                    <tr>
                        <td>Sub Category</td>
                        <th><?= $account->subcategory ?></th>
                    </tr>
                    <tr>
                        <td>User</td>
                        <th><?= $account->added ?></th>
                    </tr>
                    <tr>
                        <td>Initial Balance</td>
                        <th class="text-right"><span
                                    class="float-left">$</span><?= Template::number_format($account->initial_balance) ?>
                        </th>
                    </tr>
                    <tr>
                        <td>Normal Side</td>
                        <th><?php
                            if ($account->normal_side == 'D')
                                $normal_side = 'Debit';
                            else
                                $normal_side = 'Credit';
                            ?>
                            <?= $normal_side ?>
                        </th>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <th><?php
                            if ($account->status == Account::STATUS_ENABLED) {
                                $status = 'Active';
                            } else if ($account->status == Account::STATUS_DEACTIVATED) {
                                $status = 'Inactive';
                            } else {
                                $status = 'Deleted';
                            }
                            ?>
                            <?= $status ?>
                        </th>
                    </tr>
                    <tr>

                    </tr>
                    <tr>
                        <td>Total Debit</td>
                        <th class="text-right"><span
                                    class="float-left">$</span><?= Template::number_format($balances['total_debit']) ?>
                        </th>
                    </tr>
                    <tr>
                        <td>Total Credit</td>
                        <th class="text-right"><span
                                    class="float-left">$</span><?= Template::number_format($balances['total_credit']) ?>
                        </th>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php } // $actionable_action
    } // $actionable ?>
    <?php if ($actionable == FALSE) { ?>
        <nav class="navbar navbar-light">
            <form class="form-inline mr-auto">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <label class="input-group-text" for="inputGroupSelect01">Filter</label>
                    </div>
                    &nbsp;
                    <select name="category" class="custom-select" id="filter-category">
                        <option value="all" selected>All</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                        <option value="posted">Posted</option>
                    </select>
                    &nbsp;
                    <div class="input-group-append">
                        <button class="btn btn-raised btn-secondary filter-entries-btn" type="button">Update</button>
                    </div>
                </div>
            </form>
        </nav>
    <?php } ?>
    <div class="table-journal-view">
        <table class="table table-sm table-striped table-bordered">
            <thead class="thead-dark">
            <tr>

                <?php if ($__view_entry_date) { ?>
                    <th scope="col" class="text-center" style="min-width:100px;">Date</th>
                <?php } ?>

                <?php if ($__view_entry_user) { ?>
                    <th scope="col" class="text-center" style="min-width:100px;">User</th>
                <?php } ?>

                <?php if ($__view_entry_desc) { ?>
                    <th scope="col" class="text-center" style="min-width:250px;">Description</th>
                <?php } ?>

                <?php if ($__view_entry_account_name) { ?>
                    <th scope="col" class="text-center" style="width:200px; min-width: 200px;">Accounts</th>
                <?php } ?>

                <?php if ($__view_entry_account_debit) { ?>
                    <th scope="col" class="text-center" style="width:125px; min-width: 125px;">Debit</th>
                <?php } ?>

                <?php if ($__view_entry_account_credit) { ?>
                    <th scope="col" class="text-center" style="width:125px; min-width: 125px;">Credit</th>
                <?php } ?>

                <?php if ($__view_entry_status) { ?>
                    <th scope="col" class="text-center" style="min-width:225px;">Status</th>
                <?php } ?>

                <?php if ($__view_entry_update) { ?>
                    <th scope="col" class="text-center" style="min-width: 125px;">Update</th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($entries as $e) {
                $entry = Journal::getJournalEntry($e['ref']);
                ?>
                <tr class="journal-entry journal-entry-<?= $e['status'] ?>">

                    <?php if ($__view_entry_date) { ?>
                        <th scope="row"><?= $e['entry_date_formatted'] ?></th>
                    <?php } // $__view_entry_date ?>

                    <?php if ($__view_entry_user) { ?>
                        <td><?= $entry['user_name'] ?></td>
                    <?php } // $__view_entry_user ?>

                    <?php if ($__view_entry_desc) { ?>
                        <td>
                            <p>
                                <?= ($e['description'] == "" ? "NA" : $e['description']) ?>
                            </p>
                            <p>
                            <ul>
                                <?php foreach (unserialize($e['files']) as $f) { ?>
                                    <li><a href="../files/<?= $f ?>"><?= $f ?></a></li>
                                <?php } ?>
                            </ul>
                            </p>
                        </td>
                    <?php } // $__view_entry_desc ?>

                    <td class="account-entries" colspan="<?= $__view_entry_debitcredit_colspan ?>">
                        <table class="table">
                            <?php foreach ($entry['entries'] as $je) {
                                if ($actionable && $actionable_action == 'account') {
                                    if ($je['account_id'] != $_GET['id'])
                                        continue;
                                }
                                ?>
                                <tr>
                                    <?php
                                    $debit = '<span class="float-left">$</span>' . Template::number_format($je['debit']);
                                    $credit = '<span class="float-left">$</span>' . Template::number_format($je['credit']);
                                    if ($je['debit'] == 0) {
                                        $entry_style = "padding-left: 40px;";
                                        $debit = '';
                                    }
                                    if ($je['credit'] == 0) {
                                        $entry_style = '';
                                        $credit = '';
                                    }
                                    ?>

                                    <?php if ($__view_entry_account_name) { ?>
                                        <td style="width: 200px; min-width: 200px;<?= $entry_style ?>"><?= $je['account_id'] ?>
                                            - <?= $je['account_name'] ?></td>
                                    <?php } ?>

                                    <?php if ($__view_entry_account_debit) { ?>
                                        <td class="text-right"
                                            style="width: 125px; min-width: 125px;"><?= $debit ?></td>
                                    <?php } ?>

                                    <?php if ($__view_entry_account_credit) { ?>
                                        <td class="text-right"
                                            style="width: 125px; min-width: 125px;"><?= $credit ?></td>
                                    <?php } ?>
                                </tr>
                            <?php } // foreach $entry['entries'] as $je ?>
                        </table>
                    </td>

                    <?php if ($__view_entry_status) { ?>
                        <td class="entry-status">
                            <?php
                            $status = "";
                            if ($e['status'] == Journal::STATE_PENDING) {
                                $status = "Pending";
                            } else if ($e['status'] == Journal::STATE_REJECTED) {
                                $status = "Rejected";

                                $resp = "NA";
                                if ($e['response'] !== NULL) {
                                    $resp = $e['response'];
                                }
                                $status .= "<br>";
                                $status .= "Comment: " . $resp;
                            } else if ($e['status'] == Journal::STATE_POSTED) {
                                $status = "Posted";
                            }
                            echo $status;
                            ?>
                        </td>
                    <?php } // $__view_entry_status ?>


                    <?php if ($__view_entry_update) { ?>
                        <td class="text-center">
                            <?php if ($e['status'] == Journal::STATE_PENDING) { ?>
                                <form method="POST" action="do.manager.php" id="form<?= $e['ref'] ?>">
                                    <input type="hidden" name="ref" value="<?= $e['ref'] ?>">

                                    <input id="formResponse<?= $e['ref'] ?>" type="hidden" name="response">
                                    <input id="formAccept<?= $e['ref'] ?>" type="hidden" name="accept" value="0">

                                    <input type="button" class="btn btn-outline-success text-success" name="accept"
                                           value="Post" onclick="fill(<?= $e['ref'] ?>, 0)"><br>
                                    <input type="button" class="btn btn-outline-danger text-reject" name="reject"
                                           value="Reject" onclick="fill(<?= $e['ref'] ?>, 1)"><br>
                                </form>
                            <?php } ?>
                        </td>
                    <?php } // $__view_entry_update ?>
                </tr>
            <?php } // foreach entries as $e ?>
            </tbody>
        </table>
    </div>
</div>

<?= Template::footer() ?>
<script>
    function fill(id, reject) {
        domId = 'formResponse' + id;
        domEl = document.getElementById(domId);

        domForm = 'form' + id;
        domFormEl = document.getElementById(domForm);

        if (!reject) {
            domAccept = 'formAccept' + id;
            domAcceptEl = document.getElementById(domAccept);
            domAcceptEl.value = "1";
            domFormEl.submit();
            return;
        }

        var response = prompt("Please enter your comments", "");

        if (response == null || response == "") {
            alert("you need to enter response.");
            return;
        }

        domEl.value = response;
        domFormEl.submit();
    }

    $(document).ready(function () {
        $('.filter-entries-btn').on('click', function () {
            $val = $('#filter-category').val();
            if ($val == "all") {
                $('.journal-entry').removeClass('hidden');
            } else if ($val == "pending") {
                $('.journal-entry').removeClass('hidden');
                $('.journal-entry-1').addClass('hidden');
                $('.journal-entry-2').addClass('hidden');
            } else if ($val == "rejected") {
                $('.journal-entry').removeClass('hidden');
                $('.journal-entry-0').addClass('hidden');
                $('.journal-entry-2').addClass('hidden');
            } else if ($val == "posted") {
                $('.journal-entry').removeClass('hidden');
                $('.journal-entry-0').addClass('hidden');
                $('.journal-entry-1').addClass('hidden');
            }
        });
    });
</script>
</body>
</html>
