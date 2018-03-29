<?php

session_start();

require_once __DIR__ . '/src/template.php';
require_once __DIR__ . '/src/user.php';

User::route(TRUE);

?>
<!doctype html>
<html lang="en">
<head>
    <?= Template::header() ?>
    <link rel="stylesheet" href="public/css/style.css">

    <title><?= Template::title("Home") ?></title>
</head>
<body>
<?=Template::navbar()?>

<div class="container body-container">
    <nav class="navbar navbar-light">
        <ul class="navbar-nav mr-auto">
            <?php if (User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_ADD)) { ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-raised btn-primary" href="account/add.php">
                        <span><i class="fas fa-plus"></i> Add Account</span>
                    </a>
                </li>
            <?php } // permissible : CAN_ACCOUNT_ADD ?>
        </ul>
        <form action="home.php" method="get" class="form-inline ml-auto">

            <div class="input-group">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="inputGroupSelect01">
                        <i class="fas fa-search"></i>
                    </label>
                </div>
                &nbsp;
                <select name="category" class="custom-select" required>
                    <?php if (!isset($_GET['category'])) { ?>
                        <option value="" selected> Category...</option>
                    <?php } ?>
                    <?php foreach (Account::getAccountCategories() as $acc) {
                        $selected = '';
                        if (isset($_GET['category'])) {
                            if ($acc->id == $_GET['category']) {
                                $selected = ' selected';
                            }
                        }
                        ?>
                        <option value="<?= $acc->id ?>" <?=$selected?>><?= $acc->name ?></option>
                    <?php } // foreach $acc ?>
                </select>
                &nbsp;
                <div class="input-group-append">
                    <button type="submit" class="btn btn-raised btn-secondary" type="button">Search</button>
                </div>
            </div>

        </form>
    </nav>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
            <tr>
                <th scope="col" class="text-center">#</th>
                <th scope="col" class="text-center">Name</th>
                <th scope="col" class="text-center">Category</th>
                <th scope="col" class="text-center">Sub Category</th>
                <th scope="col" class="text-center">Initial Balance</th>
                <th scope="col" class="text-center">Normal Side</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Date/Time</th>
                <th scope="col" class="text-center">User ID</th>
                <?php if (
                    User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_EDIT) ||
                    User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_DELETE)
                ) { ?>
                    <th scope="col" class="text-center">Update</th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach (Account::getAccounts() as $acc) {
                if (isset($_GET['category'])) {
                    if ($acc->category_id != $_GET['category']) {
                        continue;
                    }
                }

                ?>
                <tr>

                    <th scope="row"><?= $acc->id ?></th>
                    <td>
                        <a href="journal/view.php?action=account&id=<?=$acc->id?>" title="View Account"><?= $acc->name ?></a>
                    </td>
                    <td><?= $acc->category ?></td>
                    <td><?= $acc->subcategory ?></td>
                    <td class="text-right"><span class="float-left">$</span><?= Template::number_format($acc->initial_balance) ?></td>

                    <?php if ($acc->normal_side == 'C') { ?>
                        <td>Credit</td>
                    <?php } else { ?>
                        <td>Debit</td>
                    <?php } ?>

                    <?php if ($acc->status == Account::STATUS_ENABLED) { ?>
                        <td>Active</td>
                    <?php } else { ?>
                        <td>Inactive</td>
                    <?php } ?>

                    <?php
                    $phpdate = strtotime($acc->created);
                    $strdate = date('m/d/Y h:iA', $phpdate);
                    ?>
                    <td><?= $strdate ?></td>
                    <td><?= $acc->added ?></td>
                    <?php if (
                        User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_EDIT) ||
                        User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_DELETE)
                    ) { ?>

                        <td class="text-center">
                            <?php if (User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_EDIT)) { ?>
                                <a class="btn btn-outline-success text-success" title="Edit"
                                   href="account/edit.php?id=<?= $acc->id ?>"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php if (User::permissibleUser(User::getUserType(), User::CAN_ACCOUNT_DELETE)) {
                                if ($acc->status == Account::STATUS_ENABLED) {
                                    $action = "disable";
                                    $icon = 'fa-trash-alt';
                                    $title = 'De-activate';
                                } else if ($acc->status == Account::STATUS_DEACTIVATED) {
                                    $action = "enable";
                                    $icon = 'fa-unlock-alt';
                                    $title = 'Activate';
                                }
                                echo '<a class="btn btn-outline-danger text-danger" title="' . $title . '" href="account/delete.php?id=' . $acc->id . '&action=' . $action . '"><i class="fas ' . $icon . '"></i></a>';
                            } ?>
                        </td>

                    <?php } // user can edit | delete ?>
                </tr>
            <?php } // foreach getAccounts as $acc ?>
            </tbody>
        </table>
    </div>
</div>

<?= Template::footer() ?>
</body>
</html>
